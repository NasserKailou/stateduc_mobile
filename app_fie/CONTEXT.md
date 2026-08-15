# CONTEXT.md — app_fie
> Fichier de référence pour toute nouvelle session IA sur ce projet.
> Mis à jour : 2026-08-15 | Branche active : `ak_app_ident`

---

## 1. Vue d'ensemble du projet

**app_fie** (Fichier Informatisé des Élèves) est l'application web PHP de gestion des élèves
pour les établissements d'enseignement au Burundi. Elle gère les inscriptions, mouvements,
examens, et se synchronise avec **StatEduc_burundi** via une API REST.

**Dépôt GitHub :** `https://github.com/NasserKailou/stateduc_mobile`
*(app_fie est dans le sous-dossier `stateduc_mobile/app_fie/`)*

**PR active :** `https://github.com/NasserKailou/stateduc_mobile/pull/4`

**Environnement cible :** XAMPP (Windows) — Apache + MySQL, accès via
`http://localhost/stateduc_mobile/app_fie/public/` (ou alias Apache configuré).

**Écosystème complet :**
- `stateduc_mobile/app_fie/` — cette application (PHP, web)
- `stateduc_mobile/StatEduc_burundi/` — SIGE source des données établissements
- `stateduc_mobile/stateduc_flutter/` — application mobile Flutter (collecte terrain)

---

## 2. Arborescence du projet

```
stateduc_mobile/app_fie/
├── public/
│   ├── index.php           # ⚠️ Front controller — point d'entrée unique de l'app
│   ├── css/                # CSS public (Bootstrap 5, FontAwesome, custom)
│   └── js/                 # JS public (app.js, sync.js, etc.)
├── config/
│   ├── config.php          # ⚠️ Configuration centrale — DB, API, constantes
│   └── Router.php          # ⚠️ Routeur HTTP — toutes les routes définies ici
├── app/
│   ├── controllers/        # Contrôleurs HTTP
│   │   ├── AdminController.php        # ⚠️ Sync + admin — CRITIQUE
│   │   ├── AuthController.php         # Connexion/déconnexion
│   │   ├── DashboardController.php    # Tableau de bord
│   │   ├── InscriptionController.php  # Inscriptions élèves
│   │   ├── MouvementController.php    # Mouvements (transferts, abandons)
│   │   ├── ExamenController.php       # Examens
│   │   ├── ParametresController.php   # Paramètres application
│   │   ├── PublicController.php       # Site public (accueil, contact...)
│   │   ├── EtablissementsApiController.php  # API JSON établissements
│   │   └── AggregatesApiController.php      # API JSON agrégats
│   ├── models/
│   │   ├── EleveModel.php             # Modèle élève
│   │   ├── EtablissementModel.php     # Modèle établissement
│   │   └── InscriptionModel.php       # Modèle inscription
│   └── views/
│       ├── layouts/        # Templates maîtres (layout.php)
│       ├── admin/          # Vues admin (sync.php ⚠️, import-excel.php, users.php...)
│       ├── auth/           # Vues connexion
│       ├── dashboard/      # Vue tableau de bord
│       ├── inscription/    # Vues inscriptions
│       ├── mouvement/      # Vues mouvements
│       ├── examen/         # Vues examens
│       ├── public_site/    # Pages publiques (accueil, aide, contact...)
│       └── errors/         # Pages d'erreur (404, 403, 500)
├── services/
│   ├── SyncService.php     # ⚠️ Orchestrateur de synchronisation — CRITIQUE
│   ├── Database.php        # ⚠️ Singleton PDO — accès DB central
│   ├── Logger.php          # Logger PSR-like (fichier)
│   ├── SecurityHelper.php  # CSRF, session sécurisée, auth checks
│   ├── StatEducClient.php  # Client API StatEduc (wrapper haut niveau)
│   ├── AggregateService.php # Calcul agrégats statistiques
│   └── IueGenerator.php    # Génération IUE (Identifiant Unique Élève)
├── api/
│   ├── endpoints/          # Endpoints API internes (si applicable)
│   └── stateduc/
│       └── StatEducApiClient.php  # ⚠️ Client HTTP bas niveau → etabs_fie_ws.php
├── db/
│   └── migrations/
│       └── add_fie_settings.sql  # Migration : table fie_settings
└── docs/                   # Documentation interne
```

---

## 3. Stack technique

| Composant | Valeur |
|-----------|--------|
| **PHP** | 8.x (strict_types=1 dans la plupart des fichiers) |
| **DB** | MySQL — accès via **PDO** (singleton `Database`) |
| **DB name** | `fie_burundi` (défaut XAMPP) |
| **Frontend** | Bootstrap 5 + FontAwesome + jQuery |
| **Routing** | Routeur maison (`Router.php`) — PAS de framework |
| **Auth** | Sessions PHP + bcrypt (password_hash/verify) |
| **Autoloading** | Manuel via `require_once` — PAS de PSR-4/Composer autoload |
| **Version app** | `FIE_VERSION = '1.1.0'` |

> ⚠️ **PAS de namespace** dans les contrôleurs/services — l'autoloader est simple
> (`require_once BASE_PATH . '/app/controllers/FooController.php'`).
> Ne pas ajouter de `namespace App\Controllers` sans refactoring complet.

---

## 4. Configuration centrale (`config/config.php`)

### Constantes importantes

| Constante | Valeur / Origine | Rôle |
|-----------|-----------------|------|
| `BASE_PATH` | `dirname(__DIR__)` de `public/index.php` | Chemin absolu racine app_fie |
| `FIE_ROOT` | identique à `BASE_PATH` | Alias |
| `FIE_ENV` | `getenv('FIE_ENV') ?: 'development'` | Environnement |
| `DB_HOST` | `getenv('FIE_DB_HOST') ?: 'localhost'` | Hôte MySQL |
| `DB_NAME` | `getenv('FIE_DB_NAME') ?: 'fie_burundi'` | Base de données |
| `DB_USER` | `getenv('FIE_DB_USER') ?: 'root'` | Utilisateur MySQL (XAMPP) |
| `DB_PASS` | `getenv('FIE_DB_PASS') ?: ''` | Mot de passe MySQL (XAMPP = vide) |
| `STATEDUC_API_BASE_URL` | Auto-détecté depuis `HTTP_HOST` ou `getenv('STATEDUC_API_URL')` | URL base de StatEduc_burundi |
| `STATEDUC_API_TOKEN` | `getenv('STATEDUC_API_TOKEN') ?: ''` | Token API (vide = API ouverte) |
| `BASE_URL` | Auto depuis `HTTP_HOST` + `SCRIPT_NAME` | URL de base de app_fie |
| `FIE_BASE_URL` | `BASE_URL . '/'` | URL avec slash final |
| `MAX_LOGIN_ATTEMPTS` | 5 | Anti-brute force |
| `LOGIN_LOCKOUT_SECONDS` | 900 (15 min) | Durée de blocage |

### Règle URL StatEduc
```php
// URL auto-détectée — fonctionne quel que soit le port XAMPP
$STATEDUC_API_BASE_URL = 'http://localhost:PORT'  // PORT = celui d'Apache (8080, 80, etc.)

// Endpoint réel appelé par StatEducApiClient :
// {STATEDUC_API_BASE_URL}/StatEduc_burundi/api/fie/etabs_fie_ws.php
```

> ⚠️ **Ne jamais hardcoder `http://localhost:8085`** — utiliser l'auto-détection.

---

## 5. Routes (Router.php)

| Méthode | Route | Contrôleur → Action |
|---------|-------|---------------------|
| GET | `/` | PublicController → home |
| GET | `/aide`, `/contact`, `/confidentialite`, `/mentions-legales` | PublicController → * |
| GET/POST | `/connexion` | AuthController → loginForm / login |
| GET | `/deconnexion` | AuthController → logout |
| GET | `/tableau-de-bord`, `/dashboard` | DashboardController → index |
| GET | `/inscription` | InscriptionController → index |
| GET | `/inscription/recherche` | InscriptionController → search |
| GET/POST | `/inscription/nouveau` | InscriptionController → newForm / processNew |
| POST | `/inscription/ajax/doublon` | InscriptionController → ajaxCheckDoublon |
| GET | `/inscription/ajax/communes|zones|collines|etablissements` | InscriptionController → ajax* |
| GET | `/inscription/:iue` | InscriptionController → detail |
| GET | `/inscription/:iue/imprimer` | InscriptionController → printFiche |
| GET | `/mouvement`, `/mouvement/nouveau`, `/mouvement/:id` | MouvementController → * |
| POST | `/mouvement/nouveau` | MouvementController → processNew |
| GET | `/examen`, `/examen/nouveau`, `/examen/:id` | ExamenController → * |
| POST | `/examen/nouveau` | ExamenController → processNew |
| GET | `/admin` | AdminController → index |
| GET | `/admin/sync` | AdminController → syncStatus |
| **POST** | **`/admin/sync/lancer`** | **AdminController → triggerSync** ⚠️ |
| GET/POST | `/admin/import-excel` | AdminController → importExcelForm / processExcelImport |
| GET | `/admin/users` | AdminController → users |
| GET | `/admin/audit` | AdminController → auditLog |
| GET/POST | `/admin/parametres` | ParametresController → index / save |
| GET | `/api/agregats` | AggregatesApiController → index |
| GET | `/api/etablissements` | EtablissementsApiController → index |

---

## 6. Flux de synchronisation — CRITIQUE

```
Vue admin/sync.php
    │ JS lancerSync() — POST /admin/sync/lancer
    ▼
AdminController::triggerSync()
    │ instancie SyncService
    │ appelle syncFromApi($updatedSince, $secteur, $province, $username)
    ▼
SyncService::syncFromApi()
    ├── try { startSyncLog() } catch → warning, syncLogId = 0  ← non-bloquant
    ├── ping() → StatEducApiClient::ping()
    │       └── getEtablissements([per_page=>1, page=>1])
    │           └── GET {STATEDUC_API_BASE_URL}/StatEduc_burundi/api/fie/etabs_fie_ws.php
    │   Si ping() échoue → lastError exposé → RuntimeException "API inaccessible : {raison réelle}"
    ├── boucle pagination : getEtablissements(page++) → upsert DB
    ├── updateSyncLog() → non-bloquant (guard id<=0)
    └── finishSyncLog() → non-bloquant (guard id<=0)
    ▼
AdminController → retourne JSON {ok: true/false, message: "...", data: {...}}
    ▼
Vue sync.php → affiche résultat dans l'UI
```

### Paramètres `syncFromApi()`
```php
// Signature correcte (4 paramètres) :
public function syncFromApi(
    ?string $updatedSince,    // Date ISO 8601 ou null (sync complète)
    ?string $secteur,         // Filtre secteur ou null
    ?string $province,        // Filtre province ou null
    string  $triggeredBy      // Nom de l'utilisateur déclencheur
): array
```

> ⚠️ **Ne jamais appeler avec une signature différente.**
> AdminController::triggerSync() appelle exactement avec ces 4 paramètres.

---

## 7. Services critiques

### `services/Database.php` — Singleton PDO
```php
// Utilisation correcte :
$pdo = Database::getInstance();
$rows = Database::query("SELECT ...", [$param1, $param2]);
$row  = Database::fetchOne("SELECT ...", [$param]);
// NE PAS appeler en statique sur instance : $db->query() → erreur
```

### `services/SyncService.php` — Points de fragilité
- `startSyncLog()` peut échouer si la table `sync_log` n'existe pas → wrappé en try/catch
- `updateSyncLog()` / `finishSyncLog()` : guarded par `if ($id <= 0) return;`
- `ping()` expose l'erreur réelle via `StatEducApiClient::$lastError`

### `api/stateduc/StatEducApiClient.php` — Client HTTP
```php
public string $lastError = '';  // Contient le message d'erreur après ping() échoué

// Endpoint appelé :
$url = STATEDUC_API_BASE_URL . '/StatEduc_burundi/api/fie/etabs_fie_ws.php?' . http_build_query($params);

// ping() : appelle getEtablissements([per_page=>1, page=>1])
// Si exception → stocke dans lastError → retourne false
```

---

## 8. Base de données

### Tables principales

| Table | Rôle |
|-------|------|
| `eleves` | Données élèves (IUE, nom, prénom, date naissance...) |
| `inscriptions` | Inscriptions d'un élève dans un établissement/année |
| `etablissements` | Établissements synchronisés depuis StatEduc |
| `mouvements` | Transferts, abandons, réintégrations |
| `examens` | Résultats examens |
| `users` | Utilisateurs de l'application |
| `sync_log` | Journal des synchronisations (peut ne pas exister → guard en place) |
| `fie_settings` | Paramètres dynamiques (migration : `db/migrations/add_fie_settings.sql`) |
| `audit_log` | Journal d'audit des actions |

### Conventions SQL
- **Charset :** `utf8mb4` + `utf8mb4_unicode_ci`
- **Engine :** InnoDB
- **Timestamps :** `cree_le DATETIME DEFAULT CURRENT_TIMESTAMP`, `modifie_le DATETIME ON UPDATE CURRENT_TIMESTAMP`
- **Clés primaires :** `id INT UNSIGNED AUTO_INCREMENT`

---

## 9. Règles absolues

1. **PAS de namespace** dans les controllers/services (autoloader simple, pas PSR-4 complet).
2. **Toujours utiliser `Database::getInstance()`** pour les accès DB — NE PAS créer de nouvelle connexion PDO.
3. **`STATEDUC_API_BASE_URL` auto-détecté** — NE JAMAIS hardcoder de port ou d'URL fixe.
4. **`STATEDUC_API_TOKEN` vide = API ouverte** — ne pas mettre de valeur factice comme `'CHANGE_ME_IN_ENV'`.
5. **`startSyncLog()` doit rester non-bloquant** (try/catch) — une table `sync_log` absente ne doit pas bloquer la sync.
6. **Signature `syncFromApi()` : 4 paramètres** — ne pas modifier sans adapter AdminController.
7. **Branche de travail : `ak_app_ident`** — ne jamais pusher sur `main` directement.
8. **PR #4 doit rester ouverte** tant que le développement est en cours.
9. **Pas de `session_start()` dans `config.php`** — géré uniquement par `SecurityHelper::startSession()` dans `public/index.php`.
10. **Les vues ne doivent pas contenir de logique métier** — toute requête DB → modèle ou service.

---

## 10. Conventions de commit

Format : `type(scope): description courte en français`

| Type | Usage |
|------|-------|
| `fix` | Correction de bug |
| `feat` | Nouvelle fonctionnalité |
| `refactor` | Réécriture sans changement de comportement |
| `docs` | Documentation uniquement |
| `chore` | Maintenance, migrations, configuration |

**Exemples :**
```
fix(sync): startSyncLog non-bloquant + ping expose lastError
fix(config): STATEDUC_API_BASE_URL auto-détecté depuis HTTP_HOST
feat(admin): page synchronisation avec feedback temps réel
fix(router): route GET /inscription manquante
```

**Workflow Git :**
```bash
# 1. Commit des changements
git add <fichiers>
git commit -m "type(scope): description"

# 2. Sync avec remote avant PR
git fetch origin main
git rebase origin/main    # résoudre conflits si besoin (prioriser remote)

# 3. Squash si plusieurs commits locaux (N = nb commits à squasher)
git reset --soft HEAD~N
git commit -m "fix(scope): description complète de tous les changements"

# 4. Push (force si rebase/squash)
git push origin ak_app_ident --force

# 5. Mettre à jour PR #4
# https://github.com/NasserKailou/stateduc_mobile/pull/4
```

---

## 11. État d'avancement au 2026-08-15

### ✅ Résolus

| Problème | Commit | Statut |
|----------|--------|--------|
| 403 Forbidden (XAMPP sans alias Apache) | `8006a47` | ✅ |
| Routage XAMPP (BASE_URL mal calculée) | `8006a47` | ✅ |
| PDO : appels en méthode d'instance au lieu de statiques | `bd2bf1c` | ✅ |
| Site public BS5 (pages accueil, aide, contact, mentions) | `8006a47` / `43c7748` | ✅ |
| Hashes bcrypt réels dans le seed SQL | `43c7748` | ✅ |
| Colonnes `sync_log` manquantes + `statut` inscriptions | `1a07825` | ✅ |
| Signature `syncFromApi()` incorrecte dans AdminController | `df8ed09` | ✅ |
| URL endpoint StatEduc incorrecte dans StatEducApiClient | `df8ed09` | ✅ |
| **Sync "API StatEduc inaccessible"** (startSyncLog bloquant, URL hardcodée) | `df2e55a` | ✅ (à vérifier XAMPP) |
| province_code manquant dans les données renvoyées | `966a22e` | ✅ |

### ⏳ En attente de vérification (nécessite XAMPP)

- Sync depuis `/admin/sync` → doit afficher erreur réelle (ex: "connexion refused") au lieu de "API inaccessible"
- `STATEDUC_API_BASE_URL` auto-détecté correctement depuis le port XAMPP actuel
- Token vide → sync fonctionne sans authentification (API ouverte)

### 🔲 Non démarré / Hors périmètre actuel

- Tests unitaires PHP (PHPUnit)
- Authentification renforcée (2FA)
- Export PDF des fiches d'inscription
- Synchronisation bidirectionnelle (app_fie → StatEduc)
- Déploiement production (Docker / serveur Ubuntu)
- Application mobile Flutter (`stateduc_flutter/`) — en développement séparé

---

## 12. Écosystème — relation avec les autres projets

```
StatEduc_burundi  ──(API REST etabs_fie_ws.php)──►  app_fie
                                                        │
                                                        │ (données établissements)
                                                        ▼
                                               stateduc_flutter
                                               (application mobile Flutter)
```

### `stateduc_flutter/` (pour information)
- **Stack :** Flutter 3.35.4 + Dart 3.9.2
- **Rôle :** Collecte de données terrain (mobile)
- **Statut :** En développement, ne pas modifier sans coordination
- **Répertoire :** `stateduc_mobile/stateduc_flutter/`

---

## 13. Diagnostic et débogage

### Logs
- Logs PHP → Apache error log (XAMPP : `C:\xampp\apache\logs\error.log`)
- Logger app_fie → `services/Logger.php` (fichier log dans le dossier app)
- Sync diagnostic → réponse JSON de `/admin/sync/lancer` + champ `message`

### Commandes de diagnostic rapide
```bash
# Tester la connectivité avec StatEduc (depuis le serveur)
curl "http://localhost/StatEduc_burundi/api/fie/etabs_fie_ws.php?page=1&per_page=1"

# Vérifier syntaxe PHP
php -l stateduc_mobile/app_fie/config/config.php
php -l stateduc_mobile/app_fie/services/SyncService.php
php -l stateduc_mobile/app_fie/api/stateduc/StatEducApiClient.php
php -l stateduc_mobile/app_fie/app/controllers/AdminController.php

# Lancer sync manuellement (simuler la vue admin)
curl -X POST "http://localhost/stateduc_mobile/app_fie/public/admin/sync/lancer" \
     -d "secteur=&province=&updated_since="
```

### Pièges connus
- **`Database::query()` retourne `false` si requête échouée** — toujours vérifier.
- **`sync_log` peut ne pas exister** — `startSyncLog()` est wrappé en try/catch, `syncLogId` vaut 0.
- **`STATEDUC_API_TOKEN` vide** → l'API est ouverte (pas d'authentification). Ne pas confondre avec token invalide.
- **`ping()` utilise `per_page=1`** — si l'API retourne une liste vide (aucun établissement), `ping()` retourne `true` quand même (comportement voulu : API accessible = succès).
- **Autoloader simple** : si un fichier contrôleur n'est pas chargé, PHP lève "Class not found" et non une 404. Vérifier que `Router.php` charge correctement le contrôleur via `BASE_PATH`.
