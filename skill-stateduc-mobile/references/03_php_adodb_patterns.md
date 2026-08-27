# Patterns PHP / ADODB / Access — Bonnes Pratiques

---

## 1. Détection robuste du port HTTP local (curl interne)

**Problème** : Apache peut écouter sur n'importe quel port (80, 8080, 8083...).
Le curl interne PHP→PHP doit cibler `127.0.0.1:{port_reel}`.

```php
function _sised_local_port() {
    $ssl_ports    = array(443, 8443);          // exclus toujours
    $fallback_ports = array(80, 8080, 8000, 8888); // sondés en fallback

    // Priorité 1 : SERVER_PORT si non-SSL
    if (isset($_SERVER['SERVER_PORT'])) {
        $p = (int)$_SERVER['SERVER_PORT'];
        if ($p > 0 && !in_array($p, $ssl_ports)) {
            $en = 0; $es = '';
            $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
            if ($s !== false) { fclose($s); return $p; }
        }
    }
    // Priorité 2 : sonder les ports standard
    foreach ($fallback_ports as $p) {
        if (in_array($p, $ssl_ports)) continue;
        $en = 0; $es = '';
        $s = @fsockopen('127.0.0.1', $p, $en, $es, 2);
        if ($s !== false) { fclose($s); return $p; }
    }
    return 80; // fallback absolu
}
$_sised_local_port   = _sised_local_port();
$_sised_local_scheme = 'http'; // TOUJOURS http pour curl interne (SSL-51)
$_sised_local_ps     = ($_sised_local_port != 80) ? ':' . $_sised_local_port : '';
$SISED_AURL_INTERNAL = $_sised_local_scheme . '://127.0.0.1' . $_sised_local_ps . $SISED_URL;
$SISED_HOST_HEADER   = $_SERVER['HTTP_HOST'];
```

---

## 2. Guard session_start() anti-deadlock

**Règle** : Tout `session_start()` dans le chemin d'un curl interne doit être gardé.

```php
// ✅ CORRECT
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ❌ INTERDIT dans les fichiers appelés par curl interne
session_start(); // sans guard → deadlock potentiel
```

**Pattern complet pour un WS appelé par curl interne**
```php
<?php
// 1. Ouvrir la session normalement (mode écriture)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Écrire les variables bootstrap
$_SESSION['login']   = $_GET['login']   ?? '';
$_SESSION['annee']   = $_GET['annee']   ?? '';
$_SESSION['secteur'] = $_GET['sector']  ?? '';

// 3. OBLIGATOIRE : fermer la session avant require common.php
session_write_close();

// 4. common.php peut maintenant s'exécuter sans deadlock
require_once 'common.php';
```

---

## 3. curl interne robuste (data_save → questionnaire_ws)

```php
$curl = new Curl();
$curl->setHeader('Host', $GLOBALS['SISED_HOST_HEADER']);
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
$curl->setOpt(CURLOPT_CONNECTTIMEOUT, 15);
$curl->setOpt(CURLOPT_TIMEOUT, 300); // 5min minimum pour gros formulaires

$curl->success(function($instance) use (...) {
    if (strpos($instance->response, "ISOKSAVEINDATABASE") !== FALSE) {
        $statut_save = "OKSAVE";
    } else {
        $statut_save = "KOSAVE";
    }
    $rps = array($lib_status => $status_ok, $lib_message => $msg_ok,
                 $lib_data   => $statut_save);
    echo json_encode($rps);
});

$curl->error(function($instance) use (...) {
    // Log l'URL pour diagnostics
    $string = date('Y/m/d H:i:s');
    $string .= ";" . $instance->error_code . ":" . $instance->error_message;
    $string .= ";" . $instance->url . ";\n";
    file_put_contents("moblogs/{$user}.log", $string, FILE_APPEND);

    $rps = array($lib_status => $status_ko, $lib_message => $status_ko,
                 $lib_data   => $instance->error_code . " : " . $instance->error_message);
    echo json_encode($rps);
});

// OBLIGATOIRE : libérer le verrou de session AVANT le curl
session_write_close();
$curl->post($urlBase, $data_to_send);
```

---

## 4. Limites PHP pour formulaires lourds

```php
// questionnaire_ws.php — EN TOUT DÉBUT DE FICHIER
set_time_limit(0);                         // pas de limite de temps
ini_set("memory_limit", "256M");           // minimum pour HTML + ADODB
ini_set('session.gc_maxlifetime', 3600);   // 1h session
```

**Dimensionnement** :
| Scénario | memory_limit recommandé |
|----------|------------------------|
| Formulaire simple (< 20 questions) | 64M suffisant |
| Formulaire standard (20-100 questions) | 128M |
| Gros formulaire + arbre ADODB (> 100 questions) | 256M |
| TMIS / arbre multi-niveaux | 512M |

---

## 5. Routes Slim REST — pattern standard

```php
// Route mobile étendue (avec id_annee dans l'URL)
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start/:id_annee',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start, $id_annee)
    use ($lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl, $app) {
        // Injecter l'année en session si absente
        if (!isset($_SESSION['annee']) || $_SESSION['annee'] == '') {
            $_SESSION['annee'] = $id_annee;
        }
        theme_save_handler(...args...);
});

// Route navigateur (sans id_annee — utilise la session)
$app->post('/theme_save/:user/:id_camp/:id_sector/:id_theme/:id_etab/:id_filter/:start',
    function ($user, $id_camp, $id_sector, $id_theme, $id_etab, $id_filter, $start)
    use (...) {
        $id_annee = isset($_SESSION['annee']) ? $_SESSION['annee'] : '';
        theme_save_handler(...args...);
});
```

**Règle** : Toujours avoir les deux routes (mobile avec `id_annee` + navigateur sans).

---

## 6. Réponse JSON standard (enveloppe se_*)

```php
// Succès
function sendSuccess($data, $message = 'ok') {
    echo json_encode([
        'se_status'  => 200,
        'se_message' => $message,
        'se_data'    => $data,
    ]);
}

// Erreur
function sendError($message, $status = 400) {
    echo json_encode([
        'se_status'  => $status,
        'se_message' => $status,
        'se_data'    => $message,
    ]);
}
```

**Format de l'année active** :
```json
{
  "se_status": 200,
  "se_message": "ok",
  "se_data": {
    "code": 21,
    "libelle": "2026/2027"
  }
}
```

---

## 7. ADODB sur Access — configuration obligatoire

```php
// Lors de la création de la connexion
$conn = ADONewConnection('access');
$conn->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=...');
$conn->SetFetchMode(ADODB_FETCH_ASSOC);

// Toujours forcer les clés UPPERCASE
if (defined('ADODB_ASSOC_CASE_UPPER')) {
    $conn->AssocCaseUpper();
}

// Utilisation — toujours UPPERCASE
$row = $conn->GetRow("SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER='$login'");
$code_user = $row['CODE_USER']; // PAS $row['code_user']
```

---

## 8. Fallback année active (mobile sans session)

```php
// Pattern robuste : priorité URL > session > PARAM_DEFAUT
$id_year = ($id_annee != '' && $id_annee != '0')
    ? $id_annee
    : (isset($_SESSION['annee']) ? $_SESSION['annee'] : '');

// Fallback si aucune année disponible
if ($id_year == '' || $id_year == '0') {
    $def = $GLOBALS['conn_dico']->GetOne('SELECT CODE_ANNEE FROM PARAM_DEFAUT');
    if ($def && (int)$def > 0) {
        $id_year = $def;
        $_SESSION['annee'] = $id_year;
    }
}
```

---

## 9. Accès campagne mobile (fallback utilisateur)

```php
// Vérification principale via DICO_FIXE_REGROUPEMENT
$requete = "SELECT DISTINCT ID_CAMPAGNE
            FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
            WHERE AU.NOM_USER LIKE '$user'
            AND DFR.ID_USER = AU.CODE_USER
            AND ID_ANNEE = $id_year
            AND ID_CAMPAGNE = $id_camp";
$camps = $GLOBALS['conn_dico']->GetAll($requete);
$access_ok = (count($camps) > 0 && $camps[0] != '');

// Fallback mobile : vérifier seulement l'existence de l'utilisateur
if (!$access_ok && $is_mobile_request) {
    $req = "SELECT CODE_USER FROM ADMIN_USERS WHERE NOM_USER LIKE '$user'";
    $row = $GLOBALS['conn_dico']->GetRow($req);
    if ($row && (int)$row['CODE_USER'] > 0) {
        $access_ok = true; // utilisateur valide, campagne déjà téléchargée
    }
}
```

---

## 10. Nettoyage production — ce qu'il ne faut JAMAIS laisser actif

```php
// ❌ JAMAIS en production dans les routes REST
echo $id;
echo "<pre>";
print_r($allPostVars);

// ❌ JAMAIS de error_log() verbeux sur des variables SQL
error_log("SQL: " . $sql);          // expose la structure DB
error_log("params: " . print_r($params, true)); // expose les données

// ✅ Conserver uniquement les erreurs légitimes
error_log("ERREUR connexion: " . $conn->ErrorMsg());
error_log("ERREUR requête: " . $conn->ErrorMsg() . " | SQL: " . $sql);
```
