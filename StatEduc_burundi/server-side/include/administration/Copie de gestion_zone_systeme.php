<?php 
header('Content-type: application/json');
require_once '../../../common.php';
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; ?>
<?php function get_systemes(){/// get_systemes()
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
      	$conn 		= $GLOBALS['conn'];
        if(!isset($_SESSION['fixe_secteurs'])){
			$requete     = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
							FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
							WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
							AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}else{
			$requete     = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
							FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
							WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
							AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')
							AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}
        //echo " <br> $requete <br>";
        $_SESSION['GestZones']['systemes'] = $conn->GetAll($requete);
        //echo'&&&&&&&&&&&<pre>';
        //print_r($_SESSION['GestZones']['systemes']);
        //die();
				
				if(isset($_GET['id_systeme_choisi'])){
						$_SESSION['GestZones']['id_systeme'] 	= $_GET['id_systeme_choisi'];
				}elseif(isset($_SESSION['secteur'])){
						$_SESSION['GestZones']['id_systeme'] 	= $_SESSION['secteur'];
				}
				if(!isset($_SESSION['GestZones']['id_systeme'])){
						$_SESSION['GestZones']['id_systeme'] 			= $_SESSION['GestZones']['systemes'][0]['id_systeme'];
				}
				
				foreach($_SESSION['GestZones']['systemes'] as $i => $systeme){
						if( $systeme['id_systeme'] == $_SESSION['GestZones']['id_systeme'] ){
								$_SESSION['GestZones']['libelle_systeme'] = $systeme['libelle_systeme'];
								break;	
						}
				}
				//echo $requete .'<br>';
    } // FIN  get_systemes()

			///// fonction de récupération des zones du theme
		function get_zone_systeme(){
				get_systemes();
				if(isset($_GET['id_zone_active']))
					$_SESSION['GestZones']['id_zone_active'] = $_GET['id_zone_active'];
				$conn 		= $GLOBALS['conn_dico'];
				
				$requete	="	SELECT *
											FROM       DICO_ZONE_SYSTEME
											WHERE      ID_SYSTEME = ".$_SESSION['GestZones']['id_systeme']." 
											AND 			 ID_ZONE = ".$_SESSION['GestZones']['id_zone_active'];
				//echo $requete ;			
				$all_res = $GLOBALS['conn_dico']->GetAll($requete);
				$_SESSION['GestZones']['zone_systeme'] = array();
				$_SESSION['GestZones']['zone_systeme'] = $all_res[0];
				//$GLOBALS['ValZoneSys']	= $_SESSION['GestZones']['zone_systeme'];
				/*
				$GLOBALS['ValZoneSys'] 														= array();
				$GLOBALS['ValZoneSys']['TYPE_OBJET']							= $zone_systeme['TYPE_OBJET'];
				$GLOBALS['ValZoneSys']['ORDRE_AFFICHAGE']					= $zone_systeme['ORDRE_AFFICHAGE'];
				$GLOBALS['ValZoneSys']['ACTIVER']									= $zone_systeme['ACTIVER'];
				$GLOBALS['ValZoneSys']['ATTRIB_OBJET']						= $zone_systeme['ATTRIB_OBJET'];
				$GLOBALS['ValZoneSys']['AFFICHE_TOTAL']						= $zone_systeme['AFFICHE_TOTAL'];
				$GLOBALS['ValZoneSys']['AFFICHE_TOTAL_VERTIC']							= $zone_systeme['AFFICHE_TOTAL_VERTIC'];
				$GLOBALS['ValZoneSys']['BOUTON_INTERFACE']				= $zone_systeme['BOUTON_INTERFACE'];
				$GLOBALS['ValZoneSys']['CHAMP_INTERFACE']					= $zone_systeme['CHAMP_INTERFACE'];
				$GLOBALS['ValZoneSys']['FONCTION_INTERFACE']			= $zone_systeme['FONCTION_INTERFACE'];
				$GLOBALS['ValZoneSys']['REQUETE_CHAMP_INTERFACE']	= $zone_systeme['REQUETE_CHAMP_INTERFACE'];
				$GLOBALS['ValZoneSys']['REQUETE_CHAMP_SAISIE']		= $zone_systeme['REQUETE_CHAMP_SAISIE'];
				*/
				//echo'<pre>';
				//print_r($_SESSION['GestZones']['zone_systeme']);
				//exit;		
		} //FIN get_zones()
		/*
		function get_libelle_trad($code,$table){
				$conn 		= $GLOBALS['conn'];
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$_SESSION['langue']."'
										AND NOM_TABLE='".$table."'";
				
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		function get_libelle($code,$table,$libelle){
				$lib = get_libelle_trad($code,$table);
				if( trim($lib) <> '')
						return $lib;
				else 
						return $libelle;
		}*/

		function get_types_objets(){
				/*$requete	="	SELECT DISTINCT TYPE_OBJET as type_objet
											FROM       DICO_ZONE_SYSTEME
											WHERE     (NOT (TYPE_OBJET IS NULL) )";*/
					$requete	="	SELECT  TYPE_OBJET, CODE_TYPE_OBJET, LIBELLE_TYPE_OBJET   
									FROM   DICO_TYPE_OBJET
									WHERE CODE_LANGUE = '".$_SESSION['langue']."'";

				$_SESSION['GestZones']['type_objet'] 		= $GLOBALS['conn_dico']->GetAll($requete);
				//$_SESSION['GestZones']['type_objet'][] 	= array('type_objet'=>'');  // pour les non saisis
				
				//echo $requete;
				//echo'<pre>';
				//print_r($_SESSION['GestZones']['zones']);		
		} 	//FIN get_zones()
		
		function get_zones_tabm_by($id_zone, $type_zone){
			
			$requete	='  SELECT DICO_ZONE.ID_ZONE
							FROM DICO_ZONE, DICO_ZONE_SYSTEME 
							WHERE DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE
							AND DICO_ZONE.ID_ZONE <> '.$id_zone.'
							AND DICO_ZONE_SYSTEME.TYPE_OBJET = \''.$type_zone.'\'
							AND DICO_ZONE_SYSTEME.ID_SYSTEME = '.$_SESSION['GestZones']['id_systeme'].' 
							AND DICO_ZONE.ID_TABLE_MERE_THEME IN 
							(	SELECT DICO_ZONE.ID_TABLE_MERE_THEME
								FROM DICO_ZONE
								WHERE DICO_ZONE.ID_ZONE = '.$id_zone.'   ) ;
							';
			//echo $requete . '<br>';
			return($GLOBALS['conn_dico']->GetAll($requete));
			
		} 
			//FIN get_zones()
		function get_type_theme_by($id_zone){
			
			$requete	='  SELECT DICO_THEME.ID_TYPE_THEME
							FROM DICO_ZONE, DICO_THEME 
							WHERE DICO_ZONE.ID_THEME = DICO_THEME.ID
							AND DICO_ZONE.ID_ZONE = '.$id_zone.';
							';
			//echo $requete . '<br>';
			return($GLOBALS['conn_dico']->GetOne($requete));
		} 	//FIN get_zones()
		
		function set_liste_precedents(){
				$conn 		= $GLOBALS['conn_dico'];
				$requete	="	SELECT		DICO_ZONE_SYSTEME.ID_ZONE as precedent_zone
											FROM			DICO_ZONE_SYSTEME, DICO_ZONE 
											WHERE     DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
											AND       DICO_ZONE.ID_THEME = ".$_SESSION['GestZones']['id_theme']." 
											AND				DICO_ZONE_SYSTEME.ACTIVER = 1 
											AND 			DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme'
											AND 			DICO_ZONE_SYSTEME.ID_SYSTEME = ".$_SESSION['GestZones']['id_systeme']." 
											AND 			DICO_ZONE_SYSTEME.ID_ZONE <> ".$_SESSION['GestZones']['id_zone_active'];
				$_SESSION['GestZones']['liste_precedents'] = $GLOBALS['conn_dico']->GetAll($requete);
				$_SESSION['GestZones']['liste_precedents'][] = array('precedent_zone'=>0); // pour avoir ds la liste des précédents le 0 :synonyme de 1er élément
				$_SESSION['GestZones']['liste_precedents'][] = array('precedent_zone'=>''); // pour les non saisis
		}
		function recherche_libelle($code,$langue,$table){
				// permet de récupérer le libellé dans la table de traduction
				// en fonction de la langue et de la table  aussi
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				//echo $requete;
				//print_r($conn);
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		function set_champs_zone_systeme(){///// Préparation des champs de DICO_ZONE 
				if(!($GLOBALS['champs_zone_systeme'])){
						$GLOBALS['champs_zone_systeme'] 		= array();
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ID_ZONE', 									'type_champ' => 'int'		, 'manip' => false	);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ID_SYSTEME', 							'type_champ' => 'int'		, 'manip' => false	);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'TYPE_OBJET', 							'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'ORDRE_AFFICHAGE', 					'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'ACTIVER', 									'type_champ' => 'int' 	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ATTRIB_OBJET', 						'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'AFFICHE_TOTAL', 						'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'EXPRESSION', 						'type_champ' => 'text'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'LIB_EXPR', 						'type_champ' => 'text'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'AFFICH_VERTIC_MES', 						'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'AFFICHE_TOTAL_VERTIC', 			'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'AFFICHE_SOUS_TOTAUX', 			'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'PRECEDENT_ZONE', 					'type_champ' => 'int'		, 'manip' => false		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'BOUTON_INTERFACE', 				'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'TABLE_INTERFACE', 					'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'CHAMP_INTERFACE', 					'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'REQUETE_CHAMP_INTERFACE', 	'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'REQUETE_CHAMP_SAISIE', 		'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'FONCTION_INTERFACE', 			'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'VALEUR_CONSTANTE', 				'type_champ' => 'int'		, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'CTRL_SAISIE_OPERATEUR', 			'type_champ' => 'text'	, 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'CTRL_SAISIE_EXPRESSION', 			'type_champ' => 'text'	, 'manip' => true		);
				}
		}

		function MAJ_ZoneSysteme(){ /// Mise à jour zone n°iZone de la table mére n°iTab 
				$conn = $GLOBALS['conn_dico'];
				$GLOBALS['OkAction'] = 1;
				$GLOBALS['post'] = 1;
				
				$req_exist = 'SELECT * FROM   DICO_ZONE_SYSTEME
										WHERE ID_ZONE  = '.$_SESSION['GestZones']['id_zone_active'].'
										AND ID_SYSTEME = '.$_SESSION['GestZones']['id_systeme'];
				$exists = $GLOBALS['conn_dico']->GetAll($req_exist);

				if( is_array($exists) and (count($exists) > 0) ){
						// UPDATE
						$GLOBALS['Action']		= 'UpdZone';
				}else{
						// INSERT
						$GLOBALS['Action']		= 'AddZone';
				}

				foreach($GLOBALS['champs_zone_systeme'] as $champ){
						if( (trim($_POST[$champ['nom_champ']]) <> '') and ($champ['manip'] == true) and ($champ['type_champ'] == 'int') ){
								if (!preg_match ("/^(0|([1-9][0-9]*))$/", trim($_POST[$champ['nom_champ']]))){
										$GLOBALS['OkAction'] 	= 0;
										break;
								}
						}
				}
				
				if( (trim($_POST['TYPE_OBJET']) == 'dimension_ligne') or (trim($_POST['TYPE_OBJET']) == 'dimension_colonne') ){
					
					$exist_dimension_ligne 		= get_zones_tabm_by($_SESSION['GestZones']['id_zone_active'], 'dimension_ligne');
					$exist_dimension_colonne 	= get_zones_tabm_by($_SESSION['GestZones']['id_zone_active'], 'dimension_colonne');

					if(get_type_theme_by($_SESSION['GestZones']['id_zone_active']) == 3){ // cas de formulaire
						if(trim($_POST['TYPE_OBJET']) == 'dimension_ligne'){
							if( is_array($exist_dimension_ligne) and count($exist_dimension_ligne) ){
								// existe déjà un type dimension ligne
								$GLOBALS['OkAction'] 	= 0;
							}
						}elseif(trim($_POST['TYPE_OBJET']) == 'dimension_colonne'){
							if( is_array($exist_dimension_colonne) and count($exist_dimension_colonne) ){
								// existe déjà un type dimension_colonne
								$GLOBALS['OkAction'] 	= 0;
							}
						}
					}elseif( (get_type_theme_by($_SESSION['GestZones']['id_zone_active']) == 2) or (get_type_theme_by($_SESSION['GestZones']['id_zone_active']) == 4) ){
						// cas de grille ou de grille_Eff
						
						if( (is_array($exist_dimension_ligne) and count($exist_dimension_ligne)) or (is_array($exist_dimension_colonne) and count($exist_dimension_colonne)) ){
							// existe déjà un type dimension_colonne
							$GLOBALS['OkAction'] 	= 0;
							
						}
					}
				}
				
				if( $GLOBALS['OkAction'] <> 0 ){

						if( is_array($exists) and (count($exists) > 0) ){
								// UPDATE
								$sql = 'UPDATE    DICO_ZONE_SYSTEME SET '."\n";
								$virg = false;
								foreach($GLOBALS['champs_zone_systeme'] as $i => $champ){
										if( $champ['manip'] == true ){
												if($virg == true){
														$sql .= ', '."\n";
												}
												$virg = true;
												if(trim($_POST[$champ['nom_champ']]) == ''){
														$sql .= $champ['nom_champ'].'='."NULL";
												}else{
														if($champ['type_champ']=='int'){
																$sql .= $champ['nom_champ'] . '=' . trim($_POST[$champ['nom_champ']]);
														}
														elseif($champ['type_champ']=='text' && $champ['nom_champ']<>'ATTRIB_OBJET'){
																$sql .= $champ['nom_champ'] . '=' . $conn->qstr(trim($_POST[$champ['nom_champ']]));
														}elseif($champ['type_champ']=='text' && $champ['nom_champ']=='ATTRIB_OBJET'){
																$_POST[$champ['nom_champ']] = preg_replace("/''/","\"",trim($_POST[$champ['nom_champ']]));
																$sql .= $champ['nom_champ'] . '= \'' . preg_replace("/'/","''",trim($_POST[$champ['nom_champ']])).'\'';
														}
												}
										}
								}
								$sql .= "\n";
								$requete = $sql . '	WHERE ID_ZONE  = '.$_SESSION['GestZones']['id_zone_active'].'
													 					AND ID_SYSTEME = '.$_SESSION['GestZones']['id_systeme'];

											 //sendList($requete); return;
								}
						else{
								// INSERT
								$tab_req = array();
								$tab_req[] = array( 'champ' => 'ID_ZONE'			, 'val' => $_SESSION['GestZones']['id_zone_active']	,'type' => 'int'	);
								$tab_req[] = array( 'champ' => 'ID_SYSTEME' 	, 'val' => $_SESSION['GestZones']['id_systeme']			,'type' => 'int'	);
		
								foreach($GLOBALS['champs_zone_systeme'] as $champ){
										if( (trim($_POST[$champ['nom_champ']]) <> '') and ($champ['manip'] == true) ){
												$tab_req[] = array( 'champ' => $champ['nom_champ'], 'val' => $_POST[$champ['nom_champ']], 'type' => $champ['type_champ']	);
										}
								}
								$sql_champs = 'INSERT INTO DICO_ZONE_SYSTEME (';
								$sql_values = 'VALUES (';
								
								foreach($tab_req as $i => $tab){
										if($i>0){
												$sql_champs .= ', ';
												$sql_values .= ', ';
										}
										$sql_champs .= $tab['champ'];
										if($tab['type']=='int'){
												$sql_values .= trim($tab['val']);
										}
										elseif($tab['type']=='text'){
												$sql_values .=  $conn->qstr(trim($tab['val']));
										}
								}
								$sql_champs .= ') ';
								$sql_values .= ') ';
								
								$requete = $sql_champs . $sql_values ;
						}
				}
				//echo '<br>'.$requete.'<br>';
				if ($GLOBALS['conn_dico']->Execute($requete) === false){
						$GLOBALS['OkAction'] 	= 0;
						echo $requete.'<br>';
				}
		}
		function get_izone_itab($val_id_zone){
				//echo '<br>param='.$val_id_zone;	
				foreach( $_SESSION['GestZones']['zones'] as $iTab => $tables_meres ){
						foreach( $tables_meres as $iZone=> $zone ){
								//echo '<br>val ='.$iZone.'=>'.$zone['ID_ZONE'] ;
								if($zone['ID_ZONE']==$val_id_zone){
										$tab_izone_itab	=	array();
										$tab_izone_itab['iZone']	=	$iZone;
										$tab_izone_itab['iTab']		=	$iTab;
										return $tab_izone_itab ;	
								}
						}
				}
		} //FIN get_izone_itab()
		
		if( $_POST['ActionZoneSyst'] == '1'){ // Actions 
				$GLOBALS['ActionPost'] = 1;
				get_zone_systeme();
				set_champs_zone_systeme();
				MAJ_ZoneSysteme();
				sendOk();
		}// FIN Actions 
		else{
				if( isset($_GET['supp_zs']) && trim($_GET['supp_zs']) <> ''){
					$GLOBALS['OkAction'] 	= 1;
					$GLOBALS['Action']		= 'SupZone';
					$GLOBALS['post'] 		= 1;
					$requete = ' DELETE FROM DICO_ZONE_SYSTEME  WHERE  ID_ZONE = '.$_GET['supp_zs'].'
									AND ID_SYSTEME = '.$_SESSION['GestZones']['id_systeme'];
					if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$GLOBALS['OkAction'] 	= 0;
					if ($GLOBALS['OkAction'] == 0) echo $requete.'<br>';
				}
				
				get_zone_systeme();
				set_champs_zone_systeme();
				$TabValZoneSys = $_SESSION['GestZones']['zone_systeme'];
				
		}
		
	function sendList($liste) {
		$posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>$liste);	
		echo json_encode($posts);
	}
	
	function sendError($message) {
		$posts = array('se_statut'=>101,'se_message'=>$message,'se_datas'=>NULL);	
		echo json_encode($posts);
	}
	
	function sendOk() {
		$posts = array('se_statut'=>200,'se_message'=>'ok','se_datas'=>'ok');	
		echo json_encode($posts);
	}
?>

