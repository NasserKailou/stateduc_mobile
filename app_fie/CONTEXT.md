# CONTEXT.md — app_fie
> Fichier de référence pour toute nouvelle session IA sur ce projet.
> Mis à jour : 2026-08-18 (session 3) | Branche active : `ak_app_ident`

---

## 1. Vue d'ensemble du projet

**app_fie** (Fichier Informatisé des Élèves) est l'application web PHP de gestion des élèves
pour les établissements d'enseignement au Burundi. Elle gère les inscriptions, mouvements,
examens, suivi pédagogique, bibliothèque, et se synchronise avec **StatEduc_burundi** via une API REST.

**Dépôt GitHub :** `https://github.com/NasserKailou/stateduc_mobile`
*(app_fie est dans le sous-dossier `stateduc_mobile/app_fie/`)*

**PR active :** `https://github.com/NasserKailou/stateduc_mobile/pull/4`

**Environnement cible :** XAMPP (Windows) — Apache + MySQL, accès via
`http://localhost/stateduc_mobile/app_fie/public/` (ou alias Apache configuré).

---

## 2. Arborescence du projet

```
stateduc_mobile/app_fie/
├── public/
│   ├── index.php           # ⚠️ Front controller — point d'entrée unique
│   ├── css/
│   │   ├── fie.css         # ⚠️ Charte graphique FIE — bleu #007bff (plus rouge)
│   │   └── fie_admin.css   # Surcharges AdminLTE pour la section admin
│   └── js/
├── config/
│   ├── config.php          # ⚠️ Configuration centrale — DB, API, constantes
│   └── Router.php          # ⚠️ Routeur HTTP — toutes les routes définies ici
├── app/
│   ├── controllers/
│   │   ├── AdminController.php          # ⚠️ Admin + CRUD users — CRITIQUE
│   │   ├── AuthController.php
│   │   ├── BibliothequeController.php   # Mini-bibliothèque publique + admin
│   │   ├── DashboardController.php
│   │   ├── HistoriqueController.php     # Historique complet élève
│   │   ├── InscriptionController.php
│   │   ├── MouvementController.php
│   │   ├── ExamenController.php
│   │   ├── ParametresController.php
│   │   ├── PublicController.php
│   │   ├── SuiviPedagogiqueController.php  # Décisions fin d'année + transferts
│   │   ├── EtablissementsApiController.php
│   │   └── AggregatesApiController.php
│   ├── models/
│   │   ├── EleveModel.php
│   │   ├── EtablissementModel.php
│   │   └── InscriptionModel.php
│   └── views/
│       ├── layouts/
│       │   ├── header.php        # ⚠️ Navbar avec liens Bibliothèque + Suivi
│       │   ├── footer.php
│       │   ├── admin_layout.php  # ⚠️ Layout AdminLTE pour section admin
│       │   └── admin_footer.php  # Footer AdminLTE
│       ├── admin/
│       │   ├── index.php
│       │   ├── sync.php
│       │   ├── users.php
│       │   ├── user_form.php     # Formulaire CRUD utilisateurs avec nouveaux rôles
│       │   ├── audit.php
│       │   └── import_excel.php
│       ├── bibliotheque/
│       │   ├── index.php   # Vue publique (sans connexion)
│       │   ├── admin.php   # Gestion bibliothécaire/admin
│       │   └── new.php     # Formulaire publication
│       ├── dashboard/
│       │   └── index.php   # 8 KPI cards avec icônes FontAwesome
│       ├── historique/
│       │   └── eleve.php   # Frise chronologique complète
│       ├── suivi/
│       │   ├── index.php         # Liste classes par rôle
│       │   ├── classe.php        # Grille élèves + décisions AJAX
│       │   ├── transferts.php    # Liste transferts + actions
│       │   └── transfert_form.php # Formulaire demande transfert
│       ├── auth/
│       ├── inscription/
│       ├── mouvement/
│       ├── examen/
│       ├── public_site/
│       └── errors/
├── services/
│   ├── Database.php        # ⚠️ Singleton PDO
│   ├── SyncService.php     # ⚠️ Orchestrateur sync — CRITIQUE
│   ├── SecurityHelper.php
│   ├── Logger.php
│   ├── StatEducClient.php
│   ├── AggregateService.php
│   └── IueGenerator.php
├── db/
│   └── migrations/
│       ├── add_fie_settings.sql      # fie_settings
│       └── 002_nouveaux_modules.sql  # ⚠️ classes, suivi, transferts, historique, bibliothèque
└── uploads/
    └── bibliotheque/               # Fichiers uploadés (documents bibliothèque)
```

---

## 3. Stack technique

| Composant | Valeur |
|-----------|--------|
| **PHP** | 8.x (strict_types=1) |
| **DB** | MySQL — accès via **PDO** (singleton `Database`) |
| **DB name** | `fie_burundi` |
| **Frontend** | Bootstrap 5.3 + FontAwesome 6.5 + AdminLTE 4 (section admin) |
| **Routing** | Routeur maison (`Router.php`) — PAS de framework |
| **Auth** | Sessions PHP + bcrypt |
| **Autoloading** | Manuel via `require_once` — PAS de PSR-4 |
| **Version app** | `FIE_VERSION = '1.1.0'` |

> ⚠️ **PAS de namespace** dans les contrôleurs/services — l'autoloader est simple.
> Ne pas ajouter de `namespace App\Controllers` sans refactoring complet.

---

## 4. Charte graphique (Session 3 — MISE À JOUR)

### Couleurs
```css
:root {
  --fie-red:          #007bff;   /* conservé comme alias pour compat */
  --fie-primary:      #007bff;   /* BLEU ciel FIE (plus rouge) */
  --fie-primary-dark: #0056b3;
  --fie-primary-light:#cce5ff;
  --fie-accent:       #17a2b8;   /* bleu-cyan secondaire */
  --fie-accent-dark:  #117a8b;
}
```

> ⚠️ **`--fie-red` = `#007bff`** (alias conservé pour ne pas casser l'existant).
> Ne pas remettre `#CE1126` sans modifier aussi `--fie-primary`.

### KPI Cards Dashboard
8 cartes avec icônes FontAwesome dans `dashboard/index.php` :
- `fa-id-card` (Carte Élève Numérique FIE)
- `fa-users` (Élèves immatriculés)
- `fa-school` (Établissements couverts)
- `fa-shield-check` (Unicité garantie)
- `fa-map-location-dot` (Couverture nationale)
- `fa-venus` (Parité filles)
- `fa-cloud-arrow-up` (Agrégats en attente)
- `fa-fingerprint` (Unicité nationale)

---

## 5. Routes (Router.php) — COMPLET

| Méthode | Route | Contrôleur → Action |
|---------|-------|---------------------|
| GET | `/` | PublicController → home |
| GET | `/aide`, `/contact`, `/confidentialite`, `/mentions-legales` | PublicController → * |
| GET/POST | `/connexion` | AuthController → loginForm/login |
| ANY | `/deconnexion` | AuthController → logout |
| GET | `/tableau-de-bord`, `/dashboard` | DashboardController → index |
| GET | `/inscription` | InscriptionController → index |
| GET | `/inscription/recherche` | InscriptionController → search |
| GET/POST | `/inscription/nouveau` | InscriptionController → newForm/processNew |
| POST | `/inscription/ajax/doublon` | InscriptionController → ajaxCheckDoublon |
| GET | `/inscription/ajax/communes\|zones\|collines\|etablissements` | InscriptionController → ajax* |
| GET | `/inscription/:iue/imprimer` | InscriptionController → printFiche |
| GET | `/inscription/:iue` | InscriptionController → detail |
| GET | `/mouvement`, `/mouvement/nouveau`, `/mouvement/:id` | MouvementController |
| GET | `/examen`, `/examen/nouveau`, `/examen/:id` | ExamenController |
| GET | `/admin` | AdminController → index |
| GET | `/admin/sync` | AdminController → syncStatus |
| POST | `/admin/sync/lancer` | AdminController → triggerSync |
| GET/POST | `/admin/import-excel` | AdminController → importExcelForm/processExcelImport |
| GET | `/admin/users` | AdminController → users |
| GET/POST | `/admin/users/nouveau` | AdminController → userNewForm/userCreate |
| GET/POST | `/admin/users/:id/editer` | AdminController → userEditForm/userUpdate |
| POST | `/admin/users/:id/supprimer` | AdminController → userDelete |
| GET | `/admin/audit` | AdminController → auditLog |
| GET/POST | `/admin/parametres` | ParametresController |
| **GET** | **`/bibliotheque`** | BibliothequeController → index (PUBLIC) |
| **GET** | **`/bibliotheque/:id/telecharger`** | BibliothequeController → telecharger |
| **GET** | **`/bibliotheque/admin`** | BibliothequeController → adminIndex |
| **GET** | **`/bibliotheque/admin/nouveau`** | BibliothequeController → adminNewForm |
| **POST** | **`/bibliotheque/admin/publier`** | BibliothequeController → adminPublish |
| **POST** | **`/bibliotheque/admin/:id/statut/:statut`** | BibliothequeController → adminSetStatut |
| **POST** | **`/bibliotheque/admin/:id/supprimer`** | BibliothequeController → adminDelete |
| **GET** | **`/suivi`** | SuiviPedagogiqueController → index |
| **GET** | **`/suivi/classe/:id`** | SuiviPedagogiqueController → classeDetail |
| **POST** | **`/suivi/decision`** | SuiviPedagogiqueController → saveDecision (AJAX) |
| **GET** | **`/suivi/transferts`** | SuiviPedagogiqueController → transfertsList |
| **GET** | **`/suivi/transfert/nouveau`** | SuiviPedagogiqueController → transfertForm |
| **POST** | **`/suivi/transfert/demander`** | SuiviPedagogiqueController → transfertSubmit |
| **POST** | **`/suivi/transfert/:id/traiter`** | SuiviPedagogiqueController → transfertTraiter |
| **GET** | **`/eleve/:iue/historique`** | HistoriqueController → eleve |
| GET | `/api/agregats` | AggregatesApiController |
| GET | `/api/etablissements` | EtablissementsApiController |

---

## 6. Rôles utilisateurs

| Rôle | Périmètre | Accès |
|------|-----------|-------|
| `super_admin` | Global | Tout |
| `admin_central` | Province (optionnel) | Tout sauf super_admin |
| `directeur_ecole` | Son école (`ecole_code`) | Suivi, élèves de l'école |
| `enseignant` | Sa classe (`classe_id`) | Suivi classe, décisions |
| `bibliothecaire` | — | Publications bibliothèque |

### Colonnes supplémentaires `fie_users`
```sql
ecole_code     VARCHAR(20)  NULL  -- Code établissement (directeur/enseignant)
classe_id      INT UNSIGNED NULL  -- ID classe (enseignant)
nom_complet    VARCHAR(200) NULL  -- Prénom + Nom concaténé
```

---

## 7. Base de données — Tables session 3 (migration 002)

| Table | Rôle |
|-------|------|
| `classes` | Classes par école/année scolaire avec FK enseignant |
| `suivi_pedagogique` | Décisions fin d'année (passe/redouble/abandonne/en_attente) |
| `transferts_scolaires` | Demandes de transfert avec statuts workflow |
| `historique_eleve` | Journal complet tous événements (inscription→promotion→transfert…) |
| `bibliotheque_thematiques` | Thématiques avec icône FA, couleur, ordre |
| `bibliotheque_documents` | Documents avec FULLTEXT index, chemin fichier |
| `bibliotheque_tags` + `bibliotheque_document_tags` | Tags many-to-many |

---

## 8. Modules fonctionnels (session 3)

### Mini-bibliothèque publique
- **Accès** : sans connexion (public)
- **Upload** : bibliothécaire/admin uniquement, dossier `uploads/bibliotheque/`
- **Recherche** : FULLTEXT MySQL sur titre + description
- **Thématiques** : annales, manuels, guides_ens, legislation, formations, statistiques, autres

### Suivi pédagogique
- **Décisions** : `passe` / `redouble` / `abandonne` / `en_attente` par élève par classe
- **AJAX** : auto-save décision au clic sans rechargement de page
- **Transferts** : workflow `demande` → `approuve` → `execute` / `rejete`
- **Périmètre** : admin voit tout, directeur = son école, enseignant = sa classe

### Historique élève
- **Route** : `/eleve/:iue/historique`
- **Frise** : événements groupés par année scolaire, filtrables par type
- **Types** : inscription, reinscription, transfert_depart, transfert_arrivee, promotion, redoublement, abandon, examen, iue_emis, modification

### AdminLTE admin
- **Layout** : `admin_layout.php` + `admin_footer.php` (CDN AdminLTE 4.0-beta2)
- **CSS** : `fie_admin.css` (surcharges couleur bleu FIE)
- **Sidebar** : navigation complète admin (sync, users, bibliothèque, import, paramètres, audit)

---

## 9. Services critiques

### `services/Database.php` — Singleton PDO
```php
$pdo  = Database::getInstance();
$rows = Database::fetchAll("SELECT ...", [$param]);
$row  = Database::fetchOne("SELECT ...", [$param]);
$val  = Database::fetchScalar("SELECT COUNT(*) ...", []);
Database::query("INSERT/UPDATE/DELETE ...", [$p1, $p2]);
```

### `services/SyncService.php`
- **Signature** : `syncFromApi(?string $updatedSince, ?int $secteur, ?string $province, ?string $triggeredBy)`
- **Pas de guard de date** : sync disponible à tout moment
- `startSyncLog()` wrappé en try/catch (table `sync_log` peut ne pas exister)

---

## 10. Règles absolues

1. **PAS de namespace** dans les controllers/services (autoloader simple).
2. **Toujours `Database::getInstance()`** pour accès DB.
3. **`STATEDUC_API_BASE_URL` auto-détecté** — NE JAMAIS hardcoder de port.
4. **`STATEDUC_API_TOKEN` vide = API ouverte** — pas de valeur factice.
5. **Sync non-bloquante** — `startSyncLog()` en try/catch.
6. **Signature `syncFromApi()` : 4 paramètres** nommés.
7. **Branche `ak_app_ident`** — ne jamais pusher sur `main` directement.
8. **PR #4 ouverte** : `https://github.com/NasserKailou/stateduc_mobile/pull/4`
9. **Pas de `session_start()` dans `config.php`** — géré par `SecurityHelper::startSession()`.
10. **Vues sans logique métier** — requêtes DB → modèle ou service.
11. **`--fie-red` = `#007bff`** (alias conservé, ne pas remettre rouge).
12. **Layout admin = `admin_layout.php`** (AdminLTE), pas `header.php` pour les pages admin.

---

## 11. Conventions de commit

Format : `type(scope): description courte en français`

| Type | Usage |
|------|-------|
| `fix` | Correction de bug |
| `feat` | Nouvelle fonctionnalité |
| `refactor` | Réécriture sans changement de comportement |
| `docs` | Documentation uniquement |
| `chore` | Maintenance, migrations |

**Workflow Git :**
```bash
git add <fichiers>
git commit -m "type(scope): description"
git fetch origin main
git rebase origin/main    # résoudre conflits si besoin
git reset --soft HEAD~N   # squash si N commits locaux
git commit -m "fix(scope): description complète"
git push origin ak_app_ident --force
# Mettre à jour PR #4
```

---

## 12. État d'avancement au 2026-08-18 (session 3)

### ✅ Résolus (session 3)

| Module | Fichier(s) | Statut |
|--------|-----------|--------|
| Charte graphique rouge→bleu | `public/css/fie.css` | ✅ |
| KPI cards FA dashboard | `views/dashboard/index.php` | ✅ |
| Migration DB nouveaux modules | `db/migrations/002_nouveaux_modules.sql` | ✅ |
| Mini-bibliothèque (contrôleur + vues) | `BibliothequeController.php` + `views/bibliotheque/` | ✅ |
| Suivi pédagogique (contrôleur + vues) | `SuiviPedagogiqueController.php` + `views/suivi/` | ✅ |
| Historique élève complet | `HistoriqueController.php` + `views/historique/eleve.php` | ✅ |
| Router.php toutes routes | `config/Router.php` | ✅ |
| header.php liens Bibliothèque + Suivi | `views/layouts/header.php` | ✅ |
| AdminLTE layout admin | `views/layouts/admin_layout.php` + `fie_admin.css` | ✅ |
| Gestion users nouveaux rôles | `AdminController.php` + `views/admin/user_form.php` | ✅ |
| CONTEXT.md app_fie créé | `app_fie/CONTEXT.md` | ✅ |

### ⏳ En attente de vérification (nécessite XAMPP)

- Exécuter `002_nouveaux_modules.sql` sur la base `fie_burundi`
- Tester route `/bibliotheque` (sans connexion)
- Tester `/suivi` (connexion enseignant/directeur)
- Tester `/eleve/:iue/historique`
- Tester AdminLTE sur `/admin/users/nouveau`

### 🔲 Non démarré / Hors périmètre actuel

- `PublicController` : ajouter lien bibliothèque dans la page d'accueil publique
- Tests unitaires PHP (PHPUnit)
- Export PDF des historiques
- Notifications (email enseignant lors d'approbation transfert)
- Application mobile Flutter (`stateduc_flutter/`)
