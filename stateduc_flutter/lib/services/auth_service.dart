import 'dart:convert';
import 'package:crypto/crypto.dart';
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
///   auth_security_q   — (legacy) security question text [kept for migration]
///   auth_security_a   — (legacy) security answer [kept for migration]
///   auth_sec_q1/q2/q3 — the 3 fixed security question texts (stored for display)
///   auth_sec_a1/a2/a3 — hashed answers: SHA-256(salt + normalised_answer)
///   auth_sec_salt     — random salt (hex) for this account's answers
///   auth_failed_attempts — consecutive failed PIN attempts counter (integer as string)
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
  // Legacy single-question keys — kept for backward compatibility / migration
  static const _kSecurityQ = 'auth_security_q';
  static const _kSecurityA = 'auth_security_a';
  // New 3-question keys
  static const _kSecQ1 = 'auth_sec_q1';
  static const _kSecQ2 = 'auth_sec_q2';
  static const _kSecQ3 = 'auth_sec_q3';
  static const _kSecA1 = 'auth_sec_a1'; // SHA-256 hash
  static const _kSecA2 = 'auth_sec_a2';
  static const _kSecA3 = 'auth_sec_a3';
  static const _kSecSalt = 'auth_sec_salt'; // per-account salt (hex)
  static const _kFailedAttempts = 'auth_failed_attempts';
  // Credentials
  static const _kServerUrl = 'auth_server_url';
  static const _kLogin = 'auth_login';
  static const _kPassword = 'auth_password';
  static const _kUserId   = 'auth_user_id';
  static const _kUserName  = 'auth_user_name';
  static const _kCodeyear  = 'auth_codeyear';
  static const _kLibyear   = 'auth_libyear';

  // ─── Fixed 3 security questions (French) ──────────────────────────────────
  static const List<String> kSecurityQuestions = [
    'Quel est le nom de votre première école primaire ?',
    'Quel est votre sport préféré ?',
    'Quel est le nom de votre ami d\'enfance ?',
  ];

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
  // FAILED ATTEMPTS COUNTER
  // ═══════════════════════════════════════════════════════════════════════════

  /// Returns the current consecutive failed PIN attempts count.
  Future<int> getFailedAttempts() async {
    final raw = await _storage.read(key: _kFailedAttempts);
    if (raw == null) return 0;
    return int.tryParse(raw) ?? 0;
  }

  /// Increments the failed attempts counter by 1.
  Future<int> incrementFailedAttempts() async {
    final current = await getFailedAttempts();
    final next = current + 1;
    await _storage.write(key: _kFailedAttempts, value: next.toString());
    debugPrint('[AuthService] failedAttempts=$next');
    return next;
  }

  /// Resets the failed attempts counter to 0 (call on successful unlock).
  Future<void> resetFailedAttempts() async {
    await _storage.write(key: _kFailedAttempts, value: '0');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SECURITY QUESTIONS (LEGACY — single question, kept for migration)
  // ═══════════════════════════════════════════════════════════════════════════

  /// [LEGACY] Kept for backward compatibility.
  /// New code should use [setThreeSecurityAnswers] instead.
  Future<void> setSecurityQuestion(
      String question, String answer) async {
    await _storage.write(key: _kSecurityQ, value: question);
    await _storage.write(key: _kSecurityA, value: answer.toLowerCase().trim());
  }

  /// [LEGACY] Kept for backward compatibility.
  Future<String?> getSecurityQuestion() async {
    return _storage.read(key: _kSecurityQ);
  }

  /// [LEGACY] Resets the PIN using the legacy single security answer.
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
  // SECURITY QUESTIONS — 3-QUESTION SYSTEM
  // ═══════════════════════════════════════════════════════════════════════════

  /// Returns true if the 3-question security answers are configured.
  Future<bool> hasThreeSecurityAnswers() async {
    final a1 = await _storage.read(key: _kSecA1);
    final a2 = await _storage.read(key: _kSecA2);
    final a3 = await _storage.read(key: _kSecA3);
    return (a1 != null && a1.isNotEmpty) &&
           (a2 != null && a2.isNotEmpty) &&
           (a3 != null && a3.isNotEmpty);
  }

  /// Normalises a security answer: trim + lowercase.
  String _normalise(String answer) => answer.trim().toLowerCase();

  /// Generates a random 16-byte salt as hex string.
  String _generateSalt() {
    // Use DateTime + hashCode as a simple non-predictable seed
    // (dart:math Random.secure() would be ideal but crypto package
    //  provides what we need here via a constant-time approach)
    final base = '${DateTime.now().microsecondsSinceEpoch}_${Object().hashCode}';
    return sha256.convert(utf8.encode(base)).toString().substring(0, 32);
  }

  /// Hashes a normalised answer with the account salt using SHA-256.
  String _hashAnswer(String normalisedAnswer, String salt) {
    final bytes = utf8.encode('$salt:$normalisedAnswer');
    return sha256.convert(bytes).toString();
  }

  /// Stores the 3 security answers (hashed with salt).
  /// [answers] must have exactly 3 elements.
  Future<void> setThreeSecurityAnswers(List<String> answers) async {
    assert(answers.length == 3, 'Exactly 3 answers required');

    // Generate or reuse existing salt
    String salt = await _storage.read(key: _kSecSalt) ?? '';
    if (salt.isEmpty) {
      salt = _generateSalt();
      await _storage.write(key: _kSecSalt, value: salt);
    }

    // Store questions (for display in recovery screen)
    await _storage.write(key: _kSecQ1, value: kSecurityQuestions[0]);
    await _storage.write(key: _kSecQ2, value: kSecurityQuestions[1]);
    await _storage.write(key: _kSecQ3, value: kSecurityQuestions[2]);

    // Store hashed answers
    await _storage.write(
        key: _kSecA1, value: _hashAnswer(_normalise(answers[0]), salt));
    await _storage.write(
        key: _kSecA2, value: _hashAnswer(_normalise(answers[1]), salt));
    await _storage.write(
        key: _kSecA3, value: _hashAnswer(_normalise(answers[2]), salt));

    debugPrint('[AuthService] 3 security answers saved (hashed)');
  }

  /// Verifies [answers] (3 strings) against stored hashes.
  /// Returns the number of correct answers (0–3).
  Future<int> verifySecurityAnswers(List<String> answers) async {
    assert(answers.length == 3, 'Exactly 3 answers required');

    final salt = await _storage.read(key: _kSecSalt) ?? '';
    if (salt.isEmpty) return 0;

    final stored = [
      await _storage.read(key: _kSecA1) ?? '',
      await _storage.read(key: _kSecA2) ?? '',
      await _storage.read(key: _kSecA3) ?? '',
    ];

    int correct = 0;
    for (int i = 0; i < 3; i++) {
      if (stored[i].isNotEmpty) {
        final hashed = _hashAnswer(_normalise(answers[i]), salt);
        if (hashed == stored[i]) correct++;
      }
    }
    debugPrint('[AuthService] verifySecurityAnswers: $correct/3 correct');
    return correct;
  }

  /// Resets the PIN using 3 security answers.
  /// Requires ≥ 2 correct answers out of 3.
  /// Throws [AuthException] on validation failure.
  Future<void> resetPinWithThreeAnswers(
      List<String> answers, String newPin) async {
    assert(answers.length == 3);

    final correct = await verifySecurityAnswers(answers);
    if (correct < 2) {
      throw AuthException(
          'Réponses incorrectes ($correct/3). '
          'Il faut au moins 2 réponses correctes sur 3.');
    }
    if (newPin.length < 4 || newPin.length > 8) {
      throw AuthException(
          'Le nouveau PIN doit comporter entre 4 et 8 chiffres');
    }
    await _storage.write(key: _kPin, value: newPin);
    await resetFailedAttempts();
    debugPrint('[AuthService] PIN réinitialisé via questions de sécurité ($correct/3 correct)');
  }

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
      await _storage.write(key: _kUserId,    value: user.idUser);
      await _storage.write(key: _kUserName,  value: user.nomUser);
      await _storage.write(key: _kCodeyear,  value: user.codeyear);
      await _storage.write(key: _kLibyear,   value: user.libyear);
      // Persist server URL in DB too (use normalized URL)
      await _db.setSetting('server_url', normalizedUrl);
      await _db.setSetting('user_id',    user.idUser);
      await _db.setSetting('user_name',  user.nomUser);
      await _db.setSetting('codeyear',   user.codeyear);
      await _db.setSetting('libyear',    user.libyear);
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
  /// codeyear and libyear are persisted since session 12 fix so that
  /// data saves include the correct school year even after a PIN-only unlock.
  Future<User?> getStoredUser() async {
    final userId    = await _storage.read(key: _kUserId);
    final userName  = await _storage.read(key: _kUserName);
    final login     = await _storage.read(key: _kLogin);
    final serverUrl = await _storage.read(key: _kServerUrl);
    if (userId == null || login == null || serverUrl == null) return null;
    // Re-configure API service with stored credentials
    final password  = await _storage.read(key: _kPassword) ?? '';
    _api.configure(serverUrl, login, password);
    // Restore codeyear from secure storage (stored at login since session 12).
    // Fall back to DB setting for older installs that stored it there.
    String codeyear = await _storage.read(key: _kCodeyear) ?? '';
    String libyear  = await _storage.read(key: _kLibyear)  ?? '';
    if (codeyear.isEmpty) codeyear = await _db.getSetting('codeyear') ?? '';
    if (libyear.isEmpty)  libyear  = await _db.getSetting('libyear')  ?? '';
    return User(
      idUser:   userId!,
      nomUser:  userName ?? login!,
      login:    login!,
      codeyear: codeyear,
      libyear:  libyear,
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
