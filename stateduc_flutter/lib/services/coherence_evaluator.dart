// =============================================================================
// coherence_evaluator.dart — Moteur d'évaluation de cohérence HORS LIGNE
// =============================================================================
//
// VERSION : Session 50 — Fix WHERE context-only (TEXT/INTEGER mismatch) + empty-SQL guard
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
//     - HAVING contexte seul → SUPPRIMÉ (step 7b — voir _stripContextOnlyHaving)
//     - parenthèses triples `(((x)))` → `(x)` (normalisées mais conservées)
//     - opérateurs `Or` / `And` (case insensitive) → `OR` / `AND`
//
//  4. RÉSULTAT ATTENDU — DUAL MODE (Session 49)
//     Le traducteur génère deux modes selon la présence de GROUP BY :
//
//     MODE EXISTS (has GROUP BY) — règles de type "existe un établissement violant ?"
//       Wrapper : SELECT COUNT(*) AS cnt FROM (...) _violations
//       → count = 0 : pas de violation
//       → count > 0 : violation détectée
//       Comparé au critere (généralement "= 0").
//
//     MODE SCALAR (pas de GROUP BY, SELECT Sum/COUNT agrégat) — règles de type
//       "Sum(NB_LATRINES_ELEVES) <= Sum(NB_LATRINES_BON_ETAT) ?"
//       Wrapper : SELECT COALESCE((SELECT col FROM (...) _s), 0) AS val
//       → retourne la vraie valeur scalaire
//       → comparée directement à la valeur scalaire de sql_assoc via _applyOperator
//
//     Détection : la SQL contient-elle GROUP BY ? Si non → MODE SCALAR.
//
//  5. MAPPING DE TABLE : ELEVES_AGE_NIVEAU_SEXE / ELEVES_NIVEAU_SEXE / ELEVES_AGE_SEXE
//     Ces tables sont MULTI-LIGNES : collected_data contient plusieurs lignes
//     par field_name (une par combinaison âge × niveau × sexe).
//     → Le CTE utilise SUM(CASE WHEN ...) au lieu de MAX(CASE WHEN ...)
//     → Exemple : FILLES_AGE_NIVEAU présent sur 3 lignes (10, 11, 12)
//                 MAX → 12  (sous-évaluation → faux positifs)
//                 SUM → 33  (correct → correspond au Sum() serveur)
//     Voir _multiRowTables et _buildPivotCte() pour l'implémentation.
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

  // Tables dont les données sont multi-lignes dans collected_data
  // (une ligne par combinaison âge × niveau × sexe).
  // → le CTE doit utiliser SUM() pour agréger toutes les lignes,
  //   sinon MAX() ne retourne que la valeur maximale et non la somme réelle,
  //   ce qui provoque de faux positifs dans les contrôles offline.
  // Exemple : FILLES_AGE_NIVEAU stocké en 3 lignes (10, 11, 12)
  //   MAX → 12  (BUG : sous-évaluation massive)
  //   SUM → 33  (CORRECT : égal à la somme du serveur)
  static const _multiRowTables = {
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
  /// MODE EXISTS (GROUP BY présent) : wrappé dans SELECT COUNT(*) AS cnt FROM (...).
  /// MODE SCALAR (pas de GROUP BY)  : wrappé dans SELECT COALESCE(val, 0) AS val.
  /// Voir [TranslationResult.isScalar] pour distinguer les deux modes.
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
        debugPrint(
            '[SqlTranslator] no known server tables in SQL — not translatable');
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
      // FIX Bug 1: Dart replaceAll(RegExp, String) ne supporte PAS les backreferences
      // \1 dans la chaîne de remplacement → produit un littéral "\1" qui crashe SQLite.
      // Solution : replaceAllMapped avec closure qui retourne m.group(1).
      for (final tableName in usedServerTables) {
        translatedSql = translatedSql.replaceAllMapped(
          RegExp('\\b${RegExp.escape(tableName)}\\.(\\w+)',
              caseSensitive: false),
          (m) => m.group(1)!,
        );
      }

      // ── Étape 7b : FIX SESSION 48 — supprimer le HAVING redondant de contexte ──
      //
      // DOIT être exécuté APRÈS l'étape 7 (suppression des qualificateurs TABLE.)
      // car le HAVING peut contenir DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT avant
      // l'étape 7. Si _stripContextOnlyHaving est appelé avant, il trouve
      // DONNEES_ETABLISSEMENT dans le body du HAVING → isContextOnly = false →
      // le HAVING est gardé → COUNT toujours 0 → aucune violation détectée.
      //
      // Après l'étape 7 : DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT → CODE_ETABLISSEMENT
      // → seuls les champs contexte restent → isContextOnly = true → HAVING supprimé.
      translatedSql = _stripContextOnlyHaving(translatedSql);

      // ── Étape 7c : FIX SESSION 50 — supprimer le WHERE redondant de contexte ──
      //
      // Même problème que le HAVING (Bug S47/S48) mais dans la clause WHERE.
      // Les règles SUM-scalaires (pas de GROUP BY) ont un WHERE du type :
      //   WHERE (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
      // Le CTE retourne des TEXT pour CODE_ETABLISSEMENT ('20952'), mais le
      // littéral du serveur est INTEGER (20952 sans guillemets).
      // SQLite : '20952' = 20952 → FALSE → toutes les lignes filtrées → Sum = NULL
      // → COALESCE(NULL, 0) = 0.0 → les deux côtés valent 0 → jamais violé.
      //
      // Le CTE est déjà filtré sur id_etab='20952' donc ce WHERE est redondant.
      // Supprimer le WHERE s'il ne contient que des champs de contexte.
      translatedSql = _stripContextOnlyWhere(translatedSql);

      // ── Étape 7d : FIX SESSION 50-B — guard SQL vide ──────────────────────
      //
      // Si après les strips le SQL traduit est vide (ex: règle non traduisible
      // avec HAVING+WHERE intégralement supprimés), éviter de générer un SQL
      // syntaxiquement invalide tel que SELECT COUNT(*) AS cnt FROM (\n) _violations.
      if (translatedSql.trim().isEmpty) {
        debugPrint('[SqlTranslator] translatedSql empty after stripping — aborting translation');
        return null;
      }

      // ── Étape 8 : wrapper — dual mode EXISTS / SCALAR ─────────────────
      //
      // SESSION 49 FIX : le wrapper COUNT(*) était toujours utilisé, y compris
      // pour les règles SUM-scalaires sans GROUP BY. Un SELECT Sum(X) retourne
      // toujours exactement 1 ligne (même si NULL) → COUNT(*) = 1 toujours
      // → count1 = 1 et count2 = 1 pour toutes les règles SUM → 1 <= 1 = never
      // violated, quelle que soit la valeur réelle de Sum(X).
      //
      // SOLUTION : détection GROUP BY dans le SQL traduit.
      //   • GROUP BY présent  → MODE EXISTS  : COUNT(*) wrapper (comportement S45-S48)
      //   • Pas de GROUP BY   → MODE SCALAR  : COALESCE(val, 0) wrapper retournant
      //                         la valeur agrégée réelle pour _applyOperator.
      final hasGroupBy = RegExp(
        r'\bGROUP\s+BY\b',
        caseSensitive: false,
      ).hasMatch(translatedSql);

      final withClause = 'WITH ${cteParts.join(',\n')}';
      final String wrappedSql;
      final bool isScalar;

      if (hasGroupBy) {
        // MODE EXISTS — requête retourne des lignes si violation
        wrappedSql =
            '$withClause\nSELECT COUNT(*) AS cnt FROM (\n$translatedSql\n) _violations';
        isScalar = false;
      } else {
        // MODE SCALAR — requête retourne une seule valeur agrégée
        // On extrait la première colonne de la sous-requête via COALESCE pour
        // gérer le cas NULL (aucune donnée → 0).
        wrappedSql =
            '$withClause\nSELECT COALESCE((SELECT * FROM (\n$translatedSql\n) _s), 0) AS val';
        isScalar = true;
      }

      debugPrint('[SqlTranslator] translated SQL (isScalar=$isScalar):\n$wrappedSql');

      return TranslationResult(
        sql: wrappedSql,
        usedTables: usedServerTables,
        fieldNames: allFields,
        isScalar: isScalar,
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
        // FIX Bug 3: exclure les noms de table eux-mêmes du jeu de champs
        if (!_isSqlKeyword(field) && !serverTables.contains(field)) {
          fields.add(field);
        }
      }
    }

    // Pattern : champs après GROUP BY et HAVING (non qualifiés)
    // Ces patterns capturent des noms de champs seuls dans les clauses de tri/filtre
    // ex: GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
    final groupHavingPattern = RegExp(
      r'''(?:GROUP\s+BY|HAVING)\s+([\w\s,=<>'"().]+?)(?:$|ORDER\s+BY|LIMIT)''',
      caseSensitive: false,
    );
    for (final m in groupHavingPattern.allMatches(sql)) {
      final clause = m.group(1) ?? '';
      final identPattern =
          RegExp(r'\b([A-Z][A-Z0-9_]*)\b', caseSensitive: false);
      for (final id in identPattern.allMatches(clause)) {
        final name = id.group(1)!.toUpperCase();
        // FIX Bug 3: exclure aussi les noms de tables serveur
        if (!_isSqlKeyword(name) &&
            name.length > 2 &&
            !serverTables.contains(name)) {
          fields.add(name);
        }
      }
    }

    // FIX Bug 2: extraire les champs non qualifiés de la clause WHERE
    // Certaines règles utilisent des noms de champs sans préfixe TABLE. (ex:
    // DOMAINE_DELIMITE, SUPERFICIE_DOMAINE) dans le WHERE.  Ces champs n'étaient
    // pas extraits, causant des colonnes NULL dans le CTE et des violations
    // jamais détectées.
    //
    // Stratégie : on extrait la portion WHERE...GROUP BY (ou WHERE...fin), puis
    // on capture tous les identifiants qui ressemblent à des noms de champs
    // (MAJUSCULES_AVEC_UNDERSCORES, longueur > 2) en excluant les mots-clés SQL
    // et les noms de tables.  Les littéraux numériques et opérateurs ne matchent
    // pas le pattern \b[A-Z][A-Z0-9_]+\b de longueur > 2.
    final whereMatch = RegExp(
      r'\bWHERE\b(.+?)(?:\bGROUP\s+BY\b|\bHAVING\b|\bORDER\s+BY\b|\bLIMIT\b|$)',
      caseSensitive: false,
      dotAll: true,
    ).firstMatch(sql);
    if (whereMatch != null) {
      final whereClause = whereMatch.group(1) ?? '';
      final identPattern =
          RegExp(r'\b([A-Z][A-Z0-9_]+)\b', caseSensitive: false);
      for (final id in identPattern.allMatches(whereClause)) {
        final name = id.group(1)!.toUpperCase();
        if (!_isSqlKeyword(name) &&
            name.length > 2 &&
            !serverTables.contains(name)) {
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
  // Deux stratégies d'agrégation selon le type de table :
  //
  // • Tables MONO-LIGNE (DONNEES_ETABLISSEMENT) :
  //     MAX(CASE WHEN field_name='X' THEN CAST(field_value AS REAL) END) AS X
  //   → Une seule ligne par champ → MAX() retourne la valeur unique.
  //
  // • Tables MULTI-LIGNES (ELEVES_AGE_NIVEAU_SEXE, ELEVES_NIVEAU_SEXE, ELEVES_AGE_SEXE) :
  //     SUM(CASE WHEN field_name='X' THEN CAST(field_value AS REAL) ELSE 0 END) AS X
  //   → Plusieurs lignes par champ (une par combinaison âge×niveau×sexe)
  //     → SUM() agrège toutes les lignes, ce qui correspond au Sum() serveur.
  //     → Exemple : FILLES_AGE_NIVEAU stocké en 3 lignes (10, 11, 12)
  //                 MAX → 12  (BUG : sous-évaluation)
  //                 SUM → 33  (CORRECT)
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

    // Détecter si la table est de type multi-lignes (ELEVES_*)
    final isMultiRow = _multiRowTables.contains(tableName.toUpperCase());

    final columnDefs = fields.map((field) {
      final isTextContext = _contextFields.contains(field.toUpperCase());
      if (isTextContext) {
        // Champs texte : pas de CAST numérique (comparaison de chaînes)
        return "    MAX(CASE WHEN UPPER(field_name)='$field' "
            "THEN field_value END) AS $field";
      } else if (isMultiRow) {
        // Tables multi-lignes : SUM() pour agréger toutes les lignes
        // (correspond au Sum() serveur sur ELEVES_AGE_NIVEAU_SEXE, etc.)
        return "    SUM(CASE WHEN UPPER(field_name)='$field' "
            "THEN CAST(field_value AS REAL) ELSE 0 END) AS $field";
      } else {
        // Tables mono-ligne : MAX() pour pivoter la valeur unique
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
    result = result.replaceAllMapped(
        RegExp(r'\bSELECT\s+TOP\s+(\d+)\s+', caseSensitive: false),
        (Match m) => 'SELECT ');
    // Le LIMIT sera ajouté après le FROM... mais TOP est rarement dans les règles.

    // 9. Guillemets doubles → guillemets simples pour les littéraux
    //    (Access autorise les guillemets doubles pour les chaînes)
    //    Attention à ne pas toucher aux noms de colonnes entre guillemets.
    //    Heuristique : remplace "valeur" (lettres/chiffres seulement) par 'valeur'
    result = result.replaceAllMapped(
        RegExp(r'"([^"]*)"'), (m) => "'${m.group(1)!.replaceAll("'", "''")}'");

    // 10. (réservé — _stripContextOnlyHaving déplacé en step 7b de translate())
    //     Voir commentaire étape 7b ci-dessus pour l'explication complète.

    return result;
  }

  /// Supprime le HAVING d'une requête traduite si et seulement si ce HAVING
  /// ne contient que des comparaisons sur des champs de contexte
  /// (CODE_ETABLISSEMENT, CODE_TYPE_ANNEE, CODE_ADMINISTRATIF).
  ///
  /// Justification : le CTE de pivot est déjà filtré sur (id_camp, id_etab),
  /// donc le HAVING de filtrage d'établissement est toujours trivial sur mobile
  /// et cause un faux-négatif systématique à cause de l'incompatibilité de type
  /// TEXT vs INTEGER entre field_value (TEXT) et les littéraux numériques du SQL
  /// serveur (ex: CODE_ETABLISSEMENT=20952 au lieu de CODE_ETABLISSEMENT='20952').
  static String _stripContextOnlyHaving(String sql) {
    // Trouve la clause HAVING (après le dernier GROUP BY)
    final havingRe = RegExp(
      r'(\bHAVING\b)(.+?)(?:;|$)',
      caseSensitive: false,
      dotAll: true,
    );

    return sql.replaceAllMapped(havingRe, (m) {
      final havingBody = m.group(2)!;

      // Extrait tous les identifiants (mots de 3+ lettres avec underscores)
      final identRe = RegExp(r'\b([A-Z][A-Z0-9_]+)\b', caseSensitive: false);
      final identifiers = identRe
          .allMatches(havingBody)
          .map((id) => id.group(1)!.toUpperCase())
          .where((id) => !_isSqlKeyword(id) && id.length > 2)
          .toSet();

      // Vérifie si tous les identifiants sont des champs de contexte
      final isContextOnly = identifiers
          .every((id) => _contextFields.contains(id));

      if (isContextOnly) {
        // HAVING ne filtre que sur l'établissement/année → redondant sur mobile
        debugPrint('[SqlTranslator] stripping context-only HAVING: $havingBody');
        return ''; // Supprime le HAVING entier
      } else {
        // HAVING contient une logique métier (SUM, COUNT, etc.) → on le garde
        return m.group(0)!;
      }
    });
  }

  /// Supprime la clause WHERE d'une requête traduite si et seulement si ce WHERE
  /// ne contient que des comparaisons sur des champs de contexte
  /// (CODE_ETABLISSEMENT, CODE_TYPE_ANNEE, CODE_ADMINISTRATIF).
  ///
  /// FIX SESSION 50-A : même problème TEXT/INTEGER que le HAVING (S47/S48) mais
  /// dans la clause WHERE des règles SUM-scalaires (sans GROUP BY).
  /// Ex : WHERE (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
  /// → '20952' (TEXT dans CTE) ≠ 20952 (INTEGER littéral) → SQLite filtre tout
  /// → Sum(X) = NULL → COALESCE → 0.0 → jamais violé.
  ///
  /// Le CTE est déjà filtré sur id_etab='20952' donc ce WHERE est redondant.
  ///
  /// STRATÉGIE :
  ///   1. Si le SQL commence par WITH, on isole le bloc CTE en cherchant la
  ///      première fermeture de parenthèse à depth=0 (fin du CTE). Le strip
  ///      s'applique uniquement sur la partie principale (après le CTE) pour
  ///      éviter de supprimer le WHERE id_camp=... interne au CTE.
  ///   2. Le lookahead de fin de corps WHERE inclut `\n\s*)` (fin de sous-requête
  ///      imbriquée dans le wrapper SCALAR COALESCE) ainsi que GROUP BY / HAVING /
  ///      ORDER BY / LIMIT / fin de chaîne.
  static String _stripContextOnlyWhere(String sql) {
    // ── Étape A : isoler la partie principale (hors bloc CTE) ──────────────
    String ctePart = '';
    String mainPart = sql;

    if (sql.trimLeft().toUpperCase().startsWith('WITH')) {
      // Trouver la première fermeture à depth=0 (= fin du bloc CTE)
      int depth = 0;
      int firstCteEnd = -1;
      for (int i = 0; i < sql.length; i++) {
        if (sql[i] == '(') {
          depth++;
        } else if (sql[i] == ')') {
          depth--;
          if (depth == 0) {
            firstCteEnd = i;
            break;
          }
        }
      }
      if (firstCteEnd >= 0) {
        ctePart  = sql.substring(0, firstCteEnd + 1);
        mainPart = sql.substring(firstCteEnd + 1);
      }
    }

    // ── Étape B : appliquer le strip sur mainPart uniquement ───────────────
    //
    // Le lookahead arrête le body WHERE sur :
    //   \n\s*\)   : fermeture de sous-requête (wrapper SCALAR ou EXISTS)
    //   GROUP BY / HAVING / ORDER BY / LIMIT : clauses SQL suivantes
    //   $          : fin de chaîne
    final whereRe = RegExp(
      r'\bWHERE\b(.+?)(?=\n\s*\)|\bGROUP\s+BY\b|\bHAVING\b|\bORDER\s+BY\b|\bLIMIT\b|$)',
      caseSensitive: false,
      dotAll: true,
    );

    final strippedMain = mainPart.replaceAllMapped(whereRe, (m) {
      final whereBody = m.group(1)!;

      // Extrait tous les identifiants (mots de 3+ lettres avec underscores)
      final identRe = RegExp(r'\b([A-Z][A-Z0-9_]+)\b', caseSensitive: false);
      final identifiers = identRe
          .allMatches(whereBody)
          .map((id) => id.group(1)!.toUpperCase())
          .where((id) => !_isSqlKeyword(id) && id.length > 2)
          .toSet();

      // Vérifie si tous les identifiants sont des champs de contexte
      final isContextOnly = identifiers.every((id) => _contextFields.contains(id));

      if (isContextOnly) {
        // WHERE ne filtre que sur l'établissement/année → redondant sur mobile
        debugPrint('[SqlTranslator] stripping context-only WHERE: $whereBody');
        return ''; // Supprime WHERE + body; le lookahead préserve \n) et GROUP BY
      } else {
        // WHERE contient une logique métier (champs de données) → on le garde
        return m.group(0)!;
      }
    });

    return ctePart + strippedMain;
  }

  // ─── Utilitaire : est-ce un mot-clé SQL ? ────────────────────────────────

  static const _sqlKeywordsSet = {
    'SELECT',
    'FROM',
    'WHERE',
    'AND',
    'OR',
    'NOT',
    'NULL',
    'IS',
    'IN',
    'GROUP',
    'BY',
    'HAVING',
    'ORDER',
    'LIMIT',
    'OFFSET',
    'DISTINCT',
    'COUNT',
    'SUM',
    'MAX',
    'MIN',
    'AVG',
    'CAST',
    'AS',
    'CASE',
    'WHEN',
    'THEN',
    'ELSE',
    'END',
    'JOIN',
    'LEFT',
    'RIGHT',
    'INNER',
    'OUTER',
    'ON',
    'UNION',
    'ALL',
    'INSERT',
    'UPDATE',
    'DELETE',
    'CREATE',
    'DROP',
    'TABLE',
    'INDEX',
    'PRIMARY',
    'KEY',
    'REFERENCES',
    'COALESCE',
    'NVL',
    'WITH',
    'REAL',
    'INTEGER',
    'TEXT',
    'BLOB',
    'NUMERIC',
  };

  static bool _isSqlKeyword(String word) =>
      _sqlKeywordsSet.contains(word.toUpperCase());
}

/// Résultat d'une traduction SQL.
class TranslationResult {
  /// Le SQL SQLite traduit.
  /// • [isScalar]=false : encapsulé dans SELECT COUNT(*) AS cnt FROM (...).
  /// • [isScalar]=true  : encapsulé dans SELECT COALESCE(val, 0) AS val.
  final String sql;

  /// Tables serveur utilisées dans la requête originale.
  final Set<String> usedTables;

  /// Noms de champs extraits pour le CTE de pivot.
  final Set<String> fieldNames;

  /// True si la requête est en mode SCALAR (pas de GROUP BY) :
  /// le SQL retourne une seule valeur agrégée (Sum, Count scalaire).
  /// False si en mode EXISTS (GROUP BY présent) : COUNT(*) des violations.
  ///
  /// SESSION 49 : nécessaire pour distinguer les deux modes d'évaluation.
  final bool isScalar;

  const TranslationResult({
    required this.sql,
    required this.usedTables,
    required this.fieldNames,
    required this.isScalar,
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
  static const _viewColumnTotal = 'TOTAL_AGE_NIVEAU';
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
      idQst: idQst,
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
          rule: rule,
          db: db,
          idCamp: idCamp,
          idEtab: idEtab,
          codeEtab: codeEtab,
          codeTypeAnnee: codeTypeAnnee,
          regexValues: regexValues,
        );

        if (violated != null && violated) {
          violations.add(_buildViolation(rule, regexValues));
        }
      } catch (e) {
        debugPrint('[CoherenceEval] error evaluating rule ${rule.idRegle}: $e');
      }
    }

    debugPrint(
        '[CoherenceEval] evaluate complete: ${violations.length} violation(s)');
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
      rule: rule,
      db: db,
      idCamp: idCamp,
      idEtab: idEtab,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
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
      serverSql: rule.sqlRegle,
      idCamp: idCamp,
      idEtab: idEtab,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );
    if (r1 == null) return null; // non traduisible → fallback

    // Traduire sql_assoc
    // FIX Bug 4: si sql_assoc n'est pas traduisible (ex: pas de table serveur
    // connue, règles 487/488 dont sql_assoc est une requête simple sans
    // DONNEES_ETABLISSEMENT), on ne retombe PAS immédiatement en fallback.
    //
    // Stratégie :
    //   • Si sql_assoc est vide → COUNT assoc = 0 (règle de type "le résultat
    //     doit être 0 violations" — critere typique = '= 0').
    //   • Si sql_assoc n'est pas traduisible mais sql_regle l'est, on compare
    //     count1 (violations détectées) directement à 0 avec le critere.
    //     Cela couvre le cas fréquent critere='= 0' : la règle est violée si
    //     count1 > 0.
    final r2 = SqlTranslator.translate(
      serverSql: rule.sqlAssoc,
      idCamp: idCamp,
      idEtab: idEtab,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );

    // Exécuter sql_regle (toujours disponible à ce stade)
    // SESSION 49 : passer isScalar pour que _execCount lise la bonne colonne
    final count1 = await _execCount(
      db, r1.sql, 'sql_regle', rule.idRegle,
      isScalar: r1.isScalar,
    );
    if (count1 == null) return null;

    double count2;
    if (r2 != null) {
      // Les deux côtés sont traduisibles → évaluation complète
      final c2 = await _execCount(
        db, r2.sql, 'sql_assoc', rule.idRegle,
        isScalar: r2.isScalar,
      );
      if (c2 == null) return null;
      count2 = c2;
    } else if (rule.sqlAssoc.trim().isEmpty) {
      // Pas d'association → on compare count1 à 0
      debugPrint(
          '[CoherenceEval] rule=${rule.idRegle} sql_assoc empty → count2=0');
      count2 = 0;
    } else {
      // sql_assoc non traduisible (pas de table serveur connue) :
      // on traite sql_regle comme un COUNT de violations et on compare à 0.
      // Cela est correct pour les règles dont le critere est '= 0' :
      //   count1=0 → pas de violation, count1>0 → violation.
      debugPrint(
          '[CoherenceEval] rule=${rule.idRegle} sql_assoc not translatable '
          '— comparing count1 to 0 directly');
      count2 = 0;
    }

    final violated = _applyOperator(count1, count2, rule.critere);
    return _SqlEvalResult(v1: count1, v2: count2, violated: violated);
  }

  /// Exécute un SQL traduit et retourne la valeur numérique du résultat.
  ///
  /// • [isScalar]=false (mode EXISTS) : lit la colonne `cnt` (COUNT(*)).
  /// • [isScalar]=true  (mode SCALAR) : lit la colonne `val` (valeur agrégée réelle).
  ///
  /// SESSION 49 : ajout du paramètre [isScalar] pour distinguer les deux modes.
  /// Le mode SCALAR est nécessaire pour les règles Sum(X) sans GROUP BY, où
  /// COUNT(*) retournait toujours 1 (1 ligne agrégée même si NULL) → jamais violé.
  ///
  /// FIX Session 48 : suppression du bloc diagnostic CTE. Le regex (.+?) non-greedy
  /// s'arrêtait au premier ')' rencontré dans le CTE (ex: END) dans MAX(CASE...END))
  /// → SQL tronqué → erreur SQLiteLog (1) near "SELECT": syntax error.
  Future<double?> _execCount(
      Database db, String sql, String label, int idRegle,
      {bool isScalar = false}) async {
    try {
      final rows = await db.rawQuery(sql);
      if (rows.isEmpty) {
        debugPrint(
            '[CoherenceEval] rawQuery $label rule=$idRegle → empty result');
        return isScalar ? 0.0 : 0.0;
      }
      if (isScalar) {
        // MODE SCALAR : première colonne de la première ligne = valeur agrégée
        final firstRow = rows.first;
        final rawVal = firstRow.values.first;
        final val = rawVal == null
            ? 0.0
            : (rawVal is num
                ? rawVal.toDouble()
                : double.tryParse(rawVal.toString()) ?? 0.0);
        debugPrint(
            '[CoherenceEval] rawQuery $label rule=$idRegle (SCALAR) → val=$val');
        return val;
      } else {
        // MODE EXISTS : colonne cnt = COUNT(*)
        final count = Sqflite.firstIntValue(rows);
        debugPrint(
            '[CoherenceEval] rawQuery $label rule=$idRegle (EXISTS) → count=$count');
        return count?.toDouble();
      }
    } catch (e) {
      debugPrint(
          '[CoherenceEval] rawQuery error $label rule=$idRegle: $e\nSQL: $sql');
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
      double fillesSum = 0.0;
      int fillesCount = 0;
      for (final entry in rawFields.entries) {
        if (_fillesPatterns.hasMatch(entry.key)) {
          fillesSum += entry.value;
          fillesCount++;
        }
      }
      if (fillesCount > 0) values[_viewColumnFilles] = fillesSum;
    }
  }

  double? _extractValue(String sql, Map<String, double> values) {
    if (sql.trim().isEmpty) return null;
    final upperSql = sql.toUpperCase();

    // Pattern SUM([TABLE.]FIELD)
    final sumRegex =
        RegExp(r'SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)', caseSensitive: false);
    final sumMatches = sumRegex.allMatches(upperSql).toList();
    if (sumMatches.isNotEmpty) {
      final result =
          _sumFieldAcrossAllFilters(sumMatches.first.group(1)!, values);
      if (result != null) return result;
      for (int i = 1; i < sumMatches.length; i++) {
        final alt = _sumFieldAcrossAllFilters(sumMatches[i].group(1)!, values);
        if (alt != null) return alt;
      }
      return null;
    }

    // Pattern SELECT [TABLE.]FIELD FROM
    final bareMatch = RegExp(
      r'SELECT\s+(?:\w+\.)?(\w+)\s+FROM',
      caseSensitive: false,
    ).firstMatch(upperSql);
    if (bareMatch != null) {
      final fieldName = bareMatch.group(1)!;
      const sqlKeywords = {
        'DISTINCT',
        'TOP',
        'ALL',
        'COUNT',
        'AVG',
        'MIN',
        'MAX',
        'SUM',
        'NVL',
        'COALESCE',
        'ISNULL',
        'NULLIF',
      };
      if (sqlKeywords.contains(fieldName)) return null;
      return _sumFieldAcrossAllFilters(fieldName, values);
    }

    return null;
  }

  double? _sumFieldAcrossAllFilters(
      String fieldName, Map<String, double> values) {
    double sum = 0;
    bool found = false;
    for (final entry in values.entries) {
      if (entry.key.split('#').first == fieldName) {
        sum += entry.value;
        found = true;
      }
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
      case '<=':
        return !(v1 <= v2);
      case '>=':
        return !(v1 >= v2);
      case '<':
        return !(v1 < v2);
      case '>':
        return !(v1 > v2);
      case '=':
        return !(v1 == v2);
      case '!=':
      case '<>':
        return !(v1 != v2);
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
      idRegle: rule.idRegle,
      idRegleAssoc: rule.idRegleAssoc,
      libRegle: rule.libRegle,
      libRegleAssoc: rule.libRegleAssoc,
      critere: rule.critere,
      message: rule.message.isNotEmpty
          ? rule.message
          : '${rule.libRegle} — incohérence détectée '
              '(${rule.libRegleAssoc})',
      value1: v1Display,
      value2: v2Display,
    );
  }

  /// Returns a human-readable French label for a critere operator.
  String _critereLabelFr(String critere) {
    switch (critere.trim()) {
      case '<=':
        return 'inférieur ou égal à';
      case '>=':
        return 'supérieur ou égal à';
      case '<':
        return 'strictement inférieur à';
      case '>':
        return 'strictement supérieur à';
      case '=':
        return 'égal à';
      case '!=':
      case '<>':
        return 'différent de';
      default:
        return critere;
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
  final int idRegle;
  final int idRegleAssoc;
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
