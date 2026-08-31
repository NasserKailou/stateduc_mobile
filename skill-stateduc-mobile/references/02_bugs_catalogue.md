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

---

## ══════════════════════════════════════════════════════
## BUGS SESSIONS 10-19 (ak_app_ident + ak_secure) — 20-27 août 2026
## ══════════════════════════════════════════════════════

---

## BUG-PHP8-001 — Fatal Error PHP 8 : ereg() undefined function

**Symptôme**
```
PHP Fatal error: Uncaught Error: Call to undefined function ereg() in instance_grille.php
PHP Fatal error: Uncaught Error: Call to undefined function eregi()
```

**Root cause**
`ereg()` et `eregi()` ont été supprimées en PHP 7.0. Le codebase StatEduc
datait de PHP 4/5 et utilisait ces fonctions dans 30+ fichiers.

**Fix**
```php
// AVANT (PHP4/5):
if (ereg('^' . $pattern . '$', $str)) { ... }
if (eregi('\.cub', $filename)) { ... }

// APRÈS (PHP8):
if (preg_match('/^' . preg_quote($pattern, '/') . '$/', $str)) { ... }
if (preg_match('/\.cub/i', $filename)) { ... }  // /i = insensible casse (remplace eregi)
```

**Fichiers modifiés** : `instance_grille.php` (16 occurrences), `aggregated_db_structure.class.php`, `load_sql.php`, `defaut_nomenc_syst.php`, `export_grille.php`, `import_excel.php`, `olap_tools/*` (4 fichiers)

**Règle** : En PHP 8, toujours utiliser `preg_match()`. Pour `eregi()` (insensible à la casse), utiliser le flag `/i`.

---

## BUG-PHP8-002 — Fatal Error PHP 8 : constructeurs PHP 4 non reconnus

**Symptôme**
```
PHP Deprecated: Methods with the same name as their class will not be constructors in PHP8
PHP Fatal error: Cannot redeclare __construct()
```

**Root cause**
PHP 4 utilisait le nom de la classe comme constructeur. PHP 8 ne l'accepte plus.
15 bibliothèques tierces (fpdf, htmlparser, pclzip, etc.) utilisaient ce pattern.

**Fix**
```php
// AVANT (PHP4):
class FPDF {
    function FPDF($orientation='P', $unit='mm', $format='A4') { ... }
}

// APRÈS (PHP8):
class FPDF {
    function __construct($orientation='P', $unit='mm', $format='A4') { ... }
}
```

**Fichiers modifiés** : `fpdf.inc.php`, `htmlparser.inc.php`, `pclzip.lib.php`, `pdftable.inc.php`, `sms.inc.php`, `class.ADODB_XML.php`, `class.xml.php`, `oleread.inc.php`, `reader.php` (15 constructeurs au total)

---

## BUG-PHP8-003 — E_WARNING generer_frame_grille() : variables non initialisées

**Symptôme (log Apache)**
```
PHP Warning: Undefined variable $aff_total_vertic in frame.class.php:3283
PHP Warning: Undefined variable $rowspan_tr in frame.class.php:2974
PHP Warning: array_keys(): Argument #1 ($array) must be of type array, null given in frame.class.php:2683
```
`gentheme` (génération du formulaire HTML) s'arrêtait silencieusement — fichiers non écrits.

**Root cause**
En PHP 8.2, `E_WARNING` sur variable non initialisée bloque l'exécution si `error_reporting` est élevé. 6 variables conditionnelles utilisées avant initialisation dans `generer_frame_grille()`.

**Fix**
```php
// Fix 1: early return si dico vide
if (empty($this->dico)) { return ''; }

// Fix 2: initialiser avant foreach conditionnel
$aff_total_vertic = false;
foreach (...) { if (...) $aff_total_vertic = true; }

// Fix 3: initialiser $rowspan_tr
$rowspan_tr = '';
if ($nb_tr > 1) { $rowspan_tr = ' rowspan="' . $nb_tr . '"'; }

// Fix 4: null-coalescing sur accès tableau
$lib = ($tab_libelles_mesures[$i_mes] ?? '');

// Fix 5: initialiser compteur avant foreach
$cpt = 0;
foreach (...) { $cpt++; }

// Fix 6: guard isset avant file_put_contents
if (isset($element)) { file_put_contents(...); }
```

**Fichiers modifiés** : `frame.class.php`, `frame_mobile.class.php`

---

## BUG-PHP8-004 — magic_quotes no-op manquant en PHP 8

**Symptôme**
```
PHP Fatal error: Call to undefined function get_magic_quotes_gpc()
```

**Root cause**
`get_magic_quotes_gpc()` supprimée en PHP 8. La fonction wrapper `manage_magic_quotes()` l'appelait sans guard.

**Fix**
```php
// AVANT:
function manage_magic_quotes(&$array) {
    if (get_magic_quotes_gpc()) {  // Fatal en PHP8
        array_walk_recursive($array, 'stripslashes');
    }
}

// APRÈS:
function manage_magic_quotes(&$array) {
    // get_magic_quotes_gpc() supprimée en PHP8 — magic quotes désactivées par défaut
    // Cette fonction est conservée pour compatibilité des appels mais est un no-op
    return;
}
```

**Fichier modifié** : `server-side/lib/fonctions.inc.php`

---

## BUG-ADODB-002 — ADODB_ASSOC_CASE non protégé → conflit define() PHP 8

**Symptôme**
```
PHP Notice: Constant ADODB_ASSOC_CASE already defined in connexion.class.php
```

**Root cause**
`define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER)` appelé deux fois quand les fichiers sont inclus dans plusieurs chemins d'exécution.

**Fix**
```php
// AVANT:
define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);

// APRÈS:
if (!defined('ADODB_ASSOC_CASE')) {
    define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER);
}
```

**Fichier modifié** : `connexion.class.php`

---

## BUG-FLUTTER-S18 — [5, text] affiché au lieu de la valeur réelle

**Symptôme**
L'application Flutter affiche `[5, text]`, `[0, text]`, `[CODE_TYPE_ACCES_0_6, radio]`
dans les champs de saisie au lieu des valeurs réelles.

**Root cause**
Le serveur retourne `Map<String, dynamic>` avec des valeurs tableau `[valeur, type]`
ex: `{"champ1": [5, "text"], "champ2": ["CODE_TYPE_ACCES_0_6", "radio"]}`.
`_autoReloadFromServerBackground()` utilisait `v.toString()` → `"[5, text]"` stocké en SQLite.
Cette valeur corrompue était relue et affichée telle quelle.

**Fix**
```dart
// AVANT (bug):
final stored = v.toString();  // → "[5, text]"

// APRÈS:
dynamic rawVal = v;
if (rawVal is List && rawVal.isNotEmpty) {
    rawVal = rawVal[0];  // extraire la vraie valeur
}
// Normaliser les IDs radio (CODE_TYPE_ACCES_0_6 → "6")
if (rawVal is String && rawVal.contains('_')) {
    final parts = rawVal.split('_');
    rawVal = parts.last;
}
final stored = rawVal?.toString() ?? '';
```

**Fichier modifié** : `lib/providers/data_entry_provider.dart`

---

## BUG-FLUTTER-S19 — [5, text] en SQLite legacy (données corrompues avant fix S18)

**Symptôme**
Même après fix S18, certains appareils affichaient encore `[5, text]` car leur SQLite
contenait déjà des données corrompues persistées avant le fix.

**Root cause**
Fix S18 corrigeait la persistance future mais pas les données déjà en base.
`getCollectedData()` lisait SQLite sans sanitiser → affichait les anciennes valeurs corrompues.

**Fix**
```dart
// database_service.dart — helper de sanitisation
static String _sanitizeStoredValue(dynamic raw) {
    if (raw == null) return '';
    final s = raw.toString();
    // Détecter format "[valeur, type]" ou "[valeur,type]"
    if (s.startsWith('[') && s.endsWith(']')) {
        final inner = s.substring(1, s.length - 1);
        final first = inner.split(',').first.trim();
        return first;
    }
    return s;
}

// Appliqué dans getCollectedData(), getAllCollectedDataForCoherence(),
// getAllCollectedDataForCampEtab() — lecture systématique avec sanitisation
```

**Fichier modifié** : `lib/services/database_service.dart`

---

## BUG-CSRF-001 — CSRF token : méthode inexistante SecurityHelper::csrfToken()

**Symptôme (app_fie)**
```
PHP Fatal error: Call to undefined method SecurityHelper::csrfToken()
```

**Root cause**
`app_fie` utilise `FIE_CSRF_TOKEN_NAME` constant + méthode `getCsrfToken()`.
Certains fichiers utilisaient le literal string ou `SecurityHelper::csrfToken()` (inexistant).

**Fix**
```php
// AVANT (bug):
'<input type="hidden" name="csrf_token" value="...">'
// OU:
$token = SecurityHelper::csrfToken();

// APRÈS:
'<input type="hidden" name="' . FIE_CSRF_TOKEN_NAME . '" value="' . getCsrfToken() . '">'
```

**Fichiers modifiés** : `ParametresController.php`, `parametres.php`, `AdminController.php`, `import_eleves.php`

---

## BUG-PDO-001 — SQLSTATE HY093 : placeholders PDO dupliqués

**Symptôme (app_fie)**
```
PDOException: SQLSTATE[HY093]: Invalid parameter number: number of bound variables does not match number of tokens
```

**Root cause**
`EleveModel` et `InscriptionModel` utilisaient des requêtes PDO avec des paramètres
nommés (`:nom`, `:prenom`) mais les passaient dans un array positonnel (`[valeur1, valeur2]`),
ou inversement mixaient paramètres nommés et positionnels dans la même requête.

**Fix**
```php
// AVANT (bug): paramètre nommé + array positionnel
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = :nom AND prenom = :prenom");
$stmt->execute([$nom, $prenom]);  // HY093

// APRÈS: cohérent — nommés + nommés
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = :nom AND prenom = :prenom");
$stmt->execute([':nom' => $nom, ':prenom' => $prenom]);

// OU positionnel + positionnel
$stmt = $pdo->prepare("SELECT * FROM eleves WHERE nom = ? AND prenom = ?");
$stmt->execute([$nom, $prenom]);
```

**Fichiers modifiés** : `EleveModel.php`, `InscriptionModel.php`

---

## BUG-PILOTE-001 — Cohérence offline déclenchée à chaque frappe (debounce intempestif)

**Symptôme**
Sur le terrain lors de la phase pilote : chaque frappe de clavier déclenchait une
vérification de cohérence → lag perceptible → frustration agents de terrain.

**Root cause**
`checkCoherenceOffline()` se déclenchait via `Timer(Duration(milliseconds: 800), ...)`
dans `updateField()` — soit à chaque modification de champ.

**Fix**
Désactiver le debounce automatique. L'agent déclenche la cohérence manuellement
(bouton dédié) ou à la soumission.
```dart
// AVANT:
void updateField(String key, String value) {
    _data[key] = value;
    _debounceTimer?.cancel();
    _debounceTimer = Timer(
        const Duration(milliseconds: 800),
        () => checkCoherenceOffline(),  // ← supprimé
    );
}

// APRÈS:
void updateField(String key, String value) {
    _data[key] = value;
    notifyListeners();  // pas de debounce auto
}
```

**Fichier modifié** : `lib/providers/data_entry_provider.dart`

---

## BUG-PILOTE-002 — Bouton Supprimer campagne trop accessible (risque suppression accidentelle)

**Symptôme (phase pilote)**
Agents de terrain supprimaient accidentellement des campagnes depuis l'écran principal
en cliquant `Icons.delete_outline` visible sur chaque CampaignCard.

**Fix**
Déplacer le bouton « Supprimer » vers l'écran **Paramètres** uniquement.
```dart
// AVANT: bouton visible dans chaque _CampaignCard
IconButton(
    icon: const Icon(Icons.delete_outline),
    onPressed: () => _deleteCampaign(context, campaign),
)

// APRÈS: bouton uniquement dans SettingsScreen
// CampaignCard: suppression du bouton delete
// SettingsScreen: ajout section "Gestion campagnes" avec bouton Supprimer
```

**Fichier modifié** : `lib/screens/campaign/campaign_list_screen.dart`, `lib/screens/settings/settings_screen.dart`

---

## BUG-PILOTE-003 — Icône déconnexion dans AppBar trop visible (déconnexions accidentelles)

**Symptôme (phase pilote)**
Agents se déconnectaient accidentellement en touchant l'icône `Icons.logout` dans l'AppBar.

**Fix**
Retirer l'icône déconnexion de l'AppBar de `CampaignListScreen`. La déconnexion
reste accessible uniquement depuis **Paramètres**.

**Fichier modifié** : `lib/screens/campaign/campaign_list_screen.dart`

---

## BUG-REGROUP-001 — ID_REGROUP_PARENTS / ID_TYPE_REGROUP_PARENTS vides pour nouvelle année

**Symptôme**
Quand une nouvelle année de collecte est créée, l'import Excel des établissements
ne remplit pas `ID_REGROUP_PARENTS` / `ID_TYPE_REGROUP_PARENTS` → hiérarchie
géographique manquante (colline → commune → province).

**Root cause**
`maj_bdd_excel()` dans `user.class.php` ne reconstruisait pas la hiérarchie
quand les colonnes parent étaient vides (nouveau template sans données).

**Fix**
Interroger `ETABLISSEMENT_REGROUPEMENT` via jointure `REGROUPEMENT → HIERARCHIE`
filtrée sur `id_chaine`, ordonnée par `NIVEAU_CHAINE ASC` pour reconstruire la
hiérarchie complète.

**Fichier modifié** : `StatEduc_burundi/server-side/classes/metier/user.class.php`

---

## BUG-KOSAVE-002 — KOSAVE bypass verif() exit() pour appels curl mobiles

**Symptôme (session 62)**
L'envoi de données depuis Flutter retournait `KOSAVE` même avec des données valides.
Code `10602` spécifique.

**Root cause**
`verif()` dans `questionnaire_ws.php` appelait `exit()` directement pour les erreurs
de validation, tronquant la réponse JSON avant que `data_save.php` ne la reçoive.
Les appels cURL mobiles n'avaient pas de session PHP active → `verif()` déclenchait
un `exit()` inattendu au lieu de retourner une erreur propre.

**Fix**
Bypass `verif()` pour les appels cURL internes mobiles — détecter l'appel cURL
par en-tête HTTP et retourner `{"MAJ_OK": false, "SQLERR": "..."}` au lieu de `exit()`.

**Fichier modifié** : `StatEduc_burundi/questionnaire_ws.php`

---

## BUG-GESTION-USER-001 — Suppressions ADMIN_USERS CODE_GROUPE=1 après "Migrer agents mobiles"

**Symptôme (session 20 / ak_secure)**
Après avoir cliqué "Migrer les agents mobiles (groupe 4)" dans la page gestion des utilisateurs
(`administration.php?val=gestionuser`), la mise à jour `DICO_FIXE_REGROUPEMENT.ID_ANNEE`
s'exécutait correctement, mais tous les enregistrements `ADMIN_USERS` avec `CODE_GROUPE = 1`
(superviseurs) étaient supprimés.

**Root cause**
Dans `gestion_user.php`, la logique de dispatch POST était :
```php
if (isset($_POST['ak_update_annee'])) {
    // UPDATE DICO... ← s'exécute correctement
} // <- ABSENCE de else/return ici !

if (isset($_POST["import"])) {
    // import Excel
} else if (count($_POST) > 0) {    // ← BUG : TRUE car $_POST a 2 champs !
    $user = $_SESSION['instance_nomenc'];  // instance périmée d'une édition précédente
    $user->get_post_template($_POST);
    $user->comparer(...);
    $user->maj_bdd($user->matrice_donnees_bdd);  // ← supprime CODE_GROUPE=1 !
    unset($_SESSION['instance_nomenc']);
}
```
Le formulaire "Migrer" soumet `$_POST = ['ak_update_annee'=>'1', 'ak_new_annee_simple'=>'X']`.
Bloc 1 s'exécute. Bloc 2 : `isset($_POST["import"])` = FALSE, mais
`count($_POST) > 0` = **TRUE** (2 champs présents). Si `$_SESSION['instance_nomenc']`
est peuplé d'une édition utilisateur antérieure, `maj_bdd()` exécute une opération
destructive avec les données périmées de la session.

**Fix**
1. Ajouter `unset($_SESSION['instance_nomenc'])` à la fin du bloc `ak_update_annee`
   pour invalider toute instance périmée.
2. Ajouter `&& !isset($_POST['ak_update_annee'])` au `else if` pour
   empêcher le déclenchement du bloc user-edit lors d'un POST de migration.

```php
// AVANT (bug) :
} else if (count($_POST)>0)  {

// APRÈS (fix) :
} else if (count($_POST) > 0 && !isset($_POST['ak_update_annee']))  {
```
Et après le bloc `ak_update_annee` :
```php
unset($_SESSION['instance_nomenc']); // BUG-GESTION-USER-001
```

**Fichier modifié** : `StatEduc_burundi/server-side/include/administration/gestion_user.php`

**Commit** : `fix(gestion_user): BUG-GESTION-USER-001 — guard ak_update_annee + unset session`

---

## BUG-MOBLOG-001 — Import Excel sans traçabilité étape par étape

**Symptôme (session 20 / ak_secure)**
L'import Excel des utilisateurs (`maj_bdd_excel()`) ne produisait qu'un log CSV minimal
dans `server-side/import_export/` sans détailler : INSERT ADMIN_USERS, lookup hiérarchique
`ETABLISSEMENT_REGROUPEMENT`, INSERT DICO_FIXE_REGROUPEMENT, transactions. En cas d'erreur
silencieuse il était impossible de diagnostiquer quelle étape avait échoué.

**Root cause**
`create_log_file()` / `record_log_file()` existaient mais ne capturaient que :
`timestamp;code_user;nom;email;tel;login;groupe;message_final`
sans les SQL intermédiaires ni les résultats étape par étape.

**Fix**
Création du système **moblogs** :
- Répertoire `StatEduc_burundi/moblogs/` (avec `.gitkeep` + `.gitignore` pour les `.log`)
- 3 nouvelles méthodes dans `user.class.php` :
  - `create_mob_log($cheminFichierExcel)` — ouvre `moblogs/import_YYYYMMDD_HHMMSS_<nom>.log`
  - `write_mob_log($ligne, $etape, $login, $detail)` — écrit une ligne tabulaire horodatée
  - `close_mob_log()` — ferme proprement avec footer
- `maj_bdd_excel()` instrumenté avec 17 points de log couvrant :
  `DEBUT_LIGNE | VALIDATION_ECHEC | LOGIN_DOUBLON | TRANSACTION_BEGIN |`
  `ADMIN_USERS_INSERT | ADMIN_USERS_OK | ADMIN_USERS_ERREUR |`
  `DICO_PARAMS | DICO_TEMPLATE_LOOKUP | DICO_TEMPLATE_FALLBACK | DICO_TEMPLATE_RESULT |`
  `HIER_LOOKUP_SQL | HIER_LOOKUP_OK | HIER_LOOKUP_VIDE |`
  `DICO_VALEURS_FINALES | DICO_DOUBLON_SKIP | DICO_INSERT_SQL |`
  `DICO_INSERT_OK | DICO_INSERT_ERREUR |`
  `DICO_SKIP_CHAMPS_VIDES | TRANSACTION_COMMIT_SECURITE | FIN_LIGNE`

**Fichiers modifiés** :
- `StatEduc_burundi/server-side/classes/metier/user.class.php` (méthodes + instrumentation)
- `StatEduc_burundi/moblogs/.gitkeep` (nouveau)
- `StatEduc_burundi/moblogs/.gitignore` (nouveau)

**Commit** : `feat(moblogs): système de log riche import Excel — 17 étapes tracées`
