import 'package:flutter/foundation.dart';
import '../models/campaign.dart';
import '../models/regroup.dart';
import '../models/school.dart';
import '../models/education_system.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CampaignProvider — manages campaigns, navigation hierarchy (system → regroup
/// drill-down → school list) and the campaign loading workflow.
///
/// Mirrors original logic from:
///   campagnes.js   → StmCampagne, StmLocalisation, stmCampagnes
///   page_camp.js   → stmPageCamp.displaySystems(), displayRegroups(),
///                    displayEtabs(), displayFinalRegroupEtabs()
///   charge_camp.js → stmChargeCamp: sequential AJAX download steps
///   page_new_camp.js → stmPageNewCamp.getCampsFromServer()
///   regroups.js    → setEtabLocs, hierarchical traversal
///   systems.js     → stmSystems
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

  List<Campaign> get campaigns => _campaigns;
  Campaign? get selectedCampaign => _selectedCampaign;
  bool get isLoadingCampaigns => _loadingCampaigns;
  String? get error => _error;

  // ─── Navigation state (system → regroup drill-down → schools) ──────────────
  List<EducationSystem> _systems = [];
  EducationSystem? _selectedSystem;

  /// Breadcrumb trail: list of regroups navigated into, from root to current.
  List<Regroup> _regroupBreadcrumb = [];
  List<Regroup> _currentRegroups = [];
  List<School> _currentSchools = [];
  List<RegroupType> _regroupTypes = [];
  List<Regroup> _allRegroups = [];

  List<EducationSystem> get systems => _systems;
  EducationSystem? get selectedSystem => _selectedSystem;
  List<Regroup> get regroupBreadcrumb => _regroupBreadcrumb;
  List<Regroup> get currentRegroups => _currentRegroups;
  List<School> get currentSchools => _currentSchools;

  /// True when the current regroup level is the last one before schools.
  bool get isAtSchoolLevel =>
      _currentRegroups.isEmpty && _currentSchools.isNotEmpty;

  // ─── Available campaigns from server ───────────────────────────────────────
  List<Campaign> _serverCampaigns = [];
  List<Campaign> get serverCampaigns => _serverCampaigns;

  // ─── Load progress ──────────────────────────────────────────────────────────
  int _loadStep = 0;
  int _loadTotalSteps = 9;
  String _loadStepLabel = '';
  bool _isLoadingCampaign = false;

  int get loadStep => _loadStep;
  int get loadTotalSteps => _loadTotalSteps;
  String get loadStepLabel => _loadStepLabel;
  bool get isLoadingCampaign => _isLoadingCampaign;

  // ═══════════════════════════════════════════════════════════════════════════
  // INIT — load campaigns from local DB
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
  // SELECT CAMPAIGN — load its systems for navigation
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectCampaign(Campaign campaign) async {
    _selectedCampaign = campaign;
    _selectedSystem = null;
    _regroupBreadcrumb = [];
    _currentRegroups = [];
    _currentSchools = [];
    _error = null;
    notifyListeners();
    try {
      _systems = await _db.getEducationSystems(campaign.idCamp);
      _allRegroups = await _db.getRegroups(campaign.idCamp);
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
    _selectedSystem = system;
    _regroupBreadcrumb = [];
    _currentSchools = [];
    _error = null;
    await _loadRegroups(null); // load root regroups
    notifyListeners();
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // NAVIGATE INTO REGROUP — drill-down one level
  // Mirrors: stmPageCamp.displayRegroups(id_regp) and displayFinalRegroupEtabs()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> navigateIntoRegroup(Regroup regroup) async {
    _regroupBreadcrumb = [..._regroupBreadcrumb, regroup];
    _error = null;

    // Check whether children are regroups or schools
    final childRegroups = await _db.getChildRegroups(
        _selectedCampaign!.idCamp, regroup.idRegp);

    if (childRegroups.isEmpty) {
      // Leaf regroup → show schools
      await _loadSchoolsForRegroup(regroup.idRegp);
    } else {
      _currentRegroups = childRegroups;
      _currentSchools = [];
    }
    notifyListeners();
  }

  /// Navigate back up the breadcrumb by [levelsUp] levels.
  Future<void> navigateUpRegroup({int levelsUp = 1}) async {
    if (_regroupBreadcrumb.isEmpty) return;
    final newBreadcrumb = _regroupBreadcrumb.sublist(
        0, (_regroupBreadcrumb.length - levelsUp).clamp(0, _regroupBreadcrumb.length));
    _regroupBreadcrumb = newBreadcrumb;

    if (newBreadcrumb.isEmpty) {
      await _loadRegroups(null); // back to root
    } else {
      final parent = newBreadcrumb.last;
      final childRegroups = await _db.getChildRegroups(
          _selectedCampaign!.idCamp, parent.idRegp);
      if (childRegroups.isEmpty) {
        await _loadSchoolsForRegroup(parent.idRegp);
      } else {
        _currentRegroups = childRegroups;
        _currentSchools = [];
      }
    }
    notifyListeners();
  }

  Future<void> _loadRegroups(String? parentId) async {
    try {
      _currentRegroups = await _db.getChildRegroups(
          _selectedCampaign!.idCamp, parentId);
      _currentSchools = [];
    } catch (e) {
      _error = 'Erreur chargement regroupements : ${e.toString()}';
    }
  }

  Future<void> _loadSchoolsForRegroup(String idRegp) async {
    try {
      _currentSchools = await _db.getSchoolsByRegroup(
        _selectedCampaign!.idCamp,
        _selectedSystem!.idSystem,
        idRegp,
      );
      _currentRegroups = [];
    } catch (e) {
      _error = 'Erreur chargement établissements : ${e.toString()}';
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SERVER: GET AVAILABLE CAMPAIGNS (page_new_camp.js)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> fetchServerCampaigns(String userId) async {
    _error = null;
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
  //   1. regroups          → GET /regroup.php/camp/{camp}
  //   2. type_regroups     → GET /type_regroup.php/camp/{camp}
  //   3. status            → GET /status.php/camp/{camp}
  //   4. etabs             → GET /etab.php/camp/{userId}/{camp}
  //   5. locs              → GET /localisation.php/camp/{userId}/{camp}
  //   6. systems           → GET /system.php/camp/{camp}
  //   7. questions         → GET /qst.php/camp/{camp}
  //   8. html forms        → GET /qst_html.php/{camp}/{qst} (per question)
  //   9. rules             → GET /rule.php/{camp}/{qst}   (per question)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> loadCampaignFromServer({
    required Campaign campaign,
    required String userId,
  }) async {
    _isLoadingCampaign = true;
    _loadStep = 0;
    _error = null;
    notifyListeners();

    try {
      // Step 1 — Regroups
      _setLoadStep(1, 'Chargement des regroupements…');
      final regroups = await _api.getRegroups(userId, campaign.idCamp);
      await _db.insertRegroups(campaign.idCamp, regroups);

      // Step 2 — Regroup types
      _setLoadStep(2, 'Chargement des types de regroupements…');
      final regroupTypes =
          await _api.getRegroupTypes(userId, campaign.idCamp);
      await _db.insertRegroupTypes(campaign.idCamp, regroupTypes);

      // Step 3 — School statuses
      _setLoadStep(3, 'Chargement des statuts…');
      final statuses =
          await _api.getSchoolStatuses(userId, campaign.idCamp);
      await _db.insertSchoolStatuses(campaign.idCamp, statuses);

      // Step 4 — Schools
      _setLoadStep(4, 'Chargement des établissements…');
      final schools = await _api.getSchools(userId, campaign.idCamp);
      await _db.insertSchools(campaign.idCamp, schools);

      // Step 5 — Localisations
      _setLoadStep(5, 'Chargement des localisations…');
      final locs = await _api.getLocalisations(userId, campaign.idCamp);
      await _db.insertLocalisations(campaign.idCamp, locs);

      // Step 6 — Education systems
      _setLoadStep(6, 'Chargement des systèmes éducatifs…');
      final systems =
          await _api.getEducationSystems(userId, campaign.idCamp);
      await _db.insertEducationSystems(campaign.idCamp, systems);

      // Step 7 — Questions
      _setLoadStep(7, 'Chargement des questions…');
      final questions = await _api.getQuestions(userId, campaign.idCamp);
      await _db.insertQuestions(campaign.idCamp, questions);

      // Steps 8+9 — HTML forms and validation rules (per question)
      _loadTotalSteps = 7 + (questions.length * 2);
      int stepOffset = 8;
      for (final q in questions) {
        _setLoadStep(
            stepOffset, 'Chargement formulaire : ${q.libQst}…');
        final html =
            await _api.getFormHtml(campaign.idCamp, q.idQst);
        await _db.insertFormHtml(campaign.idCamp, q.idQst, html);
        stepOffset++;

        _setLoadStep(stepOffset, 'Chargement règles : ${q.libQst}…');
        final rules =
            await _api.getValidationRules(campaign.idCamp, q.idQst);
        await _db.insertValidationRules(campaign.idCamp, q.idQst, rules);
        stepOffset++;
      }

      // Persist campaign
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
  // GET QUESTIONS for selected campaign + system (for data entry screen)
  // ═══════════════════════════════════════════════════════════════════════════

  Future<List<Question>> getQuestionsForSystem(
      String idCamp, String idSystem) async {
    return _db.getQuestions(idCamp, idSystem);
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  void _setLoadStep(int step, String label) {
    _loadStep = step;
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
