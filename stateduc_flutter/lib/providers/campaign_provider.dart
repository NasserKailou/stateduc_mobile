import 'package:flutter/foundation.dart';
import '../models/campaign.dart';
import '../models/regroup.dart';
import '../models/school.dart';
import '../models/education_system.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CampaignProvider — gestionnaire des campagnes, de la navigation hiérarchique
/// (système éducatif → navigation dans les regroupements → liste d'établissements)
/// et du workflow de téléchargement en 9 étapes.
///
/// Réplique la logique originale de :
///   campagnes.js    → StmCampagne, stmCampagnes
///   page_camp.js    → stmPageCamp: displaySystems, displayRegroups,
///                     displayEtabs, displayFinalRegroupEtabs
///   charge_camp.js  → stmChargeCamp: chaîne AJAX séquentielle étapes 1-9
///   page_new_camp.js → stmPageNewCamp.getCampsFromServer()
///   regroups.js     → setEtabLocs, traversée hiérarchique
///
/// Cycle de vie :
///   1. [loadLocalCampaigns]     → lit les campagnes stockées en SQLite locale
///   2. [selectCampaign]         → charge systèmes + regroupements de la campagne
///   3. [selectSystem]           → démarre la navigation depuis la racine
///   4. [navigateIntoRegroup]    → descend d'un niveau dans la hiérarchie
///   5. [navigateUpRegroup]      → remonte dans le fil d'Ariane
///   6. Niveau feuille           → [_loadSchoolsForRegroup] charge la liste d'établissements
class CampaignProvider extends ChangeNotifier {
  CampaignProvider({
    required DatabaseService db,
    required ApiService api,
  })  : _db = db,
        _api = api;

  final DatabaseService _db;
  final ApiService _api;

  // ─── Liste des campagnes locales ────────────────────────────────────────────
  List<Campaign> _campaigns = [];
  Campaign? _selectedCampaign;
  bool _loadingCampaigns = false;
  String? _error;

  List<Campaign> get campaigns         => _campaigns;
  Campaign? get selectedCampaign       => _selectedCampaign;
  bool get isLoadingCampaigns          => _loadingCampaigns;
  String? get error                    => _error;

  // ─── État de navigation dans la hiérarchie ──────────────────────────────────
  // Navigation : système → regroupement (drill-down) → liste d'établissements
  List<EducationSystem> _systems      = [];
  EducationSystem? _selectedSystem;
  List<Regroup> _regroupBreadcrumb    = [];  // fil d'Ariane des regroupements parcourus
  List<Regroup> _currentRegroups      = [];  // regroupements visibles au niveau actuel
  List<School>  _currentSchools       = [];  // établissements du nœud feuille courant
  List<RegroupType> _regroupTypes     = [];  // types de regroupements (commune, région…)
  List<Regroup> _allRegroups          = [];  // tous les regroupements (chargés à selectCampaign)
  /// Vrai pendant que _loadSchoolsForRegroup() ou _loadRegroups() est en cours.
  bool _isNavigating                   = false;

  List<EducationSystem> get systems           => _systems;
  EducationSystem? get selectedSystem         => _selectedSystem;
  List<Regroup> get regroupBreadcrumb         => _regroupBreadcrumb;
  List<Regroup> get currentRegroups           => _currentRegroups;
  List<School>  get currentSchools            => _currentSchools;
  /// Vrai pendant la navigation (chargement des regroupements ou établissements).
  bool get isNavigating                       => _isNavigating;

  /// Vrai quand on est au niveau feuille (aucun sous-regroupement, des établissements présents).
  bool get isAtSchoolLevel =>
      _currentRegroups.isEmpty && _currentSchools.isNotEmpty;

  // ─── Campagnes disponibles sur le serveur ────────────────────────────────────
  List<Campaign> _serverCampaigns = [];
  List<Campaign> get serverCampaigns => _serverCampaigns;

  // ─── Sélection d'une campagne (chargement local SQLite) ────────────────────
  /// Vrai pendant que selectCampaign() charge les systèmes / regroupements depuis SQLite.
  bool _isSelectingCampaign = false;
  bool get isSelectingCampaign => _isSelectingCampaign;

  // ─── Progression du téléchargement d'une campagne ──────────────────────────
  // Mise à jour à chaque étape du workflow charge_camp.js
  int    _loadStep       = 0;
  int    _loadTotalSteps = 9;
  String _loadStepLabel  = '';
  bool   _isLoadingCampaign = false;

  int    get loadStep           => _loadStep;
  int    get loadTotalSteps     => _loadTotalSteps;
  String get loadStepLabel      => _loadStepLabel;
  bool   get isLoadingCampaign  => _isLoadingCampaign;

  // ═══════════════════════════════════════════════════════════════════════════
  // CHARGER LES CAMPAGNES LOCALES
  // Lit toutes les campagnes déjà téléchargées et stockées dans SQLite.
  // Appelé au démarrage de l'application ou à l'ouverture de l'écran principal.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> loadLocalCampaigns() async {
    _loadingCampaigns = true;
    _error = null;
    notifyListeners();
    try {
      _campaigns = await _db.getCampaigns();
    } catch (e) {
      _error = 'Erreur chargement campagnes : ${e.toString()}';
    } finally {
      _loadingCampaigns = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SÉLECTIONNER UNE CAMPAGNE — charge ses systèmes + regroupements depuis SQLite
  // Réinitialise l'état de navigation (breadcrumb, listes).
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectCampaign(Campaign campaign) async {
    _selectedCampaign    = campaign;
    _selectedSystem      = null;
    _regroupBreadcrumb   = [];
    _currentRegroups     = [];
    _currentSchools      = [];
    _error               = null;
    _isSelectingCampaign = true;   // ← indicateur de chargement activé
    notifyListeners();
    try {
      // Charge les systèmes éducatifs, tous les regroupements et leurs types
      _systems      = await _db.getEducationSystems(campaign.idCamp);
      _allRegroups  = await _db.getRegroups(campaign.idCamp);
      _regroupTypes = await _db.getRegroupTypes(campaign.idCamp);
    } catch (e) {
      _error = 'Erreur chargement systèmes : ${e.toString()}';
    } finally {
      _isSelectingCampaign = false; // ← indicateur de chargement désactivé
    }
    notifyListeners();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SÉLECTIONNER UN SYSTÈME ÉDUCATIF — démarre la navigation depuis la racine
  //
  // Réplique : stmPageCamp.displaySystems() → displayRegroups(null)
  //
  // Stratégies de fallback pour les regroupements racine :
  //   1. getChildRegroups(null) → requête standard avec parentId = null
  //   2. _allRegroups.where(isRoot) → fallback si la requête standard échoue
  //   3. _loadSchoolsForRegroup('__all__') → configuration plate (pas de regroups)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectSystem(EducationSystem system) async {
    _selectedSystem    = system;
    _regroupBreadcrumb = [];
    _currentSchools    = [];
    _currentRegroups   = [];
    _error             = null;
    _isNavigating      = true;
    notifyListeners();

    // Stratégie 1 : charge les regroupements racine via requête DB standard
    await _loadRegroups(null);

    debugPrint('[Nav] selectSystem ${system.libSystem} → '
        '${_currentRegroups.length} root regroups after _loadRegroups');

    // Stratégie 2 : si aucun regroupement trouvé par la requête standard,
    // utilise _allRegroups (chargé à selectCampaign) filtré sur les racines.
    // Gère le cas où getChildRegroups retourne 0 lignes mais le serveur
    // a bien envoyé des regroupements (ex. tous avec parentid='0' non null).
    if (_currentRegroups.isEmpty && _allRegroups.isNotEmpty) {
      // Filtrer sur les regroupements racine uniquement (parent null = racine)
      final rootRegroups = _allRegroups.where((r) => r.isRoot).toList();
      if (rootRegroups.isNotEmpty) {
        _currentRegroups = rootRegroups;
        debugPrint('[Nav] ✓ Used _allRegroups fallback → '
            '${rootRegroups.length} root regroups');
      }
    }

    // Stratégie 3 : toujours aucun regroupement → configuration plate,
    // charge les établissements directement (pas de hiérarchie de regroupements).
    if (_currentRegroups.isEmpty) {
      debugPrint('[Nav] ⚠ No root regroups — loading schools directly '
          'for system ${system.idSystem}');
      await _loadSchoolsForRegroup('__all__');
    }

    _isNavigating = false;
    notifyListeners();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // NAVIGUER DANS UN REGROUPEMENT — descend d'un niveau
  //
  // Réplique : stmPageCamp.displayRegroups(id_regp) et displayFinalRegroupEtabs()
  //
  // Logique :
  //   - Ajoute le regroupement au fil d'Ariane
  //   - Si le regroupement est un nœud feuille (pas d'enfants) →
  //     charge les établissements de ce regroupement
  //   - Sinon → affiche ses sous-regroupements
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> navigateIntoRegroup(Regroup regroup) async {
    _regroupBreadcrumb = [..._regroupBreadcrumb, regroup];
    _error             = null;
    _isNavigating      = true;
    _currentRegroups   = [];
    _currentSchools    = [];
    notifyListeners();

    final childRegroups =
        await _db.getChildRegroups(_selectedCampaign!.idCamp, regroup.idRegp);

    debugPrint('[Nav] navigateIntoRegroup: regp=${regroup.idRegp} '
        '"${regroup.libRegp}" → ${childRegroups.length} children');

    if (childRegroups.isEmpty) {
      // Nœud feuille → charge les établissements de ce regroupement
      debugPrint('[Nav] Leaf detected → loading schools for regp=${regroup.idRegp}');
      await _loadSchoolsForRegroup(regroup.idRegp);
    } else {
      // Nœud intermédiaire → affiche les sous-regroupements
      debugPrint('[Nav] Non-leaf → showing ${childRegroups.length} children');
      _currentRegroups = childRegroups;
      _currentSchools  = [];
    }
    _isNavigating = false;
    notifyListeners();
  }

  /// Remonte dans le fil d'Ariane de [levelsUp] niveaux.
  /// Si le fil d'Ariane devient vide → revient aux regroupements racine.
  /// Si le parent résultant n'a plus d'enfants → charge ses établissements.
  Future<void> navigateUpRegroup({int levelsUp = 1}) async {
    if (_regroupBreadcrumb.isEmpty) return;
    // Tronque le fil d'Ariane en remontant [levelsUp] niveaux
    final newBreadcrumb = _regroupBreadcrumb.sublist(
        0,
        (_regroupBreadcrumb.length - levelsUp)
            .clamp(0, _regroupBreadcrumb.length));
    _regroupBreadcrumb = newBreadcrumb;
    _isNavigating      = true;
    _currentRegroups   = [];
    _currentSchools    = [];
    notifyListeners();

    if (newBreadcrumb.isEmpty) {
      // Retour à la racine : recharge les regroupements de premier niveau
      await _loadRegroups(null);
    } else {
      // Retour à un niveau intermédiaire : recharge les enfants du nouveau parent
      final parent      = newBreadcrumb.last;
      final childRegroups =
          await _db.getChildRegroups(_selectedCampaign!.idCamp, parent.idRegp);
      if (childRegroups.isEmpty) {
        // Le parent est un nœud feuille → charge ses établissements
        await _loadSchoolsForRegroup(parent.idRegp);
      } else {
        _currentRegroups = childRegroups;
        _currentSchools  = [];
      }
    }
    _isNavigating = false;
    notifyListeners();
  }

  /// Charge les regroupements enfants du [parentId] donné depuis SQLite.
  /// [parentId] = null → regroupements racine (premier niveau).
  Future<void> _loadRegroups(String? parentId) async {
    try {
      final regroups =
          await _db.getChildRegroups(_selectedCampaign!.idCamp, parentId);
      debugPrint('[Nav] _loadRegroups parentId=$parentId → '
          '${regroups.length} regroups');
      _currentRegroups = regroups;
      _currentSchools = [];
    } catch (e) {
      debugPrint('[Nav] _loadRegroups ERROR: $e');
      _error = 'Erreur chargement regroupements : ${e.toString()}';
    }
  }

  /// Charge les établissements du regroupement [idRegp] depuis SQLite,
  /// puis enrichit chaque établissement avec son libellé de statut
  /// via [DatabaseService.resolveSchoolStatuses].
  Future<void> _loadSchoolsForRegroup(String idRegp) async {
    try {
      final schools = await _db.getSchoolsByRegroup(
        _selectedCampaign!.idCamp,
        _selectedSystem!.idSystem,
        idRegp,
      );
      // Enrichit avec le libellé de statut depuis la table school_statuses
      final enriched = await _db.resolveSchoolStatuses(
          _selectedCampaign!.idCamp, schools);
      debugPrint('[Nav] _loadSchoolsForRegroup regp=$idRegp → '
          '${enriched.length} schools');
      _currentSchools  = enriched;
      _currentRegroups = [];
    } catch (e) {
      debugPrint('[Nav] _loadSchoolsForRegroup ERROR: $e');
      _error = 'Erreur chargement établissements : ${e.toString()}';
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVEUR : RÉCUPÉRER LES CAMPAGNES DISPONIBLES
  //
  // GET /user_camp.php/new_camp/{userId}/1
  // Issu de page_new_camp.js : getCampsFromServer()
  //
  // Retourne les campagnes auxquelles l'utilisateur a accès sur le serveur
  // mais qui ne sont pas encore téléchargées localement.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> fetchServerCampaigns(String userId) async {
    _error           = null;
    _serverCampaigns = [];
    _loadingCampaigns = true;   // SESSION 38 : active l'indicateur de chargement
    notifyListeners();
    try {
      _serverCampaigns  = await _api.getAvailableCampaigns(userId);
      _loadingCampaigns = false;
      notifyListeners();
    } on ApiException catch (e) {
      _error            = e.message;
      _loadingCampaigns = false;
      notifyListeners();
    } catch (e) {
      _error            = 'Erreur serveur : ${e.toString()}';
      _loadingCampaigns = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // TÉLÉCHARGER UNE CAMPAGNE DEPUIS LE SERVEUR (charge_camp.js — stmChargeCamp)
  //
  // Workflow séquentiel en 9 étapes (réplique la chaîne AJAX originale) :
  //   Étape 1 : regroupements → GET /user_camp.php/reg_camp/{LOGIN}/{campId}/1
  //   Étape 2 : types_regroups → GET /user_camp.php/typ_reg_camp/{userId}/{campId}/{typeRegroups}
  //   Étape 3 : statuts → GET /user_camp.php/etabs_status/  (sans paramètres)
  //   Étape 4 : établissements → GET /user_camp.php/etabs_camp/{userId}/{campId}/1
  //   Étape 5 : localisations → GET /user_camp.php/locs_camp/{userId}/{campId}
  //   Étape 6 : systèmes éducatifs → GET /user_camp.php/sys_camp/{userId}/{campId}
  //   Étape 7+ (par système) : → GET /data_camp.php/theme_camp/{campId}/{sysId}/eng
  //     par question :
  //     Étape 8 : formulaire HTML → deux requêtes : GET url → GET HTML avec auth
  //     Étape 9 : règles → GET /data_camp.php/regle_theme_camp/{qstId}/{sysId}
  //
  // CRITIQUE : login est utilisé pour reg_camp ; userId pour tous les autres !
  //
  // Les étapes 8 et 9 sont non-fatales : un échec ne bloque pas le téléchargement.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> loadCampaignFromServer({
    required Campaign campaign,
    required String login,   // currentUser.login  ← pour l'endpoint reg_camp
    required String userId,  // currentUser.idUser ← pour tous les autres endpoints
  }) async {
    _isLoadingCampaign = true;
    _loadStep          = 0;
    _error             = null;
    notifyListeners();

    try {
      // Étape 1 — Regroupements (utilise LOGIN)
      _setLoadStep(1, 'Chargement des regroupements…');
      final regroups = await _api.getRegroups(login, campaign.idCamp);
      await _db.insertRegroups(campaign.idCamp, regroups);

      // Étape 2 — Types de regroupements (utilise userId + campaign.typeRegroups CSV)
      _setLoadStep(2, 'Chargement des types de regroupements…');
      final regroupTypes = await _api.getRegroupTypes(
          userId, campaign.idCamp, campaign.typeRegroups);
      await _db.insertRegroupTypes(campaign.idCamp, regroupTypes);

      // Étape 3 — Statuts des établissements (sans paramètres)
      _setLoadStep(3, 'Chargement des statuts…');
      final statuses = await _api.getSchoolStatuses();
      await _db.insertSchoolStatuses(campaign.idCamp, statuses);

      // Étape 4 — Établissements (utilise userId)
      _setLoadStep(4, 'Chargement des établissements…');
      final schools = await _api.getSchools(userId, campaign.idCamp);
      await _db.insertSchools(campaign.idCamp, schools);

      // Étape 4b — Pré-calcul local des localisations (fallback hors-ligne)
      // Parcourt le graphe regroups (user's chain) pour pré-remplir lib_localisation.
      // Peut retourner la mauvaise chaîne administrative si le pays a plusieurs chaînes
      // (ex. Burundi : Chain 1=OLD vs Chain 2=NEW). Sera corrigé à l'étape 6b.
      _setLoadStep(4, 'Calcul des localisations hiérarchiques…');
      await _db.computeAndStoreLocalisations(campaign.idCamp);

      // Étape 5 — Localisations (utilise userId)
      _setLoadStep(5, 'Chargement des localisations…');
      final locs = await _api.getLocalisations(userId, campaign.idCamp);
      await _db.insertLocalisations(campaign.idCamp, locs);

      // Étape 6 — Systèmes éducatifs (utilise userId)
      _setLoadStep(6, 'Chargement des systèmes éducatifs…');
      final systems = await _api.getEducationSystems(userId, campaign.idCamp);
      await _db.insertEducationSystems(campaign.idCamp, systems);

      // Étape 6b — SESSION 53 FIX (v2) : localisation depuis la chaîne PRINCIPALE
      //
      // L'endpoint etab_hier retourne la hiérarchie calculée avec la chaîne de
      // référence du serveur (= $_SESSION['chaine'] de questionnaire.php), soit la
      // PREMIÈRE chaîne de TYPE_CHAINE_REGROUPEMENT ordonnée par ORDRE.
      //
      // Cela corrige le problème Burundi : reg_camp utilise la chaîne de l'agent
      // (Chain 2 = NEW = BUHUMUZA…) alors que questionnaire.php utilise Chain 1
      // (OLD = CANKUZO…). L'étape 4b avait donc calculé la mauvaise chaîne.
      //
      // INTERNATIONAL : fonctionne pour tout pays — la notion de "chaîne primaire"
      // (première par ORDRE) est universelle dans le schéma StatEduc.
      //
      // NON FATAL : si le serveur échoue, on garde les valeurs de l'étape 4b.
      if (systems.isNotEmpty) {
        _setLoadStep(6, 'Chargement des localisations officielles…');
        try {
          // Récupère la liste de tous les étabs pour cette campagne
          final allSchools = await _db.getSchools(campaign.idCamp);
          final etabIds = allSchools.map((s) => s.idEtab).toList();

          if (etabIds.isNotEmpty) {
            // Appel par système (le serveur filtre la chaîne par système)
            // En pratique, la chaîne primaire est la même pour tous les systèmes
            // dans la majorité des pays — on prend le premier système.
            // Si plusieurs systèmes ont des chaînes différentes, on itère tous.
            final mergedHierarchies = <String, String>{};
            for (final system in systems) {
              final hierMap = await _api.getEtabHierarchies(
                idSys:   system.idSystem,
                campId:  campaign.idCamp,
                etabIds: etabIds,
              );
              // Merge — le dernier système gagne si conflit (rare)
              // On ne remplace que si la valeur est non vide
              for (final e in hierMap.entries) {
                if (e.value.isNotEmpty) mergedHierarchies[e.key] = e.value;
              }
            }
            await _db.updateSchoolLocalisations(campaign.idCamp, mergedHierarchies);
            debugPrint('[CampaignProvider] étape 6b : ${mergedHierarchies.length} '
                'localisations serveur appliquées pour camp=${campaign.idCamp}');
          }
        } catch (e) {
          // Non fatal — l'étape 4b a déjà calculé une valeur de secours
          debugPrint('[CampaignProvider] étape 6b SKIPPED (non fatal) : $e');
        }
      }

      // Étapes 7+ : par système éducatif — questions, HTML du formulaire, règles
      // Nombre total d'étapes = 6 + somme sur tous systèmes de (1 + nb_questions * 2)
      int stepOffset = 7;
      for (final system in systems) {
        // Étape 7 (par système) — Questions / thèmes
        _setLoadStep(
            stepOffset, 'Chargement formulaires : ${system.libSystem}…');
        final questions =
            await _api.getQuestions(campaign.idCamp, system.idSystem);
        await _db.insertQuestions(campaign.idCamp, questions);
        stepOffset++;

        // Étapes 8+9 par question — HTML + Règles (échecs non fatals)
        _loadTotalSteps = stepOffset + (questions.length * 2);
        notifyListeners();

        for (final q in questions) {
          // Étape : formulaire HTML (deux requêtes) — non fatal
          _setLoadStep(stepOffset, 'Chargement formulaire : ${q.libQst}…');
          try {
            final html =
                await _api.getFormHtml(campaign.idCamp, q.idQst);
            await _db.insertFormHtml(campaign.idCamp, q.idQst, html);
          } catch (e) {
            // Échec HTML non fatal — le formulaire sera indisponible hors ligne
            // Session 49 : log explicite pour diagnostiquer les échecs silencieux
            debugPrint('[CampaignProvider] getFormHtml FAILED qst=${q.idQst} (${q.libQst}) : $e');
          }
          stepOffset++;

          // Étape : règles de validation — non fatal
          _setLoadStep(stepOffset, 'Chargement règles : ${q.libQst}…');
          try {
            final rules = await _api.getValidationRules(
                q.idQst, system.idSystem);
            await _db.insertValidationRules(
                campaign.idCamp, q.idQst, rules);
          } catch (_) {
            // Échec règles non fatal
          }
          stepOffset++;
        }
      }

      // Persiste la campagne en SQLite et rafraîchit la liste locale
      await _db.insertCampaign(campaign);
      _campaigns = await _db.getCampaigns();

      _isLoadingCampaign = false;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      _isLoadingCampaign = false;
      notifyListeners();
      return false;
    } catch (e) {
      _error = 'Erreur chargement campagne : ${e.toString()}';
      _isLoadingCampaign = false;
      notifyListeners();
      return false;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SAUVEGARDER LES PÉRIODES DE FILTRE D'UNE CAMPAGNE (depuis user.filters)
  // Stocke les périodes de collecte (ex. trimestre 1, trimestre 2) pour
  // permettre la saisie filtrée par période dans l'écran de saisie.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> saveFilterPeriodsForCampaign(
      String idCamp, List<FilterPeriod> filters) async {
    if (filters.isEmpty) return;
    await _db.insertFilterPeriods(idCamp, filters);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // RÉCUPÉRER LES QUESTIONS D'UN SYSTÈME (pour l'écran de saisie)
  // Délègue directement à DatabaseService.getQuestions().
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestionsForSystem(
      String idCamp, String idSystem) async {
    return _db.getQuestions(idCamp, idSystem);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SUPPRIMER UNE CAMPAGNE
  // Supprime toutes les données liées (établissements, formulaires, règles…)
  // et réinitialise l'état de navigation si la campagne était sélectionnée.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> deleteCampaign(String idCamp) async {
    try {
      await _db.deleteCampaign(idCamp);
      _campaigns = await _db.getCampaigns();
      if (_selectedCampaign?.idCamp == idCamp) {
        // Réinitialise l'état de navigation si la campagne supprimée était active
        _selectedCampaign  = null;
        _selectedSystem    = null;
        _regroupBreadcrumb = [];
        _currentRegroups   = [];
        _currentSchools    = [];
      }
      notifyListeners();
    } catch (e) {
      _error = 'Erreur suppression campagne : ${e.toString()}';
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Met à jour l'étape courante du téléchargement et notifie les écouteurs.
  /// Utilisé par loadCampaignFromServer() pour mettre à jour la barre de progression.
  void _setLoadStep(int step, String label) {
    _loadStep      = step;
    _loadStepLabel = label;
    notifyListeners();
  }

  /// Retourne le libellé lisible d'un type de regroupement à partir de son ID.
  /// Retourne l'ID brut si le type n'est pas trouvé dans la liste locale.
  String regroupTypeLabel(String idTypeRegp) {
    try {
      return _regroupTypes
          .firstWhere((t) => t.idTypeRegp == idTypeRegp)
          .libTypeRegp;
    } catch (_) {
      return idTypeRegp;
    }
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
