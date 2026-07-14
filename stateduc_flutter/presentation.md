# PRÉSENTATION TECHNIQUE — StatEduc Mobile
## Document de transfert pour développeur IA
### Projet : Collecte de données éducatives Burundi / Niger
### Date : 2026-07-14 | Sessions 1–49 | Branche : `ak_main` / `ak_secure`

---

> **Usage** : Ce document est structuré pour être converti directement en PowerPoint professionnel.  
> Chaque section `##` correspond à une diapositive principale.  
> Chaque `###` correspond à une sous-section ou point de bullet.  
> Les blocs de code illustrent les modifications réelles du dépôt.

---

# PARTIE I — TRAVAUX SERVEUR (PHP)

---

## SLIDE 1 — Contexte Serveur : Topologie réseau

### Architecture déployée
```
Internet / LAN utilisateur
        │
        ▼
  Fortinet NAT (port 9191 externe)
        │
        ▼
  VM Apache (port 80 ou 8080 interne)
        │
        ▼
  PHP Slim v2 → data_save.php → curl interne → questionnaire_ws.php
```

### Problème fondamental résolu (commit 41–44)
- Le port 9191 **n'existe pas sur la VM** — c'est le port du Fortinet/proxy externe
- `curl` depuis la VM vers `127.0.0.1:9191` → **Connection refused** systématiquement
- commit 41 à 43 : tentatives DNS resolve, TCP probe, CURLOPT_RESOLVE — toutes en échec
- **Session 44 : solution définitive**

---

## SLIDE 2 — `config_app.php` — Détection automatique du port local

### Fichier : `StatEduc_burundi/config_app.php`
**Rôle** : Fournit les constantes globales de configuration de l'application PHP.  
**Utilité** : Point central configurant les URLs internes utilisées par tous les fichiers PHP.

### Modification clé — Fonction `_sised_local_port()` (Session 44)

```php
// Détecte automatiquement le port Apache local par sondage TCP
// Priorité 1 : SERVER_PORT validé par fsockopen(127.0.0.1, SERVER_PORT)
// Priorité 2 : sonde 80, 8080, 8000, 8888 → premier qui répond
// Fallback   : 80
function _sised_local_port() { ... }

// URL interne curl : 127.0.0.1:PORT_LOCAL (jamais le port du Fortinet)
$SISED_AURL_INTERNAL = 'http://127.0.0.1:' . _sised_local_port() . '/stateduc/';

// Host header à injecter dans le curl
// → Apache route vers le bon VirtualHost (ex: stateduc.ins.ne:9191)
$SISED_HOST_HEADER = $_SERVER['HTTP_HOST'];
```

### Pourquoi ce design
- `curl` se connecte à **127.0.0.1:80** (Apache local, bypass Fortinet)
- `Host: stateduc.ins.ne:9191` envoyé dans le header → Apache reconnaît le VirtualHost
- Fonctionne sur : XAMPP, Apache seul, Apache+Tomcat, reverse proxy, VirtualHost
- **Analogie** : porte de service + badge visiteur normal

---

## SLIDE 3 — `data_save.php` — Sauvegarde des données

### Fichier : `StatEduc_burundi/data_save.php`
**Rôle** : Point d'entrée REST pour la sauvegarde des données de formulaire envoyées par l'app mobile.  
**Utilité** : Reçoit le POST Flutter, relaie vers `questionnaire_ws.php` via curl interne.

### Endpoint principal
```
POST /stateduc/data_save.php/theme_save/{login}/{id_camp}/{id_sys}/{id_qst}/{id_etab}/{id_filter}/{id_annee}
```

### Modifications cumulées (commit 3, 5, 11, 12, 12b, 13, 44)

| Session | Modification | Problème résolu |
|---------|-------------|----------------|
| 3 | Ajout `&annee=$id_year` dans URL curl | Données jamais écrites en DB (année manquante) |
| 5 | Ajout `&login=$user&langue=fr` | Session PHP curl vide → écritures rejetées |
| 11 | `});` → `}` (parse error ligne 334) | HTTP 500 sur tous les envois |
| 12 | Fallback `PARAM_DEFAUT` si `$id_year` vide | `codeyear=''` → grille SQL → 0 lignes |
| 12b | `session_write_close()` avant curl | Deadlock session PHP → timeout 3 min Flutter |
| 12b | `CURLOPT_CONNECTTIMEOUT=15`, `CURLOPT_TIMEOUT=60` | Curl interne sans timeout → attente infinie |
| 13 | Fix variables `$data`, `$date_time` non définies dans callback error | PHP 7 strict → fatal error |
| **44** | **`setHeader('Host', $GLOBALS['SISED_HOST_HEADER'])`** | **Bypass Fortinet/NAT → connexion directe Apache** |

### Architecture curl interne
```php
// Session 44 — connexion directe Apache + Host VirtualHost
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);
// urlBase = http://127.0.0.1:80/stateduc/questionnaire_ws.php?...
```

---

## SLIDE 4 — `data_reload.php` — Rechargement depuis serveur

### Fichier : `StatEduc_burundi/data_reload.php`
**Rôle** : Permet à l'app mobile de récupérer les données déjà saisies depuis le serveur.  
**Utilité** : Pré-remplissage des formulaires avec les données validées côté serveur.

### Modifications (commit 44)
```php
// Même correction que data_save.php :
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);

// + Timeouts ajoutés (absents avant commit 44)
$curl->setOpt(CURLOPT_CONNECTTIMEOUT, 15);
$curl->setOpt(CURLOPT_TIMEOUT, 60);
```

### Impact
- Résout les timeouts silencieux sur rechargement
- Cohérent avec `data_save.php` dans la topologie NAT

---

## SLIDE 5 — `data_rules.php` — Règles de cohérence

### Fichier : `StatEduc_burundi/data_rules.php`
**Rôle** : Fournit les règles de contrôle de cohérence associées à chaque thème, pour évaluation offline.  
**Utilité** : L'app mobile télécharge ces règles en arrière-plan et les stocke dans SQLite local.

### Endpoint
```
GET /stateduc/data_rules.php/theme_rules/{login}/{id_camp}/{id_sys}/{id_theme}/{id_etab}/null/{id_annee}
```

### Modification clé (commit 10) — Décomposition `id_theme` composite
```php
// L'app envoie id_theme COMPOSITE (ex: 15602 = thème 1560 + secteur 2)
// Avant : WHERE ID_THEME = 15602 → 0 lignes → nb_regles: 0
// Après : décomposition identique à questionnaire_reload_ws.php
$str_theme_id = substr($id_theme, 0, strlen($id_theme) - strlen($id_sector));
// → WHERE ID_THEME = 1560 → règles trouvées ✓
```

### Réponse JSON
```json
{
  "se_status": 200,
  "se_data": {
    "id_theme": 10002,
    "nb_regles": 5,
    "regles": [
      {
        "id_regle": 483,
        "lib_regle": "NB_LATRINES_ELEVES",
        "sql_regle": "SELECT Sum(NB_LATRINES_ELEVES) FROM DONNEES_ETABLISSEMENT WHERE ...",
        "sql_assoc": "SELECT Sum(NB_LATRINES_BON_ETAT) FROM DONNEES_ETABLISSEMENT WHERE ...",
        "critere": "<=",
        "associations": []
      }
    ]
  }
}
```

---

## SLIDE 6 — `data_controle.php` — Contrôle post-envoi serveur

### Fichier : `StatEduc_burundi/data_controle.php`
**Rôle** : Exécute le contrôle de cohérence serveur après envoi des données.  
**Utilité** : Validation officielle côté serveur (base SQL Server/Access) après synchronisation.

### Réécriture complète (commit 11)

**Avant** : passait `id_theme` composite comme `ctrl_id` → `WHERE ID_ASSOC_REG_THM = 15702` → 0 lignes → fatal PHP 500.

**Après** :
```php
// Décomposition id_theme composite
function controle_strip_theme_id($id_theme, $id_sector) { ... }

// Récupère toutes les règles d'association pour ce thème
function controle_run_for_theme($raw_theme_id, ...) {
    $rows = SELECT DISTINCT ID_ASSOC_REG_THM FROM DICO_REGLE_THEME_ASSOC
            WHERE ID_THEME = $stripped AND ACTIVER_CTRL = 1;
    foreach ($rows as $ctrl_id) {
        new controle_theme_batch($ctrl_id, ..., $alert=false);
    }
}
```

---

## SLIDE 7 — `questionnaire_ws.php` — Bootstrap commit curl

### Fichier : `StatEduc_burundi/questionnaire_ws.php`
**Rôle** : Worker PHP exécuté via curl interne depuis data_save.php — réalise l'écriture en base.  
**Utilité** : Traitement métier complet (grille SQL, arbre, écriture BDD).

### Modification (commit 5) — Bootstrap commit curl

```php
// AVANT : nouvelle commit PHP vide → $_commit['login'] absent
//       → arbre + écritures SQL échouent silencieusement

// APRÈS : injection des variables de commit depuis les paramètres GET curl
if (isset($_GET['login'])) {
    $_commit['login']      = $_GET['login'];
    $_commit['langue']     = $_GET['langue'] ?? 'fr';
    $_commit['style']      = 'stateduc.css';   // défaut
    $_commit['valide']     = true;              // bypass auth
    $_commit['code_user']  = 0;
    $_commit['groupe']     = 1;
}
```

---

## SLIDE 8 — Tableau récapitulatif PHP

### Tous les fichiers PHP modifiés/créés

| Fichier | commit | Rôle | Modifications principales |
|---------|----------|------|--------------------------|
| `config_app.php` | 41-44 | Configuration globale, URLs internes | `_sised_local_port()`, `SISED_HOST_HEADER`, bypass Fortinet |
| `data_save.php` | 3,5,11,12,12b,13,44 | Sauvegarde données formulaire | curl interne, `commit_write_close`, timeouts, Host header |
| `data_reload.php` | 44 | Rechargement données serveur | Host header, timeouts curl |
| `data_rules.php` | 10 | Fourniture règles cohérence | Décomposition `id_theme` composite |
| `data_controle.php` | 11 | Contrôle cohérence post-envoi | Réécriture complète, `controle_strip_theme_id` |
| `questionnaire_ws.php` | 5 | Worker écriture en base | Bootstrap commit curl (`$_commit` depuis GET) |

### Résultat final côté serveur
- ✅ Sauvegarde des données fiable (timeouts, commit, curl bypass NAT)
- ✅ Rechargement depuis serveur fonctionnel
- ✅ Règles de cohérence correctement distribuées au mobile
- ✅ Contrôle cohérence serveur fonctionnel post-envoi

---

# PARTIE II — TRAVAUX MOBILE FLUTTER

---

## SLIDE 9 — Vue d'ensemble application Flutter

### Projet : StatEduc Mobile
**Technologie** : Flutter/Dart, Android (APK)  
**Architecture** : MVC/Provider — réécriture complète depuis Cordova/JavaScript (commit 1–3)  
**Versions** : Gradle 8.14.x, AGP 8.11.1, Kotlin 2.2.20, compileSdk 36

### Stack technique

| Composant | Bibliothèque | Version |
|-----------|-------------|---------|
| HTTP | Dio | 5.7 |
| State | Provider | 6.1 |
| DB locale | sqflite | 2.3 |
| Auth sécurisée | flutter_secure_storage | 9.2 |
| Formulaires | webview_flutter | 4.10 |

### Structure du projet
```
stateduc_flutter/
├── lib/
│   ├── models/          User, Campaign, School, Question, Regroup...
│   ├── services/        ApiService, DatabaseService, AuthService, CoherenceEvaluator
│   ├── providers/       AuthProvider, CampaignProvider, DataEntryProvider
│   ├── screens/         Splash, Pin, Login, Campaigns, Schools, DataEntry, Settings
│   └── widgets/         DynamicFormWidget, _OfflineCoherenceBanner...
├── test/
│   └── sql_translator_test.dart    (15 tests unitaires)
└── CHANGELOG.md
```

---

## SLIDE 10 — Authentification & Sécurité

### Fichier : `lib/services/auth_service.dart`
**Rôle** : Gestion de l'authentification et des credentials persistants.

### Fonctionnalités
- **PIN 4–8 chiffres** + question de sécurité (stockage sécurisé `flutter_secure_storage`)
- **Basic Auth HTTP** : `Authorization: Basic base64(login:password)`
- `codeyear` + `libyear` sauvegardés dans stockage sécurisé ET SQLite (commit 12)

### Bug critique résolu (commit 12)
```dart
// AVANT : getStoredUser() retournait codeyear='' après déverrouillage PIN
// → URL sans /id_annee → data_save.php ne trouvait pas l'année → 0 écritures DB

// APRÈS : codeyear stocké dans FlutterSecureStorage au login
await _storage.write(key: _kCodeyear, value: user.codeyear);
// Restauré à chaque getStoredUser() → yearCode != '' → URL correcte
```

### Écran PIN (`pin_screen.dart`)
- Drapeau du Burundi (`assets/icon/Flag_of_country.png`) — commit 8, 18
- Deux lignes institutionnelles italiques : *"République du Burundi"* / *"Ministère de l'Éducation Nationale"* — commit 21

---

## SLIDE 11 — Base de données SQLite locale

### Fichier : `lib/services/database_service.dart`
**Rôle** : Singleton SQLite remplaçant les 25+ clés `localStorage` de l'application originale.

### Schéma — 14 tables (version 3)

| Table | Contenu |
|-------|---------|
| `settings` | Configuration URL serveur, credentials |
| `campaigns` | Campagnes téléchargées |
| `education_systems` | Systèmes éducatifs (MOBILE, Éducation de Base...) |
| `regroup_types` / `regroups` | Arbre de regroupements administratifs |
| `school_statuses` / `schools` | Établissements scolaires |
| `localisations` | Liens école ↔ système ↔ regroupements |
| `questions` / `form_html` | Thèmes de collecte + HTML des formulaires |
| `validation_rules` / `coherence_rules` | Règles de validation et cohérence |
| `collected_data` | **Données saisies** : `(id_camp, id_etab, id_qst, field_name, field_value TEXT)` |
| `filter_periods` | Périodes de filtrage |

### Méthodes clés ajoutées
- `getAllCollectedDataForCoherence()` — données pour moteur cohérence
- `getAllCollectedDataForCampEtab()` — toutes données d'un établissement/campagne
- `getDistinctEtabQstWithData()` — envoi global campagne (commit 17)

---

## SLIDE 12 — API Service — Communication HTTP

### Fichier : `lib/services/api_service.dart`
**Rôle** : Singleton Dio, point unique pour tous les appels REST vers le serveur.

### Configuration évolutive

| Paramètre | commit 1 | commit 12b | commit 17 | commit 19 |
|-----------|-----------|-------------|------------|------------|
| `connectTimeout` | 60s | 60s | 60s | 60s |
| `receiveTimeout` | 180s | 300s | 300s | **600s** |
| `sendTimeout` | 120s | 300s | 300s | **null** |
| Retry | aucun | aucun | aucun | **3 tentatives** |

### Retry automatique (commit 19)
```dart
// 3 essais au total, délai progressif 5s × tentative
// Ne réessaie PAS sur : ApiException (401, 404), connectionTimeout
// Réessaie sur : sendTimeout, receiveTimeout, DioExceptionType.unknown
static const int _kMaxRetries = 2;
static const int _kRetryDelay = 5;
```

### SSL bypass (commit 1)
```dart
// Certificats auto-signés intranet MEN
client.badCertificateCallback = (cert, host, port) => true;
```

---

## SLIDE 13 — Téléchargement de campagne — 9 étapes

### Fichier : `lib/providers/campaign_provider.dart`
**Rôle** : Orchestration du chargement complet d'une campagne.

### Flux en 9 étapes séquentielles
```
1. Regroupements administratifs  → reg_camp/{login}/{campId}/1
2. Types de regroupements        → (intégré dans l'étape 1)
3. Statuts d'établissements      → statuts
4. Établissements                → etabs_camp/{userId}/{campId}/1
5. Localisations                 → locs_camp/{userId}/{campId}
6. Systèmes éducatifs            → sys_camp/{userId}/{campId}
7. Formulaires HTML              → theme_camp/{campId}/{sysId}/eng
8. Règles de cohérence           → regle_theme_camp/{qstId}/{sysId}
9. Stockage SQLite               → DatabaseService.insertAll()
```

### Navigation hiérarchique — Triple stratégie (commit 3)

| Stratégie | Mécanisme | Cas couvert |
|-----------|-----------|-------------|
| Strategy 1 | `localisations.regroups_json` contient `idRegp` | Cas nominal |
| Strategy 2 | `schools.id_regroup = idRegp` direct SQL | Nœud intermédiaire |
| Strategy 3 | Tous les établissements de la campagne | Last resort — jamais d'écran vide |

---

## SLIDE 14 — Saisie de données — Formulaires dynamiques

### Fichier : `lib/widgets/dynamic_form/dynamic_form_widget.dart`
**Rôle** : Rendu WebView des formulaires HTML dynamiques + injection/extraction de données.

### Pipeline de traitement HTML

```
1. Téléchargement HTML (bytes ISO-8859-15)
2. _preprocessHtml() :
   a. Détection + correction Mojibake (Latin-1 → UTF-8)
   b. Désentitisation HTML (&lt; → <, etc.)
   c. $NUMERO_LOCAL_N → numéro de ligne (formulaires grille)
   d. VALUE="$VARNAME" → VALUE="" (texte vide pour _injectData)
   e. VALUE=$CODE_TYPE_0_1 → VALUE=1 (radio non quotés)
3. Rendu WebView (fond blanc, scroll horizontal tableaux)
4. _injectData() : injection JS des valeurs SQLite
5. _injectBridge() : pont JS → Flutter pour extraction à la sauvegarde
```

### Fixes critiques appliqués

| commit | Bug | Fix |
|---------|-----|-----|
| 1 | Encodage Mojibake | `ResponseType.bytes` + décodage Latin-1 explicite |
| 5 | Radios non pré-sélectionnés | Étapes 4a/4b dans `_preprocessHtml()` |
| 8 | Formulaire gris | `Container(color: Colors.white)` wrapping Stack |
| 8 | Pré-remplissage race condition | `addPostFrameCallback` pour `_injectData()` |
| 9 | Crash `(?i)` flags inline | `caseSensitive: false` paramètre Dart |
| 10 | Grille add-row mauvais index | Lecture attributs `id='ligne-paire_N_0'` des TR |

---

## SLIDE 15 — Fournisseur de données — `DataEntryProvider`

### Fichier : `lib/providers/data_entry_provider.dart`
**Rôle** : Gestion complète du cycle de vie d'une saisie (état, sauvegarde, envoi, cohérence).

### Fonctionnalités principales

**Saisie et persistance :**
- `updateField()` — debounce 800 ms → déclenche cohérence offline (commit 18)
- `saveLocally()` — persistance SQLite immédiate
- `sendToServer()` — POST REST avec retry × 3, suivi tentative en UI

**Envoi global (commit 17) :**
- `sendAllFormsForSchool()` — tous formulaires d'un établissement
- `sendAllFormsForCampaign()` — tous formulaires de toute la campagne

**Rechargement serveur intelligent (commit 17) :**
- `_autoReloadFromServerBackground()` — fusion locale/serveur
- `forceOverwrite=true` pour formulaire d'identification (serveur a priorité)

**7 déclenchements cohérence offline :**

| Événement | Délai | Depuis |
|-----------|-------|--------|
| Saisie d'un champ (debounce) | 0.8s | commit 18 |
| Sauvegarde locale | Immédiat | commit 1-16 |
| Ouverture formulaire rempli | Immédiat | commit 17 |
| Changement de filtre/période | Immédiat | commit 18 |
| Règles reçues du serveur | Arrière-plan | commit 17 |
| Données serveur fusionnées | Arrière-plan | commit 18 |
| Envoi serveur | Post-POST | commit 1-16 |

---

## SLIDE 16 — Écran de saisie — `school_data_screen.dart`

### Fichier : `lib/screens/data_entry/school_data_screen.dart`
**Rôle** : Écran principal de saisie par établissement. Affiche le formulaire WebView + bannières.

### En-tête établissement (commit 3)
```
Année en session · Hiérarchie admin · Établissement/ID/Code · Statut · Type secteur
```

### Bannière cohérence offline — `_OfflineCoherenceBanner` (commit 46)
```dart
// Fond blanc, bordure orange, titre "Contrôle de cohérence"
// Sous-titre "Contrôle local — non encore envoyé au serveur"
// Icônes error_outline rouges par violation
// Structure identique au dialog cohérence serveur (screenshot utilisateur)
```

### Indicateur de cohérence en cours (commit 18)
```dart
if (entry.isCheckingOffline)
  const LinearProgressIndicator(),   // barre de progression pendant l'évaluation
if (entry.hasOfflineCoherenceErrors)
  _OfflineCoherenceBanner(errors: entry.offlineCoherenceErrors),
```

### Menu contextuel (⋮)
- "Vérifier la cohérence" — contrôle offline immédiat (commit 21)
- "Envoyer tous les formulaires" — envoi global établissement (commit 17)

---

## SLIDE 17 — Moteur de cohérence offline — Architecture

### Fichier : `lib/services/coherence_evaluator.dart`
**Rôle** : Équivalent mobile de `controle_theme_batch.class.php` — évalue les règles SQL hors ligne.  
**Version actuelle** : commit 49

### Dual-path (commit 45)
```
evaluate()
    │
    ├─► CHEMIN 1 — SQL réel (prioritaire)
    │     SqlTranslator.translate()  →  SQL SQLite natif
    │     db.rawQuery()  →  résultat numérique
    │     _applyOperator()  →  violated: true/false
    │
    └─► CHEMIN 2 — regex fallback (conservatif)
          _extractValue()  →  SUM pattern matching
          _applyOperator()  →  conservative (pas de faux positifs)
```

### `SqlTranslator` — Pipeline de traduction (8 étapes)
```
1. Normalisation (strip ;)
2. Substitution paramètres ($CODE_ETABLISSEMENT, $CODE_TYPE_ANNEE)
3. Détection tables serveur (DONNEES_ETABLISSEMENT, ELEVES_AGE_NIVEAU_SEXE...)
4. Extraction champs référencés (SELECT, WHERE, GROUP BY, HAVING)
5. Construction CTE de pivot (MAX(CASE WHEN field_name='X' THEN...))
6. Traduction syntaxique (Is Null, NVL, Or/And...)
7. Suppression qualificateurs TABLE.FIELD → FIELD
7b. _stripContextOnlyHaving() — suppression HAVING redondant
8. Wrapper dual-mode EXISTS/SCALAR (commit 49)
```

---

## SLIDE 18 — Moteur de cohérence — CTE Pivot dynamique

### Stratégie de mapping DONNEES_ETABLISSEMENT → SQLite

**Problème** : Les règles serveur requêtent `DONNEES_ETABLISSEMENT` (vue SQL Server/Access) mais le mobile stocke les données dans `collected_data` sous forme EAV (Entity-Attribute-Value).

**Solution** : CTE de pivot dynamique généré automatiquement :

```sql
WITH DONNEES_ETABLISSEMENT AS (
  SELECT
    MAX(CASE WHEN UPPER(field_name)='NB_LATRINES_ELEVES'
        THEN CAST(field_value AS REAL) END) AS NB_LATRINES_ELEVES,
    MAX(CASE WHEN UPPER(field_name)='NB_LATRINES_BON_ETAT'
        THEN CAST(field_value AS REAL) END) AS NB_LATRINES_BON_ETAT,
    MAX(CASE WHEN UPPER(field_name)='CODE_ETABLISSEMENT'
        THEN field_value END) AS CODE_ETABLISSEMENT,
    MAX(CASE WHEN UPPER(field_name)='CODE_TYPE_ANNEE'
        THEN field_value END) AS CODE_TYPE_ANNEE
  FROM collected_data
  WHERE id_camp='2' AND id_etab='20952'
)
-- ← Contient exactement les données d'UN établissement/campagne
```

**Avantages** :
- Filtré sur `(id_camp, id_etab)` → une seule ligne par établissement
- Champs numériques : `CAST(field_value AS REAL)` → calculs arithmétiques
- Champs texte (CODE_ETABLISSEMENT) : sans CAST → comparaisons de chaînes

---

## SLIDE 19 — Moteur de cohérence — Bugs résolus (commit 45–49)

### Tableau complet des bugs et fixes

| commit | Bug | Cause racine | Fix |
|---------|-----|-------------|-----|
| 46 | `\1` littéral dans SQL → crash SQLite | Dart `replaceAll(RegExp, r'\1')` ne supporte pas les backreferences | `replaceAllMapped((m) => m.group(1)!)` |
| 46 | WHERE champs non extraits pour CTE | Extraction cherchait seulement TABLE.FIELD + GROUP BY/HAVING | Ajout extraction WHERE avec `dotAll: true` |
| 46 | Nom de table dans champs CTE | Aucun filtre `_knownServerTables` dans extraction | `!serverTables.contains(name)` |
| 46 | `sql_assoc` non traduisible → skip complet | `if (r2==null) return null` | `count2=0` fallback |
| 47 | COUNT=0 toujours (HAVING) | `'20952'(TEXT) = 20952(INTEGER)` → FALSE SQLite | `_stripContextOnlyHaving()` |
| 48-A | HAVING non supprimé (qualificateurs) | Strip appelé AVANT suppression `TABLE.FIELD` → `DONNEES_ETABLISSEMENT` dans HAVING body | Déplacement vers step 7b |
| 48-B | SQLiteLog syntax errors × 3 | Regex `(.+?)` non-greedy tronquait CTE au premier `)` | Suppression diagnostic CTE |
| **49** | **COUNT=1 toujours (SUM scalaire)** | **`COUNT(*)` d'une agrégation SUM = toujours 1 ligne** | **Dual-mode wrapper EXISTS/SCALAR** |

---

## SLIDE 20 — Moteur de cohérence — commit 49 : Fix SUM-scalaire

### Le bug — Logs réels de l'application
```
[CoherenceEval] rawQuery sql_regle rule=484 → count=1
[CoherenceEval] rawQuery sql_assoc rule=484 → count=1
[CoherenceEval] rule=484 path=SQL result=(1.0 <= 1.0) violated=false
```
Alors que `NB_LATRINES_ELEVES=50` et `NB_LATRINES_BON_ETAT=30` → violation réelle.

### La cause
```sql
-- Requête SUM-scalaire (SANS GROUP BY)
SELECT Sum(NB_LATRINES_ELEVES) FROM DONNEES_ETABLISSEMENT WHERE ...
-- ↓ toujours exactement 1 ligne (même si Sum=NULL)
-- COUNT(*) = 1 toujours
```

### Le fix — Dual-mode
```dart
final hasGroupBy = RegExp(r'\bGROUP\s+BY\b', caseSensitive: false).hasMatch(translatedSql);

if (hasGroupBy) {
  // MODE EXISTS — violations comptées
  wrappedSql = '$withClause\nSELECT COUNT(*) AS cnt FROM (\n$translatedSql\n) _violations';
  isScalar = false;
} else {
  // MODE SCALAR — valeur réelle retournée
  wrappedSql = '$withClause\nSELECT COALESCE((SELECT * FROM (\n$translatedSql\n) _s), 0) AS val';
  isScalar = true;
}
```

### Validation Python — 4/4 tests PASS
```
✅ T2 Fix SCALAR: Sum(NB_LATRINES_ELEVES)=50 > Sum(NB_LATRINES_BON_ETAT)=30 → violated=True
✅ T3 Cohérent: 20 <= 30 → violated=False (pas de faux positif)
✅ T4 Vide: COALESCE(NULL,0)=0 → not violated (conservatif)
✅ T1 Régression: rules GROUP BY toujours COUNT(*) → inchangé
```

---

## SLIDE 21 — Fonctionnalités mobiles complètes

### Tableau de toutes les fonctionnalités implémentées

| Fonctionnalité | commit | Fichier(s) | Description |
|----------------|---------|-----------|-------------|
| Authentification Basic Auth | 1 | `api_service.dart` | Login + PIN sécurisé |
| Téléchargement campagne (9 étapes) | 1–3 | `campaign_provider.dart` | Progress bar |
| Navigation hiérarchique + 3 stratégies | 1–3 | `campaign_provider.dart`, `database_service.dart` | Drill-down regroups |
| Formulaires HTML WebView | 1–5 | `dynamic_form_widget.dart` | Pré-remplissage, radios, grille |
| Bypass SSL auto-signé | 1 | `api_service.dart` | `badCertificateCallback` |
| Pré-remplissage identification | 3–5, 11 | `data_entry_provider.dart` | `_prefillIdentificationFields()` |
| Grilles add-row | 5, 10 | `dynamic_form_widget.dart` | Clone ligne + incrément indices |
| Timeout adaptatif + retry × 3 | 12b, 17, 19 | `api_service.dart` | `_withRetry()`, délai progressif 5s |
| Envoi global établissement | 17 | `data_entry_provider.dart` | `sendAllFormsForSchool()` |
| Envoi global campagne | 17 | `data_entry_provider.dart`, `campaign_detail_screen.dart` | `sendAllFormsForCampaign()` |
| Suivi tentatives retry en UI | 19 | `school_data_screen.dart` | "Envoi… (tentative 2/3)" |
| Cohérence offline — debounce 800ms | 18 | `data_entry_provider.dart` | Déclenché à chaque champ |
| Cohérence offline — 7 déclencheurs | 17–21 | `data_entry_provider.dart` | Voir Slide 15 |
| Bannière violations offline | 46 | `school_data_screen.dart` | `_OfflineCoherenceBanner` |
| Drapeau Burundi + identité institutionnelle | 18, 21 | `pin_screen.dart` | République + Ministère |
| Contraste onglets Settings | 17 | `settings_screen.dart` | `labelColor` + `indicatorColor` |
| Moteur SQL réel (SqlTranslator) | 45–49 | `coherence_evaluator.dart` | CTE pivot + dual-path |

---

## SLIDE 22 — État du projet — Commits Git

### Historique des commits clés

| Commit | Branche | Description |
|--------|---------|-------------|
| `1db4be2` | `ak_main` | commit 17 : timeout, cohérence, envoi global |
| `d1a18a9` | `ak_secure` | commit 47+48 : HAVING fix (TEXT/INTEGER + ordre) |
| `20f0a41` | `ak_main` | commit 48 : mirror synchronisé |
| `d670850` | `ak_secure` | **commit 49 : dual-mode scalar/EXISTS wrapper** |
| `a7bda92` | `ak_main` | **commit 49 : mirror synchronisé** |

### Pull Request active
**PR #2** : `ak_main → main`  
URL : https://github.com/NasserKailou/stateduc_mobile/pull/2  
Titre : *"fix(coherence): commit 45-49 — moteur cohérence offline SQLite complet"*

---

## SLIDE 23 — Tests et validation

### Tests unitaires — `stateduc_flutter/test/sql_translator_test.dart`
- 15 tests unitaires du traducteur SQL (pur Dart, sans base de données)
- Couvre : substitution paramètres, extraction champs, CTE pivot, traduction syntaxique

### Validations Python SQLite
| commit | Tests | Résultat |
|---------|-------|---------|
| 47 | 4/4 | HAVING TEXT/INTEGER ✓ |
| 48 | 6/6 | Qualifier order + CTE diagnostic ✓ |
| 49 | 4/4 | Dual-mode SCALAR/EXISTS ✓ |

### Tests terrain
- APK debug sur appareil physique Android
- Établissement de test : `id_etab=20952, id_camp=2, CODE_TYPE_ANNEE=21`
- Règles testées : 483 (NB_LATRINES_ELEVES ≤ NB_LATRINES_BON_ETAT), 484 (variante féminine), 485

---

## SLIDE 24 — Pour le développeur IA — Points d'attention

### Ce qui fonctionne (ne pas modifier sans raison)
1. **CTE pivot** : `MAX(CASE WHEN UPPER(field_name)='X' THEN...)` — syntaxe validée
2. **`_stripContextOnlyHaving()`** : doit rester en step 7b (APRÈS suppression qualificateurs)
3. **Dual-mode wrapper** : détection `GROUP BY` — ne pas remplacer par autre logique
4. **`COALESCE((SELECT * FROM (...) _s), 0)`** : gère le cas NULL SQLite

### Limites connues et à traiter
- Le chemin **regex fallback** reste conservatif (pas de faux positifs mais peut rater des violations non SUM/EXISTS)
- Les vues `ELEVES_AGE_NIVEAU_SEXE` sont dans `_knownServerTables` mais non testées
- Les tests unitaires Flutter (`flutter test`) n'ont jamais été exécutés en CI

### Paramètres de connexion
```dart
// Dans data_entry_provider.dart — passer codeEtab + codeTypeAnnee à evaluate()
CoherenceEvaluator.evaluate(
  codeEtab: school.codeAdmin,       // ex: '20952'
  codeTypeAnnee: user.codeyear,     // ex: '21'
)
```

### Structure `collected_data` (schéma SQLite)
```
(id_camp TEXT, id_etab TEXT, id_qst TEXT, id_filter TEXT,
 field_name TEXT, field_value TEXT)
```
⚠️ **`field_value` est toujours TEXT** — nécessite `CAST(field_value AS REAL)` pour calculs

---

## SLIDE 25 — Roadmap et prochaines étapes

### Terminé ✅
- [x] Moteur cohérence offline complet (commit 45–49)
- [x] Fix NAT/Fortinet curl interne (commit 44)
- [x] Envoi global établissement + campagne (commit 17)
- [x] Retry automatique × 3 avec suivi UI (commit 19)
- [x] Bannière violations offline (commit 46)

### En cours / À valider
- [ ] Build APK de production avec commit 49 — vérifier règles 483/484/485 sur appareil
- [ ] Exécuter `flutter test` en CI (15 tests unitaires créés mais jamais lancés)

### Déployé en production (PHP)
- [ ] `config_app.php` + `data_save.php` + `data_reload.php` → copier vers XAMPP production
- [ ] `data_rules.php` + `data_controle.php` → copier vers XAMPP production
- [ ] Tester endpoint `/data_save.php/test/` → vérifier `SISED_AURL_INTERNAL` = `127.0.0.1:PORT`

---

*Fin du document — StatEduc Mobile — commit 1 à 49 — 2026-07-14*  
*Sources : `stateduc_flutter/CHANGELOG.md`, `StatEduc_burundi/CHANGELOG.md`, `stateduc_flutter/architecture_technique.md`, `recapitulatif.md`, `notepresentation.md`, `administration.md`*
