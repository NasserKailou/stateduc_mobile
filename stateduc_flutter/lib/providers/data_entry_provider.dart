import 'package:flutter/foundation.dart';
import '../models/question.dart';
import '../models/user.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// DataEntryProvider — ChangeNotifier for form data entry (saisie).
///
/// Mirrors original logic from:
///   page_etab.js   → stmPageEtab: initHtml, initPageData, savePage,
///                    saveQstOnServer, reloadFromServer, getPageDataToSend,
///                    addGrilleLine (dynamic grid rows)
///   etabs.js       → StmCollectData, StmLiftEtab: CRUD for collected data
///   questions.js   → StmData.validate(), testVal, testType
///   error_msg.js   → French validation error messages
///   calc_total.js  → 2D matrix total calculations
///   script.js      → ctrl_saisie, ctrl_saisie_text, ctrl_saisie_dimension
class DataEntryProvider extends ChangeNotifier {
  DataEntryProvider({
    required DatabaseService db,
    required ApiService api,
  })  : _db = db,
        _api = api;

  final DatabaseService _db;
  final ApiService _api;

  // ─── Current context ────────────────────────────────────────────────────────
  String? _idCamp;
  String? _idEtab;
  String? _libEtab;
  String? _idSystem;

  // ─── Available questions for the current school+system ─────────────────────
  List<Question> _questions = [];
  Question? _selectedQuestion;

  // ─── Available filter periods for the selected question ────────────────────
  List<FilterPeriod> _filterPeriods = [];
  FilterPeriod? _selectedFilter;

  // ─── Form state ─────────────────────────────────────────────────────────────
  /// Cached form HTML (from DB or server).
  String? _formHtml;
  /// Current field values: fieldName → value
  Map<String, String> _formData = {};
  /// Validation errors: fieldName → error message
  Map<String, String> _validationErrors = {};
  List<ValidationRule> _rules = [];

  // ─── Status flags ───────────────────────────────────────────────────────────
  bool _isLoading = false;
  bool _isSaving = false;
  bool _isSending = false;
  bool _isReloading = false;
  bool _hasUnsavedChanges = false;
  String? _error;
  String? _successMessage;

  // ─── Getters ─────────────────────────────────────────────────────────────────
  String? get idCamp => _idCamp;
  String? get idEtab => _idEtab;
  String? get libEtab => _libEtab;
  List<Question> get questions => _questions;
  Question? get selectedQuestion => _selectedQuestion;
  List<FilterPeriod> get filterPeriods => _filterPeriods;
  FilterPeriod? get selectedFilter => _selectedFilter;
  String? get formHtml => _formHtml;
  Map<String, String> get formData => Map.unmodifiable(_formData);
  Map<String, String> get validationErrors =>
      Map.unmodifiable(_validationErrors);
  bool get isLoading => _isLoading;
  bool get isSaving => _isSaving;
  bool get isSending => _isSending;
  bool get isReloading => _isReloading;
  bool get hasUnsavedChanges => _hasUnsavedChanges;
  String? get error => _error;
  String? get successMessage => _successMessage;

  // ═══════════════════════════════════════════════════════════════════════════
  // INIT — set context for a specific school
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> initForSchool({
    required String idCamp,
    required String idEtab,
    required String libEtab,
    required String idSystem,
  }) async {
    _idCamp = idCamp;
    _idEtab = idEtab;
    _libEtab = libEtab;
    _idSystem = idSystem;
    _selectedQuestion = null;
    _selectedFilter = null;
    _formHtml = null;
    _formData = {};
    _validationErrors = {};
    _hasUnsavedChanges = false;
    _error = null;
    _successMessage = null;
    _isLoading = true;
    notifyListeners();

    try {
      _questions = await _db.getQuestions(idCamp, idSystem);
      _filterPeriods = await _db.getFilterPeriods(idCamp);
    } catch (e) {
      _error = 'Erreur chargement questions : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SELECT QUESTION — load HTML form + rules + saved data
  // Mirrors: stmPageEtab.initHtml() + initPageData()
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectQuestion(Question question) async {
    _selectedQuestion = question;
    _selectedFilter = null;
    _formData = {};
    _validationErrors = {};
    _hasUnsavedChanges = false;
    _error = null;
    _successMessage = null;
    _isLoading = true;
    notifyListeners();

    try {
      // Load form HTML from local cache
      _formHtml = await _db.getFormHtml(_idCamp!, question.idQst);

      // Load validation rules
      _rules = await _db.getValidationRules(_idCamp!, question.idQst);

      // Load saved field values (no filter yet)
      await _loadFormData(idFilter: null);
    } catch (e) {
      _error = 'Erreur chargement formulaire : ${e.toString()}';
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SELECT FILTER — reload data for selected filter period
  // ═══════════════════════════════════════════════════════════════════════════

  Future<void> selectFilter(FilterPeriod? filter) async {
    _selectedFilter = filter;
    _formData = {};
    _validationErrors = {};
    _hasUnsavedChanges = false;
    _isLoading = true;
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
      idCamp: _idCamp!,
      idEtab: _idEtab!,
      idQst: _selectedQuestion!.idQst,
      idFilter: idFilter,
    );
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // FIELD UPDATE — called by form widgets on change
  // Mirrors: script.js ctrl_saisie / ctrl_saisie_text
  // ═══════════════════════════════════════════════════════════════════════════

  void updateField(String fieldName, String value) {
    _formData[fieldName] = value;
    _hasUnsavedChanges = true;
    // Clear validation error for this field
    _validationErrors.remove(fieldName);
    notifyListeners();
  }

  /// Validates a single field against its rules.
  /// Returns the French error message, or null if valid.
  /// Mirrors: questions.js testVal(), testType(), ctrl_saisie()
  String? validateField(String fieldName, String value) {
    final fieldRules =
        _rules.where((r) => r.idChamp == fieldName).toList();
    if (fieldRules.isEmpty) return null;

    for (final rule in fieldRules) {
      final error = _applyRule(rule, value);
      if (error != null) return error;
    }
    return null;
  }

  String? _applyRule(ValidationRule rule, String value) {
    switch (rule.ruleType) {
      case 'mandatory':
        if (value.trim().isEmpty) return 'Ce champ est obligatoire';
        break;
      case 'type_int':
        if (value.isNotEmpty && int.tryParse(value) == null) {
          return 'Valeur entière requise';
        }
        break;
      case 'type_decimal':
        if (value.isNotEmpty) {
          final normalized = value.replaceAll(',', '.');
          if (double.tryParse(normalized) == null) {
            return 'Valeur numérique requise';
          }
        }
        break;
      case 'type_date':
        if (value.isNotEmpty) {
          final parts = value.split('/');
          if (parts.length != 3) return 'Format de date invalide (JJ/MM/AAAA)';
          final day = int.tryParse(parts[0]);
          final month = int.tryParse(parts[1]);
          final year = int.tryParse(parts[2]);
          if (day == null || month == null || year == null ||
              day < 1 || day > 31 || month < 1 || month > 12) {
            return 'Date invalide';
          }
        }
        break;
      case 'max_length':
        final maxLen = int.tryParse(rule.ruleValue ?? '') ?? 0;
        if (value.length > maxLen) {
          return 'Longueur maximale dépassée ($maxLen caractères)';
        }
        break;
      case 'min_val':
        final minVal = double.tryParse(rule.ruleValue ?? '') ?? 0;
        final val = double.tryParse(value.replaceAll(',', '.'));
        if (val != null && val < minVal) {
          return 'Valeur minimale : $minVal';
        }
        break;
      case 'max_val':
        final maxVal = double.tryParse(rule.ruleValue ?? '') ?? 0;
        final val = double.tryParse(value.replaceAll(',', '.'));
        if (val != null && val > maxVal) {
          return 'Valeur maximale : $maxVal';
        }
        break;
      case 'enum':
        // Allowed values separated by '|'
        final allowed =
            (rule.ruleValue ?? '').split('|').map((s) => s.trim()).toList();
        if (value.isNotEmpty && !allowed.contains(value)) {
          return 'Valeur non autorisée';
        }
        break;
    }
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // VALIDATE ALL — before save or send
  // ═══════════════════════════════════════════════════════════════════════════

  bool validateAll() {
    _validationErrors = {};
    bool isValid = true;
    for (final rule in _rules) {
      if (rule.ruleType == 'mandatory') {
        final val = _formData[rule.idChamp] ?? '';
        if (val.trim().isEmpty) {
          _validationErrors[rule.idChamp] = 'Ce champ est obligatoire';
          isValid = false;
        }
      }
    }
    // Run type/range checks on filled fields
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
    _isSaving = true;
    _error = null;
    _successMessage = null;
    notifyListeners();
    try {
      await _db.saveCollectedData(
        idCamp: _idCamp!,
        idEtab: _idEtab!,
        idQst: _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
        data: _formData,
      );
      _hasUnsavedChanges = false;
      _successMessage = 'Données sauvegardées localement';
      notifyListeners();
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
  // Builds POST body like getPageDataToSend() in original
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> sendToServer({required User user, bool online = true}) async {
    if (!online) {
      _error = 'Pas de connexion. Les données seront envoyées ultérieurement.';
      notifyListeners();
      return false;
    }
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) {
      return false;
    }

    // Save locally first
    await saveLocally();

    _isSending = true;
    _error = null;
    _successMessage = null;
    notifyListeners();

    try {
      final ok = await _api.saveData(
        campId: _idCamp!,
        etabId: _idEtab!,
        qstId: _selectedQuestion!.idQst,
        userId: user.idUser,
        filterId: _selectedFilter?.idFilter,
        data: _formData,
      );
      if (ok) {
        await _db.markCollectedDataSent(
          idCamp: _idCamp!,
          idEtab: _idEtab!,
          idQst: _selectedQuestion!.idQst,
          idFilter: _selectedFilter?.idFilter,
        );
        _successMessage = 'Données envoyées avec succès';
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
  // RELOAD FROM SERVER — mirrors stmPageEtab reload logic
  // ═══════════════════════════════════════════════════════════════════════════

  Future<bool> reloadFromServer({required User user}) async {
    if (_idCamp == null || _idEtab == null || _selectedQuestion == null) {
      return false;
    }
    _isReloading = true;
    _error = null;
    _successMessage = null;
    notifyListeners();
    try {
      final serverData = await _api.reloadData(
        campId: _idCamp!,
        etabId: _idEtab!,
        qstId: _selectedQuestion!.idQst,
        userId: user.idUser,
        filterId: _selectedFilter?.idFilter,
      );
      if (serverData == null) {
        _error = 'Aucune donnée retournée par le serveur';
        notifyListeners();
        return false;
      }
      // Overwrite local data with server values
      final Map<String, String> serverFields = {};
      serverData.forEach((k, v) => serverFields[k] = v.toString());

      await _db.deleteCollectedData(
        idCamp: _idCamp!,
        idEtab: _idEtab!,
        idQst: _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
      );
      await _db.saveCollectedData(
        idCamp: _idCamp!,
        idEtab: _idEtab!,
        idQst: _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
        data: serverFields,
      );
      await _db.markCollectedDataSent(
        idCamp: _idCamp!,
        idEtab: _idEtab!,
        idQst: _selectedQuestion!.idQst,
        idFilter: _selectedFilter?.idFilter,
      );

      _formData = Map.from(serverFields);
      _hasUnsavedChanges = false;
      _successMessage = 'Données rechargées depuis le serveur';
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
  // 2D MATRIX TOTAL CALCULATION
  // Replaces calc_total.js — calcul_Total_ThemeMat2D, set_TOTAL_ThemeMat2D,
  // calcul_Total_MatFrml, set_TOTAL_MatFrml
  //
  // The original used eval() on field names. Here we do it with pure Dart
  // without eval(), by iterating over formData keys with naming conventions.
  //
  // Naming convention (same as original):
  //   row total field:    total_r{rowIndex}
  //   column total field: total_c{colIndex}
  //   grand total field:  total_all
  //   formula total:      total_f{fieldName}
  // ═══════════════════════════════════════════════════════════════════════════

  /// Recalculates all row/column/grand totals for a 2D matrix form.
  /// Called whenever a numeric cell changes.
  Map<String, String> calculateMatrixTotals({
    required Map<String, String> data,
    required int rows,
    required int cols,
    required String cellPrefix, // e.g. 'val' → field names: val_{row}_{col}
  }) {
    final result = Map<String, String>.from(data);

    // Row totals
    for (int r = 0; r < rows; r++) {
      double rowTotal = 0;
      for (int c = 0; c < cols; c++) {
        final key = '${cellPrefix}_${r}_$c';
        rowTotal += double.tryParse(data[key] ?? '') ?? 0;
      }
      result['total_r$r'] = rowTotal.toStringAsFixed(0);
    }

    // Column totals
    for (int c = 0; c < cols; c++) {
      double colTotal = 0;
      for (int r = 0; r < rows; r++) {
        final key = '${cellPrefix}_${r}_$c';
        colTotal += double.tryParse(data[key] ?? '') ?? 0;
      }
      result['total_c$c'] = colTotal.toStringAsFixed(0);
    }

    // Grand total
    double grandTotal = 0;
    for (int r = 0; r < rows; r++) {
      grandTotal += double.tryParse(result['total_r$r'] ?? '') ?? 0;
    }
    result['total_all'] = grandTotal.toStringAsFixed(0);

    return result;
  }

  /// Recalculates a formula-based field total.
  /// [formula] is a list of field names to sum.
  /// Mirrors: set_Total_ChpsFrml, calcul_Total_MatFrml
  double calculateFormulaTotal(List<String> fieldNames) {
    double total = 0;
    for (final name in fieldNames) {
      total += double.tryParse(_formData[name] ?? '') ?? 0;
    }
    return total;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SEND ALL DATA (page_camp.js sendAllData)
  // Sends all collected data for the current campaign to the server.
  // ═══════════════════════════════════════════════════════════════════════════

  Future<Map<String, bool>> sendAllCampaignData({
    required User user,
    required String idCamp,
    required List<String> etabIds,
    required List<String> qstIds,
  }) async {
    final results = <String, bool>{};
    for (final etabId in etabIds) {
      for (final qstId in qstIds) {
        final data = await _db.getCollectedData(
          idCamp: idCamp,
          idEtab: etabId,
          idQst: qstId,
        );
        if (data.isEmpty) continue;
        try {
          final ok = await _api.saveData(
            campId: idCamp,
            etabId: etabId,
            qstId: qstId,
            userId: user.idUser,
            data: data,
          );
          results['${etabId}_$qstId'] = ok;
          if (ok) {
            await _db.markCollectedDataSent(
              idCamp: idCamp,
              idEtab: etabId,
              idQst: qstId,
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
  // HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  void clearMessages() {
    _error = null;
    _successMessage = null;
    notifyListeners();
  }

  void clearError() {
    _error = null;
    notifyListeners();
  }
}
