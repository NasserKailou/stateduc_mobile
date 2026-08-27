# Catalogue des Bugs — Root Causes et Corrections Prouvées

> Chaque entrée = un bug rencontré en production, diagnostiqué et résolu.
> Format : Symptôme → Root cause → Fix → Fichier(s) modifié(s)

---

## BUG-PORT-001 — 404 : HTTP/1.1 404 Not Found à l'envoi

**Symptôme (Logcat Flutter)**
```
[Dio←] Body: {"se_status":400,"se_data":"404 : HTTP\/1.1 404 Not Found"}
```

**Root cause**
`config_app.php` → `_sised_local_port()` avait une whitelist fixe :
```php
$ports_http_only = array(80, 8080, 8000, 8888);
```
Si Apache tourne sur un port non listé (ex: **8083**, **9090**), le port est ignoré
→ curl interne vers `127.0.0.1:80` (inexistant) → 404.

**Fix**
Remplacer la whitelist par une **liste d'exclusion SSL** :
```php
$ssl_ports = array(443, 8443);
// SERVER_PORT accepté si != 443 et != 8443
if ($p > 0 && !in_array($p, $ssl_ports)) {
    $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
    if ($s !== false) { fclose($s); return $p; }
}
```
→ Voir `templates/php/config_app_template.php`

**Fichiers modifiés** : `StatEduc_burundi/config_app.php`

---

## BUG-CURL28-001 — Timeout cURL 28 : Operation timed out after 120000ms

**Symptôme (Logcat Flutter)**
```
[Dio←] Body: {"se_status":400,"se_data":"28 : Operation timed out after 120000 milliseconds with 74427488272384 bytes received"}
```
Note : la valeur de bytes (74427488272384) est aberrante — artefact cURL quand
la connexion est coupée mid-stream.

**Root cause (3 causes combinées)**

1. **`memory_limit = 64M`** dans `questionnaire_ws.php` — trop bas.
   HTML + arbre ADODB + données formulaire dépassent 64M
   → PHP fatal error → Apache coupe la connexion → cURL rapporte timeout.

2. **`session_start()` sans guard** dans `common.php` L94 et L592.
   Après `session_write_close()` de `questionnaire_ws.php`, `common.php`
   rappelle `session_start()` sans `session_status()` check
   → re-lock fichier session → blocage sous charge Apache.

3. **`CURLOPT_TIMEOUT = 120s`** trop court dans `data_save.php`.
   Les gros formulaires (thèmes TMIS, arbre profond) dépassent 120s.

**Fix**
```php
// questionnaire_ws.php ligne 21
ini_set("memory_limit", "256M");

// common.php L94 et L592
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// data_save.php
$curl->setOpt(CURLOPT_TIMEOUT, 300); // 300s = 5 minutes
```

**Fichiers modifiés** : `questionnaire_ws.php`, `common.php`, `data_save.php`

---

## BUG-TIMEOUT-001 — Envoi bloqué 60s puis échec (timeout Dio global)

**Symptôme (Logcat Flutter)**
```
[DioX] type=DioExceptionType.connectionTimeout uri=.../annees_ws.php/active/kimba
[DioX] The request connection took longer than 0:01:00.000000
```
Double bannière d'erreur dans l'UI. Envoi de données totalement bloqué.

**Root cause**
`fetchServerActiveYear()` dans `ApiService` héritait du timeout Dio global :
```dart
connectTimeout: Duration(seconds: 60)  // trop long pour un simple check
```
`_checkYearConsistency()` était FAIL-CLOSED : retournait `false` sur tout `catch(e)`
→ bloquait l'envoi même sans vrai mismatch d'année.

**Fix**
```dart
// api_service.dart
static const Duration _kYearCheckTimeout = Duration(seconds: 8);

response = await _dio.get(
  'annees_ws.php/active/$encodedLogin',
  options: Options(
    sendTimeout: _kYearCheckTimeout,
    receiveTimeout: _kYearCheckTimeout,
  ),
).timeout(_kYearCheckTimeout, onTimeout: () => throw ApiException('Timeout 8s'));

// data_entry_provider.dart — FAIL-OPEN policy
} on ApiException catch (e) {
  debugPrint('fail-open: ${e.message}');
  return true; // laisser passer — on ne peut pas confirmer un mismatch
} catch (e) {
  debugPrint('réseau KO → fail-open: $e');
  return true; // laisser passer
}
```
→ Voir `templates/dart/api_service_year.dart` et `templates/dart/check_year_consistency.dart`

**Fichiers modifiés** : `api_service.dart`, `data_entry_provider.dart`

---

## BUG-SESSION-001 — KOSAVE généralisé sur tous les formulaires (deadlock session)

**Symptôme (moblogs/user.log)**
```
2026/05/31 02:38:33;28:Operation timed out after 120015ms;http://.../questionnaire_ws.php?...
```
Tous les thèmes KOSAVE. Aucun envoi ne passe.

**Root cause**
```
questionnaire_ws.php (session A) ──→ require common.php
common.php:94 session_start() sans guard
→ essaie d'acquérir verrou exclusif sur fichier session
→ le verrou est déjà détenu par data_save.php (même session)
→ DEADLOCK → attente infinie → timeout 120s → erreur 28
```

Historique du fix dans le code (commentaires SESSION 61) :
- Avant : `@session_start(['read_and_close' => true])` ligne 3 de questionnaire_ws.php
- Toutes les écritures `$_SESSION` bootstrap étaient perdues
- `common.php:94` → `session_start()` sans guard → deadlock

**Fix définitif**
```php
// questionnaire_ws.php : ouvrir normalement, écrire bootstrap, FERMER avant common.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
// ... écritures $_SESSION bootstrap ...
session_write_close(); // OBLIGATOIRE avant require common.php
require_once 'common.php';

// common.php : guard sur tous les session_start()
if (session_status() === PHP_SESSION_NONE) { session_start(); }
```

---

## BUG-KOSAVE-001 — KOSAVE sur thèmes sans filtre (filter=null)

**Symptôme**
Thèmes avec `id_filter="null"` : KOSAVE. Thèmes avec filtre numérique : OKSAVE.

**Root cause**
`data_save.php` n'ajoutait pas `&filtre=` à l'URL quand `id_filter == "null"`
→ `questionnaire_ws.php` : `isset($_GET['filtre'])` = false
→ fallback sur `$_SESSION['filtre']` stale (ex: '1' d'une requête précédente)
→ `WHERE CODE_TYPE_PERIODE=1` → matrice vide → KOSAVE

**Fix**
```php
// data_save.php — dans theme_save_handler()
if ($id_filter != "null") {
    $urlBase .= '&filtre='.$id_filter;
} else {
    $urlBase .= '&filtre='; // explicitement vide — force isset($_GET['filtre'])=true
}
```

**Explication** : `&filtre=` (vide) passe `isset($_GET['filtre'])=true` mais `$_GET['filtre']<>''` est false
→ `code_filtre = ''` → `get_dico()` sans clause WHERE filtre → lecture complète → OKSAVE

---

## BUG-ADODB-001 — Clés de tableau ADODB imprévisibles (LOWER vs UPPER)

**Symptôme**
Parfois `$row['CODE']` fonctionne, parfois `$row['code']` est requis.
Comportement différent entre environnements.

**Root cause**
ADODB fetch mode non forcé → dépend de la configuration PHP/ODBC du serveur.
`$ADODB_FETCH_MODE` et `ADODB_ASSOC_CASE` varient entre serveurs.

**Fix**
```php
// common_ws.php ou config_app.php — au bootstrap
$GLOBALS['conn_dico']->SetFetchMode(ADODB_FETCH_ASSOC);
if (defined('ADODB_ASSOC_CASE_UPPER')) {
    $GLOBALS['conn_dico']->AssocCaseUpper();
}
// Ou directement :
define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
```
→ Toujours utiliser des clés UPPERCASE dans le code PHP : `$row['CODE_USER']`

---

## BUG-ANNEE-001 — annees_ws.php utilise conn_dico au lieu de conn

**Symptôme**
`GET annees_ws.php/active/:login` → erreur SQL ou résultats vides.

**Root cause**
`annees_ws.php` utilisait `$GLOBALS['conn_dico']` (base dictionnaire)
mais `ANNEES_SCOLAIRES` et `PARAM_DEFAUT` sont dans la base principale `conn`.

**Fix**
```php
// annees_ws.php — toutes les requêtes sur ANNEES_SCOLAIRES
$result = $GLOBALS['conn']->GetRow($sql);  // conn = base principale
// PAS conn_dico
```

---

## BUG-FLUTTER-001 — [5, text] affichage — valeurs tableau serveur non extraites

**Symptôme**
Les champs dropdown affichent `[5, "Oui"]` au lieu de `"Oui"`.

**Root cause**
Le serveur retourne `{"valeur": [5, "Oui"]}` (tableau) pour certains champs.
Le code Flutter utilisait directement `v` sans extraire l'index 0 (code) ou 1 (libellé).

**Fix**
```dart
// api_service.dart — _autoReloadFromServerBackground()
dynamic rawVal = serverData[key];
if (rawVal is List && rawVal.isNotEmpty) {
  formData[key] = rawVal[0].toString(); // extraire le code (index 0)
} else {
  formData[key] = rawVal?.toString() ?? '';
}
```

---

## BUG-FLUTTER-002 — [X, text] SQLite — stockage de tableaux sérialisés

**Symptôme**
Après rechargement depuis SQLite, les valeurs dropdown affichent `[5, "Oui"]`.

**Root cause**
`database_service.dart` stockait les valeurs brutes sans normalisation.
SQLite TEXT stockait `[5, "Oui"]` comme chaîne littérale.

**Fix**
```dart
// database_service.dart — lors du stockage
String normalizeValue(dynamic v) {
  if (v is List && v.isNotEmpty) return v[0].toString();
  return v?.toString() ?? '';
}
```

---

## BUG-MULTIANNEE-001 — Requêtes sans filtre CODE_ANNEE → données toutes années mélangées

**Symptôme**
Données d'années précédentes apparaissent dans les formulaires de l'année courante.
Statistiques faussées par accumulation pluriannuelle.

**Root cause**
Les requêtes SQL dans `questionnaire_ws.php` et les classes ADODB
n'incluaient pas de clause `WHERE CODE_ANNEE = :annee` car la colonne
n'existait pas ou n'était pas transmise.

**Fix** → Voir `references/05_multiannee_pattern.md` pour la solution complète.

---

## BUG-PUSH-001 — Authentication failed — GitHub App token expiré

**Symptôme**
```
remote: Invalid username or token. Password authentication is not supported.
fatal: Authentication failed
```

**Root cause**
Les GitHub App tokens (format `ghs_NNNN_JWT`) expirent après **1 heure**.
Le credential store (`~/.git-credentials`) garde l'ancien token.

**Fix**
```bash
rm -f ~/.git-credentials
# Régénérer via setup_github_environment (Genspark)
# Puis push avec token frais :
NEW_TOKEN=$(cat ~/.git-credentials | grep github.com | \
  sed 's|https://x-access-token:\([^@]*\)@github.com|\1|')
git -c credential.helper= push \
  "https://x-access-token:${NEW_TOKEN}@github.com/USER/REPO.git" \
  branch:branch
```
→ Voir `scripts/push_github.sh`
