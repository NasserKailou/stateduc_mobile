import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'api_service.dart';
import 'database_service.dart';
import '../models/user.dart';

/// AuthService — manages PIN-based local authentication and server credentials.
///
/// Security improvements over the original app:
///   Original: PIN stored in localStorage as plain text (stm_User.code)
///   Flutter:  PIN stored in flutter_secure_storage (Keystore/Keychain)
///
///   Original: HTTP Basic credentials stored in sessionStorage
///   Flutter:  Credentials stored in flutter_secure_storage and sent via
///             encrypted Dio interceptor
///
/// Keys used in secure storage:
///   auth_pin          — local unlock PIN (4–8 chars)
///   auth_security_q   — security question text
///   auth_security_a   — security answer (case-insensitive stored lowercased)
///   auth_server_url   — last used server URL (also in DB settings for persistence)
///   auth_login        — last authenticated login
///   auth_password     — last authenticated password (kept for offline re-auth)
///   auth_user_id      — server user ID returned by authenticate()
///   auth_user_name    — display name
class AuthService {
  static final AuthService _instance = AuthService._internal();
  factory AuthService() => _instance;
  AuthService._internal();

  static const _storage = FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  );
  final _api = ApiService();
  final _db = DatabaseService();

  // ─── Key constants ─────────────────────────────────────────────────────────
  static const _kPin = 'auth_pin';
  static const _kSecurityQ = 'auth_security_q';
  static const _kSecurityA = 'auth_security_a';
  static const _kServerUrl = 'auth_server_url';
  static const _kLogin = 'auth_login';
  static const _kPassword = 'auth_password';
  static const _kUserId = 'auth_user_id';
  static const _kUserName = 'auth_user_name';

  // ═══════════════════════════════════════════════════════════════════════════
  // PIN MANAGEMENT
  // ═══════════════════════════════════════════════════════════════════════════

  /// Returns true if a PIN has already been set (i.e., app has been configured).
  Future<bool> hasPinConfigured() async {
    final pin = await _storage.read(key: _kPin);
    return pin != null && pin.isNotEmpty;
  }

  /// Sets the PIN for the first time (onboarding flow).
  Future<void> setPin(String pin) async {
    assert(pin.length >= 4 && pin.length <= 8,
        'PIN must be between 4 and 8 characters');
    await _storage.write(key: _kPin, value: pin);
  }

  /// Verifies the supplied PIN against stored value.
  /// Returns true on match.
  Future<bool> verifyPin(String pin) async {
    final stored = await _storage.read(key: _kPin);
    return stored == pin;
  }

  /// Changes the PIN after verifying the old one.
  /// Throws [AuthException] if the old PIN is incorrect.
  Future<void> changePin(String oldPin, String newPin) async {
    if (!await verifyPin(oldPin)) {
      throw AuthException('PIN actuel incorrect');
    }
    if (newPin.length < 4 || newPin.length > 8) {
      throw AuthException('Le nouveau PIN doit comporter entre 4 et 8 chiffres');
    }
    await _storage.write(key: _kPin, value: newPin);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SECURITY QUESTION
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> setSecurityQuestion(
      String question, String answer) async {
    await _storage.write(key: _kSecurityQ, value: question);
    await _storage.write(key: _kSecurityA, value: answer.toLowerCase().trim());
  }

  Future<String?> getSecurityQuestion() async {
    return _storage.read(key: _kSecurityQ);
  }

  /// Resets the PIN using the security answer.
  /// Returns the new temporary PIN, or throws [AuthException].
  Future<void> resetPinWithSecurityAnswer(
      String answer, String newPin) async {
    final stored = await _storage.read(key: _kSecurityA);
    if (stored == null || stored.isEmpty) {
      throw AuthException('Aucune question de sécurité définie');
    }
    if (stored != answer.toLowerCase().trim()) {
      throw AuthException('Réponse de sécurité incorrecte');
    }
    if (newPin.length < 4 || newPin.length > 8) {
      throw AuthException('Le nouveau PIN doit comporter entre 4 et 8 chiffres');
    }
    await _storage.write(key: _kPin, value: newPin);
  }

  bool get hasSecurityQuestion => _securityQuestionLoaded;
  bool _securityQuestionLoaded = false;

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER URL
  // ═══════════════════════════════════════════════════════════════════════════

  Future<String?> getServerUrl() async {
    // Prefer secure storage; fall back to DB settings for backwards-compat.
    final url = await _storage.read(key: _kServerUrl);
    if (url != null && url.isNotEmpty) return url;
    return _db.getSetting('server_url');
  }

  Future<void> setServerUrl(String url) async {
    await _storage.write(key: _kServerUrl, value: url);
    await _db.setSetting('server_url', url);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER AUTHENTICATION
  // ═══════════════════════════════════════════════════════════════════════════

  /// Authenticates against the server at [serverUrl] with [login]/[password].
  /// On success, stores credentials securely and configures the API service.
  /// Returns the [User] object from the server.
  /// Throws [AuthException] on failure.
  Future<User> login({
    required String serverUrl,
    required String login,
    required String password,
  }) async {
    // Normalise l'URL avant tout (ajoute http:// si nécessaire)
    final normalizedUrl = ApiService.normalizeServerUrl(serverUrl);
    debugPrint('[AuthService] login → url=$normalizedUrl login=$login');
    try {
      final user = await _api.authenticate(normalizedUrl, login, password);
      if (user == null) {
        throw AuthException(
            'Identifiants invalides.\nVérifiez votre identifiant et mot de passe.');
      }
      // Persist credentials for offline re-authentication
      await _storage.write(key: _kServerUrl, value: normalizedUrl);
      await _storage.write(key: _kLogin, value: login);
      await _storage.write(key: _kPassword, value: password);
      await _storage.write(key: _kUserId, value: user.idUser);
      await _storage.write(key: _kUserName, value: user.nomUser);
      // Persist server URL in DB too (use normalized URL)
      await _db.setSetting('server_url', normalizedUrl);
      await _db.setSetting('user_id', user.idUser);
      await _db.setSetting('user_name', user.nomUser);
      _securityQuestionLoaded = true;
      return user;
    } on AuthException {
      rethrow;
    } on ApiException catch (e) {
      throw AuthException(e.message);
    } catch (e) {
      debugPrint('[AuthService] login error: $e');
      throw AuthException('Erreur de connexion : ${e.toString()}');
    }
  }

  /// Returns the currently stored User info (without hitting the server).
  /// Used to restore session after app restart / PIN unlock.
  Future<User?> getStoredUser() async {
    final userId = await _storage.read(key: _kUserId);
    final userName = await _storage.read(key: _kUserName);
    final login = await _storage.read(key: _kLogin);
    final serverUrl = await _storage.read(key: _kServerUrl);
    if (userId == null || login == null || serverUrl == null) return null;
    // Re-configure API service with stored credentials
    final password = await _storage.read(key: _kPassword) ?? '';
    _api.configure(serverUrl, login, password);
    return User(
      idUser: userId!,
      nomUser: userName ?? login!,
      login: login!,
    );
  }

  /// Returns true if stored credentials exist (user has logged in before).
  Future<bool> hasStoredCredentials() async {
    final userId = await _storage.read(key: _kUserId);
    return userId != null && userId.isNotEmpty;
  }

  /// Logs out: clears server session credentials but keeps PIN and server URL.
  /// This mirrors the original deconnect() in users.js.
  Future<void> logout() async {
    try {
      await _api.logout();
    } catch (_) {
      // Best effort — ignore network errors on logout
    }
    await _storage.delete(key: _kUserId);
    await _storage.delete(key: _kUserName);
    await _storage.delete(key: _kPassword);
    await _db.deleteSetting('user_id');
    await _db.deleteSetting('user_name');
  }

  /// Full factory reset: removes ALL stored data including PIN.
  /// Used for testing / data reset.
  Future<void> fullReset() async {
    await _storage.deleteAll();
    await _db.clearAll();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // OFFLINE PIN-ONLY UNLOCK
  // ═══════════════════════════════════════════════════════════════════════════

  /// Re-configures the API service from stored credentials (for offline unlock).
  /// Called after PIN verification when network is unavailable.
  Future<bool> restoreApiSession() async {
    final serverUrl = await _storage.read(key: _kServerUrl);
    final login = await _storage.read(key: _kLogin);
    final password = await _storage.read(key: _kPassword);
    if (serverUrl == null || login == null || password == null) return false;
    _api.configure(serverUrl, login, password);
    return true;
  }

  /// Returns stored login for display in settings.
  Future<String?> getStoredLogin() async {
    return _storage.read(key: _kLogin);
  }
}

// ─── Exception ─────────────────────────────────────────────────────────────
class AuthException implements Exception {
  final String message;
  AuthException(this.message);

  @override
  String toString() => message;
}
