<?php lit_libelles_page('/import_theme.php');
	function get_themes(){
        $conn                     = $GLOBALS['conn_dico'];
        /*
		$requete                = ' SELECT D_T.ID AS id_thm, D_TRAD.LIBELLE AS libelle_thm
                                    FROM DICO_THEME AS D_T, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
									AND D_T.ID <>  '.$_GET['cur_thm'].'
									AND D_T.ID_TYPE_THEME =  '.$_GET['typ_thm'].'
                                    AND D_TRAD.NOM_TABLE=\'DICO_THEME\'
                                    AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		*/							
		$requete                = ' SELECT D_T.ID AS id_thm, D_TRAD.LIBELLE AS libelle_thm
                                    FROM DICO_THEME AS D_T, DICO_THEME_SYSTEME AS D_T_S, DICO_TRADUCTION AS D_TRAD
                                    WHERE D_T.ID = D_T_S.ID
									AND D_T.ID = D_TRAD.CODE_NOMENCLATURE
									AND D_T.ID <>  '.$_GET['cur_thm'].'
                                    AND D_T.ID_TYPE_THEME <>  8
                                    AND D_TRAD.NOM_TABLE=\'DICO_THEME\'
                                    AND D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\'
									ORDER BY D_T_S.ID_SYSTEME, D_T.ORDRE_THEME;';
        //print $requete;
        $GLOBALS['themes']  = $GLOBALS['conn_dico']->GetAll($requete);
    } //FIN get_themes()
	
	function set_tabm_values($id_thm_imp){
        $conn 		= $GLOBALS['conn_dico'];
		$req_max 	= '	SELECT     MAX(ID_TABLE_MERE_THEME) AS max_tabm
						FROM       DICO_TABLE_MERE_THEME';
		$id_tabm 	=	$GLOBALS['conn_dico']->getOne($req_max) ;
		$GLOBALS['equi_tabm'] = array();

		$req_tabm 	=	' SELECT    ID_TABLE_MERE_THEME	FROM       DICO_TABLE_MERE_THEME
						  WHERE      ID_THEME ='.$id_thm_imp;
		$tabms_thm  = $GLOBALS['conn_dico']->GetAll($req_tabm);
		
		if( is_array($tabms_thm) ){
			foreach($tabms_thm as $i => $tabm){
				$id_tabm++ ;
				$GLOBALS['equi_tabm'][$tabm['ID_TABLE_MERE_THEME']] = $id_tabm;
			}
		}
    } //FIN set_tabm_values()
	
	function set_zone_values($id_cur_thm, $id_thm_imp){
        $conn 		= $GLOBALS['conn_dico'];
		
		$id_zone 	=	$id_cur_thm * 1000 ;
		$GLOBALS['equi_zone'] = array();

		$req_zones	=	"	SELECT   ID_ZONE  FROM   DICO_ZONE WHERE    ID_THEME = ".$id_thm_imp;
		$zones_thm  = $GLOBALS['conn_dico']->GetAll($req_zones);
		
		if( is_array($zones_thm) ){
			foreach($zones_thm as $i => $zone){
				$id_zone++ ;
				$GLOBALS['equi_zone'][$zone['ID_ZONE']] = $id_zone;
			}
		}
    } //FIN set_zone_values()

	
	function get_equi_tabm($var_tabm){
		$cur_tabm = 'NULL' ;
		if( isset($GLOBALS['equi_tabm'][$var_tabm])){
			$cur_tabm = $GLOBALS['equi_tabm'][$var_tabm];
		}
		return($cur_tabm);
	}
	
	function get_equi_zone($var_zone){
		$cur_zone = 'NULL' ;
		if( isset($GLOBALS['equi_zone'][$var_zone])){
			$cur_zone = $GLOBALS['equi_zone'][$var_zone];
		}
		return($cur_zone);
	}

	function est_ds_tableau($elem,$tab){
		if(is_array($tab))
			foreach($tab as $elements){
				if( $elements == $elem ){
					return true;
				}
			}
	}

	function set_null_if_empty(&$row, $fields){
		//echo'<pre>';
		//print_r($row);
		foreach($row as $cur_field => $val_base){
			if(est_ds_tableau($cur_field, $fields)){
				//if(trim($val_base) == '' or empty($val_base)){
				if(!($val_base or $val_base=='0')){
					$row[$cur_field] = 'NULL' ;
					//echo"<br>$cur_field => ".$row[$cur_field];
				} 
			}
		}
	}
	function import_TABLE_MERE_THEME($id_cur_thm, $id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	=	' SELECT    *	FROM   DICO_TABLE_MERE_THEME WHERE  ID_THEME = '.$id_thm_imp;
		$all  		= 	$GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_TABLE_MERE_THEME', 'ID_THEME', 'PRIORITE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_TABLE_MERE_THEME
								(ID_TABLE_MERE_THEME, ID_THEME, NOM_TABLE_MERE, PRIORITE)
								VALUES ('.get_equi_tabm($elem['ID_TABLE_MERE_THEME']).','.$id_cur_thm.',\''.
								trim($elem['NOM_TABLE_MERE']).'\','.trim($elem['PRIORITE']).')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_ZONE($id_cur_thm, $id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req		= 'SELECT   *  FROM   DICO_ZONE WHERE    ID_THEME = ' . $id_thm_imp ;
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_ZONE', 'ID_THEME', 'ID_TABLE_MERE_THEME','ORDRE', 'ORDRE_TRI', 'ID_ZONE_REF', 'ID_ZONE_DEPEND');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_ZONE
								(ID_ZONE, ID_THEME, ID_TABLE_MERE_THEME, TABLE_MERE, PRIORITE, NOM_GROUPE, TABLE_FILLE, MESURE1,
								 TYPE_SAISIE_MESURE1, MESURE2, TYPE_SAISIE_MESURE2, ORDRE, SQL_REQ, CHAMP_FILS, CHAMP_PERE,
								 ORDRE_TRI, ID_ZONE_REF, ID_ZONE_DEPEND, TYPE_ZONE_BASE)
								VALUES 
								('.get_equi_zone($elem['ID_ZONE']).', '.$id_cur_thm.', '.get_equi_tabm($elem['ID_TABLE_MERE_THEME']).', 
								\''.$elem['TABLE_MERE'].'\', '.$elem['PRIORITE'].', \''.$elem['NOM_GROUPE'].'\', 
								\''.$elem['TABLE_FILLE'].'\', \''.$elem['MESURE1'].'\',
								\''.$elem['TYPE_SAISIE_MESURE1'].'\', \''.$elem['MESURE2'].'\', \''.$elem['TYPE_SAISIE_MESURE2'].'\',
								'.$elem['ORDRE'].', '.$conn->qstr($elem['SQL_REQ']).', \''.$elem['CHAMP_FILS'].'\', \''.$elem['CHAMP_PERE'].'\',
								'.$elem['ORDRE_TRI'].', '.get_equi_zone($elem['ID_ZONE_REF']).', '.get_equi_zone($elem['ID_ZONE_DEPEND']).', 
								\''.$elem['TYPE_ZONE_BASE'].'\')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_TRADUCTION($id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT DICO_TRADUCTION.*
						FROM DICO_ZONE, DICO_TRADUCTION 
						WHERE DICO_ZONE.ID_ZONE = DICO_TRADUCTION.CODE_NOMENCLATURE
						AND DICO_TRADUCTION.NOM_TABLE = \'DICO_ZONE\'
						AND DICO_ZONE.ID_THEME = ' . $id_thm_imp . ' ; ';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('CODE_NOMENCLATURE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_TRADUCTION
								(CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE, LIBELLE_MESURE1, LIBELLE_MESURE2)
								VALUES 
								('.get_equi_zone($elem['CODE_NOMENCLATURE']).', \''.$elem['NOM_TABLE'].'\', \''.$elem['CODE_LANGUE'].'\', 
								'.$conn->qstr($elem['LIBELLE']).','.$conn->qstr($elem['LIBELLE_MESURE1']).','.$conn->qstr($elem['LIBELLE_MESURE2']).')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_ZONE_SYSTEME($id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT DICO_ZONE_SYSTEME.*
						FROM DICO_ZONE_SYSTEME, DICO_ZONE 
						WHERE DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
						AND DICO_ZONE.ID_THEME = ' . $id_thm_imp . ' ;';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_ZONE', 'ID_SYSTEME', 'ACTIVER','SAUT_LIGNE', 'PRECEDENT_ZONE',
							 'AFFICHE_TOTAL','AFFICHE_TOTAL_VERTIC', 'AFFICHE_SOUS_TOTAUX', 'VALEUR_CONSTANTE', 'ORDRE_AFFICHAGE', 'AFFICH_VERTIC_MES', 'CTRL_SAISIE_OPERATEUR', 'CTRL_SAISIE_EXPRESSION', 'CTRL_SAISIE_MESSAGE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_ZONE_SYSTEME
								(ID_ZONE, ID_SYSTEME, ACTIVER, TYPE_OBJET, SAUT_LIGNE, PRECEDENT_ZONE,
								 ATTRIB_OBJET, AFFICHE_TOTAL, EXPRESSION, LIB_EXPR, AFFICHE_TOTAL_VERTIC, AFFICHE_SOUS_TOTAUX, BOUTON_INTERFACE, TABLE_INTERFACE, CHAMP_INTERFACE, REQUETE_CHAMP_INTERFACE,
								 REQUETE_CHAMP_SAISIE, FONCTION_INTERFACE, REQUETE_INTERFACE, VALEUR_CONSTANTE, ORDRE_AFFICHAGE, AFFICH_VERTIC_MES,
								 CTRL_SAISIE_OPERATEUR, CTRL_SAISIE_EXPRESSION, CTRL_SAISIE_MESSAGE)
								VALUES 
								('.get_equi_zone($elem['ID_ZONE']).', '.$_SESSION['secteur'].', '.$elem['ACTIVER'].
								',\''.$elem['TYPE_OBJET'].'\', '.$elem['SAUT_LIGNE'].', '.$elem['PRECEDENT_ZONE'].
								', '.$conn->qstr($elem['ATTRIB_OBJET']).', '.$elem['AFFICHE_TOTAL'].', \''.$elem['EXPRESSION'].'\', \''.$elem['LIB_EXPR'].'\', '.$elem['AFFICHE_TOTAL_VERTIC'].', '.$elem['AFFICHE_SOUS_TOTAUX'].', \''.$elem['BOUTON_INTERFACE'].'\',
								\''.$elem['TABLE_INTERFACE'].'\', \''.$elem['CHAMP_INTERFACE'].'\', '.$conn->qstr($elem['REQUETE_CHAMP_INTERFACE']).', 
								'.$conn->qstr($elem['REQUETE_CHAMP_SAISIE']).', '.$conn->qstr($elem['FONCTION_INTERFACE']).',
								'.$conn->qstr($elem['REQUETE_INTERFACE']).', '.$elem['VALEUR_CONSTANTE'].', '.$elem['ORDRE_AFFICHAGE'].', '.$elem['AFFICH_VERTIC_MES'].', '.$conn->qstr($elem['CTRL_SAISIE_OPERATEUR']).', '.$conn->qstr($elem['CTRL_SAISIE_EXPRESSION']).', '.$conn->qstr($elem['CTRL_SAISIE_MESSAGE']).')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_REGLE_ZONE_ASSOC($id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT DICO_REGLE_ZONE_ASSOC.*
						FROM DICO_REGLE_ZONE_ASSOC, DICO_ZONE 
						WHERE DICO_REGLE_ZONE_ASSOC.ID_ZONE = DICO_ZONE.ID_ZONE
						AND DICO_ZONE.ID_THEME = ' . $id_thm_imp . ' ;';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_ZONE', 'ID_REGLE_ZONE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_REGLE_ZONE_ASSOC
								(ID_ZONE, ID_REGLE_ZONE)
								VALUES 
								('.get_equi_zone($elem['ID_ZONE']).', '.$elem['ID_REGLE_ZONE'].')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_ZONE_JOINTURE($id_cur_thm, $id_thm_imp){
		$conn 		=  $GLOBALS['conn_dico'];
		$req	 	=	' SELECT    *	FROM   DICO_ZONE_JOINTURE WHERE  ID_THEME = '.$id_thm_imp;
		$all  		= 	$GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_THEME', 'ID_ZONE', 'N_JOINTURE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_ZONE_JOINTURE
								(ID_THEME, ID_ZONE, N_JOINTURE)
								VALUES ('.$id_cur_thm.', '.get_equi_zone($elem['ID_ZONE']).','.$elem['N_JOINTURE'].')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}

	function import_DIMENSION_ZONE($id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT  DICO_DIMENSION_ZONE.*
						FROM  DICO_DIMENSION_ZONE, DICO_ZONE 
						WHERE  DICO_DIMENSION_ZONE.ID_ZONE = DICO_ZONE.ID_ZONE
						AND DICO_ZONE.ID_THEME = ' . $id_thm_imp . ' ;';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_ZONE', 'ID_DIMENSION', 'TYPE_DIMENSION', 'NB_LIGNES_DIMENSION');
		if( is_array($all) && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO  DICO_DIMENSION_ZONE
								(ID_ZONE, ID_DIMENSION, TYPE_DIMENSION, NB_LIGNES_DIMENSION)
								VALUES 
								('.get_equi_zone($elem['ID_ZONE']).', '.$elem['ID_DIMENSION'].', 
								'.$elem['TYPE_DIMENSION'].', '.$elem['NB_LIGNES_DIMENSION'].')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	function import_INDEXES($id_thm_imp){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT  DICO_INDEXES.*
						FROM  DICO_INDEXES, DICO_ZONE 
						WHERE  DICO_INDEXES.ID_ZONE = DICO_ZONE.ID_ZONE
						AND DICO_ZONE.ID_THEME = ' . $id_thm_imp . ' ;';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_INDEXE', 'ID_ZONE');
		if( is_array($all) && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO  DICO_INDEXES
								(ID_INDEXE, ID_ZONE, CHAMP_INDEXE, VALEUR_DEFAUT_INDEXE)
								VALUES 
								('.$elem['ID_INDEXE'].', '.get_equi_zone($elem['ID_ZONE']).', \''.$elem['CHAMP_INDEXE'].'\', \''.$elem['VALEUR_DEFAUT_INDEXE'].'\')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}

	function do_import_thm( $id_cur_thm, $id_thm_imp ){
		set_tabm_values($id_thm_imp);
		set_zone_values($id_cur_thm, $id_thm_imp);
		import_TABLE_MERE_THEME($id_cur_thm, $id_thm_imp);
		import_ZONE($id_cur_thm, $id_thm_imp);
		import_TRADUCTION($id_thm_imp);
		import_ZONE_SYSTEME($id_thm_imp);
		import_REGLE_ZONE_ASSOC($id_thm_imp);
		import_ZONE_JOINTURE($id_cur_thm, $id_thm_imp);
		import_DIMENSION_ZONE($id_thm_imp);
		import_INDEXES($id_thm_imp);
	}
	
	if( isset($_POST['btn_import_thm'])){
		if( isset($_POST['thm_imp']) and (trim($_POST['thm_imp']) <> '') ){
			//die($_GET['cur_thm'].'///'.$_POST['thm_imp']);
			do_import_thm($_GET['cur_thm'],$_POST['thm_imp']);
		}
		// réactualiser la page ouvrante
		?>
		<script type="text/javascript" language="javascript">
			parent.document.location.href='administration.php?val=gestzone&id_theme_choisi=<?php echo $_GET['cur_thm']; ?>';
			fermer();
		</script>
		<?php }
?>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<script type="text/javascript" language="javascript">
			function toggle_theme() {
						var id_theme     = form_theme.themes_dispo.options[form_theme.themes_dispo.selectedIndex].value;
						var radio     = form_theme.choix_affich;
						var val_affich;
						for (var i=0; i<radio.length;i++) {
							 if (radio[i].checked) {
								val_affich=radio[i].value;
								break;
							 }
						}
						(val_affich==1) ? (val_affich='all_themes') : (val_affich='active_themes');
						location.href   = '?val=gestheme&theme_en_cours='+id_theme+'&val_affich='+val_affich;
						//location.href   = '?val=gestheme&theme_en_cours='+id_theme;
		}
		</script>
<body>
<br><br><br>
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
<table width="90%" align="center">
            <td><select name="thm_imp" onChange="toggle_theme()" style="width:100%">
                    <option value=''> ... </option>
                    <?php get_themes();
            foreach ($GLOBALS['themes'] as $id => $theme){
                echo "<option value='".$theme['ID_THM']."'>".$theme['LIBELLE_THM']."</option>";
            }
        ?>
                </select></td>
        </tr>
<tr>
            <td> <INPUT style="width:100%;" type="submit" name="btn_import_thm" <?php echo ' value="'.recherche_libelle_page('import').' ... "'; ?>></td>
        </tr>
</table>
</FORM>
</body>


