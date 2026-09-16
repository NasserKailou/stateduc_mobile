# SKILL : StatEduc Mobile — Système de Collecte Scolaire Flutter + PHP/ADODB

> **Skill réutilisable** extrait de l'intervention complète sur StatEduc Burundi (2026).
> Couvre l'architecture, les patterns de bugs connus, les corrections prouvées,
> et les templates prêts à l'emploi pour tout nouveau système similaire.
>
> **Sessions couvertes :** Sessions 1-19 (`ak_secure`) + Sessions 1-17 (`ak_app_ident`) — 20 août au 27 août 2026.
> **Deux applications :** `StatEduc_burundi` (PHP/Slim/ADODB/Access) + `app_fie` (PHP 8/PDO/Bootstrap 5)

---

## 🎯 Contexte d'application

Ce skill s'applique à tout système ayant cette topologie :

```
Application mobile Flutter  (stateduc_flutter)
        ↓ HTTP (Dio)
API REST PHP (Slim 2.x)     (StatEduc_burundi)
        ↓ ADODB
Base de données Access      (.mdb / .accdb)
        ↓ curl interne PHP→PHP
Moteur de formulaires       (questionnaire_ws.php)

Application web admin       (app_fie — PHP 8 / PDO / Bootstrap 5)
        ↓ HTTP/PDO
Base de données SQLite ou MySQL
        ↓ API HTTP
StatEduc_burundi            (syncNationalites, etab_hier, etc.)
```

**Cas typiques :** collecte de données scolaires, enquêtes statistiques nationales,
recensements sur terrain avec agents mobiles, systèmes pluriannuels avec sessions.

---

## 📁 Structure de ce Skill

```
skill-stateduc-mobile/
├── SKILL.md                          ← Ce fichier — point d'entrée
├── references/
│   ├── 01_architecture.md            ← Architecture complète du système
│   ├── 02_bugs_catalogue.md          ← Catalogue des bugs (25+ bugs) avec root cause
│   ├── 03_php_adodb_patterns.md      ← Patterns PHP/ADODB/Access
│   ├── 04_flutter_dio_patterns.md    ← Patterns Flutter/Dio
│   ├── 05_multiannee_pattern.md      ← Gestion pluriannuelle complète (4 phases)
│   ├── 06_git_workflow.md            ← Workflow Git avec GitHub App token
│   └── 07_app_fie_patterns.md        ← Patterns app_fie PHP 8/PDO/CSRF/CSS Burundi
├── templates/
│   ├── php/
│   │   ├── config_app_template.php   ← Détection port HTTP sécurisée
│   │   ├── common_session_guard.php  ← Guards session_start()
│   │   ├── data_save_curl.php        ← curl interne robuste
│   │   ├── annees_ws_template.php    ← Endpoint années actives
│   │   └── ws_route_template.php     ← Template route Slim REST
│   ├── dart/
│   │   ├── api_service_year.dart     ← fetchServerActiveYear() avec timeout 8s
│   │   ├── check_year_consistency.dart ← _checkYearConsistency() fail-open
│   │   ├── year_dropdown_widget.dart ← ExpansionTile années
│   │   └── year_confirm_dialog.dart  ← AlertDialog confirmation année
│   └── sql/
│       └── migration_multiannee.sql  ← Migration colonnes année
└── scripts/
    ├── syntax_check.py               ← Vérification accolades/parens Dart
    └── push_github.sh                ← Push avec x-access-token
```

---

## ⚡ Guide de démarrage rapide

### Situation 1 — Envoi de données bloqué avec 404

```
se_data: "404 : HTTP/1.1 404 Not Found"
```
→ **Lire** `references/02_bugs_catalogue.md` → Section **BUG-PORT-001**
→ **Appliquer** `templates/php/config_app_template.php` → fonction `_sised_local_port()`

### Situation 2 — Envoi de données bloqué avec timeout cURL 28

```
se_data: "28 : Operation timed out after 120000 milliseconds"
```
→ **Lire** `references/02_bugs_catalogue.md` → Section **BUG-CURL28-001**
→ **Appliquer** 3 corrections : memory_limit, session guard, CURLOPT_TIMEOUT

### Situation 3 — Envoi bloqué pendant 60 secondes

```
Logcat: [DioX] connectionTimeout uri=.../annees_ws.php/active/...
```
→ **Lire** `references/04_flutter_dio_patterns.md` → Section **FAIL-OPEN**
→ **Appliquer** `templates/dart/api_service_year.dart` + `templates/dart/check_year_consistency.dart`

### Situation 4 — Implémenter la gestion pluriannuelle

→ **Lire** `references/05_multiannee_pattern.md` en entier
→ Suivre les 4 phases dans l'ordre

### Situation 5 — Push GitHub échoue (token expiré)

→ **Lire** `references/06_git_workflow.md` → Section **TOKEN**
→ **Exécuter** `scripts/push_github.sh`

---

## 🔑 Règles impératives (à respecter sans exception)

1. **Tout port HTTP non-SSL est valide** pour le curl interne PHP→PHP.
   Ne jamais hardcoder une liste de ports. Exclure seulement 443 et 8443.

2. **`session_start()` doit toujours être protégé** par `session_status() === PHP_SESSION_NONE`
   dans tout fichier appelé par curl interne.

3. **`_checkYearConsistency()` doit être FAIL-OPEN** sur les erreurs réseau.
   Seul un mismatch CONFIRMÉ par le serveur doit bloquer l'envoi.

4. **`memory_limit` minimum 256M** pour `questionnaire_ws.php` (HTML + arbre ADODB).

5. **CURLOPT_TIMEOUT minimum 300s** pour les appels curl vers `questionnaire_ws.php`.

6. **Toute modification SQL doit être une migration** — jamais de DROP/RECREATE de table.

7. **ADODB sur Access** : toujours forcer `ADODB_FETCH_ASSOC` + `ADODB_ASSOC_CASE_UPPER`
   pour des clés de tableau prévisibles.

8. **Push GitHub** : toujours utiliser `x-access-token` format, jamais le credential store
   directement (les tokens GitHub App expirent en 1h).

9. **PHP 8 migration** : `ereg()` → `preg_match()`, constructeurs PHP4 → `__construct()`,
   `get_magic_quotes_gpc()` → no-op. Avant tout déploiement, scanner avec `grep -rn "ereg\|eregi"`.

10. **PDO SQLSTATE HY093** : ne jamais mélanger paramètres nommés (`:nom`) et positionnels (`?`)
    dans la même requête PDO.

11. **CSRF app_fie** : utiliser exclusivement `FIE_CSRF_TOKEN_NAME` + `getCsrfToken()`.
    Aucun literal string `'csrf_token'`, aucune méthode `SecurityHelper::csrfToken()`.

12. **Données Flutter corrompues `[5, text]`** : toujours sanitiser avec `_sanitizeStoredValue()`
    à la lecture depuis SQLite — les valeurs tableau `[val, type]` du serveur doivent être
    normalisées avant persistance ET à la lecture.

---

## 🔍 Guide de diagnostic rapide — Situations additionnelles

### Situation 6 — PHP Fatal Error : ereg() undefined function

→ **Lire** `references/07_app_fie_patterns.md` → Section **APP-FIE-001**
→ Remplacer toutes les `ereg()` par `preg_match()` (script `grep -rn "ereg"`)

### Situation 7 — PHP Fatal Error : PHP4 constructeurs

→ **Lire** `references/07_app_fie_patterns.md` → Section **APP-FIE-002**
→ Remplacer les constructeurs `function NomClasse()` par `function __construct()`

### Situation 8 — app_fie: SQLSTATE HY093 PDO

→ **Lire** `references/07_app_fie_patterns.md` → Section **APP-FIE-004**
→ Unifier les paramètres nommés/positionnels dans toutes les requêtes

### Situation 9 — Flutter: champs affichent `[5, text]`

→ **Lire** `references/02_bugs_catalogue.md` → Section **BUG-FLUTTER-S18** + **BUG-FLUTTER-S19**
→ Appliquer `_sanitizeStoredValue()` dans `database_service.dart`

### Situation 10 — Agents terrain : suppressions accidentelles campagnes/déconnexions

→ **Lire** `references/02_bugs_catalogue.md` → Sections **BUG-PILOTE-001/002/003**
→ Déplacer les actions destructives vers Paramètres uniquement
