import 'package:flutter/foundation.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import '../services/coherence_evaluator.dart';

/// DataEntryProvider — form data entry state manager.
///
/// Mirrors original logic from:
///   page_etab.js   → stmPageEtab: initHtml, initPageData, savePage,
///                    saveQstOnServer, reloadFromServer, getPageDataToSend
///   etabs.js       → StmCollectData CRUD
///   questions.js   → StmData.validate() — field validation
///   calc_total.js  → 2D matrix total calculations
///
/// CRITICAL details from page_etab.js:
///   Save:   POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
///   Reload: GET  /data_reload.php/theme_data/{login}/{sysId}/{qstId}/{campId}/{etabId}/{filter}
///   LOC_REG_0: First question must include &LOC_REG_0={etab.idRegroup} if not present
///   Radio fields in storage: key = "fieldName#optionId", value = "1"
class DataEntryProvider extends ChangeNotifier {
  DataEntryProvider({
    required DatabaseService db,
    required ApiService api,
  })  : _db = db,
        _api = api,
        _evaluator = CoherenceEvaluator(db: db);

  final DatabaseService _db;
  final ApiService _api;
  final CoherenceEvaluator _evaluator;

  // ─── Current context ────────────────────────────────────────────────────────
  String? _idCamp;
  String? _idEtab;
  String? _libEtab;
  String? _idSystem;
  String? _idRegroupEtab;  // school.idRegroup — for LOC_REG_0 injection

  // ─── School identification info (for header + pre-fill) ────────────────────
  String? _codeEtab;       // administrative code e.g. "101012071"
  String? _libyear;        // school year label e.g. "2024-2025"
  String? _codeyear;       // school year code e.g. "2024"
  String? _libStatus;      // e.g. "Public", "Privé"
  String? _libSubsector;   // e.g. "Education de Base"
  String? _adminHierarchy; // e.g. "AGADEZ / ADERBISANAT / ADEBISSANAT"

  // ─── Questions + selected question ─────────────────────────────────────────
  List<Question>  _questions         = [];
  Question?       _selectedQuestion;
  bool            _isFirstQuestion   = false;  // true when selected == questions[0]

  // ─── Filter periods ─────────────────────────────────────────────────────────
  List<FilterPeriod> _filterPeriods  = [];
  FilterPeriod?      _selectedFilter;

  // ─── Form state ─────────────────────────────────────────────────────────────
  String? _formHtml;
  Map<String, String> _formData         = {};
  Map<String, String> _validationErrors = {};
  List<ValidationRule> _rules           = [];

  // ─── Status flags ───────────────────────────────────────────────────────────
  bool    _isLoading        = false;
  bool    _isSaving         = false;
  bool    _isSending        = false;
  bool    _isReloading      = false;
  bool    _hasUnsavedChanges = false;
  String? _error;
  String? _successMessage;

  // ─── Server-side coherence check results ────────────────────────────────────
  // Populated after sendToServer() succeeds.
  // Empty list = no violations (coherence OK).
  List<CoherenceError> _coherenceErrors = [];
  bool                 _isCheckingCoherence = false;

  // ─── Offline coherence check results ────────────────────────────────────────
  // Populated after saveLocally() or immediately on field update (non-blocking).
  // Empty list = no violations detected locally.
  List<OfflineCoherenceError> _offlineCoherenceErrors = [];
  bool                        _isCheckingOffline       = false;

  // ─── Getters ─────────────────────────────────────────────────────────────────
  String? get idCamp              => _idCamp;
  String? get idEtab              => _idEtab;
  String? get libEtab             => _libEtab;
  String? get codeEtab            => _codeEtab;
  String? get libyear             => _libyear;
  String? get codeyear            => _codeyear;
  String? get libStatus           => _libStatus;
  String? get libSubsector        => _libSubsector;
  String? get adminHierarchy      => _adminHierarchy;
  List<Question> get questions    => _questions;
  Question? get selectedQuestion  => _selectedQuestion;
  List<FilterPeriod> get filterPeriods => _filterPeriods;
  FilterPeriod? get selectedFilter     => _selectedFilter;
  String? get formHtml            => _formHtml;
  Map<String, String> get formData =>
      Map.unmodifiable(_formData);
  Map<String, String> get validationErrors =>
      Map.unmodifiable(_validationErrors);
  bool    get isLoading           => _isLoading;
  bool    get isSaving            => _isSaving;
  bool    get isSending           => _isSending;
  bool    get isReloading         => _isReloading;
  bool    get hasUnsavedChanges   => _hasUnsavedChanges;
  String? get error               => _error;
  String? get successMessage      => _successMessage;
  List<CoherenceError> get coherenceErrors    => List.unmodifiable(_coherenceErrors);
  bool                 get isCheckingCoherence => _isCheckingCoherence;
  bool get hasCoherenceErrors => _coherenceErrors.isNotEmpty;

  List<OfflineCoherenceError> get offlineCoherenceErrors  => List.unmodifiable(_offlineCoherenceErrors);
  bool                        get isCheckingOffline        => _isCheckingOffline;
  bool get hasOfflineCoherenceErrors => _offlineCoherenceErrors.isNotEmpty;

  // ═══════════════════════════════════════════════════════════════════════════
  // INIT — set context for a specific school + system
  // Called when SchoolDataScreen opens.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> initForSchool({
    required String idCamp,
    required String idEtab,
    required String libEtab,
    required String idSystem,
    String? idRegroupEtab,  // school.idRegroup for LOC_REG_0
    String? codeEtab,       // administrative code
    String? libyear,        // school year label e.g. "2024-2025"
    String? codeyear,       // school year code e.g. "2024"
    String? libStatus,      // e.g. "Public"
    String? libSubsector,   // e.g. "Education de Base"
    String? adminHierarchy, // e.g. "AGADEZ / ADERBISANAT"
  }) async {
    _idCamp         = idCamp;
    _idEtab         = idEtab;
    _libEtab        = libEtab;
    _idSystem       = idSystem;
    _idRegroupEtab  = idRegroupEtab;
    _codeEtab       = codeEtab;
    _libyear        = libyear;
    _codeyear       = codeyear;
    _libStatus      = libStatus;
    _libSubsector   = libSubsector;
    _adminHierarchy = adminHierarchy;
    _selectedQuestion = null;
    _selectedFilter   = null;
    _formHtml         = null;
    _formData         = {};
    _validationErrors = {};
    _hasUnsavedChanges = false;
    _error          = null;
    _successMessage = null;
    _isFirstQuestion = false;
    _isLoading      = true;
    notifyListeners();

    try {
      _questions     = await _db.getQuestions(idCamp, idSystem);
      _filterPeriods = await _db.getFilterPeriods(idCamp);
    } catch (e) {
      _error = 'Erreur chargement questions : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }

    // Auto-select first question so the form is immediately visible
    if (_questions.isNotEmpty && _error == null) {
      await selectQuestion(_questions.first);
      // Fetch coherence rules for this school (non-blocking, best-effort)
      // Run in parallel for all questions so they are available for offline evaluation.
      _fetchAndStoreCoherenceRulesBackground(
        idCamp:  idCamp,
        idEtab:  idEtab,
        idSystem: idSystem,
        questions: _questions,
      );
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FETCH COHERENCE RULES (background, non-blocking)
  // Downloads rules from server and stores them in coherence_rules SQLite.
  // Called once when a school is opened. Silently fails if offline.
  // ═══════════════════════════════════════════════════════════════════════════

  void _fetchAndStoreCoherenceRulesBackground({
    required String idCamp,
    required String idEtab,
    required String idSystem,
    required List<Question> questions,
  }) {
    // Fire and forget — errors are non-fatal
    Future(() async {
      for (final q in questions) {
        try {
          final rules = await _api.fetchRules(
            login:    _api.login ?? '',
            campId:   idCamp,
            sysId:    idSystem,
            qstId:    q.idQst,
            etabId:   idEtab,
            filter:   null,
          );
          if (rules.isNotEmpty) {
            await _db.insertCoherenceRules(rules);
            debugPrint('[DataEntry] stored ${rules.length} offline coherence rules '
                'for qst=${q.idQst} etab=$idEtab');
          }
        } catch (_) {
          // Non-fatal — no coherence rules available offline for this question
        }
      }
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SELECT QUESTION — load HTML form + rules + saved data
  // Mirrors: stmPageEtab.initHtml() + initPageData()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectQuestion(Question question) async {
    _selectedQuestion  = question;
    _selectedFilter    = null;
    _formData          = {};
    _validationErrors  = {};
    _hasUnsavedChanges = false;
    _error             = null;
    _successMessage    = null;
    // Track if this is the first question (for LOC_REG_0 injection)
    _isFirstQuestion = _questions.isNotEmpty &&
        _questions.first.idQst == question.idQst;
    _isLoading = true;
    notifyListeners();

    try {
      // Load cached form HTML
      _formHtml = await _db.getFormHtml(_idCamp!, question.idQst);
      final _htmlSnippet = _formHtml == null
          ? 'NULL'
          : 'len=${_formHtml!.length} '
            'snippet=${_formHtml!.length > 80 ? _formHtml!.substring(0, 80) : _formHtml}';
      debugPrint('[DataEntry] selectQuestion: idQst=${question.idQst} formHtml=$_htmlSnippet');
      // Load validation rules
      _rules    = await _db.getValidationRules(_idCamp!, question.idQst);
      // Load saved field values (no filter yet)
      await _loadFormData(idFilter: null);

      // Pre-fill identification form fields for first question
      // The identification form (theme d'identification) has fields that are
      // already known: school name, code, admin code, status, subsector.
      // Pre-filling saves the user from re-entering known data.
      if (_isFirstQuestion) {
        _prefillIdentificationFields();
      }
    } catch (e) {
      _error = 'Erreur chargement formulaire : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRE-FILL IDENTIFICATION FORM
  // Injects known school fields into _formData for the identification form
  // (first question / theme d'identification).
  // Only sets fields that are not already filled (respects saved values).
  // Field names mirror the server's DICO_CHAMP values for identification.
  // ═══════════════════════════════════════════════════════════════════════════
  void _prefillIdentificationFields() {
    // Fill a field only if the source value is non-null AND non-empty,
    // AND the form field is either absent OR currently empty/blank.
    // This lets saved non-empty values take precedence but fills in blanks.
    void fill(String key, String? value) {
      if (value == null || value.trim().isEmpty) return;
      final existing = _formData[key];
      if (existing == null || existing.trim().isEmpty) {
        _formData[key] = value;
      }
    }

    // ── School name ──────────────────────────────────────────────────────────
    // Identification form uses _0 suffix (row index 0 for non-grille forms).
    fill('NOM_ETABLISSEMENT_0',  _libEtab);   // mobile identification form
    fill('NOM_ETABLISSEMENT',    _libEtab);   // fallback (other forms)
    fill('LIB_ETABLISSEMENT',    _libEtab);
    fill('NOM_ETAB',             _libEtab);

    // ── Administrative code ───────────────────────────────────────────────────
    fill('CODE_ADMINISTRATIF_0', _codeEtab);  // mobile identification form
    fill('CODE_ETABLISSEMENT',   _codeEtab);
    fill('COD_ETAB',             _codeEtab);
    fill('CODE_ADMIN',           _codeEtab);

    // ── Status / type ─────────────────────────────────────────────────────────
    fill('STATUT',               _libStatus);
    fill('LIB_STATUT',           _libStatus);

    // ── Sous-secteur ─────────────────────────────────────────────────────────
    fill('SOUS_SECTEUR',         _libSubsector);
    fill('LIB_SOUS_SECTEUR',     _libSubsector);

    // ── Année scolaire ────────────────────────────────────────────────────────
    fill('ANNEE_SCOLAIRE',       _libyear);
    fill('LIB_ANNEE',            _libyear);

    debugPrint('[DataEntry] _prefillIdentificationFields: etab=$_libEtab '
        'code=$_codeEtab status=$_libStatus subsector=$_libSubsector '
        'year=$_libyear codeyear=$_codeyear');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SELECT FILTER — reload data for selected filter period
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectFilter(FilterPeriod? filter) async {
    _selectedFilter    = filter;
    _formData          = {};
    _validationErrors  = {};
    _hasUnsavedChanges = false;
    _isLoading         = true;
    notifyListeners();
    try {
      await _loadFormData(idFilter: filter?.idFilter);
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> _loadFormData({String? idFilter}) async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) return;
    _formData = await _db.getCollectedData(
      idCamp:   _idCamp!,
      idEtab:   _idEtab!,
      idQst:    _selectedQuestion!.idQst,
      idFilter: idFilter,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FIELD UPDATE — called by form widgets on change
  // Mirrors: script.js ctrl_saisie / ctrl_saisie_text
  // ═══════════════════════════════════════════════════════════════════════════

  void updateField(String fieldName, String value) {
    _formData[fieldName] = value;
    _hasUnsavedChanges   = true;
    _validationErrors.remove(fieldName);
    notifyListeners();
  }

  /// Validates a single field against its rules.
  /// Returns French error message or null if valid.
  String? validateField(String fieldName, String value) {
    final fieldRules = _rules.where((r) => r.idChamp == fieldName).toList();
    if (fieldRules.isEmpty) return null;
    for (final rule in fieldRules) {
      final error = rule.validate(value);
      if (error != null) return error;
    }
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // VALIDATE ALL — before save or send
  // ═══════════════════════════════════════════════════════════════════════════

  bool validateAll() {
    _validationErrors = {};
    bool isValid = true;
    // Check mandatory fields first
    for (final rule in _rules) {
      if (rule.ruleType == 'mandatory') {
        final val = _formData[rule.idChamp] ?? '';
        if (val.trim().isEmpty) {
          _validationErrors[rule.idChamp] = 'Ce champ est obligatoire';
          isValid = false;
        }
      }
    }
    // Check type/range on filled fields
    for (final entry in _formData.entries) {
      if (_validationErrors.containsKey(entry.key)) continue;
      final error = validateField(entry.key, entry.value);
      if (error != null) {
        _validationErrors[entry.key] = error;
        isValid = false;
      }
    }
    notifyListeners();
    return isValid;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SAVE LOCALLY — mirrors stmPageEtab.savePage()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> saveLocally() async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) {
      return false;
    }
    _isSaving       = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();
    try {
      await _db.saveCollectedData(
        idCamp:   _idCamp!,
        idEtab:   _idEtab!,
        idQst:    _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
        data:     _formData,
      );
      _hasUnsavedChanges = false;
      _successMessage    = 'Données sauvegardées localement';
      notifyListeners();
      // Run offline coherence check in background after save
      Future(() => checkCoherenceOffline());
      return true;
    } catch (e) {
      _error = 'Erreur sauvegarde : ${e.toString()}';
      notifyListeners();
      return false;
    } finally {
      _isSaving = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SEND TO SERVER — mirrors stmPageEtab.saveQstOnServer()
  //
  // POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
  // Uses user.login (NOT user.idUser)!
  // For first question: injects LOC_REG_0={etab.idRegroup} if missing.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> sendToServer({required User user, bool online = true}) async {
    if (!online) {
      _error = 'Pas de connexion. Les données seront envoyées ultérieurement.';
      notifyListeners();
      return false;
    }
    if (_idCamp    == null ||
        _idEtab    == null ||
        _idSystem  == null ||
        _selectedQuestion == null) {
      return false;
    }

    // Save locally first (ensures data is persisted)
    await saveLocally();

    _isSending      = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      final ok = await _api.saveData(
        login:            user.login,         // ← uses login, not idUser!
        campId:           _idCamp!,
        sysId:            _idSystem!,
        qstId:            _selectedQuestion!.idQst,
        etabId:           _idEtab!,
        filter:           _selectedFilter?.idFilter,
        formData:         _formData,
        etabRegroupId:    _idRegroupEtab,     // for LOC_REG_0
        isFirstQuestion:  _isFirstQuestion,
        yearCode:         user.codeyear,      // inject school year for PHP session bypass
      );
      if (ok) {
        await _db.markCollectedDataSent(
          idCamp:   _idCamp!,
          idEtab:   _idEtab!,
          idQst:    _selectedQuestion!.idQst,
          idFilter: _selectedFilter?.idFilter,
        );
        _successMessage = 'Données envoyées avec succès';
        notifyListeners();

        // ── Coherence check (non-blocking) ─────────────────────────────────
        // Runs automatically after a successful save. The server's
        // controle_theme_batch executes SQL rules against the now-saved
        // data in DB and returns any violations.
        _isCheckingCoherence = true;
        _coherenceErrors     = [];
        notifyListeners();
        try {
          _coherenceErrors = await _api.checkCoherence(
            login:    user.login,
            campId:   _idCamp!,
            sysId:    _idSystem!,
            qstId:    _selectedQuestion!.idQst,
            etabId:   _idEtab!,
            filter:   _selectedFilter?.idFilter,
            yearCode: user.codeyear,
          );
        } catch (_) {
          _coherenceErrors = []; // Non-fatal — silently ignore
        } finally {
          _isCheckingCoherence = false;
          notifyListeners();
        }
      } else {
        _error = 'Échec de l\'envoi. Réessayez.';
      }
      notifyListeners();
      return ok;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erreur envoi : ${e.toString()}';
      notifyListeners();
      return false;
    } finally {
      _isSending = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // OFFLINE COHERENCE CHECK
  // Evaluates locally stored coherence rules against collected_data.
  // Can be called after saveLocally() or triggered manually from the UI.
  // Non-blocking: errors are surfaced through offlineCoherenceErrors getter.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> checkCoherenceOffline() async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) return;
    _isCheckingOffline      = true;
    _offlineCoherenceErrors = [];
    notifyListeners();

    try {
      final rules = await _db.getCoherenceRules(
        idCamp: _idCamp!,
        idQst:  _selectedQuestion!.idQst,
        idEtab: _idEtab!,
      );

      if (rules.isEmpty) {
        debugPrint('[DataEntry] checkCoherenceOffline: no rules stored for this context');
        return;
      }

      _offlineCoherenceErrors = await _evaluator.evaluate(
        rules:    rules,
        formData: _formData,
        idCamp:   _idCamp!,
        idQst:    _selectedQuestion!.idQst,
        idEtab:   _idEtab!,
        idFilter: _selectedFilter?.idFilter,
      );

      debugPrint('[DataEntry] checkCoherenceOffline: '
          '${_offlineCoherenceErrors.length} violation(s) found');
    } catch (e) {
      debugPrint('[DataEntry] checkCoherenceOffline error: $e');
      _offlineCoherenceErrors = [];
    } finally {
      _isCheckingOffline = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RELOAD FROM SERVER — mirrors stmPageEtab.reloadQstFromServer()
  //
  // GET /data_reload.php/theme_data/{login}/{sysId}/{qstId}/{campId}/{etabId}/{filter}
  // Uses user.login (NOT user.idUser)!
  //
  // Response: { fieldName: [value, type], ... }
  //   radio fields: stored as fieldName#optionId = '1'
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> reloadFromServer({required User user}) async {
    if (_idCamp    == null ||
        _idEtab    == null ||
        _idSystem  == null ||
        _selectedQuestion == null) {
      return false;
    }
    _isReloading    = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      // Reload from server (already parses radio field format in api_service)
      final serverFields = await _api.reloadData(
        login:    user.login,     // ← uses login, not idUser!
        sysId:    _idSystem!,
        qstId:    _selectedQuestion!.idQst,
        campId:   _idCamp!,
        etabId:   _idEtab!,
        filter:   _selectedFilter?.idFilter,
      );

      if (serverFields == null) {
        _error = 'Aucune donnée retournée par le serveur';
        notifyListeners();
        return false;
      }

      // Convert Map<String, dynamic> → Map<String, String> for local DB
      final Map<String, String> serverFieldsStr = serverFields.map(
        (k, v) => MapEntry(k, v?.toString() ?? ''),
      );

      // Replace local data with server values
      await _db.deleteCollectedData(
        idCamp:   _idCamp!,
        idEtab:   _idEtab!,
        idQst:    _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
      );
      await _db.saveCollectedData(
        idCamp:   _idCamp!,
        idEtab:   _idEtab!,
        idQst:    _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
        data:     serverFieldsStr,
      );
      await _db.markCollectedDataSent(
        idCamp:   _idCamp!,
        idEtab:   _idEtab!,
        idQst:    _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
      );

      _formData          = serverFieldsStr;
      _hasUnsavedChanges = false;
      _successMessage    = 'Données rechargées depuis le serveur';
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erreur rechargement : ${e.toString()}';
      notifyListeners();
      return false;
    } finally {
      _isReloading = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SEND ALL CAMPAIGN DATA (page_camp.js sendAllData)
  // Sends all collected data for all schools in the campaign.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<Map<String, bool>> sendAllCampaignData({
    required User user,
    required String idCamp,
    required String idSystem,
    required List<String> etabIds,
    required List<String> qstIds,
  }) async {
    final results = <String, bool>{};
    for (final etabId in etabIds) {
      for (final qstId in qstIds) {
        final data = await _db.getCollectedData(
          idCamp: idCamp,
          idEtab: etabId,
          idQst:  qstId,
        );
        if (data.isEmpty) continue;

        // Determine if this is the first question
        final isFirst = qstIds.isNotEmpty && qstIds.first == qstId;

        try {
          final ok = await _api.saveData(
            login:           user.login,    // ← uses login!
            campId:          idCamp,
            sysId:           idSystem,
            qstId:           qstId,
            etabId:          etabId,
            filter:          null,
            formData:        data,
            isFirstQuestion: isFirst,
            yearCode:        user.codeyear, // inject school year for PHP session bypass
          );
          results['${etabId}_$qstId'] = ok;
          if (ok) {
            await _db.markCollectedDataSent(
              idCamp: idCamp,
              idEtab: etabId,
              idQst:  qstId,
            );
          }
        } catch (_) {
          results['${etabId}_$qstId'] = false;
        }
      }
    }
    return results;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // 2D MATRIX TOTAL CALCULATION
  // Replaces calc_total.js — calcul_Total_ThemeMat2D, set_TOTAL_ThemeMat2D
  // ═══════════════════════════════════════════════════════════════════════════

  /// Recalculates all row/column/grand totals for a 2D matrix form.
  Map<String, String> calculateMatrixTotals({
    required Map<String, String> data,
    required int rows,
    required int cols,
    required String cellPrefix,
  }) {
    final result = Map<String, String>.from(data);

    for (int r = 0; r < rows; r++) {
      double rowTotal = 0;
      for (int c = 0; c < cols; c++) {
        rowTotal +=
            double.tryParse(data['${cellPrefix}_${r}_$c'] ?? '') ?? 0;
      }
      result['total_r$r'] = rowTotal.toStringAsFixed(0);
    }

    for (int c = 0; c < cols; c++) {
      double colTotal = 0;
      for (int r = 0; r < rows; r++) {
        colTotal +=
            double.tryParse(data['${cellPrefix}_${r}_$c'] ?? '') ?? 0;
      }
      result['total_c$c'] = colTotal.toStringAsFixed(0);
    }

    double grandTotal = 0;
    for (int r = 0; r < rows; r++) {
      grandTotal += double.tryParse(result['total_r$r'] ?? '') ?? 0;
    }
    result['total_all'] = grandTotal.toStringAsFixed(0);

    return result;
  }

  /// Calculates a formula-based total (sum of named fields).
  double calculateFormulaTotal(List<String> fieldNames) {
    return fieldNames.fold(
        0.0, (sum, name) => sum + (double.tryParse(_formData[name] ?? '') ?? 0));
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  void clearMessages() {
    _error                  = null;
    _successMessage         = null;
    _offlineCoherenceErrors = [];
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
