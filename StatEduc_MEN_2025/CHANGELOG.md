# StatEduc MEN 2025 — Journal des travaux (Serveur PHP)

Branche de développement : `ak_main`  
Dépôt : `https://github.com/NasserKailou/stateduc_mobile`  
Pull Request ouverte : [PR #1](https://github.com/NasserKailou/stateduc_mobile/pull/1)

---

## ℹ️ Session 17 — 2026-06-03 — Session Flutter uniquement (aucun changement PHP)

> **Aucune modification apportée aux fichiers PHP côté serveur lors de la session 17.**

La session 17 a porté exclusivement sur l'application mobile Flutter :

| Correction / Ajout | Impact côté serveur |
|--------------------|---------------------|
| `sendTimeout` Dio 120 s → 300 s | Aucun — le serveur ne voit pas les timeouts client |
| Cohérence offline re-déclenchée (règles arrivées + ouverture formulaire) | Aucun — exploite les règles déjà retournées par `data_rules.php` |
| `sendAllFormsForSchool()` | Aucun — utilise l'endpoint `data_save.php` existant, appels multiples normaux |
| `sendAllFormsForCampaign()` + `getDistinctEtabQstWithData()` | Aucun — même endpoint, multiples appels séquentiels |
| `_autoReloadFromServerBackground(forceOverwrite: true)` pour identification | Aucun — utilise `data_reload.php` existant, comportement GET inchangé |
| Settings `TabBar` : couleurs explicites | Aucun — UI locale uniquement |

**Commit Flutter** : `1db4be2` — `feat(session17): timeout, cohérence offline, envoi global, identification, settings`  
**Commit PHP** : *néant* — aucun fichier `StatEduc_MEN_2025/` modifié en session 17.

---

## Table des matières

1. [Architecture REST — Slim v2](#1-architecture-rest--slim-v2)
2. [Correction — page administration.php?val=param](#2-correction--page-administrationphpvalparam)
3. [Correction — AJAX gestion_base_service.php](#3-correction--ajax-gestion_base_servicephp)
4. [Correction — switch de connexion (toggle_sources)](#4-correction--switch-de-connexion-toggle_sources)
5. [Nouveau endpoint — data_save.php (route mobile)](#5-nouveau-endpoint--data_savephp-route-mobile)
6. [Nouveau endpoint — data_controle.php](#6-nouveau-endpoint--data_controlephp)
7. [Nouveau endpoint — data_rules.php](#7-nouveau-endpoint--data_rulesphp)
8. [Classes métier — contrôle de cohérence](#8-classes-métier--contrôle-de-cohérence)
9. [common.php — configuration des connexions](#9-commonphp--configuration-des-connexions)
10. [Stratégie de session pour l'app mobile](#10-stratégie-de-session-pour-lapp-mobile)

---

## 1. Architecture REST — Slim v2

### Framework
Tous les endpoints REST utilisent le micro-framework **Slim v2**. Chaque fichier PHP instancie `new \Slim\Slim()` et déclare ses routes.

### Règle Slim importante
> **Les routes plus spécifiques doivent être déclarées AVANT les routes génériques.**

Exemple dans `data_save.php` :
```php
// Route étendue (mobile, avec id_annee) — déclarée EN PREMIER
$app->post('/theme_save/:user/.../:id_annee', function(...) { ... });

// Route standard (web, sans id_annee) — déclarée EN SECOND
$app->post('/theme_save/:user/...', function(...) { ... });
```
Si la route générique est déclarée en premier, Slim la matche avant la route spécifique, et le paramètre `id_annee` n'est jamais capturé.

### Pattern de réponse standard
Tous les endpoints retournent le même enveloppe JSON :
```json
{
  "se_status":  200,
  "se_message": "OK",
  "se_data":    { ... }
}
```
- `se_status 200` = succès
- `se_status 400` = erreur métier (données manquantes, établissement non autorisé, etc.)
- `se_status 101` = erreur (utilisé dans gestion_base_service.php)

---

## 2. Correction — page `administration.php?val=param`

### Symptôme
Quand on naviguait vers `administration.php?val=param`, la page affichait du texte brut comme :
```
SELECT LIBELLE, CODE_LIBELLE FROM DICO_LIBELLE_PAGE WHERE CODE_LANGUE='fr';
{"se_statut":200, ...}
```
Et la page ne se rendu pas correctement.

### Cause racine — Bug 1 : ADOdb debug mode
**Fichier** : `StatEduc_MEN_2025/common.php`, ligne 626 (branche `postgres9`)

```php
// AVANT — actif, causait l'affichage SQL dans des <pre> :
$conn_dico = ADONewConnection('postgres9');
$conn_dico->debug = true;

// APRÈS — commenté :
$conn_dico = ADONewConnection('postgres9');
//$conn_dico->debug = true;
```

**Mécanisme** : Quand `$conn->debug = true` est actif, ADOdb (via `_adodb_debug_execute()` dans `adodb-lib.inc.php`) enveloppe **chaque requête SQL** dans des balises `<pre align=left>…</pre>` et les envoie directement sur la sortie HTTP — avant même que le HTML de la page soit émis. Résultat : toutes les requêtes SQL exécutées par `lit_libelles_page()` s'affichent en clair en tête de page.

### Détails techniques
- `lit_libelles_page()` dans `server-side/lib/fonctions.inc.php` (lignes 118–133) utilise `$GLOBALS['conn_dico']->GetAll()` pour charger les libellés
- Cette connexion `conn_dico` est créée dans `common.php` en cas de connexion via la branche `postgres9`
- Le `debug = true` était actif dans cette branche (et **seulement** cette branche), contrairement aux commentaires dans `connexion.class.php` où il était déjà commenté

---

## 3. Correction — AJAX `gestion_base_service.php`

### Symptôme
Quand la page `administration.php?val=param` se chargeait, une alerte JavaScript s'affichait avec :
```
SELECT LIBELLE... {"se_statut":200,...}
```
C'est-à-dire du SQL concatené avec le JSON de réponse.

### Cause racine — Bug 2 : pollution de sortie JSON
**Fichier** : `StatEduc_MEN_2025/server-side/include/administration/gestion_base_service.php`

L'endpoint AJAX jQuery (`dataType:'json'`) recevait une réponse corrompue : le JSON était précédé du texte SQL généré par ADOdb debug. jQuery ne pouvait pas parser le JSON → le callback `error:` s'activait → `alert(XMLHttpRequest.responseText)` affichait la réponse brute.

### Correction
Ajout du pattern **output buffering** :

```php
// En tête du fichier :
<?php
ob_start();  // Capture toute sortie parasite avant le JSON

header('Content-type: application/json');
require_once '../../../common.php';

// Dans chaque helper d'envoi :
function sendList($liste) {
    ob_clean();  // Vide le buffer (supprime SQL debug ou warnings PHP)
    $posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>$liste);
    echo json_encode($posts);
}

function sendError($message) {
    ob_clean();
    $posts = array('se_statut'=>101,'se_message'=>$message,'se_datas'=>NULL);
    echo json_encode($posts);
}

function sendOk() {
    ob_clean();
    $posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>'ok');
    echo json_encode($posts);
}
```

**Principe** : `ob_start()` intercepte toute sortie (SQL debug, notices PHP) avant qu'elle parte vers le client. `ob_clean()` vide ce buffer juste avant d'émettre le JSON → garantit une réponse JSON pure.

---

## 4. Correction — switch de connexion (`toggle_sources`)

### Symptôme
Basculer entre les connexions disponibles (bouton dans `administration.php?val=param`) ne fonctionnait pas — la page ne se rechargait pas correctement.

### Cause racine — Bug 3 : conséquence directe des bugs 1 et 2
La logique JavaScript de `toggle_sources()` dans `info_param.php` (ligne 202) est :
```javascript
function toggle_sources(serveur) {
    location.href = '?val=param&serveur=' + serveur;
}
```
Et côté PHP (lignes 61–63 de `info_param.php`) :
```php
if (isset($_GET['serveur'])) {
    $active['type'] = $_GET['serveur'];
}
```
Cette logique est **correcte**. Le problème était que la sortie SQL debug en tête de page cassait le rendu HTML, rendant les boutons dysfonctionnels.

**Correction** : En corrigeant les bugs 1 et 2, le switch de connexion fonctionne automatiquement.

---

## 5. Nouveau endpoint — `data_save.php` (route mobile)

### Problème initial
L'application mobile envoyait des données de formulaire mais elles n'étaient pas sauvegardées. Cause : la route Slim standard `POST /theme_save/...` utilise `$_SESSION['annee']` pour récupérer le code d'année scolaire, mais l'app mobile ne maintient pas de session PHP (pas de cookie de session).

### Solution : route étendue avec `id_annee`

**Fichier** : `StatEduc_MEN_2025/data_save.php`

#### Nouvelle route (ligne 172)
```php
// Route étendue (app mobile) — inclut id_annee pour fonctionner sans session navigateur
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee',
  function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee)
  use (...) {
    // Injecte id_annee dans la session si elle est vide
    if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') {
        $_SESSION['annee'] = $id_annee;
    }
    theme_save_handler(..., $id_annee, ...);
  }
);
```

#### Refactoring : extraction de `theme_save_handler()`
Pour éviter la duplication de code entre les deux routes, la logique de sauvegarde a été extraite dans une fonction `theme_save_handler()`. Les deux routes appellent cette même fonction.

#### Quatre fixes dans `theme_save_handler()`
1. **`getSurveyStatus`** : passe maintenant `$id_year` au lieu de `$_SESSION['annee']`
2. **`success` closure** : capture `$id_year` via `use (..., $id_year, ...)`
3. **`saveLogInfo`** dans le succès : passe `$id_year`
4. **`error` closure** : capture `$id_year` pour le log d'erreur

#### Route standard (ligne 179)
La route originale est conservée pour la compatibilité avec le navigateur web :
```php
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start',
  function (...) use (...) {
    $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '';
    theme_save_handler(..., $id_annee, ...);
  }
);
```

---

## 6. Nouveau endpoint — `data_controle.php`

### Objectif
Permettre à l'application mobile d'effectuer un **contrôle de cohérence des données** après chaque sauvegarde, en utilisant les règles définies dans `DICO_REGLE_THEME` et `DICO_REGLE_THEME_ASSOC`.

### Fichier créé : `StatEduc_MEN_2025/data_controle.php`

### Route
```
GET /data_controle.php/theme_controle/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
```

### Paramètres
| Paramètre | Description |
|---|---|
| `user` | Login utilisateur (vérification d'accès à la campagne) |
| `id_camp` | Identifiant de la campagne |
| `id_sector` | Identifiant du secteur (système éducatif) |
| `id_theme` | Identifiant du thème / formulaire |
| `id_etab` | Code établissement |
| `id_filter` | Période de filtre (ou `"null"`) |
| `id_annee` | Code d'année scolaire (injecté dans `$_SESSION['annee']`) |

### Réponse JSON
```json
{
  "se_status": 200,
  "se_message": "OK",
  "se_data": {
    "nb_erreurs": 2,
    "erreurs": [
      {
        "id_regle": 12,
        "id_regle_assoc": 7,
        "message": "Effectif total ≠ somme tranches d'âge : 120 > 98"
      }
    ]
  }
}
```

### Choix de la classe `controle_theme_batch`
La classe `controle_theme.class.php` (méthode `controle_regles_theme()`) génère du HTML et du JavaScript directement dans la réponse — incompatible avec une API REST JSON.

La classe `controle_theme_batch.class.php` stocke les violations dans un tableau PHP (`$tab_regles_theme_assoc_not_ok`) **sans émettre de sortie**. C'est cette classe qui est utilisée.

### Contrôle d'accès
Avant d'exécuter le contrôle, l'endpoint vérifie que l'établissement appartient bien à un regroupement autorisé pour cet utilisateur et cette campagne (requête sur `DICO_FIXE_REGROUPEMENT`).

### Compatibilité
- PHP 7.3.4 garanti (pas de syntaxe PHP 8+)
- Slim v2 (same as all other endpoints)

---

## 7. Nouveau endpoint — `data_rules.php`

### Objectif
Exposer les règles de cohérence d'un thème dans leur forme interpolée (variables PHP substituées) pour qu'elles puissent être stockées localement.

> **Note** : Cet endpoint a été créé dans le cadre d'une réflexion sur un contrôle offline. Il est conservé dans le code serveur pour usage futur, mais l'application mobile n'utilise pas cet endpoint actuellement — elle utilise exclusivement `data_controle.php` pour le contrôle de cohérence.

### Fichier créé : `StatEduc_MEN_2025/data_rules.php`

### Route
```
GET /data_rules.php/theme_rules/{user}/{id_camp}/{id_sector}/{id_theme}/{id_etab}/{id_filter}/{id_annee}
```

### Réponse JSON
```json
{
  "se_status": 200,
  "se_message": "OK",
  "se_data": {
    "id_theme": 3,
    "nb_regles": 2,
    "regles": [
      {
        "id_regle": 5,
        "lib_regle": "Effectif garçons",
        "sql_regle": "SELECT SUM(NB_G) FROM COLLECT_PRIM WHERE CODE_ETAB='ECO001' AND CODE_ANNEE=2024",
        "associations": [
          {
            "id_assoc": 12,
            "id_regle_assoc": 7,
            "lib_regle_assoc": "Effectif total",
            "sql_assoc": "SELECT SUM(TOTAL) FROM ...",
            "critere": "<=",
            "message": "Effectif garçons doit être <= effectif total"
          }
        ]
      }
    ]
  }
}
```

### Interpolation SQL via `eval()`
Comme dans `controle_theme.class.php::get_regles()`, les variables dans le SQL brut (`SQL_REGLE_THEME`) sont substituées via `eval()` :
```php
// Variables définies avant eval() :
$code_etablissement = $id_etab;
$code_annee         = $id_annee;
$code_filtre        = $id_filter;

// Substitution (remplace ${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} etc.) :
eval("\$sql=\"$sql_regle_raw\";");
```

---

## 8. Classes métier — contrôle de cohérence

### Fichiers concernés (lecture seule — non modifiés)

#### `server-side/classes/metier/controle_theme.class.php`
- `get_regles()` (lignes 239–320) : lecture de `DICO_REGLE_THEME` + `DICO_REGLE_THEME_ASSOC`, interpolation SQL via `eval()`
- `controle_regles_theme()` (lignes 331–700) : exécute les règles et émet du HTML/JS d'alerte — **utilisé uniquement dans l'interface web**

#### `server-side/classes/metier/controle_theme_batch.class.php`
- Version batch sans sortie HTML/JS
- Stocke les violations dans `$tab_regles_theme_assoc_not_ok`
- **Utilisé par `data_controle.php`** pour l'API REST mobile

### Tables de données concernées

| Table | Rôle |
|---|---|
| `DICO_REGLE_THEME` | Définit chaque règle (SQL, libellé) |
| `DICO_REGLE_THEME_ASSOC` | Associe deux règles avec un opérateur (`CRITERE`) |
| `DICO_TRADUCTION` | Libellés traduits pour les messages de violation |

---

## 9. `common.php` — configuration des connexions

### Fichier : `StatEduc_MEN_2025/common.php`

#### Modification ligne 626 (branche `postgres9`)
```php
// AVANT :
$conn_dico = ADONewConnection('postgres9');
$conn_dico->debug = true;

// APRÈS :
$conn_dico = ADONewConnection('postgres9');
//$conn_dico->debug = true;
```

#### Contexte
`common.php` est le point d'entrée de toutes les pages de l'application. Il configure les connexions ADOdb (MySQL et PostgreSQL) selon le fichier `config.php`. La branche `postgres9` est suivie quand `$curcnx['type'] == 'postgres9'`.

#### ADOdb debug mode — comportement
Quand `$conn->debug = true` est actif :
- Chaque appel à `GetAll()`, `GetRow()`, `Execute()`, etc. passe par `_adodb_debug_execute()` dans `adodb-lib.inc.php`
- La fonction `outp()` (ligne 782 de `adodb.inc.php`) appelle `echo $msg . "<br>\n"` directement
- Ces lignes HTML/SQL sont émises **avant** tout header ou output buffering

#### Valeur par défaut ADOdb
`$conn->debug = false` est la valeur par défaut déclarée à la ligne 472 de `adodb.inc.php`. La modification dans `common.php` ramène simplement la connexion `conn_dico` à ce comportement normal.

---

## 10. Stratégie de session pour l'app mobile

### Problème
Le serveur PHP utilise `$_SESSION['annee']` pour connaître l'année scolaire courante. Ce mécanisme repose sur un cookie de session PHP (`PHPSESSID`) maintenu par le navigateur entre les requêtes.

L'application mobile utilise Dio (HTTP client) sans cookie de session persistant → `$_SESSION['annee']` est vide à chaque requête.

### Solution implémentée
Chaque endpoint REST critique accepte `id_annee` comme paramètre URL optionnel :

| Endpoint | Paramètre ajouté | Ligne |
|---|---|---|
| `data_save.php` | `/:id_annee` (route étendue) | 172 |
| `data_controle.php` | `/:id_annee` | Route principale |
| `data_rules.php` | `/:id_annee` | Route principale |

Le pattern PHP côté serveur :
```php
// En début de route :
if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') {
    $_SESSION['annee'] = $id_annee;
}
// Puis utilisation :
$id_year = ($id_annee != '' && $id_annee != '0')
    ? $id_annee
    : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');
```

### Côté mobile (Flutter)
`User.codeyear` est renseigné lors de l'authentification (réponse de `user_ident.php`). Il est transmis comme dernier segment URL dans chaque requête save/contrôle.

---

## Commits associés

| SHA | Session | Message |
|-----|---------|---------|
| `1db4be2` | **17 (Flutter)** | feat(session17): timeout, cohérence offline, envoi global, identification, settings |
| `381de3e` | 1-15 (PHP) | fix(admin): suppress ADOdb debug echo + guard AJAX JSON against output pollution |
| `b544819` | 1-15 (PHP) | fix(save/coherence): complete data-not-saved fix + coherence control for mobile |
| `7bb5ac3` | 1-15 (PHP) | serveur add code |

## Fichiers modifiés / créés

| Fichier | Statut | Description |
|---|---|---|
| `common.php` | **modifié** | Ligne 626 : `$conn_dico->debug = true` → commenté |
| `server-side/include/administration/gestion_base_service.php` | **modifié** | `ob_start()` + `ob_clean()` dans les helpers JSON |
| `data_save.php` | **modifié** | Route étendue `/:id_annee` + refactoring `theme_save_handler()` + 4 fixes `$id_year` |
| `data_controle.php` | **créé** | Endpoint REST contrôle de cohérence pour app mobile |
| `data_rules.php` | **créé** | Endpoint REST exposition des règles interpolées (usage futur) |
