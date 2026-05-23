// API Service - handles all HTTP communication with the StatEduc server

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
    _dio = Dio(
      BaseOptions(
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 120),
        sendTimeout: const Duration(seconds: 60),
      ),
    );
  }

  void configure(String serverUrl, String login, String password) {
    _serverUrl = serverUrl;
    _login = login;
    _password = password;
    _dio.options.baseUrl = serverUrl;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  void updateCredentials(String login, String password) {
    _login = login;
    _password = password;
    _dio.options.headers['Authorization'] =
        'Basic ${base64Encode(utf8.encode('$login:$password'))}';
  }

  String? get serverUrl => _serverUrl;
  String? get login => _login;

  /// Authenticate user
  Future<User?> authenticate(String serverUrl, String login, String password) async {
    configure(serverUrl, login, password);
    try {
      final response = await _dio.get(
        '/user_ident.php/user/$login/$password',
      );
      final data = response.data;
      if (data is Map && data['se_status'] == 200) {
        final userData = data['se_data'];
        if (userData != null && data['se_message'] != 'log_ko') {
          return User.fromJson(userData is String ? json.decode(userData) : userData);
        }
      }
      return null;
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) throw ApiException('Serveur introuvable');
      rethrow;
    }
  }

  /// Logout user
  Future<void> logout() async {
    try {
      await _dio.get('/user_ident.php/logout/xxxx/xxxx');
    } catch (_) {}
  }

  /// Test if current session is still valid
  Future<bool> testLogin() async {
    try {
      final response = await _dio.get('/user_ident.php/user_test_login/');
      final data = response.data;
      return data is Map && data['se_status'] == 200;
    } catch (_) {
      return false;
    }
  }

  /// Get available campaigns for a user
  Future<List<Campaign>> getAvailableCampaigns(String userId) async {
    final response = await _get('/user_camp.php/new_camp/$userId/1');
    return (response as List).map((c) => Campaign.fromJson(c)).toList();
  }

  /// Get administrative entities for a campaign
  Future<List<Regroup>> getRegroups(String login, String campId) async {
    final response = await _get('/user_camp.php/reg_camp/$login/$campId/1');
    return (response as List).map((r) => Regroup.fromJson(r)).toList();
  }

  /// Get types of administrative entities
  Future<List<RegroupType>> getRegroupTypes(
      String userId, String campId, String typeRegroups) async {
    final response =
        await _get('/user_camp.php/typ_reg_camp/$userId/$campId/$typeRegroups');
    return (response as List).map((t) => RegroupType.fromJson(t)).toList();
  }

  /// Get school statuses
  Future<List<SchoolStatus>> getSchoolStatuses() async {
    final response = await _get('/user_camp.php/etabs_status/');
    return (response as List).map((s) => SchoolStatus.fromJson(s)).toList();
  }

  /// Get schools for a campaign
  Future<List<School>> getSchools(String userId, String campId) async {
    final response = await _get('/user_camp.php/etabs_camp/$userId/$campId/1');
    return (response as List).map((s) => School.fromJson(s)).toList();
  }

  /// Get localisations for a campaign
  Future<List<Localisation>> getLocalisations(
      String userId, String campId) async {
    final response = await _get('/user_camp.php/locs_camp/$userId/$campId');
    return (response as List).map((l) => Localisation.fromJson(l)).toList();
  }

  /// Get education systems for a campaign
  Future<List<EducationSystem>> getEducationSystems(
      String userId, String campId) async {
    final response = await _get('/user_camp.php/sys_camp/$userId/$campId');
    return (response as List).map((s) => EducationSystem.fromJson(s)).toList();
  }

  /// Get questionnaires/forms for a campaign and system
  Future<List<Question>> getQuestions(String campId, String sysId) async {
    final response =
        await _get('/data_camp.php/theme_camp/$campId/$sysId/eng');
    return (response as List).map((q) => Question.fromJson(q)).toList();
  }

  /// Get HTML content of a form
  Future<String> getFormHtml(String campId, String qstId) async {
    try {
      final response = await _dio.get(
        '/data_camp.php/html_theme_camp/$campId/$qstId/eng',
        options: Options(responseType: ResponseType.plain),
      );
      return response.data.toString();
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      if (e.response?.statusCode == 404) throw ApiException('Formulaire introuvable');
      rethrow;
    }
  }

  /// Get validation rules for a form
  Future<List<ValidationRule>> getValidationRules(
      String qstId, String sysId) async {
    final response =
        await _get('/data_camp.php/regle_theme_camp/$qstId/$sysId');
    return (response as List).map((r) => ValidationRule.fromJson(r)).toList();
  }

  /// Save collected data to server
  Future<bool> saveData({
    required String login,
    required String campId,
    required String sysId,
    required String qstId,
    required String etabId,
    required String? filter,
    required String formData,
  }) async {
    final filterParam = filter ?? '0';
    try {
      final response = await _dio.post(
        '/data_save.php/theme_save/$login/$campId/$sysId/$qstId/$etabId/$filterParam/0',
        data: formData,
        options: Options(
          contentType: 'application/x-www-form-urlencoded',
          responseType: ResponseType.plain,
        ),
      );
      final result = json.decode(response.data.toString());
      return result['se_status'] != 400 && result['se_data'] == 'OKSAVE';
    } on DioException catch (e) {
      if (e.response?.statusCode == 401) throw ApiException('Accès refusé');
      throw ApiException('Erreur envoi données: ${e.message}');
    }
  }

  /// Reload data from server for a school/form
  Future<Map<String, dynamic>?> reloadData({
    required String login,
    required String sysId,
    required String qstId,
    required String campId,
    required String etabId,
    required String? filter,
  }) async {
    final filterParam = filter ?? '0';
    try {
      final response = await _dio.get(
        '/data_reload.php/theme_data/$login/$sysId/$qstId/$campId/$etabId/$filterParam',
        options: Options(responseType: ResponseType.plain),
      );
      final result = json.decode(response.data.toString());
      if (result['se_status'] == 400) return null;
      return result;
    } catch (e) {
      return null;
    }
  }

  // Generic GET helper that parses se_data
  Future<dynamic> _get(String path) async {
    try {
      final response = await _dio.get(path);
      final data = response.data;
      if (data is Map && data['se_status'] == 200) {
        return data['se_data'];
      }
      if (data is Map) return data['se_data'] ?? [];
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
