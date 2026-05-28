<?php
// Buffer all output so ADOdb debug / PHP warnings cannot corrupt the JSON response
ob_start();

header('Content-type: application/json');
require_once '../../../common.php';

class Base {
	public $id = "";
    public $nomServeur = "";
    public $typeServeur = "";
    public $urlBase = "";
	public $utilisateur = "";
	public $motDePasse = "";
	public $nomBase = "";
	public $statut = "";
}

$rootPath = $GLOBALS['SISED_PATH'];
$connexion_dico = $GLOBALS['conn_dico'];

$op = $_POST['op'];

if ($op == 'liste_base') {
	$requete = "SELECT * FROM DICO_BASE ORDER BY TYPE_SERVEUR";
	$bases_array = sql($requete, $connexion_dico);
	
	//La base active
	$active = $connexion->getActive();
	$nom_base = $active['base'] == "\r"?"":$active['base'];
	$url_base = str_replace("\\/","/",$active['serveur']);	
	$act_base = new Base();
	$act_base->id = $active['id'];
	$act_base->nomServeur = $active['nom'];
	$act_base->typeServeur = $active['type'];
	$act_base->urlBase = $active['serveur'];
	$act_base->utilisateur = $active['utilisateur'] == NULL?"":$active['utilisateur'];
	$act_base->motDePasse = ".............";
	$act_base->nomBase = $active['base'] == NULL?"":$active['base'];
	$act_base->statut = 1;
	
	if (is_array($bases_array)) {   
		$list_bases = array();
		$hasActive = false;
		foreach ($bases_array as $base_array) {
			$base = new Base();
			$base->id = $base_array["ID"];
			$base->nomServeur = $base_array["NOM_SERVEUR"];
			$base->typeServeur = $base_array["TYPE_SERVEUR"];
			$base->urlBase = $base_array["URL_BASE"];
			$base->utilisateur = $base_array["UTILISATEUR"] == NULL?"":$base_array["UTILISATEUR"];
			$base->motDePasse = ".............";
			$base->nomBase = $base_array["NOM_BASE"] == NULL?"":$base_array["NOM_BASE"];
			$base->statut = $base_array["STATUT"] == NULL?"":$base_array["STATUT"];
			if ($base->statut == 1) {
				//ON est sur la ligne active dans la base
				$hasActive = true;
				if (($base->id != $active['id']) ||
					($base->nomServeur != $active['nom']) ||
					($base->typeServeur != $active['type']) ||
					($base->urlBase != $active['serveur']) ||
					($base->utilisateur != $active['utilisateur']) ||
					($base_array["MOT_DE_PASSE"] != $active['mdp']) ||
					($base->nomBase != $active['base'])) {					
					//La ligne active est differente de la ligne configur�e dans le fichier connexion.php					
					//On tente de rajouter la ligne active du fichier dans la base	
					$req = "INSERT INTO DICO_BASE(ID, NOM_SERVEUR,TYPE_SERVEUR,URL_BASE,UTILISATEUR,MOT_DE_PASSE,NOM_BASE,STATUT) VALUES(".$active['id'].",'".$active['nom']."','".$active['type']."','".$url_base."','".$active['utilisateur']."','".$active['mdp']."','".$nom_base."',1)";
					
					if ($connexion_dico->Execute($req) === false) {
						//En cas d'erreur on tente une mise � jour, car l'id de la ligne active peut exister d�j�
						$req = "UPDATE DICO_BASE SET NOM_SERVEUR='".$active['nom']."',TYPE_SERVEUR='".$active['type']."',URL_BASE='".$url_base."',UTILISATEUR='".$active['utilisateur']."',MOT_DE_PASSE='".$active['mdp']."',NOM_BASE='".$nom_base."',STATUT=1 WHERE ID=".$active['id'];
						if ($connexion_dico->Execute($req) === false) {
							//Si erreur � ce niveau aussi, alors on retourne le message d'erreur
							sendError("Insertion non effectu&eacute;e : ".$req);
							return;
						} else {
							//Si on met � jour la base on modifie la base qui devait etre affich�e
							$base = $act_base;
						}
					} else {
						//Si on arrive � ins�rer la ligne on desactive la ligne qui �tait active dans la base et on rajoute la nouvelle ligne dans la liste des resultats
						$req = "UPDATE DICO_BASE SET STATUT=0 WHERE ID=".$base->id;
						$connexion_dico->Execute($req);
						$list_bases[] = $act_base;
						$base->statut = 0;
					}
				}
			}
			$list_bases[] = $base;
		}
		if (!$hasActive) {
			//Aucune ligne active dans la base. On rajoute la ligne du fichier dans la base
			$req = "INSERT INTO DICO_BASE(ID, NOM_SERVEUR,TYPE_SERVEUR,URL_BASE,UTILISATEUR,MOT_DE_PASSE,NOM_BASE,STATUT) VALUES(".$active['id'].",'".$active['nom']."','".$active['type']."','".$url_base."','".$active['utilisateur']."','".$active['mdp']."','".$nom_base."',1)";
					
			if ($connexion_dico->Execute($req) === false) {
				$req = "UPDATE DICO_BASE SET NOM_SERVEUR='".$active['nom']."',TYPE_SERVEUR='".$active['type']."',URL_BASE='".$url_base."',UTILISATEUR='".$active['utilisateur']."',MOT_DE_PASSE='".$active['mdp']."',NOM_BASE='".$nom_base."',STATUT=1 WHERE ID=".$active['id'];
				if ($connexion_dico->Execute($req) === false) {
					//Si erreur � ce niveau aussi, alors on retourne le message d'erreur
					sendError("Insertion non effectu&eacute;e : ".$req);
					return;
				} else {
					//Si on met � jour la base on modifie la base qui devait etre affich�e
					$i = 0;
					$found = false;
					while (!$found) {
						if ($list_bases[$i]->id == $act_base->id) {
							$found = true;
						}
						$i++;
					}
					$list_bases[$i-1] = $act_base;
				}
			} else {
				$list_bases[] = $act_base;
			}
		}
		sendList($list_bases);
	}
} else if ($op == 'insert_base_access') {
	$fileName = $_POST['filename'];
	$req = "INSERT INTO DICO_BASE(NOM_SERVEUR,TYPE_SERVEUR,URL_BASE,UTILISATEUR) VALUES('Access','access','".$fileName."','Admin')";
	if ($connexion_dico->Execute($req) === false) {
		sendError("Insertion non effectu&eacute;e");
	} else {
		sendOk();
	}
} else if ($op == 'insert_base_other') {
	$nomServeur = $_POST['nom_serveur'];
	$typeServeur = $_POST['type_serveur'];
	$urlBase = $_POST['url_base'];
	$user = $_POST['user'];
	$mdp = $_POST['mdp'];
	$nomBase = $_POST['nom_base'];
	$req = "INSERT INTO DICO_BASE(NOM_SERVEUR,TYPE_SERVEUR,URL_BASE,UTILISATEUR,MOT_DE_PASSE,NOM_BASE) VALUES('$nomServeur','$typeServeur','".$urlBase."','$user','$mdp','$nomBase')";
	if ($connexion_dico->Execute($req) === false) {
		sendError("Insertion non effectu&eacute;e");
	} else {
		sendOk();
	}
} else if ($op == 'delete_base') {
	$id = $_POST['id'];
	$req = "DELETE FROM DICO_BASE WHERE ID=$id";
	if ($connexion_dico->Execute($req) === false) {
		sendError("Suppression non effectu&eacute;e");
	} else {
		sendOk();
	}
} else if ($op == 'active_base') {
	$id = $_POST['id'];
	$req = "SELECT * FROM DICO_BASE WHERE ID=$id";
	$bases_array = sql($req, $connexion_dico);
	if (is_array($bases_array)) { 		
		$nomServeur = $bases_array[0]["NOM_SERVEUR"];
		$typeServeur = $bases_array[0]["TYPE_SERVEUR"];
		$urlBase = $bases_array[0]["URL_BASE"];
		$utilisateur = $bases_array[0]["UTILISATEUR"] == NULL?"":$bases_array[0]["UTILISATEUR"];
		$motDePasse = $bases_array[0]["MOT_DE_PASSE"] == NULL?"":$bases_array[0]["MOT_DE_PASSE"];
		$nomBase = $bases_array[0]["NOM_BASE"] == NULL?"":$bases_array[0]["NOM_BASE"];		
		$connexion->active_source($id, $nomServeur, $typeServeur, $urlBase, $utilisateur, $motDePasse, $nomBase);
		$req = "UPDATE DICO_BASE SET STATUT=0 WHERE STATUT=1";
		$connexion_dico->Execute($req);
		$req = "UPDATE DICO_BASE SET STATUT=1 WHERE ID=$id";
		$connexion_dico->Execute($req);
		sendOk();
	} else {
		sendError("Activation non effectu&eacute;e");
	}
}

function sql($requete, $connexion_dico) {
	try {
		$result_array = $connexion_dico->GetAll($requete);
		return $result_array;
	}
	catch(Exception $e) {
		sendError($e->getMessage());
	}
}

function sendList($liste) {
	ob_clean(); // discard any debug/warning output before the JSON
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
?>