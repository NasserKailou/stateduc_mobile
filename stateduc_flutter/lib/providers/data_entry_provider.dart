import 'dart:async';
import 'package:flutter/foundation.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../models/sync_status.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';
import '../services/coherence_evaluator.dart';
import '../services/conditional_rules_parser.dart';
import '../services/theme_rule_engine.dart';

/// DataEntryProvider — gestionnaire de l'état de saisie des données d'un formulaire.
///
/// Réplique la logique originale de :
///   page_etab.js   → stmPageEtab: initHtml, initPageData, savePage,
///                    saveQstOnServer, reloadFromServer, getPageDataToSend
///   etabs.js       → StmCollectData CRUD
///   questions.js   → StmData.validate() — validation des champs
///   calc_total.js  → calculs de totaux de matrices 2D
///
/// DÉTAILS CRITIQUES de page_etab.js :
///   Sauvegarde : POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
///   Rechargement : GET /data_reload.php/theme_data/{login}/{sysId}/{qstId}/{campId}/{etabId}/{filter}
///   LOC_REG_0 : La première question doit inclure &LOC_REG_0={etab.idRegroup} si absent
///   Champs radio en stockage : clé = "fieldName#optionId", valeur = "1"
///
/// Fonctionnement général :
///   1. [initForSchool]   → initialise le contexte (campagne, établissement, système)
///   2. [selectQuestion]  → charge le HTML + règles + données sauvegardées du thème
///   3. [updateField]     → met à jour un champ en mémoire
///   4. [saveLocally]     → persiste dans SQLite (hors ligne)
///   5. [sendToServer]    → POST au serveur + contrôle de cohérence
///   6. [reloadFromServer] → GET depuis le serveur (remplace les données locales)
///
/// Contrôle de cohérence :
///   - Offline : [checkCoherenceOffline] via [CoherenceEvaluator] après sauvegarde
///   - Serveur : automatique après [sendToServer] via [ApiService.checkCoherence]
class DataEntryProvider extends ChangeNotifier {
  // Nombre total de tentatives d'envoi (1 initiale + _kMaxRetries re-tentatives).
  // Exposé en public pour que l'UI puisse afficher "Tentative N/kMaxSendAttempts".
  // Doit être synchronisé avec ApiService._kMaxRetries + 1.
  static const int kMaxSendAttempts = 3;  // 1 + 2 retries

  DataEntryProvider({
    required DatabaseService db,
    required ApiService api,
  })  : _db = db,
        _api = api,
        _evaluator   = CoherenceEvaluator(db: db),
        _themeEngine = ThemeRuleEngine(db: db);

  final DatabaseService _db;
  final ApiService _api;
  final CoherenceEvaluator _evaluator;
  final ThemeRuleEngine _themeEngine;

  // ─── Contexte courant ────────────────────────────────────────────────────────
  String? _idCamp;
  String? _idEtab;
  String? _libEtab;
  String? _idSystem;
  String? _idRegroupEtab;  // school.idRegroup — pour l'injection de LOC_REG_0
  String? _idStatus;       // school.idStatus — pour le pré-remplissage radio statut
  User?   _currentUser;    // mis en cache pour le rechargement automatique à l'ouverture

  // ─── Informations d'identification de l'établissement (en-tête + pré-remplissage) ──
  String? _codeEtab;       // code administratif ex. "101012071"
  String? _libyear;        // libellé de l'année scolaire ex. "2024-2025"
  String? _codeyear;       // code de l'année scolaire ex. "2024"
  String? _libStatus;      // ex. "Public", "Privé"
  String? _libSubsector;   // ex. "Education de Base"
  String? _adminHierarchy; // ex. "AGADEZ / ADERBISANAT / ADEBISSANAT"

  // ─── Questions + question sélectionnée ──────────────────────────────────────
  List<Question>  _questions         = [];
  Question?       _selectedQuestion;
  bool            _isFirstQuestion   = false;  // vrai si question sélectionnée == questions[0]

  // ─── Périodes de filtre ──────────────────────────────────────────────────────
  List<FilterPeriod> _filterPeriods  = [];
  FilterPeriod?      _selectedFilter;

  // ─── État du formulaire ──────────────────────────────────────────────────────
  String? _formHtml;
  Map<String, String> _formData         = {};  // données saisies en mémoire
  Map<String, String> _validationErrors = {};  // erreurs de validation par champ
  List<ValidationRule> _rules           = [];  // règles de validation du thème courant

  // ─── Indicateurs de statut ───────────────────────────────────────────────────
  bool    _isLoading        = false;   // chargement initial de la question
  bool    _isSaving         = false;   // sauvegarde locale en cours
  bool    _isSending        = false;   // envoi au serveur en cours
  int     _sendAttempt      = 0;       // numéro de tentative d'envoi (0 = pas d'envoi, 1-3 = retry)
  bool    _isReloading      = false;   // rechargement depuis le serveur en cours
  bool    _hasUnsavedChanges = false;  // vrai si des modifications non sauvegardées existent
  String? _error;
  String? _successMessage;
  // SESSION 53 — Message d'avertissement KOSAVE (données reçues mais non enregistrées côté serveur)
  String? _warningMessage;

  // ─── Résultats du contrôle de cohérence serveur ──────────────────────────────
  // Rempli après sendToServer() si le serveur détecte des violations.
  // Liste vide = aucune violation (cohérence OK).
  List<CoherenceError> _coherenceErrors = [];
  bool                 _isCheckingCoherence = false;

  // ─── Résultats du contrôle de cohérence hors ligne ──────────────────────────
  // Rempli après saveLocally() ou checkCoherenceOffline().
  // Liste vide = aucune violation détectée localement.
  List<OfflineCoherenceError> _offlineCoherenceErrors = [];
  bool                        _isCheckingOffline       = false;

  // ─── Résultats du moteur générique ThemeRuleEngine ────────────────────────
  // Rempli après checkCoherenceOffline() si dico_regle_theme contient des règles
  // pour le thème courant. Complément des règles paire (CoherenceEvaluator).
  List<ThemeCoherenceError> _themeCoherenceErrors = [];

  // ─── Debounce pour le contrôle offline déclenché par updateField() ───────────
  // Évite d'évaluer la cohérence à chaque frappe — attend 800 ms d'inactivité.
  Timer? _coherenceDebounce;

  // ─── AK-SYNC (ak_burundi) — état de synchronisation ─────────────────────────
  /// U25 — verrou global : un seul envoi à la fois (unitaire OU global), tous écrans.
  static bool _globalSendLock = false;
  static bool get isAnySendRunning => _globalSendLock;

  /// U04/U05 — état de sync par formulaire de l'établissement courant (clé = FormSyncStatus.key).
  Map<String, FormSyncStatus> _syncStatuses = {};
  /// État du formulaire courant (thème + période sélectionnés).
  FormSyncStatus? _currentSyncStatus;
  /// U06 — résultats détaillés du dernier envoi global.
  List<SendOutcome> _lastBatchOutcomes = [];
  /// U19 — feedbacks admin (corrections / verrous) de l'établissement courant.
  List<SyncFeedback> _feedbacks = [];
  /// U08 — dernier accusé de réception serveur.
  ServerAck? _lastAck;

  // ─── Questions conditionnelles (Fix #5) ─────────────────────────────────────
  // Règles extraites du HTML du formulaire courant lors de selectQuestion().
  // Réévaluées à chaque updateField() pour les champs sources.
  List<ConditionalRule> _conditionalRules = [];
  Set<String> _disabledFields = {};

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
  int     get sendAttempt         => _sendAttempt;   // 0 = inactif, 1/2/3 = tentative retry
  bool    get isReloading         => _isReloading;
  bool    get hasUnsavedChanges   => _hasUnsavedChanges;
  String? get error               => _error;
  String? get successMessage      => _successMessage;
  /// SESSION 53 — Message d'avertissement KOSAVE : données reçues par le serveur
  /// mais non enregistrées en base (ISOKSAVEINDATABASE absent de questionnaire_ws).
  /// Null si aucun avertissement en cours. L'UI doit l'afficher en orange/jaune.
  String? get warningMessage      => _warningMessage;
  List<CoherenceError> get coherenceErrors    => List.unmodifiable(_coherenceErrors);
  bool                 get isCheckingCoherence => _isCheckingCoherence;
  bool get hasCoherenceErrors => _coherenceErrors.isNotEmpty;

  List<OfflineCoherenceError> get offlineCoherenceErrors  => List.unmodifiable(_offlineCoherenceErrors);
  bool                        get isCheckingOffline        => _isCheckingOffline;
  bool get hasOfflineCoherenceErrors => _offlineCoherenceErrors.isNotEmpty;

  /// Violations détectées par le moteur générique ThemeRuleEngine.
  /// Complément de [offlineCoherenceErrors] — règles issues de DICO_REGLE_THEME.
  List<ThemeCoherenceError> get themeCoherenceErrors   => List.unmodifiable(_themeCoherenceErrors);
  bool get hasThemeCoherenceErrors => _themeCoherenceErrors.isNotEmpty;

  /// Ensemble des noms de champs actuellement désactivés (Fix #5 — questions conditionnelles).
  /// Mis à jour à chaque appel de updateField() sur un champ source.
  Set<String> get disabledFields => Set.unmodifiable(_disabledFields);

  // ─── AK-SYNC getters ────────────────────────────────────────────────────────
  FormSyncStatus? get currentSyncStatus => _currentSyncStatus;
  List<SendOutcome> get lastBatchOutcomes => List.unmodifiable(_lastBatchOutcomes);
  List<SyncFeedback> get feedbacks => List.unmodifiable(_feedbacks);
  ServerAck? get lastAck => _lastAck;

  /// Statut de sync d'un thème (toutes périodes confondues) pour les chips (U05).
  /// Priorité d'affichage : locked > failed/kosave/rejected > pending > sent > null (jamais saisi).
  SyncStatus? syncStatusForQuestion(String idQst) {
    SyncStatus? best;
    int rank(SyncStatus s) {
      switch (s) {
        case SyncStatus.locked: return 5;
        case SyncStatus.kosave:
        case SyncStatus.failed:
        case SyncStatus.rejected: return 4;
        case SyncStatus.pending: return 3;
        case SyncStatus.sent: return 1;
      }
    }
    for (final s in _syncStatuses.values) {
      if (s.idQst != idQst) continue;
      if (best == null || rank(s.status) > rank(best)) best = s.status;
    }
    return best;
  }

  /// Le formulaire courant est-il verrouillé par l'administration (U20/U21) ?
  bool get isCurrentFormLocked {
    if (_currentSyncStatus?.isLocked == true) return true;
    final q = _selectedQuestion?.idQst;
    if (q == null) return false;
    return _feedbacks.any((f) => f.isLock && f.appliesTo(q, _selectedFilter?.idFilter));
  }

  /// Demande de correction admin applicable au formulaire courant (U19), sinon null.
  SyncFeedback? get currentCorrectionRequest {
    final q = _selectedQuestion?.idQst;
    if (q == null) return null;
    for (final f in _feedbacks) {
      if (f.isCorrection && f.appliesTo(q, _selectedFilter?.idFilter)) return f;
    }
    return null;
  }

  /// Recharge l'état de sync de l'établissement courant depuis SQLite.
  Future<void> refreshSyncStatuses() async {
    if (_idCamp == null || _idEtab == null) return;
    final list = await _db.getFormSyncStatusesForSchool(
      idCamp: _idCamp!, idEtab: _idEtab!, codeTypeAnnee: _codeyear ?? '');
    _syncStatuses = {for (final s in list) s.key: s};
    _feedbacks = await _db.getSyncFeedback(_idCamp!, codeTypeAnnee: _codeyear ?? '', idEtab: _idEtab!);
    _refreshCurrentSyncStatus();
    notifyListeners();
  }

  void _refreshCurrentSyncStatus() {
    final q = _selectedQuestion?.idQst;
    if (q == null || _idCamp == null || _idEtab == null) { _currentSyncStatus = null; return; }
    final key = '${_codeyear ?? ''}|$_idCamp|$_idEtab|$q|${_selectedFilter?.idFilter ?? ''}';
    _currentSyncStatus = _syncStatuses[key];
  }

  // SESSION 58 — FIX KOSAVE 10502 : filtre effectif côté serveur
  //
  // ROOT CAUSE : questionnaire_ws.php utilise un double-include :
  //   1er include (GET) : crée le grille avec code_filtre=$_SESSION['filtre']
  //   2ème include (POST) : exécute comparer()+maj_bdd() sur le grille sauvé
  // Si $_SESSION['filtre'] a une valeur résiduelle d'un thème précédent
  // (ex: '21' d'un thème avec filtre) ET que le thème courant a une zone
  // TYPE_OBJET='systeme' + CHAMP_PERE='CODE_TYPE_FILTRE', alors get_dico()
  // génère WHERE CODE_TYPE_PERIODE=21 (valeur résiduelle) → aucune donnée
  // existante trouvée → toutes les lignes POST = action 'I' (INSERT) →
  // INSERT échoue (PK déjà existante) → Execute()===false → KOSAVE.
  //
  // FIX : si la campagne a des périodes disponibles (_filterPeriods non vide)
  // ET que l'utilisateur n'a pas sélectionné de filtre (_selectedFilter==null),
  // on utilise _filterPeriods.first comme filtre côté serveur UNIQUEMENT
  // (envoi du paramètre &filtre=X à questionnaire_ws.php pour forcer
  //  $_SESSION['filtre'] à une valeur valide et connue).
  //
  // Ce getter est utilisé dans sendToServer() et _autoReloadFromServerBackground()
  // à la place de _selectedFilter?.idFilter.
  //
  // IMPORTANT : les opérations SQLite (saveCollectedData, getCollectedData,
  // markCollectedDataSent) continuent d'utiliser _selectedFilter?.idFilter
  // (null pour hasFilter==false) pour ne pas fragmenter les données locales
  // par période quand le formulaire ne l'exige pas.
  String? get _effectiveServerFilter {
    if (_selectedFilter != null) return _selectedFilter!.idFilter;
    if (_filterPeriods.isNotEmpty) {
      // Utilise la première période disponible comme filtre par défaut
      // pour éviter que PHP utilise une valeur résiduelle de $_SESSION['filtre']
      return _filterPeriods.first.idFilter;
    }
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // INITIALISATION — configure le contexte pour un établissement + système donnés
  //
  // Appelé à l'ouverture de SchoolDataScreen (via addPostFrameCallback).
  // Réinitialise tout l'état (questions, formulaire, erreurs), charge les
  // questions depuis SQLite, puis sélectionne automatiquement la première
  // pour que le formulaire soit immédiatement visible.
  //
  // Lance aussi le téléchargement en arrière-plan des règles de cohérence
  // pour toutes les questions (non bloquant, best-effort).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> initForSchool({
    required String idCamp,
    required String idEtab,
    required String libEtab,
    required String idSystem,
    String? idRegroupEtab,  // school.idRegroup pour LOC_REG_0
    String? idStatus,       // school.idStatus — numérique ex. "1"=Public, "2"=Privé
    String? codeEtab,       // code administratif
    String? libyear,        // libellé année scolaire ex. "2024-2025"
    String? codeyear,       // code année scolaire ex. "2024"
    String? libStatus,      // ex. "Public"
    String? libSubsector,   // ex. "Education de Base"
    String? adminHierarchy, // ex. "AGADEZ / ADERBISANAT"
  }) async {
    _idCamp         = idCamp;
    _idEtab         = idEtab;
    _libEtab        = libEtab;
    _idSystem       = idSystem;
    _idRegroupEtab  = idRegroupEtab;
    _idStatus       = idStatus;
    _codeEtab       = codeEtab;
    _libyear        = libyear;
    _codeyear       = codeyear;
    _libStatus      = libStatus;
    _libSubsector   = libSubsector;
    _adminHierarchy = adminHierarchy;
    // NOTE : _currentUser est défini via setCurrentUser() appelé séparément
    // depuis SchoolDataScreen avant initForSchool
    _selectedQuestion = null;
    _selectedFilter   = null;
    _formHtml         = null;
    _formData         = {};
    _validationErrors = {};
    _hasUnsavedChanges = false;
    _error          = null;
    _successMessage = null;
    _isFirstQuestion = false;
    _conditionalRules = [];       // Fix #5 — réinitialisation à chaque école
    _disabledFields   = {};       // Fix #5
    _syncStatuses      = {};      // AK-SYNC
    _currentSyncStatus = null;
    _feedbacks         = [];
    _lastAck           = null;
    _isLoading      = true;
    notifyListeners();

    try {
      // Charge les questions et les périodes de filtre depuis SQLite
      _questions     = await _db.getQuestions(idCamp, idSystem);
      _filterPeriods = await _db.getFilterPeriods(idCamp);
      // AK-SYNC : état de synchronisation + feedbacks admin (SQLite, hors ligne OK)
      final list = await _db.getFormSyncStatusesForSchool(
        idCamp: idCamp, idEtab: idEtab, codeTypeAnnee: codeyear ?? '');
      _syncStatuses = {for (final s in list) s.key: s};
      _feedbacks = await _db.getSyncFeedback(idCamp, codeTypeAnnee: codeyear ?? '', idEtab: idEtab);
    } catch (e) {
      _error = 'Erreur chargement questions : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }

    // Sélection automatique de la première question pour afficher le formulaire
    if (_questions.isNotEmpty && _error == null) {
      await selectQuestion(_questions.first);
      // Téléchargement en arrière-plan des règles de cohérence pour toutes les questions
      // (non bloquant — exécuté en parallèle pour être disponibles au contrôle offline)
      _fetchAndStoreCoherenceRulesBackground(
        idCamp:  idCamp,
        idEtab:  idEtab,
        idSystem: idSystem,
        questions: _questions,
      );
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // TÉLÉCHARGEMENT DES RÈGLES DE COHÉRENCE (arrière-plan, non bloquant)
  //
  // Télécharge les règles depuis data_rules.php et les stocke dans la table
  // coherence_rules de SQLite. Appelé une fois à l'ouverture d'un établissement.
  // Échoue silencieusement si hors ligne.
  //
  // Le yearCode est passé pour corriger l'absence de session PHP côté mobile
  // (corrigé en session 14).
  // ═══════════════════════════════════════════════════════════════════════════

  void _fetchAndStoreCoherenceRulesBackground({
    required String idCamp,
    required String idEtab,
    required String idSystem,
    required List<Question> questions,
  }) {
    // Fire and forget — les erreurs sont non fatales
    Future(() async {
      final yearCode = _codeyear ?? '';
      for (final q in questions) {
        try {
          // ── Récupération des règles depuis le serveur ──────────────────────
          final rules = await _api.fetchRules(
            login:    _api.login ?? '',
            campId:   idCamp,
            sysId:    idSystem,
            qstId:    q.idQst,
            etabId:   idEtab,
            filter:   _selectedFilter?.idFilter,
            yearCode: yearCode,  // ← correction session 14 : yearCode passé au serveur
          );

          // SESSION 57 — SYNC DICO_REGLE_THEME ──────────────────────────────
          // ROOT CAUSE du bug cohérence offline :
          //   ThemeRuleEngine lit ses règles depuis dico_regle_theme (table SQLite).
          //   Avant ce fix, seules les règles du JSON asset statique (id_theme ∈
          //   [900..1070]) étaient dans dico_regle_theme.
          //   Les thèmes Burundi (9802, 9902, 10002…) n'étaient jamais insérés →
          //   getDicoReglesByTheme(9802) retournait [] → contrôle offline silencieux.
          //
          // FIX : on appelle fetchDicoRegleTheme() qui retourne les règles au
          //   format dico_regle_theme avec l'id_theme réel (ex: 9802) fourni
          //   par le serveur dans se_data.id_theme.
          //   insertDicoRegleTheme() utilise REPLACE → idempotent.
          //   Le contrôle offline se déclenche ensuite avec les bonnes règles.
          final dicoRules = await _api.fetchDicoRegleTheme(
            login:    _api.login ?? '',
            campId:   idCamp,
            sysId:    idSystem,
            qstId:    q.idQst,
            etabId:   idEtab,
            filter:   _selectedFilter?.idFilter,
            yearCode: yearCode,
          );
          if (dicoRules.isNotEmpty) {
            await _db.insertDicoRegleTheme(dicoRules);
            final idThemeStored = dicoRules.first['id_theme'];
            debugPrint('[DataEntry] SESSION57: stored ${dicoRules.length} rules '
                'in dico_regle_theme for id_theme=$idThemeStored '
                '(qst=${q.idQst})');
          }
          // ──────────────────────────────────────────────────────────────────

          if (rules.isNotEmpty) {
            await _db.insertCoherenceRules(rules);
            debugPrint('[DataEntry] stored ${rules.length} offline coherence rules '
                'for qst=${q.idQst} etab=$idEtab year=$yearCode');

            // ── SYNC MESSAGES DICO_REGLE_THEME (messages uniquement) ──────────
            // updateDicoRegleMessages met à jour uniquement la colonne message
            // des règles déjà présentes (insérées par fetchDicoRegleTheme ci-dessus).
            final msgMap = <int, String>{};
            for (final r in rules) {
              if (!msgMap.containsKey(r.idRegle) && r.message.isNotEmpty) {
                msgMap[r.idRegle] = r.message;
              }
            }
            if (msgMap.isNotEmpty) {
              await _db.updateDicoRegleMessages(msgMap);
              debugPrint('[DataEntry] synced ${msgMap.length} messages '
                  'into dico_regle_theme for qst=${q.idQst}');
            }
            // ──────────────────────────────────────────────────────────────────

            // ── SESSION 59 FIX — SYNC DICO_REGLE_THEME_ASSOC ─────────────────
            // PROBLÈME RACINE COHÉRENCE OFFLINE :
            //   ThemeRuleEngine._evaluateWithAssoc() lit dico_regle_theme_assoc
            //   pour obtenir le sql_assoc de la règle associée (règles 404, 12,
            //   13, 16, 18, 23, 47, 48, 51 — issues d'autres thèmes, absentes de
            //   dico_regle_theme). Si dico_regle_theme_assoc est vide → toutes
            //   les règles MODE ASSOC (24/36 pour theme 10502) sont ignorées.
            //
            // FIX : convertir chaque CoherenceRule en Map pour insertDicoRegleThemeAssoc.
            //   fetchRules() a déjà extrait id_assoc, id_regle_assoc, sql_assoc,
            //   critere depuis associations[] du serveur (data_rules.php ligne 305).
            //   On les stocke ici dans dico_regle_theme_assoc (REPLACE — idempotent).
            final assocRows = rules.map((r) => {
              'id_assoc':             r.idAssoc,
              'id_regle_theme':       r.idRegle,
              'id_regle_theme_assoc': r.idRegleAssoc,
              'critere':              r.critere.isNotEmpty ? r.critere : '>',
              'activer_ctrl':         1,
              'sql_assoc':            r.sqlAssoc,
            }).toList();
            if (assocRows.isNotEmpty) {
              await _db.insertDicoRegleThemeAssoc(assocRows);
              debugPrint('[DataEntry] synced ${assocRows.length} assoc rows '
                  'into dico_regle_theme_assoc for qst=${q.idQst}');
            }
            // ──────────────────────────────────────────────────────────────────

            // ── Re-déclenche le contrôle offline si les règles viennent d'arriver
            // pour la question actuellement affichée.
            if (_selectedQuestion?.idQst == q.idQst &&
                !_isCheckingOffline) {
              debugPrint('[DataEntry] rules arrived for current question '
                  '(formData=${_formData.length} fields) — '
                  're-triggering offline coherence check');
              await checkCoherenceOffline();
            }
          } else if (dicoRules.isNotEmpty) {
            // Des règles dico_regle_theme ont été chargées même si coherence_rules
            // est vide (peut arriver si le serveur retourne des règles EXISTS sans
            // associations). Re-déclenchement si c'est la question courante.
            if (_selectedQuestion?.idQst == q.idQst && !_isCheckingOffline) {
              debugPrint('[DataEntry] dico rules arrived for current question '
                  '— re-triggering offline coherence check');
              await checkCoherenceOffline();
            }
          } else {
            debugPrint('[DataEntry] no offline coherence rules returned '
                'for qst=${q.idQst} etab=$idEtab year=$yearCode '
                '(normal if no rules configured in DB for this theme)');
          }
        } catch (e, st) {
          // SESSION 59 — LOG: non fatal mais loggé pour diagnostic cohérence offline
          debugPrint('[DataEntry] _fetchAndStoreCoherenceRulesBackground '
              'exception pour qst=${q.idQst}: $e\n$st');
        }
      }
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SÉLECTIONNER UNE QUESTION — charge le formulaire HTML + règles + données sauvegardées
  //
  // Réplique : stmPageEtab.initHtml() + initPageData()
  //
  // Workflow :
  //   1. Charge le HTML du formulaire depuis le cache SQLite
  //   2. Charge les règles de validation du thème
  //   3. Charge les données collectées sauvegardées pour (idCamp, idEtab, idQst)
  //   4. Pré-remplit les champs d'identification si c'est le premier thème
  //   5. Lance le rechargement automatique depuis le serveur en arrière-plan
  // ═══════════════════════════════════════════════════════════════════════════

  // ── Définir l'utilisateur courant ────────────────────────────────────────
  // Doit être appelé depuis SchoolDataScreen après initForSchool.
  // Mis en cache pour que selectQuestion puisse déclencher le rechargement
  // automatique depuis le serveur quand les données locales sont vides.
  void setCurrentUser(User? user) {
    _currentUser = user;
  }

  Future<void> selectQuestion(Question question) async {
    _selectedQuestion  = question;
    _selectedFilter    = null;
    _formData          = {};
    _validationErrors  = {};
    _hasUnsavedChanges = false;
    _error             = null;
    _successMessage    = null;
    // Détermine si c'est la première question (pour l'injection de LOC_REG_0)
    _isFirstQuestion = _questions.isNotEmpty &&
        _questions.first.idQst == question.idQst;
    _isLoading = true;
    notifyListeners();

    try {
      // Charge le HTML du formulaire depuis le cache SQLite
      _formHtml = await _db.getFormHtml(_idCamp!, question.idQst);
      final _htmlSnippet = _formHtml == null
          ? 'NULL'
          : 'len=${_formHtml!.length} '
            'snippet=${_formHtml!.length > 80 ? _formHtml!.substring(0, 80) : _formHtml}';
      debugPrint('[DataEntry] selectQuestion: idQst=${question.idQst} formHtml=$_htmlSnippet');
      // Charge les règles de validation du thème
      _rules    = await _db.getValidationRules(_idCamp!, question.idQst);
      // Charge les valeurs de champs sauvegardées depuis SQLite
      await _loadFormData(idFilter: null);
      _refreshCurrentSyncStatus();   // AK-SYNC

      // Pré-remplissage des champs d'identification.
      // Le formulaire d'identification (thème d'identification) contient des champs
      // déjà connus : nom, code administratif, statut, sous-secteur.
      // On pré-remplit pour la première question OU si le HTML contient des noms
      // de champs d'identification connus (fonctionne pour tout type de campagne).
      final htmlLower = (_formHtml ?? '').toLowerCase();
      final isIdentificationForm = _isFirstQuestion ||
          htmlLower.contains('nom_etablissement') ||
          htmlLower.contains('code_administratif') ||
          htmlLower.contains('nom_etab') ||
          htmlLower.contains('nom_eco');
      if (isIdentificationForm) {
        _prefillIdentificationFields();
      }

      // Rechargement automatique depuis le serveur :
      // - Toujours pour le formulaire d'identification (le serveur peut avoir
      //   des champs de dates comme DATE_CREATION_0 absent des données locales).
      //   Pour l'identification : forceOverwrite=true → les données serveur
      //   remplacent toutes les données locales (le serveur est source de vérité).
      // - Pour les autres formulaires : seulement quand les données locales
      //   sont vides (première ouverture).
      // Exécuté en arrière-plan pour ne pas bloquer l'affichage du formulaire.
      if (_currentUser != null) {
        if (isIdentificationForm || _formData.isEmpty) {
          // Pour le formulaire d'identification, forcer l'écrasement des données
          // locales par les données serveur (toujours à jour, source de vérité).
          _autoReloadFromServerBackground(
            forceOverwrite: isIdentificationForm,
          );
        }
      }

      // ── Contrôle de cohérence offline au changement de question ──────────
      // Si des données locales existent déjà pour ce formulaire, on lance
      // immédiatement le contrôle offline (en arrière-plan, non bloquant).
      // Cela garantit que le bandeau d'alerte est visible dès l'ouverture
      // d'un formulaire déjà saisi, sans attendre une nouvelle sauvegarde.
      // Note : si les règles ne sont pas encore chargées (fetch background en
      // cours), le re-déclenchement depuis _fetchAndStoreCoherenceRulesBackground
      // prendra le relais dès que les règles seront disponibles.
      if (_formData.isNotEmpty) {
        Future(() => checkCoherenceOffline());
      }

      // ── Questions conditionnelles (Fix #5) ─────────────────────────────
      // Parse les règles du formulaire courant et évalue l'état initial
      // des champs désactivés selon les données déjà chargées.
      _conditionalRules = _formHtml != null
          ? ConditionalRulesParser.parse(_formHtml!)
          : [];
      _disabledFields = _evaluateConditions(_formData, _conditionalRules);
    } catch (e) {
      _error = 'Erreur chargement formulaire : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ── Rechargement automatique depuis le serveur (arrière-plan) ─────────────
  // Appelé à l'ouverture d'une question quand :
  //   - Les données locales sont vides (première saisie), OU
  //   - C'est un formulaire d'identification (le serveur peut avoir des champs
  //     de dates non présents dans les données locales de l'établissement).
  //
  // En cas de succès : fusionne les données serveur dans _formData
  //   - Données locales vides : écrasement complet par les données serveur
  //   - Données locales présentes (formulaire d'identification) :
  //     n'écrase que les champs actuellement vides pour préserver les saisies
  //
  // En cas d'erreur : ignoré silencieusement (hors ligne ou pas de sauvegarde préalable)
  void _autoReloadFromServerBackground({bool forceOverwrite = false}) {
    final user      = _currentUser;
    final question  = _selectedQuestion;
    final idCamp    = _idCamp;
    final idEtab    = _idEtab;
    final idSystem  = _idSystem;
    // SESSION 58 — FIX KOSAVE : utilise le filtre effectif serveur
    // (voir _effectiveServerFilter) pour le rechargement depuis le serveur.
    // SQLite utilise toujours _selectedFilter?.idFilter (null si hasFilter==false).
    final idFilter  = _effectiveServerFilter;
    // Capture l'état des données locales au moment de l'appel.
    // forceOverwrite = true pour le formulaire d'identification : les données
    // serveur remplacent toujours les données locales (le serveur est source de vérité).
    final localWasEmpty = _formData.isEmpty || forceOverwrite;
    if (user == null || question == null || idCamp == null ||
        idEtab == null || idSystem == null) return;

    Future(() async {
      try {
        final serverFields = await _api.reloadData(
          login:   user.login,
          sysId:   idSystem,
          qstId:   question.idQst,
          campId:  idCamp,
          etabId:  idEtab,
          filter:  idFilter,
          yearCode: _codeyear ?? '',  // AK-SYNC U13 : année explicite (pluriannuel)
        );
        if (serverFields == null || serverFields.isEmpty) return;

        // AK-SYNC U12 — PROTECTION DES SAISIES LOCALES NON ENVOYÉES :
        // Avant, saveCollectedData(serverStr) remettait is_sent=0 et écrasait en SQLite
        // des champs modifiés localement (formulaire d'identification, forceOverwrite).
        // Désormais :
        //   • si le formulaire a des données locales EN ATTENTE (pending/failed/kosave),
        //     on ne touche PAS à SQLite — on ne fait que compléter en mémoire les champs vides ;
        //   • sinon (jamais saisi ou déjà synchronisé), on persiste les valeurs serveur
        //     et on les marque comme synchronisées (is_sent=1, statut 'sent') car
        //     elles SONT la vérité serveur.
        final syncKey = '${_codeyear ?? ''}|$idCamp|$idEtab|${question.idQst}|${_selectedFilter?.idFilter ?? ''}';
        final localPending = _syncStatuses[syncKey]?.status.needsSend == true;

        // Convertit les valeurs serveur en Map<String, String>
        // Le serveur retourne des tableaux [valeur, type] ex: [5, "text"] ou ["CODE_0_6","radio"]
        // On extrait v[0] et on normalise les IDs radio (même logique que reloadFromServer).
        // Sans ce traitement, v.toString() produit "[5, text]" au lieu de "5". (fix S18)
        final Map<String, String> serverStr = {};
        serverFields.forEach((k, v) {
          String strVal;
          if (v is List && v.isNotEmpty) {
            strVal = v[0]?.toString() ?? '';
            // Normalise les anciens identifiants radio : "CODE_TYPE_ACCES_0_6" → "6"
            if (v.length >= 2 && v[1].toString() == 'radio') {
              final lastUnder = strVal.lastIndexOf('_');
              if (lastUnder >= 0) {
                final lastSeg = strVal.substring(lastUnder + 1);
                if (RegExp(r'^\d+$').hasMatch(lastSeg)) strVal = lastSeg;
              }
            }
          } else {
            strVal = v?.toString() ?? '';
          }
          serverStr[k] = strVal;
        });
        // Sauvegarde en SQLite pour utilisation hors ligne — UNIQUEMENT si aucune
        // saisie locale n'attend d'être envoyée (U12). Les lignes écrites viennent du
        // serveur : elles sont marquées synchronisées (is_sent=1 / statut 'sent').
        // NOTE : la clé SQLite reste _selectedFilter?.idFilter (pas le filtre serveur
        // effectif) pour ne pas fragmenter les données locales — voir _effectiveServerFilter.
        if (!localPending) {
          final localFilter = _selectedFilter?.idFilter;
          await _db.saveCollectedData(
            idCamp:         idCamp,
            idEtab:         idEtab,
            idQst:          question.idQst,
            idFilter:       localFilter,
            data:           serverStr,
            codeTypeAnnee:  _codeyear ?? '',  // AK-YEAR-MULTI-01
          );
          await _db.markCollectedDataSent(
            idCamp: idCamp, idEtab: idEtab, idQst: question.idQst,
            idFilter: localFilter, codeTypeAnnee: _codeyear ?? '');
          final existing = _syncStatuses[syncKey];
          if (existing == null || existing.status == SyncStatus.sent) {
            await _db.recordSendAttempt(
              idCamp: idCamp, idEtab: idEtab, idQst: question.idQst,
              idFilter: localFilter, codeTypeAnnee: _codeyear ?? '',
              status: SyncStatus.sent, error: null);
            // recordSendAttempt incrémente attempts : acceptable (trace d'un rechargement).
          }
          await refreshSyncStatuses();
        } else {
          debugPrint('[DataEntry] _autoReloadFromServerBackground: saisies locales en attente '
              'pour qst=${question.idQst} → SQLite non écrasé (U12)');
        }
        // Met à jour en mémoire uniquement si la même question est toujours affichée
        if (_selectedQuestion?.idQst == question.idQst) {
          // Stratégie de fusion :
          // - Si les données locales étaient vides : remplissage complet (rechargement total)
          // - Si les données locales étaient présentes (formulaire d'identification) :
          //   ne remplit que les champs vides/manquants pour préserver les saisies
          // - U12 : si des saisies locales sont en attente, on ne remplit QUE les vides
          //   (jamais d'écrasement complet).
          bool changed = false;
          for (final entry in serverStr.entries) {
            if (localWasEmpty && !localPending) {
              // Écrasement complet — pas de saisies à protéger
              _formData[entry.key] = entry.value;
              changed = true;
            } else {
              // Remplissage conditionnel — préserve les saisies existantes
              final existing = _formData[entry.key];
              if (existing == null || existing.trim().isEmpty) {
                if (entry.value.isNotEmpty) {
                  _formData[entry.key] = entry.value;
                  changed = true;
                }
              }
            }
          }
          if (localWasEmpty && !localPending) {
            _hasUnsavedChanges = false;
          }
          if (changed) {
            debugPrint('[DataEntry] _autoReloadFromServerBackground: '
                'loaded ${serverStr.length} fields from server for qst=${question.idQst}');
            notifyListeners();
            // Relance la cohérence offline maintenant que les données serveur
            // sont fusionnées dans _formData (non bloquant).
            if (!_isCheckingOffline) {
              Future(() => checkCoherenceOffline());
            }
          }
        }
      } catch (_) {
        // Non fatal — hors ligne ou pas de sauvegarde préalable sur le serveur
        debugPrint('[DataEntry] _autoReloadFromServerBackground: no server data (normal if first entry)');
      }
    });
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRÉ-REMPLISSAGE DES CHAMPS D'IDENTIFICATION
  //
  // Injecte dans _formData les valeurs connues de l'établissement pour
  // le formulaire d'identification (premier thème / thème d'identification).
  // Ne remplit que les champs absents ou vides (respecte les valeurs sauvegardées).
  //
  // Les noms de champs reproduisent les DICO_CHAMP du serveur pour l'identification.
  //
  // Champs radio :
  //   Le HTML contient VALUE='CODE_TYPE_STATUT_ETABLISSEMENT_0_N' où N = idStatus.
  //   On pré-remplit la clé du groupe radio avec la valeur complète de l'option
  //   pour que _injectData() puisse faire : el.checked = (el.value === val).
  // ═══════════════════════════════════════════════════════════════════════════
  void _prefillIdentificationFields() {
    // Ne remplit un champ que si la source est non nulle/non vide
    // ET que le champ est absent OU actuellement vide/blanc.
    // Les valeurs sauvegardées non vides ont la priorité.
    void fill(String key, String? value) {
      if (value == null || value.trim().isEmpty) return;
      final existing = _formData[key];
      if (existing == null || existing.trim().isEmpty) {
        _formData[key] = value;
      }
    }

    // ── Nom de l'établissement ───────────────────────────────────────────
    // Le formulaire d'identification utilise le suffixe _0 (index de ligne 0)
    fill('NOM_ETABLISSEMENT_0',  _libEtab);   // formulaire d'identification mobile
    fill('NOM_ETABLISSEMENT',    _libEtab);   // fallback (autres formulaires)
    fill('LIB_ETABLISSEMENT',    _libEtab);
    fill('NOM_ETAB',             _libEtab);

    // ── Code administratif ────────────────────────────────────────────────
    fill('CODE_ADMINISTRATIF_0', _codeEtab);  // formulaire d'identification mobile
    fill('CODE_ETABLISSEMENT',   _codeEtab);
    fill('COD_ETAB',             _codeEtab);
    fill('CODE_ADMIN',           _codeEtab);

    // ── Statut / type ─────────────────────────────────────────────────────
    fill('STATUT',               _libStatus);
    fill('LIB_STATUT',           _libStatus);

    // ── Radio : Statut de l'établissement ─────────────────────────────────
    // La valeur radio est 'CODE_TYPE_STATUT_ETABLISSEMENT_0_N' où N = idStatus.
    // idStatus='1' → Public, '2' → Privé, '3' → Communautaire.
    // On pré-remplit la clé du groupe radio avec la valeur complète de l'option
    // pour que _injectData() puisse faire : el.checked = (el.value === val).
    if (_idStatus != null && _idStatus!.isNotEmpty) {
      final statusRadioVal = 'CODE_TYPE_STATUT_ETABLISSEMENT_0_$_idStatus';
      fill('CODE_TYPE_STATUT_ETABLISSEMENT_0', statusRadioVal);
      // Noms de champs alternatifs utilisés dans d'autres formulaires
      fill('CODE_STATUT_ETABLISSEMENT_0', 'CODE_STATUT_ETABLISSEMENT_0_$_idStatus');
    }

    // ── Sous-secteur ──────────────────────────────────────────────────────
    fill('SOUS_SECTEUR',         _libSubsector);
    fill('LIB_SOUS_SECTEUR',     _libSubsector);

    // ── Année scolaire ────────────────────────────────────────────────────
    fill('ANNEE_SCOLAIRE',       _libyear);
    fill('LIB_ANNEE',            _libyear);

    debugPrint('[DataEntry] _prefillIdentificationFields: etab=$_libEtab '
        'code=$_codeEtab idStatus=$_idStatus status=$_libStatus '
        'subsector=$_libSubsector year=$_libyear codeyear=$_codeyear');
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SÉLECTIONNER UN FILTRE — recharge les données pour la période sélectionnée
  // La sélection d'un nouveau filtre efface les données en mémoire et recharge
  // depuis SQLite pour la période correspondante.
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
      _refreshCurrentSyncStatus();   // AK-SYNC
    } finally {
      _isLoading = false;
      notifyListeners();
    }
    // Relance la cohérence offline pour la nouvelle période si des données existent
    if (_formData.isNotEmpty) {
      Future(() => checkCoherenceOffline());
    }
  }

  /// Charge les données collectées depuis SQLite pour la question et le filtre courants.
  Future<void> _loadFormData({String? idFilter}) async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) return;
    _formData = await _db.getCollectedData(
      idCamp:        _idCamp!,
      idEtab:        _idEtab!,
      idQst:         _selectedQuestion!.idQst,
      idFilter:      idFilter,
      codeTypeAnnee: _codeyear ?? '',  // AK-YEAR-MULTI-01
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // MISE À JOUR D'UN CHAMP — appelé par les widgets de formulaire à chaque changement
  //
  // Réplique : script.js ctrl_saisie / ctrl_saisie_text
  //
  // Met à jour _formData en mémoire, marque les modifications non sauvegardées,
  // et efface l'erreur de validation éventuelle pour ce champ.
  // ═══════════════════════════════════════════════════════════════════════════

  void updateField(String fieldName, String value) {
    _formData[fieldName] = value;
    _hasUnsavedChanges   = true;
    _validationErrors.remove(fieldName);

    // ── Réévaluation des questions conditionnelles (Fix #5) ──────────────
    // Si le champ modifié est un champ source d'une règle conditionnelle,
    // recalcule l'ensemble des champs désactivés et notifie immédiatement
    // pour que DynamicFormWidget injecte le nouveau JS disable.
    final baseFieldName = fieldName.replaceAll(RegExp(r'_\d+$'), '');
    final affectsConditional = _conditionalRules.any(
      (r) => r.sourceField == fieldName || r.sourceField.startsWith(baseFieldName),
    );
    if (affectsConditional) {
      _disabledFields = _evaluateConditions(_formData, _conditionalRules);
    }

    notifyListeners();
    // ── Déclenchement debounced de la cohérence offline — DÉSACTIVÉ (pilote) ──
    // Le contrôle offline ne se déclenche plus automatiquement à la saisie.
    // L'agent doit cliquer sur « Vérifier la cohérence » dans le menu pour
    // lancer checkCoherenceOffline() manuellement.
    // La fonction checkCoherenceOffline() est intacte — seul son déclencheur
    // automatique est neutralisé ici. (Modification pilote — ak_secure)
    debugPrint('[DataEntry] updateField: $fieldName = "$value" '
        '(${_formData.length} champs en mémoire) '
        '— déclenchement auto désactivé, contrôle manuel uniquement');
  }

  // ── Évaluation des champs désactivés (Fix #5) ─────────────────────────────
  //
  // Pour chaque règle conditionnelle :
  //   • On lit la valeur actuelle du champ source dans [data].
  //   • Si la valeur correspond à [rule.triggerValue] → les targetFields
  //     sont ACTIVÉS (retirés de l'ensemble disabled).
  //   • Si la valeur NE correspond PAS à [rule.triggerValue], OU si elle est
  //     vide (aucune sélection) → les targetFields sont DÉSACTIVÉS.
  //
  // Convention de stockage des radios dans _formData :
  //   Les radios Oui/Non sont stockés avec le nom complet de l'input
  //   ex. "ELECTRICITE_0_1" → value "1" quand Oui est coché
  //   Mais la convention du pont JS (FieldChanged) envoie :
  //     name = "ELECTRICITE_0_1"  value = "1"   (radio coché)
  //   Donc _formData peut contenir :
  //     "ELECTRICITE_0_1" = "1"    (Oui coché)
  //     "ELECTRICITE_0_0" = "0"    ← NON, le pont envoie value de l'option cochée
  //
  //   En réalité le pont envoie : name = NAME complet ex. "ELECTRICITE_0"
  //   et value = la valeur de l'option cochée (ex. "1" ou "0").
  //   Voir _injectBridge() dans dynamic_form_widget.dart :
  //     if (el.checked) notify(name, el.value);
  //   Donc _formData["ELECTRICITE_0"] = "1" quand Oui est coché.
  //
  //   Mais les IDs d'option dans le HTML sont ELECTRICITE_0_1 / ELECTRICITE_0_0,
  //   et VALUE=$ELECTRICITE_0_1 (non résolu → remplacé par "1" par _preprocessHtml).
  //   Le NAME de l'input est "ELECTRICITE_0" (le champ source de notre règle).
  //
  // Résolution : on cherche la valeur dans _formData en testant :
  //   1. data[rule.sourceField]               → ex. data["ELECTRICITE_0"] = "1"
  //   2. data[rule.sourceField + "_0"]        → (cas où suffix _0 manquant)
  //   3. Parcours de toutes les clés commençant par rule.sourceField
  //
  Set<String> _evaluateConditions(
    Map<String, String> data,
    List<ConditionalRule> rules,
  ) {
    final disabled = <String>{};
    for (final rule in rules) {
      // Recherche de la valeur courante du champ source
      String? currentValue = data[rule.sourceField];

      // Fallback : cherche avec suffixe _0
      currentValue ??= data['${rule.sourceField}_0'];

      // Fallback : parcourt les clés qui commencent par le sourceField
      if (currentValue == null) {
        for (final entry in data.entries) {
          if (entry.key.startsWith(rule.sourceField) && entry.value.isNotEmpty) {
            currentValue = entry.value;
            break;
          }
        }
      }

      if (currentValue == null || currentValue.isEmpty) {
        // Aucune sélection → désactiver les dépendants (conservateur)
        disabled.addAll(rule.targetFields);
      } else if (currentValue != rule.triggerValue) {
        // Sélection différente du trigger → désactiver
        disabled.addAll(rule.targetFields);
      }
      // Si currentValue == rule.triggerValue → activer (ne pas ajouter à disabled)
    }
    return disabled;
  }

  /// Valide un seul champ selon ses règles.
  /// Retourne un message d'erreur en français ou null si valide.
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
  // VALIDER TOUS LES CHAMPS — avant sauvegarde ou envoi
  // Vérifie d'abord les champs obligatoires (mandatory), puis les types/plages
  // sur les champs remplis. Retourne vrai si tout est valide.
  // ═══════════════════════════════════════════════════════════════════════════

  bool validateAll() {
    _validationErrors = {};
    bool isValid = true;
    // Vérifie les champs obligatoires en premier
    for (final rule in _rules) {
      if (rule.ruleType == 'mandatory') {
        final val = _formData[rule.idChamp] ?? '';
        if (val.trim().isEmpty) {
          _validationErrors[rule.idChamp] = 'Ce champ est obligatoire';
          isValid = false;
        }
      }
    }
    // Vérifie le type/plage sur les champs remplis
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
  // SAUVEGARDER LOCALEMENT — réplique stmPageEtab.savePage()
  //
  // Persiste les données du formulaire courant dans la table collected_data
  // de SQLite. Après la sauvegarde, lance le contrôle de cohérence offline
  // en arrière-plan pour signaler d'éventuelles violations avant l'envoi.
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
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        data:          _formData,
        codeTypeAnnee: _codeyear ?? '',  // AK-YEAR-MULTI-01
      );
      // AK-SYNC U04 : le formulaire (re)passe en attente d'envoi
      await _db.markFormLocallyModified(
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        codeTypeAnnee: _codeyear ?? '',
      );
      await refreshSyncStatuses();
      _hasUnsavedChanges = false;
      _successMessage    = 'Données sauvegardées localement';
      notifyListeners();
      // Lance le contrôle de cohérence offline en arrière-plan après la sauvegarde
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
  // AK-YEAR-MULTI-02 — VÉRIFICATION COHÉRENCE ANNÉE MOBILE vs SERVEUR
  //
  // Compare l'année active mobile (_codeyear) avec l'année active du serveur
  // ($_SESSION['annee'] retourné par annees_ws.php/active/:login).
  //
  // POLITIQUE :
  //   • MISMATCH CONFIRMÉ (serveur répond, codes différents) → BLOQUER.
  //     C'est le seul cas où l'envoi doit être refusé pour éviter de polluer
  //     la base serveur avec des données d'une autre année.
  //   • SERVEUR INJOIGNABLE / TIMEOUT / ERREUR RÉSEAU → LAISSER PASSER.
  //     L'intranet MEN peut être temporairement lent. Bloquer l'envoi à cause
  //     d'un timeout n'est pas acceptable en production terrain.
  //   • ANNÉE MOBILE NON DÉFINIE → BLOQUER (cas de configuration manquante).
  //
  // TIMEOUT : fetchServerActiveYear utilise un timeout court dédié (8 s)
  // indépendant du connectTimeout global Dio (60 s). En cas de timeout,
  // DioException ou TimeoutException est levée → catch général → fail-open.
  //
  // Retourne true = opération autorisée, false = opération bloquée (_error défini).
  // ═══════════════════════════════════════════════════════════════════════════
  Future<bool> _checkYearConsistency(User user) async {
    // Si l'année mobile n'est pas définie, on ne peut pas opérer → bloquer
    final mobileCode = int.tryParse(_codeyear ?? '') ?? 0;
    if (mobileCode <= 0) {
      _error = 'Année de collecte non définie sur le mobile. '
               'Sélectionnez une année dans Paramètres puis réessayez.\n'
               'Collection year not defined on device. '
               'Please select a year in Settings and try again.';
      notifyListeners();
      return false;
    }

    try {
      final serverYear = await _api.fetchServerActiveYear(user.login);
      if (serverYear.code != mobileCode) {
        // ── Vrai mismatch confirmé par le serveur → BLOQUER ─────────────────
        _error = 'Incohérence d\'année de collecte :\n'
                 '• Mobile : $_libyear (code $mobileCode)\n'
                 '• Serveur : ${serverYear.libelle} (code ${serverYear.code})\n'
                 'Sélectionnez la même année que le serveur dans Paramètres, '
                 'puis réessayez.\n\n'
                 'Year mismatch:\n'
                 '• Device: $_libyear (code $mobileCode)\n'
                 '• Server: ${serverYear.libelle} (code ${serverYear.code})\n'
                 'Please match the server year in Settings and try again.';
        notifyListeners();
        return false;
      }
      // Années identiques → laisser passer
      debugPrint('[DataEntry] _checkYearConsistency: OK '
          '(mobile=$mobileCode server=${serverYear.code})');
      return true;

    } on ApiException catch (e) {
      // ── Erreur métier serveur (année non définie en session) ─────────────
      // L'endpoint a répondu mais avec se_status=KO.
      // Dans ce cas, le serveur est UP mais mal configuré → LAISSER PASSER
      // (on ne peut pas savoir s'il y a un mismatch, mais l'envoi peut réussir).
      debugPrint('[DataEntry] _checkYearConsistency: ApiException → fail-open: ${e.message}');
      return true;

    } catch (e) {
      // ── Timeout, erreur réseau, DioException ─────────────────────────────
      // Le serveur est inaccessible (réseau lent, hors ligne, pare-feu).
      // On ne peut PAS confirmer un mismatch → LAISSER PASSER (fail-open).
      // L'envoi lui-même échouera si le serveur est vraiment HS.
      debugPrint('[DataEntry] _checkYearConsistency: réseau KO → fail-open: $e');
      return true;
    }
  }

  // ENVOYER AU SERVEUR — réplique stmPageEtab.saveQstOnServer()
  //
  // POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
  //
  // IMPORTANT : utilise user.login (PAS user.idUser) !
  // Pour la première question : injecte LOC_REG_0={etab.idRegroup} si absent.
  //
  // Workflow :
  //   1. Vérification cohérence année (AK-YEAR-MULTI-02)
  //   2. Sauvegarde locale (assure la persistance)
  //   3. POST au serveur
  //   4. Si succès : marque is_sent=1 dans SQLite + contrôle de cohérence serveur
  //   5. Si échec : _error avec message
  //
  // Le yearCode est injecté dans l'URL pour contourner l'absence de session
  // PHP côté mobile.
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
    // AK-SYNC U25 : un seul envoi à la fois, tous écrans confondus
    if (_globalSendLock) {
      _error = 'Un envoi est déjà en cours. Patientez puis réessayez.';
      notifyListeners();
      return false;
    }
    // AK-SYNC U20/U21 : formulaire verrouillé par l'administration → pas d'envoi
    if (isCurrentFormLocked) {
      _error = 'Ce formulaire a été validé/verrouillé par l\'administration. '
               'Rechargez-le depuis le serveur pour consulter les données officielles.';
      notifyListeners();
      return false;
    }

    // AK-YEAR-MULTI-02 : vérification cohérence année avant tout envoi
    if (!await _checkYearConsistency(user)) return false;

    // Sauvegarde locale d'abord (assure la persistance même si l'envoi échoue)
    await saveLocally();

    _globalSendLock   = true;
    _isSending        = true;
    _sendAttempt      = 1;  // première tentative
    _error            = null;
    _successMessage   = null;
    _warningMessage   = null;  // SESSION 53 : réinitialise l'avertissement KOSAVE
    _lastAck          = null;
    notifyListeners();

    try {
      // SESSION 58 — FIX KOSAVE 10502 : utilise _effectiveServerFilter
      // au lieu de _selectedFilter?.idFilter pour éviter que PHP utilise
      // une valeur résiduelle de $_SESSION['filtre'] (voir getter doc).
      final serverFilter = _effectiveServerFilter;
      debugPrint('[DataEntry] sendToServer: selectedFilter=${_selectedFilter?.idFilter} '
          'effectiveServerFilter=$serverFilter '
          '(filterPeriods=${_filterPeriods.length})');

      final outcome = await _sendOneForm(
        user:           user,
        idCamp:         _idCamp!,
        idSystem:       _idSystem!,
        idQst:          _selectedQuestion!.idQst,
        idEtab:         _idEtab!,
        localFilter:    _selectedFilter?.idFilter,
        serverFilter:   serverFilter,
        formData:       _formData,
        etabRegroupId:  _idRegroupEtab,
        isFirstQuestion: _isFirstQuestion,
        onRetry: (attempt) {
          _sendAttempt = attempt + 1;
          notifyListeners();
        },
      );
      _lastAck = outcome.ack;
      await refreshSyncStatuses();

      switch (outcome.kind) {
        case SendOutcomeKind.ok:
          final ack = outcome.ack;
          _successMessage = 'Données envoyées avec succès'
              '${ack != null && ack.duplicate ? ' (déjà reçues par le serveur)' : ''}'
              '${ack != null && ack.summary.isNotEmpty ? ' — ${ack.summary}' : ''}';
          if (ack?.correctionCleared == true) {
            _successMessage = '$_successMessage\nLa demande de correction a été levée.';
          }
          if (ack?.warning == 'NO_DELETE_GUARD') {
            _warningMessage = '⚠ Le serveur a conservé ${ack!.nbDeletePrevented ?? 0} ligne(s) '
                'absente(s) de votre envoi (protection anti-suppression).';
          }
          notifyListeners();

          // ── Contrôle de cohérence serveur (non bloquant) ────────────────
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
              filter:   serverFilter,
              yearCode: _codeyear ?? user.codeyear,
            );
          } catch (_) {
            _coherenceErrors = [];
          } finally {
            _isCheckingCoherence = false;
            notifyListeners();
          }
          return true;

        case SendOutcomeKind.kosave:
          // AK-SYNC U02 : KOSAVE n'est PLUS marqué comme envoyé. Le formulaire reste
          // en attente (statut 'kosave') et sera proposé au renvoi.
          _warningMessage = '⚠ Données reçues par le serveur mais NON enregistrées '
              '(thème ${outcome.idQst}). Elles restent en attente d\'envoi.'
              '${outcome.message != null && outcome.message!.isNotEmpty ? '\nDétail : ${outcome.message}' : ''}';
          notifyListeners();
          return false;

        case SendOutcomeKind.locked:
          _error = outcome.message ?? 'Formulaire verrouillé par l\'administration.';
          notifyListeners();
          return false;

        case SendOutcomeKind.rejected:
        case SendOutcomeKind.yearMismatch:
        case SendOutcomeKind.networkError:
          _error = outcome.message ?? 'Échec de l\'envoi. Réessayez.';
          notifyListeners();
          return false;

        case SendOutcomeKind.noData:
        case SendOutcomeKind.skipped:
          _error = 'Aucune donnée à envoyer.';
          notifyListeners();
          return false;
      }
    } finally {
      _isSending      = false;
      _sendAttempt    = 0;   // remet à zéro : overlay "Tentative N/3" disparaît
      _globalSendLock = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AK-SYNC — PRIMITIVE D'ENVOI D'UN FORMULAIRE (U02, U06, U11, U20)
  //
  // Point unique d'appel de ApiService.saveData() pour TOUS les chemins d'envoi
  // (unitaire, école, campagne, « en attente »). Garantit :
  //   • un sync_id d'idempotence par tentative logique (conservé sur les retries) ;
  //   • l'enregistrement systématique du résultat dans form_sync_status
  //     (sent / failed / kosave / rejected / locked) + is_sent compat ;
  //   • KOSAVE ≠ envoyé (U02) ;
  //   • un résultat typé (SendOutcome) au lieu d'un bool (U06).
  //
  // [localFilter]  : clé SQLite (période sélectionnée ou null)
  // [serverFilter] : filtre transmis au serveur (peut différer — cf. _effectiveServerFilter)
  // ═══════════════════════════════════════════════════════════════════════════
  Future<SendOutcome> _sendOneForm({
    required User user,
    required String idCamp,
    required String idSystem,
    required String idQst,
    required String idEtab,
    required String? localFilter,
    required String? serverFilter,
    required Map<String, String> formData,
    String? etabRegroupId,
    bool isFirstQuestion = false,
    void Function(int attempt)? onRetry,
  }) async {
    final annee = _codeyear ?? '';
    if (formData.isEmpty) {
      return SendOutcome(kind: SendOutcomeKind.noData, idEtab: idEtab, idQst: idQst, idFilter: localFilter);
    }
    final syncId = ApiService.newSyncId();
    try {
      final ok = await _api.saveData(
        login:           user.login,
        campId:          idCamp,
        sysId:           idSystem,
        qstId:           idQst,
        etabId:          idEtab,
        filter:          serverFilter,
        formData:        formData,
        etabRegroupId:   etabRegroupId,
        isFirstQuestion: isFirstQuestion,
        yearCode:        annee.isNotEmpty ? annee : user.codeyear,
        onRetry:         onRetry,
        syncId:          syncId,
      );
      final ack = _api.lastAck;
      if (ok) {
        await _db.recordSendAttempt(
          idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
          codeTypeAnnee: annee, status: SyncStatus.sent, syncId: syncId, ack: ack,
          adminStatus: ack?.correctionCleared == true ? 'RECU' : null);
        return SendOutcome(kind: SendOutcomeKind.ok, idEtab: idEtab, idQst: idQst, idFilter: localFilter, ack: ack);
      }
      // saveData() ne retourne plus false silencieusement (U01) — sécurité :
      await _db.recordSendAttempt(
        idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
        codeTypeAnnee: annee, status: SyncStatus.failed, syncId: syncId,
        error: 'Réponse serveur inattendue');
      return SendOutcome(kind: SendOutcomeKind.networkError, idEtab: idEtab, idQst: idQst,
          idFilter: localFilter, message: 'Réponse serveur inattendue');
    } on KosaveException catch (e) {
      final ack = _api.lastAck;
      await _db.recordSendAttempt(
        idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
        codeTypeAnnee: annee, status: SyncStatus.kosave, syncId: syncId, ack: ack,
        error: e.hint ?? 'KOSAVE');
      return SendOutcome(kind: SendOutcomeKind.kosave, idEtab: idEtab, idQst: idQst,
          idFilter: localFilter, message: e.hint, ack: ack);
    } on FormLockedException catch (e) {
      await _db.recordSendAttempt(
        idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
        codeTypeAnnee: annee, status: SyncStatus.locked, syncId: syncId,
        error: e.toString(), adminStatus: e.statut, adminComment: e.commentaire);
      return SendOutcome(kind: SendOutcomeKind.locked, idEtab: idEtab, idQst: idQst,
          idFilter: localFilter, message: e.toString());
    } on ApiException catch (e) {
      // Rejet métier (400 : année, accès, campagne) → 'rejected' ; réseau/timeout → 'failed'
      final isReject = e.code != null ||
          e.message.contains('Annee incorrecte') ||
          e.message.contains('acc') && e.message.contains('campagne') ||
          e.message.contains('pas ouverte');
      await _db.recordSendAttempt(
        idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
        codeTypeAnnee: annee, status: isReject ? SyncStatus.rejected : SyncStatus.failed,
        syncId: syncId, error: e.message);
      return SendOutcome(kind: isReject ? SendOutcomeKind.rejected : SendOutcomeKind.networkError,
          idEtab: idEtab, idQst: idQst, idFilter: localFilter, message: e.message);
    } catch (e) {
      await _db.recordSendAttempt(
        idCamp: idCamp, idEtab: idEtab, idQst: idQst, idFilter: localFilter,
        codeTypeAnnee: annee, status: SyncStatus.failed, syncId: syncId, error: e.toString());
      return SendOutcome(kind: SendOutcomeKind.networkError, idEtab: idEtab, idQst: idQst,
          idFilter: localFilter, message: e.toString());
    }
  }

  /// Filtre serveur effectif pour un formulaire donné en batch (mirror de
  /// _effectiveServerFilter) : période locale si présente, sinon première période
  /// de la campagne si la question est périodique, sinon null.
  String? _serverFilterFor(String? localFilter, {required bool hasFilter}) {
    if (localFilter != null && localFilter.isNotEmpty) return localFilter;
    if (_filterPeriods.isNotEmpty && hasFilter) return _filterPeriods.first.idFilter;
    if (_filterPeriods.isNotEmpty) return _filterPeriods.first.idFilter; // même règle que _effectiveServerFilter
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CONTRÔLE DE COHÉRENCE HORS LIGNE
  //
  // Évalue les règles de cohérence stockées localement contre collected_data.
  // Peut être appelé après saveLocally() ou manuellement depuis l'interface.
  // Non bloquant : les violations sont exposées via offlineCoherenceErrors.
  //
  // La logique d'évaluation est dans CoherenceEvaluator.evaluate() qui applique
  // les opérateurs SQL (<=, >=, =, <, >) via _applyOperator.
  // RAPPEL : _applyOperator retourne true quand la règle EST VIOLÉE.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> checkCoherenceOffline() async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) return;
    _isCheckingOffline      = true;
    _offlineCoherenceErrors = [];   // Moteur paire désactivé (conservé pour compatibilité)
    _themeCoherenceErrors   = [];
    notifyListeners();

    try {
      // ── Moteur UNIQUE : ThemeRuleEngine (DICO_REGLE_THEME) ───────────────
      //
      // Source de vérité : les règles viennent de la BD serveur dont l'Excel
      // fichier_incohérence_mobile.xlsx est une extraction directe.
      // Le moteur paire (CoherenceEvaluator) est désactivé pour éviter les
      // doublons et les résultats contradictoires.
      //
      // Mapping idQst → idTheme :
      //   "900"   → 900  (direct)
      //   "9001"  → 900  (sous-thème, troncature 3 chiffres)
      //   "10001" → 1000 (sous-thème, troncature 4 chiffres)
      //   "1050"  → 1050 (direct 4 chiffres)
      final idThemeStr  = _selectedQuestion!.idQst;
      final idThemeFull = int.tryParse(idThemeStr) ?? 0;
      final idTheme     = _normalizeIdTheme(idThemeFull);

      // SESSION 59 — LOG DIAGNOSTIC cohérence offline
      debugPrint('[checkCoherenceOffline] idQst=$idThemeStr → idTheme=$idTheme '
          'formData=${_formData.length} champs '
          'camp=$_idCamp etab=$_idEtab filter=${_selectedFilter?.idFilter}');

      if (idTheme > 0) {
        // SESSION 59 — diagnostic : combien de règles dans SQLite pour ce thème ?
        final rulesInDb = await _db.getDicoReglesByTheme(idTheme);
        debugPrint('[checkCoherenceOffline] dico_regle_theme: ${rulesInDb.length} '
            'règles actives pour idTheme=$idTheme '
            '(ids=${rulesInDb.map((r) => r['id_regle']).toList()})');

        _themeCoherenceErrors = await _themeEngine.evaluateTheme(
          idTheme:       idTheme,
          idCamp:        _idCamp!,
          idEtab:        _idEtab!,
          idQst:         _selectedQuestion!.idQst,
          idFilter:      _selectedFilter?.idFilter,
          codeEtab:      _codeEtab,
          codeTypeAnnee: _codeyear,
          formData:      _formData,
        );
        debugPrint('[DataEntry] ThemeRuleEngine: '
            '${_themeCoherenceErrors.length} violation(s) found '
            '(idTheme=$idTheme)');
      } else {
        debugPrint('[DataEntry] idTheme inconnu depuis idQst=$idThemeStr '
            '— ThemeRuleEngine skipped');
      }

    } catch (e) {
      debugPrint('[DataEntry] checkCoherenceOffline error: $e');
      _themeCoherenceErrors = [];
    } finally {
      _isCheckingOffline = false;
      notifyListeners();
    }
  }

  // ── Normalise un idQst numérique vers l'id_theme DICO_REGLE_THEME ──────────
  //
  // SESSION 57 — Suppression de la whitelist _knownThemes
  // ROOT CAUSE du bug cohérence offline :
  //   L'ancienne implémentation filtrait les idTheme via une liste statique
  //   [900..1070]. Les thèmes Burundi (9802, 9902, 10002, 10102…) n'y étaient
  //   pas → _normalizeIdTheme retournait la valeur brute mais ThemeRuleEngine
  //   skippait si les règles n'étaient pas dans dico_regle_theme.
  //
  // Nouveau comportement : on retourne directement raw si > 0.
  //   ThemeRuleEngine.evaluateTheme(9802) cherche dans dico_regle_theme
  //   les règles avec id_theme=9802 (qui y sont maintenant grâce au fix
  //   fetchDicoRegleTheme dans _fetchAndStoreCoherenceRulesBackground).
  //   Si aucune règle → liste vide → 0 violations (correct).
  //
  // Cas :
  //   "9802"  → 9802 (direct — règles chargées depuis serveur)
  //   "98021" → on essaie troncature 4 chiffres → 9802 ✓
  //   "900"   → 900  (direct — règles dans asset JSON)
  //   "9001"  → troncature 3 chiffres → 900 ✓
  //   "0"     → 0    (inconnu — skippé)
  //

  int _normalizeIdTheme(int raw) {
    if (raw <= 0) return 0;
    // Retourne directement — ThemeRuleEngine retournera [] si pas de règles
    // (comportement correct : 0 violations = pas de règles configurées)
    return raw;
  }

  // Garde la méthode de troncature pour un usage futur si nécessaire
  // ignore: unused_element
  int _normalizeIdThemeLegacy(int raw) {
    if (raw <= 0) return 0;
    final s = raw.toString();
    for (int len in [4, 3]) {
      if (s.length > len) {
        final candidate = int.tryParse(s.substring(0, len));
        if (candidate != null) return candidate;
      }
    }
    return raw;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RECHARGER DEPUIS LE SERVEUR — réplique stmPageEtab.reloadQstFromServer()
  //
  // GET /data_reload.php/theme_data/{login}/{sysId}/{qstId}/{campId}/{etabId}/{filter}
  // IMPORTANT : utilise user.login (PAS user.idUser) !
  //
  // Réponse : { fieldName: [value, type], ... }
  //   Champs radio : stockés comme fieldName#optionId = '1'
  //
  // Remplace complètement les données locales par les données du serveur,
  // puis marque les données comme envoyées (is_sent=1).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> reloadFromServer({required User user}) async {
    if (_idCamp    == null ||
        _idEtab    == null ||
        _idSystem  == null ||
        _selectedQuestion == null) {
      return false;
    }

    // AK-YEAR-MULTI-02 : vérification cohérence année avant rechargement serveur
    if (!await _checkYearConsistency(user)) return false;

    _isReloading    = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      // Rechargement depuis le serveur (la conversion des champs radio est faite dans api_service)
      final serverFields = await _api.reloadData(
        login:    user.login,     // ← utilise login, pas idUser !
        sysId:    _idSystem!,
        qstId:    _selectedQuestion!.idQst,
        campId:   _idCamp!,
        etabId:   _idEtab!,
        filter:   _selectedFilter?.idFilter,
        yearCode: _codeyear ?? '',  // AK-SYNC U13 : année explicite (pluriannuel)
      );

      if (serverFields == null) {
        _error = 'Aucune donnée retournée par le serveur';
        notifyListeners();
        return false;
      }

      // Convertit Map<String, dynamic> → Map<String, String> pour la DB locale
      // Le serveur retourne des tableaux [valeur, type] ex: ["CODE_TYPE_ACCES_0_6","radio"]
      // On extrait v[0] et on normalise les IDs radio style ancien "CHAMP_0_6" → "6"
      final Map<String, String> serverFieldsStr = {};
      serverFields.forEach((k, v) {
        String strVal;
        if (v is List && v.isNotEmpty) {
          strVal = v[0]?.toString() ?? '';
          // Normalise les anciens identifiants radio : "CODE_TYPE_ACCES_0_6" → "6"
          if (v.length >= 2 && v[1].toString() == 'radio') {
            final lastUnder = strVal.lastIndexOf('_');
            if (lastUnder >= 0) {
              final lastSeg = strVal.substring(lastUnder + 1);
              if (RegExp(r'^\d+$').hasMatch(lastSeg)) strVal = lastSeg;
            }
          }
        } else {
          strVal = v?.toString() ?? '';
        }
        serverFieldsStr[k] = strVal;
      });

      // Remplace les données locales par les données serveur
      await _db.deleteCollectedData(
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        codeTypeAnnee: _codeyear ?? '',  // AK-YEAR-MULTI-01
      );
      await _db.saveCollectedData(
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        data:          serverFieldsStr,
        codeTypeAnnee: _codeyear ?? '',  // AK-YEAR-MULTI-01
      );
      // Marque comme synchronisé (données = serveur)
      await _db.markCollectedDataSent(
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        codeTypeAnnee: _codeyear ?? '',  // AK-YEAR-MULTI-01
      );
      // AK-SYNC U04/U17 : le formulaire local = copie du serveur → statut 'sent'
      // (lève aussi un éventuel statut kosave/failed local, mais PAS un verrou admin :
      //  recordSendAttempt ne touche pas admin_status quand adminStatus est null).
      await _db.recordSendAttempt(
        idCamp:        _idCamp!,
        idEtab:        _idEtab!,
        idQst:         _selectedQuestion!.idQst,
        idFilter:      _selectedFilter?.idFilter,
        codeTypeAnnee: _codeyear ?? '',
        status:        SyncStatus.sent,
      );
      await refreshSyncStatuses();

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
  // AK-SYNC — MOTEUR D'ENVOI GLOBAL (U03, U06, U07)
  //
  // Un seul moteur pour « Envoyer tous les formulaires » (école / campagne) et
  // « Envoyer uniquement les formulaires en attente ». Corrige les défauts des
  // anciens batchs :
  //   • itère sur (etab, qst, id_filter) → les thèmes PÉRIODIQUES sont inclus (U03) ;
  //   • transmet filter / etabRegroupId / isFirstQuestion comme sendToServer() ;
  //   • passe par _sendOneForm() → statut persistant, KOSAVE ≠ envoyé, sync_id ;
  //   • retourne une liste typée SendOutcome (U06) en plus de la Map<String,bool>
  //     historique (compatibilité des écrans existants).
  //
  // [forms]      : liste de {id_etab, id_qst, id_filter('' si aucune)}
  // [onProgress] : (envoyés, total)
  // ═══════════════════════════════════════════════════════════════════════════
  Future<List<SendOutcome>> _sendForms({
    required User user,
    required String idCamp,
    required String idSystem,
    required List<Map<String, String>> forms,
    void Function(int sent, int total)? onProgress,
  }) async {
    final outcomes = <SendOutcome>[];
    if (forms.isEmpty) return outcomes;
    if (_globalSendLock) {
      _error = 'Un envoi est déjà en cours. Patientez puis réessayez.';
      notifyListeners();
      return outcomes;
    }
    // Métadonnées des questions (hasFilter, première question) pour ce système
    List<Question> questions = _questions;
    if (questions.isEmpty || _idSystem != idSystem || _idCamp != idCamp) {
      try { questions = await _db.getQuestions(idCamp, idSystem); } catch (_) {}
    }
    if (_filterPeriods.isEmpty) {
      try { _filterPeriods = await _db.getFilterPeriods(idCamp); } catch (_) {}
    }
    final firstQst = questions.isNotEmpty ? questions.first.idQst : null;
    final hasFilterByQst = {for (final q in questions) q.idQst: q.hasFilter};
    // Feedbacks (verrous) de la campagne pour éviter des rejets inutiles
    final feedbacks = await _db.getSyncFeedback(idCamp, codeTypeAnnee: _codeyear ?? '');
    // Regroupement des écoles (LOC_REG_0) — lu une fois
    final schools = await _db.getSchools(idCamp);
    final regroupByEtab = {for (final s in schools) s.idEtab: s.idRegroup};
    // Libellés nominatifs pour le dialogue de résultat (U06)
    final libEtabById = {for (final s in schools) s.idEtab: s.libEtab};
    final libQstById  = {for (final q in questions) q.idQst: q.libQst};
    SendOutcome labeled(SendOutcome o) =>
        o.withLabels(libEtab: libEtabById[o.idEtab], libQst: libQstById[o.idQst]);

    final total = forms.length;
    int done = 0;
    _globalSendLock = true;
    _isSending      = true;
    _error          = null;
    _successMessage = null;
    _warningMessage = null;
    notifyListeners();
    try {
      for (final f in forms) {
        final etabId  = f['id_etab']!;
        final qstId   = f['id_qst']!;
        final rawF    = f['id_filter'] ?? '';
        final localFilter = rawF.isEmpty ? null : rawF;

        // Verrou admin connu localement → on n'envoie pas (évite REJET_VERROU)
        final locked = feedbacks.any((fb) => fb.idEtab == etabId && fb.isLock && fb.appliesTo(qstId, localFilter));
        if (locked) {
          outcomes.add(labeled(SendOutcome(kind: SendOutcomeKind.locked, idEtab: etabId, idQst: qstId,
              idFilter: localFilter, message: 'Verrouillé par l\'administration')));
          done++; onProgress?.call(done, total); continue;
        }

        final data = await _db.getCollectedData(
          idCamp: idCamp, idEtab: etabId, idQst: qstId, idFilter: localFilter,
          codeTypeAnnee: _codeyear ?? '');
        if (data.isEmpty) {
          outcomes.add(labeled(SendOutcome(kind: SendOutcomeKind.noData, idEtab: etabId, idQst: qstId, idFilter: localFilter)));
          done++; onProgress?.call(done, total); continue;
        }
        final outcome = await _sendOneForm(
          user:            user,
          idCamp:          idCamp,
          idSystem:        idSystem,
          idQst:           qstId,
          idEtab:          etabId,
          localFilter:     localFilter,
          serverFilter:    _serverFilterFor(localFilter, hasFilter: hasFilterByQst[qstId] ?? false),
          formData:        data,
          etabRegroupId:   regroupByEtab[etabId],
          isFirstQuestion: firstQst != null && firstQst == qstId,
        );
        outcomes.add(labeled(outcome));
        done++; onProgress?.call(done, total);
      }
      _lastBatchOutcomes = outcomes;
      final ok   = outcomes.where((o) => o.isSuccess).length;
      final ko   = outcomes.where((o) => o.isFailure).length;
      final skip = outcomes.where((o) => o.kind == SendOutcomeKind.noData || o.kind == SendOutcomeKind.skipped).length;
      _successMessage = '$ok formulaire(s) envoyé(s) avec succès'
          '${ko > 0 ? ', $ko en échec' : ''}${skip > 0 ? ', $skip sans données' : ''}.';
      notifyListeners();
      return outcomes;
    } catch (e) {
      _error = 'Erreur envoi global : ${e.toString()}';
      _lastBatchOutcomes = outcomes;
      notifyListeners();
      return outcomes;
    } finally {
      _isSending      = false;
      _globalSendLock = false;
      if (_idCamp == idCamp && _idEtab != null) { await refreshSyncStatuses(); }
      notifyListeners();
    }
  }

  static Map<String, bool> _outcomesToLegacyMap(List<SendOutcome> outcomes, {bool withEtab = true}) {
    final m = <String, bool>{};
    for (final o in outcomes) {
      final suffix = (o.idFilter != null && o.idFilter!.isNotEmpty) ? '_${o.idFilter}' : '';
      final key = withEtab ? '${o.idEtab}_${o.idQst}$suffix' : '${o.idQst}$suffix';
      m[key] = o.isSuccess;
    }
    return m;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ENVOYER TOUS LES FORMULAIRES D'UN ÉTABLISSEMENT (envoi global établissement)
  // Signature historique conservée (Map<idQst[_filtre], bool>).
  // ═══════════════════════════════════════════════════════════════════════════
  Future<Map<String, bool>> sendAllFormsForSchool({
    required User user,
    void Function(int sent, int total)? onProgress,
  }) async {
    if (_idCamp == null || _idEtab == null || _idSystem == null) {
      _error = 'Contexte invalide pour l\'envoi global';
      notifyListeners();
      return {};
    }
    final forms = await _db.getDistinctFormsWithData(_idCamp!, codeTypeAnnee: _codeyear ?? '', idEtab: _idEtab!);
    final outcomes = await _sendForms(user: user, idCamp: _idCamp!, idSystem: _idSystem!, forms: forms, onProgress: onProgress);
    return _outcomesToLegacyMap(outcomes, withEtab: false);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ENVOYER TOUS LES FORMULAIRES DE TOUS LES ÉTABLISSEMENTS (envoi global campagne)
  // Signature historique conservée (Map<"etab_qst[_filtre]", bool>).
  // ═══════════════════════════════════════════════════════════════════════════
  Future<Map<String, bool>> sendAllFormsForCampaign({
    required User user,
    required String idCamp,
    required String idSystem,
    void Function(int sent, int total)? onProgress,
  }) async {
    final forms = await _db.getDistinctFormsWithData(idCamp, codeTypeAnnee: _codeyear ?? '');
    if (forms.isEmpty) {
      _successMessage = 'Aucune donnée locale à envoyer pour cette campagne.';
      notifyListeners();
      return {};
    }
    final outcomes = await _sendForms(user: user, idCamp: idCamp, idSystem: idSystem, forms: forms, onProgress: onProgress);
    return _outcomesToLegacyMap(outcomes);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AK-SYNC U07 — ENVOYER UNIQUEMENT LES FORMULAIRES EN ATTENTE
  // (statut pending / failed / kosave + repli is_sent=0). [idEtab] optionnel
  // restreint à un établissement. Retourne les résultats typés.
  // ═══════════════════════════════════════════════════════════════════════════
  Future<List<SendOutcome>> sendPendingForms({
    required User user,
    required String idCamp,
    required String idSystem,
    String? idEtab,
    void Function(int sent, int total)? onProgress,
  }) async {
    final pending = await _db.getPendingForms(idCamp, codeTypeAnnee: _codeyear ?? '', idEtab: idEtab);
    if (pending.isEmpty) {
      _successMessage = 'Aucun formulaire en attente d\'envoi.';
      notifyListeners();
      return [];
    }
    final forms = pending.map((p) => {'id_etab': p.idEtab, 'id_qst': p.idQst, 'id_filter': p.idFilter}).toList();
    return _sendForms(user: user, idCamp: idCamp, idSystem: idSystem, forms: forms, onProgress: onProgress);
  }

  /// U06 — Réessaie uniquement les formulaires en échec du dernier envoi global.
  Future<List<SendOutcome>> retryFailedOutcomes({
    required User user,
    required String idCamp,
    required String idSystem,
    void Function(int sent, int total)? onProgress,
  }) async {
    final forms = _lastBatchOutcomes
        .where((o) => o.isFailure && o.kind != SendOutcomeKind.locked)
        .map((o) => {'id_etab': o.idEtab, 'id_qst': o.idQst, 'id_filter': o.idFilter ?? ''})
        .toList();
    return _sendForms(user: user, idCamp: idCamp, idSystem: idSystem, forms: forms, onProgress: onProgress);
  }

  /// Nombre de formulaires en attente pour la campagne (bannière U05).
  Future<int> countPendingForms(String idCamp) =>
      _db.countUnsentForms(idCamp, codeTypeAnnee: _codeyear ?? '');

  /// Liste détaillée des formulaires en attente (écran « Formulaires en attente »).
  Future<List<FormSyncStatus>> listPendingForms(String idCamp, {String? idEtab}) =>
      _db.getPendingForms(idCamp, codeTypeAnnee: _codeyear ?? '', idEtab: idEtab);

  /// Compat : ancienne API (page_camp.js sendAllData) — délègue au moteur unique.
  Future<Map<String, bool>> sendAllCampaignData({
    required User user,
    required String idCamp,
    required String idSystem,
    required List<String> etabIds,
    required List<String> qstIds,
  }) async {
    final all = await _db.getDistinctFormsWithData(idCamp, codeTypeAnnee: _codeyear ?? '');
    final forms = all.where((f) => etabIds.contains(f['id_etab']) && qstIds.contains(f['id_qst'])).toList();
    final outcomes = await _sendForms(user: user, idCamp: idCamp, idSystem: idSystem, forms: forms);
    return _outcomesToLegacyMap(outcomes);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // AK-SYNC U17 / U19 / U21 — RÉCONCILIATION AVEC LE SERVEUR (fail-open)
  //
  // À appeler à l'ouverture d'une campagne (et via « Actualiser ») :
  //   1. sync_status_ws.php/camp → pour chaque école : formulaires 'sent' si le
  //      serveur confirme OKSAVE plus récent que la dernière modif locale ;
  //      'locked' si VALIDE/VERROUILLE (U17/U21).
  //   2. sync_status_ws.php/feedback → demandes de correction / verrous en cache
  //      local (U19) + application aux statuts.
  // Silencieux si le serveur ne dispose pas du WS (ancien serveur) ou hors ligne.
  // Retourne le nombre de formulaires dont le statut local a changé.
  // ═══════════════════════════════════════════════════════════════════════════
  Future<int> reconcileWithServer({
    required User user,
    required String idCamp,
    required String idSystem,
  }) async {
    final annee = _codeyear ?? user.codeyear;
    int changed = 0;
    try {
      final server = await _api.fetchServerSyncStatus(
          login: user.login, campId: idCamp, sysId: idSystem, yearCode: annee);
      if (server != null) {
        for (final entry in server.entries) {
          final themes = entry.value['themes'];
          if (themes is Map) {
            changed += await _db.reconcileSchoolWithServer(
              idCamp: idCamp, idEtab: entry.key, codeTypeAnnee: annee,
              serverThemes: Map<String, Map<String, dynamic>>.from(
                  themes.map((k, v) => MapEntry(k.toString(), Map<String, dynamic>.from(v as Map)))),
            );
          }
        }
      }
      final fb = await _api.fetchSyncFeedback(login: user.login, campId: idCamp, yearCode: annee);
      if (fb != null) {
        await _db.replaceSyncFeedback(idCamp, annee, fb);
        await _db.applyFeedbackToSyncStatus(idCamp, annee, fb);
      }
    } catch (e) {
      debugPrint('[DataEntry] reconcileWithServer: ignoré (fail-open) : $e');
    }
    if (_idCamp == idCamp && _idEtab != null) await refreshSyncStatuses();
    return changed;
  }

  /// U21 — Détection de conflit avant envoi : le serveur a-t-il reçu une version
  /// plus récente que le dernier envoi local, alors que des modifs locales existent ?
  /// Retourne la date serveur si conflit, sinon null. Fail-open (null si WS absent).
  Future<DateTime?> detectServerConflict({required User user}) async {
    if (_idCamp == null || _idEtab == null || _idSystem == null || _selectedQuestion == null) return null;
    final st = _currentSyncStatus;
    if (st == null || !st.status.needsSend) return null; // rien à envoyer → pas de conflit
    try {
      final server = await _api.fetchServerSyncStatus(
          login: user.login, campId: _idCamp!, sysId: _idSystem!, yearCode: _codeyear ?? user.codeyear);
      final etab = server?[_idEtab!];
      final themes = etab?['themes'];
      if (themes is! Map) return null;
      final t = themes[_selectedQuestion!.idQst];
      if (t is! Map) return null;
      final raw = t['date']?.toString();
      if (raw == null || raw.isEmpty || (t['statut'] != 'OK' && t['statut'] != 'VIDE')) return null;
      final srv = DateTime.tryParse(raw.replaceAll('/', '-'));
      if (srv == null) return null;
      final lastLocalOk = st.lastSuccessAt ?? st.serverLastOk;
      // Conflit = le serveur a un OKSAVE postérieur à notre dernier envoi réussi
      // (donc écrit par quelqu'un d'autre : web admin ou autre appareil).
      if (lastLocalOk == null || srv.isAfter(lastLocalOk.add(const Duration(seconds: 5)))) return srv;
      return null;
    } catch (_) {
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CALCUL DES TOTAUX DE MATRICE 2D
  //
  // Remplace calc_total.js — calcul_Total_ThemeMat2D, set_TOTAL_ThemeMat2D
  //
  // Calcule les totaux de lignes, de colonnes et le grand total d'une
  // matrice de saisie (grille 2D). Les clés de champs suivent la convention :
  //   ${cellPrefix}_${row}_${col}   → valeur de la cellule
  //   total_r${row}                  → total de la ligne
  //   total_c${col}                  → total de la colonne
  //   total_all                      → grand total
  // ═══════════════════════════════════════════════════════════════════════════

  /// Recalcule tous les totaux de lignes, colonnes et grand total d'une grille 2D.
  Map<String, String> calculateMatrixTotals({
    required Map<String, String> data,
    required int rows,
    required int cols,
    required String cellPrefix,
  }) {
    final result = Map<String, String>.from(data);

    // Totaux de lignes
    for (int r = 0; r < rows; r++) {
      double rowTotal = 0;
      for (int c = 0; c < cols; c++) {
        rowTotal +=
            double.tryParse(data['${cellPrefix}_${r}_$c'] ?? '') ?? 0;
      }
      result['total_r$r'] = rowTotal.toStringAsFixed(0);
    }

    // Totaux de colonnes
    for (int c = 0; c < cols; c++) {
      double colTotal = 0;
      for (int r = 0; r < rows; r++) {
        colTotal +=
            double.tryParse(data['${cellPrefix}_${r}_$c'] ?? '') ?? 0;
      }
      result['total_c$c'] = colTotal.toStringAsFixed(0);
    }

    // Grand total (somme des totaux de lignes)
    double grandTotal = 0;
    for (int r = 0; r < rows; r++) {
      grandTotal += double.tryParse(result['total_r$r'] ?? '') ?? 0;
    }
    result['total_all'] = grandTotal.toStringAsFixed(0);

    return result;
  }

  /// Calcule un total basé sur une formule (somme de champs nommés).
  /// Utilisé pour les champs de total calculés dynamiquement dans les formulaires.
  double calculateFormulaTotal(List<String> fieldNames) {
    return fieldNames.fold(
        0.0, (sum, name) => sum + (double.tryParse(_formData[name] ?? '') ?? 0));
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Efface tous les messages (erreur, succès, avertissement KOSAVE, violations offline).
  void clearMessages() {
    _error                  = null;
    _successMessage         = null;
    _warningMessage         = null;  // SESSION 53
    _offlineCoherenceErrors = [];
    _themeCoherenceErrors   = [];
    notifyListeners();
  }

  /// SESSION 53 — Efface uniquement le message d'avertissement KOSAVE.
  void clearWarning() {
    _warningMessage = null;
    notifyListeners();
  }

  /// Efface uniquement les violations paire (bannière orange).
  void clearOfflineCoherenceErrors() {
    _offlineCoherenceErrors = [];
    notifyListeners();
  }

  /// Efface uniquement les violations DICO_REGLE_THEME (bannière rouge).
  void clearThemeCoherenceErrors() {
    _themeCoherenceErrors = [];
    notifyListeners();
  }

  /// Efface uniquement le message d'erreur courant.
  void clearError() {
    _error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _coherenceDebounce?.cancel();
    super.dispose();
  }
}
