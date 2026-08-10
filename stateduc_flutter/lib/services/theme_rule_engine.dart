// =============================================================================
// theme_rule_engine.dart — Moteur générique de cohérence piloté par métadonnées
// =============================================================================
//
// VERSION : Session Moteur Générique v1 — DICO_REGLE_THEME (240 règles, 16 thèmes)
//
// ARCHITECTURE :
//   ThemeRuleEngine.evaluateTheme()
//     │
//     ├─► [SAVEPOINT] injection formData → collected_data (même pattern que CoherenceEvaluator)
//     │
//     ├─► Lecture dico_regle_theme par id_theme (triées par ordre_regle)
//     │       → table SQLite locale seedée depuis les JSON assets ou l'API
//     │
//     ├─► Pour chaque règle (sql_regle autonome) :
//     │     1. SqlTranslator.translate() → SQL SQLite avec CTE pivot
//     │     2. db.rawQuery() → résultat
//     │     3. MODE EXISTS : résultat non vide → violation levée avec le message associé
//     │     4. MODE ASSOC  : comparaison val1 [critere] val2 → violation si condition vraie
//     │     5. Erreur SQL → log + ignorée (gestion défensive)
//     │
//     ├─► [SAVEPOINT ROLLBACK] — données temporaires supprimées
//     │
//     └─► Retourne List<ThemeCoherenceError>
//
// PRINCIPE DE DÉTECTION :
//   Une règle retourne une violation si et seulement si :
//   - MODE EXISTS (pas d'association) : sa requête SQL retourne au moins une ligne.
//   - MODE ASSOC  (avec association)  : val1 [critere] val2 est vrai.
//   C'est fondamentalement différent du modèle paire (sql_regle + sql_assoc + critere
//   scalaire) de CoherenceEvaluator existant.
//
// ZÉRO RÉGRESSION :
//   Ce moteur est ADDITIONNEL à CoherenceEvaluator (moteur paire existant).
//   Les deux moteurs coexistent. DataEntryProvider peut appeler les deux.
//   Les règles du modèle paire restent gérées par CoherenceEvaluator.evaluate().
//
// EXTENSIBILITÉ :
//   Pour ajouter une nouvelle règle : INSERT dans dico_regle_theme (via sync API).
//   Aucun changement de code requis.
//
// FORMAT JSON ASSET (seed initial) :
//   assets/dico_regle_theme.json       → [{id_regle, id_theme, sql_regle, ordre_regle, message}, ...]
//   assets/dico_regle_theme_assoc.json → [{id_assoc, id_regle_theme, id_regle_theme_assoc, critere, activer_ctrl}, ...]
//
// =============================================================================

import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/services.dart' show rootBundle;
import 'package:sqflite/sqflite.dart';
import '../services/coherence_evaluator.dart';
import '../services/database_service.dart';

// =============================================================================
// ThemeCoherenceError — Violation détectée par ThemeRuleEngine
// =============================================================================

/// Violation de cohérence levée par le moteur générique ThemeRuleEngine.
///
/// La violation est levée si la requête SQL retourne un résultat non vide
/// (mode EXISTS) ou si la comparaison scalaire échoue (mode ASSOC).
class ThemeCoherenceError {
  /// Identifiant de la règle violée (DICO_REGLE_THEME.ID_REGLE_THEME).
  final int idRegle;

  /// Identifiant du thème (ex. 900, 950, 1030, …).
  final int idTheme;

  /// Message d'incohérence (DICO_TRADUCTION.LIBELLE).
  final String message;

  /// Ordre d'exécution de la règle (utile pour le tri dans l'UI).
  final int ordreRegle;

  /// Valeur scalaire retournée par la règle principale (mode ASSOC uniquement).
  final double? value1;

  /// Valeur scalaire retournée par la règle associée (mode ASSOC uniquement).
  final double? value2;

  /// Critère de comparaison (mode ASSOC uniquement : '>', '=', '>=', '<=', '<').
  final String? critere;

  const ThemeCoherenceError({
    required this.idRegle,
    required this.idTheme,
    required this.message,
    required this.ordreRegle,
    this.value1,
    this.value2,
    this.critere,
  });

  @override
  String toString() =>
      'ThemeCoherenceError(idRegle=$idRegle, idTheme=$idTheme, '
      'message="$message", value1=$value1, value2=$value2, critere=$critere)';
}

// =============================================================================
// ThemeRuleEngine — Moteur d'évaluation des règles génériques par thème
// =============================================================================

/// Moteur d'évaluation offline des règles de cohérence génériques.
///
/// Lit les règles depuis la table SQLite [dico_regle_theme], les traduit
/// via [SqlTranslator] (SQL Server/Access → SQLite) et les exécute sur
/// [collected_data].
///
/// Usage :
/// ```dart
/// final engine = ThemeRuleEngine(db: _db);
/// final errors = await engine.evaluateTheme(
///   idTheme:       900,
///   idCamp:        _idCamp!,
///   idEtab:        _idEtab!,
///   idQst:         _selectedQuestion!.idQst,
///   codeEtab:      _codeEtab,
///   codeTypeAnnee: _codeyear,
///   formData:      _formData,
/// );
/// ```
class ThemeRuleEngine {
  ThemeRuleEngine({required DatabaseService db}) : _db = db;

  final DatabaseService _db;

  // ═══════════════════════════════════════════════════════════════════════════
  // API PUBLIQUE
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue toutes les règles d'un thème donné pour le contexte de saisie.
  ///
  /// [idTheme]       — identifiant du thème (900, 950, etc.)
  /// [idCamp]        — identifiant de la campagne
  /// [idEtab]        — identifiant de l'établissement (SQLite)
  /// [idQst]         — identifiant du questionnaire/thème courant
  /// [idFilter]      — période de filtre (nullable)
  /// [codeEtab]      — code administratif de l'établissement (→ $CODE_ETABLISSEMENT)
  /// [codeTypeAnnee] — code de l'année scolaire (→ $CODE_TYPE_ANNEE)
  /// [formData]      — données formulaire en mémoire (non encore sauvegardées)
  ///
  /// Retourne la liste des violations (liste vide = aucune violation ou règles inconnues).
  Future<List<ThemeCoherenceError>> evaluateTheme({
    required int idTheme,
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? idFilter,
    String? codeEtab,
    String? codeTypeAnnee,
    Map<String, String> formData = const {},
  }) async {
    // Récupère les règles pour ce thème (triées par ordre_regle)
    final rules = await _db.getDicoReglesByTheme(idTheme);
    if (rules.isEmpty) {
      debugPrint('[ThemeRuleEngine] aucune règle active pour idTheme=$idTheme');
      return [];
    }

    final logger = CoherenceLogger(idEtab: idEtab, idCamp: idCamp);
    final db    = await _db.database;

    logger.log('[ThemeRuleEngine] === DÉBUT ÉVALUATION idTheme=$idTheme '
        '${rules.length} règles ===');
    logger.log('[ThemeRuleEngine] idCamp=$idCamp idEtab=$idEtab idQst=$idQst '
        'codeEtab=${codeEtab ?? "(null)"} codeTypeAnnee=${codeTypeAnnee ?? "(null)"} '
        'formData=${formData.length} champs');

    // ── SAVEPOINT : injection formData dans collected_data ──────────────────
    // Même pattern que CoherenceEvaluator (Session 59).
    // Garantit que les valeurs en cours de saisie (non encore sauvegardées)
    // sont visibles par les CTE de pivot lors de l'exécution des règles.
    const savepointName = 'theme_rule_eval';
    await db.execute('SAVEPOINT $savepointName');

    final violations = <ThemeCoherenceError>[];

    try {
      // Injection des valeurs du formulaire en cours (temporaire)
      int injectedCount = 0;
      for (final entry in formData.entries) {
        if (entry.value.isEmpty) continue;
        try {
          await db.rawInsert(
            'INSERT OR REPLACE INTO collected_data '
            '(id_camp, id_etab, id_qst, id_filter, field_name, field_value, '
            'is_sent, updated_at) '
            'VALUES (?, ?, ?, ?, ?, ?, 0, ?)',
            [
              idCamp,
              idEtab,
              idQst,
              idFilter,
              entry.key,
              entry.value,
              DateTime.now().toIso8601String(),
            ],
          );
          injectedCount++;
        } catch (e) {
          logger.log('[ThemeRuleEngine] ⚠️ injection ${entry.key}: $e');
        }
      }
      logger.log('[ThemeRuleEngine] SAVEPOINT "$savepointName" ouvert — '
          '$injectedCount/${formData.length} champs injectés');

      // ── Évaluation de chaque règle dans l'ordre ────────────────────────────
      for (final rule in rules) {
        final idRegle    = (rule['id_regle']    as int?)    ?? 0;
        final ordreRegle = (rule['ordre_regle'] as int?)    ?? idRegle;
        final sqlRegle   = (rule['sql_regle']   as String?) ?? '';
        final message    = (rule['message']     as String?) ?? '';

        if (sqlRegle.trim().isEmpty) {
          logger.log('[ThemeRuleEngine] règle $idRegle SQL vide — ignorée');
          continue;
        }

        logger.log('\n[ThemeRuleEngine] --- Règle $idRegle (ordre=$ordreRegle) ---');
        logger.log('[ThemeRuleEngine] SQL (début): '
            '${sqlRegle.substring(0, sqlRegle.length.clamp(0, 200))}');

        // Vérifie si cette règle a des associations dans dico_regle_theme_assoc
        final assocs = await _db.getDicoRegleAssoc(idRegle);

        ThemeCoherenceError? violation;

        if (assocs.isNotEmpty) {
          // ── MODE ASSOCIATION : comparaison scalaire ──────────────────────
          violation = await _evaluateWithAssoc(
            db:            db,
            logger:        logger,
            idRegle:       idRegle,
            idTheme:       idTheme,
            ordreRegle:    ordreRegle,
            sqlRegle:      sqlRegle,
            message:       message,
            assocs:        assocs,
            idCamp:        idCamp,
            idEtab:        idEtab,
            idQst:         idQst,
            codeEtab:      codeEtab,
            codeTypeAnnee: codeTypeAnnee,
          );
        } else {
          // ── MODE EXISTS : résultat non vide = violation ──────────────────
          violation = await _evaluateExists(
            db:            db,
            logger:        logger,
            idRegle:       idRegle,
            idTheme:       idTheme,
            ordreRegle:    ordreRegle,
            sqlRegle:      sqlRegle,
            message:       message,
            idCamp:        idCamp,
            idEtab:        idEtab,
            idQst:         idQst,
            codeEtab:      codeEtab,
            codeTypeAnnee: codeTypeAnnee,
          );
        }

        if (violation != null) violations.add(violation);
      }

      logger.log('\n[ThemeRuleEngine] === FIN ÉVALUATION idTheme=$idTheme '
          '${violations.length} violation(s) ===');

    } finally {
      // ── ROLLBACK SAVEPOINT : suppression des données temporaires ──────────
      // Doit être exécuté même en cas d'exception pour ne pas laisser des
      // données temporaires dans collected_data.
      try {
        await db.execute(';ROLLBACK TO SAVEPOINT $savepointName');
        await db.execute(';RELEASE SAVEPOINT $savepointName');
      } catch (e) {
        debugPrint('[ThemeRuleEngine] ⚠️ erreur ROLLBACK SAVEPOINT: $e');
      }
      await logger.flush();
    }

    return violations;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ÉVALUATION MODE EXISTS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue une règle en mode EXISTS.
  /// Violation si la requête retourne au moins une ligne (count > 0).
  Future<ThemeCoherenceError?> _evaluateExists({
    required Database db,
    required CoherenceLogger logger,
    required int idRegle,
    required int idTheme,
    required int ordreRegle,
    required String sqlRegle,
    required String message,
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? codeEtab,
    String? codeTypeAnnee,
  }) async {
    // Traduction SQL Server/Access → SQLite
    TranslationResult? translated;
    try {
      translated = SqlTranslator.translate(
        serverSql:      sqlRegle,
        idCamp:         idCamp,
        idEtab:         idEtab,
        idQst:          idQst,
        codeEtab:       codeEtab,
        codeTypeAnnee:  codeTypeAnnee,
        logger:         logger,
      );
    } catch (e) {
      logger.log('[ThemeRuleEngine] ⚠️ règle $idRegle traduction: $e — ignorée');
      return null;
    }

    if (translated == null) {
      logger.log('[ThemeRuleEngine] règle $idRegle non traduisible — ignorée');
      return null;
    }

    // Exécution de la requête traduite
    try {
      final rows = await db.rawQuery(translated.sql);

      if (translated.isScalar) {
        // Mode SCALAR : une règle sans GROUP BY retourne une valeur agrégée.
        //
        // SESSION 52 FIX (d) — faux positifs règles scalaires de valeur de référence :
        //
        // Certaines règles (ex: id=416 msg="NB LATRINES FONC TOTAL") sont des règles
        // de référence (valeurs pour ASSOC) — elles n'ont PAS le mot "INCOHERENCE"
        // dans leur message et n'ont PAS d'entrée dans dico_regle_theme_assoc.
        // Sum(NB_LATRINES_ELEVES)=5 signifie 5 latrines, pas une incohérence.
        //
        // FIX CORRIGÉ (SESSION 52 rev1) — critère plus précis et safe :
        //
        //   • Règle de référence (pas d'INCOHERENCE dans le message + SQL Sum pur)
        //     val > 0 ne déclenche PAS de violation → ignorée silencieusement.
        //     Ces règles sont ici parce qu'elles n'ont pas d'ASSOC dans la DB locale
        //     (leurs ASSOC se trouvent dans les données serveur, pas dans le JSON statique).
        //
        //   • Règle de violation réelle ("INCOHERENCE" dans le message, ou arithmétique
        //     dans le SQL) → comportement normal val != 0 → violation.
        //
        // NOTE : zéro régression — les règles 814-834 ont toutes "INCOHERENCE" dans
        // leur message ou ont des ASSOC → ne sont pas affectées par ce skip.
        final val = _readScalarValue(rows);
        if (val != null && val != 0.0) {
          // Vérifie si c'est une règle de valeur de référence (pas une règle de violation)
          if (_isReferenceValueRule(sqlRegle, message)) {
            logger.log('[ThemeRuleEngine] ✓ règle $idRegle OK — valeur de référence '
                '(val=$val, standalone skip: message sans INCOHERENCE + Sum pur)');
          } else {
            logger.log('[ThemeRuleEngine] ✗ règle $idRegle VIOLÉE (scalar=$val)');
            return ThemeCoherenceError(
              idRegle:    idRegle,
              idTheme:    idTheme,
              message:    message,
              ordreRegle: ordreRegle,
              value1:     val,
            );
          }
        } else {
          logger.log('[ThemeRuleEngine] ✓ règle $idRegle OK (scalar=$val)');
        }
      } else {
        // Mode EXISTS : COUNT(*) des violations
        final count = Sqflite.firstIntValue(rows) ?? 0;
        if (count > 0) {
          logger.log('[ThemeRuleEngine] ✗ règle $idRegle VIOLÉE (count=$count)');
          return ThemeCoherenceError(
            idRegle:    idRegle,
            idTheme:    idTheme,
            message:    message,
            ordreRegle: ordreRegle,
            value1:     count.toDouble(),
          );
        }
        logger.log('[ThemeRuleEngine] ✓ règle $idRegle OK (count=0)');
      }
    } catch (e) {
      logger.log('[ThemeRuleEngine] ⚠️ règle $idRegle rawQuery: $e — ignorée');
    }
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // ÉVALUATION MODE ASSOCIATION
  // ═══════════════════════════════════════════════════════════════════════════

  /// Évalue une règle avec association : comparaison scalaire des deux résultats.
  ///
  /// La règle principale [sqlRegle] et la règle associée sont exécutées séparément.
  /// Leurs résultats scalaires sont comparés selon le critère.
  Future<ThemeCoherenceError?> _evaluateWithAssoc({
    required Database db,
    required CoherenceLogger logger,
    required int idRegle,
    required int idTheme,
    required int ordreRegle,
    required String sqlRegle,
    required String message,
    required List<Map<String, dynamic>> assocs,
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? codeEtab,
    String? codeTypeAnnee,
  }) async {
    // Utilise la première association active
    final assoc       = assocs.first;
    final idRegleAssoc = (assoc['id_regle_theme_assoc'] as int?) ?? 0;
    final critere     = (assoc['critere'] as String?)            ?? '>';

    logger.log('[ThemeRuleEngine] règle $idRegle MODE ASSOC '
        '→ règle associée=$idRegleAssoc critere="$critere"');

    // Récupère le SQL de la règle associée
    final assocRuleRows = await db.query(
      'dico_regle_theme',
      where: 'id_regle = ?',
      whereArgs: [idRegleAssoc],
      limit: 1,
    );

    // SESSION 52 FIX (b) — règle associée introuvable dans dico_regle_theme :
    // Le serveur stocke des règles associées qui appartiennent à d'autres thèmes
    // et ne sont donc pas dans la table dico_regle_theme locale.
    // AVANT : fallback EXISTS → comparaison arithmétique remplacée par test d'existence
    //         → faux positifs systématiques (count > 0 toujours vrai quand des données existent).
    // MAINTENANT : on cherche d'abord le sql_assoc stocké dans dico_regle_theme_assoc
    //   (ajouté par le serveur via data_rules.php → associations[].sql_assoc).
    //   Si sql_assoc est non vide → on l'utilise directement.
    //   Si sql_assoc est vide ET règle introuvable → on IGNORE la règle (skip silencieux)
    //   au lieu du fallback EXISTS abusif.

    // Tente de récupérer le sql_assoc directement depuis l'enregistrement d'association
    final sqlAssocDirect = (assoc['sql_assoc'] as String?)?.trim() ?? '';

    if (assocRuleRows.isEmpty) {
      if (sqlAssocDirect.isNotEmpty) {
        // sql_assoc disponible directement → on l'utilise sans chercher dans dico_regle_theme
        logger.log('[ThemeRuleEngine] règle $idRegle: règle associée $idRegleAssoc '
            'introuvable dans dico_regle_theme mais sql_assoc disponible → utilisation directe');
      } else {
        // sql_assoc vide ET règle introuvable → on ignore (skip silencieux) au lieu
        // du fallback EXISTS abusif qui causait des faux positifs.
        logger.log('[ThemeRuleEngine] règle $idRegle: règle associée $idRegleAssoc '
            'introuvable et sql_assoc vide → règle ignorée (pas de fallback EXISTS abusif)');
        return null;
      }
    }

    final sqlAssoc = sqlAssocDirect.isNotEmpty
        ? sqlAssocDirect
        : (assocRuleRows.first['sql_regle'] as String?) ?? '';

    // Exécute les deux règles et lit leurs valeurs scalaires
    final val1 = await _executeScalar(
      db:            db,
      logger:        logger,
      ruleName:      'règle $idRegle (principale)',
      sql:           sqlRegle,
      idCamp:        idCamp,
      idEtab:        idEtab,
      idQst:         idQst,
      codeEtab:      codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );

    final val2 = await _executeScalar(
      db:            db,
      logger:        logger,
      ruleName:      'règle $idRegleAssoc (associée)',
      sql:           sqlAssoc,
      idCamp:        idCamp,
      idEtab:        idEtab,
      idQst:         idQst,
      codeEtab:      codeEtab,
      codeTypeAnnee: codeTypeAnnee,
    );

    if (val1 == null || val2 == null) {
      logger.log('[ThemeRuleEngine] règle $idRegle ASSOC: val1=$val1 val2=$val2 '
          '— non évaluable');
      return null;
    }

    logger.log('[ThemeRuleEngine] règle $idRegle ASSOC: '
        '$val1 $critere $val2 ?');

    // Applique le critère de comparaison
    final isViolated = _applyOperator(val1, critere, val2);

    if (isViolated) {
      logger.log('[ThemeRuleEngine] ✗ règle $idRegle VIOLÉE ($val1 $critere $val2)');
      return ThemeCoherenceError(
        idRegle:    idRegle,
        idTheme:    idTheme,
        message:    message,
        ordreRegle: ordreRegle,
        value1:     val1,
        value2:     val2,
        critere:    critere,
      );
    }

    logger.log('[ThemeRuleEngine] ✓ règle $idRegle OK ($val1 [$critere] $val2 = respecté)');
    return null;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // UTILITAIRES PRIVÉS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Traduit et exécute une requête SQL, retourne la valeur scalaire (première cellule).
  Future<double?> _executeScalar({
    required Database db,
    required CoherenceLogger logger,
    required String ruleName,
    required String sql,
    required String idCamp,
    required String idEtab,
    required String idQst,
    String? codeEtab,
    String? codeTypeAnnee,
  }) async {
    TranslationResult? translated;
    try {
      translated = SqlTranslator.translate(
        serverSql:      sql,
        idCamp:         idCamp,
        idEtab:         idEtab,
        idQst:          idQst,
        codeEtab:       codeEtab,
        codeTypeAnnee:  codeTypeAnnee,
        logger:         logger,
      );
    } catch (e) {
      logger.log('[ThemeRuleEngine] ⚠️ $ruleName erreur traduction: $e');
      return null;
    }

    if (translated == null) {
      logger.log('[ThemeRuleEngine] $ruleName SQL non traduisible');
      return null;
    }

    try {
      final rows = await db.rawQuery(translated.sql);
      final val  = _readScalarValue(rows);
      logger.log('[ThemeRuleEngine] $ruleName → $val');
      return val;
    } catch (e) {
      logger.log('[ThemeRuleEngine] ⚠️ $ruleName rawQuery: $e');
      return null;
    }
  }

  /// Lit la valeur scalaire : DERNIÈRE cellule de la première ligne.
  ///
  /// SESSION 52 FIX — utilise values.last (comme CoherenceEvaluator Session 66)
  /// pour les règles scalaires multi-colonnes.
  /// Ex : SELECT Sum(FILLES), Sum(TOTAL) → values = [FILLES=12, TOTAL=30]
  ///   → last = 30 (valeur totale de référence) | first = 12 (valeur à comparer)
  double? _readScalarValue(List<Map<String, dynamic>> rows) {
    if (rows.isEmpty) return 0.0;
    final first = rows.first;
    if (first.isEmpty) return 0.0;
    final raw = first.values.last; // SESSION 52: values.last au lieu de values.first
    if (raw == null) return 0.0;
    if (raw is num) return raw.toDouble();
    return double.tryParse(raw.toString()) ?? 0.0;
  }

  /// Détermine si une règle scalaire est une valeur de référence (pas une règle
  /// de violation autonome).
  ///
  /// Critères COMBINÉS (les DEUX doivent être vrais) :
  ///   1. Le message ne contient PAS "INCOHERENCE" — les vraies règles de violation
  ///      ont systématiquement "INCOHERENCE" dans leur message (convention Burundi).
  ///      Les règles de référence ont des messages descriptifs (ex: "NB LATRINES FONC TOTAL").
  ///
  ///   2. Le SQL ne contient PAS d'arithmétique dans la clause SELECT :
  ///      Sum(X-Y), Sum(X+Y), Sum(CASE WHEN...) → c'est une différence ou un indicateur
  ///      → c'est une règle de violation (val != 0 = différence détectée).
  ///      Sum(X) seul sans opérateur → c'est une valeur de compte (référence).
  ///
  /// Exemples de valeurs de référence (skip standalone) :
  ///   message="NB LATRINES FONC TOTAL"  SQL=Sum(NB_LATRINES_ELEVES)   → référence
  ///   message="TITULAIRE 1-6 SDC"       SQL=Sum(NB_ENS_TITULAIRE)     → référence
  ///
  /// Exemples de règles de violation (ne PAS skip) :
  ///   message="INCOHERENCE ENTRE A ET B" SQL=Sum(A-B)                  → violation
  ///   message="NVX INSCRITS TOTAL..."    SQL=Sum(NVX-TOTAL) avec arith  → violation
  static bool _isReferenceValueRule(String sql, String message) {
    // Critère 1 : le message ne contient PAS "INCOHERENCE"
    if (message.toUpperCase().contains('INCOHERENCE')) return false;

    // Critère 2 : le SQL Sum ne contient PAS d'opérateurs arithmétiques
    // dans la clause SELECT (entre SELECT et FROM).
    final selMatch = RegExp(
      r'\bSELECT\b(.+?)\bFROM\b',
      caseSensitive: false,
      dotAll: true,
    ).firstMatch(sql);
    if (selMatch == null) return false;

    final selectPart = selMatch.group(1)!.trim();

    // Supprime les alias AS xxx pour éviter de confondre les alias avec des opérateurs
    final noAlias = selectPart.replaceAll(
      RegExp(r'\bAS\b\s+\w+', caseSensitive: false), '');

    // Vérifie l'absence d'arithmétique : +, -, *, / dans le SELECT
    // et l'absence de CASE WHEN (indicateur de violation conditionnel)
    final hasArithmetic = RegExp(
      r'[+\-*/]|\bCASE\b|\bWHEN\b',
      caseSensitive: false,
    ).hasMatch(noAlias);

    if (hasArithmetic) return false;  // Contient de l'arithmétique → règle de violation

    return true;  // Pas d'INCOHERENCE + pas d'arithmétique → valeur de référence
  }

  /// Applique un opérateur de comparaison.
  /// Retourne true si la condition est vraie (violation levée).
  ///
  /// Convention du fichier Excel :
  ///   critere='>' → violation si val1 > val2 (résultat > référence)
  ///   critere='=' → violation si val1 = val2 (règle toujours violée si résultat = ref)
  bool _applyOperator(double val1, String critere, double val2) {
    switch (critere.trim()) {
      case '>':  return val1 > val2;
      case '<':  return val1 < val2;
      case '=':  return val1 == val2;
      case '>=': return val1 >= val2;
      case '<=': return val1 <= val2;
      case '!=':
      case '<>': return val1 != val2;
      default:
        debugPrint('[ThemeRuleEngine] ⚠️ opérateur inconnu: "$critere" — violation non levée');
        return false;
    }
  }
}

// =============================================================================
// ThemeRuleSeederHelper — Seeding des règles depuis les assets Flutter
// =============================================================================

/// Helper statique pour le seeding initial depuis les assets JSON compilés.
///
/// Les assets JSON (dico_regle_theme.json, dico_regle_theme_assoc.json) sont
/// compilés dans l'APK/IPA et servent de seed initial pour la table SQLite.
/// L'API peut ensuite mettre à jour les règles via insertDicoRegleTheme().
///
/// À appeler une fois au démarrage de l'application.
class ThemeRuleSeederHelper {
  ThemeRuleSeederHelper._(); // Classe utilitaire statique uniquement

  /// Charge et insère les règles de cohérence depuis les JSON assets.
  ///
  /// Idempotent : ne fait rien si les règles sont déjà en base (sauf si
  /// [forceReseed] est true, utile pour les mises à jour de version).
  static Future<void> seedIfEmpty({
    required DatabaseService db,
    bool forceReseed = false,
  }) async {
    try {
      final count = await db.getDicoRegleThemeCount();
      if (count > 0 && !forceReseed) {
        debugPrint('[ThemeRuleSeeder] table déjà peuplée ($count règles) — skip');
        return;
      }

      debugPrint('[ThemeRuleSeeder] début du seeding depuis les assets JSON...');

      // ── Règles DICO_REGLE_THEME ───────────────────────────────────────────
      final rulesStr = await _loadAsset('assets/dico_regle_theme.json');
      if (rulesStr == null) {
        debugPrint('[ThemeRuleSeeder] ⚠️ assets/dico_regle_theme.json introuvable');
        return;
      }

      final rulesList = _parseJsonList(rulesStr);
      if (rulesList.isEmpty) {
        debugPrint('[ThemeRuleSeeder] ⚠️ aucune règle parsée');
        return;
      }

      await db.insertDicoRegleTheme(rulesList);
      debugPrint('[ThemeRuleSeeder] ✓ ${rulesList.length} règles insérées');

      // ── Associations DICO_REGLE_THEME_ASSOC ────────────────────────────────
      final assocsStr = await _loadAsset('assets/dico_regle_theme_assoc.json');
      if (assocsStr != null) {
        final assocsList = _parseJsonList(assocsStr);
        if (assocsList.isNotEmpty) {
          await db.insertDicoRegleThemeAssoc(assocsList);
          debugPrint('[ThemeRuleSeeder] ✓ ${assocsList.length} associations insérées');
        }
      }

      debugPrint('[ThemeRuleSeeder] ✓ seeding terminé avec succès');
    } catch (e) {
      // Non-bloquant : l'application peut fonctionner sans ces règles
      debugPrint('[ThemeRuleSeeder] ⚠️ erreur seeding: $e');
    }
  }

  static Future<String?> _loadAsset(String path) async {
    try {
      return await rootBundle.loadString(path);
    } catch (_) {
      debugPrint('[ThemeRuleSeeder] asset non trouvé: $path');
      return null;
    }
  }

  static List<Map<String, dynamic>> _parseJsonList(String jsonStr) {
    try {
      final decoded = json.decode(jsonStr);
      if (decoded is List) {
        return decoded.whereType<Map>().map((e) {
          return Map<String, dynamic>.from(e as Map);
        }).toList();
      }
      return [];
    } catch (e) {
      debugPrint('[ThemeRuleSeeder] ⚠️ erreur parsing JSON: $e');
      return [];
    }
  }
}
