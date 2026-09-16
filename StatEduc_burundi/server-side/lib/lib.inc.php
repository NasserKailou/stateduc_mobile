<?php function association(&$tab,$code)
{
	 // echo $code.'<br>';
		//echo '<pre>';  
		//print_r($tab);
    $trouve=false;
	for($i=0;$i<count($tab);$i++)
		{		
		//echo $tab[$i][0];
        
		if ($tab[$i]['code']==$code)
		{
			$trouve=true;
			return $i;

		}
	}
	//exit('PBM!!');
}

function association_inverse(&$tab,$i)
{
	    return $tab[$i]['code'];
    
	}

	

function existe_element(&$elt,&$tab,&$trouve,&$i,&$type)
{
	$i=0;
	$ok=false;	
	switch ($type)
	{
		case 'lc':
		{			
			foreach ($tab as $temp)
			{						
				if(($elt[0]==$temp[0])and ($elt[1]==$temp[1]))
				{			
					$ok=true;
					if (($elt[2]!=$temp[2]))
					{				
						$trouve='U';								
					}						
				}					
				$i++;
			}
			if (!$ok)
			{		
				$trouve='I';	
			}			
			break;
		}
		case 'l':
		case 'c':
		{
			foreach ($tab as $temp)
			{						
				if($elt[0]==$temp[0])
				{			
					$ok=true;
					if (($elt[1]!=$temp[1]))
					{				
						$trouve='U';								
					}	
				}					
				$i++;
			}
			if (!$ok)
			{		
				$trouve='I';	
			}			
			break;
		}		
	}	
	
}		

function post_to_matrice($nom,$nb_ligne,$nb_col,$tab,$type_matrice){
	switch ($type_matrice) {
		case  'lc' : 
		{
			//echo"<br>\$nb_ligne=$nb_ligne||\$nb_col=$nb_col";
			if (count($nom)==1){				
				for ($i=0 ; $i < $nb_ligne ; $i++){
					for($j=0 ; $j < $nb_col; $j++){
						$tmp = $nom['mesure1'].'_'.$i.'_'.$j;
								
						if (isset($tab[$tmp]) && !empty($tab[$tmp]))
							$matrice[$i][$j] = $tab[$tmp];	
					}
				}
			}elseif (count($nom)> 1){
				// cas de deux mesures
				
				for ($i=0 ; $i < $nb_ligne ; $i++){
					for($j=0 ; $j < $nb_col; $j++){
						$val1='';
						$val2='';
						$tmp = $nom['mesure1'].'_'.$i.'_'.$j;						
						if (($tab[$tmp]) !=''){							
							$val1	=	$tab[$tmp];	
						}
						
						$tmp = $nom['mesure2'].'_'.$i.'_'.$j;						
						if (($tab[$tmp]) !=''){							
							$val2	=	$tab[$tmp];	
						}
						if($val1!='' ||$val2!='') {
							$matrice[$i][$j]	=	array($val1,$val2);
						}
						//unset($val1,$val2);
						
					}
				}	
			}
			break;
		}
		
	case 'l':
	{
			// cas d'une seule mesure
			if (!is_array($nom)){
				for ($i=0 ; $i < $nb_ligne ; $i++){					
						$tmp = $nom['mesure1'].'_'.$i;			
						if (isset($tab[$tmp]) && !empty($tab[$tmp]))
							$matrice[$i] = $tab[$tmp];	
					}				
			}else{
				// cas de deux mesures
				for ($i=0 ; $i < $nb_ligne ; $i++){					
						$val1='';
						$val2='';
						$tmp = $nom['mesure1'].'_'.$i;						
						if (($tab[$tmp]) !=''){							
							$val1	=	$tab[$tmp];	
						}
						
						$tmp = $nom['mesure2'].'_'.$i;						
						if (($tab[$tmp]) !=''){							
							$val2	=	$tab[$tmp];	
						}
						if($val1!='' ||$val2!='') {
							$matrice[$i]	=	array($val1,$val2);
						}				
				}	
			}
			break;
		}
	case 'c':
	{
			// cas d'une seule mesure
			if (!is_array($nom)){
				for ($j=0 ; $i < $nb_col ; $j++){					
						$tmp = $nom['mesure1'].'_'.$j;			
						if (isset($tab[$tmp]) && !empty($tab[$tmp]))
							$matrice[$j] = $tab[$tmp];	
					}				
			}else{
				// cas de deux mesures
				for ($j=0 ; $j < $nb_col ; $j++){			
						
						$val1='';
						$val2='';
						$tmp = $nom['mesure1'].'_'.$j;						
						if (($tab[$tmp]) !=''){							
							$val1	=	$tab[$tmp];	
						}
						
						$tmp = $nom['mesure2'].'_'.$j;						
						if (($tab[$tmp]) !=''){							
							$val2	=	$tab[$tmp];	
						}
						if($val1!='' ||$val2!='') {
							$matrice[$j]	=	array($val1,$val2);
						}						
				}	
			}
			break;
		}
	}	
	
	return $matrice;	
}

function associer_identifiant($elt,$indice_cle){
	// Cette fonction permet de déterminer les éléments identifiants de la matrie grile
	$cle =	array();
	if (is_array($elt)){
		if ((count($elt))>$indice_cle){
			for ($i=0;$i<=$indice_cle;$i++)
			{
				$cle[$i]	=	$elt[$i];				
			}	
		}			
	}
	return $cle;			
}

function existe_element_grille_action($cle,$elt,$indice_cle,$matr1){
	/* cette fonction permet de vérifier l'existence d'un élément matricielle
	 * dans une autre matrice de même dimension (n colonnes)et (p lignes)
	 */
	 $trouve	=	false;	 
 	 if( is_array($matr1) ){
		 foreach ($matr1 as $element){
			unset($cle1);
			$cle1	=	associer_identifiant($element,$indice_cle);	 	
					
			if (count(array_diff_assoc($cle,$cle1))==0){
				// l'élément matricielle $elt a été trouvé
				$trouve		=	true;
				// Il faut vérifier si l'objet a subi des modification;	 		
				
				if (count(array_diff_assoc($elt,$element))==0){
					$action	=	'R';	 			
					return $action;
				}
				else {	 			
					//echo "elt<pre>";
					//print_r($elt);
					$GLOBALS['ancien_param_user'][] = $element;
					//echo "element<pre>";
					//print_r($element);
					$action	=	'U';	 			 			
					return $action;
				}	 			 			
			}
		 }
	 }
	 if ($trouve==false) {
	 		// il s'agit d'un élément non trouvé
	 		$action	=	'I';
	 		return $action;	
	 }		
}	

function trouver_table_associee($join,$pile_table_liee,$table_liee,$pile_jointure){
		$result	=	array();
		foreach ($pile_table_liee as $table){
				if($table!=$table_liee){
					foreach($pile_jointure[$table]['champs_jointures'] as $jointure){
						if ($jointure[0]==$join){						
							$result []=array($jointure[1],$jointure[2]);
						}	
					}	
				}	
		}
		return $result;	
}		

function genere_menu(){
		
		// bass : connexion séparée à une source dico
		if(isset($GLOBALS['conn_dico'])){
			$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
		}
		//

		$langues = array();
		if(isset($_GET['langue'])){
				$langues[]		=	$_GET['langue_regen'];
		}else{
				$langues 			= $_SESSION['tab_langues'];
		}

		require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/menu.class.php';
		// Instanciation
		$menu = new Menu();
		
		//Lecture des groupes dans la BD
		$db					= $GLOBALS['conn'];
		
		$requete		= "SELECT CODE_GROUPE FROM ADMIN_GROUPES ORDER BY ORDRE_GROUPE;";
		$groupes		= $GLOBALS['conn_dico']->GetAll($requete);
		foreach ($groupes as $groupe){// Pour chaque groupe
				
				foreach ($langues as $langue){// et pour chaque langue
						// charger le menu principal a partir de DICO_MENU (type_menu=1)
						$menu->charger_menu_principal($langue['CODE_LANGUE']);  
						// on cré le fichier _menu_princ.js, qui contient les données du menu principal de l'utilisateur
						$menu->cree_fichier_menu_principal($groupe['CODE_GROUPE'],$langue['CODE_LANGUE']);        
		
						foreach ($_SESSION['tab_secteur'] as $secteur){// et pour chaque secteur
							// charger le menu saisie a partir de DICO_MENU (type_menu=2)
							$menu->charger_menu_saisie($langue['CODE_LANGUE']);                         
							// charger les themes a partir de DICO_THEME, pour completer le menu
							//$menu->charger_theme($langue[CODE_LANGUE], $secteur[CODE_TYPE_SYSTEME_ENSEIGNEMENT]);
							// on cré le fichier _menu_saisie.js, qui contient les données du menu saisie de l'utilisateur
							// charger le menu report a partir de DICO_MENU (type_menu=3)
							$menu->charger_menu_report($langue['CODE_LANGUE']);
							if(isset($_SESSION['tab_entite_stat']) && is_array($_SESSION['tab_entite_stat']) && count($_SESSION['tab_entite_stat'])>0){
								foreach ($_SESSION['tab_entite_stat'] as $ent_stat){
									$menu->cree_fichier_menu_saisie($groupe['CODE_GROUPE'],$langue['CODE_LANGUE'], $secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']], $ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]);
									$menu->cree_fichier_menu_report($groupe['CODE_GROUPE'],$langue['CODE_LANGUE'], $secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']], $ent_stat[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]);
								}
							}else{
								$menu->cree_fichier_menu_saisie($groupe['CODE_GROUPE'],$langue['CODE_LANGUE'], $secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]);
								$menu->cree_fichier_menu_report($groupe['CODE_GROUPE'],$langue['CODE_LANGUE'], $secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]);
							}
							// charger le menu cubes a partir de DICO_MENU (type_menu=4)
							$menu->charger_menu_cubes($langue['CODE_LANGUE']);
							$menu->cree_fichier_menu_cubes($groupe['CODE_GROUPE'],$langue['CODE_LANGUE'], $secteur[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]);
						}
				}
		}
		//$GLOBALS['msg_global'] = 
}

?>