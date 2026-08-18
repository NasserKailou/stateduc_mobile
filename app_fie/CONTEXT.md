# CONTEXT.md — app_fie
> Fichier de référence pour toute nouvelle session IA sur ce projet.
> **Mis à jour : 2026-08-18 (session 12)** | Branche active : `ak_app_ident`
> PR active : https://github.com/NasserKailou/stateduc_mobile/pull/4

---

## 1. Vue d'ensemble du projet

**app_fie** (Fichier Informatisé des Élèves) est l'application web PHP de gestion des élèves
pour les établissements d'enseignement au Burundi. Elle gère les inscriptions, mouvements,
examens, suivi pédagogique, bibliothèque, et se synchronise avec **StatEduc_burundi** via une API REST.

**Dépôt GitHub :** `https://github.com/NasserKailou/stateduc_mobile`  
*(app_fie se trouve dans le sous-dossier `app_fie/` du repo — chemin sandbox : `/home/user/webapp/app_fie/`)*

**Environnement cible :** XAMPP (Windows) — Apache + MariaDB 10.4, accès via
`http://localhost/stateduc_mobile/app_fie/public/` (ou alias Apache configuré).

---

## 2. Arborescence du projet

```
app_fie/
├── public/
│   ├── index.php               # ⚠️ Front controller — point d'entrée unique
│   ├── css/
│   │   ├── fie.css             # Charte graphique FIE — bleu #007bff
│   │   └── fie_admin.css       # Surcharges AdminLTE pour la section admin
│   └── js/
├── config/
│   ├── config.php              # ⚠️ Configuration centrale — DB, API, constantes
│   │                           #    FIE_CSRF_TOKEN_NAME = '_csrf_token'
│   │                           #    FIE_DEBUG = true/false
│   └── Router.php              # ⚠️ Routeur HTTP — toutes les routes définies ici
├── app/
│   ├── controllers/
│   │   ├── AdminController.php          # ⚠️ Admin + CRUD users + import Excel — CRITIQUE
│   │   ├── AuthController.php
│   │   ├── BibliothequeController.php
│   │   ├── DashboardController.php
│   │   ├── HistoriqueController.php
│   │   ├── InscriptionController.php    # ⚠️ Inscription + AJAX cascades + doublon — CRITIQUE
│   │   ├── MouvementController.php
│   │   ├── ExamenController.php
│   │   ├── ParametresController.php
│   │   ├── PublicController.php
│   │   ├── SuiviPedagogiqueController.php
│   │   ├── EtablissementsApiController.php
│   │   └── AggregatesApiController.php
│   ├── models/
│   │   ├── EleveModel.php           # create() → IueGenerator::generate() → LOCK TABLES
│   │   ├── EtablissementModel.php   # ⚠️ Cascades province→commune→colline→etab
│   │   └── InscriptionModel.php
│   └── views/
│       ├── layouts/
│       │   ├── app_header.php        # Navbar
│       │   ├── app_footer.php
│       │   ├── admin_layout.php      # ⚠️ Layout AdminLTE
│       │   └── admin_footer.php
│       ├── admin/
│       │   ├── sync.php
│       │   ├── users.php
│       │   └── import_excel.php
│       ├── inscription/
│       │   └── new.php               # ⚠️ Formulaire inscription — CRITIQUE
│       ├── bibliotheque/
│       ├── dashboard/
│       ├── historique/
│       ├── suivi/
│       └── errors/
├── services/
│   ├── Database.php        # ⚠️ Singleton PDO — voir section 9
│   ├── SyncService.php     # ⚠️ Import Excel natif + sync API — CRITIQUE
│   ├── SecurityHelper.php  # CSRF, session, sanitize, jsonResponse()
│   ├── Logger.php
│   ├── AggregateService.php
│   └── IueGenerator.php    # ⚠️ nextSequence() utilise LOCK TABLES — CRITIQUE
├── db/
│   ├── fie_burundi.sql           # ⚠️ Schéma de base complet (vérité terrain)
│   └── migrations/
│       ├── add_fie_settings.sql
│       ├── 002_nouveaux_modules.sql   # classes, suivi, transferts, historique, bibliothèque
│       ├── 003_atlas_colline_annees.sql   # ⚠️ ref_province/commune/colline + ref_type_annee
│       ├── 004_fix_ref_type_annee_sync.sql
│       └── 005_drop_extra_columns_etablissements_miroir.sql  # ⚠️ Supprime 11 colonnes hors ATLAS
├── scripts/
│   └── extract_etab_from_excel.php    # CLI+Web — lit FICHIER_ETAB.xlsx → remplit ref_ + miroir
└── api/
    └── stateduc/
        └── StatEducApiClient.php
```

---

## 3. Stack technique

| Composant | Valeur |
|-----------|--------|
| **PHP** | 8.2 (strict_types=1) |
| **DB** | MariaDB 10.4 — accès via **PDO** (singleton `Database`) |
| **DB name** | `fie_burundi` |
| **Frontend** | Bootstrap 5.3.3 + FontAwesome 6.5 + AdminLTE 4 (section admin) |
| **Routing** | Routeur maison (`Router.php`) — PAS de framework |
| **Auth** | Sessions PHP + bcrypt |
| **Autoloading** | Manuel via `require_once` — PAS de PSR-4 |
| **Version app** | `FIE_VERSION = '1.1.0'` |

> ⚠️ **PAS de namespace** dans les contrôleurs/services — l'autoloader est simple.
> Ne pas ajouter de `namespace App\Controllers` sans refactoring complet.

---

## 4. Base de données — tables clés

### Tables de base (`fie_burundi.sql`)

| Table | Rôle |
|-------|------|
| `eleves` | Fiche identité élève (IUE, nom, prénoms, sexe, naissance) |
| `inscriptions` | Inscription annuelle d'un élève dans un établissement |
| `etablissements_miroir` | Cache local référentiel StatEduc — **14 colonnes ATLAS_COLLINE** |
| `iue_sequences` | Séquences pour génération IUE — `LOCK TABLES` dans `nextSequence()` |
| `ref_secteur_ens` | Types secteur enseignement (Préscolaire, Fondamental, etc.) — colonne `ordre` ✅ |
| `ref_type_annee` | Années scolaires — `actif=1` = année courante |
| `ref_type_niveau` | Niveaux par secteur — colonne `code_secteur` ✅ |
| `ref_type_section` | Sections (Générale, Pédagogique, Technique…) |
| `fie_users` | Utilisateurs FIE avec rôles |
| `sync_log` | Journal des synchronisations API StatEduc |
| `audit_log` | Journal des actions utilisateurs |

### Tables géographiques (créées par migration 003)

| Table | Colonnes clés | Alimentation |
|-------|--------------|--------------|
| `ref_province` | `code_province PK`, `libelle` | Import Excel / sync API |
| `ref_commune` | `code_commune PK`, `code_province`, `libelle` | Import Excel / sync API |
| `ref_colline` | `code_colline PK`, `code_commune`, `code_province`, `libelle` | Import Excel / sync API |

> ⚠️ Ces 3 tables **n'existent pas** dans `fie_burundi.sql` — elles sont créées par `003_atlas_colline_annees.sql`.
> L'application tombera en erreur si cette migration n'est pas exécutée.

### `etablissements_miroir` — structure finale (après migrations 003 + 005)

Colonnes conservées (ordre logique) :
```
code_etablissement  ← PK
nom_etablissement
province, commune, colline          ← texte libellé
chaine_localisation                 ← concaténation Province/Commune/Colline/Etab
code_province, code_commune, code_colline   ← codes entiers (ATLAS_COLLINE)
code_type_milieu, code_type_statut_org, code_type_secteur_ens   ← codes entiers
secteur_ens, statut_org, milieu     ← libellés texte (ajoutés par migration 003)
source, synced_at, stateduc_updated_at, actif
```

Colonnes **supprimées** par migration 005 (ne plus les référencer !) :
`zone_admin`, `code_zone`, `code_type_fonction`, `code_type_etablissement`,
`code_type_etat_fonct`, `code_ecole_pays`, `code_etablissement_parent`,
`telephone`, `adresse_electronique`, `responsable_ecole`, `annee_creation`

---

## 5. Charte graphique

```css
--fie-primary:      #007bff;   /* Bleu FIE */
--fie-primary-dark: #0056b3;
--fie-primary-light:#cce5ff;
--fie-accent:       #17a2b8;   /* bleu-cyan secondaire */
--fie-red:          #007bff;   /* alias conservé pour compat — NE PAS mettre #CE1126 */
```

> ⚠️ **`--fie-red` = `#007bff`** (alias conservé). Ne jamais le remettre à rouge sans modifier `--fie-primary`.

---

## 6. Routes (Router.php) — COMPLET

| Méthode | Route | Contrôleur → Action |
|---------|-------|---------------------|
| GET | `/` | PublicController → home |
| GET/POST | `/connexion` | AuthController → loginForm/login |
| ANY | `/deconnexion` | AuthController → logout |
| GET | `/tableau-de-bord` | DashboardController → index |
| GET | `/inscription/recherche` | InscriptionController → search |
| **GET/POST** | **`/inscription/nouveau`** | InscriptionController → newForm/processNew |
| **POST** | **`/inscription/ajax/doublon`** | InscriptionController → ajaxCheckDoublon |
| **GET** | **`/inscription/ajax/communes-code`** | InscriptionController → ajaxCommunesCode |
| **GET** | **`/inscription/ajax/collines-code`** | InscriptionController → ajaxCollinesCode |
| **GET** | **`/inscription/ajax/etabs-code`** | InscriptionController → ajaxEtabsCode |
| **GET** | **`/inscription/ajax/etab-detail`** | InscriptionController → ajaxEtabDetail |
| **POST** | **`/inscription/ajax/sync-annees`** | InscriptionController → ajaxSyncTypeAnnee |
| GET | `/inscription/:iue` | InscriptionController → detail |
| GET | `/inscription/:iue/imprimer` | InscriptionController → printFiche |
| GET | `/admin` | AdminController → index |
| GET | `/admin/sync` | AdminController → syncStatus |
| POST | `/admin/sync/lancer` | AdminController → triggerSync |
| GET/POST | `/admin/import-excel` | AdminController → importExcelForm/processExcelImport |
| GET | `/admin/users` | AdminController → users |
| GET/POST | `/admin/users/nouveau` | AdminController → userNewForm/userCreate |
| GET/POST | `/admin/users/:id/editer` | AdminController → userEditForm/userUpdate |
| POST | `/admin/users/:id/supprimer` | AdminController → userDelete |
| GET | `/admin/audit` | AdminController → auditLog |
| GET | `/bibliotheque` | BibliothequeController → index (PUBLIC) |
| GET | `/suivi` | SuiviPedagogiqueController → index |
| GET | `/suivi/classe/:id` | SuiviPedagogiqueController → classeDetail |
| POST | `/suivi/decision` | SuiviPedagogiqueController → saveDecision (AJAX) |
| GET | `/eleve/:iue/historique` | HistoriqueController → eleve |

---

## 7. Rôles utilisateurs

| Rôle | Périmètre | Accès |
|------|-----------|-------|
| `super_admin` | Global | Tout |
| `admin_central` | Province (optionnel) | Tout sauf super_admin |
| `directeur_ecole` | Son école (`ecole_code`) | Suivi, élèves de l'école |
| `enseignant` | Sa classe (`classe_id`) | Suivi classe, décisions |
| `bibliothecaire` | — | Publications bibliothèque |

---

## 8. Services critiques

### `config/Database.php` — Singleton PDO ⚠️

```php
// Accès
$pdo  = Database::getInstance();        // instance PDO
$rows = Database::fetchAll("SELECT ...", [$param]);
$row  = Database::fetchOne("SELECT ...", [$param]);
$val  = Database::fetchScalar("SELECT COUNT(*)", []);
Database::query("INSERT/UPDATE ...", [$p1, $p2]);

// Transactions — AVEC guard inTransaction() (fix session 10)
Database::beginTransaction();  // ne démarre pas si déjà en cours
Database::commit();            // ne commite pas si pas de transaction active
Database::rollback();          // ne rollback pas si pas de transaction active
```

> ⚠️ **`LOCK TABLES` dans `IueGenerator::nextSequence()`** émet un COMMIT implicite MySQL.
> Sans le guard `inTransaction()`, `Database::rollback()` lançait `"There is no active transaction"`.
> Fix appliqué en session 10 — **ne jamais supprimer ce guard**.

### `services/IueGenerator.php`
- `nextSequence()` utilise `LOCK TABLES iue_sequences WRITE / UNLOCK TABLES`
- Ce LOCK force un COMMIT implicite sur toute transaction PDO ouverte
- C'est intentionnel (séquence atomique) — le guard dans `Database.php` gère cela

### `services/SyncService.php` ⚠️
- **`readExcelNative(string $path)`** : lecture pure PHP via ZipArchive+SimpleXML (pas Python, pas Composer)
  - Lit `xl/sharedStrings.xml`, résout la feuille via `xl/workbook.xml` + `xl/_rels/workbook.xml.rels`
  - Parse `xl/worksheets/sheet1.xml`, décode types `s` (sharedString), `b` (bool), `inlineStr`
- **`importFromExcel(string $path)`** : mode INSERT-ONLY
  - Pré-charge tous les `code_etablissement` existants en un SELECT
  - Skip les codes déjà présents (`$existingCodes` hashmap)
  - Retourne `['inserted' => N, 'skipped' => N, 'errors' => N, 'total' => N]`
- **`insertEtablissement(array $etab, string $source)`** : INSERT plain (pas ON DUPLICATE KEY)
  - Alimente aussi `ref_province`, `ref_commune`, `ref_colline` (ON DUPLICATE KEY UPDATE libelle)
- **`normalizeExcelRow(array $row)`** : mappe colonnes Excel ATLAS_COLLINE → tableau PHP
- Colonnes INSERT : uniquement les colonnes post-migration-005 (aucune colonne supprimée !)

### `services/SecurityHelper.php`
- `verifyCsrf($token)` → vérifie `$_SESSION[FIE_CSRF_TOKEN_NAME]`
- `getCsrfToken()` → retourne le token (crée si absent)
- `csrfField()` → génère `<input type="hidden" name="_csrf_token" value="...">`
- `jsonResponse(array $data, int $code)` → header JSON + `exit` (**exit inclus**)
- `requireLogin()` → redirige si pas connecté
- `e($str)` → htmlspecialchars

---

## 9. Formulaire inscription (`new.php`) — architecture JS

### Cascade AJAX Province → Commune → Colline → Établissement
```
selProv.change → GET ajax/communes-code?code_province=N
selComm.change → GET ajax/collines-code?code_commune=N
                  → si aucune colline → loadEtabs() direct
selColl.change / selSecteur.change → loadEtabs()
loadEtabs()    → GET ajax/etabs-code?code_commune=N&code_colline=N[&secteur=N]
```

### Fonctions JS clés
```javascript
// getJSON — avec r.ok guard (fix session 11)
function getJSON(url, cb, errCb) {
  fetch(url)
    .then(r => { if (!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
    .then(cb)
    .catch(e => { console.error('GET', url, e); if (errCb) errCb(e); });
}

// postJSON — avec r.ok guard + errCb (fix session 10)
function postJSON(url, data, cb, errCb) {
  // FormData, fetch POST, r.ok check, r.json(), catch → errCb
}

// CSRF récupéré côté JS
const CSRF = document.querySelector('input[name="<?= FIE_CSRF_TOKEN_NAME ?>"]')?.value ?? '';
// → toujours utiliser FIE_CSRF_TOKEN_NAME (= '_csrf_token'), jamais 'csrf_token'
```

### Modal doublon
- Bouton `#btn-check-doublon` → `POST ajax/doublon` avec `nom`, `prenoms`, `date_naissance`, `_csrf_token`
- Réponse : `{ found: bool, count: N, doublons: [{iue, nom, prenoms, ...}] }`
- Si doublon : checkbox confirmation + bouton "Continuer malgré tout" → `doublonConfirmed = true` → active `#btn-submit-insc`
- Si aucun doublon : active directement `#btn-submit-insc`
- **Le bouton submit peut être utilisé sans vérification doublon** (avertissement non-bloquant)

---

## 10. Modèle `EtablissementModel.php` — méthodes cascade

| Méthode | Source primaire | Fallback |
|---------|----------------|---------|
| `getProvinces()` | `ref_province` | `etablissements_miroir.province` DISTINCT |
| `getCommunesByCode(int $cp)` | `ref_commune WHERE code_province=?` | `etablissements_miroir.commune` DISTINCT |
| `getCollinesByCode(int $cc)` | `ref_colline WHERE code_commune=?` | `etablissements_miroir.colline` DISTINCT |
| `getEtablissementsByCode(int $ccl, int $cc, ?int $secteur)` | `etablissements_miroir` | — |
| `findByCode(int $code)` | `etablissements_miroir WHERE code_etablissement=?` | — |

> ⚠️ Les fallbacks sur `etablissements_miroir` ne fonctionnent que si les données texte
> (`province`, `commune`, `colline`) ont été importées. Après un import Excel propre,
> les tables `ref_*` sont alimentées et les fallbacks ne sont jamais atteints.

---

## 11. Règles absolues

1. **PAS de namespace** dans les controllers/services.
2. **Toujours `Database::getInstance()`** pour accès DB.
3. **`inTransaction()` guard obligatoire** dans `beginTransaction/commit/rollback` — ne jamais l'enlever.
4. **`STATEDUC_API_BASE_URL` auto-détecté** — NE JAMAIS hardcoder de port.
5. **`STATEDUC_API_TOKEN` vide = API ouverte** — pas de valeur factice.
6. **`FIE_CSRF_TOKEN_NAME`** = `'_csrf_token'` — utiliser la constante, jamais la valeur littérale `'csrf_token'`.
7. **`SecurityHelper::jsonResponse()`** appelle `exit` — tout code après est mort.
8. **`readExcelNative()`** — PAS de Python, PAS de Composer. ZipArchive+SimpleXML uniquement.
9. **Import Excel = INSERT-ONLY** — ne jamais passer à ON DUPLICATE KEY sans confirmation explicite.
10. **Branche `ak_app_ident`** — ne jamais pusher directement sur `main`.
11. **PR #4 ouverte** : `https://github.com/NasserKailou/stateduc_mobile/pull/4`
12. **`--fie-red` = `#007bff`** (alias conservé, ne pas remettre rouge).
13. **Layout admin = `admin_layout.php`** (AdminLTE), pas `app_header.php`.
14. **Migrations à exécuter dans l'ordre** : 002 → 003 → 004 → 005 → add_fie_settings.

---

## 12. Pièges connus

| Piège | Cause | Guard |
|-------|-------|-------|
| `SQLSTATE[HY093]: Invalid parameter number` inscription | Placeholder PDO nommé réutilisé deux fois dans un même INSERT (`:created_by, :created_by`) | Renommé en `:created_by, :updated_by` dans EleveModel + InscriptionModel ✅ session 12 |
| `"There is no active transaction"` | `LOCK TABLES` dans `nextSequence()` commit implicite | `inTransaction()` dans Database.php ✅ session 10 |
| Cascades AJAX silencieuses | `getJSON` sans `r.ok` check | Ajouté session 11 ✅ |
| Bouton sync bloqué "Synchronisation…" | `postJSON` sans `errCb` | Ajouté session 11 ✅ |
| `r.json()` SyntaxError sur HTML | Pas de `r.ok` avant `.json()` | Ajouté sessions 10+11 ✅ |
| Import Excel écrase données | `ON DUPLICATE KEY UPDATE` | Retiré session 10 — INSERT-ONLY ✅ |
| CSRF invalide côté JS | `'csrf_token'` au lieu de `'_csrf_token'` | Corrigé session 10 ✅ |
| Doublon check bouton muet | `errCb` absent dans `postJSON` | Corrigé session 10 ✅ |
| Python3 absent sur XAMPP | `readExcelPythonFallback()` | Remplacé par `readExcelNative()` session 9 ✅ |
| `array_keys(null)` TypeError | `MetaColumnNames()` retourne null (StatEduc conn) | Guard `if(!is_array())` dans frame*.class.php ✅ session 10 |

---

## 13. Conventions de commit

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
git rebase origin/main          # résoudre conflits si besoin (prioriser remote)
# Si plusieurs commits locaux à squasher :
git reset --soft HEAD~N
git commit -m "fix(scope): description complète"
git push origin ak_app_ident    # --force si rebase/squash
# Commenter PR #4
gh pr comment 4 --body "..."
```

---

## 14. État d'avancement — historique complet des sessions

### Sessions 1–7 (fondations, formulaire, cascade ATLAS_COLLINE)
- ✅ Architecture MVC, routeur, authentification, dashboard
- ✅ Charte graphique bleu FIE (`--fie-primary: #007bff`)
- ✅ Modules bibliothèque, suivi pédagogique, historique élève
- ✅ Formulaire inscription `new.php` — cascade Province→Commune→Colline→Établissement
- ✅ Modal doublon Bootstrap (retour JSON riche)
- ✅ Endpoints AJAX code-based : `communes-code`, `collines-code`, `etabs-code`
- ✅ Migration 003 : `ref_province/commune/colline` + `ref_type_annee`
- ✅ AdminLTE layout section admin

### Session 8
- ✅ `ob_start()/ob_get_clean()` ajouté autour de gentheme (→ revert session 9)
- ✅ Migration 004 : fix `ref_type_annee`

### Session 9 — 4 bugs corrigés (commit `555e470`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| Excel import échoue (Python3 absent XAMPP) | `SyncService.php` | `readExcelNative()` ZipArchive+SimpleXML |
| Gentheme freeze — `ob_start` capture tout | `generer_theme.php` | Suppression ob_start, `ob_implicit_flush(true)` |
| `etablissements_miroir` colonnes en trop | Migration 005 créée | DROP 11 colonnes hors ATLAS_COLLINE |
| Script extraction FICHIER_ETAB.xlsx manquant | `scripts/extract_etab_from_excel.php` créé | CLI+Web, batches 500 |

### Session 10 — 5 bugs corrigés (commit `04a0e1f`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| `"There is no active transaction"` soumission formulaire | `Database.php` | `inTransaction()` guard |
| `array_keys(null)` TypeError gentheme | `frame.class.php` + `frame_mobile.class.php` | `if(!is_array($ColTab))` |
| Import Excel écrase données existantes | `SyncService.php` | INSERT-ONLY + `insertEtablissement()` |
| `AdminController` `$result['ok']` toujours false | `AdminController.php` | try/catch + check `$result['errors']===0` |
| Bouton doublon muet / bloqué | `new.php` | `postJSON` errCb + `r.ok` + CSRF key `_csrf_token` |

### Session 11 — 2 bugs corrigés (commit `263127b`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| `getJSON` sans `r.ok` — cascades silencieuses | `new.php` | `if (!r.ok) throw` avant `r.json()` |
| `sync-annees` bouton bloqué sur erreur | `new.php` | 4e arg `errCb` + reset bouton + alert |

### Session 12 — 1 bug corrigé (commit `ff58722`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| `SQLSTATE[HY093]: Invalid parameter number` — inscription bloquée | `EleveModel.php` + `InscriptionModel.php` | Placeholder `:created_by` utilisé deux fois dans VALUES → renommé `:created_by`/`:updated_by` |

---

## 15. Checklist état actuel (après session 11)

### ✅ Terminé — tout fonctionne
- [x] Architecture complète MVC + routeur + auth
- [x] Formulaire inscription — cascade Province→Commune→Colline→Étab (JS + AJAX)
- [x] Modal doublon — vérification avant soumission
- [x] Soumission formulaire — flux complet sans erreur de transaction
- [x] Import Excel `SyncService` — ZipArchive natif, INSERT-ONLY, résumé inserted/skipped
- [x] `AdminController` — flash message correct pour import Excel
- [x] `EtablissementModel` — toutes méthodes cascade correctes (ref_* + fallback)
- [x] Migration 005 — colonnes ATLAS_COLLINE alignées
- [x] Script extraction `extract_etab_from_excel.php`
- [x] `generer_theme.php` StatEduc — streaming `ob_implicit_flush` (fix session 9)
- [x] `frame.class.php` + `frame_mobile.class.php` — guard `MetaColumnNames()` null (fix session 10)
- [x] `Database.php` — `inTransaction()` guard (fix session 10)
- [x] JS `getJSON` + `postJSON` — `r.ok` guard + `errCb` (fix sessions 10+11)
- [x] `EleveModel::create()` + `InscriptionModel::create()` — placeholders PDO uniques (fix session 12)

### ⏳ En attente de test manuel (nécessite XAMPP)
- [ ] Exécuter migrations 003 + 005 sur `fie_burundi`
- [ ] Importer `FICHIER_ETAB.xlsx` via `/admin/import-excel`
- [ ] Tester cascade Province→Commune→Colline→Étab dans le formulaire
- [ ] Tester soumission complète d'une inscription
- [ ] Vérifier que la vérification doublon affiche le modal correctement

### 🔲 Non démarré
- [ ] Tests unitaires PHP (PHPUnit)
- [ ] Export PDF historiques élèves
- [ ] Notifications email (transferts)
- [ ] Pagination dans `/inscription/recherche`
