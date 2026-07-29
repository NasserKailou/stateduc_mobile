import 'dart:async';
import 'package:flutter/foundation.dart';
import '../models/question.dart';
import '../models/user.dart';
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
    _isLoading      = true;
    notifyListeners();

    try {
      // Charge les questions et les périodes de filtre depuis SQLite
      _questions     = await _db.getQuestions(idCamp, idSystem);
      _filterPeriods = await _db.getFilterPeriods(idCamp);
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
          final rules = await _api.fetchRules(
            login:    _api.login ?? '',
            campId:   idCamp,
            sysId:    idSystem,
            qstId:    q.idQst,
            etabId:   idEtab,
            filter:   _selectedFilter?.idFilter,
            yearCode: yearCode,  // ← correction session 14 : yearCode passé au serveur
          );
          if (rules.isNotEmpty) {
            await _db.insertCoherenceRules(rules);
            debugPrint('[DataEntry] stored ${rules.length} offline coherence rules '
                'for qst=${q.idQst} etab=$idEtab year=$yearCode');
            // ── Re-déclenche le contrôle offline si les règles viennent d'arriver
            // pour la question actuellement affichée.
            // La condition _formData.isNotEmpty a été retirée : on re-déclenche
            // systématiquement dès que les règles arrivent pour la question courante,
            // même si les données sont vides (le contrôle retournera 0 violations,
            // ce qui est correct et met à jour l'UI de façon cohérente).
            if (_selectedQuestion?.idQst == q.idQst &&
                !_isCheckingOffline) {
              debugPrint('[DataEntry] rules arrived for current question '
                  '(formData=${_formData.length} fields) — '
                  're-triggering offline coherence check');
              await checkCoherenceOffline();
            }
          } else {
            debugPrint('[DataEntry] no offline coherence rules returned '
                'for qst=${q.idQst} etab=$idEtab year=$yearCode '
                '(normal if no rules configured in DB for this theme)');
          }
        } catch (_) {
          // Non fatal — pas de règles disponibles offline pour cette question
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
    final idFilter  = _selectedFilter?.idFilter;
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
        );
        if (serverFields == null || serverFields.isEmpty) return;

        // Convertit les valeurs serveur en Map<String, String>
        final Map<String, String> serverStr = serverFields.map(
          (k, v) => MapEntry(k, v?.toString() ?? ''),
        );
        // Sauvegarde en SQLite pour utilisation hors ligne
        await _db.saveCollectedData(
          idCamp:   idCamp,
          idEtab:   idEtab,
          idQst:    question.idQst,
          idFilter: idFilter,
          data:     serverStr,
        );
        // Met à jour en mémoire uniquement si la même question est toujours affichée
        if (_selectedQuestion?.idQst == question.idQst) {
          // Stratégie de fusion :
          // - Si les données locales étaient vides : remplissage complet (rechargement total)
          // - Si les données locales étaient présentes (formulaire d'identification) :
          //   ne remplit que les champs vides/manquants pour préserver les saisies
          bool changed = false;
          for (final entry in serverStr.entries) {
            if (localWasEmpty) {
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
          if (localWasEmpty) {
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
      idCamp:   _idCamp!,
      idEtab:   _idEtab!,
      idQst:    _selectedQuestion!.idQst,
      idFilter: idFilter,
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
    // ── Déclenchement debounced de la cohérence offline ──────────────────
    // Attend 800 ms après la dernière frappe avant d'évaluer, pour éviter
    // de sur-solliciter SQLite à chaque caractère saisi.
    debugPrint('[DataEntry] updateField: $fieldName = "$value" '
        '(${_formData.length} champs en mémoire) '
        '— debounce 800ms → checkCoherenceOffline');
    _coherenceDebounce?.cancel();
    _coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
      debugPrint('[DataEntry] debounce fired: '
          '_formData=${_formData.length} _isCheckingOffline=$_isCheckingOffline');
      if (!_isCheckingOffline) {
        checkCoherenceOffline();
      }
    });
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
        idCamp:   _idCamp!,
        idEtab:   _idEtab!,
        idQst:    _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
        data:     _formData,
      );
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
  // ENVOYER AU SERVEUR — réplique stmPageEtab.saveQstOnServer()
  //
  // POST /data_save.php/theme_save/{login}/{campId}/{sysId}/{qstId}/{etabId}/{filter}/0
  //
  // IMPORTANT : utilise user.login (PAS user.idUser) !
  // Pour la première question : injecte LOC_REG_0={etab.idRegroup} si absent.
  //
  // Workflow :
  //   1. Sauvegarde locale (assure la persistance)
  //   2. POST au serveur
  //   3. Si succès : marque is_sent=1 dans SQLite + contrôle de cohérence serveur
  //   4. Si échec : _error avec message
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

    // Sauvegarde locale d'abord (assure la persistance même si l'envoi échoue)
    await saveLocally();

    _isSending      = true;
    _sendAttempt    = 1;  // première tentative
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      // saveData() utilise _withRetry() en interne — le retry est transparent ici.
      // _sendAttempt sera mis à jour via le callback onRetry si le retry se déclenche.
      final ok = await _api.saveData(
        login:            user.login,         // ← utilise login, pas idUser !
        campId:           _idCamp!,
        sysId:            _idSystem!,
        qstId:            _selectedQuestion!.idQst,
        etabId:           _idEtab!,
        filter:           _selectedFilter?.idFilter,
        formData:         _formData,
        etabRegroupId:    _idRegroupEtab,     // pour LOC_REG_0 (première question)
        isFirstQuestion:  _isFirstQuestion,
        yearCode:         user.codeyear,      // contournement session PHP absente
        onRetry: (attempt) {
          // Callback appelé par _withRetry avant chaque nouvelle tentative
          _sendAttempt = attempt + 1;
          notifyListeners();
        },
      );
      if (ok) {
        // Marque les données comme envoyées dans SQLite
        await _db.markCollectedDataSent(
          idCamp:   _idCamp!,
          idEtab:   _idEtab!,
          idQst:    _selectedQuestion!.idQst,
          idFilter: _selectedFilter?.idFilter,
        );
        _successMessage = 'Données envoyées avec succès';
        notifyListeners();

        // ── Contrôle de cohérence serveur (non bloquant) ────────────────
        // S'exécute automatiquement après chaque envoi réussi.
        // controle_theme_batch.class.php exécute les règles SQL sur les
        // données nouvellement sauvegardées en DB et retourne les violations.
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
          _coherenceErrors = []; // Non fatal — ignoré silencieusement
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
      _isSending   = false;
      _sendAttempt = 0;   // remet à zéro : overlay "Tentative N/3" disparaît
      notifyListeners();
    }
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

      if (idTheme > 0) {
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
  // Les thèmes connus sont : 900, 920, 940, 950, 960, 970, 980, 990,
  //   1000, 1010, 1020, 1030, 1040, 1050, 1060, 1070.
  //
  // Cas :
  //   "900"   → 900  (correspondance directe)
  //   "9001"  → 900  (sous-thème : on essaie la troncature à 3 chiffres)
  //   "9002"  → 900
  //   "10001" → 1000 (sous-thème 5 chiffres : troncature à 4 chiffres)
  //   "1050"  → 1050 (correspondance directe 4 chiffres)
  //   "0"     → 0    (inconnu)
  //
  static const _knownThemes = {
    900, 920, 940, 950, 960, 970, 980, 990,
    1000, 1010, 1020, 1030, 1040, 1050, 1060, 1070,
  };

  int _normalizeIdTheme(int raw) {
    if (raw <= 0) return 0;
    // Correspondance directe
    if (_knownThemes.contains(raw)) return raw;
    // Tente troncature : garder les N premiers chiffres
    final s = raw.toString();
    for (int len in [4, 3]) {
      if (s.length > len) {
        final candidate = int.tryParse(s.substring(0, len));
        if (candidate != null && _knownThemes.contains(candidate)) {
          return candidate;
        }
      }
    }
    // Retourne la valeur brute — ThemeRuleEngine retournera [] si theme inconnu
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
      // Marque comme synchronisé (données = serveur)
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
  // ENVOYER TOUS LES FORMULAIRES D'UN ÉTABLISSEMENT (envoi global établissement)
  //
  // Envoie toutes les questions/formulaires de l'établissement COURANT en une
  // seule opération. C'est l'équivalent de cliquer sur "Envoyer" pour chaque
  // formulaire individuellement, mais en une seule action.
  //
  // Retourne une Map<idQst, bool> indiquant le résultat pour chaque formulaire.
  // Le callback [onProgress] est appelé après chaque envoi pour mettre à jour
  // une éventuelle barre de progression dans l'interface.
  //
  // Contrairement à sendToServer() qui n'envoie que le formulaire courant,
  // cette méthode itère sur TOUTES les questions de l'établissement actuel.
  // ═══════════════════════════════════════════════════════════════════════════

  /// Envoie tous les formulaires de l'établissement courant vers le serveur.
  ///
  /// [user] : utilisateur connecté (login + yearCode).
  /// [onProgress] : callback optionnel appelé après chaque envoi avec (index, total).
  Future<Map<String, bool>> sendAllFormsForSchool({
    required User user,
    void Function(int sent, int total)? onProgress,
  }) async {
    if (_idCamp == null || _idEtab == null || _idSystem == null) {
      _error = 'Contexte invalide pour l\'envoi global';
      notifyListeners();
      return {};
    }

    final results = <String, bool>{};
    final questionsToSend = List<Question>.from(_questions);
    final total = questionsToSend.length;
    int sent = 0;

    _isSending = true;
    _error     = null;
    _successMessage = null;
    notifyListeners();

    try {
      for (int i = 0; i < questionsToSend.length; i++) {
        final q = questionsToSend[i];
        // Charge les données collectées pour cette question depuis SQLite
        final data = await _db.getCollectedData(
          idCamp:   _idCamp!,
          idEtab:   _idEtab!,
          idQst:    q.idQst,
          idFilter: null,
        );
        if (data.isEmpty) {
          // Aucune donnée locale pour ce formulaire → ignore
          results[q.idQst] = false;
          sent++;
          onProgress?.call(sent, total);
          continue;
        }

        final isFirst = _questions.isNotEmpty && _questions.first.idQst == q.idQst;
        try {
          final ok = await _api.saveData(
            login:           user.login,
            campId:          _idCamp!,
            sysId:           _idSystem!,
            qstId:           q.idQst,
            etabId:          _idEtab!,
            filter:          null,
            formData:        data,
            etabRegroupId:   _idRegroupEtab,
            isFirstQuestion: isFirst,
            yearCode:        user.codeyear,
          );
          results[q.idQst] = ok;
          if (ok) {
            await _db.markCollectedDataSent(
              idCamp:   _idCamp!,
              idEtab:   _idEtab!,
              idQst:    q.idQst,
              idFilter: null,
            );
          }
        } on ApiException catch (e) {
          results[q.idQst] = false;
          debugPrint('[DataEntry] sendAllFormsForSchool: '
              'ApiException for qst=${q.idQst}: ${e.message}');
        } catch (e) {
          results[q.idQst] = false;
          debugPrint('[DataEntry] sendAllFormsForSchool: '
              'error for qst=${q.idQst}: $e');
        }
        sent++;
        onProgress?.call(sent, total);
      }

      // Résumé
      final okCount   = results.values.where((v) => v).length;
      final skipCount = results.values.where((v) => !v).length;
      _successMessage = '$okCount formulaire(s) envoyé(s) avec succès'
          '${skipCount > 0 ? ', $skipCount ignoré(s) (données manquantes ou erreur)' : ''}.';
      notifyListeners();
      return results;
    } catch (e) {
      _error = 'Erreur envoi global : ${e.toString()}';
      notifyListeners();
      return results;
    } finally {
      _isSending = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ENVOYER TOUS LES FORMULAIRES DE TOUS LES ÉTABLISSEMENTS (envoi global campagne)
  //
  // Envoie TOUTES les données collectées pour TOUS les établissements de la
  // campagne courante. Utilise getDistinctEtabQstWithData() pour trouver tous
  // les couples (id_etab, id_qst) qui ont des données, sans se limiter à
  // l'établissement actuellement affiché.
  //
  // Le callback [onProgress] est appelé après chaque envoi pour mettre à jour
  // une barre de progression dans l'interface.
  //
  // Retourne Map<"${idEtab}_${idQst}", bool> pour le résumé de l'opération.
  // ═══════════════════════════════════════════════════════════════════════════

  /// Envoie tous les formulaires de tous les établissements de la campagne.
  ///
  /// [user] : utilisateur connecté (login + codeyear + yearCode).
  /// [idCamp] : identifiant de la campagne à synchroniser.
  /// [idSystem] : identifiant du système éducatif.
  /// [onProgress] : callback optionnel appelé après chaque envoi (index, total).
  Future<Map<String, bool>> sendAllFormsForCampaign({
    required User user,
    required String idCamp,
    required String idSystem,
    void Function(int sent, int total)? onProgress,
  }) async {
    final results = <String, bool>{};

    // Récupère tous les couples (etab, qst) qui ont des données pour la campagne
    final etabQstList = await _db.getDistinctEtabQstWithData(idCamp);
    if (etabQstList.isEmpty) {
      _successMessage = 'Aucune donnée locale à envoyer pour cette campagne.';
      notifyListeners();
      return {};
    }

    final total = etabQstList.length;
    int sent = 0;

    _isSending      = true;
    _error          = null;
    _successMessage = null;
    notifyListeners();

    try {
      for (int i = 0; i < etabQstList.length; i++) {
        final pair    = etabQstList[i];
        final etabId  = pair['id_etab']!;
        final qstId   = pair['id_qst']!;
        final key     = '${etabId}_$qstId';

        // Charge les données pour ce couple (etab, qst)
        final data = await _db.getCollectedData(
          idCamp:   idCamp,
          idEtab:   etabId,
          idQst:    qstId,
          idFilter: null,
        );
        if (data.isEmpty) {
          results[key] = false;
          sent++;
          onProgress?.call(sent, total);
          continue;
        }

        try {
          final ok = await _api.saveData(
            login:    user.login,
            campId:   idCamp,
            sysId:    idSystem,
            qstId:    qstId,
            etabId:   etabId,
            filter:   null,
            formData: data,
            yearCode: user.codeyear,
          );
          results[key] = ok;
          if (ok) {
            await _db.markCollectedDataSent(
              idCamp:   idCamp,
              idEtab:   etabId,
              idQst:    qstId,
              idFilter: null,
            );
          }
        } on ApiException catch (e) {
          results[key] = false;
          debugPrint('[DataEntry] sendAllFormsForCampaign: '
              'ApiException for $key: ${e.message}');
        } catch (e) {
          results[key] = false;
          debugPrint('[DataEntry] sendAllFormsForCampaign: error for $key: $e');
        }
        sent++;
        onProgress?.call(sent, total);
      }

      final okCount   = results.values.where((v) => v).length;
      final failCount = results.values.where((v) => !v).length;
      _successMessage = '$okCount formulaire(s) envoyé(s) avec succès'
          '${failCount > 0 ? ', $failCount ignoré(s)' : ''}.';
      notifyListeners();
      return results;
    } catch (e) {
      _error = 'Erreur envoi global campagne : ${e.toString()}';
      notifyListeners();
      return results;
    } finally {
      _isSending = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ENVOYER TOUTES LES DONNÉES D'UNE CAMPAGNE (page_camp.js sendAllData)
  //
  // Envoie toutes les données collectées pour tous les établissements et
  // toutes les questions d'une campagne. Retourne un dictionnaire de résultats
  // indexé par "${idEtab}_${idQst}" (vrai = envoyé, faux = échec).
  //
  // Utilisé pour la synchronisation globale depuis l'écran de campagne.
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
        // Récupère les données collectées pour (campagne, établissement, question)
        final data = await _db.getCollectedData(
          idCamp: idCamp,
          idEtab: etabId,
          idQst:  qstId,
        );
        if (data.isEmpty) continue;  // pas de données → ignore

        // Détermine si c'est la première question (pour LOC_REG_0)
        final isFirst = qstIds.isNotEmpty && qstIds.first == qstId;

        try {
          final ok = await _api.saveData(
            login:           user.login,    // ← utilise login !
            campId:          idCamp,
            sysId:           idSystem,
            qstId:           qstId,
            etabId:          etabId,
            filter:          null,
            formData:        data,
            isFirstQuestion: isFirst,
            yearCode:        user.codeyear, // contournement session PHP
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

  /// Efface tous les messages (erreur, succès, violations offline).
  void clearMessages() {
    _error                  = null;
    _successMessage         = null;
    _offlineCoherenceErrors = [];
    _themeCoherenceErrors   = [];
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
