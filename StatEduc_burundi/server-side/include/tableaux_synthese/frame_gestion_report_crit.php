<script language="JavaScript" type="text/javascript">
	function recharger(id_crit) {
		location.href   = 'synthese.php?val=OpenPopupRptCrit&id_rpt=<?php echo $_GET['id_rpt']?>&id_crit='+id_crit;
	}
	
	var xhr = null; 

	function getXhr(){
		if(window.XMLHttpRequest) // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		   xhr = false; 
		} 
	}
	
	/**
	* Méthode qui sera appelée sur le click du bouton
	*/
	function load_champs(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('CHAMP_TABLE_DONNEES').innerHTML = leselect;
				jQuery("#CHAMP_TABLE_DONNEES select").attr('style', 'width:230px;');
				jQuery("#CHAMP_TABLE_DONNEES select").uniform();
			}
		}
		sel = document.getElementById('NOM_TABLE_DONNEES');
		table = sel.options[sel.selectedIndex].value;
		url="synthese.php?val=charger_chps&table="+table+"&chp_fils=<?php echo $val['CHAMP_TABLE_DONNEES']; ?>";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);		
		//xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');// necessaire pour la methode post
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
	}
	
</script>

<?php lit_libelles_page('/liste_user_report.php');
	$requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'];
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);

	$requete                = '	SELECT * FROM DICO_CRITERE_FILTRE ORDER BY LIBELLE_CRITERE;';
	//print $requete;
	$all_criteres 			= $GLOBALS['conn_dico']->GetAll($requete);
	
	if( $this->action == 'Open' ){
		$selected_critere	=	$val['ID_CRITERE'];
	}elseif(isset($_GET['id_crit']) and (trim($_GET['id_crit']) <> '')){
		$selected_critere	= $_GET['id_crit'] ;
	}else{
		$selected_critere	=	$val['ID_CRITERE'];
	}

	$requete                = '	SELECT DICO_CRITERE_FILTRE.*
								FROM DICO_CRITERE_FILTRE
								WHERE DICO_CRITERE_FILTRE.ID_CRITERE =' . $selected_critere . ' ;';
	//print $requete;
	$tab_crit_choisi		= $GLOBALS['conn_dico']->GetAll($requete);
	$nom_table 				= $tab_crit_choisi[0]['TABLE_REF'];
	//die($table_ref );
	if( isset($nom_table ) and (trim($nom_table ) <> '')){
		if ( isset($_SESSION['secteur']) and (in_array(strtoupper($nom_table.'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$this->conn->MetaTables('TABLES')))) ){
				$lie_systeme  =   true; 
			}
		if ( $lie_systeme == true ) {
			//$lie_systeme  =   true; 
			$requete    ='SELECT '.$nom_table.'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table.' AS CODE, '.$nom_table.'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table.' AS LIBELLE'
					.' FROM '.$nom_table.','.$nom_table.'_'.$GLOBALS['PARAM']['SYSTEME']
					.' WHERE '.$nom_table.'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table.'='
					. $nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table
					.' AND '.$nom_table.'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table.'<> 255'
					.' AND '.$nom_table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$_SESSION['secteur']
					.' ORDER BY '.$nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table;
		} else {
			//$lie_systeme  =   false;
			$requete    ='SELECT '.$nom_table.'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table.' AS CODE, '.$nom_table.'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table.' AS LIBELLE'
						.' FROM '.$nom_table.
						' ORDER BY '.$nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table;
		}
		/*$requete    ='SELECT '.$nom_table.'.'.$GLOBALS['PARAM']['CODE'].'_'.$nom_table.' AS CODE, '.$nom_table.'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$nom_table.' AS LIBELLE'
					.' FROM '.$nom_table.
					' ORDER BY '.$nom_table.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$nom_table; */
		//die('here');
		//die($requete);
		$all_val_filtres = $GLOBALS['conn']->GetAll($requete);           
        // Ajout du All Ajout yacine
         $All_filtre            =array();
         $All_filtre['CODE']    = 0;
         $All_filtre['LIBELLE'] =  recherche_libelle_page('all');                
         $all_val_filtres[]      = $All_filtre;                 
         // Fin ajout du All Fin Ajout yacine
	}
	$all_op_req = array(  
							array ('CODE' => 'AND', 'LIBELLE' => 'AND' ),
							array ('CODE' => 'OR', 	'LIBELLE' => 'OR'  )
						 );
	lit_libelles_page('/liste_user_report.php');				 
	$all_op_crit = array(  
							array ('CODE' => '1', 'VAL' => '=',  'LIBELLE' => recherche_libelle_page('egale') ),
							array ('CODE' => '2', 'VAL' => '<>', 'LIBELLE' => recherche_libelle_page('diff') ),
							array ('CODE' => '3', 'VAL' => '<',	 'LIBELLE' => recherche_libelle_page('inf') ),
							array ('CODE' => '4', 'VAL' => '>',  'LIBELLE' => recherche_libelle_page('sup') ),
							array ('CODE' => '5', 'VAL' => '<=', 'LIBELLE' => recherche_libelle_page('inf_eg') ),
							array ('CODE' => '6', 'VAL' => '>=', 'LIBELLE' => recherche_libelle_page('sup_eg') )
						 );
						 
	lit_libelles_page('/'.$this->frame);
	
	$selected_critere_lib = "";
?>
<table align="center" border="1" width="400">
    <tr> 
        <td style="min-width:60px"><?php echo recherche_libelle_page('id_rpt'); ?><br/><INPUT readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
        <td><?php echo recherche_libelle_page('id_crit'); ?><br/>
			<INPUT type="hidden" name="ID_CRITERE" value="<?php echo $selected_critere; ?>">
			<INPUT readonly="1" type="text" size="20" value="<?php 
                 foreach ($all_criteres as $i => $crit) {
					if ($crit['ID_CRITERE'] == $selected_critere){
						$selected_critere_lib = $crit['LIBELLE_CRITERE'];
						echo $selected_critere_lib;
					}
				}
				?>">
        </td>
        <td><?php echo recherche_libelle_page('libelle'); ?><br/><INPUT type="text" size="20" name="LIBELLE" value="<?php echo ($val['LIBELLE']=='')?$selected_critere_lib:$val['LIBELLE']; ?>"></td>
        <td><?php echo recherche_libelle_page('op_crit'); ?><br/>
			<select name="OP_CRITERE_FILTRE_REPORT" style="width:120px;">
                <!--<option value=''></option>-->
                <?php foreach ($all_op_crit as $i => $op_crit){
								echo "<option value='".trim($op_crit['CODE'])."'";
								if (trim($op_crit['CODE']) == trim($val['OP_CRITERE_FILTRE_REPORT'])){
										echo " selected";
								}
								echo ">".$op_crit['LIBELLE']."</option>";
						}
				
				?>
            </select>
		</td>
        <td style="min-width: 100px;"><?php echo recherche_libelle_page('val_crit'); ?><br/> 
			<select name="VAL_CRITERE_FILTRE_REPORT" style="width:110px;">
                <!--<option value=''></option>-->
                <?php if(is_array($all_val_filtres)){
						foreach ($all_val_filtres as $i => $val_filtres){
							echo "<option value='".trim($val_filtres['CODE'])."'";
							if (trim($val_filtres['CODE']) == trim($val['VAL_CRITERE_FILTRE_REPORT'])){
								echo " selected";
							}
							echo ">".$val_filtres['LIBELLE']."</option>";
						}
					}
				?>
            </select></td>
        <td><?php echo recherche_libelle_page('nom_tab'); ?><br/>
		<select name="NOM_TABLE_DONNEES" id="NOM_TABLE_DONNEES" onchange="load_champs()" style="width:230px;">
		<?php $TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
				foreach($TabBD as $tab)
				{
					$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
					$deb_fact_tab = 'FACT_TABLE_';
					if(!preg_match("/^($deb_fact_tab)/",strtoupper($tab)) && !preg_match("/^($deb_nomenc)/",strtoupper($tab)) && !preg_match("/^(DICO_)/",strtoupper($tab)) && !preg_match("/^(ADMIN_DROITS)/",strtoupper($tab)) && !preg_match("/^(ADMIN_GROUPES)/",strtoupper($tab)) && !preg_match("/^(ADMIN_USERS)/",strtoupper($tab)) && !preg_match("/^(PARAM_DEFAUT)/",strtoupper($tab)) && !preg_match("/^(SYSTEME)/",strtoupper($tab)))
					{
						$tables[] = $tab;
					}
				}
			if($GLOBALS['conn']->databaseType<>'access'){
				$ReqBD	=	$GLOBALS['conn']->MetaTables("VIEWS");
				foreach($ReqBD as $tab)
				{
					$tables[] = $tab;
				}
			}
			$TabBD = $tables;
			sort($TabBD);
			echo "<option value=''></option>";
			foreach ($TabBD as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($val['NOM_TABLE_DONNEES']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}
		?>																				
		</select >
		</td> 
        <td style="min-width: 115px;"><?php echo recherche_libelle_page('chp_tab'); ?><br/>
		<div id='CHAMP_TABLE_DONNEES' style='display:inline'>														
		<select name="CHAMP_TABLE_DONNEES" style="width:230px;">
			<option value=''></option>
		</select>
		</div>		
		</td>
        <td style="min-width: 85px;"><?php echo recherche_libelle_page('op_req'); ?><br/>
		<select name="OPERATEUR_REQUETE" style="width:50px;">
                <option value=''></option>
                <?php foreach ($all_op_req as $i => $op_req){
								echo "<option value='".trim($op_req['CODE'])."'";
								if (trim($op_req['CODE']) == trim($val['OPERATEUR_REQUETE'])){
										echo " selected";
								}
								echo ">".$op_req['LIBELLE']."</option>";
						}
				?>
            </select>
		</td>
        <td><div style="width:60px"><?php echo recherche_libelle_page('activer'); ?></div><INPUT name="ACTIVER_CRITERE" type="checkbox" value="1" <?php if($val['ACTIVER_CRITERE']=='1') echo' checked';?>></td>
    </tr>
</table>
<script type='text/javascript'>
	load_champs();
</script>
