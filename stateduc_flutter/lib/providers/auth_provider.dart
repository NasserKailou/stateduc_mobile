import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';

/// AuthProvider — ChangeNotifier for user session state.
///
/// Mirrors original login flow from:
///   page_index.js   → init_connexion(), user_is_login(), user_is_logout()
///   users.js        → stmUser.connect(), stmUser.deconnect()
///   page_settings.js → PIN change, security question change
///
/// State transitions:
///   unknown → (hasPinConfigured? → needsServerLogin) → pinRequired → loggedIn
///   loggedIn → pinRequired (after app background)
///   loggedIn → serverLoginRequired (credentials expired)
enum AuthState {
  /// App just launched, determining initial state
  unknown,
  /// No PIN set yet — first-time setup required
  firstTimeSetup,
  /// PIN set but needs server login (no stored credentials)
  needsServerLogin,
  /// PIN set + credentials stored — waiting for PIN unlock
  pinRequired,
  /// Authenticated and ready
  loggedIn,
}

class AuthProvider extends ChangeNotifier {
  AuthProvider({required AuthService authService})
      : _auth = authService;

  final AuthService _auth;

  // ─── State ─────────────────────────────────────────────────────────────────
  AuthState _state = AuthState.unknown;
  User? _user;
  String? _error;
  bool _loading = false;
  String? _serverUrl;
  String? _storedLogin;
  String? _securityQuestion;

  AuthState get state => _state;
  User? get user => _user;
  String? get error => _error;
  bool get isLoading => _loading;
  bool get isLoggedIn => _state == AuthState.loggedIn;
  String? get serverUrl => _serverUrl;
  String? get storedLogin => _storedLogin;
  String? get securityQuestion => _securityQuestion;

  // ═══════════════════════════════════════════════════════════════════════════
  // INITIALIZATION — called from main.dart after Provider tree is ready
  // ═══════════════════════════════════════════════════════════════════════════

  /// Determines the initial auth state on app start.
  Future<void> initialize() async {
    _setLoading(true);
    try {
      _serverUrl = await _auth.getServerUrl();
      _storedLogin = await _auth.getStoredLogin();
      _securityQuestion = await _auth.getSecurityQuestion();

      final hasPin = await _auth.hasPinConfigured();
      if (!hasPin) {
        _state = AuthState.firstTimeSetup;
        return;
      }
      final hasCredentials = await _auth.hasStoredCredentials();
      if (!hasCredentials) {
        _state = AuthState.needsServerLogin;
        return;
      }
      // PIN is set and credentials stored → waiting for PIN
      _state = AuthState.pinRequired;
    } catch (e) {
      _error = e.toString();
      _state = AuthState.firstTimeSetup; // Safest fallback
    } finally {
      _setLoading(false);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FIRST-TIME SETUP
  // ═══════════════════════════════════════════════════════════════════════════

  /// Step 1 of first-time setup: set PIN + optional security question.
  Future<bool> setupPin({
    required String pin,
    String? securityQuestion,
    String? securityAnswer,
  }) async {
    _clearError();
    _setLoading(true);
    try {
      await _auth.setPin(pin);
      if (securityQuestion != null && securityAnswer != null) {
        await _auth.setSecurityQuestion(securityQuestion, securityAnswer);
        _securityQuestion = securityQuestion;
      }
      _state = AuthState.needsServerLogin;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PIN VERIFICATION (local unlock)
  // ═══════════════════════════════════════════════════════════════════════════

  /// Verifies PIN locally. On success, restores API session and transitions
  /// to loggedIn (offline) or stays at pinRequired if offline restore fails.
  Future<bool> unlockWithPin(String pin) async {
    _clearError();
    _setLoading(true);
    try {
      final ok = await _auth.verifyPin(pin);
      if (!ok) {
        _error = 'PIN incorrect';
        notifyListeners();
        return false;
      }
      // Restore the User object from secure storage
      final storedUser = await _auth.getStoredUser();
      if (storedUser != null) {
        _user = storedUser;
        _state = AuthState.loggedIn;
        await _auth.restoreApiSession();
      } else {
        // PIN ok but no stored user — need server login
        _state = AuthState.needsServerLogin;
      }
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER LOGIN
  // ═══════════════════════════════════════════════════════════════════════════

  /// Performs server authentication.
  /// On success → loggedIn; on failure → error displayed.
  Future<bool> loginToServer({
    required String serverUrl,
    required String login,
    required String password,
  }) async {
    _clearError();
    _setLoading(true);
    try {
      final user = await _auth.login(
        serverUrl: serverUrl,
        login: login,
        password: password,
      );
      _user = user;
      _serverUrl = serverUrl;
      _storedLogin = login;
      _state = AuthState.loggedIn;
      notifyListeners();
      return true;
    } on AuthException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erreur de connexion : ${e.toString()}';
      notifyListeners();
      return false;
    } finally {
      _setLoading(false);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LOGOUT
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> logout() async {
    _setLoading(true);
    try {
      await _auth.logout();
    } finally {
      _user = null;
      _state = AuthState.pinRequired;
      _setLoading(false);
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SETTINGS ACTIONS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Changes the server URL (stored in secure storage + DB).
  Future<bool> updateServerUrl(String url) async {
    _clearError();
    try {
      await _auth.setServerUrl(url);
      _serverUrl = url;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  /// Changes the PIN — requires old PIN for verification.
  Future<bool> changePin(String oldPin, String newPin) async {
    _clearError();
    try {
      await _auth.changePin(oldPin, newPin);
      notifyListeners();
      return true;
    } on AuthException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  /// Updates the security question and answer.
  Future<bool> updateSecurityQuestion(
      String question, String answer) async {
    _clearError();
    try {
      await _auth.setSecurityQuestion(question, answer);
      _securityQuestion = question;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  /// Resets PIN using security answer.
  Future<bool> resetPinWithSecurityAnswer(
      String answer, String newPin) async {
    _clearError();
    try {
      await _auth.resetPinWithSecurityAnswer(answer, newPin);
      notifyListeners();
      return true;
    } on AuthException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  void clearError() => _clearError();

  void _clearError() {
    _error = null;
    notifyListeners();
  }

  void _setLoading(bool value) {
    _loading = value;
    notifyListeners();
  }
}
