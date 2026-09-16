<?php
/**
 * nasser_logger.php — Logger de diagnostic pour debug ID_REGROUP_PARENTS / ID_TYPE_REGROUP_PARENTS
 *
 * OBJECTIF :
 *   Tracer CHAQUE clic utilisateur sur la page gestion_user (import Excel)
 *   et CHAQUE étape interne (validation, SQL, hiérarchie, transaction)
 *   pour permettre au développeur de diagnostiquer les valeurs manquantes
 *   dans DICO_FIXE_REGROUPEMENT.ID_REGROUP_PARENTS / ID_TYPE_REGROUP_PARENTS.
 *
 * FICHIER LOG PRODUIT :
 *   StatEduc_burundi/moblogs/nasser.log  (toujours le même fichier, append)
 *
 * UTILISATION :
 *   Inclure en tête de gestion_user.php :
 *     require_once $GLOBALS['SISED_PATH'] . 'moblogs/nasser_logger.php';
 *
 *   Puis appeler dans le code PHP instrumenté :
 *     NasserLog::clic('IMPORT_EXCEL_POST', $_POST, $_FILES);
 *     NasserLog::etape('ADMIN_USERS_INSERT', 'soul', 'SQL: INSERT INTO...', 'OK');
 *     NasserLog::sql('HIER_LOOKUP', 'SELECT R.CODE_REGROUPEMENT...', $rows_found);
 *     NasserLog::valeur('ID_REGROUP_PARENTS déterminé', '15,7');
 *     NasserLog::err('DICO_INSERT', 'ERREUR DB: ...message...');
 *
 * @author   fix AK — debug nasser.log session 20
 * @version  1.0
 */

class NasserLog
{
    /**
     * Interrupteur global des logs.
     * false = silencieux (production) — tous les appels NasserLog::*() deviennent des no-ops.
     * true  = actif (debug local) — remet les logs en marche sans toucher aux call-sites.
     * Pour réactiver temporairement : changer false → true ci-dessous.
     */
    private static $enabled = false;

    /** Chemin absolu vers nasser.log */
    private static $logFile = null;

    /** Séparateur de section */
    const SEP  = '─────────────────────────────────────────────────────────────────────────────────';
    const SEP2 = '═════════════════════════════════════════════════════════════════════════════════';

    // ─────────────────────────────────────────────────────────────────────────
    // Initialisation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Retourne le chemin absolu vers nasser.log.
     * Cherche SISED_PATH en globals, sinon remonte depuis __FILE__.
     */
    private static function logPath()
    {
        if (self::$logFile !== null) {
            return self::$logFile;
        }

        if (!empty($GLOBALS['SISED_PATH'])) {
            $dir = rtrim($GLOBALS['SISED_PATH'], '/\\') . DIRECTORY_SEPARATOR . 'moblogs';
        } else {
            // Fallback : remonter depuis moblogs/nasser_logger.php → StatEduc_burundi/moblogs/
            $dir = dirname(__FILE__);
        }

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        self::$logFile = $dir . DIRECTORY_SEPARATOR . 'nasser.log';
        return self::$logFile;
    }

    /**
     * Écriture brute dans le fichier log.
     * No-op immédiat si $enabled === false (mode production).
     */
    private static function write($line)
    {
        if (!self::$enabled) { return; }
        $path = self::logPath();
        $fp   = @fopen($path, 'a');
        if ($fp) {
            fputs($fp, $line . "\n");
            fclose($fp);
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // API publique
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Enregistre un clic utilisateur (POST/GET reçu).
     *
     * @param string $action   Nom de l'action : 'IMPORT_EXCEL', 'MIGRER_ANNEE', 'AFFICHAGE_PAGE'
     * @param array  $post     $_POST reçu (les mots de passe seront masqués)
     * @param array  $files    $_FILES reçu (optionnel)
     * @param array  $get      $_GET reçu (optionnel)
     */
    public static function clic($action, $post = [], $files = [], $get = [])
    {
        $ts   = date('Y-m-d H:i:s');
        $user = isset($_SESSION['code_user']) ? $_SESSION['code_user'] : 'N/A';
        $ip   = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A';

        self::write('');
        self::write(self::SEP2);
        self::write(sprintf('[%s] ►► CLIC : %s | User:%s | IP:%s', $ts, strtoupper($action), $user, $ip));
        self::write(self::SEP2);

        // POST — masquer mots de passe
        if (!empty($post)) {
            $safe_post = $post;
            foreach (['PASSWORD', 'MOT_DE_PASSE', 'password', 'mot_de_passe', 'pass'] as $k) {
                if (isset($safe_post[$k])) $safe_post[$k] = '***';
            }
            self::write(sprintf('[%s]   POST  : %s', $ts, json_encode($safe_post, JSON_UNESCAPED_UNICODE)));
        }

        // GET
        if (!empty($get)) {
            self::write(sprintf('[%s]   GET   : %s', $ts, json_encode($get, JSON_UNESCAPED_UNICODE)));
        }

        // FILES
        if (!empty($files) && isset($files['file'])) {
            self::write(sprintf('[%s]   FILE  : name=%s | size=%d | type=%s | error=%d',
                $ts,
                $files['file']['name']  ?? 'N/A',
                $files['file']['size']  ?? 0,
                $files['file']['type']  ?? 'N/A',
                $files['file']['error'] ?? -1
            ));
        }

        // Session utile
        $sess_keys = ['code_user','annee','groupe','id_groupe','instance_nomenc'];
        $sess_info = [];
        foreach ($sess_keys as $k) {
            if (isset($_SESSION[$k])) {
                $v = $_SESSION[$k];
                // Ne pas sérialiser les objets lourds
                $sess_info[$k] = is_object($v) ? '[objet '.get_class($v).']' : $v;
            }
        }
        self::write(sprintf('[%s]   SESSION : %s', $ts, json_encode($sess_info, JSON_UNESCAPED_UNICODE)));
    }

    /**
     * Enregistre une étape de traitement (non-SQL).
     *
     * @param string $etape    Nom de l'étape : 'VALIDATION', 'DEBUT_BOUCLE', 'FIN_BOUCLE'…
     * @param string $login    Login en cours de traitement ($tab[4])
     * @param string $detail   Description libre
     * @param string $resultat 'OK' | 'ECHEC' | 'SKIP' | '' (libre)
     */
    public static function etape($etape, $login, $detail, $resultat = '')
    {
        $ts  = date('Y-m-d H:i:s');
        $res = $resultat ? " => $resultat" : '';
        self::write(sprintf('[%s]   %-35s login=%-15s | %s%s',
            $ts, strtoupper($etape), $login, $detail, $res));
    }

    /**
     * Enregistre une requête SQL + son résultat.
     *
     * @param string     $contexte  Ex: 'ADMIN_USERS_INSERT', 'HIER_LOOKUP', 'DICO_INSERT_CHECK'
     * @param string     $sql       La requête SQL complète
     * @param mixed      $resultat  Tableau de lignes retournées, booléen, entier…
     * @param string     $db_err    Message d'erreur DB si échec (optionnel)
     */
    public static function sql($contexte, $sql, $resultat = null, $db_err = '')
    {
        $ts = date('Y-m-d H:i:s');
        self::write(sprintf('[%s]   [SQL/%s]', $ts, strtoupper($contexte)));
        self::write(sprintf('[%s]     SQL   : %s', $ts, trim(preg_replace('/\s+/', ' ', $sql))));

        if ($resultat === false || $resultat === null) {
            self::write(sprintf('[%s]     RESULT: ECHEC/NULL | DB_ERR: %s', $ts, $db_err ?: 'N/A'));
        } elseif (is_array($resultat)) {
            $count = count($resultat);
            self::write(sprintf('[%s]     RESULT: %d ligne(s) retournée(s)', $ts, $count));
            if ($count > 0 && $count <= 5) {
                foreach ($resultat as $i => $row) {
                    self::write(sprintf('[%s]       [%d] %s', $ts, $i, json_encode($row, JSON_UNESCAPED_UNICODE)));
                }
            } elseif ($count > 5) {
                self::write(sprintf('[%s]       [0] %s', $ts, json_encode($resultat[0], JSON_UNESCAPED_UNICODE)));
                self::write(sprintf('[%s]       ... (%d lignes supplémentaires non affichées)', $ts, $count - 1));
            }
        } elseif (is_int($resultat) || is_numeric($resultat)) {
            self::write(sprintf('[%s]     RESULT: COUNT = %s', $ts, $resultat));
        } elseif (is_bool($resultat)) {
            self::write(sprintf('[%s]     RESULT: %s', $ts, $resultat ? 'TRUE/OK' : 'FALSE/ECHEC'));
        } else {
            self::write(sprintf('[%s]     RESULT: %s', $ts, print_r($resultat, true)));
        }
    }

    /**
     * Enregistre une valeur calculée (ID_REGROUP_PARENTS, ID_TYPE_REGROUP_PARENTS, etc.)
     *
     * @param string $nom    Nom du champ ou de la variable
     * @param mixed  $valeur Valeur (vide = problème à investiguer)
     * @param string $source D'où vient la valeur : 'TEMPLATE_DICO' | 'ETABLISSEMENT_REGROUPEMENT' | 'VIDE'
     */
    public static function valeur($nom, $valeur, $source = '')
    {
        $ts  = date('Y-m-d H:i:s');
        $vide = (is_string($valeur) && trim($valeur) === '') || $valeur === null;
        $flag = $vide ? ' ◄◄ VIDE/MANQUANT ◄◄' : '';
        $src  = $source ? " [source: $source]" : '';
        self::write(sprintf('[%s]   VALEUR  %-35s = "%s"%s%s',
            $ts, $nom, $valeur, $src, $flag));
    }

    /**
     * Enregistre une erreur.
     *
     * @param string $contexte Contexte de l'erreur
     * @param string $message  Message d'erreur complet
     */
    public static function err($contexte, $message)
    {
        $ts = date('Y-m-d H:i:s');
        self::write(sprintf('[%s]   !! ERREUR [%s] : %s', $ts, strtoupper($contexte), $message));
    }

    /**
     * Enregistre le résultat d'une transaction ADODB.
     *
     * @param string $action  'BEGIN' | 'COMMIT' | 'ROLLBACK'
     * @param string $login   Login concerné
     * @param string $raison  Raison du COMMIT ou ROLLBACK
     */
    public static function transaction($action, $login, $raison = '')
    {
        $ts = date('Y-m-d H:i:s');
        $symbols = ['BEGIN' => '↓', 'COMMIT' => '✓ COMMIT', 'ROLLBACK' => '✗ ROLLBACK'];
        $sym = $symbols[$action] ?? $action;
        self::write(sprintf('[%s]   TRANSACTION %-10s login=%-15s | %s',
            $ts, $sym, $login, $raison));
    }

    /**
     * Séparateur de ligne Excel (entre deux lignes).
     *
     * @param int    $num_ligne  Numéro de ligne (1-based)
     * @param string $login      Login de la ligne
     * @param string $code_user  CODE_USER ($tab[0])
     * @param string $code_etab  CODE_ETAB ($tab[7])
     */
    public static function ligne($num_ligne, $login, $code_user, $code_etab)
    {
        $ts = date('Y-m-d H:i:s');
        self::write('');
        self::write(self::SEP);
        self::write(sprintf('[%s]   LIGNE Excel #%d | login="%s" | CODE_USER=%s | CODE_ETAB=%s',
            $ts, $num_ligne, $login, $code_user, $code_etab));
        self::write(self::SEP);
    }

    /**
     * Enregistre les paramètres GLOBALS['PARAM'] liés à la hiérarchie.
     * À appeler une fois au début de l'import pour savoir quelles tables/colonnes sont utilisées.
     */
    public static function params_hierarchie()
    {
        $ts   = date('Y-m-d H:i:s');
        $keys = [
            'ETABLISSEMENT_REGROUPEMENT', 'CODE_ETABLISSEMENT', 'REGROUPEMENT',
            'TYPE_REGROUPEMENT', 'HIERARCHIE', 'NIVEAU_CHAINE',
            'TYPE_CHAINE_REGROUPEMENT', 'LIAISONS', 'CODE', 'LIBELLE',
            'REG_CODE_REGROUPEMENT',
        ];
        self::write(sprintf('[%s]   === GLOBALS[PARAM] hiérarchie ===', $ts));
        foreach ($keys as $k) {
            $v = isset($GLOBALS['PARAM'][$k]) ? $GLOBALS['PARAM'][$k] : '[NON DEFINI]';
            self::write(sprintf('[%s]     PARAM[%-35s] = "%s"', $ts, $k.']', $v));
        }
    }

    /**
     * En-tête de session — écrit au tout début de chaque requête HTTP.
     * Donne le contexte global (URL, user, session).
     */
    public static function debut_requete()
    {
        $ts  = date('Y-m-d H:i:s');
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A';
        $met = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'N/A';
        self::write('');
        self::write(self::SEP2);
        self::write(self::SEP2);
        self::write(sprintf('[%s] ■ NOUVELLE REQUÊTE : %s %s', $ts, $met, $uri));
        self::write(self::SEP2);
        self::write(self::SEP2);
    }

    /**
     * Écrit un message libre (commentaire, note de debug).
     */
    public static function note($message)
    {
        $ts = date('Y-m-d H:i:s');
        self::write(sprintf('[%s]   NOTE : %s', $ts, $message));
    }
}
