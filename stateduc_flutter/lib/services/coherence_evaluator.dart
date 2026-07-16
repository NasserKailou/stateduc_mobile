// =============================================================================
// coherence_evaluator.dart — Moteur d'évaluation de cohérence HORS LIGNE
// =============================================================================
//
// VERSION : Session 60 — fix updated_at NOT NULL, NOUVEAUX_INSCRITS, SAVEPOINT ';' prefix
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
//     ├─► [S59] SAVEPOINT coherence_eval + injection formData → collected_data
//     │       → Garantit que CTE voit les valeurs en mémoire non encore sauvegardées
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
//  4. RÉSULTAT ATTENDU — DUAL MODE (Sessions 49 + 52)
//     Le traducteur génère deux modes selon la structure GROUP BY :
//
//     MODE EXISTS (GROUP BY avec colonne non-contexte) — règles "existe un étab violant ?"
//       Wrapper : SELECT COUNT(*) AS cnt FROM (...) _violations
//       → count = 0 : pas de violation
//       → count > 0 : violation détectée
//       Comparé au critere (généralement "= 0").
//
//     MODE SCALAR (pas de GROUP BY OU GROUP BY contexte-only + SELECT agrégat pur)
//       "Sum(NB_LATRINES_ELEVES) <= Sum(NB_LATRINES_BON_ETAT) ?"
//       Wrapper : SELECT COALESCE((SELECT col FROM (...) _s), 0) AS val
//       → retourne la vraie valeur scalaire
//       → comparée directement à la valeur scalaire de sql_assoc via _applyOperator
//
//     Détection (dans l'ordre) :
//       1. Pas de GROUP BY → MODE SCALAR (S49)
//       2. GROUP BY contexte-only + SELECT = Sum/Count/Avg purs → SCALAR (S52)
//          GROUP BY supprimé (redondant mobile), valeur lue directement
//       3. Sinon → MODE EXISTS
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
//  6. FIX SESSION 58 — LIKE suffixe exact '_0' pour DONNEES_ETABLISSEMENT
//
//  7. FIX SESSION 59 — Injection formData dans collected_data via SAVEPOINT
//     PROBLÈME : le chemin SQL/CTE lit uniquement collected_data (SQLite).
//     Si l'utilisateur saisit une valeur mais ne sauvegarde pas encore (formData
//     en mémoire), le CTE retourne 0 pour ce champ → faux positif asymétrique :
//       ex. NB_LATRINES_BON_ETAT_F non encore sauvegardé → CTE lit 0
//           NB_LATRINES_ELEVES sauvegardé → CTE lit 4
//           critere '<=' → !(0 <= 4) → VIOLATED (faux positif)
//     SOLUTION : avant la boucle des règles, on commence un SAVEPOINT SQLite,
//
//  8. FIX SESSION 60 — Trois correctifs critiques bloquants :
//     A. collected_data.updated_at TEXT NOT NULL sans DEFAULT → INSERT S59
//        manquait is_sent et updated_at → NOT NULL constraint failed →
//        0 champs injectés → SAVEPOINT entièrement neutralisé.
//        Fix : ajouter is_sent=0, updated_at=DateTime.now().toIso8601String().
//     B. NOUVEAUX_INSCRITS absent de _knownServerTables / _multiRowTables →
//        règle 496 (FILLES_NV_INCRITES <= TOTAL_NV_INCRITS) toujours SKIPPED.
//        Champs dans collected_data : FILLES_NV_INCRITES_0_8_4 (suffixe multi-lignes).
//        Fix : ajouter 'NOUVEAUX_INSCRITS' aux deux sets (SUM + LIKE 'FIELD_%').
//     C. SAVEPOINT ROLLBACK sans préfixe ';' → warning silencieux Android API ≤ 27
//        pouvant laisser le SAVEPOINT ouvert → données temporaires non supprimées.
//        Fix : préfixer ';' à 'ROLLBACK TO SAVEPOINT' et 'RELEASE SAVEPOINT'.
//     on insère chaque entrée de formData dans collected_data (INSERT OR REPLACE),
//     puis on exécute toutes les règles. Le ROLLBACK TO SAVEPOINT en fin de bloc
//     supprime toutes ces insertions temporaires sans affecter les données réelles.
//     S57 utilisait LIKE 'FIELD%' pour DONNEES_ETABLISSEMENT, ce qui causait
//     une ambiguïté : LIKE 'NB_ELEVES%' matchait AUSSI 'NB_ELEVES_F_0' alors
//     qu'on voulait uniquement 'NB_ELEVES_0'. MAX() retournait alors la valeur
//     d'un champ incorrect selon l'ordre des lignes dans collected_data.
//     Fix : utiliser LIKE 'FIELD_0' (suffixe exact) pour DONNEES_ETABLISSEMENT.
//     Le suffixe est toujours exactement '_0' pour ces champs (HTML input name).
//     Les tables ELEVES_* gardent LIKE 'FIELD_%' (suffixe _0_70, _0_71, etc.).
//
//  7. TABLES INCONNUES
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

import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:path_provider/path_provider.dart';
import 'package:sqflite/sqflite.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

// =============================================================================
// CoherenceLogger — Journalisation exhaustive sur fichier
// =============================================================================
//
// Écrit un fichier de log complet lors de chaque appel à evaluate().
// Chemin : {AppDocumentsDir}/coherence_latest.log (écrasé à chaque run)
//
// Utilisation :
//   final logger = CoherenceLogger(idEtab: idEtab, idCamp: idCamp);
//   logger.log('message');
//   await logger.flush();  // Écrit tout sur disque en fin d'evaluate()
//
// Le fichier est lisible sur l'appareil via un explorateur de fichiers ou
// ADB pull. Il contient tous les debugPrint du moteur de cohérence.

class CoherenceLogger {
  CoherenceLogger({required this.idEtab, required this.idCamp});

  final String idEtab;
  final String idCamp;
  final _buf = StringBuffer();

  static const _fileName = 'coherence_latest.log';

  /// Ajoute une ligne de log (timestamp + message).
  ///
  /// S60 FIX E — Android logcat tronque debugPrint à ~4000 caractères.
  /// Les SQL traduits (CTE + wrapper) peuvent dépasser cette limite.
  /// On découpe les messages longs en tranches de 800 caractères pour
  /// que chaque tranche soit affichée intégralement dans logcat.
  /// Le fichier .log reçoit le message complet sans troncature.
  void log(String message) {
    final now = DateTime.now().toIso8601String();
    _buf.writeln('$now  $message');
    // Miroir vers debugPrint — découpage en tranches de 800 car. pour Android logcat
    const _kChunkSize = 800;
    if (message.length <= _kChunkSize) {
      debugPrint(message);
    } else {
      // Découpe le message en lignes naturelles d'abord, puis en tranches
      final lines = message.split('\n');
      final buf = StringBuffer();
      for (final line in lines) {
        if (buf.length + line.length + 1 > _kChunkSize) {
          if (buf.isNotEmpty) { debugPrint(buf.toString()); buf.clear(); }
        }
        buf.writeln(line);
        if (buf.length > _kChunkSize) {
          // Ligne individuelle trop longue → trancher brutalement
          final s = buf.toString();
          for (int i = 0; i < s.length; i += _kChunkSize) {
            debugPrint(s.substring(i, (i + _kChunkSize).clamp(0, s.length)));
          }
          buf.clear();
        }
      }
      if (buf.isNotEmpty) debugPrint(buf.toString().trimRight());
    }
  }

  /// Écrit le buffer sur disque dans {AppDocumentsDir}/coherence_latest.log.
  /// Appelé à la fin de evaluate() quel que soit le résultat.
  Future<void> flush() async {
    try {
      final dir = await getApplicationDocumentsDirectory();
      final file = File('${dir.path}/$_fileName');
      await file.writeAsString(_buf.toString(), flush: true);
      // On ne passe PAS par log() pour éviter la boucle infinie
      debugPrint('[CoherenceLogger] log écrit → ${file.path}');
    } catch (e) {
      debugPrint('[CoherenceLogger] ⚠️ erreur écriture log: $e');
    }
  }
}

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

  // SESSION 58 — Logger de fichier partagé avec CoherenceEvaluator.
  // Initialisé avant chaque appel à translate() depuis _evaluateViaSql().
  // Toutes les sorties debugPrint du traducteur sont aussi écrites dans le log.
  static CoherenceLogger? _logger;

  /// Enregistre un message dans le logger de fichier ET dans debugPrint.
  static void _log(String msg) {
    debugPrint(msg);
    _logger?.log(msg);
  }

  // Tables serveur connues qui peuvent être mappées vers collected_data
  static const _knownServerTables = {
    'DONNEES_ETABLISSEMENT',
    'ELEVES_AGE_NIVEAU_SEXE',
    'ELEVES_NIVEAU_SEXE',
    'ELEVES_AGE_SEXE',
    // S60 FIX B — règle 496 (FILLES_NV_INCRITES <= TOTAL_NV_INCRITS) était SKIPPED
    // car NOUVEAUX_INSCRITS n'était pas reconnu → traducteur retournait null → regex
    // fallback avec v1=null v2=null. Champs : FILLES_NV_INCRITES_0_8_4 (multi-lignes).
    'NOUVEAUX_INSCRITS',
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
    // S60 FIX B — NOUVEAUX_INSCRITS : données multi-lignes (une par catégorie).
    // Suffixe HTML : _0_8_4 → SUM + LIKE 'FIELD_%' (même stratégie que ELEVES_*).
    'NOUVEAUX_INSCRITS',
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
  ///   [idQst]         → id de la question (filtre optionnel CTE par formulaire)
  ///   [codeEtab]      → valeur pour \$CODE_ETABLISSEMENT  (ex. '101012071')
  ///   [codeTypeAnnee] → valeur pour \$CODE_TYPE_ANNEE (ex. '2024')
  ///
  /// MODE EXISTS (GROUP BY non-contexte) : wrappé dans SELECT COUNT(*) AS cnt FROM (...).
  /// MODE SCALAR (pas de GROUP BY, ou GROUP BY contexte-only + SELECT agrégat) :
  ///   wrappé dans SELECT COALESCE(val, 0) AS val.
  /// Voir [TranslationResult.isScalar] pour distinguer les deux modes.
  static TranslationResult? translate({
    required String serverSql,
    required String idCamp,
    required String idEtab,
    String? idQst,
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
        SqlTranslator._log(
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
          idQst: idQst,
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
        SqlTranslator._log('[SqlTranslator] translatedSql empty after stripping — aborting translation');
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
      // SESSION 52 FIX : un deuxième pattern cause des faux positifs :
      //   SELECT Sum(X) FROM T GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
      //   → hasGroupBy = true → MODE EXISTS → COUNT(*) = 1 toujours
      //   → (1 < 1) → !(1 < 1) = true → FAUX POSITIF
      //
      //   Ce pattern correspond à une requête Sum-scalaire avec GROUP BY de
      //   contexte uniquement (le serveur regroupe par établissement pour avoir
      //   la somme par établissement — contexte unique côté mobile).
      //   → doit être traité comme SCALAR (lire la valeur réelle Sum(X)).
      //
      // RÈGLE DE DÉTECTION MODE (dans l'ordre de priorité) :
      //
      //   1. Pas de GROUP BY → MODE SCALAR (règles Sum sans groupement)
      //
      //   2. GROUP BY contexte-only ET SELECT = agrégats purs (Sum/Count/Avg/Max/Min)
      //      → MODE SCALAR : on supprime le GROUP BY (redondant sur mobile où le CTE
      //        est déjà filtré par établissement) et on lit la valeur agrégée.
      //      Exemple : SELECT Sum(FILLES_AGE_NIVEAU) FROM ELEVES GROUP BY CODE_ETABLISSEMENT
      //        → supprime GROUP BY → SELECT Sum(FILLES_AGE_NIVEAU) FROM ELEVES
      //        → COALESCE wrapper → val = somme réelle
      //
      //   3. GROUP BY avec colonne non-contexte → MODE EXISTS (règles de violation
      //      WHERE qui filtrent les établissements qui violent la condition)
      //      Exemple : SELECT CODE_ETAB FROM DONNEES WHERE ELEC=0 GROUP BY CODE_ETAB
      //        → COUNT(*) > 0 → violation détectée

      final hasGroupBy = RegExp(
        r'\bGROUP\s+BY\b',
        caseSensitive: false,
      ).hasMatch(translatedSql);

      // Détection du pattern Sum-scalaire avec GROUP BY de contexte (Fix S52)
      final isSumScalarGroupBy = hasGroupBy &&
          _isSumScalarWithContextGroupBy(translatedSql);

      // Si GROUP BY contexte-only avec Sum/Count dans SELECT :
      // supprimer le GROUP BY pour obtenir une requête scalaire propre.
      if (isSumScalarGroupBy) {
        translatedSql = _stripContextOnlyGroupBy(translatedSql);
        SqlTranslator._log('[SqlTranslator] Sum-scalar with context-only GROUP BY detected '
            '→ GROUP BY stripped → SCALAR mode');
      }

      final withClause = 'WITH ${cteParts.join(',\n')}';
      final String wrappedSql;
      final bool isScalar;

      // Recalcule hasGroupBy après le strip éventuel du GROUP BY
      final hasGroupByFinal = !isSumScalarGroupBy &&
          RegExp(r'\bGROUP\s+BY\b', caseSensitive: false)
              .hasMatch(translatedSql);

      if (hasGroupByFinal) {
        // MODE EXISTS — requête retourne des lignes si violation
        // COUNT(*) = 0 si aucune violation, > 0 si violation détectée.
        wrappedSql =
            '$withClause\nSELECT COUNT(*) AS cnt FROM (\n$translatedSql\n) _violations';
        isScalar = false;
      } else {
        // MODE SCALAR — requête retourne une seule valeur agrégée (Sum, Count, etc.)
        //
        // SESSION 54 FIX : certaines règles ont un SELECT multi-colonnes agrégées :
        //   SELECT Sum(FILLES_AGE_NIVEAU) AS SommeX, Sum(TOTAL_AGE_NIVEAU) AS SommeY
        // Le wrapper  SELECT (__scalar_val)  exige une sous-requête mono-colonne.
        // SQLite error : "sub-select returns 2 columns - expected 1".
        //
        // Le serveur compare val_sql[0][0] vs val_sql_assoc[0][0] — toujours la
        // PREMIÈRE valeur de la PREMIÈRE ligne. On réduit donc le SELECT à sa
        // première colonne avant de construire le wrapper.
        //
        // Stratégie : réécrire SELECT col1, col2, ... FROM T → SELECT col1 FROM T
        // en utilisant _keepFirstSelectColumn().
        final scalarSql = _keepFirstSelectColumn(translatedSql);
        SqlTranslator._log('[SqlTranslator] SCALAR: kept first column only:\n$scalarSql');
        wrappedSql =
            '$withClause\nSELECT COALESCE((__scalar_val), 0) AS val\nFROM (\n  SELECT (\n$scalarSql\n  ) AS __scalar_val\n) _wrapper';
        isScalar = true;
      }

      SqlTranslator._log('[SqlTranslator] translated SQL (isScalar=$isScalar):\n$wrappedSql');

      return TranslationResult(
        sql: wrappedSql,
        usedTables: usedServerTables,
        fieldNames: allFields,
        isScalar: isScalar,
      );
    } catch (e, st) {
      SqlTranslator._log('[SqlTranslator] translation error: $e\n$st');
      return null;
    }
  }

  /// Réduit le SELECT d'une requête à sa première colonne uniquement.
  ///
  /// Utilisé en MODE SCALAR pour les règles dont le SELECT contient plusieurs
  /// colonnes agrégées (ex: `Sum(X) AS a, Sum(Y) AS b`). Le wrapper SCALAR
  /// `SELECT (sub-query)` exige que la sous-requête retourne exactement 1 colonne.
  /// Le serveur compare toujours `val_sql[0][0]` — la première valeur.
  ///
  /// Exemple :
  ///   SELECT Sum(FILLES_AGE_NIVEAU) AS SommeX, Sum(TOTAL_AGE_NIVEAU) AS SommeY
  ///   FROM ELEVES_AGE_NIVEAU_SEXE
  /// →
  ///   SELECT Sum(FILLES_AGE_NIVEAU) AS SommeX
  ///   FROM ELEVES_AGE_NIVEAU_SEXE
  ///
  /// Si le SELECT ne contient qu'une colonne (ou si le pattern ne matche pas),
  /// retourne le SQL inchangé.
  static String _keepFirstSelectColumn(String sql) {
    // Trouve SELECT ... FROM et extrait la liste des colonnes
    final selRe = RegExp(
      r'(\bSELECT\b)(.*?)(\bFROM\b)',
      caseSensitive: false,
      dotAll: true,
    );
    final m = selRe.firstMatch(sql);
    if (m == null) return sql;

    final selectKw  = m.group(1)!;   // "SELECT"
    final colsPart  = m.group(2)!;   // " Sum(X) AS a, Sum(Y) AS b "
    final fromKw    = m.group(3)!;   // "FROM"

    // Découpe en colonnes de niveau 0 (respecte les parenthèses imbriquées)
    final cols = <String>[];
    final buf  = StringBuffer();
    int depth  = 0;
    for (int i = 0; i < colsPart.length; i++) {
      final c = colsPart[i];
      if (c == '(') {
        depth++;
        buf.write(c);
      } else if (c == ')') {
        depth--;
        buf.write(c);
      } else if (c == ',' && depth == 0) {
        cols.add(buf.toString());
        buf.clear();
      } else {
        buf.write(c);
      }
    }
    if (buf.isNotEmpty) cols.add(buf.toString());

    if (cols.length <= 1) return sql; // Déjà mono-colonne

    // Ne garder que la première colonne
    final firstCol = cols.first;
    final before   = sql.substring(0, m.start);
    final after    = sql.substring(m.end);
    SqlTranslator._log('[SqlTranslator] _keepFirstSelectColumn: '
        '${cols.length} cols → kept: "${firstCol.trim()}"');
    return '$before$selectKw${firstCol.trimRight()} $fromKw$after';
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
    SqlTranslator._log('[SqlTranslator] after param substitution: '
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

    // FIX Bug S53-B + S55-B: extraire les champs non qualifiés du SELECT lui-même,
    // en excluant les alias (tokens après AS).
    //
    // Quand le SQL n'utilise PAS de qualificateur TABLE.FIELD (ex:
    //   SELECT Sum(FILLES_AGE_NIVEAU) FROM ELEVES_AGE_NIVEAU_SEXE
    // ), le champ FILLES_AGE_NIVEAU n'était pas extrait → colonne manquante
    // dans le CTE → SQLite : "no such column: FILLES_AGE_NIVEAU".
    //
    // FIX S55-B : le scanner S53-B extrayait aussi les ALIAS définis après AS
    // (ex: AS SommeDeFILLES_AGE_NIVEAU → SOMMEDEFILLES_AGE_NIVEAU ajouté au CTE).
    // Ces alias n'existent pas dans collected_data → colonne CTE inutile (toujours 0)
    // et logs pollués. Solution : supprimer les alias AS xxx avant de scanner.
    final selectMatch = RegExp(
      r'\bSELECT\b(.+?)\bFROM\b',
      caseSensitive: false,
      dotAll: true,
    ).firstMatch(sql);
    if (selectMatch != null) {
      // Supprimer les alias AS xxx avant extraction pour éviter d'ajouter
      // SommeDeXXX comme champ du CTE (ne correspond à rien dans collected_data)
      final selectClause = (selectMatch.group(1) ?? '')
          .replaceAll(RegExp(r'\bAS\b\s+\w+', caseSensitive: false), '');
      final identPattern =
          RegExp(r'\b([A-Z][A-Z0-9_]+)\b', caseSensitive: false);
      for (final id in identPattern.allMatches(selectClause)) {
        final name = id.group(1)!.toUpperCase();
        if (!_isSqlKeyword(name) &&
            name.length > 2 &&
            !serverTables.contains(name)) {
          fields.add(name);
        }
      }
    }

    SqlTranslator._log('[SqlTranslator] fields extracted for CTE: $fields');
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
    // idQst conservé pour la signature mais non utilisé dans le CTE (FIX S55).
    String? idQst,
  }) {
    final escapedCamp = idCamp.replaceAll("'", "''");
    final escapedEtab = idEtab.replaceAll("'", "''");

    // FIX SESSION 57 — Tous les champs HTML sont stockés AVEC un suffixe
    // numérique (_0, _0_70, _0_71, …) quel que soit le type de table.
    //
    // Exemples observés dans collected_data (logs device) :
    //   DONNEES_ETABLISSEMENT : NB_ELEVES_F_0, ELECTRICITE_0,
    //                           NB_LATRINES_BON_ETAT_0, …
    //   ELEVES_AGE_NIVEAU_SEXE: FILLES_AGE_NIVEAU_0_70,
    //                           TOTAL_AGE_NIVEAU_0_71, …
    //
    // FIX SESSION 58 — Ambiguïté LIKE pour DONNEES_ETABLISSEMENT :
    //   S57 utilisait LIKE 'FIELD%' (préfixe libre) pour DONNEES_ETABLISSEMENT.
    //   Problème : LIKE 'NB_ELEVES%' matche AUSSI 'NB_ELEVES_F_0' en plus de
    //   'NB_ELEVES_0', car NB_ELEVES est un préfixe de NB_ELEVES_F.
    //   MAX() retourne alors la valeur du champ le plus grand parmi les deux,
    //   ce qui donne un résultat incorrect.
    //
    //   Solution : utiliser LIKE 'FIELD_0' (suffixe exact '_0') pour
    //   DONNEES_ETABLISSEMENT — le suffixe HTML est toujours exactement '_0'
    //   pour ces champs (une seule section de formulaire par champ).
    //   → LIKE 'NB_ELEVES_0'   → matche uniquement NB_ELEVES_0       ✅
    //   → LIKE 'NB_ELEVES_F_0' → matche uniquement NB_ELEVES_F_0     ✅
    //
    //   Tables ELEVES_* : garde LIKE 'FIELD_%' (suffixe _0_70, _0_71, etc.)
    //   Le '_' dans LIKE matche un caractère quelconque — FIELD_ exige au moins
    //   un caractère après le préfixe, ce qui est toujours vrai pour _0_NN.
    //
    // Règle finale :
    //   • Champs CONTEXTE : injectés SANS suffixe → correspondance EXACTE =
    //   • DONNEES_ETABLISSEMENT (mono-ligne) : MAX + LIKE 'FIELD_0' (exact)
    //   • Tables ELEVES_* (multi-lignes)     : SUM + LIKE 'FIELD_%' (préfixe)
    final isMultiRow = _multiRowTables.contains(tableName.toUpperCase());

    final columnDefs = fields.map((field) {
      final upperField = field.toUpperCase();
      final isTextContext = _contextFields.contains(upperField);
      if (isTextContext) {
        // Champs de contexte : injectés sans suffixe → = exact.
        return "    MAX(CASE WHEN UPPER(field_name)='$upperField' "
            "THEN field_value END) AS $upperField";
      } else if (isMultiRow) {
        // Tables multi-lignes (ELEVES_*) : SUM + LIKE préfixe '_' (1+ car après).
        // 'FIELD_%' garantit un caractère après le nom (= _0_70, _0_71, etc.)
        // et évite de matcher un champ 'FIELDXXX' sans undersccore.
        return "    SUM(CASE WHEN UPPER(field_name) LIKE '${upperField}_%' "
            "THEN CAST(field_value AS REAL) ELSE 0 END) AS $upperField";
      } else {
        // Tables mono-ligne (DONNEES_ETABLISSEMENT) : MAX + LIKE suffixe exact '_0'.
        // FIX S58 : '_0' évite l'ambiguïté NB_ELEVES_0 vs NB_ELEVES_F_0.
        return "    MAX(CASE WHEN UPPER(field_name) LIKE '${upperField}_0' "
            "THEN CAST(field_value AS REAL) END) AS $upperField";
      }
    }).join(',\n');

    // FIX SESSION 55 — NE PAS filtrer par id_qst dans le CTE de cohérence.
    //
    // PROBLÈME INTRODUIT EN S53 :
    //   Le filtre AND id_qst='...' dans le CTE filtrait sur l'id_qst du formulaire
    //   COURANT (ex: '9502'). Mais les données des tables ELEVES sont sauvegardées
    //   sous l'id_qst DU FORMULAIRE ÉLÈVES (ex: '9501'), pas celui du formulaire
    //   de cohérence. Résultat : le CTE retournait 0 lignes → val=0.0 → aucune
    //   violation jamais détectée → les contrôles offline ne fonctionnaient pas.
    //
    // MODÈLE SERVEUR (controle_theme_batch.class.php) :
    //   Les requêtes SQL de cohérence s'exécutent sur des VUES SQL Server qui
    //   contiennent TOUTES les données de l'établissement pour la campagne,
    //   sans filtrage par thème/questionnaire. Le mobile reproduit ce comportement.
    //
    // SOLUTION : filtrer sur (id_camp, id_etab) uniquement — comme le serveur.
    //   Le CTE agrège toutes les données de l'établissement pour la campagne,
    //   indépendamment du formulaire d'origine. C'est nécessaire pour les règles
    //   inter-thèmes (ex: Total filles ≤ Total élèves, où filles vient d'un thème
    //   et total d'un autre).
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
        SqlTranslator._log('[SqlTranslator] stripping context-only HAVING: $havingBody');
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
        SqlTranslator._log('[SqlTranslator] stripping context-only WHERE: $whereBody');
        return ''; // Supprime WHERE + body; le lookahead préserve \n) et GROUP BY
      } else {
        // WHERE contient une logique métier (champs de données) → on le garde
        return m.group(0)!;
      }
    });

    return ctePart + strippedMain;
  }

  /// Détecte les requêtes Sum-scalaires avec GROUP BY de contexte uniquement.
  ///
  /// Pattern cible (règles de type comparaison de sommes, ex: règle 495) :
  ///   SELECT Sum(FILLES_AGE_NIVEAU) AS xxx
  ///   FROM ELEVES_AGE_NIVEAU_SEXE
  ///   GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
  ///
  /// Deux critères combinés :
  ///   1. La liste SELECT ne contient que des fonctions d'agrégation
  ///      (Sum, Count, Avg, Max, Min) — pas de colonnes simples.
  ///   2. Le GROUP BY ne contient que des champs de contexte
  ///      (CODE_ETABLISSEMENT, CODE_TYPE_ANNEE, CODE_ADMINISTRATIF).
  ///
  /// Ces requêtes retournent une seule valeur scalaire par établissement.
  /// Elles doivent être traitées en MODE SCALAR, pas EXISTS.
  /// En mode EXISTS COUNT(*) = 1 toujours → (1 < 1) → violated=true → FAUX POSITIF.
  ///
  /// Fix Session 52 — règles 483–495 et similaires.
  static bool _isSumScalarWithContextGroupBy(String sql) {
    // 1. Extraire la liste SELECT (entre SELECT et FROM)
    final selMatch = RegExp(
      r'\bSELECT\b(.+?)\bFROM\b',
      caseSensitive: false,
      dotAll: true,
    ).firstMatch(sql);
    if (selMatch == null) return false;

    // Supprimer les alias AS xxx puis tester chaque colonne
    final selectPart = selMatch.group(1)!
        .replaceAll(RegExp(r'\bAS\b\s+\w+', caseSensitive: false), '');
    final colParts = selectPart.split(',');
    for (final col in colParts) {
      final trimmed = col.trim();
      if (trimmed.isEmpty) continue;
      // Chaque colonne doit être une fonction d'agrégation
      if (!RegExp(r'^(Sum|Count|Avg|Max|Min)\s*\(',
              caseSensitive: false)
          .hasMatch(trimmed)) {
        return false;
      }
    }

    // 2. Extraire GROUP BY et vérifier que seuls des champs de contexte y figurent
    final grpMatch = RegExp(
      r'\bGROUP\s+BY\b(.+?)(?:\bHAVING\b|\bORDER\s+BY\b|\bLIMIT\b|$)',
      caseSensitive: false,
      dotAll: true,
    ).firstMatch(sql);
    if (grpMatch == null) return false; // Pas de GROUP BY

    final groupCols = grpMatch.group(1)!.split(',');
    for (final col in groupCols) {
      final trimmed = col.trim().toUpperCase();
      if (!_contextFields.contains(trimmed)) return false;
    }

    return true;
  }

  /// Supprime le GROUP BY d'une requête Sum-scalaire dont le GROUP BY ne contient
  /// que des champs de contexte (résultat de [_isSumScalarWithContextGroupBy]).
  ///
  /// Supprime également le HAVING résiduel s'il est présent (déjà supprimé par
  /// [_stripContextOnlyHaving] dans les étapes précédentes, mais par sécurité).
  ///
  /// Fix Session 52 — appliqué dans l'étape 8 de [translate()] AVANT le wrapper.
  static String _stripContextOnlyGroupBy(String sql) {
    // Supprime GROUP BY ... jusqu'à HAVING / ORDER BY / LIMIT / fin
    // HAVING doit avoir été supprimé par _stripContextOnlyHaving (étape 7b)
    // mais on nettoie aussi HAVING résiduel pour robustesse.
    String result = sql.replaceAll(
      RegExp(
        r'\bGROUP\s+BY\b.+?(?=\bHAVING\b|\bORDER\s+BY\b|\bLIMIT\b|$)',
        caseSensitive: false,
        dotAll: true,
      ),
      '',
    );
    // Supprimer HAVING résiduel context-only s'il reste
    result = _stripContextOnlyHaving(result);
    return result.trimRight();
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
  ///   [codeEtab]      — code administratif de l'établissement (pour \$CODE_ETABLISSEMENT)
  ///   [codeTypeAnnee] — code de l'année scolaire (pour \$CODE_TYPE_ANNEE)
  ///
  /// SESSION 58 : à chaque appel, un fichier coherence_latest.log est écrit dans
  /// le répertoire documents de l'application (chemin retourné dans les logs).
  ///
  /// SESSION 59 : avant la boucle des règles, un SAVEPOINT SQLite est ouvert et
  /// toutes les entrées de formData sont injectées dans collected_data (INSERT OR
  /// REPLACE). À la fin du bloc (finally), ROLLBACK TO SAVEPOINT supprime ces
  /// insertions temporaires sans affecter les données réelles. Cela garantit que
  /// le chemin SQL/CTE voit toujours les valeurs en cours de saisie (non encore
  /// sauvegardées), éliminant les faux positifs dus à l'asymétrie persisted/mémoire.
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

    // SESSION 58 — Créer le logger de fichier pour ce run
    final logger = CoherenceLogger(idEtab: idEtab, idCamp: idCamp);
    logger.log('========================================================');
    logger.log('[CoherenceEval] === RUN START ===');
    logger.log('[CoherenceEval] idCamp=$idCamp idQst=$idQst idEtab=$idEtab');
    logger.log('[CoherenceEval] codeEtab=${codeEtab ?? "(null)"} '
        'codeTypeAnnee=${codeTypeAnnee ?? "(null)"}');
    logger.log('[CoherenceEval] rules count: ${rules.length}');
    logger.log('========================================================');

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

    // LOG DIAGNOSTIC S56/S58 — Noms de champs réels dans collected_data
    // (utile pour vérifier que FILLES_AGE_NIVEAU_0_70 etc. sont bien présents)
    if (persistedData.isNotEmpty || allEtabData.isNotEmpty) {
      final allKeys = {
        ...persistedData.keys,
        ...allEtabData.keys,
      }.where((k) => !k.startsWith('CODE_')).toList()..sort();
      logger.log('[CoherenceEval] collected_data ALL fields '
          '(idEtab=$idEtab, total=${allKeys.length}):');
      // Log par blocs de 30 pour lisibilité
      for (int i = 0; i < allKeys.length; i += 30) {
        final chunk = allKeys.skip(i).take(30).join(', ');
        logger.log('  [$i..${i + 30}] $chunk');
      }
    } else {
      logger.log('[CoherenceEval] ⚠️ collected_data VIDE pour '
          'idCamp=$idCamp idEtab=$idEtab — aucune donnée sauvegardée localement.');
    }

    logger.log('[CoherenceEval] evaluate: idQst=$idQst idEtab=$idEtab '
        'persistedFields=${persistedData.length} formFields=${formData.length} '
        'totalValues=${regexValues.length} rules=${rules.length} '
        'codeEtab=$codeEtab codeTypeAnnee=$codeTypeAnnee');

    final violations = <OfflineCoherenceError>[];

    // ═══════════════════════════════════════════════════════════════════════
    // SESSION 59 — Injection formData dans collected_data via SAVEPOINT SQLite
    // ═══════════════════════════════════════════════════════════════════════
    //
    // PROBLÈME RACINE DES FAUX POSITIFS :
    //   Le CTE généré par SqlTranslator lit UNIQUEMENT collected_data (SQLite).
    //   Quand un utilisateur saisit une valeur sans encore sauvegarder,
    //   cette valeur est dans formData (Map<String, String>) en mémoire mais
    //   ABSENTE de collected_data. Le CTE retourne alors COALESCE(NULL, 0) = 0.0
    //   pour ce champ, tandis que l'autre côté de la comparaison peut avoir une
    //   valeur > 0 déjà sauvegardée → violation asymétrique = faux positif.
    //
    //   Exemple observé (règle 484) :
    //     NB_LATRINES_BON_ETAT_F en cours de saisie (=987, formData, non sauvegardé)
    //     NB_LATRINES_ELEVES déjà sauvegardé (=4, collected_data)
    //     critere '<=' : !(0.0 <= 4.0) → VIOLATED (faux positif)
    //
    // SOLUTION — SAVEPOINT pattern :
    //   1. Ouvrir un SAVEPOINT nommé 'coherence_eval'
    //   2. Insérer toutes les entrées de formData dans collected_data avec le
    //      suffixe HTML correct (_0 pour DONNEES_ETABLISSEMENT) en utilisant
    //      INSERT OR REPLACE pour écraser les valeurs déjà présentes.
    //   3. Exécuter toutes les règles de cohérence (CTE voit maintenant formData)
    //   4. ROLLBACK TO SAVEPOINT → toutes les insertions temporaires sont supprimées
    //   5. RELEASE SAVEPOINT → nettoie le SAVEPOINT
    //
    // IMPORTANT :
    //   • Le SAVEPOINT est dans un try/finally pour garantir le ROLLBACK même
    //     en cas d'exception.
    //   • Seules les valeurs numériques de formData sont injectées (CAST safe).
    //   • On NE conserve PAS les champs de contexte (CODE_ETABLISSEMENT, etc.)
    //     qui sont déjà corrects dans collected_data et gérés séparément.
    //   • Le suffixe _0 est appliqué si absent (champs DONNEES_ETABLISSEMENT).
    //     Les champs ELEVES_* ont déjà leur suffixe complet (_0_70, _0_71).

    int injectedCount = 0;
    try {
      // ── Étape 1 : SAVEPOINT ──────────────────────────────────────────────
      await db.execute('SAVEPOINT coherence_eval');
      logger.log('[CoherenceEval] S59: SAVEPOINT coherence_eval ouvert');

      // ── Étape 2 : injection formData ─────────────────────────────────────
      for (final entry in formData.entries) {
        final rawKey = entry.key;
        final rawValue = entry.value;

        // Ne pas injecter les champs vides ou non numériques
        // (les règles ne comparent que des valeurs numériques via Sum/Max)
        if (rawValue.trim().isEmpty) continue;
        // Vérification souple : on injecte même les valeurs non numériques
        // (ex: champs texte), le CAST AS REAL dans le CTE les ignore proprement.

        // Construire le field_name correct pour collected_data :
        //   • Si le nom possède déjà un suffixe numérique (_0, _0_70, etc.) → garder
        //   • Sinon → ajouter _0 (champs DONNEES_ETABLISSEMENT standard)
        final fieldName = _formKeyToFieldName(rawKey);

        try {
          // S60 FIX A — INSERT manquait is_sent et updated_at.
          // collected_data.updated_at est TEXT NOT NULL sans DEFAULT →
          // DatabaseException(NOT NULL constraint failed: collected_data.updated_at)
          // → toutes les injections échouaient → 0 champs injectés → S59 neutralisé.
          await db.execute(
            'INSERT OR REPLACE INTO collected_data '
            '(id_camp, id_etab, id_qst, id_filter, field_name, field_value, is_sent, updated_at) '
            'VALUES (?, ?, ?, ?, ?, ?, 0, ?)',
            [idCamp, idEtab, idQst ?? '', '', fieldName, rawValue,
             DateTime.now().toIso8601String()],
          );
          injectedCount++;
        } catch (e) {
          logger.log('[CoherenceEval] S60: ⚠️ injection échouée pour '
              'field=$fieldName val=$rawValue: $e');
        }
      }
      logger.log('[CoherenceEval] S59: $injectedCount champs injectés depuis formData');

      // ── Étape 3 : boucle des règles (avec CTE qui voit formData) ─────────
      for (final rule in rules) {
        logger.log('--------------------------------------------------------');
        logger.log('[CoherenceEval] rule=${rule.idRegle} lib="${rule.libRegle}" '
            'critere="${rule.critere}"');
        try {
          final violated = await _evaluateRule(
            rule: rule,
            db: db,
            idCamp: idCamp,
            idEtab: idEtab,
            idQst: idQst,
            codeEtab: codeEtab,
            codeTypeAnnee: codeTypeAnnee,
            regexValues: regexValues,
            logger: logger,
          );

          if (violated != null && violated) {
            violations.add(_buildViolation(rule, regexValues));
            logger.log('[CoherenceEval] rule=${rule.idRegle} *** VIOLATED ***');
          } else if (violated == false) {
            logger.log('[CoherenceEval] rule=${rule.idRegle} OK (not violated)');
          } else {
            logger.log('[CoherenceEval] rule=${rule.idRegle} SKIPPED (not evaluable)');
          }
        } catch (e) {
          logger.log('[CoherenceEval] error evaluating rule ${rule.idRegle}: $e');
        }
      }
    } finally {
      // ── Étape 4 + 5 : ROLLBACK + RELEASE — toujours exécuté ─────────────
      //
      // S60 FIX C — Android API ≤ 27 : sqflite.execSQL() peut ignorer silencieusement
      // ROLLBACK TO SAVEPOINT sans le préfixe ';'. Résultat possible : SAVEPOINT non
      // annulé → insertions temporaires non supprimées → pollution de collected_data.
      // Le préfixe ';' est géré correctement par toutes les versions de SQLite Android.
      try {
        await db.execute(';ROLLBACK TO SAVEPOINT coherence_eval');
        await db.execute(';RELEASE SAVEPOINT coherence_eval');
        logger.log('[CoherenceEval] S60: SAVEPOINT coherence_eval rollback+release '
            '($injectedCount insertions temporaires supprimées)');
      } catch (e) {
        logger.log('[CoherenceEval] S60: ⚠️ erreur ROLLBACK SAVEPOINT: $e');
      }
    }

    logger.log('========================================================');
    logger.log('[CoherenceEval] evaluate complete: '
        '${violations.length} violation(s)');
    logger.log('=== RUN END ===');
    // Écriture sur fichier disque — non-bloquant pour l'UI
    await logger.flush();

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
    String? idQst,
    String? codeEtab,
    String? codeTypeAnnee,
    required Map<String, double> regexValues,
    required CoherenceLogger logger,
  }) async {
    // ── CHEMIN 1 : SQL réel (SqlTranslator + rawQuery) ────────────────────
    final sqlResult = await _evaluateViaSql(
      rule: rule,
      db: db,
      idCamp: idCamp,
      idEtab: idEtab,
      idQst: idQst,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
      logger: logger,
    );

    if (sqlResult != null) {
      // Le chemin SQL a réussi
      logger.log('[CoherenceEval] rule=${rule.idRegle} path=SQL '
          'result=(${sqlResult.v1} ${rule.critere} ${sqlResult.v2}) '
          'violated=${sqlResult.violated}');
      return sqlResult.violated;
    }

    // ── CHEMIN 2 : regex fallback ─────────────────────────────────────────
    final v1 = _extractValue(rule.sqlRegle, regexValues);
    final v2 = _extractValue(rule.sqlAssoc, regexValues);

    logger.log('[CoherenceEval] rule=${rule.idRegle} path=REGEX '
        'v1=$v1 v2=$v2 critere="${rule.critere}"');

    if (v1 == null || v2 == null) {
      logger.log('[CoherenceEval] skip rule=${rule.idRegle} — '
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
    String? idQst,
    String? codeEtab,
    String? codeTypeAnnee,
    required CoherenceLogger logger,
  }) async {
    logger.log('[CoherenceEval] _evaluateViaSql rule=${rule.idRegle} '
        'idQst=$idQst idEtab=$idEtab codeEtab=$codeEtab '
        'critere="${rule.critere}"');
    logger.log('[CoherenceEval] sql_regle raw: ${rule.sqlRegle}');
    logger.log('[CoherenceEval] sql_assoc raw: ${rule.sqlAssoc}');

    // SESSION 58 — Connecter le logger au SqlTranslator pour que toutes ses
    // sorties soient aussi écrites dans le fichier log de cohérence.
    SqlTranslator._logger = logger;

    // Traduire sql_regle
    final r1 = SqlTranslator.translate(
      serverSql: rule.sqlRegle,
      idCamp: idCamp,
      idEtab: idEtab,
      idQst: idQst,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );
    if (r1 == null) {
      logger.log('[CoherenceEval] rule=${rule.idRegle} sql_regle not translatable → regex fallback');
      return null; // non traduisible → fallback
    }
    logger.log('[CoherenceEval] rule=${rule.idRegle} sql_regle (isScalar=${r1.isScalar}):\n${r1.sql}');

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
      idQst: idQst,
      codeEtab: codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );
    if (r2 != null) {
      logger.log('[CoherenceEval] rule=${rule.idRegle} sql_assoc (isScalar=${r2.isScalar}):\n${r2.sql}');
    }

    // Exécuter sql_regle (toujours disponible à ce stade)
    // SESSION 49 : passer isScalar pour que _execCount lise la bonne colonne
    final count1 = await _execCount(
      db, r1.sql, 'sql_regle', rule.idRegle,
      isScalar: r1.isScalar,
      logger: logger,
    );
    if (count1 == null) return null;

    double count2;
    if (r2 != null) {
      // Les deux côtés sont traduisibles → évaluation complète
      final c2 = await _execCount(
        db, r2.sql, 'sql_assoc', rule.idRegle,
        isScalar: r2.isScalar,
        logger: logger,
      );
      if (c2 == null) return null;
      count2 = c2;
    } else if (rule.sqlAssoc.trim().isEmpty) {
      // Pas d'association → on compare count1 à 0
      logger.log(
          '[CoherenceEval] rule=${rule.idRegle} sql_assoc empty → count2=0');
      count2 = 0;
    } else {
      // S60 FIX D — sql_assoc peut être un littéral numérique pur (ex: '0', '1').
      // Dans ce cas, double.tryParse() retourne la valeur directement.
      // Règles 486–489 : sql_assoc='0', critere='=' → la règle est violée si
      // count1 (COUNT des établissements violant la condition WHERE) != 0.
      // Cette branche couvre aussi le cas générique sql_assoc non traduisible :
      // on compare count1 à la valeur littérale si elle est numérique,
      // sinon on compare à 0 (comportement conservatif identique au cas vide).
      final literalValue = double.tryParse(rule.sqlAssoc.trim());
      if (literalValue != null) {
        logger.log(
            '[CoherenceEval] rule=${rule.idRegle} sql_assoc is numeric literal '
            '${rule.sqlAssoc.trim()} → count2=$literalValue');
        count2 = literalValue;
      } else {
        // sql_assoc non traduisible et non numérique → compare count1 à 0
        logger.log(
            '[CoherenceEval] rule=${rule.idRegle} sql_assoc not translatable '
            '— comparing count1 to 0 directly');
        count2 = 0;
      }
    }

    final violated = _applyOperator(count1, count2, rule.critere);
    logger.log('[CoherenceEval] rule=${rule.idRegle} '
        'v1=$count1 ${rule.critere} v2=$count2 → violated=$violated');
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
      {bool isScalar = false,
      CoherenceLogger? logger}) async {
    try {
      final rows = await db.rawQuery(sql);
      if (rows.isEmpty) {
        final msg = '[CoherenceEval] rawQuery $label rule=$idRegle → empty result';
        debugPrint(msg); logger?.log(msg);
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
        final msg = '[CoherenceEval] rawQuery $label rule=$idRegle (SCALAR) → val=$val';
        debugPrint(msg); logger?.log(msg);
        return val;
      } else {
        // MODE EXISTS : colonne cnt = COUNT(*)
        final count = Sqflite.firstIntValue(rows);
        final msg = '[CoherenceEval] rawQuery $label rule=$idRegle (EXISTS) → count=$count';
        debugPrint(msg); logger?.log(msg);
        return count?.toDouble();
      }
    } catch (e) {
      final msg = '[CoherenceEval] rawQuery error $label rule=$idRegle: $e\nSQL:\n$sql';
      debugPrint(msg); logger?.log(msg);
      return null;
    }
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // SESSION 59 — Helpers pour l'injection formData
  // ═══════════════════════════════════════════════════════════════════════════

  /// Convertit une clé formData (nom de champ HTML) en field_name pour
  /// collected_data, en garantissant la présence d'un suffixe numérique.
  ///
  /// Règle :
  ///   • Si le nom se termine déjà par `_\d+` (suffixe numérique existant,
  ///     ex: `NB_LATRINES_BON_ETAT_F_0`, `FILLES_AGE_NIVEAU_0_70`) → garder tel quel.
  ///   • Sinon → ajouter `_0` (champs DONNEES_ETABLISSEMENT standard).
  ///
  /// Exemples :
  ///   'NB_LATRINES_BON_ETAT_F'    → 'NB_LATRINES_BON_ETAT_F_0'
  ///   'NB_LATRINES_BON_ETAT_F_0'  → 'NB_LATRINES_BON_ETAT_F_0'  (inchangé)
  ///   'FILLES_AGE_NIVEAU_0_70'    → 'FILLES_AGE_NIVEAU_0_70'     (inchangé)
  ///   'ELECTRICITE'               → 'ELECTRICITE_0'
  static String _formKeyToFieldName(String formKey) {
    // Détecte si le nom se termine par _suivi_de_chiffres (ex: _0, _70, _0_70)
    final hasNumericSuffix = RegExp(r'_\d+$').hasMatch(formKey);
    return hasNumericSuffix ? formKey : '${formKey}_0';
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
      debugPrint('[CoherenceEval] unknown critère "$critere" — skipping');
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
