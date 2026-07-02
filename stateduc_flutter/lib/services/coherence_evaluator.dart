// =============================================================================
// coherence_evaluator.dart — Moteur d'évaluation de cohérence HORS LIGNE
// =============================================================================
//
// Ce fichier implémente l'évaluation offline des règles de cohérence StatEduc.
// Il est le pendant mobile de controle_theme_batch.class.php (côté serveur).
//
// CONTEXTE :
//   Le serveur évalue la cohérence en exécutant des requêtes SQL (sql_regle et
//   sql_assoc) sur la base SQL Server/Oracle, puis compare les résultats avec un
//   opérateur (critere). Cette approche n'est pas possible hors ligne car le
//   mobile n'a pas accès à la base de données serveur.
//
// APPROCHE OFFLINE :
//   Les SQL serveur suivent des patterns prévisibles :
//
//   Cas simple (champ form direct) :
//     SELECT Sum(TABLE.NOM_CHAMP) FROM TABLE WHERE ...
//     → on extrait NOM_CHAMP et on le cherche dans collected_data
//
//   Cas vue agrégée (ELEVES_AGE_NIVEAU_SEXE) :
//     SELECT Sum(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU) FROM ...
//     SELECT Sum(ELEVES_AGE_NIVEAU_SEXE.TOTAL_AGE_NIVEAU) FROM ...
//     → ces colonnes n'existent pas dans collected_data (c'est une vue DB)
//     → FILLES_AGE_NIVEAU ≈ somme des champs filles (_F_ / _F$ dans le nom)
//     → TOTAL_AGE_NIVEAU  ≈ somme de tous les champs numériques du formulaire
//
//   Cas multi-tables (DONNEES_ETABLISSEMENT) :
//     SELECT Sum(DONNEES_ETABLISSEMENT.NB_ELEVES_F) FROM ...
//     → NB_ELEVES_F peut être un champ d'un autre formulaire (autre idQst)
//     → on charge tous les collected_data de l'école pour la campagne
//       et on somme toutes les occurrences du champ (cross-question)
//
// PRINCIPE DE VIOLATION :
//   - Si la condition "V1 critere V2" est VRAIE → données cohérentes (OK)
//   - Si la condition est FAUSSE              → violation détectée (KO)
//   - _applyOperator() retourne true quand la règle EST violée
//
// CONSERVATISME :
//   Si un champ SQL ne peut pas être résolu malgré les trois stratégies,
//   la règle est silencieusement ignorée (pas de faux positifs).
//   Ce moteur ne signale que les violations CERTAINES.
//
// PATTERNS SQL SUPPORTÉS (Access/SQL Server style avec HAVING) :
//   1. SELECT Sum(TABLE.FIELD) AS Alias FROM TABLE GROUP BY ... HAVING ...
//   2. SELECT Sum(FIELD) FROM TABLE WHERE ...
//   3. SELECT NVL(SUM(TABLE.FIELD),0) FROM ...    (Oracle NVL)
//   4. SELECT COALESCE(SUM(TABLE.FIELD),0) FROM ...
//   5. SELECT TABLE.FIELD FROM ...               (champ brut qualifié)
//   6. SELECT FIELD FROM ...                     (champ brut sans qualificateur)
//
// COLONNES DE VUES GÉRÉES PAR APPROXIMATION :
//   TOTAL_AGE_NIVEAU → somme de tous les champs numériques du formulaire courant
//   FILLES_AGE_NIVEAU → somme des champs dont le nom contient un indicateur filles
//                        (_F_, _F$, NB_F_, NB_F$, _FILLES_, FILLE)
//
import 'package:flutter/foundation.dart';
import '../services/api_service.dart';
import '../services/database_service.dart';

/// CoherenceEvaluator — Moteur d'évaluation de cohérence hors ligne.
///
/// Équivalent mobile de `controle_theme_batch.class.php` (serveur).
///
/// Principe de fonctionnement :
///   1. Charge les données persistées depuis SQLite (collected_data)
///      — d'abord pour l'idQst courant (Session 39), puis pour TOUS les
///        formulaires de l'école+campagne (Session 40, cross-question)
///   2. Superpose les données non sauvegardées (formData en mémoire)
///   3. Calcule des totaux virtuels pour les vues DB non directement stockées
///   4. Pour chaque règle : extrait V1 (sql_regle) et V2 (sql_assoc)
///   5. Applique l'opérateur critere : si NON respecté → violation
///
/// Différence avec le contrôle serveur :
///   - Serveur : exécute SQL réel sur SQL Server/Oracle → résultat exact
///   - Offline  : extrait le nom de champ par regex et cherche dans SQLite
///     → approximation acceptable pour un feedback immédiat à l'agent
class CoherenceEvaluator {
  CoherenceEvaluator({
    required DatabaseService db,
  }) : _db = db;

  final DatabaseService _db;

  // Colonnes de vues DB agrégées que nous ne pouvons pas résoudre directement
  // depuis collected_data — traitées par approximation via totaux virtuels.
  static const _viewColumnTotal   = 'TOTAL_AGE_NIVEAU';
  static const _viewColumnFilles  = 'FILLES_AGE_NIVEAU';

  // Patterns dans les noms de champs de formulaire qui identifient les filles.
  // Exemples observés : NB_F_6ANS, NB_F_TOTAL, FILLES_6ANS, NB_FILLES
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
  ///   [formData] — données formulaire en mémoire (peuvent contenir des modifs non sauvegardées)
  ///   [idCamp], [idQst], [idEtab], [idFilter] — contexte de saisie courant
  ///
  /// Algorithme :
  ///   1. Charge les données du formulaire courant (idQst) depuis SQLite
  ///   2. Charge les données de TOUS les formulaires de l'école (cross-question)
  ///   3. Superpose formData (données non sauvegardées, priorité la plus haute)
  ///   4. Calcule les totaux virtuels (TOTAL_AGE_NIVEAU, FILLES_AGE_NIVEAU)
  ///   5. Pour chaque règle : _extractValue(sql) → valeur numérique ou null
  ///   6. Si V1 ou V2 non trouvé → règle ignorée silencieusement
  ///   7. _applyOperator(V1, V2, critere) → true si VIOLÉE
  Future<List<OfflineCoherenceError>> evaluate({
    required List<CoherenceRule> rules,
    required Map<String, String> formData,
    required String idCamp,
    required String idQst,
    required String idEtab,
    String? idFilter,
  }) async {
    if (rules.isEmpty) return [];

    final Map<String, double> values = {};

    // ── Étape 1 : données du formulaire courant (toutes périodes) ─────────
    // SESSION 39 : getAllCollectedDataForCoherence() charge tous les filtres
    // pour reproduire le SUM() sans restriction de filtre du serveur.
    // Clés au format "FIELD_NAME#FILTER_ID" ou "FIELD_NAME".
    final persistedData = await _db.getAllCollectedDataForCoherence(
      idCamp: idCamp,
      idEtab: idEtab,
      idQst:  idQst,
    );
    for (final entry in persistedData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) values[entry.key.toUpperCase()] = v;
    }

    // ── Étape 2 : données cross-formulaires de l'école (cross-question) ───
    // SESSION 40 : charge TOUS les champs collectés pour cette école + campagne,
    // tous formulaires confondus. Nécessaire pour les règles qui référencent des
    // champs d'une autre table DB (ex : DONNEES_ETABLISSEMENT.NB_ELEVES_F peut
    // être saisi dans un formulaire différent du formulaire courant).
    // Les doublons sont sommés (même comportement que SUM() du serveur).
    // Ces données sont à PRIORITÉ INFÉRIEURE aux données du formulaire courant
    // (écrasées dans l'étape suivante si même clé).
    final allEtabData = await _db.getAllCollectedDataForCampEtab(
      idCamp: idCamp,
      idEtab: idEtab,
    );
    for (final entry in allEtabData.entries) {
      final key = entry.key.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null && !values.containsKey(key)) {
        // N'écrase pas les données du formulaire courant (déjà chargées étape 1)
        values[key] = v;
      }
    }

    // ── Étape 3 : données non sauvegardées en mémoire (priorité max) ──────
    // Override avec les valeurs du formulaire couramment affiché qui n'ont pas
    // encore été persistées dans SQLite (saisie en cours).
    for (final entry in formData.entries) {
      final v = double.tryParse(entry.value);
      if (v != null) values[entry.key.toUpperCase()] = v;
    }

    // ── Étape 4 : totaux virtuels pour les vues DB ─────────────────────────
    // Les règles pour idQst=9502 référencent ELEVES_AGE_NIVEAU_SEXE, une vue DB
    // qui agrège les champs du formulaire. Nous calculons des approximations :
    //   TOTAL_AGE_NIVEAU  = somme de tous les champs numériques du formulaire courant
    //   FILLES_AGE_NIVEAU = somme des champs "filles" (NB_F_*, FILLES_*, etc.)
    // Ces virtuels ne sont injectés que si les vraies colonnes de vue sont absentes.
    _injectVirtualAggregates(values, persistedData, formData);

    debugPrint('[CoherenceEval] evaluate: idQst=$idQst idEtab=$idEtab '
        'persistedFields=${persistedData.length} formFields=${formData.length} '
        'totalValues=${values.length} rules=${rules.length}');

    final violations = <OfflineCoherenceError>[];

    for (final rule in rules) {
      try {
        final v1 = _extractValue(rule.sqlRegle, values);
        final v2 = _extractValue(rule.sqlAssoc, values);

        debugPrint('[CoherenceEval] rule=${rule.idRegle} '
            'sql_regle="${rule.sqlRegle}" → v1=$v1 | '
            'sql_assoc="${rule.sqlAssoc}" → v2=$v2 | '
            'critere="${rule.critere}"');

        if (v1 == null || v2 == null) {
          debugPrint(
              '[CoherenceEval] skip idRegle=${rule.idRegle} — v1=$v1 v2=$v2 '
              '— field(s) not resolvable from collected data');
          continue;
        }

        final violated = _applyOperator(v1, v2, rule.critere);
        debugPrint('[CoherenceEval] rule=${rule.idRegle} v1=$v1 ${rule.critere} v2=$v2 → violated=$violated');
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
    debugPrint('[CoherenceEval] evaluate complete: ${violations.length} violation(s)');
    return violations;
  }

  // ═══════════════════════════════════════════════════════════════════════════
  // HELPERS PRIVÉS
  // ═══════════════════════════════════════════════════════════════════════════

  /// Injecte dans [values] des approximations pour les colonnes de vues DB
  /// qui ne sont pas stockées directement dans collected_data.
  ///
  /// Les colonnes de vues concernées ici (observées en production) :
  ///   TOTAL_AGE_NIVEAU  — colonne de la vue ELEVES_AGE_NIVEAU_SEXE
  ///     = somme de tous les champs numériques du formulaire courant
  ///   FILLES_AGE_NIVEAU — colonne de la vue ELEVES_AGE_NIVEAU_SEXE
  ///     = somme des champs dont le nom porte un marqueur "filles"
  ///
  /// Ces valeurs ne sont injectées que si la colonne n'est PAS déjà présente
  /// dans [values] (conservatisme : si les vraies données existent, on les garde).
  void _injectVirtualAggregates(
    Map<String, double> values,
    Map<String, String> persistedData,
    Map<String, String> formData,
  ) {
    // Reconstruit les données brutes du formulaire courant (sans suffixe filtre)
    // pour calculer les agrégats. On combine données persistées + formData.
    final rawFields = <String, double>{};

    for (final entry in persistedData.entries) {
      // Retire le suffixe filtre (#FILTER_ID) pour ne garder que le nom de champ
      final fieldName = entry.key.split('#').first.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null) {
        rawFields[fieldName] = (rawFields[fieldName] ?? 0.0) + v;
      }
    }
    for (final entry in formData.entries) {
      final fieldName = entry.key.toUpperCase();
      final v = double.tryParse(entry.value);
      if (v != null) rawFields[fieldName] = v; // formData écrase (non sauvegardé)
    }

    if (rawFields.isEmpty) return;

    // Calcule TOTAL_AGE_NIVEAU = somme de tous les champs numériques
    if (!values.containsKey(_viewColumnTotal)) {
      final total = rawFields.values.fold(0.0, (a, b) => a + b);
      if (total > 0) {
        values[_viewColumnTotal] = total;
        debugPrint('[CoherenceEval] virtual $_viewColumnTotal = $total '
            '(sum of ${rawFields.length} fields)');
      }
    }

    // Calcule FILLES_AGE_NIVEAU = somme des champs "filles"
    if (!values.containsKey(_viewColumnFilles)) {
      double fillesSum = 0.0;
      int fillesCount = 0;
      for (final entry in rawFields.entries) {
        if (_fillesPatterns.hasMatch(entry.key)) {
          fillesSum += entry.value;
          fillesCount++;
        }
      }
      if (fillesCount > 0) {
        values[_viewColumnFilles] = fillesSum;
        debugPrint('[CoherenceEval] virtual $_viewColumnFilles = $fillesSum '
            '(sum of $fillesCount filles fields)');
      }
    }
  }

  /// Extrait une valeur numérique depuis [sql] en analysant le nom de champ
  /// et en le cherchant dans [values] (map NOM_CHAMP → valeur double).
  ///
  /// SESSION 40 — corrections majeures :
  ///
  ///   Problème 1 : TABLE.FIELD qualifié
  ///     L'ancienne regex `SUM\s*\(\s*(\w+)\s*\)` capturait le qualificateur de
  ///     table (ex : ELEVES_AGE_NIVEAU_SEXE) au lieu du nom de champ (FILLES_AGE_NIVEAU)
  ///     car `\w+` s'arrête au point. La nouvelle regex `(?:\w+\.)?(\w+)` ignore
  ///     optionnellement le préfixe table.
  ///
  ///   Problème 2 : Access SQL avec HAVING au lieu de WHERE
  ///     Les SQL StatEduc utilisent GROUP BY ... HAVING (((TABLE.CODE_ETAB)=X) ...)
  ///     au lieu de WHERE. Le parsing regex n'est pas affecté (on ne parse que le
  ///     SELECT et le FROM).
  ///
  ///   Problème 3 : Multi-colonnes SELECT
  ///     SELECT Sum(T.F1) AS A1, Sum(T.F2) AS A2 FROM ...
  ///     La nouvelle implémentation extrait TOUTES les SUM() et utilise la PREMIÈRE
  ///     colonne (comportement serveur : lit result[0] du RecordSet).
  ///
  /// Stratégies de résolution (dans l'ordre) :
  ///   1. Pattern SUM(TABLE.FIELD) ou SUM(FIELD) — prend la 1re colonne SUM
  ///   2. Pattern NVL(SUM(...),0) ou COALESCE(SUM(...),0) — pareil
  ///   3. Bare SELECT TABLE.FIELD FROM ou SELECT FIELD FROM
  ///   4. null si aucun pattern reconnu ou champ introuvable
  double? _extractValue(String sql, Map<String, double> values) {
    if (sql.trim().isEmpty) return null;
    final upperSql = sql.toUpperCase();

    // ── Pattern 1+2 : SUM([TABLE.]FIELD) avec ou sans NVL/COALESCE ─────────
    // Capte aussi bien SUM(FIELD) que SUM(TABLE.FIELD).
    // Le qualificateur table `(?:\w+\.)?` est optionnel, le groupe capturant
    // capture uniquement le nom de champ après le point (ou le nom seul).
    //
    // Exemples matchés :
    //   SUM(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU)  → FILLES_AGE_NIVEAU
    //   SUM(NB_ELEVES_F)                               → NB_ELEVES_F
    //   NVL(SUM(T.NB_G), 0)                            → NB_G
    //   COALESCE(SUM(NB_F),0)                          → NB_F
    final sumRegex = RegExp(
      r'SUM\s*\(\s*(?:\w+\.)?\s*(\w+)\s*\)',
      caseSensitive: false,
    );
    final sumMatches = sumRegex.allMatches(upperSql).toList();
    if (sumMatches.isNotEmpty) {
      // Utilise la PREMIÈRE colonne SUM — mirrors le comportement serveur
      // (exécute la requête et lit la première colonne du résultat).
      final fieldName = sumMatches.first.group(1)!;
      final result = _sumFieldAcrossAllFilters(fieldName, values);
      if (result != null) return result;

      // Tentative sur toutes les colonnes SUM si la première échoue.
      // Utile si les colonnes sont dans un ordre différent de celui attendu.
      for (int i = 1; i < sumMatches.length; i++) {
        final alt = _sumFieldAcrossAllFilters(sumMatches[i].group(1)!, values);
        if (alt != null) {
          debugPrint('[CoherenceEval] _extractValue: fallback to SUM column '
              '${i + 1}/${sumMatches.length} "${sumMatches[i].group(1)}"');
          return alt;
        }
      }
      return null;
    }

    // ── Pattern 3 : SELECT [TABLE.]FIELD FROM (champ brut sans agrégation) ──
    // Exemples :
    //   SELECT DONNEES_ETABLISSEMENT.NB_ELEVES FROM ...  → NB_ELEVES
    //   SELECT NB_TOTAL FROM ...                        → NB_TOTAL
    final bareMatch = RegExp(
      r'SELECT\s+(?:\w+\.)?(\w+)\s+FROM',
      caseSensitive: false,
    ).firstMatch(upperSql);

    if (bareMatch != null) {
      final fieldName = bareMatch.group(1)!;
      // Exclure les mots-clés SQL qui ne sont pas des noms de champs
      const sqlKeywords = {
        'DISTINCT', 'TOP', 'ALL', 'COUNT', 'AVG', 'MIN', 'MAX', 'SUM',
        'NVL', 'COALESCE', 'ISNULL', 'NULLIF',
      };
      if (sqlKeywords.contains(fieldName)) return null;
      return _sumFieldAcrossAllFilters(fieldName, values);
    }

    return null;
  }

  /// Calcule la somme d'un champ sur tous les filtres disponibles dans [values].
  ///
  /// Les clés peuvent avoir un suffixe filtre : "NOM_CHAMP#ID_FILTRE".
  /// Si le champ existe sans suffixe (données non filtrées), il est inclus.
  ///
  /// Retourne null si le champ n'existe PAS DU TOUT (aucune occurrence trouvée)
  /// — pour que l'appelant puisse ignorer silencieusement la règle et éviter
  /// les faux positifs (comportement conservatif).
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
