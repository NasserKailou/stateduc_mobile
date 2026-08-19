<?php
	 require_once $GLOBALS['SISED_PATH_CLS'] . 'adodb/adodb.inc.php';
	  if (!defined('ADODB_ASSOC_CASE')) { define('ADODB_ASSOC_CASE', ADODB_ASSOC_CASE_UPPER); }
	  $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

//Verification de l'existance du  $GLOBALS['PARAM']['dico_DB']

class connexion {

    public  $sources;
    private $types;
	private $dbs_manage;
	public  $ok;

    public function __construct() {

        $this->sources = $this->get_sources();
        $this->types = $this->set_types();

    }

    public function set_dbs_manage() {
        $this->dbs_manage = 2;
    }

    private function get_sources() {

        $result = array();

        $lines = file($GLOBALS['SISED_PATH'] . 'connexion.php');

        foreach($lines as $line) {

            if(preg_match('`^#`', $line)) {
                $line = preg_replace('`^#(.*)\r?\n$`', '\\1', $line);
				$line = preg_replace("/\s+/","",$line);
                $info = explode(';', $line);
                if(count($info) == 7) {

                    $temp = array();

                    $temp['id'] = trim($info[0]);
                    $temp['nom'] = trim($info[1]);
                    $temp['type'] = trim($info[2]);
                    $temp['serveur'] = trim($info[3]);
                    $temp['utilisateur'] = trim($info[4]);
                    $temp['mdp'] = trim($info[5]);
                    $temp['base'] = trim($info[6]);

                    array_push($result, $temp);
                }
            }
        }
        return $result;
    }

    private function set_sources() {

        $content = '';

        $content .= '<?php ' . "\n\n";

        foreach($this->sources as $s) {

            $content .= '#' . $s['id'] . ';' . $s['nom'] . ';' . $s['type'] . ';' . $s['serveur'] . ';' . $s['utilisateur'] . ';' . $s['mdp'] . ';' . $s['base'] . "\n";

        }

        $content .= "\n".'?>';

        file_put_contents($GLOBALS['SISED_PATH'] . 'connexion.php', $content);

    }

    private function set_types() {

        $result = array();

        array_push($result, 'mssql');
        array_push($result, 'access');
        array_push($result, 'mysqli');
        array_push($result, 'oracle');
        array_push($result, 'postgres9');

        return $result;

    }

    private function get_sources_lastid() {

        $ids = array();

        foreach($this->sources as $s) {

            array_push($ids, $s['id']);

        }

        rsort($ids);

        return $ids[0];

    }
		
		public function get_cnx_key_of($cnx_id){
				foreach($this->sources as $i=>$s) {
						if($cnx_id == $s['id']) {
								return $i;
						}
				}
		}
		
    public function init($id=false) {

        if($id) {

            foreach($this->sources as $s) {

                if($s['id'] == $id) {

                    $curcnx = $s;
                    break;

                }

            }

        } else {

            $curcnx = $this->sources[0];

        }

        $GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
		
		// Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
		// Session 24 : Support SQL Server 2012 pour dico_DB en plus d'Access
		// Detection automatique : fichier Access .mdb/.accdb si present sur disque,
		// sinon SQL Server 2012 si connexion principale est mssql,
		// sinon meme SGBD que la connexion principale (mysql/postgres).
		// Aucun changement de code metier requis - totalement transparent.
		if (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb')) {
			// Connexion dico Access .mdb (compatibilite legacy)
			$conn_dico = ADONewConnection('access');
			$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.mdb' . ';Uid=Admin;Pwd=\'\';');
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif (file_exists($GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb')) {
			// Connexion dico Access .accdb
			$conn_dico = ADONewConnection('access');
			$conn_dico->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/dico_DB.accdb' . ';Uid=Admin;Pwd=\'\';');
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif ($curcnx['type'] == 'mssql') {
			// Connexion dico SQL Server 2012+ (dico_DB sur meme serveur SQL que la base principale)
			// Si PARAM['dico_DB'] est defini dans params.php/params_sys.php il est utilise,
			// sinon on essaie 'dico_DB' puis la base principale en fallback.
			ini_set('display_errors', 0); error_reporting(0);
			$conn_dico = ADONewConnection('mssqlnative');
			$conn_dico->setConnectionParameter('CharacterSet', 'UTF-8');
			ini_set('display_errors', 1); error_reporting(1);
			$dico_db_name = (isset($GLOBALS['PARAM']['dico_DB']) && $GLOBALS['PARAM']['dico_DB'] != '')
				? $GLOBALS['PARAM']['dico_DB'] : 'dico_DB';
			$con_result = $conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_db_name);
			if (!$con_result) {
				// Fallback : si dico_DB introuvable, utiliser la meme base que la connexion principale
				$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']);
			}
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif ($curcnx['type'] == 'mysql') {
			$conn_dico = ADONewConnection('mysqli');
			$dico_db_name = (isset($GLOBALS['PARAM']['dico_DB']) && $GLOBALS['PARAM']['dico_DB'] != '')
				? $GLOBALS['PARAM']['dico_DB'] : 'dico_DB';
			$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_db_name);
			$conn_dico->setCharset('utf8');
			$GLOBALS['conn_dico'] = $conn_dico;
		} elseif ($curcnx['type'] == 'postgres9') {
			$conn_dico = ADONewConnection('postgres9');
			$dico_db_name = (isset($GLOBALS['PARAM']['dico_DB']) && $GLOBALS['PARAM']['dico_DB'] != '')
				? $GLOBALS['PARAM']['dico_DB'] : 'dico_DB';
			$conn_dico->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $dico_db_name);
			$GLOBALS['conn_dico'] = $conn_dico;
		}
		//fin mise a jour Nasser - Session 24
		
		$conn_type = $curcnx['type'];
		if($conn_type == 'mssql') {
			$conn_type .= 'native'; 
		} else if($conn_type == 'mysql') {
			$conn_type = 'mysqli'; 
		}
        $conn = ADONewConnection($conn_type);
		 
		   /* var_dump($conn);
			exit;*/
			
        if($curcnx['type'] == 'access') {
			
			/*ini_set("display_errors",0);error_reporting(0);
			$conn->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $GLOBALS['SISED_PATH'] . 'server-side/db/bASE_MEPSA.mdb' . ';Uid=Admin;Pwd=\'\';');
			$GLOBALS['conn'] = $conn;
			
			ini_set("display_errors",1);error_reporting(1);
			*/
			//$conn->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $GLOBALS['SISED_PATH_DB'] . $curcnx['serveur'] . ';Uid=' . $curcnx['utilisateur'] . ';Pwd=' . $curcnx['mdp'] . ';');
			try
			{
				ini_set("display_errors",0);error_reporting(0);
				if(($conn->Connect('Driver={Microsoft Access Driver (*.mdb)};Dbq=' . $curcnx['serveur'] . ';Uid=' . $curcnx['utilisateur'] . ';Pwd=' . $curcnx['mdp'] . ';'))===false)
					 throw new Exception('ERR_ACCES_BASE');
			}
			catch (Exception $e) {
				echo "error3".$e->getMessage();
				ini_set("display_errors",1);error_reporting(1);
				session_start();
				//echo $_SESSION['groupe'];die;
				if($_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2){
				//	header('Location: '.$GLOBALS['SISED_AURL'].'administration.php?val=param_conn');
				}else{
					//session_destroy();
					//header('Location: '.$GLOBALS['SISED_AURL'].'index.php?val=param_conn');
				}
				exit();
			}
			
			/*var_dump($conn->IsConnected());
			$requete = "select * from TYPE_AUTEUR";
			$fixe_regroup = $conn->GetAll($requete);
			print_r($fixe_regroup);
			exit;*/
			
			
        } elseif($curcnx['type']=='mysql') {
            //$conn->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']);              
			try{
				ini_set("display_errors",0);error_reporting(0);
			//$conn->setConnectionParameter('CharacterSet','UTF-8');
			//$conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME,'UTF-8');
				//$conn->setConnectionParameter(MYSQLI_SET_CHARSET_NAME,'utf8mb4');
				//$conn->debug = true;
				if(($conn->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']))===false)
					 throw new Exception('ERR_ACCES_BASE');
				$conn->setCharset('utf8');
			}
			catch (Exception $e) {
				//echo "error4".$e->getMessage();
				ini_set("display_errors",1);error_reporting(1);
				session_start();
				if($_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2){
					header('Location: '.$GLOBALS['SISED_AURL'].'administration.php?val=param_conn');
				}else{
					session_destroy();
					header('Location: '.$GLOBALS['SISED_AURL'].'index.php?val=param_conn');
				}
				exit();
			}
        } else {
            //$conn->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']);              
			try{
				ini_set("display_errors",0);error_reporting(0);//$conn->debug = true;
				if(($conn->Connect($curcnx['serveur'], $curcnx['utilisateur'], $curcnx['mdp'], $curcnx['base']))===false)
					 throw new Exception('ERR_ACCES_BASE');
			}
			catch (Exception $e) {
				echo "error4".$e->getMessage();
				ini_set("display_errors",1);error_reporting(1);
				session_start();
				if($_SESSION['groupe'] ==1 || $_SESSION['groupe'] ==2){
					header('Location: '.$GLOBALS['SISED_AURL'].'administration.php?val=param_conn');
				}else{
					session_destroy();
					header('Location: '.$GLOBALS['SISED_AURL'].'index.php?val=param_conn');
				}
				exit();
			}
        }
		$this->ok = $conn->IsConnected();
		//echo '<pre>';
		//print_r($conn);
		//var_dump($conn);
		// bass
		//var_dump($conn->IsConnected());
		//exit();
		if( $this->ok <> true){
				//require_once $GLOBALS['SISED_AURL'] . 'no_conn.php';
				//die('not connected');
				if(isset($this->dbs_manage)){
						$get = '?dbs_manage=1';
				}
				header('Location: '.$GLOBALS['SISED_AURL'].'no_conn.php'.$get);
				exit();
		}
		// fin bass
        $GLOBALS['conn'] = $conn;
		
		if (!isset($GLOBALS['conn_dico'])){
			$GLOBALS['conn_dico'] = $conn;
		}
    }
		
		public function test_connect( $type, $serveur, $utilisateur, $mdp, $base){
				$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;

        $test_conn = ADONewConnection($type);

        if( $type == 'access' ) {

            $test_conn->Connect('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=' . $serveur . ';Uid=' . $utilisateur . ';Pwd=' . $mdp . ';');

        } else {

            $test_conn->Connect($serveur, $utilisateur, $mdp, $base);              

        }
				
				return($test_conn->IsConnected());
				
		}

    public function set_default($id) {

        foreach($this->sources as $i=>$s) {

            if($s['id'] == $id) {

                $tmp = $this->sources[0];
                $this->sources[0] = $this->sources[$i];
                $this->sources[$i] = $tmp;

                $this->set_sources();

                break;

            }

        }

    }
	
	public function getActive() {
		$liste = $this->get_sources();
		return $liste[0];
	}
	
	public function active_source($id, $nom, $type, $serveur, $utilisateur, $mdp='', $base='') {

        $content = '';
		file_put_contents($GLOBALS['SISED_PATH'] . 'connexion.php', $content);
		$content .= '<?php ' . "\n\n";
		$content .= '#' . $id . ';' . $nom . ';' . $type . ';' . $serveur . ';' . $utilisateur . ';' . $mdp . ';' . $base;
		$content .= "\n\n".'?>';
		file_put_contents($GLOBALS['SISED_PATH'] . 'connexion.php', $content);
		
		$this->sources = $this->get_sources();

    }

    public function ajouter_source($nom, $type, $serveur, $utilisateur, $mdp='', $base='') {
		
		$tmp = array();
		$id = get_sources_lastid() + 1;
        $temp['id'] = $id;
        $temp['nom'] = $nom;
        $temp['type'] = $type;
        $temp['serveur'] = $serveur;
        $temp['utilisateur'] = $utilisateur;
        $temp['mdp'] = $mdp;
        $temp['base'] = $base;

        array_push($this->sources, $tmp);
		
		$this->set_sources();
    }

    public function supprimer_source($id) {

        foreach($this->sources as $i=>$s) {

            if($s['id'] == $id) {

                unset($this->sources[$i]);

                $this->set_sources();
                break;

            }

        }

    }
	
	//Supprimer toutes les sources
	public function supprimer_tout() {
		$this->sources = array();
		//$this->set_sources();
	}

    public function modifier_source($id, $nom, $type, $serveur, $utilisateur, $mdp='', $base='') {

        foreach($this->sources as $i=>$s) {

            if($s['id'] == $id) {
                $this->sources[$i]['nom'] = $nom;
                $this->sources[$i]['type'] = $type;
                $this->sources[$i]['serveur'] = $serveur;
                $this->sources[$i]['utilisateur'] = $utilisateur;
                $this->sources[$i]['mdp'] = $mdp;
                $this->sources[$i]['base'] = $base;

                $this->set_sources();
                break;

            }

        }

    }

    public function build_cnx_box_sources($nom, $selected=0) {

        $result = '';

        $result = '<select name="'.$nom.'" onchange="toggle_'.$nom.'();">' . "\n";

        foreach($this->sources as $s) {

            if($s['id'] == $selected) {

                $sel = ' selected';

            } else {

                $sel = '';

            }

            $result .= '<option value="' . $s['id'] . '"'.$sel.'>' . $s['nom'] . '</option>' . "\n";

        }

        $result .= '</select>' . "\n";

        return $result;

    }

    public function build_cnx_box_type($nom, $selected='mssql') {

        $result = '';

        $result = '<select name="'.$nom.'">' . "\n";

        foreach($this->types as $t) {

            if($t == $selected) {

                $sel = ' selected';

            } else {

                $sel = '';

            }

            $result .= '<option value="' . $t . '"'.$sel.'>' . $t . '</option>' . "\n";

        }

        $result .= '</select>' . "\n";

        return $result;

    }

}

?>
