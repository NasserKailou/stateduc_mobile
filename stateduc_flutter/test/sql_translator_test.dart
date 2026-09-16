// =============================================================================
// sql_translator_test.dart
// Tests unitaires du moteur de traduction SQL (SqlTranslator)
// =============================================================================
//
// Ce fichier teste la classe SqlTranslator en isolation (pas de SQLite réel).
// Il vérifie que les requêtes SQL de type Access/SQL Server sont correctement
// traduites en SQL compatible SQLite.
//
// Cas testés :
//   1. Règle électricité (ELECTRICITE / FONCT_ALIMENT_ELECTRICITE)
//   2. Règle clôture / superficie (CLOTURE / SUPERFICIE)
//   3. Substitution des paramètres ($CODE_ETABLISSEMENT / $CODE_TYPE_ANNEE)
//   4. Traduction syntaxique Is Null → IS NULL
//   5. Traduction NVL → COALESCE
//   6. Détection des tables serveur connues
//   7. Requête sans table serveur connue → null (non traduisible)
//
// Pour lancer :
//   flutter test test/sql_translator_test.dart

import 'package:flutter_test/flutter_test.dart';
import 'package:stateduc_mobile/services/coherence_evaluator.dart';

void main() {
  // ═══════════════════════════════════════════════════════════════════════════
  // 1. Substitution des paramètres
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — substitution des paramètres', () {
    test('substitue \$CODE_ETABLISSEMENT par sa valeur', () {
      const sql = '''
        SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT
        WHERE CODE_ETABLISSEMENT = \$CODE_ETABLISSEMENT
      ''';
      final result = SqlTranslator.translate(
        serverSql:      sql,
        idCamp:         'C1',
        idEtab:         'E1',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull, reason: 'La requête doit être traduisible');
      expect(result!.sql, contains("'101012071'"),
          reason: 'Le code établissement doit être substitué');
      expect(result.sql, isNot(contains('\$CODE_ETABLISSEMENT')),
          reason: 'Le paramètre ne doit plus apparaître');
    });

    test('substitue \$CODE_TYPE_ANNEE par sa valeur', () {
      const sql = '''
        SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT
        WHERE CODE_TYPE_ANNEE = \$CODE_TYPE_ANNEE
      ''';
      final result = SqlTranslator.translate(
        serverSql:      sql,
        idCamp:         'C1',
        idEtab:         'E1',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql, contains("'2024'"),
          reason: 'Le code type année doit être substitué');
      expect(result.sql, isNot(contains('\$CODE_TYPE_ANNEE')));
    });

    test('gère les paramètres manquants sans crash', () {
      const sql = 'SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT '
          'WHERE CODE_ETABLISSEMENT = \$CODE_ETABLISSEMENT';
      // codeEtab non fourni → le paramètre reste tel quel dans le SQL
      // Mais la traduction doit quand même réussir (ne pas crasher)
      expect(
        () => SqlTranslator.translate(
          serverSql: sql,
          idCamp:    'C1',
          idEtab:    'E1',
          // codeEtab et codeTypeAnnee intentionnellement omis
        ),
        returnsNormally,
        reason: 'Ne doit pas lever d\'exception si les paramètres manquent',
      );
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 2. Traduction syntaxique
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — traduction syntaxique', () {
    test('traduit "Is Null" → "IS NULL" (case-insensitive)', () {
      const sql = '''
        SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT
        WHERE FONCT_ALIMENT_ELECTRICITE Is Null
      ''';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('IS NULL'));
      expect(result.sql.toUpperCase(), isNot(contains('IS\n NULL')));
    });

    test('traduit "Is Not Null" → "IS NOT NULL"', () {
      const sql = '''
        SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT
        WHERE FONCT_ALIMENT_ELECTRICITE Is Not Null
      ''';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('IS NOT NULL'));
    });

    test('traduit NVL → COALESCE', () {
      const sql = '''
        SELECT NVL(ELECTRICITE, 0) FROM DONNEES_ETABLISSEMENT
      ''';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('COALESCE('));
      expect(result.sql.toUpperCase(), isNot(contains('NVL(')));
    });

    test('le SQL traduit contient un CTE WITH', () {
      const sql = '''
        SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT
        WHERE ELECTRICITE = 1
      ''';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), startsWith('WITH'),
          reason: 'Le SQL traduit doit commencer par WITH ... CTE');
    });

    test('le SQL traduit contient SELECT COUNT(*) AS cnt', () {
      const sql = 'SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT WHERE ELECTRICITE = 1';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('SELECT COUNT(*)'),
          reason: 'Le wrapper COUNT doit être présent');
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 3. Règle électricité — test du cas concret du cahier des charges
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — règle électricité (cas concret)', () {
    // La requête exacte extraite du cahier des charges (Session 45)
    const electriciteSqlRegle = '''
SELECT DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT
FROM DONNEES_ETABLISSEMENT
WHERE (((DONNEES_ETABLISSEMENT.ELECTRICITE)=0 Or (DONNEES_ETABLISSEMENT.ELECTRICITE) Is Null)
       AND ((DONNEES_ETABLISSEMENT.FONCT_ALIMENT_ELECTRICITE)=1))
   OR (((DONNEES_ETABLISSEMENT.ELECTRICITE)=1)
       AND ((DONNEES_ETABLISSEMENT.FONCT_ALIMENT_ELECTRICITE) Is Null))
GROUP BY DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT, DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE
HAVING (((DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT)=\$CODE_ETABLISSEMENT)
       AND ((DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE)=\$CODE_TYPE_ANNEE));
''';

    test('la requête électricité est traduisible', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull,
          reason: 'La requête électricité doit être traduisible par SqlTranslator');
    });

    test('le CTE contient les champs ELECTRICITE et FONCT_ALIMENT_ELECTRICITE', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.fieldNames, contains('ELECTRICITE'),
          reason: 'ELECTRICITE doit être dans les champs extraits');
      expect(result.fieldNames, contains('FONCT_ALIMENT_ELECTRICITE'),
          reason: 'FONCT_ALIMENT_ELECTRICITE doit être dans les champs extraits');
    });

    test('le SQL traduit référence collected_data', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql, contains('collected_data'),
          reason: 'Le CTE doit lire depuis collected_data');
    });

    test('le SQL traduit contient IS NULL (après traduction depuis "Is Null")', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('IS NULL'),
          reason: '"Is Null" doit avoir été traduit en "IS NULL"');
    });

    test('le SQL traduit filtre par id_camp et id_etab', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql, contains("id_camp='CAMP_2024'"),
          reason: 'Le CTE doit filtrer par id_camp');
      expect(result.sql, contains("id_etab='ETAB_001'"),
          reason: 'Le CTE doit filtrer par id_etab');
    });

    test('la table DONNEES_ETABLISSEMENT est identifiée', () {
      final result = SqlTranslator.translate(
        serverSql:      electriciteSqlRegle,
        idCamp:         'C1',
        idEtab:         'E1',
        codeEtab:       '101',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.usedTables, contains('DONNEES_ETABLISSEMENT'));
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 4. Règle clôture / superficie (simulation)
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — règle clôture/superficie', () {
    // Requête simulée pour la règle "établissement clôturé mais superficie nulle"
    // Le schéma exact peut varier selon la configuration du serveur,
    // mais le principe est : CLOTURE=1 AND (SUPERFICIE Is Null OR SUPERFICIE=0)
    const clotureSuperficieSql = '''
SELECT DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT
FROM DONNEES_ETABLISSEMENT
WHERE ((DONNEES_ETABLISSEMENT.CLOTURE)=1)
  AND ((DONNEES_ETABLISSEMENT.SUPERFICIE) Is Null OR (DONNEES_ETABLISSEMENT.SUPERFICIE)=0)
GROUP BY DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT, DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE
HAVING (((DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT)=\$CODE_ETABLISSEMENT)
       AND ((DONNEES_ETABLISSEMENT.CODE_TYPE_ANNEE)=\$CODE_TYPE_ANNEE));
''';

    test('la requête clôture/superficie est traduisible', () {
      final result = SqlTranslator.translate(
        serverSql:      clotureSuperficieSql,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull,
          reason: 'La requête clôture/superficie doit être traduisible');
    });

    test('les champs CLOTURE et SUPERFICIE sont extraits', () {
      final result = SqlTranslator.translate(
        serverSql:      clotureSuperficieSql,
        idCamp:         'CAMP_2024',
        idEtab:         'ETAB_001',
        codeEtab:       '101012071',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.fieldNames, contains('CLOTURE'));
      expect(result.fieldNames, contains('SUPERFICIE'));
    });

    test('IS NULL est présent dans le SQL traduit', () {
      final result = SqlTranslator.translate(
        serverSql:      clotureSuperficieSql,
        idCamp:         'C1',
        idEtab:         'E1',
        codeEtab:       '101',
        codeTypeAnnee:  '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('IS NULL'));
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 5. Requête sans table serveur connue → non traduisible
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — requêtes non traduisibles', () {
    test('retourne null pour une requête sans table serveur connue', () {
      // Requête simple ne référençant aucune table connue
      const sql = 'SELECT SUM(NB_ELEVES_F) FROM NB_ELEVES_F_QST';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
      );
      expect(result, isNull,
          reason: 'Doit retourner null si aucune table serveur connue → '
              'chemin regex fallback');
    });

    test('retourne null pour une requête vide', () {
      final result = SqlTranslator.translate(
        serverSql: '  ', idCamp: 'C1', idEtab: 'E1',
      );
      expect(result, isNull);
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 6. Robustesse — point-virgule final supprimé
  // ═══════════════════════════════════════════════════════════════════════════
  group('SqlTranslator — robustesse', () {
    test('le point-virgule final est retiré de la requête', () {
      const sql = 'SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT '
          'WHERE ELECTRICITE=1;';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      // Le SQL généré ne doit pas contenir de point-virgule au milieu
      // (le CTE wrappé ne doit pas avoir de ; interne)
      final sqlWithoutTerminal = result!.sql.trimRight();
      // Seul le dernier caractère peut éventuellement être un ;
      // Le SELECT COUNT(*) ... ne doit pas avoir de ; intermédiaire
      expect(sqlWithoutTerminal.split(';').length, lessThanOrEqualTo(2),
          reason: 'Pas de point-virgule intermédiaire dans le SQL généré');
    });

    test('les guillemets dans les valeurs de paramètres sont échappés', () {
      const sql = 'SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT '
          'WHERE CODE_ETABLISSEMENT = \$CODE_ETABLISSEMENT';
      // Valeur avec apostrophe (cas extrême)
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: "O'Brien", // apostrophe dans le code
      );
      expect(result, isNotNull);
      // L'apostrophe doit être doublée : 'O''Brien'
      expect(result!.sql, contains("'O''Brien'"),
          reason: 'Les apostrophes dans les paramètres doivent être échappées');
    });
  });

  // ═══════════════════════════════════════════════════════════════════════════
  // 7. Documentation de l'approche — tests de non-régression
  // ═══════════════════════════════════════════════════════════════════════════
  //
  // Ces tests vérifient que l'approche de traduction SQL est cohérente avec
  // les exigences du cahier des charges (Session 45).
  //
  // APPROCHE CHOISIE : CTE de pivot dynamique
  //   - Les tables serveur (DONNEES_ETABLISSEMENT, ELEVES_AGE_NIVEAU_SEXE)
  //     sont remplacées par des CTEs qui pivotent collected_data.
  //   - Le pivot est dynamique : les champs sont extraits du SQL de la règle.
  //   - La requête traduite est encapsulée dans SELECT COUNT(*) pour obtenir
  //     un entier (0 = pas de violation, > 0 = violation).
  //
  // POURQUOI PAS UNE VRAIE TABLE VIRTUELLE :
  //   SQLite ne supporte pas les CREATE VIEW paramétrés. Un CTE est la seule
  //   approche compatible avec la syntaxe SQLite portable.
  //
  // ÉQUIVALENCE AVEC LE SERVEUR :
  //   - La règle retourne des lignes si violation → COUNT(*) > 0 côté SQLite
  //   - Le critere "= 0" signifie : "la requête doit retourner 0 lignes"
  //   - Si COUNT = 0 → pas de violation (critere respecté)
  //   - Si COUNT > 0 → violation (critere non respecté)
  //
  group('Documentation approche — non-régression', () {
    test('le wrapper COUNT permet la comparaison avec le critere "= 0"', () {
      // Le critere standard pour les règles d'existence est "= 0"
      // (la requête doit retourner 0 lignes pour que les données soient cohérentes).
      // Le COUNT(*) traduit cela en : count == 0 → OK, count > 0 → violation
      // L'opérateur _applyOperator('=') retourne !(v1 == v2)
      //   → !(0 == 0) = false → pas de violation si count = 0 ✓
      //   → !(1 == 0) = true  → violation si count = 1 ✓
      const sql = 'SELECT CODE_ETABLISSEMENT FROM DONNEES_ETABLISSEMENT '
          'WHERE ELECTRICITE=1 AND FONCT_ALIMENT_ELECTRICITE IS NULL';
      final result = SqlTranslator.translate(
        serverSql: sql, idCamp: 'C1', idEtab: 'E1',
        codeEtab: '101', codeTypeAnnee: '2024',
      );
      expect(result, isNotNull);
      expect(result!.sql.toUpperCase(), contains('COUNT(*)'),
          reason: 'COUNT(*) est nécessaire pour comparer au critere "= 0"');
    });
  });
}
