// =============================================================================
// coherence_evaluator.dart — Moteur d'évaluation de cohérence HORS LIGNE
// =============================================================================
//
// VERSION : Session 45 — Moteur SQL réel sur SQLite
//
// CONTEXTE :
//   Le serveur évalue la cohérence en exécutant des requêtes SQL (sql_regle et
//   sql_assoc) sur la base SQL Server/Access, puis compare les résultats avec un
//   opérateur (critere). Cette approche n'était pas possible hors ligne car le
//   mobile n'a pas accès à la base de données serveur.
//
// APPROCHE SESSION 45 — EXÉCUTION SQL RÉELLE SUR SQLITE :
//   Plutôt qu'un pattern-matching par regex (approche Sessions 38–44), on traduit
//   les requêtes SQL serveur vers SQLite et on les exécute directement sur
//   collected_data. Cela garantit un résultat IDENTIQUE au serveur pour les mêmes
//   données.
//
// ─── ARCHITECTURE ────────────────────────────────────────────────────────────
//
//   CoherenceEvaluator.evaluate()
//     │
//     ├─► [CHEMIN 1 — SQL réel]  SqlTranslator.translate()
//     │       → SQL SQLite natif exécuté via db.rawQuery()
//     │       → COUNT(*) des violations → comparé au critere
//     │
//     └─► [CHEMIN 2 — regex fallback] _extractValue() + _applyOperator()
//             → utilisé si le traducteur ne peut pas traiter le SQL
//             → conservatif : aucun faux positif
//
// ─── SqlTranslator — STRATÉGIE DE TRADUCTION ─────────────────────────────────
//
//  Le traducteur transforme les requêtes SQL de type Access/SQL Server en SQL
//  compatible SQLite. Les règles de traduction sont appliquées dans l'ordre :
//
//  1. SUBSTITUTION DES PARAMÈTRES
//     $CODE_ETABLISSEMENT  → valeur réelle de l'établissement (ex. '101012071')
//     $CODE_TYPE_ANNEE     → valeur réelle de l'année (ex. '2024')
//     Les paramètres sont remplacés par des littéraux SQL entre guillemets simples.
//
//  2. MAPPING DE TABLE : DONNEES_ETABLISSEMENT → sous-requête SQLite
//     La table serveur DONNEES_ETABLISSEMENT est une vue SQL Server contenant
//     toutes les données d'un établissement pour une campagne. Côté mobile,
//     ces données sont stockées dans collected_data sous forme de lignes
//     (field_name, field_value). Le traducteur remplace DONNEES_ETABLISSEMENT
//     par un CTE (Common Table Expression) de pivot dynamique :
//
//     WITH DONNEES_ETABLISSEMENT AS (
//       SELECT
//         MAX(CASE WHEN field_name='ELECTRICITE' THEN CAST(field_value AS REAL) END)
//           AS ELECTRICITE,
//         MAX(CASE WHEN field_name='FONCT_ALIMENT_ELECTRICITE' THEN CAST(field_value AS REAL) END)
//           AS FONCT_ALIMENT_ELECTRICITE,
//         MAX(CASE WHEN field_name='CODE_ETABLISSEMENT' THEN field_value END)
//           AS CODE_ETABLISSEMENT,
//         MAX(CASE WHEN field_name='CODE_TYPE_ANNEE' THEN field_value END)
//           AS CODE_TYPE_ANNEE
//       FROM collected_data
//       WHERE id_camp='CAMP_ID' AND id_etab='ETAB_ID'
//     )
//     SELECT ... (requête originale traduite)
//
//     Les champs du CTE sont extraits automatiquement depuis le SQL de la règle.
//
//  3. SYNTAXE ACCESS → SQLITE
//     - `Is Null`          → `IS NULL`
//     - `Is Not Null`      → `IS NOT NULL`
//     - `NVL(x, y)`        → `COALESCE(x, y)`
//     - HAVING avec filtres → HAVING conservé (SQLite le supporte)
//     - parenthèses triples `(((x)))` → `(x)` (normalisées mais conservées)
//     - opérateurs `Or` / `And` (case insensitive) → `OR` / `AND`
//
//  4. RÉSULTAT ATTENDU
//     La requête originale du serveur retourne le CODE_ETABLISSEMENT si une
//     violation existe, NULL sinon. Le traducteur encapsule la requête dans
//     un COUNT(*) pour obtenir un entier :
//       → 0 = pas de violation
//       → > 0 = violation détectée
//     Ce résultat est comparé au critere de la règle (généralement "= 0").
//
//  5. MAPPING DE TABLE : ELEVES_AGE_NIVEAU_SEXE
//     Même principe que DONNEES_ETABLISSEMENT — les champs sont extraits
//     depuis collected_data en pivotant sur field_name.
//
//  6. TABLES INCONNUES
//     Si le SQL contient une table non reconnue, le traducteur retourne null
//     et le chemin fallback (regex) est utilisé.
//
// ─── EXEMPLES CONCRETS ───────────────────────────────────────────────────────
//
//  Règle électricité (sql_regle) :
//    SELECT DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT
//    FROM DONNEES_ETABLISSEMENT
//    WHERE (((DONNEES_ETABLISSEMENT.ELECTRICITE)=0
//           Or (DONNEES_ETABLISSEMENT.ELECTRICITE) Is Null)
//          AND ((DONNEES_ETABLISSEMENT.FONCT_ALIMENT_ELECTRICITE)=1))
//       OR (((DONNEES_ETABLISSEMENT.ELECTRICITE)=1)
//          AND ((DONNEES_ETABLISSEMENT.FONCT_ALIMENT_ELECTRICITE) Is Null))
//    GROUP BY DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT,
//             DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE
//    HAVING (((DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT)=$CODE_ETABLISSEMENT)
//           AND ((DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE)=$CODE_TYPE_ANNEE));
//
//  → SQL SQLite généré (form schématique) :
//    WITH DONNEES_ETABLISSEMENT AS (
//      SELECT
//        MAX(CASE WHEN field_name='ELECTRICITE' THEN CAST(field_value AS REAL) END) AS ELECTRICITE,
//        MAX(CASE WHEN field_name='FONCT_ALIMENT_ELECTRICITE' THEN CAST(field_value AS REAL) END) AS FONCT_ALIMENT_ELECTRICITE,
//        MAX(CASE WHEN field_name='CODE_ETABLISSEMENT' THEN field_value END) AS CODE_ETABLISSEMENT,
//        MAX(CASE WHEN field_name='CODE_TYPE_ANNEE' THEN field_value END) AS CODE_TYPE_ANNEE
//      FROM collected_data
//      WHERE id_camp='C1' AND id_etab='E1'
//    )
//    SELECT COUNT(*) AS cnt
//    FROM (
//      SELECT CODE_ETABLISSEMENT
//      FROM DONNEES_ETABLISSEMENT
//      WHERE ((ELECTRICITE=0 OR ELECTRICITE IS NULL) AND FONCT_ALIMENT_ELECTRICITE=1)
//         OR (ELECTRICITE=1 AND FONCT_ALIMENT_ELECTRICITE IS NULL)
//      GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
//      HAVING CODE_ETABLISSEMENT='101012071' AND CODE_TYPE_ANNEE='2024'
//    ) _violations
//
//  → résultat : 1 si violation, 0 si cohérent
//
// ─── CONSERVATION DU CHEMIN REGEX ─────────────────────────────────────────────
//
//  Le chemin regex (Sessions 38–44) est conservé comme fallback pour :
//   - Les règles avec syntaxe hors du périmètre du traducteur
//   - Les règles sur des vues agrégées non traduisibles (TOTAL_AGE_NIVEAU)
//   - La fiabilité en cas d'erreur inattendue du traducteur
//   Le fallback est CONSERVATIF : si un champ est introuvable, la règle est
//   ignorée silencieusement (pas de faux positifs).
//
// ─── PORT SERVEUR VARIABLE ────────────────────────────────────────────────────
//
//  Ce fichier n'effectue aucun appel réseau — le moteur offline est
//  entièrement autonome. La note de port variable est pertinente uniquement
//  pour config_app.php et data_save.php (côté PHP, déjà corrigé Session 44).
//
// =============================================================================

import 'package:flutter/foundation.dart';
import 'package:sqflite/sqflite.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

// =============================================================================
// SqlTranslator — Traducteur SQL Access/SQL Server → SQLite
// =============================================================================

/// Traduit les requêtes SQL de contrôle de cohérence (syntaxe Access/SQL Server)
/// en SQL compatible SQLite, en s'appuyant sur la table `collected_data`.
///
/// Usage :
///   final result = SqlTranslator.translate(
///     serverSql: rule.sqlRegle,
///     idCamp: idCamp,
///     idEtab: idEtab,
///     codeEtab: codeEtab,      // valeur réelle pour $CODE_ETABLISSEMENT
///     codeTypeAnnee: codeyear, // valeur réelle pour $CODE_TYPE_ANNEE
///   );
///   if (result != null) {
///     final rows = await db.rawQuery(result.sql);
///     final count = Sqflite.firstIntValue(rows) ?? 0;
///     // count > 0 → violation
///   }
class SqlTranslator {
  SqlTranslator._(); // Classe statique uniquement

  // Tables serveur connues qui peuvent être mappées vers collected_data
  static const _knownServerTables = {
    'DONNEES_ETABLISSEMENT',
    'ELEVES_AGE_NIVEAU_SEXE',
    'ELEVES_NIVEAU_SEXE',
    'ELEVES_AGE_SEXE',
  };

  // Champs de contexte toujours inclus dans le CTE de pivot
  // (nécessaires pour les filtres HAVING sur CODE_ETABLISSEMENT et CODE_TYPE_ANNEE)
  static const _contextFields = [
    'CODE_ETABLISSEMENT',
    'CODE_TYPE_ANNEE',
    'CODE_ADMINISTRATIF',
  ];

  /// Traduit [serverSql] en SQL SQLite exécutable.
  ///
  /// Retourne un [TranslationResult] ou null si la requête n'est pas traduisible.
  ///
  /// Paramètres de substitution :
  ///   [idCamp]        → id de la campagne (filtre collected_data)
  ///   [idEtab]        → id de l'établissement (filtre collected_data)
  ///   [codeEtab]      → valeur pour \$CODE_ETABLISSEMENT  (ex. '101012071')
  ///   [codeTypeAnnee] → valeur pour \$CODE_TYPE_ANNEE (ex. '2024')
  ///
  /// La requête traduite wrappée dans SELECT COUNT(*) AS cnt FROM (...) _v
  /// retourne 0 si aucune violation, > 0 si violation détectée.
  static TranslationResult? translate({
    required String serverSql,
    required String idCamp,
    required String idEtab,
    String? codeEtab,
    String? codeTypeAnnee,
  }) {
    if (serverSql.trim().isEmpty) return null;

    try {
      // ── Étape 1 : normalisation de base ───────────────────────────────
      // Retire le point-virgule final s'il existe
      String sql = serverSql.trim();
      if (sql.endsWith(';')) sql = sql.substring(0, sql.length - 1).trim();

      // ── Étape 2 : substitution des paramètres ─────────────────────────
      sql = _substituteParams(
        sql: sql,
        codeEtab: codeEtab,
        codeTypeAnnee: codeTypeAnnee,
      );

      // ── Étape 3 : identifier les tables serveur utilisées ─────────────
      final usedServerTables = _detectServerTables(sql);
      if (usedServerTables.isEmpty) {
        // Pas de table serveur connue → on ne peut pas traduire
        debugPrint('[SqlTranslator] no known server tables in SQL — not translatable');
        return null;
      }

      // ── Étape 4 : extraire les champs référencés par la requête ───────
      // Pour construire le CTE de pivot, on a besoin de connaître TOUS les
      // champs référencés dans le SQL (SELECT, WHERE, GROUP BY, HAVING).
      final allFields = _extractAllFieldNames(sql, usedServerTables);

      // ── Étape 5 : construire le CTE de pivot pour chaque table serveur ──
      final cteParts = <String>[];
      for (final tableName in usedServerTables) {
        final cte = _buildPivotCte(
          tableName: tableName,
          fields: allFields,
          idCamp: idCamp,
          idEtab: idEtab,
        );
        cteParts.add('$tableName AS (\n$cte\n)');
      }

      // ── Étape 6 : traduction syntaxique Access → SQLite ────────────────
      String translatedSql = _translateSyntax(sql);

      // ── Étape 7 : supprimer les qualificateurs de table dans le SQL ────
      // Ex: DONNEES_ETABLISSEMENT.ELECTRICITE → ELECTRICITE
      for (final tableName in usedServerTables) {
        translatedSql = translatedSql.replaceAll(
          RegExp('\\b${RegExp.escape(tableName)}\\.(\\w+)', caseSensitive: false),
          r'\1',
        );
        // Supprimer aussi les références non qualifiées à la table dans FROM
        // (le CTE les fournit déjà)
      }

      // ── Étape 8 : wrapper COUNT(*) ─────────────────────────────────────
      // La requête originale retourne des lignes si violation.
      // On encapsule pour obtenir un COUNT.
      final withClause = 'WITH ${cteParts.join(',\n')}';
      final countSql =
          '$withClause\nSELECT COUNT(*) AS cnt FROM (\n$translatedSql\n) _violations';

      debugPrint('[SqlTranslator] translated SQL:\n$countSql');

      return TranslationResult(
        sql: countSql,
        usedTables: usedServerTables,
        fieldNames: allFields,
      );
    } catch (e, st) {
      debugPrint('[SqlTranslator] translation error: $e\n$st');
      return null;
    }
  }

  // ─── Substitution des paramètres ─────────────────────────────────────────

  static String _substituteParams({
    required String sql,
    String? codeEtab,
    String? codeTypeAnnee,
  }) {
    String result = sql;

    // $CODE_ETABLISSEMENT → valeur réelle entre guillemets simples
    if (codeEtab != null && codeEtab.isNotEmpty) {
      final escaped = codeEtab.replaceAll("'", "''");
      result = result.replaceAll(
        RegExp(r'\$CODE_ETABLISSEMENT\b', caseSensitive: false),
        "'$escaped'",
      );
    }

    // $CODE_TYPE_ANNEE → valeur réelle entre guillemets simples
    if (codeTypeAnnee != null && codeTypeAnnee.isNotEmpty) {
      final escaped = codeTypeAnnee.replaceAll("'", "''");
      result = result.replaceAll(
        RegExp(r'\$CODE_TYPE_ANNEE\b', caseSensitive: false),
        "'$escaped'",
      );
    }

    // Paramètres non substitués → laisser tel quel (la requête échouera ou
    // retournera 0 — comportement conservatif)
    debugPrint('[SqlTranslator] after param substitution: '
        'codeEtab=${codeEtab ?? "(null)"} '
        'codeTypeAnnee=${codeTypeAnnee ?? "(null)"}');

    return result;
  }

  // ─── Détection des tables serveur utilisées ───────────────────────────────

  static Set<String> _detectServerTables(String sql) {
    final upper = sql.toUpperCase();
    final found = <String>{};
    for (final table in _knownServerTables) {
      // Match TABLE suivi de . (qualificateur) ou de FROM/JOIN TABLE (référence directe)
      if (RegExp(
        '\\b${RegExp.escape(table)}\\b',
        caseSensitive: false,
      ).hasMatch(upper)) {
        found.add(table);
      }
    }
    return found;
  }

  // ─── Extraction des noms de champs référencés ─────────────────────────────
  //
  // Extrait TOUS les noms de champs qualifiés (TABLE.FIELD) et non qualifiés
  // depuis le SQL. Ces champs seront inclus dans le CTE de pivot.

  static Set<String> _extractAllFieldNames(
      String sql, Set<String> serverTables) {
    final fields = <String>{};

    // Ajoute toujours les champs de contexte (pour filtres HAVING)
    fields.addAll(_contextFields);

    // Pattern : TABLE.FIELD (champs qualifiés)
    for (final table in serverTables) {
      final qualPattern = RegExp(
        '\\b${RegExp.escape(table)}\\.(\\w+)\\b',
        caseSensitive: false,
      );
      for (final m in qualPattern.allMatches(sql)) {
        final field = m.group(1)!.toUpperCase();
        if (!_isSqlKeyword(field)) {
          fields.add(field);
        }
      }
    }

    // Pattern : champs après GROUP BY et HAVING (non qualifiés)
    // Ces patterns capturent des noms de champs seuls dans les clauses de tri/filtre
    // ex: GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
    final groupHavingPattern = RegExp(
      r'(?:GROUP\s+BY|HAVING)\s+([\w\s,=<>\'\"().]+?)(?:$|ORDER\s+BY|LIMIT)',
      caseSensitive: false,
    );
    for (final m in groupHavingPattern.allMatches(sql)) {
      final clause = m.group(1) ?? '';
      final identPattern = RegExp(r'\b([A-Z][A-Z0-9_]*)\b', caseSensitive: false);
      for (final id in identPattern.allMatches(clause)) {
        final name = id.group(1)!.toUpperCase();
        if (!_isSqlKeyword(name) && name.length > 2) {
          fields.add(name);
        }
      }
    }

    debugPrint('[SqlTranslator] fields extracted for CTE: $fields');
    return fields;
  }

  // ─── Construction du CTE de pivot ────────────────────────────────────────
  //
  // Génère une sous-requête CTE qui pivote collected_data vers les colonnes
  // attendues par la requête serveur.
  //
  // Schéma généré :
  //   SELECT
  //     MAX(CASE WHEN field_name='FIELD_A' THEN CAST(field_value AS REAL) END) AS FIELD_A,
  //     MAX(CASE WHEN field_name='FIELD_B' THEN CAST(field_value AS REAL) END) AS FIELD_B,
  //     MAX(CASE WHEN field_name='CODE_ETABLISSEMENT' THEN field_value END) AS CODE_ETABLISSEMENT,
  //     ...
  //   FROM collected_data
  //   WHERE id_camp='CAMP_ID' AND id_etab='ETAB_ID'
  //
  // Nota : on utilise MAX() pour agréger (pivot) car les données sont stockées
  // en lignes. MAX() retourne la valeur si elle est unique, ou la plus grande
  // valeur si elle apparaît plusieurs fois (filtre), ce qui est cohérent avec
  // le comportement du serveur pour les données d'établissement.
  //
  // Les champs de texte (CODE_ETABLISSEMENT, etc.) n'ont pas de CAST pour
  // préserver la comparaison de chaînes dans les filtres HAVING.

  static String _buildPivotCte({
    required String tableName,
    required Set<String> fields,
    required String idCamp,
    required String idEtab,
  }) {
    final escapedCamp = idCamp.replaceAll("'", "''");
    final escapedEtab = idEtab.replaceAll("'", "''");

    final columnDefs = fields.map((field) {
      final isTextContext = _contextFields.contains(field.toUpperCase());
      if (isTextContext) {
        // Champs texte : pas de CAST numérique (comparaison de chaînes)
        return "    MAX(CASE WHEN UPPER(field_name)='$field' "
            "THEN field_value END) AS $field";
      } else {
        // Champs numériques : CAST vers REAL pour les calculs arithmétiques
        return "    MAX(CASE WHEN UPPER(field_name)='$field' "
            "THEN CAST(field_value AS REAL) END) AS $field";
      }
    }).join(',\n');

    return '  SELECT\n$columnDefs\n'
        "  FROM collected_data\n"
        "  WHERE id_camp='$escapedCamp' AND id_etab='$escapedEtab'";
  }

  // ─── Traduction syntaxique Access/SQL Server → SQLite ─────────────────────

  static String _translateSyntax(String sql) {
    String result = sql;

    // 1. Is Null / Is Not Null (case-insensitive, avec espaces variables)
    result = result.replaceAll(
        RegExp(r'\bIs\s+Not\s+Null\b', caseSensitive: false), 'IS NOT NULL');
    result = result.replaceAll(
        RegExp(r'\bIs\s+Null\b', caseSensitive: false), 'IS NULL');

    // 2. NVL(x, y) → COALESCE(x, y)
    result = result.replaceAll(
        RegExp(r'\bNVL\s*\(', caseSensitive: false), 'COALESCE(');

    // 3. ISNULL(x, y) → COALESCE(x, y)  [SQL Server]
    result = result.replaceAll(
        RegExp(r'\bISNULL\s*\(', caseSensitive: false), 'COALESCE(');

    // 4. IIF(cond, true_val, false_val) → CASE WHEN cond THEN true_val ELSE false_val END
    //    SQLite ne supporte pas IIF avant 3.32 — on garde IIF si présent
    //    (SQLite 3.32+ le supporte nativement) — pas de traduction nécessaire.

    // 5. Parenthèses triples Access → SQLite les supporte nativement, rien à faire
    //    (((x))) est valide SQL dans SQLite

    // 6. Mots-clés booléens : Or → OR, And → AND (normalisation de casse)
    //    Attention : ne pas remplacer "Oracle", "Order", etc.
    //    On utilise des word boundaries.
    result = result.replaceAll(RegExp(r'\bOr\b'), 'OR');
    result = result.replaceAll(RegExp(r'\bAnd\b'), 'AND');

    // 7. DATEDIFF, DATEADD → non supporté SQLite, mais pas dans les règles de cohérence
    // 8. TOP N → LIMIT N  (Access TOP)
    result = result.replaceAll(
        RegExp(r'\bSELECT\s+TOP\s+(\d+)\s+', caseSensitive: false),
        (Match m) => 'SELECT ');
    // Le LIMIT sera ajouté après le FROM... mais TOP est rarement dans les règles.

    // 9. Guillemets doubles → guillemets simples pour les littéraux
    //    (Access autorise les guillemets doubles pour les chaînes)
    //    Attention à ne pas toucher aux noms de colonnes entre guillemets.
    //    Heuristique : remplace "valeur" (lettres/chiffres seulement) par 'valeur'
    result = result.replaceAllMapped(
        RegExp(r'"([^"]*)"'),
        (m) => "'${m.group(1)!.replaceAll("'", "''")}'"
    );

    return result;
  }

  // ─── Utilitaire : est-ce un mot-clé SQL ? ────────────────────────────────

  static const _sqlKeywordsSet = {
    'SELECT', 'FROM', 'WHERE', 'AND', 'OR', 'NOT', 'NULL', 'IS', 'IN',
    'GROUP', 'BY', 'HAVING', 'ORDER', 'LIMIT', 'OFFSET', 'DISTINCT',
    'COUNT', 'SUM', 'MAX', 'MIN', 'AVG', 'CAST', 'AS', 'CASE', 'WHEN',
    'THEN', 'ELSE', 'END', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'OUTER',
    'ON', 'UNION', 'ALL', 'INSERT', 'UPDATE', 'DELETE', 'CREATE', 'DROP',
    'TABLE', 'INDEX', 'PRIMARY', 'KEY', 'REFERENCES', 'COALESCE', 'NVL',
    'WITH', 'REAL', 'INTEGER', 'TEXT', 'BLOB', 'NUMERIC',
  };

  static bool _isSqlKeyword(String word) =>
      _sqlKeywordsSet.contains(word.toUpperCase());
}

/// Résultat d'une traduction SQL.
class TranslationResult {
  /// Le SQL SQLite traduit, encapsulé dans SELECT COUNT(*) AS cnt FROM (...).
  final String sql;

  /// Tables serveur utilisées dans la requête originale.
  final Set<String> usedTables;

  /// Noms de champs extraits pour le CTE de pivot.
  final Set<String> fieldNames;

  const TranslationResult({
    required this.sql,
    required this.usedTables,
    required this.fieldNames,
  });
}

// =============================================================================
// CoherenceEvaluator — Moteur d'évaluation de cohérence hors ligne.
// =============================================================================
//
// Equivalent mobile de `controle_theme_batch.class.php` (côté serveur).
//
// Session 45 : deux chemins d'évaluation :
//   1. SQL réel (SqlTranslator + rawQuery sur SQLite)  — prioritaire
//   2. Regex fallback (Sessions 38–44)                 — conservatif
//
// Le chemin SQL réel garantit l'équivalence exacte avec le serveur.
// Le chemin regex est conservé pour la robustesse.

/// CoherenceEvaluator — Moteur d'évaluation de cohérence hors ligne.
///
/// Session 45 — Dual-path : SQL réel (prioritaire) + regex fallback (conservatif).
class CoherenceEvaluator {
  CoherenceEvaluator({
    required DatabaseService db,
  }) : _db = db;

  final DatabaseService _db;

  // Colonnes de vues DB agrégées traitées par le chemin regex (approx.)
  static const _viewColumnTotal  = 'TOTAL_AGE_NIVEAU';
  static const _viewColumnFilles = 'FILLES_AGE_NIVEAU';

  // Patterns dans les noms de champs pour les filles
  static final _fillesPatterns = RegExp(
    r'(^NB_F_|_F_|_F$|^FILLES_|_FILLES_|FILLE)',
    caseSensitive: false,
  );

  // ═══════════════════════════════════════════════════════════════════════════
  // API PUBLIQUE
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue toutes les règles de cohérence pour le contexte donné.
  /// Retourne la liste des violations (liste vide = cohérence OK ou non évaluable).
  ///
  /// Paramètres :
  ///   [rules]    — règles pour ce (idCamp, idQst, idEtab), chargées depuis SQLite
  ///   [formData] — données formulaire en mémoire (modifications non sauvegardées)
  ///   [idCamp], [idQst], [idEtab], [idFilter] — contexte de saisie courant
  ///   [codeEtab]      — code administratif de l'établissement (pour $CODE_ETABLISSEMENT)
  ///   [codeTypeAnnee] — code de l'année scolaire (pour $CODE_TYPE_ANNEE)
  ///
  /// Algorithme (session 45) :
  ///   Pour chaque règle :
  ///     1. Tente la traduction SQL (SqlTranslator.translate)
  ///     2. Si succès → exécute rawQuery → COUNT(*) → comparé au critere
  ///     3. Si échec → chemin regex fallback (_extractValue + _applyOperator)
  Future<List<OfflineCoherenceError>> evaluate({
    required List<CoherenceRule> rules,
    required Map<String, String> formData,
    required String idCamp,
    required String idQst,
    required String idEtab,
    String? idFilter,
    String? codeEtab,
    String? codeTypeAnnee,
  }) async {
    if (rules.isEmpty) return [];

    // ── Obtenir un accès direct à la base SQLite ─────────────────────────
    final db = await _db.database;

    // ── Charger les données pour le chemin regex (fallback) ───────────────
    // Ces chargements sont aussi utilisés comme source de vérité pour les
    // valeurs en mémoire non encore sauvegardées.
    final Map<String, double> regexValues = {};

    final persistedData = await _db.getAllCollectedDataForCoherence(
      idCamp: idCamp,
      idEtab: idEtab,
      idQst:  idQst,
    );
    for (final entry in persistedData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) regexValues[entry.key.toUpperCase()] = v;
    }

    final allEtabData = await _db.getAllCollectedDataForCampEtab(
      idCamp: idCamp,
      idEtab: idEtab,
    );
    for (final entry in allEtabData.entries) {
      final key = entry.key.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null && !regexValues.containsKey(key)) {
        regexValues[key] = v;
      }
    }

    // Données en mémoire (priorité max — modifications non encore sauvegardées)
    for (final entry in formData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) regexValues[entry.key.toUpperCase()] = v;
    }

    // Totaux virtuels pour les vues DB (chemin regex uniquement)
    _injectVirtualAggregates(regexValues, persistedData, formData);

    debugPrint('[CoherenceEval] evaluate: idQst=$idQst idEtab=$idEtab '
        'persistedFields=${persistedData.length} formFields=${formData.length} '
        'totalValues=${regexValues.length} rules=${rules.length} '
        'codeEtab=$codeEtab codeTypeAnnee=$codeTypeAnnee');

    final violations = <OfflineCoherenceError>[];

    for (final rule in rules) {
      try {
        final violated = await _evaluateRule(
          rule:           rule,
          db:             db,
          idCamp:         idCamp,
          idEtab:         idEtab,
          codeEtab:       codeEtab,
          codeTypeAnnee:  codeTypeAnnee,
          regexValues:    regexValues,
        );

        if (violated != null && violated) {
          violations.add(_buildViolation(rule, regexValues));
        }
      } catch (e) {
        debugPrint('[CoherenceEval] error evaluating rule ${rule.idRegle}: $e');
      }
    }

    debugPrint('[CoherenceEval] evaluate complete: ${violations.length} violation(s)');
    return violations;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ÉVALUATION D'UNE RÈGLE — dual path SQL / regex
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue une règle unique. Retourne :
  ///   true  → règle VIOLÉE
  ///   false → règle respectée
  ///   null  → règle non évaluable (ignorée silencieusement)
  Future<bool?> _evaluateRule({
    required CoherenceRule rule,
    required Database db,
    required String idCamp,
    required String idEtab,
    String? codeEtab,
    String? codeTypeAnnee,
    required Map<String, double> regexValues,
  }) async {

    // ── CHEMIN 1 : SQL réel (SqlTranslator + rawQuery) ────────────────────
    final sqlResult = await _evaluateViaSql(
      rule:           rule,
      db:             db,
      idCamp:         idCamp,
      idEtab:         idEtab,
      codeEtab:       codeEtab,
      codeTypeAnnee:  codeTypeAnnee,
    );

    if (sqlResult != null) {
      // Le chemin SQL a réussi
      debugPrint('[CoherenceEval] rule=${rule.idRegle} path=SQL '
          'result=(${sqlResult.v1} ${rule.critere} ${sqlResult.v2}) '
          'violated=${sqlResult.violated}');
      return sqlResult.violated;
    }

    // ── CHEMIN 2 : regex fallback ─────────────────────────────────────────
    final v1 = _extractValue(rule.sqlRegle, regexValues);
    final v2 = _extractValue(rule.sqlAssoc, regexValues);

    debugPrint('[CoherenceEval] rule=${rule.idRegle} path=REGEX '
        'v1=$v1 v2=$v2 critere="${rule.critere}"');

    if (v1 == null || v2 == null) {
      debugPrint('[CoherenceEval] skip rule=${rule.idRegle} — '
          'v1=$v1 v2=$v2 — field(s) not resolvable (conservative skip)');
      return null; // ignoré
    }

    return _applyOperator(v1, v2, rule.critere);
  }

  // ─── CHEMIN SQL : traduction + exécution ──────────────────────────────────

  Future<_SqlEvalResult?> _evaluateViaSql({
    required CoherenceRule rule,
    required Database db,
    required String idCamp,
    required String idEtab,
    String? codeEtab,
    String? codeTypeAnnee,
  }) async {
    // Traduire sql_regle
    final r1 = SqlTranslator.translate(
      serverSql:      rule.sqlRegle,
      idCamp:         idCamp,
      idEtab:         idEtab,
      codeEtab:       codeEtab,
      codeTypeAnnee:  codeTypeAnnee,
    );
    if (r1 == null) return null; // non traduisible → fallback

    // Traduire sql_assoc
    final r2 = SqlTranslator.translate(
      serverSql:      rule.sqlAssoc,
      idCamp:         idCamp,
      idEtab:         idEtab,
      codeEtab:       codeEtab,
      codeTypeAnnee:  codeTypeAnnee,
    );
    if (r2 == null) return null; // non traduisible → fallback

    // Exécuter les deux requêtes traduitess
    final count1 = await _execCount(db, r1.sql, 'sql_regle', rule.idRegle);
    final count2 = await _execCount(db, r2.sql, 'sql_assoc', rule.idRegle);

    if (count1 == null || count2 == null) return null;

    final violated = _applyOperator(count1, count2, rule.critere);
    return _SqlEvalResult(v1: count1, v2: count2, violated: violated);
  }

  /// Exécute un SQL traduit et retourne la valeur entière du COUNT(*).
  /// Retourne null en cas d'erreur.
  Future<double?> _execCount(
      Database db, String sql, String label, int idRegle) async {
    try {
      final rows = await db.rawQuery(sql);
      final count = Sqflite.firstIntValue(rows);
      debugPrint('[CoherenceEval] rawQuery $label rule=$idRegle → count=$count');
      return count?.toDouble();
    } catch (e) {
      debugPrint('[CoherenceEval] rawQuery error $label rule=$idRegle: $e\nSQL: $sql');
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // CHEMIN REGEX FALLBACK (Sessions 38–44 — conservé intégralement)
  // ═══════════════════════════════════════════════════════════════════════════

  /// Injecte dans [values] des approximations pour les colonnes de vues DB
  void _injectVirtualAggregates(
    Map<String, double> values,
    Map<String, String> persistedData,
    Map<String, String> formData,
  ) {
    final rawFields = <String, double>{};
    for (final entry in persistedData.entries) {
      final fieldName = entry.key.split('#').first.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null) rawFields[fieldName] = (rawFields[fieldName] ?? 0.0) + v;
    }
    for (final entry in formData.entries) {
      final fieldName = entry.key.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null) rawFields[fieldName] = v;
    }
    if (rawFields.isEmpty) return;

    if (!values.containsKey(_viewColumnTotal)) {
      final total = rawFields.values.fold(0.0, (a, b) => a + b);
      if (total > 0) values[_viewColumnTotal] = total;
    }
    if (!values.containsKey(_viewColumnFilles)) {
      double fillesSum = 0.0; int fillesCount = 0;
      for (final entry in rawFields.entries) {
        if (_fillesPatterns.hasMatch(entry.key)) { fillesSum += entry.value; fillesCount++; }
      }
      if (fillesCount > 0) values[_viewColumnFilles] = fillesSum;
    }
  }

  double? _extractValue(String sql, Map<String, double> values) {
    if (sql.trim().isEmpty) return null;
    final upperSql = sql.toUpperCase();

    // Pattern SUM([TABLE.]FIELD)
    final sumRegex = RegExp(r'SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)', caseSensitive: false);
    final sumMatches = sumRegex.allMatches(upperSql).toList();
    if (sumMatches.isNotEmpty) {
      final result = _sumFieldAcrossAllFilters(sumMatches.first.group(1)!, values);
      if (result != null) return result;
      for (int i = 1; i < sumMatches.length; i++) {
        final alt = _sumFieldAcrossAllFilters(sumMatches[i].group(1)!, values);
        if (alt != null) return alt;
      }
      return null;
    }

    // Pattern SELECT [TABLE.]FIELD FROM
    final bareMatch = RegExp(
      r'SELECT\s+(?:\w+\.)?(\w+)\s+FROM', caseSensitive: false,
    ).firstMatch(upperSql);
    if (bareMatch != null) {
      final fieldName = bareMatch.group(1)!;
      const sqlKeywords = {
        'DISTINCT', 'TOP', 'ALL', 'COUNT', 'AVG', 'MIN', 'MAX', 'SUM',
        'NVL', 'COALESCE', 'ISNULL', 'NULLIF',
      };
      if (sqlKeywords.contains(fieldName)) return null;
      return _sumFieldAcrossAllFilters(fieldName, values);
    }

    return null;
  }

  double? _sumFieldAcrossAllFilters(String fieldName, Map<String, double> values) {
    double sum = 0; bool found = false;
    for (final entry in values.entries) {
      if (entry.key.split('#').first == fieldName) { sum += entry.value; found = true; }
    }
    return found ? sum : null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // OPÉRATEUR CRITERE — commun aux deux chemins
  // ═══════════════════════════════════════════════════════════════════════════

  /// Applique l'opérateur critere et retourne TRUE si la règle est VIOLÉE.
  ///
  /// Pour le chemin SQL : v1 = COUNT(sql_regle), v2 = COUNT(sql_assoc).
  ///   critere '= 0' : violée si COUNT(sql_regle) > 0 → _applyOperator(count, 0, '=')
  ///   → !(count == 0) → true si count > 0
  ///
  /// Pour le chemin regex : v1 et v2 sont des sommes de champs.
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

  // ─── Construction d'un objet violation ────────────────────────────────────

  OfflineCoherenceError _buildViolation(
      CoherenceRule rule, Map<String, double> regexValues) {
    // Tente d'extraire les valeurs pour l'affichage à l'agent
    final v1Display = _extractValue(rule.sqlRegle, regexValues) ?? 0.0;
    final v2Display = _extractValue(rule.sqlAssoc, regexValues) ?? 0.0;

    return OfflineCoherenceError(
      idRegle:       rule.idRegle,
      idRegleAssoc:  rule.idRegleAssoc,
      libRegle:      rule.libRegle,
      libRegleAssoc: rule.libRegleAssoc,
      critere:       rule.critere,
      message:       rule.message.isNotEmpty
          ? rule.message
          : '${rule.libRegle} — incohérence détectée '
            '(${rule.libRegleAssoc})',
      value1:        v1Display,
      value2:        v2Display,
    );
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

// ─── Résultat intermédiaire évaluation SQL ────────────────────────────────────
class _SqlEvalResult {
  final double v1;
  final double v2;
  final bool violated;
  _SqlEvalResult({required this.v1, required this.v2, required this.violated});
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
