// =============================================================================
// coherence_evaluator.dart — Moteur d'évaluation de cohérence HORS LIGNE
// =============================================================================
//
// Ce fichier implémente l'évaluation offline des règles de cohérence StatEduc.
// Il est le pendant mobile de controle_theme_batch.class.php (côté serveur).
//
// CONTEXTE :
//   Le serveur évalue la cohérence en exécutant des requêtes SQL (sql_regle et
//   sql_assoc) sur la base Oracle/MySQL, puis compare les résultats avec un
//   opérateur (critere). Cette approche n'est pas possible hors ligne car le
//   mobile n'a pas accès à la base de données serveur.
//
// APPROCHE OFFLINE :
//   Les SQL serveur suivent un pattern prévisible :
//     SELECT SUM(NOM_CHAMP) FROM TABLE WHERE CODE_ETAB=X AND CODE_ANNEE=Y [...]
//
//   On extrait NOM_CHAMP par regex et on recherche sa valeur dans la table
//   SQLite `collected_data` (données saisies par l'agent de collecte).
//   La somme SUM() est calculée sur tous les filtres disponibles.
//
// PRINCIPE DE VIOLATION :
//   - Si la condition "V1 critere V2" est VRAIE → données cohérentes (OK)
//   - Si la condition est FAUSSE              → violation détectée (KO)
//   - _applyOperator() retourne true quand la règle EST violée
//
// CONSERVATISME :
//   Si un champ SQL ne peut pas être extrait ou trouvé dans collected_data,
//   la règle est silencieusement ignorée (pas de faux positifs).
//   Ce moteur ne signale que les violations CERTAINES.
//
// PATTERNS SQL SUPPORTÉS :
//   1. SELECT SUM(CHAMP) FROM ...
//   2. SELECT NVL(SUM(CHAMP),0) FROM ...    (Oracle NVL)
//   3. SELECT COALESCE(SUM(CHAMP),0) FROM ...
//   4. SELECT CHAMP FROM ...               (champ brut sans agrégation)
//
// PATTERNS SQL NON SUPPORTÉS (ignorés silencieusement) :
//   - JOINs multi-tables
//   - Sous-requêtes
//   - COUNT(), AVG(), MIN(), MAX() sans SUM()
//   - Fonctions SQL complexes
import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CoherenceEvaluator — Moteur d'évaluation de cohérence hors ligne.
///
/// Équivalent mobile de `controle_theme_batch.class.php` (serveur).
///
/// Principe de fonctionnement :
///   1. Charge les données persistées depuis SQLite (collected_data)
///   2. Superpose les données non sauvegardées (formData en mémoire)
///   3. Pour chaque règle : extrait V1 (sql_regle) et V2 (sql_assoc)
///   4. Applique l'opérateur critere : si NON respecté → violation
///
/// Différence avec le contrôle serveur :
///   - Serveur : exécute SQL réel sur Oracle/MySQL → résultat exact
///   - Offline  : extrait le nom de champ par regex et cherche dans SQLite
///     → approximation acceptable pour un feedback immédiat à l'agent
class CoherenceEvaluator {
  CoherenceEvaluator({
    required DatabaseService db,
  }) : _db = db;

  final DatabaseService _db;

  // ═══════════════════════════════════════════════════════════════════════════
  // API PUBLIQUE
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue toutes les règles de cohérence pour le contexte donné.
  /// Retourne la liste des violations (liste vide = cohérence OK ou non évaluable).
  ///
  /// Paramètres :
  ///   [rules]    — règles pour ce (idCamp, idQst, idEtab), chargées depuis SQLite
  ///   [formData] — données formulaire en mémoire (peuvent contenir des modifs non sauvegardées)
  ///   [idCamp], [idQst], [idEtab], [idFilter] — contexte de saisie courant
  ///
  /// Algorithme :
  ///   1. Construit values = collected_data SQLite + formData (override)
  ///      (les clés de champ sont mises en MAJUSCULES pour comparaison insensible à la casse)
  ///   2. Pour chaque règle : _extractValue(sql) → valeur numérique ou null
  ///   3. Si V1 ou V2 non trouvé → règle ignorée silencieusement
  ///   4. _applyOperator(V1, V2, critere) → true si VIOLÉE
  ///   5. Violation → OfflineCoherenceError ajouté à la liste retournée
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
  // HELPERS PRIVÉS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Extrait une valeur numérique depuis [sql] en analysant le nom de champ
  /// et en le cherchant dans [values] (map NOM_CHAMP → valeur double).
  ///
  /// Patterns SQL supportés :
  ///   SELECT SUM(NOM_CHAMP) FROM ...                      → Pattern 1
  ///   SELECT NVL(SUM(NOM_CHAMP),0) FROM ...               → Pattern 1 (Oracle NVL)
  ///   SELECT COALESCE(SUM(NOM_CHAMP),0) FROM ...          → Pattern 1
  ///   SELECT NOM_CHAMP FROM ...                           → Pattern 2
  ///
  /// Retourne null si :
  ///   - Le pattern n'est pas reconnu (JOIN, sous-requête, etc.)
  ///   - Le nom de champ n'est pas trouvé dans [values]
  ///   - Le SQL est vide
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

  /// Calcule la somme d'un champ sur tous les filtres disponibles dans [values].
  /// Les clés peuvent avoir un suffixe filtre : "NOM_CHAMP#ID_FILTRE".
  /// Si aucun filtre n'est trouvé, retourne values[fieldName] directement.
  /// Retourne null si le champ n'existe pas (pour que l'appelant puisse ignorer
  /// la règle — comportement conservatif, évite les faux positifs).
  double? _sumFieldAcrossAllFilters(String fieldName, Map<String, double> values) {
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
    // Retourne null si le champ est introuvable — le moteur ignorera silencieusement
    // la règle plutôt que d'évaluer avec 0 (évite les faux positifs/négatifs).
    return found ? sum : null;
  }

  /// Applique l'opérateur critere et retourne TRUE si la règle est VIOLÉE.
  ///
  /// IMPORTANT : retourne true quand la contrainte N'EST PAS respectée.
  ///   critere '<=': règle = "V1 doit être <= V2"  → violée si V1 > V2  → return !(V1 <= V2)
  ///   critere '>=': règle = "V1 doit être >= V2"  → violée si V1 < V2  → return !(V1 >= V2)
  ///
  /// Opérateurs reconnus (observés dans StatEduc) : <=  >=  <  >  =  !=  <>
  /// Opérateur inconnu → return false (pas de violation — conservatisme).
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

// ─── Modèle de violation offline ─────────────────────────────────────────────

/// Violation de cohérence détectée par le moteur offline (CoherenceEvaluator).
/// Équivalent de CoherenceError (version serveur) mais avec les valeurs calculées
/// V1 et V2 pour affichage à l'agent de collecte.
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
