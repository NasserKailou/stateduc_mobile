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
import 'package:flutter/foundation.dart';
import 'package:dio/dio.dart';
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

  ApiService() {
    _dio = Dio(
      BaseOptions(
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 120),
        sendTimeout: const Duration(seconds: 60),
        // CRITICAL: disable auto-redirect so Dio doesn't silently follow
        // 301/302 redirects and lose the Authorization header in the process.
        followRedirects: false,
        // Accept all status codes so we can handle them manually
        validateStatus: (status) => status != null && status < 500,
      ),
    );
    // Add request/response logger interceptor for debugging
    _dio.interceptors.add(_buildLogInterceptor());
  }

  // ─── Log Interceptor ────────────────────────────────────────────────────────

  InterceptorsWrapper _buildLogInterceptor() {
    return InterceptorsWrapper(
      onRequest: (options, handler) {
        debugPrint('[Dio→] ${options.method} ${options.uri}');
        debugPrint('[Dio→] Headers: ${options.headers}');
        if (options.data != null) {
          final dataStr = options.data.toString();
          debugPrint('[Dio→] Body: ${dataStr.length > 200 ? dataStr.substring(0, 200) + "…" : dataStr}');
        }
        handler.next(options);
      },
      onResponse: (response, handler) {
        debugPrint('[Dio←] ${response.statusCode} ${response.requestOptions.uri}');
        final bodyStr = response.data?.toString() ?? '';
        debugPrint('[Dio←] Body: ${bodyStr.length > 300 ? bodyStr.substring(0, 300) + "…" : bodyStr}');
        handler.next(response);
      },
      onError: (DioException e, handler) {
        debugPrint('[Dio✗] ${e.type} ${e.requestOptions.uri}');
        debugPrint('[Dio✗] message=${e.message}');
        if (e.response != null) {
          debugPrint('[Dio✗] status=${e.response?.statusCode} body=${e.response?.data}');
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
    debugPrint('[ApiService] Authorization=Basic ${base64Encode(utf8.encode('$login:$password'))}');
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

      // Handle 3xx redirects explicitly (followRedirects: false)
      if (statusCode >= 300 && statusCode < 400) {
        final location = response.headers.value('location') ?? '';
        debugPrint('[ApiService] authenticate: redirect $statusCode → $location');
        // A redirect here usually means Apache/Nginx auth layer — re-try with
        // the full redirect URL while preserving our Auth header.
        if (location.isNotEmpty) {
          return _authenticateRedirect(location, login, password);
        }
        throw ApiException('Redirection inattendue ($statusCode). Vérifiez l\'URL serveur.');
      }

      // 401 at HTTP level = Apache/Nginx Basic Auth protecting the endpoint
      // This is DIFFERENT from PHP app-level auth (se_message: log_ko)
      if (statusCode == 401) {
        debugPrint('[ApiService] authenticate: HTTP 401 — Apache/Nginx layer rejected credentials');
        throw ApiException(
          'Accès refusé (401).\n'
          'Le serveur a une protection HTTP (Apache/Nginx).\n'
          'Vérifiez vos identifiants ou contactez l\'administrateur.'
        );
      }

      if (statusCode == 404) {
        throw ApiException('Serveur introuvable (404). Vérifiez l\'URL : $_serverUrl');
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
        debugPrint('[ApiService] authenticate: log_ko → identifiants invalides (PHP level)');
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
      debugPrint('[ApiService] authenticate DioException: type=${e.type} status=${e.response?.statusCode} msg=${e.message}');
      debugPrint('[ApiService] authenticate DioException body=${e.response?.data}');
      if (e.type == DioExceptionType.connectionTimeout ||
          e.type == DioExceptionType.receiveTimeout ||
          e.type == DioExceptionType.sendTimeout) {
        throw ApiException('Serveur injoignable (timeout). Vérifiez l\'URL et le réseau.');
      }
      if (e.type == DioExceptionType.connectionError) {
        throw ApiException('Impossible de joindre le serveur. Vérifiez l\'URL : $_serverUrl');
      }
      if (e.response?.statusCode == 401) {
        throw ApiException(
          'Accès refusé (401).\n'
          'Le serveur exige une authentification HTTP supplémentaire.\n'
          'Contactez l\'administrateur du serveur.'
        );
      }
      if (e.response?.statusCode == 404) throw ApiException('Serveur introuvable (404). Vérifiez l\'URL.');
      throw ApiException('Erreur réseau : ${e.message ?? e.type.name}');
    }
  }

  /// Follows a redirect for authenticate(), preserving the Authorization header.
  Future<User?> _authenticateRedirect(
      String redirectUrl, String login, String password) async {
    debugPrint('[ApiService] _authenticateRedirect → $redirectUrl');
    try {
      final redirectDio = Dio(BaseOptions(
        connectTimeout: const Duration(seconds: 30),
        receiveTimeout: const Duration(seconds: 30),
        followRedirects: false,
        validateStatus: (s) => s != null && s < 500,
        headers: {
          'Authorization': 'Basic ${base64Encode(utf8.encode('$login:$password'))}',
          'User-Agent': 'Mozilla/5.0 (Linux; Android 10) StatEduc/1.0',
          'Accept': 'application/json, text/plain, */*',
        },
      ));
      final r = await redirectDio.get(redirectUrl,
          options: Options(responseType: ResponseType.plain));
      final rawBody = r.data?.toString().trim() ?? '';
      if (rawBody.isEmpty) return null;
      final parsed = json.decode(rawBody);
      if (parsed is Map) {
        if (parsed['se_message'] == 'log_ko') return null;
        if (parsed['se_status'] == 200) {
          final ud = parsed['se_data'];
          if (ud != null) {
            final userMap = ud is String
                ? json.decode(ud) as Map<String, dynamic>
                : ud as Map<String, dynamic>;
            return User.fromJson(userMap);
          }
        }
      }
      return null;
    } catch (e) {
      debugPrint('[ApiService] _authenticateRedirect error: $e');
      return null;
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
    return [];}

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
    final data = await _get(
        'user_camp.php/typ_reg_camp/$userId/$campId/$typeRegroups');
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
    final data =
        await _get('data_camp.php/theme_camp/$campId/$sysId/eng');
    if (data is List) {
      return data.map((q) => Question.fromJson(q)).toList();
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

      // Step 2: fetch the HTML content with Basic Auth
      final step2 = await Dio().get(
        htmlUrl,
        options: Options(
          responseType: ResponseType.plain,
          headers: {
            'Authorization': _dio.options.headers['Authorization'],
          },
        ),
      );
      return step2.data.toString();
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
    final data =
        await _get('data_camp.php/regle_theme_camp/$qstId/$sysId');
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
    required String login,       // currentUser.login
    required String campId,      // stmCamp.id
    required String sysId,       // stmPageEtab.currSys.id
    required String qstId,       // question id
    required String etabId,      // school id
    required String? filter,     // filter period id or null
    required Map<String, dynamic> formData,  // dynamic to accept both String and server data
    String? etabRegroupId,       // school.idRegroup (for LOC_REG_0 in q1)
    bool isFirstQuestion = false, // true when sending question[0] → includes LOC_REG_0
    bool isLastPage = true,
  }) async {
    final filterParam = (filter == null || filter.isEmpty) ? '0' : filter;
    // Build form body like getPageDataToSend()
    final bodyParts = <String>[];
    formData.forEach((key, value) {
      final strVal = value?.toString() ?? '';
      final encodedVal = strVal.replaceAll('/', '_slh_');
      bodyParts.add('$key=${Uri.encodeComponent(encodedVal)}');
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
      final response = await _dio.post(
        'data_save.php/theme_save/$login/$campId/$sysId/$qstId/$etabId/$filterParam/0',
        data: body,
        options: Options(
          contentType: 'application/x-www-form-urlencoded',
          responseType: ResponseType.plain,
        ),
      );
      final responseStr = response.data.toString();
      // Response is a JSON string
      final result = json.decode(responseStr);
      if (result is Map) {
        if (result['se_status'] == 400) {
          throw ApiException(
              result['se_data']?.toString() ?? 'Erreur serveur');
        }
        return result['se_data'] == 'OKSAVE';
      }
      return false;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Endpoint introuvable (vérifiez l\'URL serveur)');
      }
      throw ApiException('Erreur envoi données : ${e.message}');
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
    required String login,    // stmChargeCamp.currUser.login
    required String sysId,    // stmPageEtab.currSys.id
    required String qstId,    // question id
    required String campId,   // stmCamp.id
    required String etabId,   // school id
    required String? filter,  // filter period id or null
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
  // Mirrors: getDataFromServer(servSuffix, params, callBack) in charge_camp.js
  //   success callback receives response.se_data
  // ═══════════════════════════════════════════════════════════════════════════

  Future<dynamic> _get(String path) async {
    try {
      final response = await _dio.get(path);
      final statusCode = response.statusCode ?? 0;
      if (statusCode == 401) throw ApiException('Accès refusé');
      if (statusCode == 404) throw ApiException('Endpoint introuvable');
      final data = response.data;
      if (data is Map) {
        // Unwrap se_data envelope (always present in server responses)
        return data['se_data'] ?? data;
      }
      return data ?? [];
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) {
        throw ApiException('Endpoint introuvable');
      }
      throw ApiException('Erreur réseau : ${e.message}');
    }
  }
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;
}
