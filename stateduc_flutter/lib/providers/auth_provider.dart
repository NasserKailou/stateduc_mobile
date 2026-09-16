import 'package:flutter/foundation.dart';
import '../models/user.dart';
import '../models/school_year.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/database_service.dart';

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

  // Session 50 additions
  int _failedAttempts = 0;
  bool _hasSecurityAnswers = false;

  // AK-YEAR-01 — gestion pluriannuelle
  SchoolYear? _activeYear;          // Année active sélectionnée par l'utilisateur
  List<SchoolYear> _schoolYears = []; // Cache en mémoire des années disponibles
  bool _yearsLoading = false;

  AuthState get state => _state;
  User? get user => _user;
  String? get error => _error;
  bool get isLoading => _loading;
  bool get isLoggedIn => _state == AuthState.loggedIn;
  String? get serverUrl => _serverUrl;
  String? get storedLogin => _storedLogin;
  String? get securityQuestion => _securityQuestion;

  /// Number of consecutive failed PIN attempts for the current account.
  int get failedAttempts => _failedAttempts;

  /// True if the 3-question security answers have been configured.
  bool get hasSecurityAnswers => _hasSecurityAnswers;

  /// True if the "PIN oublié ?" button should be visible (≥3 failed attempts
  /// AND security answers are configured).
  bool get canShowForgotPin => _failedAttempts >= 3 && _hasSecurityAnswers;

  /// The 3 fixed security question strings (read-only, defined in AuthService).
  List<String> get securityQuestions => AuthService.kSecurityQuestions;

  // AK-YEAR-01 getters
  /// Année actuellement active (choisie par l'utilisateur dans les Paramètres).
  /// Null tant que non chargée ou si aucune année n'est disponible.
  SchoolYear? get activeYear => _activeYear;

  /// Liste complète des années disponibles (cachée en mémoire).
  List<SchoolYear> get schoolYears => _schoolYears;

  /// True si le chargement des années est en cours.
  bool get yearsLoading => _yearsLoading;

  /// Code de l'année active : activeYear.code si disponible,
  /// sinon user.codeyear (compatibilité descendante exacte).
  String get effectiveYearCode =>
      _activeYear != null
          ? _activeYear!.code.toString()
          : (_user?.codeyear ?? '');

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
      _failedAttempts = await _auth.getFailedAttempts();
      _hasSecurityAnswers = await _auth.hasThreeSecurityAnswers();

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

  /// Step 1 of first-time setup: set PIN + 3 mandatory security answers.
  Future<bool> setupPin({
    required String pin,
    // Legacy optional params — kept for backward compatibility
    String? securityQuestion,
    String? securityAnswer,
    // New: 3 mandatory answers
    List<String>? securityAnswers,
  }) async {
    _clearError();
    _setLoading(true);
    try {
      await _auth.setPin(pin);

      // Handle 3-question setup (new path — mandatory)
      if (securityAnswers != null && securityAnswers.length == 3) {
        await _auth.setThreeSecurityAnswers(securityAnswers);
        _hasSecurityAnswers = true;
        // Also update legacy field for backward compat
        _securityQuestion = AuthService.kSecurityQuestions[0];
      } else if (securityQuestion != null && securityAnswer != null) {
        // Legacy optional path — still supported for backward compat
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
  /// Increments [failedAttempts] on failure, resets on success.
  Future<bool> unlockWithPin(String pin) async {
    _clearError();
    _setLoading(true);
    try {
      final ok = await _auth.verifyPin(pin);
      if (!ok) {
        // Increment failed attempts counter
        _failedAttempts = await _auth.incrementFailedAttempts();
        _error = 'PIN incorrect';
        notifyListeners();
        return false;
      }
      // Successful unlock — reset counter
      await _auth.resetFailedAttempts();
      _failedAttempts = 0;

      // Restore the User object from secure storage
      final storedUser = await _auth.getStoredUser();
      if (storedUser != null) {
        _user = storedUser;
        _state = AuthState.loggedIn;
        await _auth.restoreApiSession();
        // AK-YEAR-01 : restaure l'année active persistée
        await _loadActiveYearFromStorage();
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
      // Refresh security answers state after login
      _hasSecurityAnswers = await _auth.hasThreeSecurityAnswers();
      _state = AuthState.loggedIn;
      // AK-YEAR-01 : init année active (user.codeyear comme défaut)
      await _loadActiveYearFromStorage();
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

  /// Updates the legacy security question and answer (Settings screen).
  /// Also kept for backward compatibility with settings_screen.dart.
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

  /// Updates the 3 security answers (Settings security tab).
  Future<bool> updateThreeSecurityAnswers(List<String> answers) async {
    assert(answers.length == 3);
    _clearError();
    try {
      await _auth.setThreeSecurityAnswers(answers);
      _hasSecurityAnswers = true;
      notifyListeners();
      return true;
    } catch (e) {
      _error = e.toString();
      notifyListeners();
      return false;
    }
  }

  /// [LEGACY] Resets PIN using single security answer.
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

  /// Resets PIN using 3 security answers (≥2/3 required).
  /// On success: PIN updated + failed attempts reset.
  Future<bool> resetPinWithThreeAnswers(
      List<String> answers, String newPin) async {
    assert(answers.length == 3);
    _clearError();
    try {
      await _auth.resetPinWithThreeAnswers(answers, newPin);
      _failedAttempts = 0;
      notifyListeners();
      return true;
    } on AuthException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AK-YEAR-01 — GESTION PLURI-ANNUELLE
  // ═══════════════════════════════════════════════════════════════════════════

  /// Charge la liste des années depuis SQLite (cache), puis tente un rafraîchissement
  /// réseau. Échouera silencieusement si le réseau est absent (on garde le cache).
  ///
  /// Appelée à l'ouverture de l'onglet Année dans les Paramètres ET après le
  /// téléchargement d'une campagne depuis le serveur (AK-YEAR-02).
  ///
  /// [force] : si true, ignore le guard _yearsLoading (permet le bouton Rafraîchir
  ///           d'interrompre un chargement bloqué). Par défaut false.
  Future<void> loadYears({bool force = false}) async {
    if (_yearsLoading && !force) return;
    _yearsLoading = true;
    notifyListeners();

    final db  = DatabaseService();
    final api = ApiService();

    try {
      // 1. Cache SQLite d'abord (affichage instantané hors ligne)
      final cached = await db.getSchoolYears();
      if (cached.isNotEmpty) {
        _schoolYears = cached;
        _restoreActiveYear();
        notifyListeners();
        debugPrint('[AuthProvider] loadYears: ${cached.length} année(s) depuis cache SQLite');
      }

      // 2. Tentative de rafraîchissement réseau
      final login = _user?.login ?? _storedLogin ?? '';
      debugPrint('[AuthProvider] loadYears: tentative réseau — login="$login"');
      if (login.isNotEmpty) {
        final fetched = await api.fetchYears(login);
        debugPrint('[AuthProvider] loadYears: réseau → ${fetched.length} année(s)');
        if (fetched.isNotEmpty) {
          await db.saveSchoolYears(fetched);
          _schoolYears = fetched;
          _restoreActiveYear();
          notifyListeners();
        } else {
          debugPrint('[AuthProvider] loadYears: réseau vide (0 années) — cache conservé');
        }
      } else {
        debugPrint('[AuthProvider] loadYears: login vide — impossible d\'appeler le réseau');
      }
    } catch (e) {
      debugPrint('[AuthProvider] loadYears error (non-fatal): $e');
    } finally {
      _yearsLoading = false;
      notifyListeners();
    }
  }

  /// Restaure l'état de _activeYear depuis SQLite settings ('active_year_code').
  /// Si aucune préférence n'est stockée, utilise user.codeyear comme valeur par défaut.
  void _restoreActiveYear() {
    if (_schoolYears.isEmpty) return;
    final savedCodeStr = _activeYear != null
        ? _activeYear!.code.toString()
        : (_user?.codeyear ?? '');
    final savedCode = int.tryParse(savedCodeStr) ?? 0;
    // Cherche dans la liste ; si introuvable, prend la première de la liste
    _activeYear = _schoolYears.firstWhere(
      (y) => y.code == savedCode,
      orElse: () => _schoolYears.first,
    );
    debugPrint('[AuthProvider] _restoreActiveYear: activeYear=${_activeYear?.code}/${_activeYear?.libelle}');
  }

  /// Sélectionne [year] comme année active et la persiste dans SQLite settings.
  Future<void> setActiveYear(SchoolYear year) async {
    _activeYear = year;
    notifyListeners();
    try {
      await DatabaseService().setSetting('active_year_code', year.code.toString());
      debugPrint('[AuthProvider] setActiveYear: ${year.code}/${year.libelle}');
    } catch (e) {
      debugPrint('[AuthProvider] setActiveYear persist error (non-fatal): $e');
    }
  }

  /// Charge l'année active persistée depuis SQLite settings au démarrage.
  /// Appelé lors de l'initialisation (après login PIN).
  Future<void> _loadActiveYearFromStorage() async {
    try {
      final db = DatabaseService();
      final codeStr = await db.getSetting('active_year_code');
      if (codeStr == null || codeStr.isEmpty) {
        // Pas encore de préférence — init depuis user.codeyear
        final defaultCode = int.tryParse(_user?.codeyear ?? '') ?? 0;
        if (defaultCode > 0) {
          await db.setSetting('active_year_code', defaultCode.toString());
        }
        // _activeYear restera null jusqu'à loadYears()
        return;
      }
      // Essayer de trouver l'année dans le cache SQLite
      final years = await db.getSchoolYears();
      final code  = int.tryParse(codeStr) ?? 0;
      if (years.isNotEmpty) {
        _schoolYears = years;
        _activeYear  = years.firstWhere(
          (y) => y.code == code,
          orElse: () => years.first,
        );
        debugPrint('[AuthProvider] _loadActiveYearFromStorage: ${_activeYear?.code}/${_activeYear?.libelle}');
      }
    } catch (e) {
      debugPrint('[AuthProvider] _loadActiveYearFromStorage error (non-fatal): $e');
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
