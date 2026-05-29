import 'package:flutter/foundation.dart';
import '../models/campaign.dart';
import '../models/regroup.dart';
import '../models/school.dart';
import '../models/education_system.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CampaignProvider — manages campaigns, navigation hierarchy
/// (system → regroup drill-down → school list) and the 9-step download workflow.
///
/// Mirrors original logic from:
///   campagnes.js    → StmCampagne, stmCampagnes
///   page_camp.js    → stmPageCamp: displaySystems, displayRegroups,
///                     displayEtabs, displayFinalRegroupEtabs
///   charge_camp.js  → stmChargeCamp: sequential AJAX download steps 1-9
///   page_new_camp.js → stmPageNewCamp.getCampsFromServer()
///   regroups.js     → setEtabLocs, hierarchical traversal
class CampaignProvider extends ChangeNotifier {
  CampaignProvider({
    required DatabaseService db,
    required ApiService api,
  })  : _db = db,
        _api = api;

  final DatabaseService _db;
  final ApiService _api;

  // ─── Campaign list ──────────────────────────────────────────────────────────
  List<Campaign> _campaigns = [];
  Campaign? _selectedCampaign;
  bool _loadingCampaigns = false;
  String? _error;

  List<Campaign> get campaigns         => _campaigns;
  Campaign? get selectedCampaign       => _selectedCampaign;
  bool get isLoadingCampaigns          => _loadingCampaigns;
  String? get error                    => _error;

  // ─── Navigation state ──────────────────────────────────────────────────────
  List<EducationSystem> _systems      = [];
  EducationSystem? _selectedSystem;
  List<Regroup> _regroupBreadcrumb    = [];
  List<Regroup> _currentRegroups      = [];
  List<School>  _currentSchools       = [];
  List<RegroupType> _regroupTypes     = [];
  List<Regroup> _allRegroups          = [];
  /// True while _loadSchoolsForRegroup() or _loadRegroups() is in flight.
  bool _isNavigating                   = false;

  List<EducationSystem> get systems           => _systems;
  EducationSystem? get selectedSystem         => _selectedSystem;
  List<Regroup> get regroupBreadcrumb         => _regroupBreadcrumb;
  List<Regroup> get currentRegroups           => _currentRegroups;
  List<School>  get currentSchools            => _currentSchools;
  /// True while navigating (loading regroups or schools from DB).
  bool get isNavigating                       => _isNavigating;

  bool get isAtSchoolLevel =>
      _currentRegroups.isEmpty && _currentSchools.isNotEmpty;

  // ─── Available campaigns from server ───────────────────────────────────────
  List<Campaign> _serverCampaigns = [];
  List<Campaign> get serverCampaigns => _serverCampaigns;

  // ─── Campaign download progress ────────────────────────────────────────────
  int    _loadStep       = 0;
  int    _loadTotalSteps = 9;
  String _loadStepLabel  = '';
  bool   _isLoadingCampaign = false;

  int    get loadStep           => _loadStep;
  int    get loadTotalSteps     => _loadTotalSteps;
  String get loadStepLabel      => _loadStepLabel;
  bool   get isLoadingCampaign  => _isLoadingCampaign;

  // ═══════════════════════════════════════════════════════════════════════════
  // LOAD LOCAL CAMPAIGNS
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
  // SELECT CAMPAIGN — load its systems + regroups from local DB
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectCampaign(Campaign campaign) async {
    _selectedCampaign = campaign;
    _selectedSystem   = null;
    _regroupBreadcrumb = [];
    _currentRegroups  = [];
    _currentSchools   = [];
    _error = null;
    notifyListeners();
    try {
      _systems      = await _db.getEducationSystems(campaign.idCamp);
      _allRegroups  = await _db.getRegroups(campaign.idCamp);
      _regroupTypes = await _db.getRegroupTypes(campaign.idCamp);
    } catch (e) {
      _error = 'Erreur chargement systèmes : ${e.toString()}';
    }
    notifyListeners();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SELECT SYSTEM — start regroup drill-down from root
  // Mirrors: stmPageCamp.displaySystems() → displayRegroups(null)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectSystem(EducationSystem system) async {
    _selectedSystem    = system;
    _regroupBreadcrumb = [];
    _currentSchools    = [];
    _currentRegroups   = [];
    _error             = null;
    _isNavigating      = true;
    notifyListeners();
    await _loadRegroups(null); // load root regroups
    _isNavigating = false;
    notifyListeners();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // NAVIGATE INTO REGROUP — drill-down one level
  // Mirrors: stmPageCamp.displayRegroups(id_regp) and displayFinalRegroupEtabs()
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
      // Leaf regroup → load schools for this regroup
      debugPrint('[Nav] Leaf detected → loading schools for regp=${regroup.idRegp}');
      await _loadSchoolsForRegroup(regroup.idRegp);
    } else {
      debugPrint('[Nav] Non-leaf → showing ${childRegroups.length} children');
      _currentRegroups = childRegroups;
      _currentSchools  = [];
    }
    _isNavigating = false;
    notifyListeners();
  }

  /// Navigate back up the breadcrumb [levelsUp] levels.
  Future<void> navigateUpRegroup({int levelsUp = 1}) async {
    if (_regroupBreadcrumb.isEmpty) return;
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
      await _loadRegroups(null);
    } else {
      final parent      = newBreadcrumb.last;
      final childRegroups =
          await _db.getChildRegroups(_selectedCampaign!.idCamp, parent.idRegp);
      if (childRegroups.isEmpty) {
        await _loadSchoolsForRegroup(parent.idRegp);
      } else {
        _currentRegroups = childRegroups;
        _currentSchools  = [];
      }
    }
    _isNavigating = false;
    notifyListeners();
  }

  Future<void> _loadRegroups(String? parentId) async {
    try {
      _currentRegroups =
          await _db.getChildRegroups(_selectedCampaign!.idCamp, parentId);
      _currentSchools = [];
    } catch (e) {
      _error = 'Erreur chargement regroupements : ${e.toString()}';
    }
  }

  Future<void> _loadSchoolsForRegroup(String idRegp) async {
    try {
      final schools = await _db.getSchoolsByRegroup(
        _selectedCampaign!.idCamp,
        _selectedSystem!.idSystem,
        idRegp,
      );
      debugPrint('[Nav] _loadSchoolsForRegroup regp=$idRegp → '
          '${schools.length} schools');
      _currentSchools  = schools;
      _currentRegroups = [];
    } catch (e) {
      debugPrint('[Nav] _loadSchoolsForRegroup ERROR: $e');
      _error = 'Erreur chargement établissements : ${e.toString()}';
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER: GET AVAILABLE CAMPAIGNS
  // GET /user_camp.php/new_camp/{userId}/1
  // From page_new_camp.js: getCampsFromServer()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> fetchServerCampaigns(String userId) async {
    _error          = null;
    _serverCampaigns = [];
    notifyListeners();
    try {
      _serverCampaigns = await _api.getAvailableCampaigns(userId);
      notifyListeners();
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
    } catch (e) {
      _error = 'Erreur serveur : ${e.toString()}';
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // LOAD CAMPAIGN FROM SERVER (charge_camp.js — stmChargeCamp)
  //
  // Sequential steps (mirrors original AJAX chain):
  //   Step 1: regroups    → GET /user_camp.php/reg_camp/{LOGIN}/{campId}/1
  //   Step 2: type_regs   → GET /user_camp.php/typ_reg_camp/{userId}/{campId}/{typeRegroups}
  //   Step 3: statuses    → GET /user_camp.php/etabs_status/  (NO params)
  //   Step 4: schools     → GET /user_camp.php/etabs_camp/{userId}/{campId}/1
  //   Step 5: locs        → GET /user_camp.php/locs_camp/{userId}/{campId}
  //   Step 6: systems     → GET /user_camp.php/sys_camp/{userId}/{campId}
  //   Step 7+ (per sys):  → GET /data_camp.php/theme_camp/{campId}/{sysId}/eng
  //     per question:
  //     Step 8: html form → two-step: GET url → GET HTML with auth
  //     Step 9: rules     → GET /data_camp.php/regle_theme_camp/{qstId}/{sysId}
  //
  // CRITICAL: login is for reg_camp; userId is for all others!
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> loadCampaignFromServer({
    required Campaign campaign,
    required String login,   // currentUser.login  ← for reg_camp endpoint
    required String userId,  // currentUser.idUser ← for all other endpoints
  }) async {
    _isLoadingCampaign = true;
    _loadStep          = 0;
    _error             = null;
    notifyListeners();

    try {
      // Step 1 — Regroups (uses LOGIN)
      _setLoadStep(1, 'Chargement des regroupements…');
      final regroups = await _api.getRegroups(login, campaign.idCamp);
      await _db.insertRegroups(campaign.idCamp, regroups);

      // Step 2 — Regroup types (uses userId + campaign.typeRegroups CSV)
      _setLoadStep(2, 'Chargement des types de regroupements…');
      final regroupTypes = await _api.getRegroupTypes(
          userId, campaign.idCamp, campaign.typeRegroups);
      await _db.insertRegroupTypes(campaign.idCamp, regroupTypes);

      // Step 3 — School statuses (NO params)
      _setLoadStep(3, 'Chargement des statuts…');
      final statuses = await _api.getSchoolStatuses();
      await _db.insertSchoolStatuses(campaign.idCamp, statuses);

      // Step 4 — Schools (uses userId)
      _setLoadStep(4, 'Chargement des établissements…');
      final schools = await _api.getSchools(userId, campaign.idCamp);
      await _db.insertSchools(campaign.idCamp, schools);

      // Step 5 — Localisations (uses userId)
      _setLoadStep(5, 'Chargement des localisations…');
      final locs = await _api.getLocalisations(userId, campaign.idCamp);
      await _db.insertLocalisations(campaign.idCamp, locs);

      // Step 6 — Education systems (uses userId)
      _setLoadStep(6, 'Chargement des systèmes éducatifs…');
      final systems = await _api.getEducationSystems(userId, campaign.idCamp);
      await _db.insertEducationSystems(campaign.idCamp, systems);

      // Steps 7+: per system — questions, HTML, rules
      // Total steps = 6 + sum over all systems of (1 + questions.length * 2)
      int stepOffset = 7;
      for (final system in systems) {
        // Step 7 (per system) — Questions
        _setLoadStep(
            stepOffset, 'Chargement formulaires : ${system.libSystem}…');
        final questions =
            await _api.getQuestions(campaign.idCamp, system.idSystem);
        await _db.insertQuestions(campaign.idCamp, questions);
        stepOffset++;

        // Steps 8+9 per question — HTML + Rules (non-fatal failures)
        _loadTotalSteps = stepOffset + (questions.length * 2);
        notifyListeners();

        for (final q in questions) {
          // Step: HTML form (two-step fetch) — non-fatal
          _setLoadStep(stepOffset, 'Chargement formulaire : ${q.libQst}…');
          try {
            final html =
                await _api.getFormHtml(campaign.idCamp, q.idQst);
            await _db.insertFormHtml(campaign.idCamp, q.idQst, html);
          } catch (_) {
            // HTML fetch failure is non-fatal — form will be unavailable offline
          }
          stepOffset++;

          // Step: Validation rules — non-fatal
          _setLoadStep(stepOffset, 'Chargement règles : ${q.libQst}…');
          try {
            final rules = await _api.getValidationRules(
                q.idQst, system.idSystem);
            await _db.insertValidationRules(
                campaign.idCamp, q.idQst, rules);
          } catch (_) {
            // Rules fetch failure is non-fatal
          }
          stepOffset++;
        }
      }

      // Also save filter periods from user's filters if embedded in campaign
      // (filter_periods are stored per-campaign at load time)

      // Persist campaign to DB
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
  // SAVE FILTER PERIODS for a campaign (from user.filters)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> saveFilterPeriodsForCampaign(
      String idCamp, List<FilterPeriod> filters) async {
    if (filters.isEmpty) return;
    await _db.insertFilterPeriods(idCamp, filters);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // GET QUESTIONS (for data entry screen)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestionsForSystem(
      String idCamp, String idSystem) async {
    return _db.getQuestions(idCamp, idSystem);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // DELETE CAMPAIGN
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> deleteCampaign(String idCamp) async {
    try {
      await _db.deleteCampaign(idCamp);
      _campaigns = await _db.getCampaigns();
      if (_selectedCampaign?.idCamp == idCamp) {
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

  void _setLoadStep(int step, String label) {
    _loadStep      = step;
    _loadStepLabel = label;
    notifyListeners();
  }

  /// Returns human-readable label for a regroup type ID.
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
