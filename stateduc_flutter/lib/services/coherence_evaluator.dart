import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CoherenceEvaluator — offline coherence check engine.
///
/// Context:
///   The server's coherence system works by evaluating two SQL rules
///   (sql_regle and sql_assoc), each of which returns a single numeric
///   value from the production database, then comparing them with a
///   `critere` operator (e.g. "<=", ">", "=").
///
/// Offline approach:
///   Server SQL is against the server DB (MySQL/Oracle) and cannot run
///   locally.  However the SQL follows a predictable pattern:
///
///     SELECT SUM(FIELD_NAME) FROM SOME_TABLE
///       WHERE CODE_ETAB = 'X' AND CODE_ANNEE = Y [AND ...]
///
///   We extract FIELD_NAME with a regex, then query the local
///   `collected_data` table for the matching field value that the user
///   has entered for the current school+question+filter context.
///
///   SUM aggregates across all rows with that field name (e.g. across
///   multiple filter periods for the same question).
///
/// Limitations:
///   - Only supports `SELECT SUM(field)` or `SELECT field` patterns.
///   - Multi-table JOINs or subqueries are not evaluated (skipped).
///   - If the field cannot be found in collected_data the rule is skipped.
///
/// This evaluator is intentionally conservative: it returns violations
/// only when it is CERTAIN of a problem. When in doubt it stays silent.
class CoherenceEvaluator {
  CoherenceEvaluator({
    required DatabaseService db,
  }) : _db = db;

  final DatabaseService _db;

  // ═══════════════════════════════════════════════════════════════════════════
  // PUBLIC API
  // ═══════════════════════════════════════════════════════════════════════════

  /// Evaluates all coherence rules for the given context and returns a list
  /// of violations (empty list = all rules pass or cannot be evaluated).
  ///
  /// [rules]    — rules for this (idCamp, idQst, idEtab) context
  /// [formData] — current form data Map<fieldName, value> (may contain unsaved changes)
  /// [idCamp], [idQst], [idEtab], [idFilter] — current data entry context
  Future<List<OfflineCoherenceError>> evaluate({
    required List<CoherenceRule> rules,
    required Map<String, String> formData,
    required String idCamp,
    required String idQst,
    required String idEtab,
    String? idFilter,
  }) async {
    if (rules.isEmpty) return [];

    // Build a combined data map: DB values + any unsaved formData overrides
    final Map<String, double> values = {};

    // Load persisted collected_data for this context
    final persistedData = await _db.getCollectedData(
      idCamp:   idCamp,
      idEtab:   idEtab,
      idQst:    idQst,
      idFilter: idFilter,
    );
    for (final entry in persistedData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) values[entry.key.toUpperCase()] = v;
    }
    // Override with unsaved formData (in-memory)
    for (final entry in formData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) values[entry.key.toUpperCase()] = v;
    }

    final violations = <OfflineCoherenceError>[];

    for (final rule in rules) {
      try {
        final v1 = _extractValue(rule.sqlRegle, values);
        final v2 = _extractValue(rule.sqlAssoc, values);

        if (v1 == null || v2 == null) {
          // Cannot evaluate — skip silently
          debugPrint(
              '[CoherenceEval] skip idRegle=${rule.idRegle} — field not found in collected data');
          continue;
        }

        final violated = _applyOperator(v1, v2, rule.critere);
        if (violated) {
          violations.add(OfflineCoherenceError(
            idRegle:      rule.idRegle,
            idRegleAssoc: rule.idRegleAssoc,
            libRegle:     rule.libRegle,
            libRegleAssoc: rule.libRegleAssoc,
            critere:      rule.critere,
            message:      rule.message.isNotEmpty
                ? rule.message
                : '${rule.libRegle} doit être ${_critereLabelFr(rule.critere)} ${rule.libRegleAssoc}',
            value1:       v1,
            value2:       v2,
          ));
        }
      } catch (e) {
        debugPrint('[CoherenceEval] error evaluating rule ${rule.idRegle}: $e');
      }
    }
    return violations;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // PRIVATE HELPERS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Extracts a numeric value from [sql] by parsing the field name and
  /// looking it up in [values].
  ///
  /// Supported patterns:
  ///   SELECT SUM(FIELD_NAME) FROM ...
  ///   SELECT FIELD_NAME FROM ...
  ///   SELECT NVL(SUM(FIELD_NAME),0) FROM ...   (Oracle NVL)
  ///   SELECT COALESCE(SUM(FIELD_NAME),0) FROM ...
  double? _extractValue(String sql, Map<String, double> values) {
    if (sql.trim().isEmpty) return null;
    final upperSql = sql.toUpperCase();

    // Pattern 1: SELECT [NVL(|COALESCE(] SUM(FIELD) [),0)] FROM
    final sumMatch = RegExp(
      r'SELECT\s+(?:NVL\s*\(\s*|COALESCE\s*\(\s*)?SUM\s*\(\s*(\w+)\s*\)',
      caseSensitive: false,
    ).firstMatch(upperSql);

    if (sumMatch != null) {
      final fieldName = sumMatch.group(1)!;
      return _sumFieldAcrossAllFilters(fieldName, values);
    }

    // Pattern 2: SELECT FIELD FROM (bare field without aggregation)
    final bareMatch = RegExp(
      r'SELECT\s+(\w+)\s+FROM',
      caseSensitive: false,
    ).firstMatch(upperSql);

    if (bareMatch != null) {
      final fieldName = bareMatch.group(1)!;
      // Skip SQL keywords that aren't field names
      const sqlKeywords = {'DISTINCT', 'TOP', 'ALL', 'COUNT', 'AVG', 'MIN', 'MAX'};
      if (sqlKeywords.contains(fieldName)) return null;
      return values[fieldName];
    }

    return null;
  }

  /// For SUM() rules, sum all values with this field name across the values map.
  /// In practice the map already contains a single entry per field (per filter).
  double _sumFieldAcrossAllFilters(String fieldName, Map<String, double> values) {
    double sum = 0;
    bool found = false;
    for (final entry in values.entries) {
      // Field names may have a filter suffix like FIELD#FILTER_ID
      final key = entry.key.split('#').first;
      if (key == fieldName) {
        sum += entry.value;
        found = true;
      }
    }
    return found ? sum : (values[fieldName] ?? 0);
  }

  /// Returns true when the rule is VIOLATED (i.e. the constraint is NOT met).
  ///
  /// The critere from the server describes the REQUIRED relationship: v1 critere v2.
  /// The rule is violated when that relationship does NOT hold.
  ///
  /// Operators observed in StatEduc:
  ///   <=  >=  <  >  =  !=  <>
  bool _applyOperator(double v1, double v2, String critere) {
    switch (critere.trim()) {
      case '<=': return !(v1 <= v2);
      case '>=': return !(v1 >= v2);
      case '<':  return !(v1 <  v2);
      case '>':  return !(v1 >  v2);
      case '=':  return !(v1 == v2);
      case '!=':
      case '<>': return !(v1 != v2);
      default:
        debugPrint('[CoherenceEval] unknown critere "$critere" — skipping');
        return false;
    }
  }

  /// Returns a human-readable French label for a critere operator.
  String _critereLabelFr(String critere) {
    switch (critere.trim()) {
      case '<=': return 'inférieur ou égal à';
      case '>=': return 'supérieur ou égal à';
      case '<':  return 'strictement inférieur à';
      case '>':  return 'strictement supérieur à';
      case '=':  return 'égal à';
      case '!=':
      case '<>': return 'différent de';
      default:   return critere;
    }
  }
}

// ─── Offline violation model ─────────────────────────────────────────────────

/// A coherence rule violation detected by the offline engine.
class OfflineCoherenceError {
  final int    idRegle;
  final int    idRegleAssoc;
  final String libRegle;
  final String libRegleAssoc;
  final String critere;
  final String message;
  final double value1;
  final double value2;

  const OfflineCoherenceError({
    required this.idRegle,
    required this.idRegleAssoc,
    required this.libRegle,
    required this.libRegleAssoc,
    required this.critere,
    required this.message,
    required this.value1,
    required this.value2,
  });
}
