# CONTEXT.md — StatEduc_burundi
> Fichier de référence pour toute nouvelle session IA sur ce projet.
> **Mis à jour : 2026-08-19 (session 14)** | Branche active : `ak_app_ident`
> PR active : https://github.com/NasserKailou/stateduc_mobile/pull/4

---

## 1. Vue d'ensemble du projet

**StatEduc_burundi** est le système d'information de gestion de l'éducation (SIGE) du Burundi.
Il gère la collecte, la saisie et l'exploitation des données scolaires (établissements, élèves,
enseignants, annuaires, statistiques). Application PHP legacy (PHP 7/8) basée sur ADOdb, sans framework MVC.

**Dépôt GitHub :** `https://github.com/NasserKailou/stateduc_mobile`  
*(StatEduc_burundi est dans le sous-dossier `StatEduc_burundi/` — chemin sandbox : `/home/user/webapp/StatEduc_burundi/`)*

**Environnement cible :** XAMPP (Windows) avec Apache + MariaDB.
Tourne aussi sur serveurs Ubuntu avec Nginx en reverse-proxy devant Apache.

---

## 2. Arborescence du projet

```
StatEduc_burundi/
├── config.php              # Constantes SISED_PATH_* de base
├── config_app.php          # Constantes dynamiques ($SISED_PATH, $SISED_AURL, SISED_AURL_INTERNAL)
├── common.php              # Bootstrap principal : session, connexion DB, theme_manager, sécurité
│                           # ⚠️ define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER) → clés MAJUSCULES
├── params.php              # $GLOBALS['PARAM'] : abstraction des noms de tables/colonnes DB
├── questionnaire.php       # ⚠️ Page centrale de saisie des données — CRITIQUE
├── footer.php              # $.unblockUI() appelé ici (ligne ~2408)
├── api/
│   └── fie/
│       └── etabs_fie_ws.php  # ⚠️ API REST consommée par app_fie — CRITIQUE
├── moblogs/                # Logs de diagnostic (fichiers .log)
├── client-side/
│   ├── css/
│   ├── js/
│   └── image/
└── server-side/
    ├── classes/
    │   ├── adodb/           # ⚠️ NE PAS MODIFIER — ORM ADOdb (code vendeur)
    │   ├── affichage/
    │   │   ├── frame.class.php          # ⚠️ MetaColumnNames() null guard (fix session 10)
    │   │   └── frame_mobile.class.php   # ⚠️ MetaColumnNames() null guard (fix session 10)
    │   └── metier/
    │       ├── grille.class.php          # ⚠️ Grille de saisie — constructeur critique
    │       ├── theme_manager.class.php
    │       └── ... (50+ classes)
    ├── include/
    │   └── administration/
    │       └── generer_theme.php   # ⚠️ Génération des thèmes — streaming fix session 9
    ├── instances/
    │   ├── instance_grille.php           # ⚠️ Instanciation grille — CRITIQUE
    │   └── instance_grille_reload_ws.php
    ├── lib/
    └── sql/
```

---

## 3. Stack technique

| Composant | Valeur |
|-----------|--------|
| **PHP** | 7.4 → 8.x (migration en cours) |
| **ORM DB** | ADOdb (PHP) — **NOT PDO** |
| **DB** | MySQL/MariaDB (InnoDB, utf8mb4) |
| **DB** | SQL Server aussi (connexion séparée pour certaines données StatEduc) |
| **Serveur** | Apache/XAMPP (dev) + Nginx/Apache (prod) |
| **Frontend** | jQuery, jQuery UI, $.blockUI |
| **PHP config** | `memory_limit 128M` forcé dans `questionnaire.php` |
| **Fetch mode** | `ADODB_FETCH_ASSOC` + `ADODB_ASSOC_CASE_UPPER` |

> ⚠️ **RÈGLE CRITIQUE ADOdb :** Toutes les clés de colonnes retournées par ADOdb
> sont en **MAJUSCULES**. Ex : `$row['CODE_ETABLISSEMENT']`, jamais `$row['code_etablissement']`.
> Défini dans `common.php` : `define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER)`.

---

## 4. Variables globales et session critiques

| Variable | Rôle | Où initialisée |
|----------|------|----------------|
| `$GLOBALS['conn']` | Connexion ADOdb principale (MySQL) | `common.php` ~ligne 81 |
| `$GLOBALS['PARAM']` | Abstraction noms tables/colonnes | `params.php` |
| `$GLOBALS['SISED_PATH']` | Chemin absolu racine projet | `config_app.php` |
| `$GLOBALS['_qr_theme_id_resolved']` | ID thème interne résolu (questionnaire → instance_grille) | `questionnaire.php` |
| `$_SESSION['code_etab']` | Code établissement sélectionné (persiste) | `questionnaire.php` |
| `$_SESSION['hierarchie_regroup']` | HTML hiérarchie géographique établissement | `questionnaire.php` bloc GET code_etab |
| `$_SESSION['infos_etab']` | HTML infos établissement | `questionnaire.php` `get_infos_etab()` |
| `$_SESSION['theme_manager']` | Objet theme_manager sérialisé | `questionnaire.php` INTÉRIEUR gate ligne ~1263 |
| `$_SESSION['chaine']` | Code chaîne regroupement | `questionnaire.php` |
| `$SISED_AURL_INTERNAL` | URL interne pour cURL (bypass NAT/Fortinet) | `config_app.php` |

---

## 5. Concept ABSOLUMENT CRITIQUE : `ID_THEME_SYSTEME` ≠ `ID` (DICO_THEME)

> **Ne jamais confondre ces deux identifiants — source historique de TOUS les bugs formulaire.**

| Variable | Valeur exemple | Signification |
|----------|---------------|---------------|
| `$_GET['theme']` | `9002` | **ID_THEME_SYSTEME** — identifiant visible dans menus/URL |
| `theme_manager->id` | `90` | **ID interne DICO_THEME** — identifiant dictionnaire |
| `DICO_THEME_SYSTEME.ID_THEME_SYSTEME` | `9002` | clé menu (= `$_GET['theme']`) |
| `DICO_THEME_SYSTEME.ID` | `90` | FK vers DICO_THEME.ID (= `theme_manager->id`) |

**Règle :** `get_dico()`, `grille->__construct()` et tout `SELECT ... FROM DICO_THEME WHERE ID=?`
prennent l'**ID interne** (ex: 90), jamais l'`ID_THEME_SYSTEME` (ex: 9002).

---

## 6. Fichiers CRITIQUES — règles d'intervention

### `questionnaire.php` ⚠️

**Ne jamais modifier sans comprendre :**

- **Gate ligne ~1263 :** `if((trim($_SESSION['hierarchie_regroup']) <> '') or (trim($_SESSION['infos_etab']) <> ''))`
  - Ce bloc conditionne l'affichage du "rappel" (infos établissement) ET `$_SESSION['theme_manager']`.
  - **`require_once $curfile` (instance_grille.php) est HORS de ce gate** (ligne ~1622) — la grille est toujours instanciée.

- **Bloc GET code_etab (~lignes 1126–1221) :** Peuple `$_SESSION['hierarchie_regroup']` et `$_SESSION['infos_etab']`.
  - N'est exécuté QUE si `$_GET['code_etab']` est présent dans l'URL.

- **Fix session repopulation :** Bloc inséré AVANT le gate :
  - Si `$_SESSION['code_etab']` est en session mais `hierarchie_regroup`/`infos_etab` sont vides (reload sans GET code_etab), recharge depuis DB avant d'évaluer le gate.

- **Résolution `$_qr_theme_id` (ID interne) — 3 niveaux :**
  1. `theme_manager->id` (cas nominal, APPARTENANCE correspond)
  2. Parcours de `$theme_manager->list[]` sans filtre APPARTENANCE
  3. `SELECT D_T_S.ID FROM DICO_THEME_SYSTEME WHERE ID_THEME_SYSTEME=$_GET['theme'] AND ID_SYSTEME=$secteur`
     → ⚠️ **`D_T_S.ID` est l'ID interne**, pas `D_T_S.ID_THEME_SYSTEME`
  - **Passe 3b (fix session 3) :** sans filtre `AND ID_SYSTEME` si passe 3 échoue aussi

### `server-side/classes/metier/grille.class.php` → `get_dico()` ⚠️

- La requête FRAME filtre sur `B.APPARTENANCE = type_ent_stat`.
- **Fix (session 2) :** Si FRAME = NULL avec filtre APPARTENANCE → passe 2 sans filtre APPARTENANCE.
- Constructeur `grille` signature :
  ```php
  __construct($code_etablissement, $code_annee, $id_theme_INTERNE, $id_systeme, $code_filtre="")
  ```

### `server-side/instances/instance_grille.php` ⚠️

- Utilise `$GLOBALS['_qr_theme_id_resolved']` (ID interne) en priorité, fallback sur `$theme_manager->id`.
- Guard : retourne si `$id_theme` vide (message clair).
- Guard (session 2) : retourne si `$curobj_grille->template` vide après instanciation.

### `server-side/include/administration/generer_theme.php` ⚠️

**Fix session 9 — streaming (1 456 itérations du constructeur `frame`) :**
```php
// En haut du fichier — OBLIGATOIRE pour streaming
ignore_user_abort(true);
@ini_set('zlib.output_compression', '0');
@ini_set('output_buffering', '0');
while (ob_get_level() > 0) { @ob_end_flush(); }
ob_implicit_flush(true);
// Après chaque bloc de génération :
@ob_flush(); @flush();
```

> ⚠️ **NE PAS remettre `ob_start()/ob_get_clean()`** autour de `new frame(...)`.
> Avec 1 456 itérations, le constructeur prend plusieurs secondes — l'ob_start capturait
> toute la sortie et le navigateur ne voyait rien jusqu'à timeout.

### `server-side/classes/affichage/frame.class.php` et `frame_mobile.class.php` ⚠️

**Fix session 10 — `MetaColumnNames()` peut retourner null :**
```php
// À la ligne ~462 (frame) et ~687 (frame_mobile)
$ColTab = $this->conn->MetaColumnNames($table_nomenclature);
// FIX: MetaColumnNames() retourne null/false si la table n'existe pas dans la connexion
// (connexion SQL Server via ADODB_ASSOC_CASE_UPPER) — array_keys(null) = TypeError PHP8
if (!is_array($ColTab)) { $ColTab = array(); }
$champs = array_keys($ColTab);
```

> ⚠️ **Ne jamais supprimer ce guard.** La connexion SQL Server peut ne pas avoir accès
> à certaines tables de nomenclature — ce cas est normal en production.

### `api/fie/etabs_fie_ws.php` ⚠️

- API REST consommée par **app_fie** pour synchroniser les établissements.
- Authentification par token : lit `token.php` puis env `STATEDUC_FIE_TOKEN`. Si aucun → API ouverte.
- Bootstrap ADOdb manuel en haut du fichier (pas de `common.php` — l'API est autonome).
- Fix localisation : JOIN `TYPE_CHAINE_REGROUPEMENT` dans `fie_etabs_load_localisation_batch()`.

---

## 7. Concepts métier clés

### Hiérarchie géographique (chaîne de regroupement)
```
Pays → Province → Commune → Zone → Colline → Établissement
```
- Table `HIERARCHIE` : `NIVEAU_HIERARCHIE` (1=pays, 2=province, 3=commune, 4=zone, 5=colline)
- Table `TYPE_CHAINE_REGROUPEMENT` (alias `TYPE_CHAINE_LOC` dans PARAM) : discriminant essentiel
- **TOUJOURS joindre `TYPE_CHAINE_REGROUPEMENT`** dans les requêtes sur `HIERARCHIE` pour éviter les doublons.
- Niveaux dans `fie_etabs_build_loc_struct()` :
  - `niv_chaine['province']` → NIVEAU_HIERARCHIE de la province (typ. 2)
  - `niv_chaine['commune']`  → NIVEAU_HIERARCHIE de la commune  (typ. 3)
  - `niv_chaine['zone']`     → NIVEAU_HIERARCHIE de la zone     (typ. 4)

### Thème de collecte
- `DICO_THEME_SYSTEME` lie un thème à un type d'entité statistique (APPARTENANCE).
- `theme_manager->id` peut être `null` si aucune ligne ne correspond à l'APPARTENANCE — guard obligatoire.
- `$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT']` = nom réel de la table d'association établissement↔regroupement.

### blockUI / spinner
- `$.blockUI()` appelé au début du BODY.
- `$.unblockUI()` dans `footer.php` ligne ~2408.
- Fallback JS : `window.addEventListener('load', ...)` avec timeout 8s si footer.php ne se charge pas.

---

## 8. Mapping PARAM (params.php) — tables clés

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

## 9. Règles absolues

1. **NE JAMAIS modifier `server-side/classes/adodb/`** — code vendeur ADOdb.
2. **Toutes les clés ADOdb en MAJUSCULES** (`$row['NIVEAU_HIERARCHIE']`, pas `$row['niveau_hierarchie']`).
3. **Toujours joindre `TYPE_CHAINE_REGROUPEMENT`** dans les requêtes sur `HIERARCHIE` impliquant des niveaux géographiques.
4. **`require_once $curfile` est HORS du gate ligne ~1263** — ne jamais le déplacer à l'intérieur.
5. **Le gate ligne ~1263 conditionne `$_SESSION['theme_manager']`** — tout code dépendant de cette session var doit supposer qu'elle peut être absente.
6. **Ne pas hardcoder de ports ou d'URLs** — utiliser `$_SERVER['HTTP_HOST']` ou `$SISED_AURL_INTERNAL`.
7. **`memory_limit 128M`** : forcé en haut de `questionnaire.php` — ne pas retirer.
8. **Branche de travail : `ak_app_ident`** — ne jamais pusher directement sur `main`.
9. **Logs de diagnostic** : écrire dans `moblogs/` avec `@file_put_contents`.
10. **NE PAS remettre `ob_start()`** dans `generer_theme.php` — fix streaming sessions 9 obligatoire.
11. **Guard `if (!is_array($ColTab))`** dans `frame.class.php` et `frame_mobile.class.php` — ne jamais supprimer.

---

## 10. Pièges connus

| Piège | Cause | Guard / Fix |
|-------|-------|-------------|
| `array_keys(null)` TypeError PHP8 gentheme | `MetaColumnNames()` retourne null (table absente connexion SQL Server) | `if(!is_array($ColTab))` dans frame*.class.php ✅ session 10 |
| `gentheme` spinner infini / timeout | `ob_start` capture 1 456 itérations `frame` | Supprimé, `ob_implicit_flush(true)` ✅ session 9 |
| Formulaire absent (thème 9002) | `ID_THEME_SYSTEME ≠ ID` confusion | 3 passes de résolution + Passe 3b sans filtre ID_SYSTEME ✅ session 3 |
| FRAME = NULL | Filtre APPARTENANCE trop strict | Passe 2 sans filtre APPARTENANCE ✅ session 2 |
| Gate ligne ~1263 jamais vrai au reload | `hierarchie_regroup` vide sans `GET code_etab` | Repopulation depuis DB avant gate ✅ session 2 |
| Spinner infini blocUI | `footer.php` ne se charge pas | Fallback `window.load` timeout 8s ✅ |
| `ADODB_ASSOC_CASE` déjà défini | Constante définie deux fois | Guard `if(!defined(...))` dans common.php ✅ |
| cURL interne SSL 51 | Port 443 ciblé sur 127.0.0.1 | Exclure port 443, forcer HTTP ✅ |
| province/commune/zone null dans API | JOIN `TYPE_CHAINE_REGROUPEMENT` manquant | Ajouté dans `fie_etabs_load_localisation_batch()` ✅ |
| E_PARSE `instance_grille.php` | Backslash-apostrophes `\'` | Corrigé session 3 ✅ |

---

## 11. Conventions de commit

Format : `type(scope): description courte en français`

**Workflow Git :**
```bash
git add <fichiers>
git commit -m "type(scope): description"
git fetch origin main && git rebase origin/main
# Résoudre conflits en priorisant remote
git reset --soft HEAD~N && git commit -m "message complet"  # squash si N commits
git push origin ak_app_ident  # --force si rebase/squash
gh pr comment 4 --body "..."
```

---

## 12. Diagnostic et débogage

### Logs disponibles
```
moblogs/diag_questionnaire.log   # repopulation session, résolution thème, gate
moblogs/diag_grille.log          # constructeur grille, FRAME, template
```

### Mots clés à rechercher dans les logs
| Mot clé | Signification |
|---------|--------------|
| `INFO_THEME_L1_RESOLVED` | Thème résolu via passe 1 (theme_manager->id) |
| `INFO_THEME_L2_RESOLVED` | Thème résolu via passe 2 (list[] sans APPARTENANCE) |
| `INFO_THEME_L3a_RESOLVED` | Thème résolu via passe 3 (SELECT avec ID_SYSTEME) |
| `INFO_THEME_L3b_RESOLVED` | Thème résolu via passe 3b (SELECT sans ID_SYSTEME) |
| `FRAME_FALLBACK2` | FRAME obtenu sans filtre APPARTENANCE |
| `ERR_TEMPLATE_VIDE` | FRAME trouvé mais template vide — vérifier DICO_THEME |
| `ERR_THEME_INTROUVABLE` | Aucune des 3 passes n'a résolu le thème |

### Commandes rapides
```bash
# Tester l'API etabs directement
curl "http://localhost/StatEduc_burundi/api/fie/etabs_fie_ws.php?page=1&per_page=1"

# Vérifier syntaxe PHP
php -l StatEduc_burundi/server-side/include/administration/generer_theme.php
php -l StatEduc_burundi/server-side/classes/affichage/frame.class.php
php -l StatEduc_burundi/api/fie/etabs_fie_ws.php

# Suivre les logs questionnaire
tail -f StatEduc_burundi/moblogs/diag_questionnaire.log
```

---

## 13. État d'avancement — historique complet des sessions

### Sessions 1–2 (fixes fondamentaux)
| Bug | Fix | Statut |
|-----|-----|--------|
| `etabs_fie_ws.php` 500 (bootstrap ADOdb manquant) | Bootstrap ADOdb ajouté | ✅ |
| Spinner infini questionnaire | `register_shutdown_function` + fallback `window.load` 8s | ✅ |
| `theme_manager->id` null → crash | Guard dans instance_grille | ✅ |
| Gate ligne 1263 jamais vrai au reload | Repopulation session depuis DB | ✅ |
| FRAME NULL (filtre APPARTENANCE trop strict) | Passe 2 sans APPARTENANCE | ✅ |
| province/commune/zone null API | JOIN `TYPE_CHAINE_REGROUPEMENT` | ✅ |
| Sync "API inaccessible" dans app_fie | URL endpoint corrigée | ✅ |

### Session 3
| Bug | Fix | Statut |
|-----|-----|--------|
| E_PARSE `instance_grille.php` (backslash-apostrophes) | Corrigé | ✅ (valider XAMPP) |
| ERR_THEME_INTROUVABLE thème 9002 type_ent_stat=1 | Passe 3b sans filtre ID_SYSTEME | ✅ (valider XAMPP) |

### Session 8
| Bug | Fix | Statut |
|-----|-----|--------|
| gentheme lent (ob_start ajouté par erreur) | ob_start enveloppant frame — à revert session 9 | ❌ → revert |

### Session 9 — 2 bugs StatEduc corrigés (commit `555e470`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| gentheme freeze / timeout navigateur | `generer_theme.php` | Suppression ob_start + `ob_implicit_flush(true)` + @ob_flush/@flush après chaque bloc |
| (Sync réseau inaccessible — non-code) | — | Problème serveur StatEduc, pas corrigible côté code |

### Session 10 — 2 bugs StatEduc corrigés (commit `04a0e1f`)
| Bug | Fichier | Fix |
|-----|---------|-----|
| `array_keys(null)` TypeError PHP8 gentheme | `frame.class.php` ligne 462 | `if (!is_array($ColTab)) { $ColTab = array(); }` |
| même bug | `frame_mobile.class.php` ligne 687 | même guard |

### Session 11 — audit complet (aucun nouveau bug StatEduc)
- ✅ Lecture complète `frame.class.php` et `frame_mobile.class.php` — guards en place, conformes
- ✅ `generer_theme.php` — streaming correct, ob_start absent confirmé

---

## 14. Checklist état actuel (après session 11)

### ✅ Terminé — fonctionnel
- [x] `questionnaire.php` — résolution thème 3 passes + passe 3b
- [x] `questionnaire.php` — repopulation session au reload
- [x] `grille.class.php` — passe 2 FRAME sans filtre APPARTENANCE
- [x] `instance_grille.php` — guard thème vide + guard template vide
- [x] `generer_theme.php` — streaming ob_implicit_flush (plus de timeout)
- [x] `frame.class.php` — guard MetaColumnNames() null
- [x] `frame_mobile.class.php` — guard MetaColumnNames() null
- [x] `etabs_fie_ws.php` — bootstrap ADOdb + JOIN TYPE_CHAINE_REGROUPEMENT
- [x] `common.php` — guard ADODB_ASSOC_CASE déjà défini

### ⏳ En attente de test manuel (XAMPP)
- [ ] `questionnaire.php?theme=9002&code_etab=21422&type_ent_stat=2` → formulaire doit s'afficher
- [ ] `administration.php?val=gentheme` → ne doit plus freezer, affichage progressif
- [ ] Chercher `INFO_THEME_L3b_RESOLVED` dans `moblogs/diag_questionnaire.log`
- [ ] `etabs_fie_ws.php?page=1&per_page=1` → JSON avec `province`/`commune`/`zone` non-null

### 🔲 Non démarré
- [ ] Tests unitaires / intégration
- [ ] Migration complète PHP 8 (warnings restants)
- [ ] Documentation API `etabs_fie_ws` (OpenAPI/Swagger)
- [ ] Index DB sur tables HIERARCHIE, REGROUPEMENT (performance)

---

## SESSION 14 (2026-08-19) — Migration PHP 7→8

### Fichiers corrigés

| Fichier | Correction |
|---|---|
| `server-side/instances/instance_grille.php` | 16 `ereg()` → `preg_match()/preg_quote()` |
| `server-side/lib/fonctions.inc.php` | `manage_magic_quotes()` → no-op PHP 8 (get_magic_quotes_gpc supprimé) |
| `server-side/classes/metier/aggregated_db_structure.class.php` | 1 `ereg()` → `preg_match()` |
| `server-side/include/outils_integres/load_sql.php` | 5 `ereg()` → `preg_match()` |
| `server-side/include/outils_integres/defaut_nomenc_syst.php` | 1 `ereg()` → `preg_match()` |
| `server-side/include/outils_integres/export_grille.php` | 1 `ereg()` → `preg_match()` |
| `server-side/include/saisie_donnees/import_excel.php` | 2 `ereg()` → `preg_match()` |
| `server-side/include/olap_tools/frame_gestion_olap_inst.php` | `eregi()` → `preg_match()` |
| `server-side/include/olap_tools/frame_gestion_olap_tabm.php` | `eregi()` → `preg_match()` |
| `server-side/include/olap_tools/gestion_olap_import_cube.php` | `eregi()` → `preg_match()` |
| `server-side/include/olap_tools/popup_olap_import_un_cube.php` | `eregi()` → `preg_match()` |
| `server-side/lib/fpdf.inc.php` | `FPDF()` → `__construct()` |
| `server-side/lib/htmlparser.inc.php` | 4 PHP4 constructors → `__construct()` |
| `server-side/lib/pclzip.lib.php` | `PclZip()` → `__construct()` |
| `server-side/lib/pdftable.inc.php` | `PDFTable()` → `__construct()` |
| `server-side/lib/sms.inc.php` | `SmsSender()` → `__construct()` |
| `server-side/lib/adodb_xml/class.ADODB_XML.php` | `ADODB_XML()` → `__construct()` |
| `server-side/lib/adodb_xml/class.xml.php` | 2 constructors → `__construct()` |
| `server-side/include/saisie_donnees/oleread.inc.php` | `OLERead()` → `__construct()` |
| `server-side/include/saisie_donnees/reader.php` | `Spreadsheet_Excel_Reader()` → `__construct()` |

### Fichiers NON encore corrigés (PHP 8 — warnings non bloquants)
- `gestion_zone.classold.php` — fichier `.classold`, non chargé en production
- `questionnaire.php`/`questionnaire_ws.php` — `split()` = JavaScript (non PHP), ignoré
- `frame.class.php`, `gestion_zone.class.php` — `each()` = JavaScript embarqué dans PHP, non bloquant
- `controle.inc.php` — `ereg()` sur lignes complexes multilignes (patterns de dates) — nécessite vérification manuelle
- `gestion_table_simple.class - Copie.php` — fichier de backup (non actif)
