<?php lit_libelles_page('/import_zones_syst.php');
	
	function get_systemes_configured($id_thm){
    
		$conn                     = $GLOBALS['conn_dico'];
        $requete = 'SELECT DISTINCT DICO_ZONE_SYSTEME.ID_SYSTEME 
                FROM DICO_ZONE_SYSTEME, DICO_ZONE
                WHERE DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
                AND DICO_ZONE.ID_THEME ='.$id_thm;
        $syst_configured = $GLOBALS['conn_dico']->GetAll($requete);
        $str_critere = '';
        if (is_array( $syst_configured)){
            foreach($syst_configured as $un_syst){
            if ($str_critere =='')
                $str_critere .=' AND CODE_NOMENCLATURE IN ('.$un_syst['ID_SYSTEME'];
            else
                 $str_critere .=','.$un_syst['ID_SYSTEME'];
            }
            $str_critere .= ')';
        }
		$requete = 'SELECT DISTINCT DICO_TRADUCTION.CODE_NOMENCLATURE as id_systeme, DICO_TRADUCTION.LIBELLE as libelle_systeme
					FROM  DICO_TRADUCTION
					WHERE DICO_TRADUCTION.CODE_LANGUE = \''.$_SESSION['langue'].'\''.$str_critere.
					'AND DICO_TRADUCTION.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' ;';
		$GLOBALS['syst_conf']  = $GLOBALS['conn']->GetAll($requete);
        //die ($requete);
    } //FIN get_themes()
	

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
	
	function do_import_zs($id_thm, $from_syst, $to_syst){
		$conn 		= $GLOBALS['conn_dico'];
		$req	 	= '	SELECT DICO_ZONE_SYSTEME.*
						FROM DICO_ZONE_SYSTEME, DICO_ZONE 
						WHERE DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
						AND DICO_ZONE.ID_THEME = ' . $id_thm . '
						AND DICO_ZONE_SYSTEME.ID_SYSTEME = ' . $from_syst . ' ;';
		$all  		= $GLOBALS['conn_dico']->GetAll($req);
		$champs_int = array('ID_ZONE', 'ID_SYSTEME', 'ACTIVER','SAUT_LIGNE', 'PRECEDENT_ZONE',
							 'AFFICHE_TOTAL', 'AFFICHE_TOTAL_VERTIC', 'AFFICHE_SOUS_TOTAUX', 'VALEUR_CONSTANTE', 'ORDRE_AFFICHAGE', 'AFFICH_VERTIC_MES', 'CTRL_SAISIE_OPERATEUR', 'CTRL_SAISIE_EXPRESSION', 'CTRL_SAISIE_MESSAGE');
		if( is_array($all)  && count($all) ){
			foreach($all as $i => $elem){
				set_null_if_empty($elem, $champs_int);
				$req_ins 	= ' INSERT INTO DICO_ZONE_SYSTEME
								(ID_ZONE, ID_SYSTEME, ACTIVER, TYPE_OBJET, SAUT_LIGNE, PRECEDENT_ZONE,
								 ATTRIB_OBJET, AFFICHE_TOTAL, EXPRESSION, LIB_EXPR, AFFICHE_TOTAL_VERTIC, AFFICHE_SOUS_TOTAUX, BOUTON_INTERFACE, TABLE_INTERFACE, CHAMP_INTERFACE, REQUETE_CHAMP_INTERFACE,
								 REQUETE_CHAMP_SAISIE, FONCTION_INTERFACE, REQUETE_INTERFACE, VALEUR_CONSTANTE, ORDRE_AFFICHAGE, AFFICH_VERTIC_MES,
								 CTRL_SAISIE_OPERATEUR, CTRL_SAISIE_EXPRESSION, CTRL_SAISIE_MESSAGE)
								VALUES 
								('.$elem['ID_ZONE'].', '.$to_syst.', '.$elem['ACTIVER'].
								',\''.$elem['TYPE_OBJET'].'\', '.$elem['SAUT_LIGNE'].', '.$elem['PRECEDENT_ZONE'].
								', '.$conn->qstr($elem['ATTRIB_OBJET']).', '.$elem['AFFICHE_TOTAL'].', \''.$elem['EXPRESSION'].'\', \''.$elem['LIB_EXPR'].'\', '.$elem['AFFICHE_TOTAL_VERTIC'].', '.$elem['AFFICHE_SOUS_TOTAUX'].', \''.$elem['BOUTON_INTERFACE'].'\',
								\''.$elem['TABLE_INTERFACE'].'\', \''.$elem['CHAMP_INTERFACE'].'\', '.$conn->qstr($elem['REQUETE_CHAMP_INTERFACE']).', 
								'.$conn->qstr($elem['REQUETE_CHAMP_SAISIE']).', '.$conn->qstr($elem['FONCTION_INTERFACE']).',
								'.$conn->qstr($elem['REQUETE_INTERFACE']).', '.$elem['VALEUR_CONSTANTE'].', '.$elem['ORDRE_AFFICHAGE'].', '.$elem['AFFICH_VERTIC_MES'].', '.$conn->qstr($elem['CTRL_SAISIE_OPERATEUR']).', '.$conn->qstr($elem['CTRL_SAISIE_EXPRESSION']).', '.$conn->qstr($elem['CTRL_SAISIE_MESSAGE']).')';      
				if ($GLOBALS['conn_dico']->Execute($req_ins) === false) echo '<br> Error : ' . $req_ins . '<br>';
			}
		}
	}
	
	
	if( isset($_POST['btn_import_zs'])){
		if( isset($_POST['from_syst']) and (trim($_POST['from_syst']) <> '') ){
			do_import_zs($_GET['cur_thm'], $_POST['from_syst'], $_GET['to_syst']);
		}
		// réactualiser la page ouvrante
		?>
		<script type="text/Javascript">
			parent.document.location.reload();
			fermer();
		</script>
		<?php }
?>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<body>
<br><br><br>
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
<table width="90%" align="center">
	<tr>
		<td>
			<select name="from_syst"  style="width:100%">
				<option value=''> ... </option>
				<?php get_systemes_configured($_GET['cur_thm']);
					foreach ($GLOBALS['syst_conf'] as $i => $systemes){
						echo "<option value='".$systemes['id_systeme']."'";
						echo ">".$systemes['libelle_systeme']."</option>";
					}
				?>
			</select>
		</td>
	</tr>
	<tr>
		<td> <INPUT style="width:100%;" type="submit" name="btn_import_zs" <?php echo ' value="'.recherche_libelle_page('import_zs').' ... "'; ?>></td>
	</tr>
</table>
</FORM>
</body>


