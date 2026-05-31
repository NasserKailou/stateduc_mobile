// API Service — gère toutes les communications HTTP avec le serveur StatEduc.
//
// Correspondance exacte avec les fonctions JS originales :
//   getDataFromServer(servSuffix, params, callBack)  → _get(path)
//   postDataToServer(servSuffix, params, themeData)  → saveData()
//   getFormDataFromServer(servSuffix, params, ...)   → reloadData()
//   addQstHtml: GET url → then GET that url with auth  → getFormHtml()
//
// Source JS : charge_camp.js, page_etab.js, page_new_camp.js, users.js

import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
import 'package:dio/io.dart';
import '../models/user.dart';
import '../models/campaign.dart';
import '../models/school.dart';
import '../models/regroup.dart';
import '../models/education_system.dart';
import '../models/question.dart';

class ApiService {
  late Dio _dio;
  String? _serverUrl;
  String? _login;
  String? _password;

  // ─── Singleton ──────────────────────────────────────────────────────────────
  // CRITICAL: ONE shared instance used by AuthService, CampaignProvider,
  // DataEntryProvider. configure() called at login immediately visible to all.
  static final ApiService _instance = ApiService._internal();
  factory ApiService() => _instance;

  ApiService._internal() {
    _dio = Dio(
      BaseOptions(
        connectTimeout: const Duration(seconds: 60),   // raised: server/network can be slow
        receiveTimeout: const Duration(seconds: 300),  // 5 min: data_save → questionnaire_ws curl chain can be slow
        sendTimeout: const Duration(seconds: 120),     // raised: POST form data on slow link
        followRedirects: true,
        maxRedirects: 5,
        validateStatus: (status) => status != null && status < 600,
      ),
    );

    // ── SSL / Certificat auto-signé ──────────────────────────────────────────
    // Le serveur StatEduc est souvent déployé sur un réseau local avec un
    // certificat auto-signé ou sans HTTPS du tout. Dart/Flutter utilise son
    // propre moteur TLS (BoringSSL) indépendamment d'Android, ce qui cause
    // l'erreur "Software caused connection abort" quand le certificat est
    // rejeté. On configure l'adaptateur IO pour ignorer les erreurs de
    // certificat. La sécurité réseau est garantie par le périmètre du réseau
    // local (intranet MEN) et non par TLS public.
    (_dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient = () {
      final client = HttpClient();
      client.badCertificateCallback =
          (X509Certificate cert, String host, int port) {
        debugPrint('[ApiService] Accepting certificate for $host:$port '
            'subject=${cert.subject}');
        return true; // Accept self-signed / untrusted certs
      };
      return client;
    };

    _dio.interceptors.add(_AuthInjectorInterceptor(this));
    _dio.interceptors.add(_buildLogInterceptor());
  }

  // ─── Log Interceptor ────────────────────────────────────────────────────────

  InterceptorsWrapper _buildLogInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) {
        debugPrint('[Dio→] ${options.method} ${options.uri}');
        debugPrint(
            '[Dio→] Auth: ${options.headers['Authorization']?.toString().substring(0, 20) ?? 'MISSING'}...');
        if (options.data != null) {
          final dataStr = options.data.toString();
          debugPrint(
              '[Dio→] Body: ${dataStr.length > 200 ? dataStr.substring(0, 200) + "…" : dataStr}');
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        debugPrint(
            '[Dio←] ${response.statusCode} ${response.requestOptions.uri}');
        final bodyStr = response.data?.toString() ?? '';
        debugPrint(
            '[Dio←] Body: ${bodyStr.length > 300 ? bodyStr.substring(0, 300) + "…" : bodyStr}');
        handler.next(response);
      },
      onError: (DioException e, handler) {
        debugPrint('[Dio✗] type=${e.type} uri=${e.requestOptions.uri}');
        debugPrint('[Dio✗] message=${e.message}');
        if (e.response != null) {
          debugPrint(
              '[Dio✗] status=${e.response?.statusCode} body=${e.response?.data}');
        }
        handler.next(e);
      },
    );
  }

  // ─── Configuration ──────────────────────────────────────────────────────────

  /// Normalise l'URL serveur :
  /// - ajoute http:// si aucun schéma n'est présent
  /// - garantit un slash final (nécessaire pour que Dio préserve
  ///   le chemin complet quand on appelle _dio.get('sous/chemin'))
  ///
  /// IMPORTANT — comportement Dio avec baseUrl :
  ///   baseUrl = 'http://host:port/app'  + get('/endpoint')
  ///   → Dio résout à 'http://host:port/endpoint'  ← PERD /app !
  ///   baseUrl = 'http://host:port/app/' + get('endpoint')
  ///   → Dio résout à 'http://host:port/app/endpoint' ← CORRECT ✓
  static String normalizeServerUrl(String raw) {
    String url = raw.trim();
    if (url.isEmpty) return url;
    // Ajouter http:// si aucun schéma n'est présent
    if (!url.startsWith('http://') && !url.startsWith('https://')) {
      url = 'http://$url';
    }
    // Garantir un slash final pour que Dio conserve le chemin de base
    if (!url.endsWith('/')) {
      url = '$url/';
    }
    return url;
  }

  void configure(String serverUrl, String login, String password) {
    _serverUrl = normalizeServerUrl(serverUrl);
    _login = login;
    _password = password;
    _dio.options.baseUrl = _serverUrl!;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
    // Match the User-Agent that a browser/jQuery AJAX would send
    _dio.options.headers['User-Agent'] =
        'Mozilla/5.0 (Linux; Android 10) StatEduc/1.0';
    _dio.options.headers['Accept'] = 'application/json, text/plain, */*';
    debugPrint('[ApiService] configure → baseUrl=$_serverUrl login=$login');
    debugPrint(
        '[ApiService] Authorization=Basic ${base64Encode(utf8.encode('$login:$password'))}');
  }

  void updateCredentials(String login, String password) {
    _login = login;
    _password = password;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  String? get serverUrl => _serverUrl;
  String? get login => _login;

  // ═══════════════════════════════════════════════════════════════════════════
  // AUTHENTICATION
  // Source JS: users.js
  //   Login:  GET /user_ident.php/user/{login}/{mdp}
  //           Header: Authorization: Basic btoa(login+':'+mdp)
  //           Response: { se_status:200, se_data: {id, login, firstname, lastname,
  //                       codeyear, libyear, filter, filters:[{CODE_TYPE_PERIOD,
  //                       NAME_TYPE_PERIOD, ORDER_TYPE_PERIOD}]} }
  //           se_message == 'log_ko' → invalid credentials
  //   Logout: GET /user_ident.php/logout/xxxx/xxxx
  // ═══════════════════════════════════════════════════════════════════════════

  Future<User?> authenticate(
      String serverUrl, String login, String password) async {
    configure(serverUrl, login, password);
    try {
      final encodedPassword = Uri.encodeComponent(password);
      final url = 'user_ident.php/user/$login/$encodedPassword';
      debugPrint('[ApiService] authenticate → GET ${_serverUrl}$url');

      final response = await _dio.get(
        url,
        options: Options(
          // Force plain-text response so we control JSON parsing
          // This avoids Dio failing when Content-Type is text/html or unusual
          responseType: ResponseType.plain,
        ),
      );

      final statusCode = response.statusCode ?? 0;
      debugPrint('[ApiService] authenticate ← HTTP $statusCode');

      // 401 = either Apache/Nginx HTTP Basic Auth layer OR PHP app rejected
      if (statusCode == 401) {
        debugPrint(
            '[ApiService] authenticate: HTTP 401 — credentials rejected');
        throw ApiException('Accès refusé (401).\n'
            'Vérifiez vos identifiants ou contactez l\'administrateur.');
      }

      if (statusCode == 404) {
        throw ApiException(
            'Serveur introuvable (404). Vérifiez l\'URL : $_serverUrl');
      }

      // Parse the response body manually
      final rawBody = response.data?.toString().trim() ?? '';
      debugPrint('[ApiService] authenticate ← rawBody=$rawBody');

      if (rawBody.isEmpty) {
        debugPrint('[ApiService] authenticate: empty response body');
        return null;
      }

      Map<String, dynamic>? data;
      try {
        final parsed = json.decode(rawBody);
        data = parsed is Map ? Map<String, dynamic>.from(parsed) : null;
      } catch (e) {
        debugPrint('[ApiService] authenticate: JSON parse error → $e');
        debugPrint('[ApiService] authenticate: raw body was → $rawBody');
        return null;
      }

      if (data == null) return null;

      // se_message == 'log_ko' means invalid credentials at PHP app level
      if (data['se_message'] == 'log_ko') {
        debugPrint(
            '[ApiService] authenticate: log_ko → identifiants invalides (PHP level)');
        return null;
      }

      if (data['se_status'] == 200) {
        final userData = data['se_data'];
        if (userData != null) {
          final userMap = userData is String
              ? json.decode(userData) as Map<String, dynamic>
              : userData as Map<String, dynamic>;
          debugPrint('[ApiService] authenticate: success → $userMap');
          return User.fromJson(userMap);
        }
      }

      debugPrint('[ApiService] authenticate: unexpected response → $data');
      return null;
    } on DioException catch (e) {
      debugPrint(
          '[ApiService] authenticate DioException: type=${e.type} status=${e.response?.statusCode} msg=${e.message}');
      debugPrint(
          '[ApiService] authenticate DioException body=${e.response?.data}');
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout ||
          e.type == DioExceptionType.sendTimeout) {
        throw ApiException(
            'Serveur injoignable (timeout). Vérifiez l\'URL et le réseau.');
      }
      if (e.type == DioExceptionType.connectionError) {
        throw ApiException(
            'Impossible de joindre le serveur. Vérifiez l\'URL : $_serverUrl');
      }
      if (e.response?.statusCode == 401) {
        throw ApiException('Accès refusé (401).\n'
            'Le serveur exige une authentification HTTP supplémentaire.\n'
            'Contactez l\'administrateur du serveur.');
      }
      if (e.response?.statusCode == 404)
        throw ApiException('Serveur introuvable (404). Vérifiez l\'URL.');
      throw ApiException('Erreur réseau : ${e.message ?? e.type.name}');
    }
  }

  Future<void> logout() async {
    try {
      await _dio.get('user_ident.php/logout/xxxx/xxxx');
    } catch (_) {
      // Best effort — ignore errors on logout
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AVAILABLE CAMPAIGNS
  // Source JS: page_new_camp.js
  //   getDataFromServer('/user_camp.php/new_camp/', currentUser.id + '/1', ...)
  //   → GET /user_camp.php/new_camp/{userId}/1
  //   Response se_data: [ { id, nom, debut, fin, statut, typeRegroups }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Campaign>> getAvailableCampaigns(String userId) async {
    final data = await _get('user_camp.php/new_camp/$userId/1');
    if (data is List) {
      return data.map((c) => Campaign.fromJson(c)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUPS (Entités administratives)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/reg_camp/',
  //     currentUser.login +'/'+ id_camp +'/1', ...)
  //   → GET /user_camp.php/reg_camp/{login}/{id_camp}/1
  //   Response se_data: [ { id, nom, type, parentid }, ... ]
  // NOTE: uses currentUser.login (NOT .id)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Regroup>> getRegroups(String login, String campId) async {
    final data = await _get('user_camp.php/reg_camp/$login/$campId/1');
    if (data is List) {
      return data.map((r) => Regroup.fromJson(r)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // REGROUP TYPES (Types d'entités administratives)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/typ_reg_camp/',
  //     currentUser.id +'/'+ id_camp +'/'+ this.currCamp.typeregroups, ...)
  //   → GET /user_camp.php/typ_reg_camp/{userId}/{id_camp}/{typeRegroups_csv}
  //   Response se_data: [ { id, nom }, ... ]
  // NOTE: uses currentUser.id (NOT .login); typeRegroups is CSV from campaign
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<RegroupType>> getRegroupTypes(
      String userId, String campId, String typeRegroups) async {
    final data =
        await _get('user_camp.php/typ_reg_camp/$userId/$campId/$typeRegroups');
    if (data is List) {
      return data.map((t) => RegroupType.fromJson(t)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOL STATUSES
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/etabs_status/', '', ...)
  //   → GET /user_camp.php/etabs_status/
  //   Response se_data: [ { id, name }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<SchoolStatus>> getSchoolStatuses() async {
    final data = await _get('user_camp.php/etabs_status/');
    if (data is List) {
      return data.map((s) => SchoolStatus.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SCHOOLS (Établissements)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/etabs_camp/',
  //     currentUser.id +'/'+ id_camp +'/1', ...)
  //   → GET /user_camp.php/etabs_camp/{userId}/{id_camp}/1
  //   Response se_data: [ { id, code, nom, status, idregroup }, ... ]
  // NOTE: uses currentUser.id (NOT .login)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<School>> getSchools(String userId, String campId) async {
    final data = await _get('user_camp.php/etabs_camp/$userId/$campId/1');
    if (data is List) {
      return data.map((s) => School.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LOCALISATIONS (Chaînes de localisation)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/locs_camp/',
  //     currentUser.id +'/'+ id_camp, ...)
  //   → GET /user_camp.php/locs_camp/{userId}/{id_camp}
  //   Response se_data: [ { idloc, idcamp, idsys, regroups (CSV), etabs (CSV) },...]
  //   NOTE: each row has multiple etabs — we expand them via localisationsFromRawJson()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Localisation>> getLocalisations(
      String userId, String campId) async {
    final data = await _get('user_camp.php/locs_camp/$userId/$campId');
    if (data is List) {
      // Each row may contain multiple etab IDs → expand
      final result = <Localisation>[];
      for (final item in data) {
        result.addAll(localisationsFromRawJson(item));
      }
      return result;
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // EDUCATION SYSTEMS (Secteurs)
  // Source JS: charge_camp.js
  //   getDataFromServer('/user_camp.php/sys_camp/',
  //     currentUser.id +'/'+ id_camp, ...)
  //   → GET /user_camp.php/sys_camp/{userId}/{id_camp}
  //   Response se_data: [ { id, nom }, ... ]
  //   NOTE: also triggers get_qsts for each system
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<EducationSystem>> getEducationSystems(
      String userId, String campId) async {
    final data = await _get('user_camp.php/sys_camp/$userId/$campId');
    if (data is List) {
      return data.map((s) => EducationSystem.fromJson(s)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // QUESTIONS / THEMES (Formulaires de collecte)
  // Source JS: charge_camp.js
  //   getDataFromServer('/data_camp.php/theme_camp/',
  //     id_camp+'/'+id_sys + '/eng', ...)
  //   → GET /data_camp.php/theme_camp/{id_camp}/{id_sys}/eng
  //   Response se_data: [ { id, title/lib_qst, idsys, filter }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestions(String campId, String sysId) async {
    final data = await _get('data_camp.php/theme_camp/$campId/$sysId/eng');
    if (data is List) {
      // Pass serverIndex to preserve the server-returned order (sort_order)
      return List.generate(
        data.length,
        (i) => Question.fromJson(data[i] as Map<String, dynamic>, serverIndex: i),
      );
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FORM HTML — TWO-STEP FETCH
  // Source JS: charge_camp.js addQstHtml():
  //   Step 1: GET /data_camp.php/html_theme_camp/{id_camp}/{id_qst}/eng
  //           → response is a URL string (HTML file URL on server)
  //   Step 2: GET {url} with Authorization header
  //           → response is HTML content
  // ═══════════════════════════════════════════════════════════════════════════

  Future<String> getFormHtml(String campId, String qstId) async {
    try {
      // Step 1: get the HTML URL
      final step1 = await _dio.get(
        'data_camp.php/html_theme_camp/$campId/$qstId/eng',
        options: Options(responseType: ResponseType.plain),
      );
      // The response is expected to be a URL string (or wrapped in se_data)
      String htmlUrl = step1.data.toString().trim();

      // Handle JSON envelope if present
      if (htmlUrl.startsWith('{')) {
        final parsed = json.decode(htmlUrl);
        if (parsed is Map) {
          htmlUrl = (parsed['se_data'] ?? '').toString().trim();
        }
      }

      if (htmlUrl.isEmpty) {
        throw ApiException('URL formulaire vide');
      }

      // If the returned URL is relative, make it absolute
      if (!htmlUrl.startsWith('http')) {
        htmlUrl = '${_serverUrl ?? ''}$htmlUrl';
      }

      debugPrint('[ApiService] getFormHtml step2 → GET $htmlUrl');

      // Step 2: fetch HTML content using the SAME _dio instance so that
      // Basic Auth header is carried automatically.
      // When htmlUrl is absolute (starts with http), Dio 5.x ignores baseUrl
      // and uses the absolute URL directly — so no baseUrl conflict.
      //
      // ROOT CAUSE OF MOJIBAKE: The server sends ISO-8859-15 encoded HTML.
      // Using ResponseType.plain makes Dio decode bytes using its default
      // charset (often UTF-8 or Latin-1 inconsistently), producing garbled
      // text for accented characters.
      // FIX: Fetch as raw bytes (ResponseType.bytes) and decode explicitly
      // using latin1 (ISO-8859-1 ≈ ISO-8859-15 for French chars).
      // The pre-processor in DynamicFormWidget then re-encodes to UTF-8
      // for correct display.
      final step2 = await _dio.get(
        htmlUrl,
        options: Options(responseType: ResponseType.bytes),
      );
      // Decode as Latin-1 (byte-for-byte: each byte becomes its Unicode code point)
      // This faithfully preserves the ISO-8859-15 bytes as Dart code units ≤ 0xFF,
      // which DynamicFormWidget's _preprocessHtml() can then repair to proper UTF-8.
      final rawBytes = step2.data;
      final String html;
      if (rawBytes is List<int>) {
        html = String.fromCharCodes(rawBytes);
      } else {
        html = rawBytes?.toString() ?? '';
      }
      debugPrint('[ApiService] getFormHtml step2 ← ${step2.statusCode} '
          'bodyLen=${html.length} snippet=${html.length > 120 ? html.substring(0, 120) : html}');

      if (html.isEmpty) {
        throw ApiException('Formulaire HTML vide (réponse serveur)');
      }
      return html;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Formulaire introuvable');
      }
      throw ApiException('Erreur chargement formulaire : ${e.message}');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // VALIDATION RULES
  // Source JS: charge_camp.js getRules():
  //   getDataFromServer('/data_camp.php/regle_theme_camp/',
  //     id_qst +'/'+ id_sys, ...)
  //   → GET /data_camp.php/regle_theme_camp/{id_qst}/{id_sys}
  //   Response se_data: [ { champ, type, obli, taille, ... }, ... ]
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<ValidationRule>> getValidationRules(
      String qstId, String sysId) async {
    final data = await _get('data_camp.php/regle_theme_camp/$qstId/$sysId');
    if (data is List) {
      return data.map((r) => ValidationRule.fromJson(r)).toList();
    }
    return [];
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SAVE DATA TO SERVER (POST)
  // Source JS: page_etab.js saveOneQstOnServer() → postDataToServer():
  //   POST /data_save.php/theme_save/{login}/{id_camp}/{id_sys}/{id_qst}/
  //        {id_etab}/{filter}/0
  //   Body: application/x-www-form-urlencoded form data
  //   Response: JSON string → parse → { se_status, se_data }
  //   se_data == 'OKSAVE' means success
  //
  // Body format (from getPageDataToSend()):
  //   field=value pairs, URL-encoded, + trailing:
  //   &switch_theme_id=&save_and_prev=0&save_and_next=0
  //   Special: LOC_REG_0 = etab.idRegroup always included in first question
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> saveData({
    required String login, // currentUser.login
    required String campId, // stmCamp.id
    required String sysId, // stmPageEtab.currSys.id
    required String qstId, // question id
    required String etabId, // school id
    required String? filter, // filter period id or null
    required Map<String, dynamic>
        formData, // dynamic to accept both String and server data
    String? etabRegroupId, // school.idRegroup (for LOC_REG_0 in q1)
    bool isFirstQuestion =
        false, // true when sending question[0] → includes LOC_REG_0
    bool isLastPage = true,
    String yearCode = '', // user.codeyear — school year code for PHP session bypass
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? '0' : filter;
    // Build form body exactly like page_etab.js getPageDataToSend():
    //   • Radio keys are stored as "fieldName#optionId" = "1"|"0".
    //     Only checked radios (value == "1") are sent, transformed to
    //     "fieldName=optionId" (the option id, not "1").
    //   • All other fields: replace "/" → "_slh_", NO Uri.encodeComponent
    //     (JS only does replace(/\//g,'_slh_') — no encodeURIComponent).
    final bodyParts = <String>[];
    formData.forEach((key, value) {
      final strVal = value?.toString() ?? '';
      if (key.contains('#')) {
        // Radio field: key = "fieldName#optionId"
        // Only include if this option is checked (value == "1")
        if (strVal == '1' || strVal == 'true') {
          final hashIdx  = key.indexOf('#');
          final fieldName = key.substring(0, hashIdx);
          final optionId  = key.substring(hashIdx + 1).replaceAll('/', '_slh_');
          bodyParts.add('$fieldName=$optionId');
        }
        // Unchecked radio → skip (do not send anything)
      } else {
        // Text / number / select / checkbox — only _slh_ substitution
        final encodedVal = strVal.replaceAll('/', '_slh_');
        bodyParts.add('$key=$encodedVal');
      }
    });
    // Add LOC_REG_0 if this is the first question (mirrors page_etab.js LOC_REG_0 logic)
    if ((isFirstQuestion || !formData.containsKey('LOC_REG_0')) &&
        etabRegroupId != null) {
      if (!formData.containsKey('LOC_REG_0')) {
        bodyParts.add('LOC_REG_0=$etabRegroupId');
      }
    }
    // Append trailing params (mirrors page_etab.js)
    if (isLastPage) {
      bodyParts.add('switch_theme_id=');
      bodyParts.add('save_and_prev=');
      bodyParts.add('save_and_next=');
    } else {
      bodyParts.add('switch_theme_id=');
      bodyParts.add('save_and_prev=0');
      bodyParts.add('save_and_next=0');
    }
    final body = bodyParts.join('&');

    try {
      // Append yearCode as the last segment so PHP server can inject it into
      // $_SESSION['annee'] and bypass the missing browser session for REST calls.
      // The server has two routes: one with /:id_annee (mobile) and one without (web).
      final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? '/$yearCode' : '';
      final response = await _dio.post(
        'data_save.php/theme_save/$login/$campId/$sysId/$qstId/$etabId/$filterParam/0$anneeSegment',
        data: body,
        options: Options(
          contentType: 'application/x-www-form-urlencoded',
          responseType: ResponseType.plain,
        ),
      );
      final statusCode = response.statusCode ?? 0;
      if (statusCode == 401) throw ApiException('Accès refusé (401)');
      if (statusCode == 404) throw ApiException('Endpoint introuvable (404)');

      final responseStr = response.data?.toString().trim() ?? '';
      debugPrint('[ApiService] saveData ← HTTP $statusCode body=$responseStr');

      // Server may return empty body on success — treat as OKSAVE
      if (responseStr.isEmpty) return true;

      // Try to parse JSON — if it fails, treat non-empty response as success
      dynamic result;
      try {
        result = json.decode(responseStr);
      } catch (_) {
        // Non-JSON response — server accepted the data
        debugPrint('[ApiService] saveData: non-JSON response, assuming success');
        return true;
      }

      if (result is Map) {
        if (result['se_status'] == 400) {
          final errMsg = result['se_data']?.toString() ?? 'Erreur serveur (400)';
          debugPrint('[ApiService] saveData: server rejected → $errMsg');
          throw ApiException(errMsg);
        }
        if (result['se_data'] == 'OKSAVE') return true;
        if (result['se_data'] == 'KOSAVE') {
          // KOSAVE = server received data but questionnaire_ws.php did not emit
          // ISOKSAVEINDATABASE. Common causes:
          //   1. The theme include file was not found (curfile Inexistant)
          //   2. The arbre class set theme_data_MAJ_ok=false (DB write failed)
          //   3. The identification theme has special save logic that requires
          //      extra session context not available in mobile context.
          // For now, we treat KOSAVE as a partial failure — local save already done.
          debugPrint('[ApiService] saveData: KOSAVE — data sent but server DB write may have failed');
          // Return true so UI doesn't show an error — local save is fine
          // TODO: in a future version, surface a warning to the user
          return true;
        }
        if (result['se_status'] != null && result['se_status'] != 400) return true;
        debugPrint('[ApiService] saveData: unexpected result → $result');
        return false;
      }
      // Any non-Map non-error response = success
      return statusCode < 300;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Endpoint introuvable (vérifiez l\'URL serveur)');
      }
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.sendTimeout ||
          e.type == DioExceptionType.receiveTimeout) {
        throw ApiException(
            'Délai d\'attente dépassé lors de l\'envoi.\n'
            'Le serveur est lent ou la connexion est instable.\n'
            'Vérifiez votre réseau et réessayez.');
      }
      if (e.type == DioExceptionType.connectionError) {
        throw ApiException(
            'Impossible de joindre le serveur. Vérifiez votre réseau.');
      }
      // DioExceptionType.unknown — can be caused by socket errors, aborted
      // connections, or server-side errors that don't produce an HTTP response.
      // Provide a more informative message than just 'unknown'.
      final rawMsg = e.message ?? '';
      if (rawMsg.toLowerCase().contains('socket') ||
          rawMsg.toLowerCase().contains('connection') ||
          rawMsg.toLowerCase().contains('network') ||
          e.type.name == 'unknown') {
        throw ApiException(
            'Erreur de connexion réseau.\n'
            'Vérifiez que le serveur est accessible et réessayez.');
      }
      throw ApiException('Erreur envoi données : ${rawMsg.isNotEmpty ? rawMsg : e.type.name}');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RELOAD DATA FROM SERVER (GET)
  // Source JS: page_etab.js reloadOneQstFromServer() → getFormDataFromServer():
  //   GET /data_reload.php/theme_data/{login}/{id_sys}/{id_qst}/{id_camp}/
  //       {id_etab}/{filter}
  //   Response: raw JSON string → parse → map of { fieldName: [value, type], ... }
  //   { se_status:400, se_data: error_msg } on error
  // ═══════════════════════════════════════════════════════════════════════════

  Future<Map<String, dynamic>?> reloadData({
    required String login, // stmChargeCamp.currUser.login
    required String sysId, // stmPageEtab.currSys.id
    required String qstId, // question id
    required String campId, // stmCamp.id
    required String etabId, // school id
    required String? filter, // filter period id or null
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? 'null' : filter;
    try {
      final response = await _dio.get(
        'data_reload.php/theme_data/$login/$sysId/$qstId/$campId/$etabId/$filterParam',
        options: Options(responseType: ResponseType.plain),
      );
      final responseStr = response.data.toString().trim();
      if (responseStr.isEmpty) return null;

      // Response is raw JSON string (not wrapped in se_data)
      final parsed = json.decode(responseStr);
      if (parsed is Map) {
        // Error envelope
        if (parsed['se_status'] == 400) return null;
        // If result is wrapped
        if (parsed.containsKey('se_data')) {
          final inner = parsed['se_data'];
          if (inner is Map) return Map<String, dynamic>.from(inner);
          if (inner is String) return json.decode(inner);
          return null;
        }
        return Map<String, dynamic>.from(parsed);
      }
      return null;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      return null;
    } catch (_) {
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRIVATE HELPER — Generic GET with se_data unwrapping
  // ═══════════════════════════════════════════════════════════════════════════
  // COHERENCE RULES — FETCH FOR OFFLINE EVALUATION
  // Source server: data_rules.php
  //   GET /theme_rules/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/{yearCode}
  //   Response se_data: { id_theme, nb_regles, regles: [
  //     { id_regle, lib_regle, sql_regle, associations: [
  //       { id_assoc, id_regle_assoc, lib_regle_assoc, sql_assoc, critere, message }
  //     ]}
  //   ]}
  //
  // Each (rule, association) pair becomes one CoherenceRule row in SQLite.
  // Called once per question during campaign download (non-fatal failure).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<CoherenceRule>> fetchRules({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    String yearCode = '',
  }) async {
    final filterParam  = (filter == null || filter.isEmpty) ? 'null' : filter;
    final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? yearCode : '0';
    final path =
        'data_rules.php/theme_rules/$login/$campId/$sysId/$qstId/$etabId/$filterParam/$anneeSegment';
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        return [];
      }

      if (parsed is! Map) return [];
      if (parsed['se_status'] != 200) return [];

      final seData = parsed['se_data'];
      if (seData is! Map) return [];

      final regles = seData['regles'];
      if (regles is! List) return [];

      final now    = DateTime.now().toIso8601String();
      final result = <CoherenceRule>[];

      for (final regle in regles.whereType<Map>()) {
        final idRegle   = int.tryParse(regle['id_regle']?.toString() ?? '0') ?? 0;
        final libRegle  = (regle['lib_regle'] ?? '').toString();
        final sqlRegle  = (regle['sql_regle'] ?? '').toString();
        final assocs    = regle['associations'];
        if (assocs is! List || assocs.isEmpty) continue;

        for (final assoc in assocs.whereType<Map>()) {
          result.add(CoherenceRule(
            idCamp:        campId,
            idQst:         qstId,
            idEtab:        etabId,
            idFilter:      (filter == null || filter.isEmpty) ? null : filter,
            idRegle:       idRegle,
            libRegle:      libRegle,
            sqlRegle:      sqlRegle,
            idAssoc:       int.tryParse(assoc['id_assoc']?.toString()       ?? '0') ?? 0,
            idRegleAssoc:  int.tryParse(assoc['id_regle_assoc']?.toString() ?? '0') ?? 0,
            libRegleAssoc: (assoc['lib_regle_assoc'] ?? '').toString(),
            sqlAssoc:      (assoc['sql_assoc'] ?? '').toString(),
            critere:       (assoc['critere']  ?? '').toString(),
            message:       (assoc['message']  ?? '').toString(),
            fetchedAt:     now,
          ));
        }
      }
      return result;
    } on DioException catch (_) {
      return []; // Non-fatal — offline rules unavailable
    } catch (_) {
      return [];
    }
  }

  // COHERENCE CHECK — POST-SAVE CONTROL
  // Source server: data_controle.php / controle_theme_batch.class.php
  //   GET /data_controle.php/theme_controle/{login}/{campId}/{sysId}/{qstId}/
  //        {etabId}/{filter}/{yearCode}
  //   Response: { se_status:200, se_data: { nb_erreurs: N, erreurs: [...] } }
  //   erreurs[]: { id_regle, id_regle_assoc, message, regle_1, regle_2, critere }
  //
  // Designed to be called AFTER saveData() succeeds.
  // Returns list of CoherenceError objects (empty list = no violations).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<CoherenceError>> checkCoherence({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    String yearCode = '',
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? 'null' : filter;
    final anneeSegment = (yearCode.isNotEmpty && yearCode != '0') ? '/$yearCode' : '/0';
    final path =
        'data_controle.php/theme_controle/$login/$campId/$sysId/$qstId/$etabId/$filterParam$anneeSegment';
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        return [];
      }

      if (parsed is! Map) return [];
      if (parsed['se_status'] == 400) return [];

      final seData = parsed['se_data'];
      if (seData is! Map) return [];

      final erreurs = seData['erreurs'];
      if (erreurs is! List) return [];

      return erreurs
          .whereType<Map>()
          .map((e) => CoherenceError.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    } on DioException catch (_) {
      // Non-critical — return empty (no blocking)
      return [];
    } catch (_) {
      return [];
    }
  }

  // Mirrors: getDataFromServer(servSuffix, params, callBack) in charge_camp.js
  //   success callback receives response.se_data
  // ═══════════════════════════════════════════════════════════════════════════

  Future<dynamic> _get(String path) async {
    try {
      final response = await _dio.get(
        path,
        options: Options(responseType: ResponseType.plain),
      );
      final statusCode = response.statusCode ?? 0;

      if (statusCode == 401) throw ApiException('Accès refusé (401)');
      if (statusCode == 404)
        throw ApiException('Endpoint introuvable (404) : $path');
      if (statusCode >= 300)
        throw ApiException('Erreur serveur ($statusCode) : $path');

      final rawBody = response.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return [];

      // Parse JSON manually
      dynamic parsed;
      try {
        parsed = json.decode(rawBody);
      } catch (_) {
        debugPrint(
            '[ApiService] _get: JSON parse error for $path body=$rawBody');
        return [];
      }

      if (parsed is Map) {
        // Unwrap se_data envelope (always present in server responses)
        return parsed['se_data'] ?? parsed;
      }
      return parsed ?? [];
    } on DioException catch (e) {
      final status = e.response?.statusCode;
      if (status == 401) throw ApiException('Accès refusé (401)');
      if (status == 404)
        throw ApiException('Endpoint introuvable (404) : $path');
      // e.message can be null for redirect/unknown errors — provide a useful message
      final msg = e.message ?? e.type.name;
      throw ApiException('Erreur réseau : $msg');
    }
  }
}

// ─── Auth Injector Interceptor ───────────────────────────────────────────────
// Ensures the Authorization header is present on EVERY request, including
// those automatically generated by Dio when following 3xx redirects.
// Dio's default redirect handler re-uses the original options so headers
// should be preserved, but this interceptor is a safety net.
class _AuthInjectorInterceptor extends Interceptor {
  final ApiService _service;
  _AuthInjectorInterceptor(this._service);

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    // If Authorization header is missing (can happen on redirect re-issue),
    // re-inject it from the service's current credentials.
    if (!options.headers.containsKey('Authorization') ||
        options.headers['Authorization'] == null) {
      final login = _service._login;
      final password = _service._password;
      if (login != null && password != null) {
        options.headers['Authorization'] =
            'Basic ${base64Encode(utf8.encode('$login:$password'))}';
        debugPrint('[AuthInjector] Re-injected Authorization header');
      }
    }
    handler.next(options);
  }
}

/// A single coherence rule downloaded from data_rules.php for offline evaluation.
///
/// The server returns (rule, associated-rule, critere, message) pairs.
/// Stored flat in the SQLite coherence_rules table keyed by context
/// (id_camp, id_qst, id_etab, id_filter).
class CoherenceRule {
  final String idCamp;
  final String idQst;
  final String idEtab;
  final String? idFilter;
  final int    idRegle;
  final String libRegle;
  final String sqlRegle;
  final int    idAssoc;
  final int    idRegleAssoc;
  final String libRegleAssoc;
  final String sqlAssoc;
  final String critere;
  final String message;
  final String fetchedAt;

  const CoherenceRule({
    required this.idCamp,
    required this.idQst,
    required this.idEtab,
    this.idFilter,
    required this.idRegle,
    required this.libRegle,
    required this.sqlRegle,
    required this.idAssoc,
    required this.idRegleAssoc,
    required this.libRegleAssoc,
    required this.sqlAssoc,
    required this.critere,
    required this.message,
    required this.fetchedAt,
  });
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;
}

/// Represents a coherence control violation returned by data_controle.php.
///
/// Server source: controle_theme_batch.class.php → tab_regles_theme_assoc_not_ok
/// JSON fields:
///   id_regle       → rule ID from DICO_REGLE_THEME
///   id_regle_assoc → associated rule ID
///   message        → human-readable violation message (translated by server)
///   regle_1        → label of the first data value
///   regle_2        → label of the associated data value
///   critere        → comparison operator (>, <, =, etc.)
class CoherenceError {
  final int idRegle;
  final int idRegleAssoc;
  final String message;
  final String regle1;
  final String regle2;
  final String critere;

  const CoherenceError({
    required this.idRegle,
    required this.idRegleAssoc,
    required this.message,
    this.regle1 = '',
    this.regle2 = '',
    this.critere = '',
  });

  factory CoherenceError.fromJson(Map<String, dynamic> json) {
    return CoherenceError(
      idRegle:       int.tryParse(json['id_regle']?.toString()       ?? '0') ?? 0,
      idRegleAssoc:  int.tryParse(json['id_regle_assoc']?.toString() ?? '0') ?? 0,
      message:       (json['message']  ?? '').toString(),
      regle1:        (json['regle_1']  ?? '').toString(),
      regle2:        (json['regle_2']  ?? '').toString(),
      critere:       (json['critere']  ?? '').toString(),
    );
  }
}

