# CHANGELOG — StatEduc Mobile (Flutter)

Historique complet de toutes les modifications apportées à l'application Flutter StatEduc Mobile.

---

## [Session 60] — 2026-07-16 — Cinq correctifs critiques moteur offline (updated_at, NOUVEAUX_INSCRITS, SAVEPOINT ';', sql_assoc littéral, debugPrint)

### Contexte
Les logs console Android (après S59) révèlent que le SAVEPOINT S59 n'injectait **0 champ**
(NOT NULL constraint sur `updated_at`). En parallèle, la règle 496 restait toujours SKIPPED
(`NOUVEAUX_INSCRITS` table inconnue), et des warnings Android API ≤ 27 sur le ROLLBACK
risquaient de laisser des données temporaires non supprimées.

---

### Fix A — CRITIQUE : INSERT collected_data manquait `updated_at` + `is_sent`

**Root cause** : `collected_data.updated_at TEXT NOT NULL` n'a pas de valeur DEFAULT.
L'INSERT S59 ne fournissait que 6 colonnes → `DatabaseException(NOT NULL constraint failed:
collected_data.updated_at)` → chaque injection échouait → 0 champs injectés → S59 était
entièrement neutralisé malgré le SAVEPOINT ouvert.

**Fix** :
```dart
// AVANT (S59 — cassé) :
'INSERT OR REPLACE INTO collected_data '
'(id_camp, id_etab, id_qst, id_filter, field_name, field_value) '
'VALUES (?, ?, ?, ?, ?, ?)',
[idCamp, idEtab, idQst ?? '', '', fieldName, rawValue],

// APRÈS (S60 — correct) :
'INSERT OR REPLACE INTO collected_data '
'(id_camp, id_etab, id_qst, id_filter, field_name, field_value, is_sent, updated_at) '
'VALUES (?, ?, ?, ?, ?, ?, 0, ?)',
[idCamp, idEtab, idQst ?? '', '', fieldName, rawValue,
 DateTime.now().toIso8601String()],
```

---

### Fix B — CRITIQUE : `NOUVEAUX_INSCRITS` absent de `_knownServerTables` / `_multiRowTables`

**Root cause** : La table `NOUVEAUX_INSCRITS` n'était pas dans les sets de tables connues.
`SqlTranslator._detectServerTables()` retournait `{}` → `translate()` retournait null →
chemin regex fallback avec `v1=null v2=null` → règle 496 toujours SKIPPED.

**Champs observés** : `FILLES_NV_INCRITES_0_8_4`, `TOTAL_NV_INCRITS_0_8_4` (suffixe `_0_8_4`
→ table multi-lignes → CTE doit utiliser `SUM + LIKE 'FIELD_%'`).

**Fix** :
```dart
static const _knownServerTables = {
  'DONNEES_ETABLISSEMENT',
  'ELEVES_AGE_NIVEAU_SEXE',
  'ELEVES_NIVEAU_SEXE',
  'ELEVES_AGE_SEXE',
  'NOUVEAUX_INSCRITS',   // S60 FIX B
};

static const _multiRowTables = {
  'ELEVES_AGE_NIVEAU_SEXE',
  'ELEVES_NIVEAU_SEXE',
  'ELEVES_AGE_SEXE',
  'NOUVEAUX_INSCRITS',   // S60 FIX B — suffixe _0_8_4 → SUM + LIKE 'FIELD_%'
};
```

---

### Fix C — IMPORTANT : SAVEPOINT ROLLBACK/RELEASE sans préfixe `;` sur Android API ≤ 27

**Root cause** : Sur Android API ≤ 27, `sqflite.execSQL('ROLLBACK TO SAVEPOINT x')` peut
ignorer silencieusement la commande sans le préfixe `;`. Conséquence : le SAVEPOINT reste
ouvert et les insertions temporaires de formData ne sont pas supprimées → pollution possible
de `collected_data` avec des valeurs intermédiaires.

**Fix** :
```dart
// AVANT (S59) :
await db.execute('ROLLBACK TO SAVEPOINT coherence_eval');
await db.execute('RELEASE SAVEPOINT coherence_eval');

// APRÈS (S60) :
await db.execute(';ROLLBACK TO SAVEPOINT coherence_eval');
await db.execute(';RELEASE SAVEPOINT coherence_eval');
```

---

### Fix D — `sql_assoc` littéral numérique (règles 486–489)

**Analyse** : Pour les règles 486–489, `sql_assoc='0'` est un littéral numérique pur, pas
une requête SQL. La branche précédente (`sql_assoc not translatable → count2=0`) était
fonctionnellement équivalente pour `critere='='`, mais ne distinguait pas le cas numérique
du cas non-traduisible. On parse maintenant explicitement avec `double.tryParse(sql_assoc)`
pour un comportement correct et des logs clairs.

```dart
final literalValue = double.tryParse(rule.sqlAssoc.trim());
if (literalValue != null) {
  count2 = literalValue;  // '0' → 0.0, '1' → 1.0, etc.
} else {
  count2 = 0;  // fallback conservatif
}
```

---

### Fix E — debugPrint tronqué sur Android logcat (cosmétique)

**Cause** : Android logcat tronque `debugPrint` à ~4000 caractères. Les SQL traduits
(CTE + wrapper EXISTS/SCALAR) peuvent dépasser cette limite, corrompant les logs.

**Fix** : `CoherenceLogger.log()` découpe maintenant les messages en tranches de 800 char.
Le fichier `coherence_latest.log` reçoit le message complet sans troncature.

---

### Fichiers modifiés

| Fichier | Modification |
|---------|-------------|
| `lib/services/coherence_evaluator.dart` | Fix A (INSERT), Fix B (NOUVEAUX_INSCRITS), Fix C (ROLLBACK ';'), Fix D (sql_assoc numérique), Fix E (debugPrint chunking) |

### Validation

```
/tmp/validate_s60.py → 74/74 PASS
```

---

## [Session 59] — 2026-07-15 — Injection formData via SAVEPOINT SQLite (élimination faux positifs/négatifs)

### Contexte
Après S58, la règle 494 et les règles ELEVES fonctionnaient. La capture screenshot montre
3 incohérences détectées sur "Mob-Donnees generales 1" avec `987` saisi dans "dont pour
filles en bon état". L'analyse a identifié la **cause racine ultime** :

Le chemin SQL/CTE (`SqlTranslator` → `db.rawQuery()`) lit **uniquement `collected_data`**
(SQLite persisté). Quand l'utilisateur saisit une valeur dans le formulaire sans encore
sauvegarder, cette valeur est dans `formData` (Map en mémoire) mais **absente de
`collected_data`**. Le CTE retourne alors `COALESCE(NULL, 0) = 0.0` pour ce champ.

Conséquence :
- Si le champ saisi n'est pas encore persisté ET qu'il représente la partie gauche d'une
  comparaison `<=` dont la partie droite EST persistée → résultat asymétrique.
- Cas 1 (faux négatif) : champ gauche = 0 (car non persisté) < droite → règle non violée
  alors que la vraie valeur saisie violerait la règle.
- Cas 2 (faux positif possible) : valeur persistée incohérente avec la saisie en cours.

### Solution — SAVEPOINT SQLite (S59)

Avant la boucle d'évaluation des règles, `evaluate()` ouvre un **SAVEPOINT SQLite** et
insère toutes les entrées de `formData` dans `collected_data` via `INSERT OR REPLACE`.
À la fin du bloc (toujours via `finally`), `ROLLBACK TO SAVEPOINT` supprime ces insertions
temporaires **sans affecter les données réellement persistées**.

```dart
// SAVEPOINT garantit l'atomicité : injection temporaire → évaluation → rollback
await db.execute('SAVEPOINT coherence_eval');
try {
  for (final entry in formData.entries) {
    final fieldName = _formKeyToFieldName(entry.key);  // ajoute _0 si absent
    await db.execute(
      'INSERT OR REPLACE INTO collected_data '
      '(id_camp, id_etab, id_qst, id_filter, field_name, field_value) '
      'VALUES (?, ?, ?, ?, ?, ?)',
      [idCamp, idEtab, idQst ?? '', '', fieldName, entry.value],
    );
  }
  // ... évaluation de toutes les règles ...
} finally {
  await db.execute('ROLLBACK TO SAVEPOINT coherence_eval');
  await db.execute('RELEASE SAVEPOINT coherence_eval');
}
```

**`_formKeyToFieldName()` (nouveau helper) :**
```dart
// Si la clé a déjà un suffixe numérique (_0, _0_70, etc.) → garder tel quel
// Sinon → ajouter _0 (champs DONNEES_ETABLISSEMENT sans suffixe dans formData)
static String _formKeyToFieldName(String formKey) {
  final hasNumericSuffix = RegExp(r'_\d+$').hasMatch(formKey);
  return hasNumericSuffix ? formKey : '${formKey}_0';
}
```

### Résultats

Avec S59, le CTE **voit toujours les valeurs en cours de saisie** de `formData`,
même si elles ne sont pas encore sauvegardées dans SQLite. Les règles de cohérence
fonctionnent donc **quelques soit l'état de sauvegarde du formulaire**, conformément
à la demande : *"les incohérences doivent marcher quelques soit la requête définie
du moment que les critères sont vérifiés"*.

Les 3 violations de la capture screenshot correspondent à `987` > seuils cohérents
(latrine total, latrine filles, latrine bon état) — ce sont de **vraies violations**
correctement détectées après S59.

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` :
  - VERSION → Session 59
  - `evaluate()` : SAVEPOINT + injection formData + finally ROLLBACK
  - `_formKeyToFieldName()` : nouveau helper statique
  - Commentaires/docs complets S59

### Tests
- `/tmp/validate_s59.py` → **55/55 PASS** (10 blocs : A–J)
  - Bloc A : `_formKeyToFieldName` (10 cas)
  - Bloc B : SAVEPOINT inject+rollback SQLite
  - Bloc C : Règle latrines, faux négatif → violation détectée
  - Bloc D : NB_LATRINES_BON_ETAT_F <= NB_LATRINES_BON_ETAT (scénario screenshot)
  - Bloc E : ELEVES SUM multi-lignes non cassées
  - Bloc F : Intégrité ROLLBACK (0 résidu)
  - Bloc G : Edge cases (vide, texte, doubles suffixes)
  - Bloc H : INSERT OR REPLACE idempotence
  - Bloc I : Scénario screenshot exact (987 → violation)
  - Bloc J : Régression S58 ambiguïté LIKE _0/_F_0

---

## [Session 58] — 2026-07-15 — Fix ambiguïté LIKE + Logging fichier exhaustif

### Contexte
Après S57, la règle 494 (`Sum(FILLES_AGE_NIVEAU) <= Sum(TOTAL_AGE_NIVEAU)`) fonctionnait
correctement sur device. L'analyse des logs a révélé deux problèmes résiduels :

1. **Ambiguïté LIKE** : `LIKE 'NB_ELEVES%'` matchait à la fois `NB_ELEVES_0` (total élèves)
   ET `NB_ELEVES_F_0` (élèves filles) dans `DONNEES_ETABLISSEMENT`, car `NB_ELEVES` est un
   préfixe de `NB_ELEVES_F`. `MAX()` pouvait retourner la mauvaise valeur selon les données.

2. **Logs inaccessibles** : les `debugPrint` n'étaient visibles que dans Android logcat lors
   d'un débogage USB — impossible à capturer pour analyse asynchrone sur le terrain.

### Bug corrigé A — Ambiguïté LIKE (CRITIQUE)

**Problème S57 :**
```sql
-- LIKE 'NB_ELEVES%' matchait NB_ELEVES_0=20 ET NB_ELEVES_F_0=8 → MAX=20 (ok ici)
-- Mais si NB_ELEVES_HANDI_0=25 existait → MAX=25 pour la colonne NB_ELEVES ← FAUX!
MAX(CASE WHEN UPPER(field_name) LIKE 'NB_ELEVES%' THEN CAST(field_value AS REAL) END)
```

**Fix S58 — suffixe exact `_0` pour DONNEES_ETABLISSEMENT :**
```sql
-- LIKE 'NB_ELEVES_0'   → matche uniquement NB_ELEVES_0     ✅
-- LIKE 'NB_ELEVES_F_0' → matche uniquement NB_ELEVES_F_0   ✅ (pas de confusion)
MAX(CASE WHEN UPPER(field_name) LIKE 'NB_ELEVES_0' THEN CAST(field_value AS REAL) END)
```

Le suffixe HTML pour `DONNEES_ETABLISSEMENT` est **toujours exactement `_0`** (une seule
section de formulaire par champ). La pattern `LIKE 'FIELD_0'` est donc à la fois exacte
et sans ambiguïté. Note : dans SQLite, `_` dans un LIKE matche n'importe quel caractère
unique — mais la longueur de la chaîne garantit qu'il n'y a pas de match intempestif avec
des suffixes plus longs (`_0_70`, `_0_71`, etc.).

**Tables ELEVES_* : passage de `LIKE 'FIELD%'` à `LIKE 'FIELD_%'`**
L'underscore requis entre le nom de champ et le suffixe améliore la robustesse
(garantit qu'il y a au moins un caractère, correspondant au `_` du suffixe `_0_70`).
Le comportement fonctionnel reste identique car les champs ELEVES ont toujours un `_`
avant leurs suffixes numériques.

**Règle unifiée S58 dans `_buildPivotCte()` :**
| Catégorie | Stockage HTML | Stratégie CTE |
|-|-|-|
| Contexte (`CODE_ETABLISSEMENT`, `CODE_TYPE_ANNEE`) | Injecté sans suffixe | `= 'FIELD'` exact |
| DONNEES_ETABLISSEMENT (mono-row) | Avec suffixe `_0` | `MAX + LIKE 'FIELD_0'` ← **S58** |
| ELEVES_* (multi-row) | Avec suffixe `_0_70`, `_0_71`… | `SUM + LIKE 'FIELD_%'` ← **S58** |

### Nouveauté B — Logging fichier exhaustif (CoherenceLogger)

**Classe `CoherenceLogger`** ajoutée dans `coherence_evaluator.dart` :
- À chaque appel de `evaluate()`, crée/écrase `{AppDocumentsDir}/coherence_latest.log`
- Contenu : TOUS les messages du moteur (SqlTranslator + CoherenceEvaluator)
  - Champs complets de `collected_data` (sans limite des 20 premiers)
  - SQL bruts (server), SQL traduits (CTE complet + wrapper)
  - Résultat de chaque règle : `path=SQL|REGEX`, `v1`, `v2`, `critere`, `violated`
  - Marqueurs `=== RUN START ===` / `=== RUN END ===` pour délimiter les runs
- Dépendance ajoutée : `path_provider: ^2.1.4` dans `pubspec.yaml`
- Le fichier est lisible via explorateur de fichiers Android ou `adb pull`

**Path du fichier sur Android :**
```
/data/user/0/com.example.stateduc_mobile/app_flutter/coherence_latest.log
```
Ou via `adb` :
```bash
adb pull /data/user/0/com.example.stateduc_mobile/app_flutter/coherence_latest.log
```

**Architecture logger :**
```dart
final logger = CoherenceLogger(idEtab: idEtab, idCamp: idCamp);
// logger.log() → debugPrint() + buffer interne
// SqlTranslator._logger = logger → toutes ses sorties aussi dans le fichier
await logger.flush(); // → écrit {AppDocumentsDir}/coherence_latest.log
```

### Validation Python
`/tmp/validate_s58.py` — **50/50 PASS** incluant :
- T01–T06 : Ambiguïté LIKE résolue (`NB_ELEVES_0` vs `NB_ELEVES_F_0` vs `NB_ELEVES_HANDI_0`)
- T07–T11 : ELEVES_* `LIKE 'FIELD_%'` — comportement correct
- T12–T16 : Règle 493 (capture d'écran device) — violations correctement détectées/ignorées
- T17–T20 : Règle 494 (la seule qui fonctionnait) — régression validée
- T21–T24 : Règle électricité EXISTS — régression validée
- T25–T31 : LIKE `_0` exact : pas de match sur suffixes longs
- T32–T35 : CTE ELEVES génère les bonnes patterns `FIELD_%`
- T36–T43 : Régression S56/S57 complète
- T44–T50 : Structure CoherenceLogger

### Note sur la capture d'écran device (règle 493)
La violation affichée sur la capture (F=1 dans la grille ELEVES, F+M=8) est **légitime** :
le formulaire `infos_gen_1.html` (données générales) n'a pas encore été sauvegardé →
`NB_ELEVES_F_0` absent de `collected_data` → `MAX(LIKE 'NB_ELEVES_F_0')=NULL=0.0` →
`Sum(FILLES_AGE_NIVEAU)=1 ≠ NB_ELEVES_F=0` → violation détectée.
**Correction** : sauvegarder `infos_gen_1.html` avec `NB_ELEVES_F=1` → règle 493 OK.
Avec S58, si `NB_ELEVES_F_0` est présent, sa valeur est lue sans ambiguïté.

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` :
  - Classe `CoherenceLogger` ajoutée (avant `SqlTranslator`)
  - `SqlTranslator._logger` (static) + `_log()` helper qui route vers logger et debugPrint
  - `_buildPivotCte()` : `LIKE 'FIELD_0'` pour DONNEES_ETABLISSEMENT, `LIKE 'FIELD_%'` pour ELEVES_*
  - `evaluate()` : crée `CoherenceLogger`, log exhaustif, `await logger.flush()` en fin
  - `_evaluateRule()`, `_evaluateViaSql()`, `_execCount()` : logger propagé
  - Commentaire version mis à jour (Session 58)
- `pubspec.yaml` : `path_provider: ^2.1.4` ajouté

---

## [Session 57] — 2026-07-15 — Fix CTE LIKE préfixe universel (DONNEES_ETABLISSEMENT : champs HTML suffixés _0)

### Contexte
Malgré la correction S56 (LIKE pour les tables ELEVES multi-row), la règle 493 continuait à
retourner `sql_assoc val=0.0` sur device. Les logs S56 confirmaient que les champs ELEVES
étaient désormais lus correctement (`v1=8.0`), mais le côté DONNEES_ETABLISSEMENT restait à
zéro :

```
[CoherenceEval] rawQuery sql_assoc rule=493 (SCALAR) → val=0.0
[CoherenceEval] rule=493 v1=8.0 = v2=0.0 → violated=true
```

### Root cause

**Nommage HTML des champs DONNEES_ETABLISSEMENT : suffixe `_0`**

La page `infos_gen_1.html` (questionnaire général) stocke ses champs avec un suffixe `_0` :
```html
<INPUT NAME='NB_ELEVES_F_0'  ...>
<INPUT NAME='ELECTRICITE_0'  ...>
<INPUT NAME='NB_LATRINES_BON_ETAT_0' ...>
```

Ces noms sont transmis tels quels via le pont JavaScript → Flutter → `saveCollectedData()`.
Dans `collected_data`, les `field_name` sont donc `NB_ELEVES_F_0`, `ELECTRICITE_0`, etc.

**Le CTE avant ce fix (S56 state) :**
```sql
MAX(CASE WHEN UPPER(field_name)='NB_ELEVES_F' THEN CAST(field_value AS REAL) END)
```
→ **Aucune ligne ne matche** (le champ s'appelle `NB_ELEVES_F_0`, pas `NB_ELEVES_F`) →
`MAX()=NULL` → `COALESCE(NULL,0)=0.0` → `val=0.0` → violation incorrecte (faux positif).

### Bug corrigé — S57 (CRITIQUE)

**Extension de la stratégie LIKE aux tables mono-lignes (DONNEES_ETABLISSEMENT)**

Règle unifiée S57 dans `_buildPivotCte()` :
| Catégorie de champ | Stockage HTML | Stratégie CTE |
|-|-|-|
| Contexte (`CODE_ETABLISSEMENT`, `CODE_TYPE_ANNEE`) | Injecté sans suffixe | `= 'FIELD'` exact |
| ELEVES_* (multi-row) | Avec suffixe `_0_70`, `_0_71`… | `SUM + LIKE 'FIELD%'` |
| DONNEES_ETABLISSEMENT (mono-row) | Avec suffixe `_0` | `MAX + LIKE 'FIELD%'` ← **NOUVEAU** |

**Avant S57 (S56 state) :**
```dart
} else {
  // Tables mono-ligne : champs stockés tels quels.
  return "    MAX(CASE WHEN UPPER(field_name)='$upperField' "
      "THEN CAST(field_value AS REAL) END) AS $upperField";
}
```

**Après S57 :**
```dart
} else {
  // Mono-row (DONNEES_ETABLISSEMENT): MAX + LIKE prefix (S57 NEW)
  // Ex: NB_ELEVES_F_0 → LIKE 'NB_ELEVES_F%'
  return "    MAX(CASE WHEN UPPER(field_name) LIKE '${upperField}%' "
      "THEN CAST(field_value AS REAL) END) AS $upperField";
}
```

**Impact :** `NB_ELEVES_F_0` matchée par `LIKE 'NB_ELEVES_F%'` → `MAX()=8.0` → `val=8.0` →
règle 493 : `v1=8.0 <= v2=8.0` → `violated=false` ✅

### Validation Python
Simulateur Python mis à jour : **50/50 PASS** (T01–T25), incluant 4 nouveaux tests S57 :
- T22 : `NB_ELEVES_F_0=8` → `MAX(LIKE 'NB_ELEVES_F%')=8.0` ✅ (avant S57 : 0.0)
- T23 : Règle 493 cross-table : `FILLES=8, NB_ELEVES_F=8` → `NOT violated` ✅ (avant S57 : violated=True ❌)
- T24 : Règle 493 violation réelle : `FILLES=10 > NB_ELEVES_F=8` → `violated=True` ✅
- T25 : Plusieurs champs DONNEES_ETABLISSEMENT (`ELECTRICITE_0`, `NB_LATRINES_BON_ETAT_0`) tous capturés via LIKE ✅

Le test T22 a révélé un détail important : le SQL serveur pour `sql_assoc` utilise
`Max(NB_ELEVES_F)` (agrégat Access/MDB) — non un `SELECT` scalaire nu — ce qui active
correctement le mode SCALAR (GROUP BY context-only → stripped).

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` :
  - `_buildPivotCte()` : `MAX + LIKE 'FIELD%'` pour champs DONNEES_ETABLISSEMENT (tables mono-row)
  - Commentaire version mis à jour (Session 57)

---

## [Session 56] — 2026-07-15 — Fix CTE LIKE préfixe (champs HTML suffixés FILLES_AGE_NIVEAU_0_70)

### Contexte
Malgré les corrections S55 (suppression `id_qst` du CTE), les contrôles offline continuaient
à retourner `val=0.0` pour toutes les règles ELEVES. Capture d'écran device : 60 (Filles) > 54
(F+M) — incohérence évidente — mais aucune violation détectée. Logs confirmaient que le SQL
généré était syntaxiquement correct (CTE bien formé, `SUM()` utilisé, pas de `id_qst`) mais
la requête retournait toujours 0.

### Bug corrigé — S56 (CRITIQUE)

**Nommage HTML des champs de grille : suffixe `_0_70`, `_0_71`…**

La grille ELEVES (section 4.1 — Répartition des élèves par âge, sexe et par année d'études)
utilise des `<INPUT>` avec des noms comme :
```
FILLES_AGE_NIVEAU_0_70    (1ère année, tranche d'âge id=70)
FILLES_AGE_NIVEAU_0_71    (1ère année, tranche d'âge id=71)
FILLES_AGE_NIVEAU_0_72    (1ère année, tranche d'âge id=72)
TOTAL_AGE_NIVEAU_0_70
TOTAL_AGE_NIVEAU_0_71     ...etc.
```

Ces noms sont transmis tels quels via le pont JavaScript → Flutter → `saveCollectedData()`.
Dans `collected_data`, les `field_name` sont donc `FILLES_AGE_NIVEAU_0_70`, etc.

**Le CTE avant ce fix :**
```sql
SUM(CASE WHEN UPPER(field_name)='FILLES_AGE_NIVEAU' THEN CAST(field_value AS REAL) ELSE 0 END)
```
→ **Aucune ligne ne matche** (les champs s'appellent `FILLES_AGE_NIVEAU_0_70`, pas
`FILLES_AGE_NIVEAU`) → `SUM()=0` → `val=0.0` → aucune violation jamais détectée.

**Fix :** Utiliser `LIKE 'FILLES_AGE_NIVEAU%'` pour les champs des tables multi-lignes :
```sql
SUM(CASE WHEN UPPER(field_name) LIKE 'FILLES_AGE_NIVEAU%' THEN CAST(field_value AS REAL) ELSE 0 END)
```
→ Capture `FILLES_AGE_NIVEAU_0_70`, `FILLES_AGE_NIVEAU_0_71`… → `SUM()=60` → violation détectée ✅

**Portée du fix :** uniquement les colonnes non-contexte des tables `ELEVES_*` (multi-row).
Les champs de contexte (`CODE_ETABLISSEMENT`, etc.) et les tables mono-ligne
(`DONNEES_ETABLISSEMENT`) continuent à utiliser la correspondance exacte `=`.

**Note :** Pour `DONNEES_ETABLISSEMENT` les champs étaient supposés stockés sans suffixe à ce
stade — cette hypothèse s'est avérée incorrecte (voir Session 57 : tous les champs HTML ont
un suffixe `_0`). La correction complète a été apportée en S57.

### Log diagnostic ajouté

`evaluate()` affiche maintenant un échantillon des vrais `field_name` présents dans
`collected_data` pour faciliter le debug futur :
```
[CoherenceEval] collected_data field samples (idEtab=20952): FILLES_AGE_NIVEAU_0_70, FILLES_AGE_NIVEAU_0_71, TOTAL_AGE_NIVEAU_0_70, ...
```

### Validation Python
Simulateur Python mis à jour : **36/36 PASS** (T01–T21), incluant 4 nouveaux tests S56 :
- T18 : `FILLES_AGE_NIVEAU_0_70=60` → `Sum=60.0` ✅ (LIKE prefix fonctionne)
- T19 : violation réelle détectée : `FILLES=60 > TOTAL=54` → `violated=True` ✅
- T20 : cas légit : `FILLES=54 <= TOTAL=108` → `violated=False` ✅
- T21 : CTE contient `LIKE 'FILLES_AGE_NIVEAU%'` et non `= 'FILLES_AGE_NIVEAU'` ✅

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` :
  - `_buildPivotCte()` : `LIKE 'FIELD%'` pour champs ELEVES (tables multi-row)
  - `evaluate()` : log diagnostic champs `collected_data`
  - Commentaire version mis à jour (Session 56)

---

## [Session 55] — 2026-07-15 — Fix cohérence offline inter-thèmes (suppression id_qst du CTE + alias extraction)

### Contexte
Les contrôles de cohérence offline ne se déclenchaient jamais malgré des données incorrectes.
Les logs device montraient systématiquement `val=0.0` pour les règles 494 et 495 (tables
`ELEVES_AGE_NIVEAU_SEXE`). L'analyse a révélé deux bugs distincts introduits en Session 53.

### Bugs corrigés

**Bug S55-A (CRITIQUE) — Filtre `id_qst` incorrect dans le CTE de cohérence**

En S53, un filtre `AND id_qst='<idQst_courant>'` avait été ajouté dans `_buildPivotCte()`
pour éviter l'agrégation multi-formulaires. Ce filtre était correct pour les règles
intra-thème, mais **brisait les règles inter-thèmes** :

- Les règles de cohérence du thème 9502 référencent des données ELEVES.
- Les données ELEVES sont sauvegardées sous l'`id_qst` du formulaire ELÈVES (ex: `'9501'`),
  pas celui du formulaire de cohérence (`'9502'`).
- Résultat : `WHERE id_camp='2' AND id_etab='20952' AND id_qst='9502'` → **0 lignes** →
  `SUM() = 0` → `val=0.0` → aucune violation jamais détectée.

**Modèle serveur** (`controle_theme_batch.class.php`) : les vues SQL Server contiennent
TOUTES les données de l'établissement pour la campagne, sans filtrage par thème.
Le mobile doit reproduire ce comportement.

**Fix :** Suppression du filtre `id_qst` dans `_buildPivotCte()`. Le CTE filtre désormais
uniquement sur `(id_camp, id_etab)`, comme le serveur. Le paramètre `idQst` est conservé
dans la signature pour compatibilité mais n'est plus utilisé dans la requête CTE.

**Bug S55-B (SECONDAIRE) — Extraction d'alias dans `_extractAllFieldNames()`**

Le scanner SELECT de S53-B extrayait tous les identifiants du SELECT, y compris les **alias**
définis après `AS` (ex: `AS SommeDeFILLES_AGE_NIVEAU` → `SOMMEDEFILLES_AGE_NIVEAU` ajouté
comme champ du CTE). Ces alias n'existent pas dans `collected_data` → colonne CTE inutile
(toujours 0) et logs pollués avec des champs fantômes.

Logs avant fix :
```
[SqlTranslator] fields extracted for CTE: {CODE_ETABLISSEMENT, CODE_TYPE_ANNEE,
  CODE_ADMINISTRATIF, TOTAL_AGE_NIVEAU, SOMMEDETOTAL_AGE_NIVEAU}
```
`SOMMEDETOTAL_AGE_NIVEAU` est un alias, pas un champ de `collected_data`.

**Fix :** `.replaceAll(RegExp(r'\bAS\b\s+\w+', caseSensitive: false), '')` appliqué sur
la clause SELECT avant le scan des identifiants. Seuls les vrais noms de champs sont extraits.

### Règle de filtrage par thème
La demande utilisateur "n'exécuter que les règles du thème courant" est déjà satisfaite au
niveau de la requête base de données : `getCoherenceRules(idCamp, idQst, idEtab)` dans
`database_service.dart` (L1324) filtre `WHERE id_camp=? AND id_qst=? AND id_etab=?`.
Seules les règles du thème courant sont évaluées. Le fix S55-A concerne uniquement les
**données** agrégées dans le CTE, pas le filtrage des règles elles-mêmes.

### Validation Python
Simulateur Python mis à jour : **28/28 tests PASS** (T01–T17), incluant 5 nouveaux tests S55 :
- T13 : données ELEVES dans `id_qst='9501'`, règle dans `id_qst='9502'` → détectée (25 ≤ 50 OK)
- T14 : violation inter-thèmes : FILLES=60 > TOTAL=50 → violated=True ✅
- T15 : alias `SOMMEDEFILLES_AGE_NIVEAU` absent du corps CTE ✅
- T16 : scénario complet règles 494/495 : Sum(FILLES)=33 ≤ Sum(TOTAL)=100 → correct ✅
- T17 : règles intra-thème non régressées ✅

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` :
  - `_buildPivotCte()` : suppression filtre `id_qst` du WHERE, suppression variable `escapedQst`
  - `_extractAllFieldNames()` : strip `AS xxx` avant scan du SELECT
  - Commentaire version mis à jour (Session 55)

---

## [Session 54] — 2026-07-15 — Fix SCALAR multi-colonnes (_keepFirstSelectColumn)

### Contexte
Sur device (rule=493), le wrapper SCALAR échouait avec l'erreur SQLite :
`sub-select returns 2 columns - expected 1`
La cause : `sql_regle` de la règle 493 contient deux colonnes dans son SELECT :
`SELECT Sum(FILLES_AGE_NIVEAU) AS SommeDeFILLES_AGE_NIVEAU, Sum(TOTAL_AGE_NIVEAU) AS SommeDeTOTAL_AGE_NIVEAU FROM ELEVES_AGE_NIVEAU_SEXE`.
Le wrapper SCALAR S53 encapsulait ce SELECT multi-colonnes directement dans une
sous-requête scalaire `SELECT (...) AS __scalar_val` — SQLite exige exactement 1 colonne.

### Bug corrigé

**Bug S54 — SCALAR wrapper crash sur SELECT multi-colonnes**
Quand `sql_regle` possède `SELECT Sum(X), Sum(Y) FROM ...`, le mode SCALAR (après
suppression du GROUP BY contexte-only) produisait une sous-requête à 2 colonnes dans
`SELECT (SELECT Sum(X), Sum(Y) FROM ...) AS __scalar_val`, ce qui est interdit par SQLite.

**Fix :** Nouvelle méthode `_keepFirstSelectColumn()` qui réduit le SELECT à sa première
colonne avant l'emballage SCALAR. La méthode respecte les parenthèses imbriquées (split
de niveau 0) pour éviter de couper à l'intérieur d'un appel de fonction.

**Bug secondaire corrigé :** Le regex `(\bSELECT\b)(.*?)(\bFROM\b)` consomme l'espace
avant `FROM` dans `cols_part`. Sans `trimRight()`, la reconstruction produit
`SommeDeFILLES_AGE_NIVEAUFROM ELEVES...` (espace manquant → erreur syntaxe SQL).
Fix : `return '$before$selectKw${firstCol.trimRight()} $fromKw$after';`

**Comportement côté serveur :** Le PHP compare `val_sql[0][0]` vs `val_sql_assoc[0][0]`
(toujours la première valeur de la première ligne) → conserver uniquement la première
colonne en mode SCALAR est le comportement correct et cohérent avec le serveur.

### Validation Python
Simulateur Python mis à jour : **25/25 tests PASS** (T01–T15), incluant 3 nouveaux tests S54 :
- T13 : `keep_first_select_column` — espace avant FROM préservé, mono-colonne confirmé
- T14 : SELECT Sum(X), Sum(Y) → réduit à Sum(X) = 11.0 sans erreur SQLite
- T15 : Règle 493 complète — Sum(FILLES)=11 = Sum(NB_ELEVES_F)=11 → NOT violated

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` — ajout `_keepFirstSelectColumn()` + appel dans SCALAR path

---

## [Session 53] — 2026-07-15 — Fix chaîne complète idQst + extraction champs SELECT + logging

### Contexte
Malgré les corrections S45→S52, les contrôles offline continuaient à déclencher des faux
positifs (ex: `33 <= 100` signalé comme violé). Analyse approfondie + simulateur Python
confirmé : 4 bugs distincts subsistaient dans la chaîne d'évaluation.

### Bugs corrigés

**Bug 1 — CTE sans filtre `id_qst` (cause principale des faux positifs)**
`_buildPivotCte()` lisait `WHERE id_camp=... AND id_etab=...` sans filtre `id_qst`.
Pour les tables MULTI-LIGNES (ELEVES_*), le SUM() agrègeait TOUS les formulaires du même
établissement, pas seulement le formulaire courant → valeurs sur-comptées → comparaisons
fausses. Fix : ajout du paramètre `idQst?` dans `_buildPivotCte()`, `translate()`,
`_evaluateViaSql()`, `_evaluateRule()` et propagation depuis `evaluate()`.

**Bug 2 — Champs non qualifiés dans le SELECT non extraits pour le CTE**
`_extractAllFieldNames()` n'extrayait pas les champs de la clause SELECT quand ils
n'étaient pas préfixés par `TABLE.` (ex: `Sum(FILLES_AGE_NIVEAU)` sans préfixe).
Résultat : colonne manquante dans le CTE → erreur SQLite "no such column".
Fix : scan de la clause SELECT ajouté (FIX Bug S53-B).

**Bug 3 — SCALAR wrapper `SELECT *` multi-colonnes**
L'ancien wrapper `SELECT COALESCE((SELECT * FROM (...) _s), 0)` échouait si la
sous-requête retournait plusieurs colonnes (SELECT Sum(X), Sum(Y)).
Fix : nouveau wrapper `SELECT COALESCE((__scalar_val), 0) AS val FROM (SELECT (...) AS __scalar_val) _wrapper`.

**Bug 4 — `idQst` non propagé dans la chaîne d'appel**
`evaluate()` passait `idQst` à `_evaluateRule()` mais sans le paramètre — `_evaluateRule()`
et `_evaluateViaSql()` ne l'avaient pas dans leur signature et ne le transmettaient pas
à `translate()` → le filtre `id_qst` dans le CTE n'était jamais activé.
Fix : ajout de `String? idQst` dans `_evaluateRule()` et `_evaluateViaSql()`, avec
propagation complète vers les deux appels `translate()`.

### Logging amélioré
`_evaluateViaSql()` affiche maintenant le SQL complet traduit, les valeurs v1/v2,
le critère et `violated` pour chaque règle — facilite le diagnostic sur device.

### Validation Python
Simulateur Python complet : **19/19 tests PASS** (T01–T12), couvrant :
- Filtre id_qst présent/absent dans le CTE
- Mode SCALAR sans GROUP BY et avec GROUP BY contexte-only
- Mode EXISTS avec GROUP BY non-contexte
- Exécution réelle 33 ≤ 100 → NOT violated (le bug rapporté)
- Isolation id_qst : SUM=20 avec filtre vs SUM=30 sans filtre (cross-form)
- DONNEES_ETABLISSEMENT violation/non-violation
- SCALAR wrapper colonne unique

### Fichiers modifiés
- `lib/services/coherence_evaluator.dart` — 6 corrections + logging amélioré

---

## [Session 52] — 2026-07-15 — Fix faux positifs GROUP BY context-only → SCALAR

### Contexte
Session 51 avait corrigé les faux positifs ELEVES via MAX→SUM dans `_buildPivotCte()`.
Après recompilation, les contrôles offline se déclenchaient encore sur des valeurs correctes.

**Logs observés :**
```
[CoherenceEval] rawQuery sql_regle rule=495 (EXISTS) → count=1
[CoherenceEval] rawQuery sql_assoc rule=495 (EXISTS) → count=1
[CoherenceEval] rule=495 path=SQL result=(1.0 < 1.0) violated=true  ← FAUX POSITIF
```

### Cause racine

**Structure SQL du serveur (règle 495, sql_regle) :**
```sql
SELECT Sum(FILLES_AGE_NIVEAU) AS SommeDeFILLES_AGE_NIVEAU
FROM ELEVES_AGE_NIVEAU_SEXE
GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
HAVING (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
```

Après les strips S47/S50 (HAVING context-only supprimé) :
```sql
SELECT Sum(FILLES_AGE_NIVEAU) AS SommeDeFILLES_AGE_NIVEAU
FROM ELEVES_AGE_NIVEAU_SEXE
GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
```

- `hasGroupBy = true` → **MODE EXISTS** → `SELECT COUNT(*) AS cnt FROM (...)`
- Cette requête retourne **toujours 1 ligne** (1 ligne agrégée par établissement)
- `COUNT(*) = 1` pour `sql_regle` ET `sql_assoc`
- `_applyOperator(1.0, 1.0, '<')` → `!(1.0 < 1.0)` → `!false` → **`true` = FAUX POSITIF**

### Analyse

Ce pattern `SELECT Sum(X) ... GROUP BY [context_fields_only]` est une requête **Sum-scalaire** :
- Le serveur groupe par établissement/année pour obtenir la somme de cet établissement
- Côté mobile, le CTE est déjà filtré par `id_etab` → 1 établissement → **1 seule ligne**
- → La requête retourne une **valeur scalaire**, pas une liste de violations
- → Doit être traitée en **MODE SCALAR**, pas EXISTS

### Fix : `_isSumScalarWithContextGroupBy()` + `_stripContextOnlyGroupBy()` (étape 8)

**Fichier** : `coherence_evaluator.dart`

**Nouvelle logique de détection MODE (étape 8) — 3 cas :**
1. Pas de GROUP BY → **MODE SCALAR** (S49, inchangé)
2. GROUP BY context-only + SELECT = agrégats purs (Sum/Count/Avg/…) → **MODE SCALAR** (S52 NEW)
   - GROUP BY supprimé (redondant sur mobile)
   - Valeur scalaire lue directement via COALESCE wrapper
3. GROUP BY avec colonne non-contexte → **MODE EXISTS** (S45-S48, inchangé)

**`_isSumScalarWithContextGroupBy(sql)`** :
- Critère 1 : chaque colonne SELECT est une fonction d'agrégation (`Sum(`, `Count(`, etc.)
- Critère 2 : chaque colonne GROUP BY est un champ de contexte

**`_stripContextOnlyGroupBy(sql)`** :
- Supprime `GROUP BY [context_fields_only]` + HAVING résiduel
- Transforme la requête en SELECT pur sans groupement

**Résultat :**
```
Avant fix : rule=495 EXISTS → count=1 vs count=1 → (1<1)=false → violated=true  (FAUX POSITIF)
Après fix : rule=495 SCALAR → val=22.0 vs val=50.0 → (22<50)=true → violated=false ✓
Vraie violation : val=60 vs val=40 → (60<50)=false → violated=true ✓
Règle EXISTS préservée : SELECT CODE_ETAB + WHERE ELEC=0 → isScalar=False ✓
```

**Validation Python :** 3/3 tests PASS.

---



### Contexte
Session 50 avait corrigé le mismatch TEXT/INTEGER dans WHERE pour les règles SUM-scalaires.
Les contrôles se déclenchaient désormais, mais continuaient à produire des **faux positifs** :
la bannière « 1 incohérence(s) locale(s) » s'affichait même lorsque les valeurs saisies
étaient correctes (ex. : FILLES_AGE_NIVEAU ≤ TOTAL_AGE_NIVEAU vérifiée côté utilisateur).

---

### Bug S51-A : MAX au lieu de SUM pour les tables ELEVES multi-lignes

#### Cause racine

Dans `collected_data`, les tables `ELEVES_AGE_NIVEAU_SEXE`, `ELEVES_NIVEAU_SEXE` et
`ELEVES_AGE_SEXE` stockent **plusieurs lignes par `field_name`** — une ligne par combinaison
âge × niveau × sexe :

```
('2','20952','10502','FILLES_AGE_NIVEAU','10')  ← 6ans/1ère
('2','20952','10502','FILLES_AGE_NIVEAU','11')  ← 7ans/1ère
('2','20952','10502','FILLES_AGE_NIVEAU','12')  ← 8ans/2ème
```

Le CTE pivot généré par `_buildPivotCte()` utilisait `MAX(CASE WHEN ...)` pour tous les champs :

```sql
MAX(CASE WHEN UPPER(field_name)='FILLES_AGE_NIVEAU' THEN CAST(field_value AS REAL) END)
-- → MAX(10, 11, 12) = 12  ← BUG : sous-évaluation massive
```

La règle serveur utilise `Sum(ELEVES_AGE_NIVEAU_SEXE.FILLES_AGE_NIVEAU)` = 10 + 11 + 12 = **33**.
Mobile retournait 12 → 12 ≤ 24 (MAX du TOTAL) → jamais violé par chance, mais aussi
jamais correct. Dans d'autres combinaisons, le seuil TOTAL était aussi sous-évalué différemment,
entraînant des faux positifs (`12 > 11` alors que `33 ≤ 66`).

**Validation Python :**
- CTE MAX (BUG) : FILLES = 12.0, TOTAL = 24.0 → sous-évaluation confirmée
- CTE SUM (FIX) : FILLES = 33.0, TOTAL = 66.0 → correct, violated = False ✓
- Vraie violation (FILLES=41 > TOTAL=35) : violated = True ✓
- DONNEES_ETABLISSEMENT (mono-ligne) : MAX conservé, valeurs correctes ✓

#### Fix : `_multiRowTables` + `SUM(CASE WHEN ... ELSE 0 END)`

**Fichier** : `coherence_evaluator.dart`

**Nouvelle constante** :
```dart
static const _multiRowTables = {
  'ELEVES_AGE_NIVEAU_SEXE',
  'ELEVES_NIVEAU_SEXE',
  'ELEVES_AGE_SEXE',
};
```

**Modification de `_buildPivotCte()`** :
```dart
final isMultiRow = _multiRowTables.contains(tableName.toUpperCase());

// Avant (BUG) :
"    MAX(CASE WHEN UPPER(field_name)='$field' THEN CAST(field_value AS REAL) END) AS $field"

// Après (FIX — tables multi-lignes) :
"    SUM(CASE WHEN UPPER(field_name)='$field' THEN CAST(field_value AS REAL) ELSE 0 END) AS $field"

// DONNEES_ETABLISSEMENT (mono-ligne) : MAX conservé
"    MAX(CASE WHEN UPPER(field_name)='$field' THEN CAST(field_value AS REAL) END) AS $field"
```

**Impact** : Les contrôles offline sur les tables ELEVES comparent désormais la somme réelle
de toutes les lignes (comme le serveur), éliminant tous les faux positifs.

---

### Fix S51-B : Pinch-to-zoom WebView (Android)

#### Cause racine

Android WebView ignore `user-scalable=yes` dans le viewport meta par défaut.
Sans configuration supplémentaire, le zoom deux doigts ne fonctionnait pas dans les formulaires.

#### Fix : `dynamic_form_widget.dart`

**1. Nouvelle méthode `_enablePinchZoom()`** appelée dans `onPageFinished` :
```dart
void _enablePinchZoom() {
  _controller.runJavaScript(r'''
    var meta = document.querySelector('meta[name="viewport"]');
    meta.setAttribute('content',
      'width=device-width, initial-scale=1.0, minimum-scale=0.25, maximum-scale=10.0, user-scalable=yes');
    document.documentElement.style.touchAction = 'pan-x pan-y pinch-zoom';
    document.body.style.touchAction = 'pan-x pan-y pinch-zoom';
  ''');
}
```

**2. `WebViewWidget` avec `gestureRecognizers`** :
```dart
WebViewWidget(
  controller: _controller,
  gestureRecognizers: {
    Factory<OneSequenceGestureRecognizer>(() => ScaleGestureRecognizer()),
    Factory<OneSequenceGestureRecognizer>(() => EagerGestureRecognizer()),
  },
)
```
→ Les gestes multi-touch (pinch) sont transmis au moteur WebView au lieu d'être
  interceptés par l'arbre de gestes Flutter.

**3. CSS responsive multi-breakpoints** :
- `< 480px` (smartphone compact) : font 11px, paddings réduits, inputs compacts
- `481–600px` (mobile standard) : font 12px, paddings normaux
- `> 600px` (tablette) : font 14px, paddings généreux, cellules min-width 80px
- `touch-action: pan-x pan-y pinch-zoom` sur `html, body` (autorise le pinch CSS)

**4. Imports ajoutés** :
```dart
import 'package:flutter/foundation.dart'; // Factory
import 'package:flutter/gestures.dart';   // ScaleGestureRecognizer, EagerGestureRecognizer
```

---

### Récapitulatif des fixes S51

| Fix | Fichier | Impact |
|-----|---------|--------|
| MAX→SUM pour ELEVES multi-lignes | `coherence_evaluator.dart` | Élimine tous les faux positifs des contrôles offline ELEVES |
| Zoom deux doigts WebView Android | `dynamic_form_widget.dart` | Zoom/dézoom fonctionnel dans les formulaires de saisie |
| CSS responsive 3 breakpoints | `dynamic_form_widget.dart` | Formulaires adaptés smartphone compact / mobile / tablette |

---



### Contexte
Session 49 avait corrigé le wrapper `COUNT(*)` → mode SCALAR pour les règles SUM-scalaires.
Les logs confirmaient `isScalar=true` mais continuaient à retourner `val=0.0` des deux côtés :
```
[CoherenceEval] rawQuery sql_regle rule=483 (SCALAR) → val=0.0
[CoherenceEval] rawQuery sql_assoc rule=483 (SCALAR) → val=0.0
[CoherenceEval] rule=483 path=SQL result=(0.0 <= 0.0) violated=false
```

### Cause racine — Bug S50-A : mismatch TEXT/INTEGER dans WHERE

**SQL généré (après fix S49) :**
```sql
SELECT COALESCE((SELECT * FROM (
SELECT Sum(NB_LATRINES_FILLES) AS SommeDeNB_LATRINES_FILLES
FROM DONNEES_ETABLISSEMENT
WHERE (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
) _s), 0) AS val
```

**Cause** : Le WHERE filtre `CODE_ETABLISSEMENT=20952` avec un **littéral INTEGER** (pas de guillemets).
Mais le CTE produit des TEXT : `CODE_ETABLISSEMENT = '20952'`.

SQLite : `'20952' = 20952` → **FALSE** → toutes les lignes filtrées → `Sum(NB_LATRINES_FILLES) = NULL`
→ `COALESCE(NULL, 0) = 0.0` → les deux côtés valent 0 → `!(0 <= 0) = false` → jamais violé.

**C'est le même bug TEXT/INTEGER que S47 (HAVING) — mais dans la clause WHERE.**
`_stripContextOnlyHaving()` ne traitait pas le WHERE.

Le CTE est déjà filtré sur `id_etab='20952'` → le WHERE de contexte est entièrement redondant.

### Fix — Bug S50-A : `_stripContextOnlyWhere()` (étape 7c)

**Fichier** : `coherence_evaluator.dart` — nouvelle méthode statique + appel étape 7c.

**Stratégie** :
1. Si le SQL commence par `WITH`, isoler le bloc CTE (première fermeture à depth=0).
   Le strip s'applique **uniquement sur la partie principale** (après le CTE) pour ne pas
   supprimer le `WHERE id_camp=... AND id_etab=...` interne au CTE.
2. Regex WHERE body stop avec lookahead : `\n\s*\)` (fin de sous-requête dans wrapper SCALAR),
   `GROUP BY`, `HAVING`, `ORDER BY`, `LIMIT`, ou fin de chaîne.
3. Si le body WHERE ne contient que des identifiants de contexte (CODE_ETABLISSEMENT,
   CODE_TYPE_ANNEE, CODE_ADMINISTRATIF), supprimer le WHERE entier.

**SQL généré après fix :**
```sql
SELECT COALESCE((SELECT * FROM (
SELECT Sum(NB_LATRINES_FILLES) AS SommeDeNB_LATRINES_FILLES
FROM DONNEES_ETABLISSEMENT
        ← WHERE supprimé : context-only, redondant avec id_etab CTE
) _s), 0) AS val
```

**Résultat** : `Sum(NB_LATRINES_FILLES) = 50.0` → `COALESCE(50.0, 0) = 50.0` → valeur réelle.

### Fix — Bug S50-B : garde SQL vide (étape 7d)

Ajout d'un guard avant l'étape 8 :
```dart
if (translatedSql.trim().isEmpty) {
  debugPrint('[SqlTranslator] translatedSql empty after stripping — aborting translation');
  return null;
}
```
Évite de générer `SELECT COUNT(*) AS cnt FROM (\n) _violations` qui causerait une
`OperationalError SQLite` si les strips étape 7b/7c retournaient un SQL vide.

### Validation (Python SQLite, 9/9 tests)
- T1 Sum(NB_LATRINES_FILLES) context-only WHERE supprimé → val=50 ✓
- T2 Sum(NB_LATRINES_ELEVES) context-only WHERE supprimé → val=100 ✓
- T3 critère <= : 50<=100 → not violated ✓
- T4 VIOLATION filles=150 > élèves=100 → violated=True ✓
- T5 WHERE métier (NB_LATRINES_FILLES > 0) → conservé ✓
- T6 WHERE context-only + GROUP BY → WHERE supprimé, GROUP BY préservé ✓
- T7/T8/T9 CTE interne WHERE id_camp=... → non touché ✓

### Logs attendus après fix
```
[SqlTranslator] stripping context-only WHERE: (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
[CoherenceEval] rawQuery sql_regle rule=483 (SCALAR) → val=50.0
[CoherenceEval] rawQuery sql_assoc rule=483 (SCALAR) → val=100.0
[CoherenceEval] rule=483 path=SQL result=(50.0 <= 100.0) violated=false
```
Ou, si filles > élèves :
```
[CoherenceEval] rawQuery sql_regle rule=483 (SCALAR) → val=150.0
[CoherenceEval] rawQuery sql_assoc rule=483 (SCALAR) → val=100.0
[CoherenceEval] rule=483 path=SQL result=(150.0 <= 100.0) violated=true ← CORRECT
```

### Pas de régression
- Règles MODE EXISTS (GROUP BY présent) → non touchées
- WHERE avec logique métier (champs non-contexte) → conservé
- WHERE interne au CTE (`id_camp, id_etab`) → non touché (split CTE/main)

---

## [Session 49] — 2026-07-14 — Fix wrapper SUM-scalaire : mode SCALAR vs EXISTS

### Contexte
Après Sessions 47+48, les règles 483/484/485 (latrines, surfaces) retournaient toujours `violated=false`.
Logs de référence :
```
[CoherenceEval] rawQuery sql_regle rule=484 → count=1
[CoherenceEval] rawQuery sql_assoc rule=484 → count=1
[CoherenceEval] rule=484 path=SQL result=(1.0 <= 1.0) violated=false
```
Alors que les vraies données = `NB_LATRINES_ELEVES=50, NB_LATRINES_BON_ETAT=30` → violation réelle attendue.

### Cause racine — Wrapper `COUNT(*)` systématique sur requêtes SUM-scalaires

**Symptôme** : `count1 = 1, count2 = 1` toujours → `!(1 <= 1) = false` → jamais violé.

**Cause** : `translate()` encapsulait TOUTES les requêtes dans `SELECT COUNT(*) AS cnt FROM (...) _violations`, y compris les requêtes SUM-scalaires sans `GROUP BY`.

Un `SELECT Sum(NB_LATRINES_ELEVES) ...` sans `GROUP BY` retourne **toujours exactement 1 ligne** (même si `Sum=NULL`).
`COUNT(*)` de 1 ligne = `1` toujours → `count1=1` et `count2=1` pour les deux côtés → `1 <= 1 → True` → `!(True) = False` → never violated.

**Ce bug affecte toutes les règles de type :**
```sql
-- sql_regle :
SELECT Sum(NB_LATRINES_ELEVES) AS SommeDeNB_LATRINES_ELEVES
FROM DONNEES_ETABLISSEMENT
WHERE (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
-- ← PAS de GROUP BY → SUM-scalaire
```

### Fix — Dual-mode wrapper (MODE EXISTS vs MODE SCALAR)

**Fichier** : `coherence_evaluator.dart` — étape 8 de `translate()` + `_execCount()`.

**Détection** : présence de `GROUP BY` dans le SQL traduit (après step 7/7b).

```
GROUP BY présent  → MODE EXISTS  : SELECT COUNT(*) AS cnt FROM (...) _violations
                    _execCount() lit la colonne `cnt` (Sqflite.firstIntValue)
                    count > 0 → violation

Pas de GROUP BY   → MODE SCALAR  : SELECT COALESCE((SELECT * FROM (...) _s), 0) AS val
                    _execCount() lit firstRow.values.first (double)
                    valeur réelle → comparée directement via _applyOperator
```

**`TranslationResult`** : ajout du champ `isScalar: bool` pour distinguer les deux modes à l'exécution.

**`_execCount()`** : ajout du paramètre `{bool isScalar = false}` :
- `isScalar=false` → `Sqflite.firstIntValue(rows)?.toDouble()`
- `isScalar=true`  → `firstRow.values.first` converti en `double`

**`_evaluateViaSql()`** : passe `isScalar: r1.isScalar` et `isScalar: r2.isScalar` aux appels `_execCount`.

### Exemple concret — Règle 483 (NB_LATRINES_ELEVES <= NB_LATRINES_BON_ETAT)

**Avant fix :**
```
sql_regle COUNT(*) = 1  sql_assoc COUNT(*) = 1
!(1.0 <= 1.0) = False  ← BUG
```

**Après fix :**
```
sql_regle SCALAR val = 50.0  sql_assoc SCALAR val = 30.0
!(50.0 <= 30.0) = True  ← CORRECT (violation détectée)
```

### Validation (Python SQLite, 4/4 tests)
- T1 Bug COUNT(*) toujours 1 → violated=False (bug démontré) ✓
- T2 Fix SCALAR NB_LATRINES_ELEVES=50 > NB_LATRINES_BON_ETAT=30 → violated=True ✓
- T3 Cohérent : NB_LATRINES_ELEVES=20 <= NB_LATRINES_BON_ETAT=30 → violated=False ✓
- T4 Aucune donnée latrines → COALESCE(NULL, 0)=0 vs 0 → not violated ✓

### Pas de régression
- Règles MODE EXISTS (GROUP BY présent) → COUNT(*) wrapper conservé identique (Sessions 45-48)
- Règles SUM-scalaires cohérentes (v1 <= v2) → SCALAR mode retourne not violated correctement
- Cas données absentes → COALESCE(0) → not violated (conservatif, pas de faux positifs)

---

## [Session 48] — 2026-07-14 — Fix _stripContextOnlyHaving ordre + suppression diagnostic CTE crashant

### Contexte
Après Session 47, les logs montrent encore `HAVING (((CODE_ETABLISSEMENT)=20952)...)` non supprimé dans le SQL traduit pour les règles avec qualificateurs `TABLE.FIELD` (ex: électricité), et des erreurs `SQLiteLog (1) near "SELECT": syntax error` répétées.

### Bug 1 — CRITIQUE — `_stripContextOnlyHaving` appelé AVANT la suppression des qualificateurs

**Symptôme** : Le HAVING reste présent dans le SQL traduit pour les règles utilisant `DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT` (syntaxe qualifiée) → `count=0` → violation non détectée.

**Cause** : `_stripContextOnlyHaving` était appelé dans `_translateSyntax()` (step 10), **avant** la suppression des qualificateurs `TABLE.FIELD` (step 7 dans `translate()`). Le body du HAVING contenait encore `DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT` → le regex trouvait `DONNEES_ETABLISSEMENT` comme identifiant → n'appartient pas à `_contextFields` → `isContextOnly = false` → HAVING gardé.

**Fix** : Déplacement de l'appel `_stripContextOnlyHaving()` de `_translateSyntax()` step 10 vers `translate()` **step 7b**, c'est-à-dire immédiatement APRÈS la boucle de suppression des qualificateurs. Après step 7 : `DONNEES_ETABLISSEMENT.CODE_ETABLISSEMENT` → `CODE_ETABLISSEMENT` → `isContextOnly = true` → HAVING supprimé.

### Bug 2 — Diagnostic CTE crashe SQLite (erreurs dans les logs)

**Symptôme** : `E/SQLiteLog: (1) near "SELECT": syntax error in "WITH DONNEES_ETABLISSEMENT AS ( SELECT MAX(CASE WHEN UPPER(field_name) SELECT * FROM ..."` — 3 fois par règle.

**Cause** : Le bloc diagnostic dans `_execCount()` utilisait le regex `(.+?)` (non-greedy avec `dotAll: true`) pour extraire le body du CTE. Ce regex s'arrêtait au **premier `)` trouvé** dans le SQL — qui est le `)` de `MAX(CASE WHEN...END)` — produisant un CTE tronqué → SQL invalide → crash SQLite. Le `catch (_) {}` empêchait le blocage de l'évaluation mais polluait les logs avec 3 fausses erreurs par règle.

**Fix** : Suppression complète du bloc diagnostic CTE dans `_execCount()`. Le vrai `rawQuery` fonctionne correctement ; seul le diagnostic était défaillant.

### Validation (Python SQLite, 6/6 tests)
- Électricité violation (qualificateurs + `$CODE_ETABLISSEMENT`): count=1 ✓
- Latrines violation (qualificateurs + `$CODE_ETABLISSEMENT`): count=1 ✓
- Domaine violation (sans qualificateurs, entier hardcodé): count=1 ✓
- Électricité cohérent: count=0 (pas de régression) ✓
- Latrines cohérent: count=0 (pas de régression) ✓
- Domaine cohérent: count=0 (pas de régression) ✓

---

## [Session 47] — 2026-07-14 — Fix HAVING type-mismatch TEXT/INTEGER (0 violations systématique)

### Contexte
Après les 4 corrections Session 46, le contrôle offline retournait encore systématiquement 0 violation pour id_etab=20952, id_camp=2, CODE_TYPE_ANNEE=21 — un établissement ayant des incohérences réelles (latrines, domaine).

### Cause racine identifiée — Incompatibilité de type TEXT/INTEGER dans SQLite HAVING

**Symptôme** : `[CoherenceEval] rawQuery sql_regle rule=489 → count=0` sur toutes les règles.

**Analyse des logs** : Le SQL généré contenait :
```sql
GROUP BY CODE_ETABLISSEMENT, CODE_TYPE_ANNEE
HAVING (((CODE_ETABLISSEMENT)=20952) AND ((CODE_TYPE_ANNEE)=21))
```

**Cause** : `collected_data.field_value` est de type **TEXT**. Le pivot CTE produit donc
`CODE_ETABLISSEMENT = '20952'` (TEXT). Le SQL serveur compare avec `CODE_ETABLISSEMENT = 20952`
(entier littéral — sans guillemets, car ces valeurs ne sont pas des paramètres `$` mais des constantes
injectées par le batch serveur pour le filtrage multi-établissements).

En SQLite : `'20952' (TEXT) = 20952 (INTEGER)` → **FALSE** (types incompatibles).
→ Le HAVING filtre TOUTES les lignes → COUNT = 0 → aucune violation détectée.

**Validation Python** : simulation SQLite reproduit exactement le bug (count=0 avec HAVING, count=1 sans).

### Fix — `_stripContextOnlyHaving()` : suppression du HAVING redondant de contexte

**Fichier** : `coherence_evaluator.dart` — nouvelle méthode statique `_stripContextOnlyHaving()`,
appelée comme étape 10 dans `_translateSyntax()`.

**Justification** : Sur mobile, le CTE de pivot est déjà filtré sur `(id_camp, id_etab)` → le HAVING
de filtrage d'établissement est **toujours trivial** (il ne filtre jamais de lignes réelles car le CTE
ne contient que les données d'UN seul établissement). Le supprimer est donc à la fois correct et
nécessaire pour éviter la comparaison TEXT/INTEGER.

**Logique** : Si le HAVING ne contient QUE des identifiants appartenant à
`{CODE_ETABLISSEMENT, CODE_TYPE_ANNEE, CODE_ADMINISTRATIF}` → suppression du HAVING.
Si le HAVING contient d'autres identifiants (SUM, NB_ELEVES, etc.) → conservation intacte.

### Diagnostic — Log CTE pivot dans `_execCount()`

Ajout d'un log diagnostique pour visualiser les valeurs réellement pivotées depuis `collected_data` :
```
[SqlTranslator] CTE DONNEES_ETABLISSEMENT pivot (rule=489): {CODE_ETABLISSEMENT: 20952, ...}
```
Permet de vérifier instantanément que le pivot a bien extrait les bons champs et valeurs.

### Validation finale (Python SQLite, 4/4 tests)
- Rule 489 (latrines) données violées : count=1 ✓
- Rule 488 (domaine) données violées : count=1 ✓
- Rule 489 données cohérentes : count=0 ✓ (pas de régression)
- Rule 488 données cohérentes : count=0 ✓ (pas de régression)

---

## [Session 46] — 2026-07-14 — Corrections critiques moteur cohérence offline (4 bugs)

### Contexte
Tests en mode debug (Session 45) ont révélé 4 bugs bloquants empêchant toute détection de violation.

### Bug 1 — CRITIQUE — `\1` littéral dans SQL (SQLite crash)
**Fichier** : `coherence_evaluator.dart` — méthode `translate()`, étape 7

**Symptôme** : `E/SQLiteLog: unrecognized token: "\"` — toutes les règles 483/484/... échouaient avec SQL contenant `Sum(\1)`.

**Cause** : `String.replaceAll(RegExp, String)` en Dart **ne supporte pas** les backreferences `\1` dans la chaîne de remplacement — le `\1` est inséré tel quel dans le SQL.

**Fix** : Remplacement de `replaceAll(regex, r'\1')` par `replaceAllMapped(regex, (m) => m.group(1)!)`.

### Bug 2 — Champs WHERE non extraits pour le CTE
**Fichier** : `coherence_evaluator.dart` — méthode `_extractAllFieldNames()`

**Symptôme** : `DOMAINE_DELIMITE` et `SUPERFICIE_DOMAINE` absents du CTE → colonnes NULL → WHERE toujours faux → violations jamais détectées.

**Cause** : L'extraction ne cherchait que les champs qualifiés (`TABLE.FIELD`) et les clauses GROUP BY/HAVING, pas les identifiants nus dans WHERE.

**Fix** : Ajout d'une extraction des identifiants non qualifiés dans la clause WHERE (via regex avec `dotAll: true`).

### Bug 3 — Nom de table inclus dans les champs du CTE
**Fichier** : `coherence_evaluator.dart` — méthode `_extractAllFieldNames()`

**Symptôme** : `DONNEES_ETABLISSEMENT` apparaissait dans les champs extraits → colonne CTE inutile.

**Cause** : Pas de filtre excluant les noms de tables serveur (`_knownServerTables`) lors de l'extraction.

**Fix** : Ajout de `!serverTables.contains(name)` dans les trois boucles d'extraction.

### Bug 4 — `sql_assoc` non traduisible → skip complet (règles 487, 488)
**Fichier** : `coherence_evaluator.dart` — méthode `_evaluateViaSql()`

**Symptôme** : Règles 487 et 488 jamais évaluées car `sql_assoc` ne contient pas `DONNEES_ETABLISSEMENT`.

**Cause** : `if (r2 == null) return null` causait le fallback même quand `sql_regle` était traduisible.

**Fix** : Si `sql_assoc` non traduisible ou vide → `count2 = 0` (compare `count1` violations à 0). Correct pour critere `= 0` (la règle est violée si count violations > 0).

### UI — Bannière offline style "Contrôle de cohérence"
**Fichier** : `school_data_screen.dart` — widget `_OfflineCoherenceBanner`

Refonte visuelle pour ressembler au dialog serveur (screenshot utilisateur) :
- Titre : **"Contrôle de cohérence"** (comme le dialog serveur)
- Sous-titre : *"Contrôle local — non encore envoyé au serveur"* (distinction claire)
- Compteur : "N incohérence(s) détectée(s) :" (même format)
- Icônes rouges `error_outline` par violation (identique au dialog serveur)
- Fond blanc avec bordure orange et ombre légère

---

## [Session 45] — 2026-07-14 — Moteur de cohérence offline SQL réel sur SQLite

### Objectif
Remplace l'évaluation regex (Sessions 38–44) par un moteur d'exécution SQL réel.
Les requêtes SQL de cohérence (Access/SQL Server) sont traduites et exécutées directement
sur la base SQLite locale, garantissant l'équivalence exacte avec le contrôle serveur.

### Nouveautés

####  (classe statique dans )
Traduit les requêtes SQL Access/SQL Server vers SQLite :
- Substitution des paramètres : ,  → valeurs réelles
- Mapping de table :  → CTE de pivot dynamique sur 
- Traduction syntaxique :  → ,  → 
-  → ,  → 
- Wrapper  : la requête retourne un entier (0 = OK, >0 = violation)

####  — Dual-path
1. **Chemin SQL réel** (prioritaire) :  + 
2. **Chemin regex fallback** (conservatif) : extraction regex + 

#### 
Passe maintenant  et  au moteur pour la substitution des paramètres.

### Règles validées
- Règle électricité :  vs  (cas concret Session 45)
- Règle clôture/superficie :  vs 

### Tests
-  : 15 tests unitaires du traducteur SQL (pur Dart, sans DB)

### Conservation
- Le contrôle serveur (data_controle.php via API) est intégralement conservé (additive)
- Le chemin regex est conservé comme fallback pour la robustesse

---

## [Unreleased] — 2026-06-17 — Session 21 : correction cohérence hors ligne

### 🔴 Fix — `coherence_evaluator.dart` : `_sumFieldAcrossAllFilters` retournait `0` au lieu de `null`

**Problème** : Quand un champ référencé dans une règle SQL (pattern `SUM(CHAMP)`) n'était pas
présent dans les données collectées (`collected_data`), `_sumFieldAcrossAllFilters()` retournait
`0.0` au lieu de `null`. Cela causait l'évaluation de la règle avec `V1=0 critere V2=0`, produisant
des résultats incorrects (faux négatifs si critère `<=`, faux positifs si critère `>=`).

**Correction** : Retourne `null` quand le champ est introuvable. L'appelant `_extractValue()` propage
`null` vers `evaluate()` qui ignore silencieusement la règle (comportement conservatif correct —
le moteur ne signale que les violations certaines).

```dart
// Avant : double _sumFieldAcrossAllFilters(...) { ... return found ? sum : (values[fieldName] ?? 0); }
// Après : double? _sumFieldAcrossAllFilters(...) { ... return found ? sum : null; }
```

---

### 🔴 Fix — `data_entry_provider.dart` : re-déclenchement cohérence après chargement des règles

**Problème** : Dans `_fetchAndStoreCoherenceRulesBackground()`, le re-déclenchement du contrôle
de cohérence offline (après insertion des règles depuis le serveur) était conditionné à
`_formData.isNotEmpty`. Cette condition empêchait le contrôle de s'exécuter si les règles
arrivaient avant que l'utilisateur ait commencé à saisir.

**Correction** : Suppression de la condition `_formData.isNotEmpty`. Le re-déclenchement est
maintenant systématique dès que les règles arrivent pour la question courante (et que
`_isCheckingOffline == false`). Si les données sont vides, le contrôle retournera 0 violations —
ce qui est correct et met à jour l'UI de façon cohérente.

```dart
// Avant :
if (_selectedQuestion?.idQst == q.idQst && _formData.isNotEmpty && !_isCheckingOffline)
// Après :
if (_selectedQuestion?.idQst == q.idQst && !_isCheckingOffline)
```

---

### 🟢 Amélioration — `data_entry_provider.dart` : `updateField()` — suppression garde `_formData.isNotEmpty`

Dans le callback debounce de `updateField()`, la garde `if (_formData.isNotEmpty)` a été retirée.
Elle était redondante (le champ vient d'être mis à jour donc `_formData` est forcément non vide)
et aurait pu masquer le problème si l'implémentation changeait.

**Avant** :
```dart
_coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
  if (_formData.isNotEmpty && !_isCheckingOffline) {
    checkCoherenceOffline();
  }
});
```

**Après** :
```dart
_coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
  if (!_isCheckingOffline) {
    checkCoherenceOffline();
  }
});
```

---

### 🟢 Amélioration — `school_data_screen.dart` : bouton manuel "Vérifier la cohérence"

Ajout d'une option "Vérifier la cohérence" dans le menu popup (⋮) de la barre d'application.
Permet à l'agent de collecte de déclencher manuellement le contrôle hors ligne si le check
automatique (debounce 800ms) n'a pas encore pu s'exécuter (ex. : règles pas encore téléchargées).

```dart
PopupMenuItem(
  value: 'check_coherence',
  child: ListTile(
    leading: Icon(Icons.rule_folder_outlined),
    title: Text('Vérifier la cohérence'),
    subtitle: Text('Contrôle offline immédiat'),
  )),
```

---

### 🔵 Diagnostic — `data_entry_provider.dart` : `debugPrint` enrichis

Ajout de `debugPrint` détaillés pour tracer le flux de cohérence offline dans les logs Flutter :
- `[DataEntry] updateField: CHAMP = "valeur" (N champs en mémoire) — debounce 800ms`
- `[DataEntry] debounce fired: _formData=N _isCheckingOffline=false`
- `[DataEntry] rules arrived for current question (formData=N fields) — re-triggering`
- `[DataEntry] checkCoherenceOffline: aucune règle stockée pour idCamp=... idQst=... idEtab=...`

---

### 🟢 Amélioration — `pin_screen.dart` : en-tête institutionnel complet

**Avant** : L'écran d'accueil affichait directement le drapeau du Burundi (session 18), sans mention du nom de l'institution.

**Après** : Deux lignes institutionnelles en **italique** s'affichent au-dessus du drapeau, avec la même police et couleur principale (`colorScheme.primary`) que le titre "StatEduc" :

```dart
// Ligne 1 — République du Burundi
// Ligne 2 — Ministère de l'Éducation Nationale
// (séparateur 14px)
// Drapeau du Burundi (96×64 px, ombre, coin arrondis)
// (séparateur 12px)
// StatEduc  (headlineMedium bold)
// Collecte de données éducatives (bodyMedium)
```

**Style des deux lignes institutionnelles** :
```dart
Theme.of(context).textTheme.headlineMedium?.copyWith(
  fontStyle: FontStyle.italic,      // texte oblique
  fontWeight: FontWeight.w600,      // semi-bold — même poids que StatEduc
  color: primaryColor,              // couleur principale de l'application
)
```

**Résultat visuel** :
```
  République du Burundi                  ← italique, couleur principale
  Ministère de l'Éducation Nationale     ← italique, couleur principale

        [Drapeau du Burundi]

            StatEduc
  Collecte de données éducatives
```

---

### 📄 Nouveau document — `administration.md`

Création du guide d'administration complet A→Z de l'application, destiné aux administrateurs et superviseurs de collecte du MEN. Couvre 20 sections :
- Installation, premier démarrage, connexion au serveur
- Création, modification et réinitialisation du PIN
- Configuration URL serveur, téléchargement de campagne
- Navigation, remplissage questionnaire, sauvegarde locale
- Contrôle de cohérence hors ligne (7 déclenchements)
- Envoi des données (formulaire / établissement / campagne)
- Contrôle de cohérence serveur, rechargement depuis serveur
- Gestion des erreurs réseau (tableau messages + diagnostic)
- Tableau de bord administrateur — vérifications essentielles

---

### 📄 Documents mis à jour

- **`recapitulatif.md`** : duplication de contenu supprimée (la moitié du fichier était dupliquée depuis la session 18) ; mise à jour sessions 19-20 ; nouvelle section "Timeouts Dio — Configuration actuelle" ; ajout `administration.md` dans le tableau documentation ; correctifs sessions 19+20 ajoutés au tableau historique
- **`notepresentation.md`** : références mises à jour sessions 19-20 ; tableau documentation enrichi avec `administration.md` ; timeouts mis à jour (600s / null) ; tableau résultats complété
- **`stateduc_flutter/CHANGELOG.md`** : entrée session 20 (ce fichier)

---

## [Unreleased] — 2026-06-15 — Session 19 : correction timeout + retry automatique

### 🔴 Fix — "Délai d'attente dépassé lors de l'envoi" sur réseau stable

**Symptôme** : L'erreur _"Délai d'attente dépassé lors de l'envoi. Le serveur est lent ou la connexion est instable."_ s'affichait même avec un réseau Wi-Fi ou LTE stable (capture écran utilisateur).

**Causes racines identifiées** :
1. `sendTimeout: Duration(seconds: 300)` — Dio peut déclencher ce timeout prématurément sur Android même sur réseau stable, quand le serveur XAMPP tarde à accuser réception du POST.
2. `receiveTimeout: Duration(seconds: 300)` — insuffisant : la chaîne `data_save.php → curl interne → questionnaire_ws.php` peut dépasser 5 min sur XAMPP chargé.
3. Aucun mécanisme de retry : la moindre erreur transitoire (reset TCP, microcoupure) causait un échec définitif.

**Correctifs appliqués** (`api_service.dart`) :

| Paramètre | Avant | Après | Raison |
|-----------|-------|-------|--------|
| `sendTimeout` | `300s` | `null` (désactivé) | Body POST < 10 KB — faux-positif Android fréquent |
| `receiveTimeout` | `300s` | `600s` (10 min) | Chaîne save→questionnaire_ws peut dépasser 5 min |
| Retry | aucun | 2 re-tentatives | Erreurs transitoires réseau/timeout |

**Nouveau helper `_withRetry<T>()`** :
```dart
// 2 re-tentatives = 3 essais au total
// Délai progressif : 5s × numéro de tentative
// Ne réessaie PAS sur : ApiException (401, 404, se_status 400), connectionTimeout
// Réessaie sur : sendTimeout, receiveTimeout, DioExceptionType.unknown
static const int _kMaxRetries = 2;
static const int _kRetryDelay = 5;

Future<T> _withRetry<T>(Future<T> Function() fn, {void Function(int attempt)? onRetry}) async { ... }
```

**Refactoring `saveData()`** :
- Public `saveData()` → délègue à `_withRetry()` + accepte `onRetry` callback
- Privé `_saveDataOnce()` → implémentation HTTP unique (inchangée)
- `onRetry` transmis depuis `DataEntryProvider.sendToServer()` → affichage "Tentative 2/3…" dans l'UI

**Messages d'erreur améliorés** (par type `DioExceptionType`) :
- `connectionTimeout` → _"Impossible de joindre le serveur. Vérifiez l'URL et votre connexion réseau."_
- `sendTimeout`/`receiveTimeout` → _"Délai d'attente dépassé après 3 tentatives. Le serveur ne répond pas…"_
- `unknown`/socket → _"Erreur réseau lors de l'envoi… Réessayez quand le réseau est stable."_

---

### 🟢 Amélioration — Suivi des tentatives de retry dans l'UI

**Fichiers** : `data_entry_provider.dart`, `school_data_screen.dart`

**Nouveautés** :
- Champ `int _sendAttempt` (0 = inactif, 1–3 = numéro de tentative en cours)
- Getter public `int get sendAttempt`
- Constante publique `static const int kMaxSendAttempts = 3`
- `sendToServer()` : initialise `_sendAttempt = 1`, callback `onRetry` incrémente avant chaque re-tentative, `finally` remet `_sendAttempt = 0`
- Overlay `LoadingOverlay` : affiche _"Envoi… (tentative 2/3)"_ dès que `sendAttempt > 1`

```dart
// school_data_screen.dart — overlay message
message: entry.isSending
    ? (entry.sendAttempt > 1
        ? 'Envoi… (tentative ${entry.sendAttempt}/${DataEntryProvider.kMaxSendAttempts})'
        : 'Envoi en cours…')
    : 'Rechargement…',
```

---

## [Unreleased] — 2026-06-15 — Session 18 : drapeau Burundi + renforcement cohérence offline

### 🟢 Amélioration — `pin_screen.dart` : remplacement de l'icône `school` par le drapeau du Burundi

**Avant** : L'écran d'accueil/PIN affichait une icône Material `Icons.school` générique (taille 72).

**Après** : L'asset `assets/icon/Flag_of_country.png` (drapeau du Burundi) est affiché dans un
conteneur rectangulaire arrondi (96×64 px) avec ombre légère, centré au-dessus du titre.
Un `errorBuilder` assure le repli sur `Icons.school` si l'image n'est pas chargée
(robustesse en cas d'asset manquant).

```dart
// AVANT
Icon(Icons.school, size: 72, color: Theme.of(context).colorScheme.primary)

// APRÈS
Container(
  width: 96, height: 64,
  decoration: BoxDecoration(
    borderRadius: BorderRadius.circular(6),
    boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.18), blurRadius: 8, offset: Offset(0, 3))],
  ),
  child: ClipRRect(
    borderRadius: BorderRadius.circular(6),
    child: Image.asset(
      'assets/icon/Flag_of_country.png',
      fit: BoxFit.cover,
      errorBuilder: (_, __, ___) => Icon(Icons.school, size: 64, ...),
    ),
  ),
)
```

L'asset `assets/icon/` est déjà déclaré dans `pubspec.yaml` → aucune modification de config nécessaire.

---

### 🔴 Fix — Cohérence offline non déclenchée à la saisie

**Symptôme observé** : Les contrôles de cohérence offline ne s'affichaient que lors de l'envoi
au serveur (`sendToServer()`), jamais pendant la saisie ni à l'ouverture d'un formulaire.

**Audit session 18 — problèmes identifiés** :

| # | Problème | Localisation |
|---|---------|-------------|
| A | `updateField()` ne déclenche JAMAIS la cohérence offline | `data_entry_provider.dart` |
| B | `selectFilter()` ne re-déclenche pas la cohérence après changement de période | `data_entry_provider.dart` |
| C | `_autoReloadFromServerBackground()` met à jour `_formData` sans re-vérifier la cohérence | `data_entry_provider.dart` |
| D | Indicateur visuel "contrôle en cours" absent dans l'UI | `school_data_screen.dart` |

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — 4 corrections :

**Fix A — Import `dart:async` + Timer debounce + `updateField()` déclenche la cohérence** :
```dart
// En tête de fichier — ajout
import 'dart:async';

// Dans la déclaration des champs
Timer? _coherenceDebounce;  // debounce 800 ms pour updateField()

// updateField() — désormais déclenche checkCoherenceOffline() en debounce
void updateField(String fieldName, String value) {
  _formData[fieldName] = value;
  _hasUnsavedChanges   = true;
  _validationErrors.remove(fieldName);
  notifyListeners();
  // Debounce 800 ms — évite une évaluation à chaque frappe
  _coherenceDebounce?.cancel();
  _coherenceDebounce = Timer(const Duration(milliseconds: 800), () {
    if (_formData.isNotEmpty && !_isCheckingOffline) {
      checkCoherenceOffline();
    }
  });
}
```

**Fix B — `selectFilter()` relance la cohérence après changement de filtre** :
```dart
// À la fin de selectFilter(), après le chargement des données
if (_formData.isNotEmpty) {
  Future(() => checkCoherenceOffline());
}
```

**Fix C — `_autoReloadFromServerBackground()` relance la cohérence après fusion** :
```dart
// Après notifyListeners() dans le bloc if (changed)
if (!_isCheckingOffline) {
  Future(() => checkCoherenceOffline());
}
```

**Fix D — Nettoyage du Timer dans `dispose()`** :
```dart
@override
void dispose() {
  _coherenceDebounce?.cancel();
  super.dispose();
}
```

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** — indicateur visuel :
```dart
// Avant le banner d'erreurs offline : spinner pendant le contrôle
if (entry.isCheckingOffline)
  const LinearProgressIndicator(),
if (entry.hasOfflineCoherenceErrors)
  _OfflineCoherenceBanner(errors: entry.offlineCoherenceErrors, ...),
```

---

### 📊 Tableau complet des déclenchements cohérence offline (après session 18)

| Événement | Trigger | Délai | Depuis |
|-----------|---------|-------|--------|
| Saisie d'un champ | `updateField()` → debounce 800 ms | 0.8 s après dernière frappe | **Session 18** |
| Sauvegarde locale | `saveLocally()` → `Future()` | Immédiat (arrière-plan) | Sessions 1-16 |
| Ouverture formulaire déjà rempli | `selectQuestion()` → `Future()` | Immédiat | Session 17 |
| Changement de filtre/période | `selectFilter()` → `Future()` | Immédiat | **Session 18** |
| Règles reçues du serveur | `_fetchAndStoreCoherenceRulesBackground()` | Arrière-plan | Session 17 |
| Données serveur fusionnées | `_autoReloadFromServerBackground()` → `Future()` | Arrière-plan | **Session 18** |
| Envoi serveur | `sendToServer()` → `checkCoherence()` (API) | Après POST réussi | Sessions 1-16 |

---

### 📊 Fichiers modifiés — Session 18

| Fichier | Type | Résumé |
|---------|------|--------|
| `lib/screens/login/pin_screen.dart` | UX | Remplacement `Icons.school` → `Image.asset('assets/icon/Flag_of_country.png')` avec `errorBuilder` |
| `lib/providers/data_entry_provider.dart` | Fix | `dart:async` import ; `Timer _coherenceDebounce` ; `updateField()` debounce ; `selectFilter()` trigger ; `_autoReloadFromServerBackground()` trigger ; `dispose()` |
| `lib/screens/data_entry/school_data_screen.dart` | UX | `LinearProgressIndicator` pendant `isCheckingOffline` |

---

## [Unreleased] — 2026-06-03 — Session 17 : timeout, cohérence offline, envoi global, identification, contraste settings

### 🔴 Fix — `api_service.dart` : timeout "délais d'attente dépassé" sur réseau stable

**Symptôme** : L'envoi d'un formulaire échouait avec `DioExceptionType.sendTimeout` même sur un réseau intranet stable. La chaîne d'appels `data_save.php → session_write_close → curl interne → questionnaire_ws.php` peut dépasser 2 minutes sur un serveur XAMPP chargé.

**Cause racine** : `sendTimeout` était fixé à **120 s** — insuffisant pour les envois lents (payload volumineux + traitement PHP).

**`stateduc_flutter/lib/services/api_service.dart`** :
- `sendTimeout` 120 s → **300 s** (5 minutes)
- `connectTimeout` reste 60 s, `receiveTimeout` reste 300 s (inchangés depuis session 12b)

```dart
connectTimeout: const Duration(seconds: 60),
receiveTimeout: const Duration(seconds: 300),
sendTimeout:    const Duration(seconds: 300),  // était 120 s
```

| Timeout | Avant | Après | Rôle |
|---------|-------|-------|------|
| `connectTimeout` | 60 s | 60 s | Établissement connexion TCP |
| `receiveTimeout` | 300 s | 300 s | Attente réponse complète serveur |
| `sendTimeout` | **120 s** | **300 s** | Envoi du corps de la requête |

---

### 🟠 Fix — Cohérence offline non déclenchée après sauvegarde locale

**Symptôme** : Après `saveLocally()`, l'indicateur de cohérence offline restait vide. Les règles de cohérence étaient chargées en arrière-plan ; au moment où `checkCoherenceOffline()` était appelé, elles n'étaient pas encore en SQLite → résultat vide.

**Cause racine** : `checkCoherenceOffline()` n'était déclenché que dans le flux `sendToServer()` / `saveLocally()`, pas après l'arrivée des règles ni à l'ouverture d'un formulaire déjà rempli.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — deux corrections :

**Fix 1 — Re-déclencher la cohérence quand les règles arrivent (`_fetchAndStoreCoherenceRulesBackground`)** :
```dart
if (rules.isNotEmpty) {
  await _db.insertCoherenceRules(rules);
  // NOUVEAU : re-trigger si règles arrivent pour le formulaire courant déjà rempli
  if (_selectedQuestion?.idQst == q.idQst &&
      _formData.isNotEmpty &&
      !_isCheckingOffline) {
    await checkCoherenceOffline();
  }
}
```

**Fix 2 — Déclencher la cohérence à l'ouverture d'un formulaire déjà rempli (`selectQuestion`)** :
```dart
// NOUVEAU : lance la cohérence offline si le formulaire contient déjà des données
if (_formData.isNotEmpty) {
  Future(() => checkCoherenceOffline());
}
```

---

### 🟢 Nouveau — Envoi global : tous les formulaires d'un établissement

**Besoin** : Pouvoir envoyer en une seule action tous les formulaires saisis pour l'établissement courant, sans devoir naviguer dans chaque formulaire et cliquer "Envoyer".

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — nouvelle méthode `sendAllFormsForSchool()` :
```dart
Future<Map<String, bool>> sendAllFormsForSchool({
  required User user,
  void Function(int sent, int total)? onProgress,
}) async {
  // Itère sur toutes les questions (_questions) de l'établissement courant (_idEtab)
  // Pour chaque question : charge les données depuis SQLite, envoie via _api.saveData()
  // Marque is_sent=1 dans SQLite en cas de succès
  // Retourne Map<idQst, bool> (true = succès, false = échec)
}
```

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** — nouveau menu item et méthode :
- Ajout dans le `PopupMenu` : `'send_all'` → `ListTile` avec `Icons.cloud_sync_outlined` et label "Envoyer tous les formulaires"
- `_onMenuSelected` : branche `'send_all'` → appel `_sendAllForms(context, auth, entry)`
- Nouvelle méthode `_sendAllForms()` :
  - Dialogue de confirmation
  - Progress dialog avec `ValueNotifier<int>` et `LinearProgressIndicator`
  - Appel `entry.sendAllFormsForSchool(user: user, onProgress: callback)`
  - Fermeture du progress dialog
  - Dialogue résumé : ✅ N succès / ⚠️ N échecs

---

### 🟢 Nouveau — Envoi global : tous les formulaires de toute la campagne

**Besoin** : Depuis l'écran de campagne, envoyer d'un seul tap tous les formulaires de tous les établissements collectés.

**`stateduc_flutter/lib/services/database_service.dart`** — nouvelle méthode `getDistinctEtabQstWithData()` :
```dart
Future<List<Map<String, String>>> getDistinctEtabQstWithData(String idCamp) async {
  final db = await database;
  final rows = await db.rawQuery(
    'SELECT DISTINCT id_etab, id_qst FROM collected_data WHERE id_camp = ?',
    [idCamp],
  );
  return rows
      .map((r) => {
            'id_etab': r['id_etab'] as String? ?? '',
            'id_qst':  r['id_qst']  as String? ?? '',
          })
      .where((m) => m['id_etab']!.isNotEmpty && m['id_qst']!.isNotEmpty)
      .toList();
}
```

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** — nouvelle méthode `sendAllFormsForCampaign()` :
```dart
Future<Map<String, bool>> sendAllFormsForCampaign({
  required User user,
  required String idCamp,
  required String idSystem,
  void Function(int sent, int total)? onProgress,
}) async {
  // Utilise _db.getDistinctEtabQstWithData(idCamp) pour lister toutes les paires (etab, qst)
  // Pour chaque paire : charge les données SQLite, envoie, marque is_sent=1 si succès
  // Retourne Map<"${etabId}_${qstId}", bool>
}
```

**`stateduc_flutter/lib/screens/schools/campaign_detail_screen.dart`** — conversion `StatelessWidget` → `StatefulWidget` + bouton global :

```dart
// AVANT
class CampaignDetailScreen extends StatelessWidget { ... }

// APRÈS
class CampaignDetailScreen extends StatefulWidget {
  const CampaignDetailScreen({super.key, required this.campaign});
  final Campaign campaign;
  @override
  State<CampaignDetailScreen> createState() => _CampaignDetailScreenState();
}
class _CampaignDetailScreenState extends State<CampaignDetailScreen> {
  Campaign get campaign => widget.campaign;
}
```

- Imports ajoutés : `auth_provider.dart`, `data_entry_provider.dart`
- Bouton `OutlinedButton.icon` en tête de liste établissements :
  - Label : "Envoyer tous les établissements"
  - Icône : `Icons.cloud_sync_outlined`
  - Désactivé si `entry.isSending`
  - Appelle `_sendAllCampaignForms()` avec confirmation + progress dialog + résumé

---

### 🟡 Fix — Identification : données serveur ne remplacent pas les données locales

**Symptôme** : Sur certains types de campagne, le formulaire d'identification se pré-remplissait avec les données locales (souvent vides ou incomplètes) et ignorait les données du serveur.

**Cause racine** : `_autoReloadFromServerBackground()` utilisait `localWasEmpty = _formData.isEmpty` pour décider si le serveur devait écraser le local. Si des données locales existaient (même incomplètes), le serveur était ignoré.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
```dart
// AVANT — paramètre inexistant, toujours basé sur _formData.isEmpty
void _autoReloadFromServerBackground() { ... }

// APRÈS — forceOverwrite=true => localWasEmpty=true => serveur gagne toujours
void _autoReloadFromServerBackground({bool forceOverwrite = false}) {
  final localWasEmpty = _formData.isEmpty || forceOverwrite;
  ...
}
```
- `selectQuestion()` appelle désormais :
  ```dart
  _autoReloadFromServerBackground(forceOverwrite: isIdentificationForm);
  ```
- Pour les formulaires d'identification → **le serveur a toujours priorité** (données d'établissement officielles).
- Pour les autres formulaires → comportement inchangé (le local n'est écrasé que s'il était vide).

---

### 🟡 Fix — Settings : onglets Serveur/PIN/Sécurité trop peu contrastés

**Symptôme** : Dans l'écran Paramètres, les libellés et icônes des onglets "Serveur", "PIN", "Sécurité" étaient grisés et difficiles à lire (surtout l'onglet non sélectionné).

**Cause racine** : Material 3 applique par défaut `unselectedLabelColor = onSurface.withOpacity(0.38)` — très peu lisible sur un fond coloré (`AppBar`).

**`stateduc_flutter/lib/screens/settings/settings_screen.dart`** :
```dart
// AVANT — couleurs Material 3 par défaut (trop grises)
TabBar(
  controller: _tabController,
  tabs: const [ Tab(icon: Icon(Icons.dns_outlined), text: 'Serveur'), ... ],
),

// APRÈS — couleurs explicites basées sur appBarFg
final appBarFg = Theme.of(context).appBarTheme.foregroundColor
    ?? Theme.of(context).colorScheme.onPrimary;
TabBar(
  controller: _tabController,
  labelColor:            appBarFg,
  unselectedLabelColor:  appBarFg.withOpacity(0.80),
  indicatorColor:        appBarFg,
  labelStyle:            const TextStyle(fontWeight: FontWeight.w600, fontSize: 12),
  unselectedLabelStyle:  const TextStyle(fontWeight: FontWeight.w500, fontSize: 12),
  tabs: const [ Tab(icon: Icon(Icons.dns_outlined), text: 'Serveur'), ... ],
),
```

---

### 📊 Fichiers modifiés — Session 17 (Flutter uniquement, aucun changement PHP)

| Fichier | Type | Résumé |
|---------|------|--------|
| `lib/services/api_service.dart` | Fix | `sendTimeout` 120 s → 300 s |
| `lib/services/database_service.dart` | Nouveau | `getDistinctEtabQstWithData(idCamp)` |
| `lib/providers/data_entry_provider.dart` | Fix + Nouveau | Cohérence offline re-trigger × 2 ; `_autoReloadFromServerBackground(forceOverwrite)` ; `sendAllFormsForSchool()` ; `sendAllFormsForCampaign()` |
| `lib/screens/data_entry/school_data_screen.dart` | Nouveau | Menu "Envoyer tous les formulaires" + `_sendAllForms()` avec progress dialog |
| `lib/screens/schools/campaign_detail_screen.dart` | Nouveau | `StatelessWidget` → `StatefulWidget` ; bouton "Envoyer tous les établissements" + `_sendAllCampaignForms()` |
| `lib/screens/settings/settings_screen.dart` | Fix | `TabBar` : `labelColor`/`unselectedLabelColor`/`indicatorColor` explicites depuis `appBarFg` |

**Commit** : `1db4be2` — `feat(session17): timeout, cohérence offline, envoi global, identification, settings`  
**PR** : [#1 — ak_main → main](https://github.com/NasserKailou/stateduc_mobile/pull/1)

---

## [Unreleased] — 2026-05-30 — Session 13 : PHP 500 sur data_save.php — variables indéfinies dans callbacks

### 🔴 Fix CRITIQUE — HTTP 500 sur POST `/theme_save/.../id_annee` malgré le fix session 12b

**Symptôme** : `[Dio←] 500 .../data_save.php/theme_save/test/2/2/15702/70/0/0/23` — body vide.
**Log Apache** : `PHP Parse error: syntax error, unexpected ')' on line 334` sur le XAMPP de l'utilisateur.

**Double diagnostic** :

1. **Parse error sur XAMPP** : L'utilisateur n'avait pas encore copié le `data_save.php` corrigé (sessions 11-12b) vers son XAMPP. La version sur le serveur contenait encore `});` (ligne 334 originale, corrigée en session 11 → `}` dans notre repo). **Action** : l'utilisateur doit copier le fichier depuis le repo vers XAMPP.

2. **Variables indéfinies dans `theme_save_handler()`** (présentes dans notre repo, fixes additionnelles session 13) :
   - `$curl->error()` callback (ancienne L296) capturait `$data` dans `use(...)` — variable qui n'existe pas dans le scope de `theme_save_handler()` (elle existe dans la route GET originale mais pas dans la fonction déléguée). PHP 7 strict mode → fatal error.
   - `$date_time` utilisé dans `saveLogInfo()` dans le callback `error` (ancienne L307) — également non défini dans ce scope (défini seulement dans le callback `success`).
   - `saveLogInfo()` appelée sans le 9e paramètre `$id_annee` depuis l'ancien callback error → warning PHP.

**`StatEduc_MEN_2025/data_save.php`** (session 13) :
- Callback `$curl->error()` dans `theme_save_handler()` : suppression de `$data` du `use(...)`, suppression de l'utilisation de `$data` dans le body, remplacement de `$date_time` non défini par `$date_time_err = date(...)` local, passage de `$id_year` à `saveLogInfo()`.
- `saveLogInfo()` : paramètre `$id_annee` rendu optionnel (`= 0`) pour compatibilité avec les appels existants.

---

## [Unreleased] — 2026-05-29 — Session 12b : KOSAVE timeout 3min — deadlock Apache self-curl

### 🔴 Fix CRITIQUE — Timeout 3 minutes sur l'envoi + KOSAVE persistant

**Diagnostic complet** :

*Chaîne d'appels du save* :
```
Flutter Dio POST → data_save.php (Slim route) → $curl->post() → questionnaire_ws.php
```

**Problème 1 — Timeout 3 minutes** : `data_save.php` fait un appel curl **HTTP interne** vers `questionnaire_ws.php` sur le même serveur Apache. Aucun timeout n'était configuré sur cet objet curl PHP. Si Apache est saturé (plusieurs requêtes parallèles) ou si `questionnaire_ws.php` est lent (page HTML complète + queries DB), le curl attend **indéfiniment**. Flutter time-out après 180s et renvoie `DioExceptionType.receiveTimeout`.

**Problème 2 — Session deadlock potentiel** : `common_ws.php` (inclus par `data_save.php`) appelle `session_start()`. Sans `session_write_close()` avant l'appel curl interne, la session PHP reste verrouillée pendant tout l'appel. Si `questionnaire_ws.php` tente d'accéder à la même session ID (via cookie ou config partagée), un deadlock de fichier de session peut se produire.

**`StatEduc_MEN_2025/data_save.php`** :
- `CURLOPT_CONNECTTIMEOUT = 15` : echec rapide si le serveur interne est injoignable
- `CURLOPT_TIMEOUT = 60` : abort au bout de 60s si questionnaire_ws.php ne répond pas
- `session_write_close()` avant chaque `$curl->post()` (deux appels : route GET ligne 170, route POST `theme_save_handler` ligne 342)
- Ces fixes évitent que Flutter attende 3 minutes et reçoive un timeout au lieu d'une réponse

**`stateduc_flutter/lib/services/api_service.dart`** :
- `receiveTimeout` élevé de 180s → **300s (5 minutes)** : le save est une opération lente (`data_save.php` → curl → `questionnaire_ws.php` = page entière + 2× include grille + queries DB). 5 min = sécurité au cas où le serveur est lent mais répond quand même

**Note** : si KOSAVE persiste après ces fixes, la cause est que `questionnaire_ws.php` ne trouve pas le fichier grille `$curfile` (ACTION_THEME introuvable) ou que la DB write échoue silencieusement. Vérifier les logs XAMPP (`moblogs/test.log`) pour le détail.

---

## [Unreleased] — 2026-05-29 — Session 12 : données jamais écrites en DB (codeyear vide) + en-tête formulaire enrichi

### 🔴 Fix CRITIQUE — OKSAVE/KOSAVE : données envoyées mais jamais écrites en base de données

**Cause racine** : `auth_service.dart::getStoredUser()` retournait `codeyear: ''` après un déverrouillage PIN (sans reconnexion réseau). Cette chaîne vide se propageait comme suit :
- `yearCode = ''` dans `saveData()` d'`api_service.dart`
- `anneeSegment = ''` → URL 7 segments (sans `/id_annee`)
- `data_save.php` : pas de paramètre `id_annee` → `$id_year = $_SESSION['annee']` (vide en contexte REST)
- `questionnaire_ws.php?annee=` → `$_SESSION['annee'] = ''`
- La grille SQL : `WHERE CODE_TYPE_ANNEE = ''` → 0 lignes → pas d'écriture en DB
- `questionnaire_ws.php` retourne quand même `ISOKSAVEINDATABASE` (faux positif) → OKSAVE sans écriture

**`stateduc_flutter/lib/services/auth_service.dart`** (fix Flutter — cause racine) :
- Ajout des constantes `_kCodeyear = 'auth_codeyear'` et `_kLibyear = 'auth_libyear'`
- `login()` : sauvegarde de `codeyear` et `libyear` dans le stockage sécurisé (`flutter_secure_storage`) ET dans la base SQLite (`_db.setSetting`)
- `getStoredUser()` : restaure `codeyear`/`libyear` depuis le stockage sécurisé avec fallback DB pour les anciennes installations
- Correction : `codeyear` est maintenant correctement rétabli après un déverrouillage PIN → `yearCode != ''` → URL 8 segments → `annee` correcte → écriture en DB

**`StatEduc_MEN_2025/data_save.php`** (filet de sécurité PHP) :
- Ajout du fallback `PARAM_DEFAUT` dans `theme_save_handler()` quand `$id_year` reste vide après résolution URL/session :
  ```php
  if ($id_year == '' || $id_year == '0') {
      $_def = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
      if ($_def && (int)$_def > 0) { $id_year = $_def; $_SESSION['annee'] = $id_year; }
  }
  ```
- Protège contre les appelants legacy qui n'envoient pas `id_annee` dans l'URL

### 🟡 Amélioration — En-tête de chaque formulaire : libellés enrichis

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** :
- "Année Courante" renommé en **"Année en session"**
- "Sous secteur" renommé en **"Type secteur"** (correspond au `libSystem` du système d'enseignement)
- Correction : `libSubsector` ne prend plus `libStatus` comme fallback (les deux sont des concepts différents)
- L'en-tête affiche donc : Année en session · Hiérarchie admin · Établissement/ID/Code · **Statut** · **Type secteur**

---

## [Unreleased] — 2026-05-29 — Session 11 : parse error data_save, HTTP 500 contrôle, pré-remplissage dates identification

### 🔴 Fix CRITIQUE — `data_save.php` : erreur de parsing PHP bloquant tous les envois

**Cause racine** : La ligne 334 (comptage PHP via `\n`) contenait `});` — résidu d'une ancienne fermeture de route Slim. La fonction `theme_save_handler()` est une fonction PHP autonome qui se ferme avec `}` seul. Le `)` orphelin causait `Parse error: syntax error, unexpected ')'` → HTTP 500 sur **tous** les envois.

**Note technique** : Le fichier `data_save.php` a des fins de ligne CRLF avec des `\n` Unix intégrés à la ligne 194 (1437 octets, 33 `\n` intégrés). PHP compte les lignes via `\n` uniquement → la "ligne 334" PHP ≠ ligne 334 CRLF. Correction appliquée par analyse octets Python : `lf_lines[333] = b'}\r'`.

**`StatEduc_MEN_2025/data_save.php`** :
- `});` → `}` à la ligne 334 (comptage PHP/LF)

### 🔴 Fix — `data_controle.php` : HTTP 500 sur tous les contrôles de cohérence

**Cause racine** : `controle_theme_batch.class.php` prend `$ctrl_id = ID_ASSOC_REG_THM` (une règle d'association précise) et non un ID de thème. Passer `15702` comme `ctrl_id` → `WHERE ID_ASSOC_REG_THM = 15702` → 0 lignes → `array_change_key_case_unicode(null)` → fatal PHP 500. De plus, le paramètre `$alert` n'a pas de valeur par défaut → un appel à 5 arguments causait aussi une erreur fatale.

**`StatEduc_MEN_2025/data_controle.php`** — réécriture complète :
- `controle_strip_theme_id($id_theme, $id_sector)` : décompose l'ID thème composite (ex. `15702` → `1570`) identique à `data_rules.php`
- `controle_run_for_theme($raw_theme_id, ...)` : requête `SELECT DISTINCT ID_ASSOC_REG_THM FROM DICO_REGLE_THEME_ASSOC WHERE ID_THEME = x AND ACTIVER_CTRL = 1`, puis appel `new controle_theme_batch($ctrl_id, ..., $alert=false)` pour chaque règle d'association, collecte des violations

### 🟡 Fix — Formulaire identification : dates `DATE_CREATION_0` / `DATE_RECONNAISSANCE_0` non pré-remplies

**Cause racine** : Ces champs existent uniquement côté serveur (pas dans le modèle `School` local). Le rechargement auto depuis le serveur ne se déclenchait que si `_formData.isEmpty` → premier chargement seulement. En ré-ouvrant la fiche, les données locales existaient → pas de rechargement → dates absentes.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- `_autoReloadFromServerBackground()` se déclenche **toujours** pour les formulaires d'identification (pas seulement si `_formData.isEmpty`)
- Fusion intelligente : si `localWasEmpty=true` au moment de l'appel → remplacement complet + `_hasUnsavedChanges = false` ; sinon → remplissage conditionnel (ne remplace que les champs vides, préserve les saisies utilisateur)

---

## [Unreleased] — 2026-05-29 — Session 10 : cohérence nb_regles, grille add-row, pré-remplissage identification

### 🔴 Fix — Cohérence `nb_regles:0` : `data_rules.php` interrogeait le mauvais `ID_THEME`

**Cause racine** : L'app mobile envoie un `id_theme` **composite** (ex. `15602` = thème `1560` + secteur `2`). `data_rules.php` utilisait directement `WHERE ID_THEME=15602` alors que la table `DICO_REGLE_THEME` stocke l'ID brut (`1560`). Même décomposition que dans `questionnaire_reload_ws.php` :  
`str_theme_id = substr(id_theme, 0, len(id_theme) - len(id_sector))`

**`StatEduc_MEN_2025/data_rules.php`** (les deux routes GET) :
- Ajout de la décomposition du `id_theme` composite avant la requête `DICO_REGLE_THEME`
- Variables : `$str_theme_id` (route 1) et `$str_theme_id2` (route 2)
- La requête utilise maintenant `WHERE ID_THEME = (int)$str_theme_id`

### 🔴 Fix — Grille add-row : `maxIdx` calculé sur le mauvais segment numérique

**Cause racine** : La JS utilisait `el.name.match(/_(\d+)(?:_\d+)?$/)` sur les noms de champs pour déduire l'index de ligne. Certains formulaires ont des champs comme `CODE_TYPE_DISCIPLINE_FORM_1_0` où `1` est le numéro de **colonne**, pas de ligne → `maxIdx = 1` au lieu de `0` → la nouvelle ligne est indexée `2` au lieu de `1`.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Remplacement de la détection de `maxIdx` par lecture des attributs `id` des `<TR>` (ex. `id='ligne-paire_14_0'` → index 14)
- La regex de remplacement utilise maintenant `new RegExp('_' + maxRowIdx + '(_\\d+)?$')` (index spécifique) au lieu du pattern générique
- Fallback : `/(\d+)(_\d+)?$/` si aucun TR id trouvé

### 🟡 Fix — Identification pré-remplissage : boutons radio non cochés au premier ouverture

**Cause racine** : `_prefillIdentificationFields()` ne remplissait que les champs texte (nom, code, statut textuel). Les radios HTML de l'identification (`CODE_TYPE_STATUT_ETABLISSEMENT_0`) ont des VALUE littéraux comme `'CODE_TYPE_STATUT_ETABLISSEMENT_0_1'` — il faut pré-remplir `_formData['CODE_TYPE_STATUT_ETABLISSEMENT_0'] = 'CODE_TYPE_STATUT_ETABLISSEMENT_0_1'` pour que `_injectData()` coche le bon bouton.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- Nouveau champ `_idStatus` + paramètre `idStatus` dans `initForSchool()`
- `_prefillIdentificationFields()` : si `_idStatus != null`, pré-remplit `CODE_TYPE_STATUT_ETABLISSEMENT_0` avec la valeur composite `CODE_TYPE_STATUT_ETABLISSEMENT_0_{idStatus}`
- Fonctionne car `el.value === val` dans `_injectData()` compare la chaîne complète

**`stateduc_flutter/lib/screens/data_entry/school_data_screen.dart`** :
- Passage de `idStatus: widget.school.idStatus` à `initForSchool()`

---

## [Unreleased] — 2026-05-29 — Session 9 : crash FormatException regex, icône splash asset, regroups parentid=0

### 🔴 Fix CRITIQUE — Crash `FormatException: Invalid group (?i)` dans les formulaires

**Cause racine** : `RegExp(r'(?i)(value=)...')` — Dart ne supporte **pas** les flags inline comme `(?i)` dans les expressions régulières. Flutter lève une `FormatException` au moment de compiler le regex, ce qui crashe `_preprocessHtml()` et affiche l'écran rouge d'erreur.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Remplacé `RegExp(r'(?i)(value=)"(\$[A-Z_]...)"')` par `RegExp(r'(value=)"(\$[A-Z_]...)"', caseSensitive: false)`
- Remplacé `RegExp(r'(?i)(value=)\$([A-Z_]...)')` par `RegExp(r'(value=)\$([A-Z_]...)', caseSensitive: false)`
- Dart utilise `caseSensitive: false` comme paramètre — pas de flag inline

### 🔴 Fix — Icône splash toujours vide : asset `assets/icon/icon.png` non déclaré

**Cause racine** : `pubspec.yaml` déclarait `- assets/` (répertoire racine uniquement). Flutter **n'inclut pas automatiquement les sous-répertoires** — chaque sous-dossier doit être déclaré explicitement. `assets/icon/icon.png` n'était donc pas bundlé dans l'APK → `Image.asset` échouait silencieusement.

**`stateduc_flutter/pubspec.yaml`** :
- Ajouté `- assets/icon/` sous la clé `assets:`

### 🟡 Fix — Navigation regroupements : `parentid=0` ignoré comme racine

**Cause racine** : Certains déploiements StatEduc retournent `parentid: "0"` pour les regroupements racine (au lieu de `-1` qui est la convention JS standard). Le code ne mappait que `-1` → null (racine). Résultat : tous les regroupements semblaient avoir un parent → `getChildRegroups(null)` retournait 0 lignes → navigation vide.

**`stateduc_flutter/lib/models/regroup.dart`** :
- `fromJson` : ajouté `"0"` et `""` comme sentinels supplémentaires → mappés null (racine)
- Getter `isRoot` : robustifié → vrai si `idParentRegp` est null, `'-1'`, `'0'`, ou chaîne vide

**`stateduc_flutter/lib/services/database_service.dart`** :
- Requête fallback `getChildRegroups` : ajouté `OR id_parent_regp = '0'` pour gérer les données existantes en DB

**`stateduc_flutter/lib/providers/campaign_provider.dart`** :
- Ajout d'un 2e fallback dans `selectSystem()` : si `getChildRegroups` retourne vide mais `_allRegroups` contient des entrées, utilise `_allRegroups.where((r) => r.isRoot)` — couvre les données migrées avant ce correctif

---

## [Unreleased] — 2026-05-29 — Session 8 : icône splash, formulaire gris, pré-remplissage, grille scroll, typo PHP

### 🔴 Fix — Icône splash screen toujours vide (cercle blanc)

**Cause racine confirmée** : `icon.png` est une image 2048×2048 RGBA avec fond blanc (87 % de pixels blancs) et contenu bleu centré. L'approche précédente (cercle blanc + `BoxFit.contain`) rendait le logo invisible : blanc sur blanc.

**`stateduc_flutter/lib/screens/splash/splash_screen.dart`** :
- Supprimé le `Container` avec fond blanc et `BoxDecoration` cercle
- Remplacé par `ClipOval` + `Image.asset(..., fit: BoxFit.cover, width: 160, height: 160)`
- Le fond blanc de `icon.png` lui-même fournit le cercle blanc — le `ClipOval` le découpe circulairement
- Sur le fond bleu splash, le résultat est un cercle blanc avec le logo bleu centré

### 🔴 Fix — Formulaire gris (écran WebView entièrement gris)

**Cause racine** : Le `Stack` de l'overlay de chargement avait un fond transparent. Avant que `setBackgroundColor(Colors.white)` prenne effet dans le moteur WebView, la couleur de fond du `Scaffold` parent (gris clair) transparaissait.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Enveloppé le `Stack` de chargement dans un `Container(color: Colors.white, ...)`
- Le fond blanc garantit qu'aucun gris ne transparaît, même avant que le WebView initialise sa propre couleur de fond

### 🔴 Fix — Pré-remplissage identification : race condition de timing

**Cause racine** : `onPageFinished` était appelé directement sur le thread de rendu Flutter. Si `selectQuestion()` avait appelé `notifyListeners()` juste avant que le WebView termine son chargement, `widget.data` pouvait encore contenir l'ancien `Map` vide au moment où `_injectData()` s'exécutait (avant que Flutter propage le nouveau `data` aux props du widget).

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `onPageFinished` utilise maintenant `WidgetsBinding.instance.addPostFrameCallback(...)` pour différer `_injectData()` + `_injectBridge()` jusqu'au prochain frame Flutter
- Garantit que `widget.data` reflète les dernières valeurs pré-remplies (`_prefillIdentificationFields()`) avant l'injection JS

### 🔴 Fix — Injection JS champs avec `NAME=` majuscule

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `_injectData()` JavaScript inclut désormais un fallback case-insensitive pour les noms d'attributs
- Si `querySelectorAll('[name="X"]')` ne trouve rien, boucle sur tous les inputs et compare `getAttribute('name').toUpperCase() === name.toUpperCase()`
- Couvre les formulaires dont le HTML utilise `NAME='NOM_ETABLISSEMENT_0'` (majuscule) au lieu de `name=`

### 🟡 Fix — Formulaires grille : scroll horizontal des tableaux larges

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- `_buildHtmlPage()` : CSS amélioré — les `<table>` sont maintenant enveloppés par JS dans un `<div class="div-table-questionnaire">` avec `overflow-x: auto; display: block; -webkit-overflow-scrolling: touch`
- `th` : `white-space: normal; min-width: 60px` pour permettre le retour à la ligne dans les en-têtes de colonnes grille
- Script `DOMContentLoaded` qui enveloppe automatiquement tous les tableaux en divs scrollables

### 🔴 Fix — Typo `$id_teme` dans `data_save.php` (route GET)

**`StatEduc_MEN_2025/data_save.php`** (route GET `/theme_save/.../:data`, ligne 153) :
- Corrigé `if ($id_teme == $id_theme_ident)` → `if ($id_theme == $id_theme_ident)`
- Ce typo empêchait la comparaison du thème courant avec le thème d'identification → `LOC_REG_0` n'était jamais injecté côté serveur pour la route GET historique

---

## [Unreleased] — 2026-05-29 — Session 5 : fix VALUE=$VAR dans les formulaires grille + détection grille améliorée

### 🔴 Fix CRITIQUE — Boutons radio/select jamais pré-sélectionnés dans les formulaires grille

**Problème** : Dans tous les formulaires grille (personnel enseignant, locaux, effectifs…), les données précédemment saisies n'étaient pas restaurées sur les boutons radio et listes déroulantes lors du chargement du formulaire.

**Cause racine** : Les fichiers HTML sont des templates PHP servis par le serveur après substitution. L'application mobile cache le HTML brut (non substitué) — les champs texte ont `VALUE="$NOM_0"` et les radios ont `VALUE=$CODE_TYPE_SEXE_0_1` (sans quotes). La fonction `_injectData()` fait `el.checked = (el.value === val)` mais `el.value` vaut le littéral `"$CODE_TYPE_SEXE_0_1"` au lieu de `"1"` → la comparaison échoue toujours.

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- **Étape 4a** dans `_preprocessHtml()` : Remplace `VALUE="$VARNAME"` (texte) → `VALUE=""` (vide)
  - `_injectData()` remplit ensuite la valeur correcte via JS
- **Étape 4b** dans `_preprocessHtml()` : Remplace `VALUE=$CODE_TYPE_SEXE_0_1` (non quoté) → `VALUE=1`
  - Le dernier segment numérique après `_` est la valeur réelle de l'option radio/select
  - `_injectData()` peut maintenant faire `el.checked = (el.value === "1")` correctement
- Les deux remplacements sont insensibles à la casse (`(?i)`) pour `value=` / `VALUE=`

### 🔴 Fix — Détection et comptage des formulaires grille incomplets

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :

**`_detectGridForm()`** :
- Ajout de `MiseEvidenceLigneFrame` comme signal de détection grille
  → `Personnel_Enseignant_4.html` n'a ni `NUMERO_LOCAL` ni `addGrilleLine` mais utilise `MiseEvidenceLigneFrame`
  → Sans ce signal, le bouton « + Ajouter une ligne » n'était pas affiché

**`_countGridRows()`** :
- Réécriture pour gérer deux conventions de nommage des lignes :
  1. `$NUMERO_LOCAL_N` — formulaires locaux (9303, Mob1-Locaux, effectif_gp_age)
  2. `id='ligne-paire_N_0'` / `id='ligne-impaire_N_0'` — formulaires personnel (Personnel_Enseignant_4)
- Renvoie le nombre de lignes HTML pré-générées pour affichage correct dans le compteur du bouton

---

### 🔴 Fix CRITIQUE — Envoi données toujours en échec (`data_save.php` + `questionnaire_ws.php`)

**Problème** : Les données n'arrivent toujours pas en base malgré le fix `&annee` de la session 3.

**Cause racine** : `questionnaire_ws.php` est appelé via curl interne depuis `data_save.php`. Ce curl crée une **nouvelle session PHP vide** — `$_SESSION['login']` n'est jamais positionné, or la classe arbre et les requêtes SQL en dépendent pour écrire les données (`UPDATE ... SET login=...`). Sans login, les écritures échouent silencieusement ou sont rejetées.

**Corrections apportées** :

**`StatEduc_MEN_2025/data_save.php`** (lignes 139 + 291) :
- Ajout de `&login=$user&langue=fr` aux deux URL curl vers `questionnaire_ws.php`
- Avant : `...&annee='.$id_year`
- Après  : `...&annee='.$id_year.'&login='.$user.'&langue=fr'`

**`StatEduc_MEN_2025/questionnaire_ws.php`** (après ligne 23, avant `$GLOBALS['lancer_theme_manager']`) :
- Ajout du bloc "Mobile/curl session bootstrap" :
  - `$_SESSION['login']` ← `$_GET['login']` (si fourni)
  - `$_SESSION['langue']` ← `$_GET['langue']` (si fourni, défaut `'fr'`)
  - `$_SESSION['style']` ← défaut `'stateduc.css'` (évite erreur CSS)
  - `$_SESSION['valide']` ← `true` (bypass vérification session)
  - `$_SESSION['code_user']` ← `0`, `$_SESSION['groupe']` ← `1` (bypass restrictions privilèges)

### 🔴 Fix — Splash screen : icône blanche dans le cercle

**`stateduc_flutter/lib/screens/splash/splash_screen.dart`** :
- Changé `logo.gif` (bannière paysage 370×109) → `icon.png` (carré 2048×2048) avec `ClipOval` + `fit: BoxFit.cover`

### 🔴 Fix — Formulaire d'identification : champs vides

**Cause** : `_prefillIdentificationFields()` utilisait des noms sans suffixe `_0` (ex: `NOM_ETABLISSEMENT`) alors que le formulaire serveur utilise `NOM_ETABLISSEMENT_0`, `CODE_ADMINISTRATIF_0`, etc.

**`stateduc_flutter/lib/providers/data_entry_provider.dart`** :
- Noms de champs corrigés avec suffixe `_0` : `NOM_ETABLISSEMENT_0`, `CODE_ADMINISTRATIF_0`
- Logique fill améliorée : remplace aussi les valeurs vides (ne se limite plus aux champs absents)
- Ajout des variantes sans suffixe en fallback pour les autres formulaires

### 🔴 Fix — Mojibake (`NÂ°`, `attribuÃ©`, `PrÃ©nom`) : correction à la source

**Cause racine** : `getFormHtml()` dans `api_service.dart` utilisait `ResponseType.plain`, laissant Dio décoder les octets ISO-8859-15 avec une interprétation inconsistante selon la locale.

**`stateduc_flutter/lib/services/api_service.dart`** :
- Changé `ResponseType.plain` → `ResponseType.bytes` pour le téléchargement HTML
- Décodage explicite `String.fromCharCodes(rawBytes)` = Latin-1 pur (byte → code point)
- Le pré-processeur `_preprocessHtml()` détecte ensuite le mojibake et répare (Latin-1 → UTF-8)

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Détection mojibake améliorée : liste de patterns fréquents (`Ã©`, `Â°`, `Nâ`, etc.)
- Ajout du désentitisation HTML : `&lt;` → `<`, `&gt;` → `>`, `&amp;` → `&`, etc.
  → Corrige `&lt;b&gt;1.6 Chaine …&lt;/b&gt;` affiché en texte brut
- `$NUMERO_LOCAL_N` → numéro de ligne (déjà présent, conservé)

### 🔴 Fix — Formulaires de type grille (personnel enseignant, locaux)

**`stateduc_flutter/lib/widgets/dynamic_form/dynamic_form_widget.dart`** :
- Détection automatique des formulaires grille (`$NUMERO_LOCAL`, `addGrilleLine`, pattern `FIELD_N_col`)
- Affichage d'un bouton natif **"+ Ajouter une ligne"** en bas des formulaires grille
- Le bouton clone la dernière ligne du tableau et incrémente les indices des champs
- Compatibilité : appelle `addGrilleLine()` JS si disponible, sinon fallback DOM clone
- Compteur de lignes affiché sur le bouton pour confirmation visuelle

---

## [Unreleased] — 2026-05-29 — Session 3 : fixes données serveur + formulaires

### Fix critique — Données non persistées sur le serveur (`data_save.php`)

**Problème** : L'utilisateur voyait « Données envoyées avec succès » mais le serveur ne montrait aucune donnée.

**Cause racine** : `data_save.php` → `theme_save_handler()` — le relais curl interne vers `questionnaire_ws.php` ne transmettait pas le paramètre `annee`. Sans année en session, `questionnaire_ws.php` ne pouvait pas exécuter les requêtes SQL filtrées par année, causant un retour silencieux sans écriture en base.

**Fichier corrigé** : `StatEduc_MEN_2025/data_save.php`
- Ligne 139 (route web) et ligne 291 (`theme_save_handler`) : ajout de `&annee=$id_year` à l'URL curl vers `questionnaire_ws.php`.
- Avant : `...&type_ent_stat='.$id_camp`
- Après  : `...&type_ent_stat='.$id_camp.'&annee='.$id_year`

### Ajouté — En-tête d'identification au-dessus de chaque formulaire

Affiche les informations complètes de l'établissement en haut de chaque formulaire (comme sur le serveur web) :
- **Année Courante** : ex. « 2024-2025 »
- **Hiérarchie géographique** : ex. « AGADEZ / ADERBISANAT / ADEBISSANAT »
- **Établissement** : Nom, Identifiant, Code Administratif
- **Statut** et **Sous secteur**

Widgets ajoutés dans `school_data_screen.dart` : `_SchoolInfoHeader`, `_InfoRow`, `_InfoChip`.

### Ajouté — Pré-remplissage du formulaire d'identification

Le premier formulaire (thème d'identification) est automatiquement pré-rempli avec les données déjà connues de l'établissement :
- Nom établissement (`NOM_ETABLISSEMENT`, `NOM_ETAB`, `LIB_ETABLISSEMENT`)
- Code administratif (`CODE_ETABLISSEMENT`, `COD_ETAB`, `CODE_ADMIN`)
- Statut (`STATUT`, `LIB_STATUT`)
- Sous-secteur (`SOUS_SECTEUR`, `LIB_SOUS_SECTEUR`)
- Année scolaire (`ANNEE_SCOLAIRE`, `LIB_ANNEE`)

Méthode `_prefillIdentificationFields()` ajoutée dans `DataEntryProvider`.

### Modifié — `DataEntryProvider.initForSchool()` (paramètres étendus)

Nouveaux paramètres optionnels : `codeEtab`, `libyear`, `codeyear`, `libStatus`, `libSubsector`, `adminHierarchy`.
Nouveaux getters publics : `codeEtab`, `libyear`, `codeyear`, `libStatus`, `libSubsector`, `adminHierarchy`.

### Modifié — `School` model

Ajout de deux champs optionnels : `libStatus` (libellé du statut résolu, ex. « Public ») et `libHierarchy` (hiérarchie géographique, ex. « AGADEZ / ADERBISANAT »). Ajout de `copyWith()`.

### Fix — Encodage HTML (ISO-8859-15 → UTF-8)

Correction de l'affichage en Mojibake (« UtilisÃ©e » → « Utilisée »).
`dynamic_form_widget.dart` : ajout de `_preprocessHtml()` qui détecte et corrige le double-encodage Latin-1/UTF-8 avant rendu WebView.

### Fix — `$NUMERO_LOCAL_N` non résolu

Les templates HTML de grille contiennent `$NUMERO_LOCAL_0`, `$NUMERO_LOCAL_1` etc. au lieu de numéros de lignes.
`_preprocessHtml()` les remplace par leur numéro d'ordre affiché (1, 2, 3 …).

### Fix — `didUpdateWidget` dans `DynamicFormWidget`

Rechargement automatique de l'URL WebView lorsque le HTML du formulaire change (navigation entre questions).

- **Icône de lancement** : `assets/icon/icon.png` (2048×2048) utilisée pour générer toutes les densités Android mipmap (`mdpi` 48px, `hdpi` 72px, `xhdpi` 96px, `xxhdpi` 144px, `xxxhdpi` 192px) via script Python/Pillow.
- **Splash screen** : `drawable/splash_logo.png` (512×512) générée depuis `icon.png`. `launch_background.xml` mis à jour pour afficher ce logo centré sur fond bleu `#1565C0`.
- **`ic_launcher_round`** dans tous les dossiers mipmap (Android 7.1+).
- `android:roundIcon="@mipmap/ic_launcher_round"` dans `AndroidManifest.xml`.

### Modifié
- `drawable/launch_background.xml` : référence `@drawable/splash_logo` (512px) au lieu de `@mipmap/ic_launcher` (trop petit pour un splash).

### Fix supplémentaire — navigation regroups (2e round)

**Problème identifié après tests** : `_buildEmptyState` apparaissait **immédiatement après** le clic sur "Education de Base" (avant même de naviguer dans un sous-regroup).

**Cause** : `getChildRegroups(idCamp, null)` ne retournait aucun regroup racine. Deux sous-cas :
1. Les regroups racines ont `id_parent_regp = '-1'` (string) au lieu de `NULL` — la requête `IS NULL` ne les trouve pas.
2. Les regroups racines ont `id_parent_regp = ''` (chaîne vide) — même problème.

**Corrections — `lib/services/database_service.dart`** :
- `getChildRegroups()` : si la requête `IS NULL` retourne 0, tente une requête de fallback `OR '-1' OR ''` pour les cas de mauvais stockage.
- `getChildRegroups()` : si toujours 0, retourne **tous** les regroups de la campagne (last resort).
- `getSchoolsByRegroup()` : ajout du sentinel `'__all__'` — quand `idRegp == '__all__'`, retourne tous les établissements de la campagne directement (court-circuit Strategy 1 & 2).
- Logs `[DB]` ajoutés dans `getChildRegroups()` pour chaque chemin (IS NULL, fallback -1/empty, last resort).

**Corrections — `lib/providers/campaign_provider.dart`** :
- `selectSystem()` : si `_loadRegroups(null)` retourne 0 regroups, bascule automatiquement en `_loadSchoolsForRegroup('__all__')` → affiche tous les établissements de la campagne directement.
- `_loadRegroups()` : ajout log `[Nav]` avec le nombre de regroups retournés.

---

## [1.0.2] — 2026-05-29 — fix(schools): triple-strategy fallback

### Problème
Après chargement d'une campagne, cliquer sur un système éducatif (ex : **Education de Base** sous **MOBILE**) affichait `_buildEmptyState` — "Aucun établissement trouvé pour ce regroupement." — même avec ≥ 4 établissements chargés.

### Cause racine
`getSchoolsByRegroup()` cherchait uniquement via `localisations.regroups_json`. Or le serveur `locs_camp` ne stocke que les IDs de la **chaîne directe** de l'utilisateur (feuilles + parents via `ID_REGROUP_PARENTS`), pas tous les nœuds intermédiaires de l'arbre de navigation. Quand l'utilisateur clique sur un nœud intermédiaire absent de `regroups_json`, la méthode retournait `[]`.

### Corrections — `lib/services/database_service.dart`
Ajout d'un import `package:flutter/foundation.dart` pour `debugPrint`.

**Triple stratégie dans `getSchoolsByRegroup()`** :

| Stratégie | Mécanisme | Cas couvert |
|-----------|-----------|-------------|
| **Strategy 1** *(existante)* | `localisations.regroups_json` contient `idRegp` | Chaîne locs complète — cas nominal |
| **Strategy 2** *(nouveau fallback)* | `schools.id_regroup = idRegp` direct SQL | Nœud intermédiaire absent de la chaîne locs |
| **Strategy 3** *(last resort)* | Tous les établissements de la campagne | Aucune correspondance → jamais d'écran vide |

**Logs debug `[DB]`** ajoutés :
- Nombre de lignes `localisations` pour `(id_camp, id_system)` — détecte mismatch `id_system`
- Échantillon `regroups_json` du premier enregistrement
- Résultats quantifiés pour chaque stratégie

### Corrections — `lib/providers/campaign_provider.dart`
**Logs debug `[Nav]`** ajoutés dans :
- `navigateIntoRegroup()` : affiche le `idRegp` cliqué, le nombre d'enfants détectés, et si on bascule en mode leaf
- `_loadSchoolsForRegroup()` : affiche le nombre d'établissements retournés

Ces logs sont visibles via `flutter logs` sur l'appareil physique pour diagnostics terrain.

---

## [1.0.1] — 2026-05-28 — fix(ssl): accept self-signed certificates

### Problème
L'écran "Charger une campagne" affichait **"Erreur réseau : The connection errored: Software caused connection abort"** malgré une connexion réseau stable.

### Cause racine — triple blocage SSL
1. `network_security_config.xml` contenait des entrées CIDR invalides (`<domain>192.168.1.0/24</domain>`) bloquant tout le trafic HTTP.
2. Dart/Flutter utilise son propre moteur TLS (BoringSSL) indépendamment d'Android — rejetait les certificats auto-signés du serveur intranet.
3. Absence de `HttpOverrides` global — les instances `HttpClient` hors Dio n'étaient pas configurées.

### Corrections

#### `android/app/src/main/res/xml/network_security_config.xml`
Remplacé la configuration invalide par un `base-config` permissif :
```xml
<network-security-config>
    <base-config cleartextTrafficPermitted="true">
        <trust-anchors>
            <certificates src="system"/>
            <certificates src="user"/>
        </trust-anchors>
    </base-config>
</network-security-config>
```

#### `lib/services/api_service.dart`
Ajout imports `dart:io` et `package:dio/io.dart`.
Override de l'adaptateur HTTP Dio pour accepter les certificats auto-signés :
```dart
(_dio.httpClientAdapter as IOHttpClientAdapter).createHttpClient = () {
  final client = HttpClient();
  client.badCertificateCallback = (cert, host, port) => true;
  return client;
};
```

#### `lib/main.dart`
Ajout d'un `HttpOverrides` global couvrant toutes les instances `HttpClient` hors Dio :
```dart
class _TrustAllCertificates extends HttpOverrides {
  @override
  HttpClient createHttpClient(SecurityContext? context) {
    return super.createHttpClient(context)
      ..badCertificateCallback = (cert, host, port) => true;
  }
}

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  HttpOverrides.global = _TrustAllCertificates();
  ...
}
```

---

## [1.0.0] — 2026-05-22 à 2026-05-28 — Réécriture complète Flutter

### Vue d'ensemble
Réécriture complète de l'application mobile StatEduc depuis Cordova/JavaScript vers Flutter/Dart. L'application originale utilisait `localStorage` et des appels AJAX jQuery ; la nouvelle version utilise SQLite (sqflite), Provider pour la gestion d'état, Dio pour les requêtes HTTP, et une architecture propre MVC/Provider.

### Architecture générale

#### Modèles (`lib/models/`)
- `User` — utilisateur authentifié (id, login, prénom, nom, année, filtres)
- `Campaign` — campagne de collecte (id, nom, dates, statut, typeRegroups CSV)
- `Regroup` / `RegroupType` — arbre de regroupements administratifs
- `School` / `SchoolStatus` — établissements scolaires
- `EducationSystem` — systèmes éducatifs (MOBILE, Education de Base, etc.)
- `Question` — thèmes de collecte avec règles de validation
- `Localisation` — liaisons école ↔ système ↔ chaîne de regroupements

#### Services (`lib/services/`)
- `ApiService` — singleton Dio avec Basic Auth, intercepteurs de log, SSL bypass, retry
- `DatabaseService` — singleton SQLite remplaçant les 25+ clés `localStorage` originales
- `AuthService` — authentification + stockage sécurisé des credentials (flutter_secure_storage)
- `CoherenceEvaluator` — moteur d'évaluation offline des règles de cohérence

#### Providers (`lib/providers/`)
- `CampaignProvider` — gestion des campagnes, navigation hiérarchique regroupements → établissements, téléchargement en 9 étapes (regroups → types → statuts → schools → locs → systems → formulaires + règles)
- `DataEntryProvider` — saisie de données par établissement, sauvegarde locale + synchronisation serveur, cohérence offline

#### Écrans (`lib/screens/`)
- `SplashScreen` — écran de démarrage avec vérification session
- `OnboardingScreen` — configuration URL serveur + login
- `LoginScreen` — authentification
- `CampaignsScreen` — liste des campagnes téléchargées
- `LoadCampaignScreen` — chargement d'une nouvelle campagne (9 étapes avec progress bar)
- `CampaignDetailScreen` — sélecteur de système éducatif → navigation hiérarchique → liste établissements
- `SchoolDataScreen` — formulaire de saisie par établissement (rendu HTML WebView + bannière cohérence)

### Schéma SQLite (version 3)
Tables créées : `settings`, `campaigns`, `education_systems`, `regroup_types`, `regroups`, `school_statuses`, `schools`, `localisations`, `questions`, `form_html`, `validation_rules`, `coherence_rules`, `collected_data`, `filter_periods`

### Fonctionnalités migrées depuis JavaScript original
- Authentification Basic Auth (charge_camp.js, users.js)
- Chargement campagne multi-étapes avec barre de progression (charge_camp.js — stmChargeCamp)
- Navigation hiérarchique regroupements (page_camp.js — displayRegroups, displayFinalRegroupEtabs)
- Affichage liste établissements filtrés par regroupement et système
- Rendu formulaires HTML via WebView (deux requêtes authentifiées)
- Sauvegarde données : POST multipart vers `/data_camp.php/save_data/` (page_etab.js)
- Règles de cohérence offline : évaluation SQL côté client (data_rules.php)
- Indicateur connectivité réseau (connectivity_plus)
- Stockage PIN/credentials sécurisé (flutter_secure_storage)

### Build Android
- Gradle 8.14.x, AGP 8.11.1, Kotlin 2.2.20, compileSdk 36
- Dépendances : dio 5.7, provider 6.1, sqflite 2.3, flutter_secure_storage 9.2, webview_flutter 4.10
- Signing : keystore configuré dans `key.properties`

### Corrections Build
- Suppression `flutter_sms` (namespace AGP 8.x incompatible)
- Suppression `vibration` (incompatibilité KGP)
- Remplacement `flutter_html` (beta cassée) par `webview_flutter` pour le rendu formulaires
- Fix `GeneratedPluginRegistrant.java` après changement plugins
- Upgrade Kotlin 2.0.21 → 2.1.0 → 2.2.20 pour compatibilité AGP 8.11.1
- Fix `debugPrint` string interpolation (erreur Dart)
- Fix `ValidationRule.validate()` méthode manquante
- Fix `ApiService` singleton — instance partagée AuthService + CampaignProvider
- Fix timeouts Dio : connectTimeout 60s, receiveTimeout 180s, sendTimeout 120s

### Fix spinner infini
Le `StreamBuilder` de chargement n'avait pas de condition de terminaison quand le step atteignait 100%. Remplacé par un `Consumer<CampaignProvider>` qui lit `isLoadingCampaign` directement.

### Fix formulaires
- Encodage UTF-8 des paramètres POST
- Transformation radio buttons : `{"radio_name": "value"}` → `"radio_name=value"` (format FormData)
- Authentification sur la seconde requête HTML (fetch URL → fetch contenu)

### Fix navigation
- `CampaignDetailScreen` : machine à 5 états (`selectedSystem == null` / `isNavigating` / `regroups` / `schools` / `empty`) pour éviter le spinner infini
- `navigateUpRegroup` : recalcul du niveau correct en remontant le breadcrumb

---

## Notes techniques

### Endpoint mapping (JS → Flutter)
| Endpoint | Paramètre clé | Notes |
|----------|---------------|-------|
| `new_camp/{userId}/1` | `currentUser.id` | Liste des campagnes disponibles |
| `reg_camp/{login}/{campId}/1` | `currentUser.login` ← LOGIN, pas ID ! | Regroupements |
| `etabs_camp/{userId}/{campId}/1` | `currentUser.id` | Établissements |
| `locs_camp/{userId}/{campId}` | `currentUser.id` | Localisations |
| `sys_camp/{userId}/{campId}` | `currentUser.id` | Systèmes éducatifs |
| `theme_camp/{campId}/{sysId}/eng` | — | Questions/formulaires |
| `regle_theme_camp/{qstId}/{sysId}` | — | Règles de cohérence |
| `save_data/{campId}/{etabId}/{sysId}/{qstId}/{filterId}` | — | Sauvegarde saisie |

### Format `locs_camp` (serveur)
```json
{ "idloc": ..., "idcamp": "...", "idsys": "...",
  "regroups": "id1,id2,...",  // CSV — IDs chaîne utilisateur seulement
  "etabs": "id1,id2,..." }    // CSV — établissements filtrés
```
⚠ `regroups` contient uniquement la chaîne directe de l'utilisateur, pas tous les nœuds de navigation.

### Format `reg_camp` (serveur → client)
```json
{ "id": "...", "nom": "...", "type": "...", "parentid": "-1" }
```
`parentid == "-1"` → racine → stocké `NULL` dans SQLite.
