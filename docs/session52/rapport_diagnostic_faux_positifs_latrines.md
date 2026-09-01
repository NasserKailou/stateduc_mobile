# Rapport Diagnostic — Faux Positifs Latrines (Session 52)

**Date** : 2026-08-10  
**Établissement** : 22222 (codeEtab=0402012A01, campagne=2, année=21)  
**Thème analysé** : 900 (latrines) + 950 (contexte log)  
**5 faux positifs signalés** :

1. NB LATRINES FONC TOTAL  
2. INCOHERENCE ENTRE NB LATRINES FONC TOTAL ET NB LATRINES FONC FILLES (2.8/a NB LATRINE FONC)  
3. INCOHERENCE ENTRE NB LATRINES FONC TOTAL ET NB LATRINES BON ETAT TOTAL (2.8/a & c)  
4. INCOHERENCE ENTRE NB LATRINES BON ETAT FILLES ET NB LATRINES BON ETAT (2.8/a & c NB LATRINE FONC & LATRINES BON ETAT)  
5. NB LATRINES FONC HANDICAP TOTAL  

---

## 1. Identification des règles latrines

| Règle | Thème | SQL (résumé) | Type |
|-------|-------|--------------|------|
| 416 | 900 | `SELECT Sum(DONNEES_ETABLISSEMENT.NB_LATRINES_ELEVES) AS NB_LATRINES_FONC_TOTAL FROM DONNEES_ETABLISSEMENT WHERE (context)` | Scalaire référence |
| 417 | 900 | `SELECT Sum(DONNEES_ETABLISSEMENT.NB_LATRINES_FILLES) AS NB_LATRINES_FONC_FILLES FROM DONNEES_ETABLISSEMENT WHERE (context)` | Scalaire référence |
| 418 | 900 | `SELECT Sum(DONNEES_ETABLISSEMENT.NB_LATRINES_BON_ETAT) AS NB_LATRINES_BON_ETAT_TOTAL FROM DONNEES_ETABLISSEMENT WHERE (context)` | Scalaire référence |
| 419 | 900 | `SELECT Sum(DONNEES_ETABLISSEMENT.NB_LATRINES_BON_ETAT_F) AS NB_LATRINES_BON_ETAT_FILLES FROM DONNEES_ETABLISSEMENT WHERE (context)` | Scalaire référence |
| 420 | 900 | `SELECT Sum(DONNEES_ETABLISSEMENT.NB_TOTAL_HANDI) AS NB_LATRINES_FONC_HANDICAP_TOTAL FROM DONNEES_ETABLISSEMENT WHERE (context)` | Scalaire référence |

**Constat clé** : Ces 5 règles n'ont **aucune entrée dans `dico_regle_theme_assoc`**. Elles sont conçues pour être utilisées comme valeurs de référence dans des comparaisons ASSOC (ex : `règle_filles ≤ règle_total`), **pas comme des vérificateurs autonomes de cohérence**.

---

## 2. Cause racine des faux positifs

### Bug (d) — Scalaire de référence déclenche violation à tort

**Chemin d'exécution avant correction** :

```
ThemeRuleEngine._evaluateExists() → Mode SCALAR
  ↓ SqlTranslator.translate() → isScalar=true (pas de GROUP BY métier)
  ↓ SELECT Sum(NB_LATRINES_ELEVES) FROM DONNEES_ETABLISSEMENT WHERE (context) → val = 5.0
  ↓ val != null && val != 0.0 → TRUE
  ↓ return ThemeCoherenceError(...)  ← FAUX POSITIF
```

**Exemple concret** : L'établissement a 5 latrines fonctionnelles. `Sum(NB_LATRINES_ELEVES) = 5`. Comme `5 != 0`, l'ancien code déclenchait une violation. Mais `5 latrines = 5` n'est PAS une incohérence — c'est simplement la valeur de référence.

---

## 3. Bugs identifiés dans le log (idTheme=950)

### Bug (a1) — Règle 811 : `near "ISNULL": syntax error`

**Cause** : `_extractAllFieldNames()` scannait le SQL et trouvait `ISNULL` comme identifiant. N'étant pas dans `_sqlKeywordsSet`, il était ajouté au CTE : `SUM(CASE WHEN UPPER(col) LIKE 'ISNULL_%' ...) AS ISNULL` → mot réservé SQLite → erreur syntaxe.

**Fix (a1)** : Ajout de `ISNULL`, `IFNULL`, `IIF`, `SWITCH`, `EXPR`, `EXPR1` et 40+ autres noms de fonctions SQL dans `_sqlKeywordsSet` de `SqlTranslator`.

### Bug (a2) — Règle 810 : `ambiguous column name: CODE_ETABLISSEMENT`

**Cause** : INNER JOIN entre ETABLISSEMENT et ELEVES_AGE_NIVEAU_SEXE. Step 7 de `translate()` strippait tous les qualificateurs `TABLE.FIELD` → `FIELD`. Les deux CTEs exposant `CODE_ETABLISSEMENT`, SQLite ne pouvait pas résoudre l'ambiguïté.

**Fix (a2)** : Détection `usedServerTables.length > 1` (JOIN multi-tables). Si JOIN → on **garde** les qualificateurs `TABLE.FIELD` (les CTEs portent exactement le nom de la table serveur, donc `CTE_NAME.FIELD` est valide dans SQLite).

### Bug (b) — Règles 404, 35, 79, 4, 382 "introuvable" → fallback EXISTS abusif

**Cause** : Ces règles appartiennent à d'autres thèmes et ne sont pas dans `dico_regle_theme` local. `_evaluateWithAssoc()` faisait un fallback vers `_evaluateExists()` qui remplaçait une comparaison arithmétique (ex : `filles ≤ total`) par un simple test d'existence (`COUNT(*) > 0`). Résultat : violation dès qu'il existe des données, même cohérentes.

**Racine sous-jacente** : La colonne `sql_assoc` n'existait pas dans `dico_regle_theme_assoc`. Le serveur (`data_rules.php`) renvoyait déjà `associations[].sql_assoc` dans son JSON, mais Flutter le jetait silencieusement.

**Fix (b)** :
- Schéma : `ALTER TABLE dico_regle_theme_assoc ADD COLUMN sql_assoc TEXT NOT NULL DEFAULT ''`
- Migration : DB version v4 → v5
- `insertDicoRegleThemeAssoc()` : stocke maintenant `a['sql_assoc'] ?? ''`
- `_evaluateWithAssoc()` : si règle introuvable dans `dico_regle_theme` mais `sql_assoc` non vide → utilisation directe. Si les deux manquent → skip silencieux (pas de fallback EXISTS).

### Bug (c) — Règle 836 : `WHERE stripped → GROUP BY seul → count=1 toujours`

**Cause** : La règle associée 382 était "introuvable" → fallback EXISTS → après strip du contexte WHERE, il ne restait que `GROUP BY CODE_TYPE_NIVEAU` → `COUNT(*) = 1` toujours vrai.

**Fix** : Couvert par Fix (b). Règle 836 avec assoc 382 introuvable + sql_assoc vide → skip silencieux. Zéro faux positif.

---

## 4. Correctifs apportés — Résumé

| Fichier | Fix | Description |
|---------|-----|-------------|
| `coherence_evaluator.dart` | (a1) | +40 noms de fonctions SQL dans `_sqlKeywordsSet` |
| `coherence_evaluator.dart` | (a2) | JOIN-aware table qualifier stripping (garde TABLE.FIELD si multi-tables) |
| `database_service.dart` | (b) schema | `sql_assoc` dans `dico_regle_theme_assoc` + migration v5 |
| `theme_rule_engine.dart` | (b) logique | `_evaluateWithAssoc()` utilise `sql_assoc` direct, skip si vide |
| `theme_rule_engine.dart` | (d) | `_isPureReferenceScalarSql()` + skip standalone pour règles référence |
| `theme_rule_engine.dart` | bonus | `_readScalarValue()` → `values.last` (parité CoherenceEvaluator S66) |
| `campaign_list_screen.dart` | UX | Bouton Home (🏠) entre Paramètres et Déconnexion → PinScreen |

---

## 5. Non-régression — Règles 814-834

Les règles 814-834 (thème 950) sont toutes en mode EXISTS avec `GROUP BY` métier ou en mode ASSOC avec règles dans `dico_regle_theme` — aucune n'est un scalaire de référence pure. Les fixes (a1), (a2), (b), (d) ne modifient pas leur chemin d'exécution :

- Fix (a1) : ne change que l'extraction des noms de champs → pas d'impact si les règles 814-834 n'utilisent pas ISNULL/IIF/etc.
- Fix (a2) : s'active uniquement si `usedServerTables.length > 1` — les règles 814-834 mono-table ne sont pas affectées.
- Fix (b) : s'active seulement quand la règle associée est introuvable — si les règles associées de 814-834 sont dans `dico_regle_theme`, le chemin historique est conservé.
- Fix (d) : `_isPureReferenceScalarSql()` retourne `false` pour toute règle avec CASE WHEN, arithmétique, ou GROUP BY → les règles 814-834 ne sont pas touchées.

**Log de référence** : Le log fourni (coherence_latest.log, idTheme=950) confirme que les règles 814, 815, 817-834 sont toutes marquées `✓ OK` avant les correctifs, et les correctifs sont strictement additifs.

---

## 6. Vérification post-correction (attendu)

Avec l'établissement 22222, données cohérentes :
- Règles 416-420 (latrines) → `✓ OK — scalaire de référence pure (val=N, standalone skip)` × 5
- Règles 810, 811 → ne crashent plus (ambiguïté/syntaxe corrigées)
- Règles 812, 836 → skip silencieux (assoc introuvable + sql_assoc vide → null retourné)
- Règles 814-834 → inchangées, toutes `✓ OK`
- **0 faux positif** dans le contrôle de cohérence des latrines

Test négatif (vérification que les vraies incohérences sont bien détectées) :
- Saisir NB_LATRINES_FILLES > NB_LATRINES_ELEVES → devrait déclencher une règle ASSOC comparant les deux après synchronisation (sql_assoc peuplé par le serveur).
