/// ApiService — HTTP client for all StatEduc server endpoints.
///
/// All endpoints verified against original JS source:
///   charge_camp.js  — 9 download steps
///   page_etab.js    — save + reload
///   page_new_camp.js — available campaigns
///
/// Server response envelope: { se_status: 200, se_data: [...] }
///
/// CRITICAL ENDPOINT DISTINCTION (verified in charge_camp.js):
///   reg_camp uses currentUser.LOGIN  (not id!)
///   ALL other endpoints use currentUser.ID

import 'dart:convert';
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
    _dio = Dio(BaseOptions(
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 120),
      sendTimeout:    const Duration(seconds: 60),
    ));
  }

  void configure(String serverUrl, String login, String password) {
    _serverUrl = serverUrl;
    _login     = login;
    _password  = password;
    _dio.options.baseUrl = serverUrl;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  void updateCredentials(String login, String password) {
    _login    = login;
    _password = password;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  String? get serverUrl => _serverUrl;
  String? get login     => _login;

  // ═══════════════════════════════════════════════════════════════════════════
  // AUTH
  // ═══════════════════════════════════════════════════════════════════════════

  /// Authenticate user.
  /// GET /user_ident.php/user/{login}/{password}
  Future<User?> authenticate(
      String serverUrl, String login, String password) async {
    configure(serverUrl, login, password);
    try {
      final response = await _dio.get('/user_ident.php/user/$login/$password');
      final data = response.data;
      if (data is Map && data['se_status'] == 200) {
        final userData = data['se_data'];
        if (userData != null && data['se_message'] != 'log_ko') {
          return User.fromJson(userData is String
              ? json.decode(userData)
              : userData as Map<String, dynamic>);
        }
      }
      return null;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) throw ApiException('Serveur introuvable');
      rethrow;
    }
  }

  /// Logout.
  /// GET /user_ident.php/logout/xxxx/xxxx
  Future<void> logout() async {
    try {
      await _dio.get('/user_ident.php/logout/xxxx/xxxx');
    } catch (_) {}
  }

  /// Test if session is still valid.
  /// GET /user_ident.php/user_test_login/
  Future<bool> testLogin() async {
    try {
      final response = await _dio.get('/user_ident.php/user_test_login/');
      final data = response.data;
      return data is Map && data['se_status'] == 200;
    } catch (_) {
      return false;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CAMPAIGNS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Get available campaigns for user.
  /// GET /user_camp.php/new_camp/{userId}/1  ← uses userId (id)
  /// From page_new_camp.js: getDataFromServer('/user_camp.php/new_camp/', currentUser.id+'/1', ...)
  Future<List<Campaign>> getAvailableCampaigns(String userId) async {
    final data = await _get('/user_camp.php/new_camp/$userId/1');
    return (data as List).map((c) => Campaign.fromJson(c as Map<String, dynamic>)).toList();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CAMPAIGN DATA DOWNLOAD (charge_camp.js — 9 sequential steps)
  // ═══════════════════════════════════════════════════════════════════════════

  /// Step 1 — Get administrative entities (regroups).
  /// GET /user_camp.php/reg_camp/{LOGIN}/{campId}/1
  /// ⚠ USES LOGIN not userId! (from charge_camp.js get_regroups: currentUser.login+'/' ...)
  Future<List<Regroup>> getRegroups(String login, String campId) async {
    final data = await _get('/user_camp.php/reg_camp/$login/$campId/1');
    return (data as List).map((r) => Regroup.fromJson(r as Map<String, dynamic>)).toList();
  }

  /// Step 2 — Get regroup types.
  /// GET /user_camp.php/typ_reg_camp/{userId}/{campId}/{typeRegroups}
  /// ⚠ Passes campaign.typeRegroups (CSV string) as path param!
  /// From charge_camp.js: currentUser.id +'/'+ id_camp +'/'+ this.currCamp.typeregroups
  Future<List<RegroupType>> getRegroupTypes(
      String userId, String campId, String typeRegroups) async {
    final data = await _get(
        '/user_camp.php/typ_reg_camp/$userId/$campId/$typeRegroups');
    return (data as List).map((t) => RegroupType.fromJson(t as Map<String, dynamic>)).toList();
  }

  /// Step 3 — Get school statuses.
  /// GET /user_camp.php/etabs_status/  ← NO params!
  /// From charge_camp.js get_status: getDataFromServer('/user_camp.php/etabs_status/', '', ...)
  Future<List<SchoolStatus>> getSchoolStatuses() async {
    final data = await _get('/user_camp.php/etabs_status/');
    return (data as List).map((s) => SchoolStatus.fromJson(s as Map<String, dynamic>)).toList();
  }

  /// Step 4 — Get schools (établissements).
  /// GET /user_camp.php/etabs_camp/{userId}/{campId}/1
  /// From charge_camp.js get_etabs: currentUser.id +'/'+ id_camp +'/1'
  Future<List<School>> getSchools(String userId, String campId) async {
    final data = await _get('/user_camp.php/etabs_camp/$userId/$campId/1');
    return (data as List).map((s) => School.fromJson(s as Map<String, dynamic>)).toList();
  }

  /// Step 5 — Get localisations.
  /// GET /user_camp.php/locs_camp/{userId}/{campId}
  /// From charge_camp.js get_locs: currentUser.id +'/'+ id_camp
  /// Response: array of raw items; each item may have multiple etabs → expand.
  Future<List<Localisation>> getLocalisations(
      String userId, String campId) async {
    final data = await _get('/user_camp.php/locs_camp/$userId/$campId');
    final result = <Localisation>[];
    for (final raw in (data as List)) {
      result.addAll(localisationsFromRawJson(raw as Map<String, dynamic>));
    }
    return result;
  }

  /// Step 6 — Get education systems.
  /// GET /user_camp.php/sys_camp/{userId}/{campId}
  /// From charge_camp.js get_systems: currentUser.id +'/'+ id_camp
  Future<List<EducationSystem>> getEducationSystems(
      String userId, String campId) async {
    final data = await _get('/user_camp.php/sys_camp/$userId/$campId');
    return (data as List).map((s) => EducationSystem.fromJson(s as Map<String, dynamic>)).toList();
  }

  /// Step 7 — Get questions/forms for a system.
  /// GET /data_camp.php/theme_camp/{campId}/{sysId}/eng
  /// From charge_camp.js get_qsts: id_camp+'/'+id_sys+'/eng'
  Future<List<Question>> getQuestions(String campId, String sysId) async {
    final data = await _get('/data_camp.php/theme_camp/$campId/$sysId/eng');
    return (data as List).map((q) => Question.fromJson(q as Map<String, dynamic>)).toList();
  }

  /// Step 8 — Get form HTML (TWO-STEP fetch).
  ///
  /// Step 8a: GET /data_camp.php/html_theme_camp/{campId}/{qstId}/eng
  ///   → returns a URL string (the se_data field is the URL)
  ///
  /// Step 8b: GET {url} with Authorization header
  ///   → returns raw HTML content
  ///
  /// From charge_camp.js addQsts():
  ///   getDataFromServer('/data_camp.php/html_theme_camp/', id_camp +'/'+ qst.id +'/eng', addQstHtmlFuncOk)
  ///   → addQstHtml receives 'html' (which is actually a URL)
  ///   → then $.ajax({ url: html, headers: { Authorization: ... } })
  Future<String> getFormHtml(String campId, String qstId) async {
    try {
      // Step 8a: Get the URL
      final urlResponse = await _dio.get(
        '/data_camp.php/html_theme_camp/$campId/$qstId/eng',
      );
      final envelope = urlResponse.data;
      String htmlUrl;
      if (envelope is Map && envelope.containsKey('se_data')) {
        htmlUrl = envelope['se_data'].toString();
      } else {
        htmlUrl = envelope.toString();
      }
      htmlUrl = htmlUrl.trim();

      // Step 8b: Fetch the actual HTML from the returned URL with auth
      final htmlResponse = await _dio.get(
        htmlUrl,
        options: Options(
          responseType: ResponseType.plain,
          headers: {
            'Authorization': _dio.options.headers['Authorization'],
          },
        ),
      );
      return htmlResponse.data.toString();
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) throw ApiException('Formulaire introuvable');
      rethrow;
    }
  }

  /// Step 9 — Get validation rules for a form.
  /// GET /data_camp.php/regle_theme_camp/{qstId}/{sysId}
  /// From charge_camp.js getRules: id_qst +'/'+ id_sys
  Future<List<ValidationRule>> getValidationRules(
      String qstId, String sysId) async {
    final data = await _get('/data_camp.php/regle_theme_camp/$qstId/$sysId');
    return (data as List).map((r) => ValidationRule.fromJson(r as Map<String, dynamic>)).toList();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // DATA ENTRY — SAVE
  // ═══════════════════════════════════════════════════════════════════════════

  /// Save collected data to server.
  ///
  /// POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
  /// Body: application/x-www-form-urlencoded
  ///
  /// From page_etab.js saveOneQstOnServer() / charge_camp.js postDataToServer():
  ///   postDataToServer('/data_save.php', '/theme_save/'
  ///     + stmChargeCamp.currUser.login +'/'+ stmCamp.id +'/'+ stmPageEtab.currSys.id
  ///     +'/'+ idQst +'/'+ idEtab +'/'+ filter, themeData, ...)
  ///
  /// LOC_REG_0 injection: for the FIRST question, if LOC_REG_0 not already in formData,
  ///   append &LOC_REG_0={etabRegroupId}
  ///   From page_etab.js getPageDataToSend():
  ///     if (stmCamp.getQsts()[0].getId() == idQst && dataToSend.indexOf('LOC_REG_') < 0)
  ///       dataToSend += '&LOC_REG_0=' + stmPageEtab.etab.idRegroup;
  ///
  /// Radio field format in POST body: fieldName=optionId (not fieldName#optionId=1)
  ///   From page_etab.js getPageDataToSend():
  ///     if (type == "radio") { name = key.substr(0, key.indexOf("#")); id = key.substr(indexOf+1); }
  ///     dataToSend += '&' + name + '=' + id;
  Future<bool> saveData({
    required String login,     // currentUser.login (NOT id!)
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,   // null → sent as '0' in URL
    required Map<String, String> formData,
    String? etabRegroupId,     // for LOC_REG_0 injection (= school.idRegroup)
    bool isFirstQuestion = false,
  }) async {
    final filterParam = filter ?? '0';
    final url = '/data_save.php/theme_save/$login/$campId/$sysId/$qstId/$etabId/$filterParam/0';

    // Build form-urlencoded body from formData map
    // Mirrors getPageDataToSend() in page_etab.js
    final bodyParts = <String>[];
    formData.forEach((key, value) {
      if (key.contains('#')) {
        // Radio field: stored as 'fieldName#optionId' with value '1'
        // POST as: fieldName=optionId
        if (value == '1') {
          final name    = key.substring(0, key.indexOf('#'));
          final optId   = key.substring(key.indexOf('#') + 1);
          bodyParts.add('$name=${Uri.encodeQueryComponent(optId)}');
        }
      } else {
        // Text/select/checkbox field
        // Slashes are encoded as _slh_ per page_etab.js getPageDataToSend()
        final encoded = value.replaceAll('/', '_slh_');
        bodyParts.add('$key=${Uri.encodeQueryComponent(encoded)}');
      }
    });

    // LOC_REG_0 injection: first question + not already in formData
    if (isFirstQuestion && etabRegroupId != null) {
      final bodyStr = bodyParts.join('&');
      if (!bodyStr.contains('LOC_REG_')) {
        bodyParts.add('LOC_REG_0=${Uri.encodeQueryComponent(etabRegroupId)}');
      }
    }

    // Add pagination markers (from page_etab.js saveOneQstOnServer):
    //   if (currNumPage == totalPages) themeData += '&switch_theme_id=&save_and_prev=&save_and_next='
    //   else                          themeData += '&switch_theme_id=&save_and_prev=0&save_and_next=0'
    bodyParts.add('switch_theme_id=');
    bodyParts.add('save_and_prev=');
    bodyParts.add('save_and_next=');

    final body = bodyParts.join('&');

    try {
      final response = await _dio.post(
        url,
        data: body,
        options: Options(
          contentType: 'application/x-www-form-urlencoded',
          responseType: ResponseType.plain,
        ),
      );
      final result = json.decode(response.data.toString());
      if (result['se_status'] == 400) return false;
      return result['se_data'] == 'OKSAVE';
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      throw ApiException('Erreur envoi données: ${e.message}');
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // DATA ENTRY — RELOAD
  // ═══════════════════════════════════════════════════════════════════════════

  /// Reload data from server for a school+form.
  ///
  /// GET /data_reload.php/theme_data/{login}/{sysId}/{qstId}/{campId}/{etabId}/{filter}
  ///
  /// From page_etab.js reloadOneQstFromServer():
  ///   getFormDataFromServer('/data_reload.php', '/theme_data/'
  ///     + stmChargeCamp.currUser.login +'/'+ stmPageEtab.currSys.id +'/'
  ///     + idQst +'/'+ stmCamp.id +'/'+ idEtab +'/'+ filter, ...)
  ///
  /// Response: raw JSON (NOT wrapped in se_envelope) — a map of:
  ///   { fieldName: [value, type], ... }
  ///   e.g. { "FIELD1": ["42", "text"], "RADIO_FLD": ["opt2", "radio"] }
  ///
  /// From page_etab.js reloadDataCallback():
  ///   type = formElt[1]; value = formElt[0];
  ///   if (type == "radio") { value = 1; tagName += "#" + formElt[0]; }
  ///   → radio field stored as 'fieldName#optionId': 1
  ///   → skip keys starting with 'DELETE_'
  ///
  /// Returns parsed field map ready to store in DB, or null on error.
  Future<Map<String, String>?> reloadData({
    required String login,
    required String sysId,
    required String qstId,
    required String campId,
    required String etabId,
    required String? filter,
  }) async {
    final filterParam = filter ?? '0';
    final url =
        '/data_reload.php/theme_data/$login/$sysId/$qstId/$campId/$etabId/$filterParam';
    try {
      final response = await _dio.get(
        url,
        options: Options(responseType: ResponseType.plain),
      );
      final rawJson = json.decode(response.data.toString());

      // Check for error envelope
      if (rawJson is Map && rawJson['se_status'] == 400) return null;

      // Parse response: { fieldName: [value, type], ... }
      // Mirrors reloadDataCallback() in page_etab.js
      final Map<String, String> fields = {};
      final Map<String, dynamic> dataMap =
          (rawJson is Map) ? rawJson.cast<String, dynamic>() : {};

      dataMap.forEach((key, formElt) {
        if (key.startsWith('DELETE_')) return;
        if (formElt is List && formElt.isNotEmpty) {
          final type  = formElt.length > 1 ? formElt[1].toString() : 'text';
          final value = formElt[0].toString();
          if (type == 'radio') {
            // Radio: stored as fieldName#optionId = '1'
            fields['$key#$value'] = '1';
          } else {
            fields[key] = value;
          }
        }
      });
      return fields;
    } catch (e) {
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRIVATE HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Generic GET — parses se_data from response envelope.
  Future<dynamic> _get(String path) async {
    try {
      final response = await _dio.get(path);
      final data = response.data;
      if (data is Map) {
        // Standard envelope: { se_status, se_data }
        return data['se_data'] ?? [];
      }
      return data ?? [];
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) throw ApiException('Endpoint introuvable');
      throw ApiException('Erreur réseau: ${e.message}');
    }
  }
}

class ApiException implements Exception {
  final String message;
  ApiException(this.message);

  @override
  String toString() => message;
}
