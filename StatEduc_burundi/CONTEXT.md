# CONTEXT.md — StatEduc_burundi
> Fichier de référence pour toute nouvelle session IA sur ce projet.
> Mis à jour : 2026-08-15 | Branche active : `ak_app_ident`

---

## 1. Vue d'ensemble du projet

**StatEduc_burundi** est le système d'information de gestion de l'éducation (SIGE) du Burundi.
Il gère la collecte, la saisie et l'exploitation des données scolaires (établissements, élèves,
enseignants, annuaires, statistiques). C'est une application PHP legacy (PHP 7/8) basée sur
ADOdb, sans framework MVC.

**Dépôt GitHub :** `https://github.com/NasserKailou/stateduc_mobile`
*(StatEduc_burundi est un sous-dossier du repo `stateduc_mobile`)*

**Environnement cible :** XAMPP (Windows) avec Apache + MySQL.
Le projet tourne aussi sur serveurs Ubuntu avec Nginx en reverse-proxy devant Apache.

---

## 2. Arborescence du projet

```
StatEduc_burundi/
├── config.php              # Constantes SISED_PATH_* de base
├── config_app.php          # Constantes dynamiques ($SISED_PATH, $SISED_AURL, SISED_AURL_INTERNAL)
├── common.php              # Bootstrap principal : session, connexion DB, theme_manager, sécurité
├── params.php              # $GLOBALS['PARAM'] : abstraction des noms de tables/colonnes DB
├── questionnaire.php       # ⚠️ Page centrale de saisie des données — CRITIQUE
├── footer.php              # Pied de page (ligne 2408 : $.unblockUI() appelé ici)
├── api/
│   └── fie/
│       └── etabs_fie_ws.php  # ⚠️ API REST consommée par app_fie — CRITIQUE
├── moblogs/                # Logs de diagnostic (diag_questionnaire.log, etc.)
├── questionnaire/
│   ├── fr/                 # Templates questionnaire en français
│   └── eng/                # Templates questionnaire en anglais
├── client-side/
│   ├── css/                # Feuilles de style (jQuery UI, themes, plupload)
│   ├── js/                 # JavaScript client (jQuery, blockUI, menus, arbre)
│   └── image/              # Images statiques
└── server-side/
    ├── classes/
    │   ├── adodb/           # ⚠️ NE PAS MODIFIER — ORM ADOdb (vendeur)
    │   ├── affichage/       # Classes d'affichage HTML
    │   ├── arbre/           # Classe arbre (hiérarchie géographique)
    │   └── metier/          # ⚠️ Classes métier — modifier avec précaution
    │       ├── grille.class.php          # Grille de saisie (constructeur ligne 434)
    │       ├── theme_manager.class.php   # Gestion des thèmes de collecte
    │       ├── chaine.class.php          # Chaînes de regroupement géographique
    │       ├── user.class.php            # Gestion des utilisateurs
    │       └── ... (50+ classes)
    ├── include/
    │   ├── saisie_donnees/   # Fichiers inclus dans questionnaire.php
    │   └── ...               # Autres sous-modules (annuaire, administration, etc.)
    ├── instances/
    │   ├── instance_grille.php           # ⚠️ Instanciation de la grille de saisie — CRITIQUE
    │   └── instance_grille_reload_ws.php # Rechargement AJAX de la grille
    ├── lib/
    │   ├── adodb_xml/        # ADOdb XML
    │   ├── codeguy-Slim/     # Slim framework (web services)
    │   └── ... (autres libs vendeur)
    ├── sql/                  # Scripts SQL de création/migration
    └── templates/            # Templates HTML server-side
```

---

## 3. Stack technique

| Composant      | Valeur                              |
|----------------|-------------------------------------|
| **PHP**        | 7.4 → 8.x (migration en cours)     |
| **ORM DB**     | ADOdb (PHP) — NOT PDO               |
| **DB**         | MySQL (InnoDB, utf8mb4)             |
| **Serveur**    | Apache/XAMPP (dev) + Nginx/Apache (prod) |
| **Frontend**   | jQuery, jQuery UI, $.blockUI        |
| **PHP config** | `memory_limit 128M` (forcé dans questionnaire.php) |
| **Fetch mode** | `ADODB_FETCH_ASSOC` + `ADODB_ASSOC_CASE_UPPER` |

> ⚠️ **RÈGLE CRITIQUE ADOdb :** Toutes les clés de colonnes retournées par ADOdb
> sont en **MAJUSCULES** (ex : `$row['CODE_ETABLISSEMENT']`, jamais `$row['code_etablissement']`).
> Défini dans `common.php` : `define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER)`.

---

## 4. Variables globales et session critiques

| Variable | Rôle | Où initialisée |
|----------|------|----------------|
| `$GLOBALS['conn']` | Connexion ADOdb principale | `common.php` ligne ~81 |
| `$GLOBALS['PARAM']` | Abstraction noms tables/colonnes | `params.php` |
| `$GLOBALS['SISED_PATH']` | Chemin absolu racine projet | `config_app.php` |
| `$GLOBALS['_qr_theme_id_resolved']` | ID thème résolu passé de questionnaire.php à instance_grille.php | `questionnaire.php` |
| `$_SESSION['code_etab']` | Code de l'établissement sélectionné (persiste) | `questionnaire.php` |
| `$_SESSION['hierarchie_regroup']` | HTML de la hiérarchie géographique de l'établissement | `questionnaire.php` (bloc GET code_etab) |
| `$_SESSION['infos_etab']` | HTML des infos de l'établissement | `questionnaire.php` (fonction `get_infos_etab()`) |
| `$_SESSION['theme_manager']` | Objet theme_manager sérialisé | `questionnaire.php` (INTÉRIEUR du gate ligne 1263) |
| `$_SESSION['chaine']` | Code de la chaîne de regroupement | `questionnaire.php` |
| `$_SESSION['code_regroupement']` | Code du regroupement de l'établissement | `questionnaire.php` |
| `$SISED_AURL_INTERNAL` | URL interne pour cURL (bypass NAT/Fortinet) | `config_app.php` |

---

## 5. Fichiers CRITIQUES — règles d'intervention

### `questionnaire.php` ⚠️
**Ne jamais modifier sans comprendre :**
- **Gate ligne ~1263 :** `if((trim($_SESSION['hierarchie_regroup']) <> '') or (trim($_SESSION['infos_etab']) <> ''))`
  → Ce bloc conditionne l'affichage du "rappel" (infos établissement) ET l'assignment de `$_SESSION['theme_manager']`.
  → **Le `require_once $curfile` (instance_grille.php) est HORS de ce gate** (ligne ~1622) — la grille est toujours instanciée.
- **Bloc GET code_etab (~lignes 1126-1221) :** Peuple `$_SESSION['hierarchie_regroup']` et `$_SESSION['infos_etab']`.
  → N'est exécuté QUE si `$_GET['code_etab']` est présent dans l'URL.
- **Fix session repopulation (ajouté cette session) :** Bloc inséré AVANT le gate :
  → Si `$_SESSION['code_etab']` est en session mais `hierarchie_regroup`/`infos_etab` sont vides (reload sans GET code_etab),
    recharge les vars depuis la DB avant d'évaluer le gate.

### `server-side/instances/instance_grille.php` ⚠️
- Utilise `$GLOBALS['_qr_theme_id_resolved']` en fallback pour `$id_theme` si `$theme_manager->id === null`.
- Retourne immédiatement si `$id_theme` est vide (garde anti-crash).
- Constructeur `grille` signature : `__construct($code_etablissement, $code_annee, $id_theme, $id_systeme, $code_filtre="")`

### `api/fie/etabs_fie_ws.php` ⚠️
- API REST consommée par **app_fie** pour synchroniser les établissements.
- Authentification par token : lit `token.php` puis env `STATEDUC_FIE_TOKEN`. Si aucun → API ouverte.
- Fix localisation (cette session) : JOIN `TYPE_CHAINE_REGROUPEMENT` ajouté dans `fie_etabs_load_localisation_batch()`.
- Bootstrap ADOdb manuel en haut du fichier (pas de `common.php` — l'API est autonome).

### `server-side/classes/` ⚠️
- **NE PAS MODIFIER** `adodb/` — code vendeur.
- Modifier `metier/` avec précaution (classes utilisées partout).

---

## 6. Concepts métier clés

### Hiérarchie géographique (chaîne de regroupement)
```
Pays → Province → Commune → Zone → Colline → Établissement
```
- Table `HIERARCHIE` : NIVEAU_HIERARCHIE (1=pays, 2=province, 3=commune, 4=zone, 5=colline)
- Table `TYPE_CHAINE_REGROUPEMENT` (alias `TYPE_CHAINE_LOC` dans PARAM) : discriminant essentiel
  → **TOUJOURS joindre TYPE_CHAINE_REGROUPEMENT** dans les requêtes sur HIERARCHIE pour éviter
  les doublons quand un regroupement appartient à plusieurs chaînes.
- Niveaux dans `fie_etabs_build_loc_struct()` :
  - `niv_chaine['province']` → NIVEAU_HIERARCHIE de la province (typ. 2)
  - `niv_chaine['commune']`  → NIVEAU_HIERARCHIE de la commune  (typ. 3)
  - `niv_chaine['zone']`     → NIVEAU_HIERARCHIE de la zone     (typ. 4)

### Thème de collecte
- `DICO_THEME_SYSTEME` : table qui lie un thème à un type d'entité statistique (APPARTENANCE).
- `theme_manager->id` peut être `null` si aucune ligne ne correspond à l'APPARTENANCE donnée.
- `$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']` = nom réel de la table d'association établissement↔regroupement.

### blockUI / spinner
- `$.blockUI()` appelé au début du BODY (empêche interactions pendant chargement).
- `$.unblockUI()` appelé dans `footer.php` ligne 2408 (débloque après rendu complet).
- Fix spinner infini (session précédente) :
  - `register_shutdown_function` pour forcer `$.unblockUI()` même en cas d'erreur PHP.
  - Fallback JavaScript `window.addEventListener('load', ...)` avec timeout 8s.

---

## 7. Mapping PARAM (params.php) — tables clés

| Clé PARAM | Table réelle DB |
|-----------|----------------|
| `ETABLISSEMENT_REGROUPEMENT` | `ETABLISSEMENT_REGROUPEMENT` |
| `CODE_ETABLISSEMENT` | `CODE_ETABLISSEMENT` |
| `HIERARCHIE` | `HIERARCHIE` |
| `NIVEAU_CHAINE` | `NIVEAU_HIERARCHIE` |
| `TYPE_CHAINE_REGROUPEMENT` | `TYPE_CHAINE_LOC` |
| `REGROUPEMENT` | `REGROUPEMENT` |
| `TYPE_REGROUPEMENT` | `TYPE_REGROUPEMENT` |

---

## 8. Règles absolues

1. **NE JAMAIS modifier `server-side/classes/adodb/`** — code vendeur ADOdb.
2. **Toutes les clés ADOdb en MAJUSCULES** (`$row['NIVEAU_HIERARCHIE']`, pas `$row['niveau_hierarchie']`).
3. **Toujours joindre `TYPE_CHAINE_REGROUPEMENT`** dans les requêtes sur `HIERARCHIE` impliquant des niveaux géographiques.
4. **`require_once $curfile` est HORS du gate ligne 1263** — ne jamais le déplacer à l'intérieur.
5. **Le gate ligne 1263 conditionne `$_SESSION['theme_manager']`** — tout code dépendant de cette session var doit supposer qu'elle peut être absente.
6. **Ne pas hardcoder de ports ou d'URLs** dans les fichiers PHP — utiliser `$_SERVER['HTTP_HOST']` ou `$SISED_AURL_INTERNAL`.
7. **`memory_limit 128M`** : forcé en haut de `questionnaire.php` (la config PHP par défaut est insuffisante).
8. **Branche de travail : `ak_app_ident`** — ne jamais pusher directement sur `main`.
9. **Logs de diagnostic** : toujours écrire dans `moblogs/` avec `@file_put_contents` (@ pour ignorer erreurs de permission).

---

## 9. Conventions de commit

Format : `type(scope): description courte en français`

| Type | Usage |
|------|-------|
| `fix` | Correction de bug |
| `feat` | Nouvelle fonctionnalité |
| `refactor` | Réécriture sans changement de comportement |
| `docs` | Documentation uniquement |
| `chore` | Maintenance, nettoyage |

**Exemples :**
```
fix(questionnaire): repopulation session hierarchie_regroup quand reload sans GET code_etab
fix(etabs-ws): jointure TYPE_CHAINE_REGROUPEMENT manquante dans localisation batch
feat(api-fie): ajout pagination et filtre province dans etabs_fie_ws
```

**Workflow Git :**
1. `git add <fichiers>`
2. `git commit -m "type(scope): description"`
3. `git fetch origin main && git rebase origin/main` (sync avant PR)
4. Résoudre conflits en priorisant le code remote
5. `git reset --soft HEAD~N && git commit -m "message complet"` (squash si N commits)
6. `git push origin ak_app_ident --force` (force après rebase/squash)
7. Mettre à jour PR #4 : `https://github.com/NasserKailou/stateduc_mobile/pull/4`

---

## 10. État d'avancement au 2026-08-15

### ✅ Résolus (sessions précédentes + session courante)

| Problème | Commit | Statut |
|----------|--------|--------|
| `etabs_fie_ws.php` retournait 500 (bootstrap ADOdb manquant) | `a4bca2e` | ✅ Résolu |
| Spinner infini sur questionnaire.php | `b2c2836` | ✅ Résolu |
| `theme_manager->id` null → crash instance_grille | `b2c2836` | ✅ Résolu |
| Signature `syncFromApi()` incorrecte dans AdminController | `df8ed09` | ✅ Résolu |
| URL endpoint incorrecte dans StatEducApiClient | `df8ed09` | ✅ Résolu |
| **Formulaire ne s'affiche pas** (gate session vide au reload) | `df2e55a` | ✅ Résolu (à vérifier XAMPP) |
| **Sync "API StatEduc inaccessible"** (startSyncLog avant ping, URL hardcodée) | `df2e55a` | ✅ Résolu (à vérifier XAMPP) |
| **province/commune/zone = null** (JOIN TYPE_CHAINE_REGROUPEMENT manquant) | `df2e55a` | ✅ Résolu (à vérifier XAMPP) |

### ⏳ En attente de vérification (nécessite XAMPP)

- `questionnaire.php?theme=1102&type_ent_stat=1` → formulaire doit s'afficher sans `code_etab` en GET
- Sync depuis app_fie → doit afficher l'erreur réelle au lieu de "API inaccessible"
- `etabs_fie_ws.php?page=1&per_page=1` → JSON doit avoir `province`/`commune`/`zone` non-null

### 🔲 Non démarré / Hors périmètre actuel

- Tests unitaires / intégration
- Migration complète PHP 8 (warnings restants)
- Documentation API etabs_fie_ws (OpenAPI/Swagger)
- Performance : index DB sur tables HIERARCHIE, REGROUPEMENT

---

## 11. Diagnostic et débogage

### Logs disponibles
- `moblogs/diag_questionnaire.log` — repopulation session, gate évaluations
- Logs Apache/PHP : `error_log()` → `error_log` XAMPP ou `/var/log/apache2/error.log` prod
- `moblogs/` — tous les logs diagnostic ajoutés par les fixes

### Commandes de diagnostic rapide
```bash
# Tester l'API etabs_fie_ws directement
curl "http://localhost/StatEduc_burundi/api/fie/etabs_fie_ws.php?page=1&per_page=1"

# Vérifier les logs questionnaire
tail -f StatEduc_burundi/moblogs/diag_questionnaire.log

# Vérifier syntaxe PHP d'un fichier
php -l StatEduc_burundi/questionnaire.php
php -l StatEduc_burundi/api/fie/etabs_fie_ws.php
```

### Pièges connus
- **`ADODB_ASSOC_CASE` déjà défini** : constante définie dans deux endroits → warning PHP 8. Guard `if(!defined(...))` ajouté.
- **cURL interne via SISED_AURL_INTERNAL** : ne jamais cibler le port 443 (SSL invalide pour 127.0.0.1). Toujours cibler le port HTTP d'Apache (80/8080/8000/8888).
- **$.blockUI sans $.unblockUI** : si footer.php ne se charge pas (erreur PHP), le spinner reste bloqué. Le fallback `window.load` avec timeout 8s débloque automatiquement.
- **Valeurs null dans `etabs_fie_ws.php`** : toujours vérifier que les JOIN incluent `TYPE_CHAINE_REGROUPEMENT` pour les requêtes sur HIERARCHIE.
