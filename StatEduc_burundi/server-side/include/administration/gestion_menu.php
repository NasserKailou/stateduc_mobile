<script type="text/Javascript">
function toggle_menu() {
        var id_menu     = form_menu.menus_dispo.options[form_menu.menus_dispo.selectedIndex].value;
        location.href   = '?val=gestmenu&menu_en_cours='+id_menu;
}
function toggle_type_menu() {
        var id_menu     = form_menu.type_menu.options[form_menu.type_menu.selectedIndex].value;
        location.href   = '?val=gestmenu&type_menu_en_cours='+id_menu;
}
function vider_champs(val_type){
	if(val_type=='' || val_type=='OlapFile'){
		form_menu.server_olap.value = "";
		form_menu.server_olap.style.backgroundColor = "#CCCCCC";
		form_menu.server_olap.disabled = true;
		form_menu.base_olap.value = "";
		form_menu.base_olap.style.backgroundColor = "#CCCCCC";
		form_menu.base_olap.disabled = true;
	}
	if(val_type=='OlapServer'){
		form_menu.server_olap.style.backgroundColor = "#FFFFFF";
		form_menu.server_olap.disabled = false;
		form_menu.base_olap.style.backgroundColor = "#FFFFFF";
		form_menu.base_olap.disabled = false;
		
	}
}
</script>

<?php // bass : connexion séparée à une source dico
	if(isset($GLOBALS['conn_dico'])){
		$GLOBALS['conn'] = $GLOBALS['conn_dico'] ;
	}
	//
	lit_libelles_page(__FILE__);
	$_SESSION['Nouveau'] = recherche_libelle_page('nouveau');
	if($_SESSION['Nouveau']=='' && $_SESSION['langue']=='fr') $_SESSION['Nouveau'] = 'Nouveau'; else $_SESSION['Nouveau'] = 'New';
    
    function supprime_menu(){
        // Supprime le menu courant
        // TODO: et la suppression en cascade?
        
        // Suppression du libellé du menu (nom_interne) dans la table DICO_TRADUCTION
        $requete                = "DELETE FROM DICO_TRADUCTION
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_menu'][$_SESSION['i']]['ID']."
                                    and NOM_TABLE='DICO_MENU';";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
        
        // Suppression dans la table DICO_MENU
        $requete                = "DELETE FROM DICO_MENU
                                    WHERE ID =".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
		// Suppression dans la table ADMIN_DROITS
        $requete                = "DELETE FROM ADMIN_DROITS
                                    WHERE ID_MENU =".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].";";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
       
    }
		

    function recherche_nouvel_id($tab_table, $nom_id){
        // Recherche d'une nouvelle valeur de l'ID disponible dans une table 
        // dont les éléments sont passés dans un tableau en paramètre
        // Fonction utilisée pour l'ajout d'enregistrements        
        /*$nouvel_id              = 0;
        foreach ($tab_table as $ligne){
            if ($nouvel_id < $ligne[$nom_id]){
                $nouvel_id      = $ligne[$nom_id];
            }
        }*/
		$req_max 	=	'SELECT    MAX(ID) FROM  DICO_MENU';
		$nouvel_id 	=	$GLOBALS['conn_dico']->getOne($req_max) ;
        $nouvel_id  += 10;
        return $nouvel_id;
        
    }

    function ajout_menu(){
		// Création d'un nouvel enregistrement dans DICO_MENU et dans DICO_TRADUCTION
        
        // Il faut d'abord trouver une nouvelle valeur de l'ID disponible dans DICO_MENU 
        $nouvel_id              = recherche_nouvel_id($_SESSION['tab_menu'], 'ID');
		//echo '<pre>';
		//print_r($_SESSION['tab_menu']);
		//die('nouvel_id:'.$nouvel_id);
        // Création de l'enregistrement dans DICO_MENU
        $db                     = $GLOBALS['conn_dico'];
        $requete                = "INSERT INTO DICO_MENU
                                    VALUES (".$nouvel_id.", ".$_SESSION['type_menu'].", 0, 0, '', 130,'','','');";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        
        // Création de l'enregistrement dans ADMIN_DROITS (par défaut pour l'administrateur
        $db                     = $GLOBALS['conn_dico'];
        $requete                = "INSERT INTO ADMIN_DROITS
                                    VALUES (1, ".$nouvel_id.");";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
        // pour que les nouveaux éléments du menu Cube soient automatiquement attribués au groupe Utilisateurs cubes
		if($_SESSION['type_menu']==4){
			$requete = "INSERT INTO ADMIN_DROITS
                        VALUES (4, ".$nouvel_id.");";
			//print $requete;
			$GLOBALS['conn_dico']->Execute($requete);
		}
        // Création du libellé dans DICO_TRADUCTION
        $requete                = "INSERT INTO DICO_TRADUCTION
                                    VALUES (".$nouvel_id.", 'DICO_MENU', '".$_SESSION['langue']."', '".$_SESSION['Nouveau']."', NULL, NULL);";
        //print $requete;
        $GLOBALS['conn_dico']->Execute($requete);
				insert_traduction('DICO_TRADUCTION', $nouvel_id, 'DICO_MENU', $_SESSION['langue'], $_SESSION['Nouveau'], 1);
        
    }

    function maj_menu(){
        // Met à jour les éléments du menu courant (après click sur "Enregistrer")
        
        // mise à jour du libellé dans DICO_TRADUCTION
        if ($_POST['nom_menu'] <> $_SESSION['tab_menu'][$_SESSION['i']]['THEME_LIBELLE']){
            $requete            = "UPDATE DICO_TRADUCTION
                                    SET LIBELLE = ".$GLOBALS['conn_dico']->qstr($_POST['nom_menu'])."
                                    WHERE CODE_NOMENCLATURE =".$_SESSION['tab_menu'][$_SESSION['i']]['ID']."
                                    and NOM_TABLE='DICO_MENU';";
            //print $requete.'<BR>';
            $GLOBALS['conn_dico']->Execute($requete);
        }
        
        //mise à jour des droits
        foreach($_SESSION['tab_groupes'] as $k=>$groupe){
            // Recherhe s'il existe un droit pour ce groupe dans la BD
            if (isset($_SESSION['tab_droits'])){
                foreach ($_SESSION['tab_droits'] as $droits){
                    if ($droits[CODE_GROUPE] == $groupe[CODE_GROUPE]){
                        if (isset($_POST['droits_'.$groupe['CODE_GROUPE']])){
                        } else  { // il existe dans la BD mais pas dans le POST -> il faut le détruire
                                    $requete    = "DELETE FROM ADMIN_DROITS
                                                    WHERE CODE_GROUPE =".$droits['CODE_GROUPE']."
                                                    and ID_MENU=".$droits['ID_MENU'].";";
                                    //print $requete;
                                    $GLOBALS['conn_dico']->Execute($requete);
                                } 
                        break;
                    }
                }
            } else  { // Il n'existe aucun droit en BD
                        if (isset($_POST['droits_'.$groupe['CODE_GROUPE']])){ // Il existe un POST
                            $requete            = "INSERT INTO ADMIN_DROITS
                                                    VALUES (".$droits[CODE_GROUPE].", ".$droits['ID_MENU'].");";
                            //print $requete;
                            $GLOBALS['conn_dico']->Execute($requete);
                        }
                    }
        }
        
        // Création d'un nouveau droit (existe dans le post mais pas dans la BD)
        foreach($_SESSION['tab_groupes'] as $k=>$groupe){
            if (isset($_POST['droits_'.$groupe['CODE_GROUPE']])){
                // Recherche existence de ce droit dans la BD
                $requete                = "SELECT A_D.CODE_GROUPE, A_D.ID_MENU
                                            FROM ADMIN_DROITS AS A_D
                                            WHERE A_D.CODE_GROUPE = ".$groupe['CODE_GROUPE']."
                                            And A_D.ID_MENU = ".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].";";
                //print $requete;
                $tab_droits  = $GLOBALS['conn_dico']->GetAll($requete);
                if (count($tab_droits) == 0){
                    $requete            = "INSERT INTO ADMIN_DROITS
                                            VALUES (".$groupe[CODE_GROUPE].", ".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].");";
                    //print $requete;
                    $GLOBALS['conn_dico']->Execute($requete);
                }
            }
        }
        
        // mise à jour des autres informations directement sur la table DICO_MENU
        $requete            = "UPDATE DICO_MENU
                                SET PERE = ".$_POST['menu_pere'].",
                                PRECEDENT = ".$_POST['precedent'].",
                                ACTION_MENU = '".$_POST['action_menu']."',
                                TAILLE = ".$_POST['taille_menu'].",
							    TYPE_OLAP_SOURCE = '".$_POST['type_olap_source']."',
								SERVER_OLAP = '".$_POST['server_olap']."',
								BASE_OLAP = '".$_POST['base_olap']."'
                                WHERE ID =".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].";";
        //print $requete;
        $res = $GLOBALS['conn_dico']->Execute($requete);
        //var_dump($res);
    }
        
    function lit_droits(){
        // Chargement des infos concernant les droits du menu courant
        $db                     = $GLOBALS['conn_dico'];
        $requete                = "SELECT A_D.CODE_GROUPE, A_D.ID_MENU
                                    FROM ADMIN_DROITS AS A_D, ADMIN_GROUPES AS A_G
                                    WHERE A_D.CODE_GROUPE = A_G.CODE_GROUPE
                                    And A_D.ID_MENU = ".$_SESSION['tab_menu'][$_SESSION['i']]['ID'].";";
        $_SESSION['tab_droits']  = $GLOBALS['conn_dico']->GetAll($requete);
        
    } //FIN lit_droits()

    function lit_groupes(){
        // Chargement des infos concernant les groupes 
        $db                     = $GLOBALS['conn_dico'];
        /*$requete                = "SELECT A_G.CODE_GROUPE, D_TRAD.LIBELLE
                                    FROM ADMIN_GROUPES AS A_G, DICO_TRADUCTION AS D_TRAD
                                    WHERE A_G.CODE_GROUPE = D_TRAD.CODE_NOMENCLATURE
                                    And D_TRAD.NOM_TABLE='ADMIN_GROUPES'
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."';";*/
		 $requete                = "SELECT CODE_GROUPE, LIBELLE_GROUPE AS LIBELLE
                                    FROM ADMIN_GROUPES
									WHERE CODE_GROUPE <> 0
                                   	ORDER BY ORDRE_GROUPE;";
        
		$_SESSION['tab_groupes']  = $GLOBALS['conn_dico']->GetAll($requete);

    } //FIN lit_groupes()

    function lit_menu(){
        // Chargement des infos concernant les menus
        $requete                = "SELECT D_M.ID, D_M.PERE, D_M.PRECEDENT, D_M.TAILLE, D_M.ACTION_MENU,
                                    D_TRAD.LIBELLE, D_M.TYPE_OLAP_SOURCE, D_M.SERVER_OLAP, D_M.BASE_OLAP
                                    FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
                                    And D_TRAD.NOM_TABLE='DICO_MENU'
                                    And D_M.TYPE_MENU=".$_SESSION['type_menu']."
                                    And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'
                                    ORDER BY LIBELLE;";
        //print $requete;
        $_SESSION['tab_menu']  = $GLOBALS['conn_dico']->GetAll($requete);
        lit_droits();

    } //FIN lit_menu()

   if (isset($_POST['btn_menu'])){
        $_SESSION['message']= '';
        switch ($_POST['btn_menu']){
            case '>':           if ($_SESSION['i'] < count($_SESSION['tab_menu'])-1){
                                    $_SESSION['i']          += 1; 
                                    lit_droits();
                                }
                                break;
            case '<':           if ($_SESSION['i'] > 0){
                                    $_SESSION['i']          -= 1;
                                    lit_droits();
                                }
                                break;
            case '>>':          $_SESSION['i']               = count($_SESSION['tab_menu'])-1; 
                                lit_droits();
                                break;
            case '<<':          $_SESSION['i']               = 0; 
                                lit_droits();
                                break;
            case recherche_libelle_page(Ajout):
                                ajout_menu(); // Création d'un nouvel enregistrement dans DICO_MENU et dans DICO_TRADUCTION
                                lit_menu(); // Lecture dans la BD pour rafraichir
                                foreach($_SESSION['tab_menu'] as $k=>$row){
                                    if ($row['LIBELLE'] == $_SESSION['Nouveau']){
                                        $_SESSION['i']        = $k; 
                                    }
                                }
                                lit_droits();
                                break;
            case recherche_libelle_page(Enregistre):
                                maj_menu(); // Mise à jour des modifications sur l'enregistrement courant
                                lit_menu(); // Lecture dans la BD pour rafraichir
                                lit_droits();
                                break;
            case recherche_libelle_page(Supprime):
                                supprime_menu(); // Suppression du MENU courant
                                $_SESSION['i']          = 0;
                                lit_menu(); // Lecture dans la BD pour rafraichir
                                lit_droits();
                                break;
        }
    }
    
    else if (!isset($_GET['menu_en_cours']) and !isset($_POST['btn_menu_systeme'])){
        $_SESSION['type_menu']          = 1;
        lit_groupes();                   // Lecture des groupes pour la gestion des droits
        lit_menu();                    // Lecture des menus dans la BD au démarrage
        $_SESSION['i']                  = 0;
        lit_droits();
        
    } else if (isset($_GET['menu_en_cours'])){
        // Accès direct au menu_en_cours (par le select)
        $_SESSION['i']                  = $_GET['menu_en_cours'];
        lit_droits();

    } 
		if( isset($_POST['btn_genere']) and trim($_POST['btn_genere'])<>'' ){ 
				require_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
        genere_menu();
				lit_menu();
				//die('fin genere');
    }	
		if (isset($_GET['type_menu_en_cours'])){
        // Modification du type de menu_en_cours (par le select)
        $_SESSION['type_menu']          = $_GET['type_menu_en_cours'];
        lit_menu();                    // Lecture des menus après changement de type_menu
        lit_droits();
    }

	// Cas d'un serveur olap                
	$sql ='SELECT * FROM DICO_OLAP_AUTRE_CONNEXION WHERE SGBD='.'\'OlapServer\'';
	$result =  $GLOBALS['conn_dico']->GetAll($sql); 
	$defaut_olap_server = $result[0]['SERVEUR'];
	$defaut_olap_db = $result[0]['DB'];
?>









<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>

<title>Document sans titre</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form_menu" method="post" action="">
<BR><BR>
<table align="center"><caption><?php echo recherche_libelle_page('MenuManagement'); ?></caption><TR><TD>
<table align="center" style="border: none;">
  <tr> 
    <td><?php echo recherche_libelle_page('TypMenu'); ?></td>
    <td>
        <select name="type_menu" onChange="toggle_type_menu()">
        <?php echo "<option value='1'";
            if ($_SESSION['type_menu'] == 1){
                echo " selected";
            }
            echo ">".recherche_libelle_page('MenuPrincipal')."</option>";
			
            echo "<option value='2'";
            if ($_SESSION['type_menu'] == 2){
                echo " selected";
            }
            echo ">".recherche_libelle_page('MenuSaisie')."</option>";
			
            echo "<option value='3'";
            if ($_SESSION['type_menu'] == 3){
                echo " selected";
            }
            echo ">".recherche_libelle_page('MenuReport')."</option>";
			
			echo "<option value='4'";
            if ($_SESSION['type_menu'] == 4){
                echo " selected";
            }
            echo ">".recherche_libelle_page('MenuCubes')."</option>";
        ?>
        </select>
	</td>
  </tr>
  <tr> 
    <td>&nbsp;</td>
  </tr>
  <tr> 
    <td><?php echo recherche_libelle_page('NumMenu'); ?></td>
    <td><?php echo $_SESSION['tab_menu'][$_SESSION['i']]['ID'] ?></td>
  </tr>
  <tr> 
    <td><?php echo recherche_libelle_page('NomMenu'); ?></td>
    <td>
        <input type="text" name="nom_menu" value="<?php echo $_SESSION['tab_menu'][$_SESSION['i']]['LIBELLE'] ?>">
	</td>
  </tr>
	<?php if($_SESSION['type_menu'] == 4){
  ?>
  	 <tr> 
    <td><?php echo recherche_libelle_page('TypCub'); ?></td>
    <td>
        <select name="type_olap_source" onChange="vider_champs(this.value)">
	    <option value="">---</option>
		<?php echo "<option value='OlapServer'";
            if ($_SESSION['tab_menu'][$_SESSION['i']]['TYPE_OLAP_SOURCE'] == 'OlapServer'){
                echo " selected";
            }
            echo ">Server Cube</option>";
			
            echo "<option value='OlapFile'";
            if ($_SESSION['tab_menu'][$_SESSION['i']]['TYPE_OLAP_SOURCE'] == 'OlapFile'){
                echo " selected";
            }
            echo ">Local Cube</option>";
        ?>
        </select>
	</td>
  	</tr>
  	<tr> 
   	<td><?php echo recherche_libelle_page('server_olap'); ?></td>
   	<?php ;
		if($_SESSION['tab_menu'][$_SESSION['i']]['SERVER_OLAP']=='' && $_SESSION['tab_menu'][$_SESSION['i']]['TYPE_OLAP_SOURCE']=='OlapServer') $server = $defaut_olap_server;
		else $server = $_SESSION['tab_menu'][$_SESSION['i']]['SERVER_OLAP'];
	?>
	<td><input type="text" name="server_olap" value="<?php echo $server ?>" <?php if($server=='') echo ' style="background-color:#CCCCCC"';?>/></td>
  	</tr>
  	<tr> 
	<?php ;
		if($_SESSION['tab_menu'][$_SESSION['i']]['BASE_OLAP']=='' && $_SESSION['tab_menu'][$_SESSION['i']]['TYPE_OLAP_SOURCE']=='OlapServer') $db_olap = $defaut_olap_db;
		else $db_olap = $_SESSION['tab_menu'][$_SESSION['i']]['BASE_OLAP'];
	?>
   	<td><?php echo recherche_libelle_page('base_olap'); ?></td>
   	<td><input type="text" name="base_olap" value="<?php echo $db_olap ?>" <?php if($db_olap=='') echo ' style="background-color:#CCCCCC"';?>/></td>
  	</tr>
	<?php
	}
	?>
  <tr> 
    <td><?php
 if ($_SESSION['type_menu'] == 4)
 	echo recherche_libelle_page('Action_CubeName');
 else
 	echo recherche_libelle_page('Action'); ?></td>
    <td>
        <input type="text" name="action_menu" value="<?php echo $_SESSION['tab_menu'][$_SESSION['i']]['ACTION_MENU'] ?>">
	</td>
  </tr>
  <tr> 
    <td><?php echo recherche_libelle_page('Pere'); ?></td>
    <td>
        <select name="menu_pere">
        <?php echo "<option value='0'";
            if ($v['ID'] == 0){
                echo " selected";
            }
            echo ">Premier</option>";
            foreach ($_SESSION['tab_menu'] as $k => $v){
				//Pour eclairer le choix avant les duplications des noms de menu
		        if($_SESSION['type_menu']==4){
					$requete        = "SELECT D_TRAD.LIBELLE
										FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
										WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
										And D_TRAD.NOM_TABLE='DICO_MENU'
										And D_M.ID=".$v['PERE']."
										And D_M.TYPE_MENU=".$_SESSION['type_menu']."
										And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'";
					$pere  = $GLOBALS['conn_dico']->GetOne($requete);
					$pere = ' ('.$pere.')';
				}else{
					$pere = '';
				}
				//Fin eclairer choix
			    if ($v['ID'] <> $_SESSION['tab_menu'][$_SESSION['i']]['ID']){ // Il est inutile de mettre l'éléement courant dans le combo
                    echo "<option value='".$v['ID']."'";
                    if ($v['ID'] == $_SESSION['tab_menu'][$_SESSION['i']]['PERE']){
                        echo " selected";
                    }
                    echo ">".$v['LIBELLE'].$pere."</option>";
                }
            }
        ?>
        </select>
        <?php if($_SESSION['type_menu']==4)
		echo '('.recherche_libelle_page('pere_menu').')';
		else
		echo '('.$_SESSION['tab_menu'][$_SESSION['i']]['PERE'].')';
		?>
	</td>
  </tr>
  <tr> 
    <td><?php echo recherche_libelle_page('Preced'); ?></td>
    <td>
        <select name="precedent">
        <?php echo "<option value='0'";
            if ($v['ID'] == 0){
                echo " selected";
            }
            echo ">Premier</option>";
            foreach ($_SESSION['tab_menu'] as $k => $v){
               	//Pour eclairer le choix avant les duplications des noms de menu
		        if($_SESSION['type_menu']==4){
					$requete        = "SELECT D_TRAD.LIBELLE
										FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
										WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
										And D_TRAD.NOM_TABLE='DICO_MENU'
										And D_M.ID=".$v['PERE']."
										And D_M.TYPE_MENU=".$_SESSION['type_menu']."
										And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'";
					$pere  = $GLOBALS['conn_dico']->GetOne($requete);
					$pere = ' ('.$pere.')';
				}else{
					$pere = '';
				}
				//Fin eclairer choix
			if ($v[ID] <> $_SESSION['tab_menu'][$_SESSION['i']]['ID']){ // Il est inutile de mettre l'éléement courant dans le combo
                    echo "<option value='".$v['ID']."'";
                    if ($v['ID'] == $_SESSION['tab_menu'][$_SESSION['i']]['PRECEDENT']){
                        echo " selected";
                    }
                    echo ">".$v['LIBELLE'].$pere."</option>";
                }
            }
        ?>
        </select>
        <?php if($_SESSION['type_menu']==4)
		echo '('.recherche_libelle_page('pere_menu').')';
		else
 		echo '('.$_SESSION['tab_menu'][$_SESSION['i']]['PRECEDENT'].')';
		?>
	</td>
  </tr>
  <tr> 
    <td><?php echo recherche_libelle_page('TailleMenu'); ?></td>
    <td>
        <input type="text" name="taille_menu" value="<?php echo $_SESSION['tab_menu'][$_SESSION['i']]['TAILLE'] ?>">
	</td>
  </tr>
  <tr>
    <?php 
		echo "\n".'<td colspan="2">';
		$cpt = 0;
		foreach($_SESSION['tab_groupes'] as $k=>$row){
            if(count($_SESSION['tab_groupes'])>4 && $cpt==3) echo '<br/>';
			echo "\n".' '. $row['LIBELLE'] . ' : ';
            echo '<input type="checkbox" name="droits_'.$row['CODE_GROUPE'].'"';
            foreach($_SESSION['tab_droits'] as $row){
                if ($row['CODE_GROUPE'] == $k+1){
                    echo ' checked';
                }
            }
            echo '>';
			$cpt++;
        }
		echo "\n".'</td>';
    ?>
  </tr>
  <tr>
  </tr>
</table>
<table align="center" style="border: none;">
  <tr>
    <td>
        <input type="submit" name="btn_menu" <?php echo ' value="'.recherche_libelle_page('Ajout').'"'  ?>>
	</td>
    <td>
		<input type="submit" name="btn_menu" <?php echo ' value="'.recherche_libelle_page('Enregistre').'"' ?>>
	</td>
    <td>
        <input type="submit" name="btn_menu" <?php echo ' value="'.recherche_libelle_page('Supprime').'"' ?>>
	</td>
    <td>
        <input type="submit" name="btn_menu" value="<<">
	</td>
    <td>
		<input type="submit" name="btn_menu" value="<">
	</td>
    <td>
        <input type="submit" name="btn_menu" value=">">
	</td>
    <td>
        <input type="submit" name="btn_menu" value=">>">
	</td>
  </tr>
</table>
<table align="center" style="border: none;">
  <tr> 
    <td>  
        <select name="menus_dispo" size="12" onChange="toggle_menu()">
        <?php //echo '-->'.$_SESSION['i'];
            foreach ($_SESSION['tab_menu'] as $k => $v){
               	//Pour eclairer le choix avant les duplications des noms de menu
		        if($_SESSION['type_menu']==4){
					$requete        = "SELECT D_TRAD.LIBELLE
										FROM DICO_MENU AS D_M, DICO_TRADUCTION AS D_TRAD
										WHERE D_M.ID = D_TRAD.CODE_NOMENCLATURE
										And D_TRAD.NOM_TABLE='DICO_MENU'
										And D_M.ID=".$v['PERE']."
										And D_M.TYPE_MENU=".$_SESSION['type_menu']."
										And D_TRAD.CODE_LANGUE='".$_SESSION['langue']."'";
					$pere  = $GLOBALS['conn_dico']->GetOne($requete);
					$pere = ' ('.$pere.')';
				}else{
					$pere = '';
				}
				//Fin eclairer choix
				echo "<option value='".$k."'";
                if ($v['ID'] == $_SESSION['tab_menu'][$_SESSION['i']]['ID']){
                    echo " selected";
                }
                echo ">".$v['LIBELLE'].$pere."</option>";
            }
        ?>
        </select>
	</td>
  </tr>
</table> 
</td>
<td>
	<table>
		<tr>
			<td><input type="submit" name="btn_genere" <?php echo ' value="'.recherche_libelle_page('genere').'"'; ?>></td>
		</tr>
	</table>
</td>
</tr></table>

</form>
</body>
</html>
<script type="text/Javascript" language="javascript">
     vider_champs(form_menu.type_olap_source.options[form_menu.type_olap_source.selectedIndex].value);
</script>