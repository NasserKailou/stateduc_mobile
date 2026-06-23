<?php lit_libelles_page('/export.php');
	if($GLOBALS['PARAM']['FILTRE']==true){
		//$_SESSION['FILTRE']['FILTRE_1'] = $GLOBALS['PARAM']['FILTRE'];
		if(isset($_GET['suppr_filter']) && $_GET['suppr_filter']==1){
			$_SESSION['FILTRE']['FILTRE_1'] = false;
		}
		if(isset($_GET['activ_filter']) && $_GET['activ_filter']==1){
			$_SESSION['FILTRE']['FILTRE_1'] = true;
		}
	}
	
	if(isset($_GET['suppr_filter']) && $_GET['suppr_filter']==2){
		$_SESSION['FILTRE']['FILTRE_2'] = false;
	}/*else{//Cas Tanzanie. Supprimer le else dans le cas general
			$_SESSION['FILTRE']['FILTRE_2']	=	true;
			$_SESSION['FILTRE']['TYPE_FILTRE_2']	=	'TYPE_OWNERSHIP';
			$_SESSION['FILTRE']['CODE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['CODE'].'_TYPE_OWNERSHIP';
			$_SESSION['FILTRE']['CODE_TYPE_FILTRE_2_ALIAS']	=	$GLOBALS['PARAM']['CODE'].'_TYPE_OWNERSHIP';
			$_SESSION['FILTRE']['LIBELLE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['LIBELLE'].'_TYPE_OWNERSHIP';
			$_SESSION['FILTRE']['ORDRE_TYPE_FILTRE_2']	=	$GLOBALS['PARAM']['ORDRE'].'_TYPE_OWNERSHIP';
	}*/
	$GLOBALS['PARAM']['FILTRE'] = $_SESSION['FILTRE']['FILTRE_1'];
	//$GLOBALS['type_reg'] = 2 ; //Cas Tanzanie
	//echo 'FILTRE_2<pre>';
	//print_r($_SESSION['FILTRE']);
	//echo "<pre>";
	//print_r($_POST);
	if( count($_POST) > 0 ){
			$is_set_reg = false ;
			$is_set_ann = false ;
			$is_set_filtre = false ;
			for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
					if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
							$is_set_reg = true ;
							break;
					}
			}
			
			for( $i = 0 ; $i < $_POST['nb_annees_dispo'] ; $i++ ){
					if(isset($_POST['ANN_'.$i]) and trim($_POST['ANN_'.$i]) <> ''){
							$is_set_ann = true ;
							break;
					}
			}
			if($GLOBALS['PARAM']['FILTRE']){
				for( $i = 0 ; $i < $_POST['nb_filtres_dispo'] ; $i++ ){
						if(isset($_POST['PERIOD_'.$i]) and trim($_POST['PERIOD_'.$i]) <> ''){
								$is_set_filtre = true ;
								break;
						}
				}
			}
			if($_SESSION['FILTRE']['FILTRE_2']){
				for( $i = 0 ; $i < $_POST['nb_filtres2_dispo'] ; $i++ ){
						if(isset($_POST['FILTRE2_'.$i]) and trim($_POST['FILTRE2_'.$i]) <> ''){
								$is_set_filtre2 = true ;
								break;
						}
				}
			}
			if(!$is_set_reg){
					$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
			}elseif(!$is_set_ann){
					$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_annees').'"';
			}elseif($GLOBALS['PARAM']['FILTRE'] && !$is_set_filtre){
					$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_filtres').'"';
			}elseif($_SESSION['FILTRE']['FILTRE_2'] && !$is_set_filtre2){
					$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_filtres2').'"';
			}elseif(isset($_POST['chemin']) && $_POST['chemin']==''){
					$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_file_location').'"';
			}elseif( ($is_set_reg == true) and ($is_set_ann == true) ){
					$do_action_post = true;
			}
			
			$_SESSION['template_excel'] = $_POST['fichier_excel'];
	}
	$template_excel = "";
	if (isset($_SESSION['template_excel']) && $_SESSION['template_excel']<>''){
		$template_excel = $_SESSION['template_excel'];
	}	
	if(isset($_GET['code_regroup_0'])){
		unset($_SESSION['reg_parents']);
	}
	require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
	//require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre_export_excel.class.php'; // Cas tanzanie. A remplacer par celui ci dessus dans le cas general
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg) {
				location.href= '?val=aggregated_export&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg;
		}
		
		function Nom_Fichier_Export(){
			sel_sys = document.Formulaire.id_systeme;
			sys = sel_sys.options[sel_sys.selectedIndex].text;
			sel_type_reg = document.Formulaire.type_reg;
			reg = sel_type_reg.options[sel_type_reg.selectedIndex].text;
			Formulaire.export_file_name.value = sys;
			Formulaire.export_file_name.value += '_'+reg;
			nom_fichier_export = "";
			for(var i = 0; i < Formulaire.nb_regs_dispo.value; i++){
				if(eval('Formulaire.REGS_'+i+'.checked')==true){
					Formulaire.export_file_name.value += '_'+eval('Formulaire.REGS_'+i+'.alt');
				}
			}
			for(var i = 0; i < Formulaire.nb_annees_dispo.value; i++){
				if(eval('Formulaire.ANN_'+i+'.checked')==true){
					Formulaire.export_file_name.value += '_'+eval('Formulaire.ANN_'+i+'.alt');
				}
			}
			if(document.Formulaire.PERIOD_0){
				for(var i = 0; i < Formulaire.nb_filtres_dispo.value; i++){
					if(eval('Formulaire.PERIOD_'+i+'.checked')==true){
						Formulaire.export_file_name.value += '_'+eval('Formulaire.PERIOD_'+i+'.alt');
					}
				}
			}
			//Formulaire.export_file_name.value += '_';
			text_rech=' ';
			maReg = new RegExp(text_rech) ;
			text = Formulaire.export_file_name.value;
			Formulaire.export_file_name.value = text.replace(maReg, '_');
			nom_fichier_export = Formulaire.export_file_name.value;
			if(nom_fichier_export.length > 65){
				Formulaire.export_file_name.value = nom_fichier_export.substring(0,65);
			}
		}
		
		function manage_check(var1, var2){
				var chaine_eval1 ='document.getElementById("' + var1 + '").checked == 1;';
				if (eval(chaine_eval1)){
						$("#" + var1).uniform('update');
						var i = 0 ;
						while ( document.getElementById( var2 + '_' + i ) ){
								var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 1;';
								//alert(chaine_eval2);
								eval(chaine_eval2);
								$("#" + var2 + "_" + i).uniform('update');
								i++;
						}
				}else{
						var i = 0 ;
						while ( document.getElementById( var2 + '_' + i ) ){
								var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
								eval(chaine_eval2);
								$("#" + var2 + "_" + i).uniform('update');
								i++;
						}
				}
		}
		function manage_check_2(var1, var2)
		{	
			var chaine_eval1 ='document.getElementById("' + var1 + '").checked = 1;';
			eval(chaine_eval1);
			$("#" + var1).uniform('update');	
			var i = 0 ;
			while ( document.getElementById( var2 + '_' + i ) ){
				if((var2 + '_' + i) != var1){
					var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
					eval(chaine_eval2);
					$("#" + var2 + "_" + i).uniform('update');	
				}
				i++;
			}
		}

</script>

<?php function get_systemes(){/// get_systemes()
				$conn 		= $GLOBALS['conn'];
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
      	if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
			$requete = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
						D_TRAD.LIBELLE as libelle_systeme
						FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
						WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
						AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}else{
			$requete = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme,
						D_TRAD.LIBELLE as libelle_systeme
						FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
						WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
						AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')
						AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}	
		//echo $requete;
		try {            
				$GLOBALS['systemes'] = $conn->GetAll($requete);
				foreach ($GLOBALS['systemes'] as $i => $systemes){
						$GLOBALS['systemes'][$i] = array_change_key_case_unicode($systemes);
				}
				if(!is_array($GLOBALS['systemes'])){                
						 throw new Exception('ERR_SQL');   
				}
				if( isset($_GET['id_systeme']) and (trim($_GET['id_systeme'])<>'') ){
						$GLOBALS['id_systeme'] 	= $_GET['id_systeme'];
				}elseif(isset($_POST['id_systeme'])){
						$GLOBALS['id_systeme'] 	= $_POST['id_systeme'];
				}elseif(isset($_SESSION['secteur'])){
						$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
				}
				if(!isset($GLOBALS['id_systeme'])){
						$GLOBALS['id_systeme'] 	= $GLOBALS['systemes'][0]['id_systeme'];
				}
		}
		catch (Exception $e) {
				 $erreur = new erreur_manager($e,$requete);
		} 
			// echo $requete .'<br>';
    } // FIN  get_systemes()

		function get_chaines_systeme(){
				
				$conn 		= $GLOBALS['conn'];
        $requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
                        '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
                        FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
                        WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'] ;
				try {            
						$GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
						if(!is_array($GLOBALS['chaines_systeme'])){                
								 throw new Exception('ERR_SQL');   
						}
						if(isset($_GET['id_chaine']) and (trim($_GET['id_chaine'])<>'') ){
								$GLOBALS['id_chaine'] 	= $_GET['id_chaine'];
						}elseif(isset($_POST['id_chaine'])){
								$GLOBALS['id_chaine'] 	= $_POST['id_chaine'];
						}
						if(!isset($GLOBALS['id_chaine'])){
								$GLOBALS['id_chaine'] 	= $GLOBALS['chaines_systeme'][0]['CODE_TYPE_CH'];
						}
				}
				catch (Exception $e) {
						 $erreur = new erreur_manager($e,$requete);
				} 
		}
		 
		function get_type_regs_chaine(){
				
				$arbre = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arbre->chaine ;

				if(isset($_GET['type_reg']) and (trim($_GET['type_reg'])<>'') ){
						$GLOBALS['type_reg'] 	= $_GET['type_reg'];
				}elseif(isset($_POST['type_reg'])){
						$GLOBALS['type_reg'] 	= $_POST['type_reg'];
				}elseif(isset($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']]) && count($_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']])>0){
						$GLOBALS['type_reg'] 	= $_SESSION['fixe_type_reg'][$GLOBALS['id_chaine']][0];
				}
				if(!isset($GLOBALS['type_reg'])){
						$GLOBALS['type_reg'] 	= $GLOBALS['type_regs_chaine'][0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				}
		} 
		
		function tableau_check( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1 ){
				$html 	= '';
				if(is_array($tab)){
						$html .= "\n" . '<TABLE border="1"  width="100%">';
						$i_tr = 0 ;
						if($chp_check<>'ANN'){
							while(isset($tab[$i_tr])){
									$html .= "\n\t" . '<tr>';
									$i_td = 1;
									for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){
	
										 if(isset($tab[$i])){
													$html .= "\n\t\t" . '<td nowrap align="right">'.$tab[$i][$lib].' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
													$html .=' alt="'.$tab[$i][$lib].'" onclick="manage_check_2(\''.$chp_check.'_'.$i.'\', \''.$chp_check.'\'); Nom_Fichier_Export()"';
													if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
														$html .= ' CHECKED';
													}
													/*elseif(isset($_SESSION['cfg_exp'][$chp_check.'_'.$i]) and trim($_SESSION['cfg_exp'][$chp_check.'_'.$i]) <> ''){
														$html .= ' CHECKED';
													}elseif(isset($_COOKIE[$chp_check.'_'.$i]) and trim($_COOKIE[$chp_check.'_'.$i]) <> ''){
														$html .= ' CHECKED';
													}*/
													/*elseif(isset($_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && in_array(trim($tab[$i][$code]),$_SESSION['fixe_reg'][$GLOBALS['type_reg']])){
														$html .= ' CHECKED';
													}*/
													elseif(in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs']) && $chp_check=='REGS' && !in_array(trim($tab[$i][$code]),(array)($_SESSION['fixe_reg'][$GLOBALS['type_reg']]))){
														$html .= ' DISABLED';
													}else{
														$html .= '' ;
													}
													$html .= '></td>';
													$i_td++;
											}else{
													$html .= "\n\t\t" . '<td colspan="'. ( $nb_td - $i_td +1) .'"></td>';										
													$i_tr = $i ;
													break;
											}
									}
									$html .= "\n\t" . '</tr>';
									$i_tr += $nb_td ;
							}
						}else{
							$exist_cookie_ann = false;
							for( $i = 0 ; $i < count($tab) ; $i++ ){
								if(isset($_COOKIE[$chp_check.'_'.$i]) and trim($_COOKIE[$chp_check.'_'.$i]) <> '') $exist_cookie_ann = true;
							}
							while(isset($tab[$i_tr])){
								$html .= "\n\t" . '<tr>';
								$i_td = 1;
								for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){
									 if(isset($tab[$i])){
											$html .= "\n\t\t" . '<td nowrap align="right">'.$tab[$i][$lib].' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
											$html .=' alt="'.$tab[$i][$lib].'" onclick="manage_check_2(\''.$chp_check.'_'.$i.'\', \''.$chp_check.'\'); Nom_Fichier_Export()"';
											if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
												$html .= ' CHECKED';
											}elseif(isset($_COOKIE[$chp_check.'_'.$i]) and trim($_COOKIE[$chp_check.'_'.$i]) <> ''){
												$html .= ' CHECKED';
											}elseif(isset($_SESSION['annee']) and trim($_SESSION['annee']) == $tab[$i][$code] and count($_POST)==0 and !$exist_cookie_ann){
												$html .= ' CHECKED';
											}else{
												$html .= '' ;
											}
											$html .= '></td>';
											$i_td++;
									}else{
											$html .= "\n\t\t" . '<td colspan="'. ( $nb_td - $i_td +1) .'"></td>';										
											$i_tr = $i ;
											break;
									}
								}
								$html .= "\n\t" . '</tr>';
								$i_tr += $nb_td ;
							}
						}
						//Mis en commentaire par HEBIE 
						/*if( count($tab) > 1 ){
								if($_POST[$chp_all_check]==1) $checked = ' CHECKED';
								else $checked = '';
								$html .= "\n" . '<tr><td nowrap colspan="' . $nb_td . '" align="center">' . recherche_libelle_page($lib_all_check) . ' <INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'></td></tr>';
						}*/
						//Fin Mis en commentaire par HEBIE
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
		function tableau_check2( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1 ){
				$html 	= '';
				if(is_array($tab)){
						$html .= "\n" . '<TABLE border="1"  width="100%">';
						$i_tr = 0 ;
						while(isset($tab[$i_tr])){
								$html .= "\n\t" . '<tr>';
								$i_td = 1;
								for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){

									 if(isset($tab[$i])){
												$html .= "\n\t\t" . '<td nowrap align="right">'.$tab[$i][$lib].' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
												(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> '') ? ($html .= ' CHECKED') : ($html .= '') ; 
												$html .= ' alt="'.$tab[$i][$lib].'" onclick="Nom_Fichier_Export()"></td>';
												$i_td++;
										}else{
												$html .= "\n\t\t" . '<td colspan="'. ( $nb_td - $i_td +1) .'"></td>';										
												$i_tr = $i ;
												break;
										}
								}
								$html .= "\n\t" . '</tr>';
								$i_tr += $nb_td ;
						}
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
		get_systemes();
		get_chaines_systeme();
		get_type_regs_chaine();

		$GLOBALS['nb_td'] = 4;
?>
<style type="text/css" media="all">
		.cachediv {
				 visibility: hidden;
				 overflow: hidden;
				 height: 1px;
				 margin-top: -100%px;
				 position: absolute;
		}
		.montrediv {
				 visibility: visible;
				 overflow: visible;
		}
		.espace_2 {
			margin: 4px ;
		}
		
		.details {
	text-align: right;
	overflow: auto;
		}
</style>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<FORM name="Formulaire"  method="post" action="">
    <div align="center">
	      <table class='espace_2' border='1' style="overflow: auto;" width="50%">
            <CAPTION>
            <B><?php echo recherche_libelle_page('EntAggrExport');?></B>
            </CAPTION>
            <tr class="espace_2"> 
                <td><br>
					<table border='1' class="espace_2" width="98%">
						<tr>
							<td align="center"><INPUT type='button' name='' value='<?php echo recherche_libelle_page('manip_nlle_struct'); ?>' onclick="javascript:OpenAggregateDBStructure();" style="width:98%"/></td>
						</tr>
					</table>
					<table border='1' class="espace_2" width="98%">
						<tr>
							<td align="center"><INPUT type='button' name='' value='<?php echo recherche_libelle_page('excel_file_descrip'); ?>' onclick="javascript:OpenPopupExcelAggregTables();" style="width:98%"/></td>
						</tr>
					</table>
                    <br> 
                    <table border='1' class="espace_2" width="98%">
                        <tr> 
                            <td> <TABLE border='1' align="center"  width="100%">
                                    <CAPTION>
                                    <B><?php echo recherche_libelle_page('Secteur');?></B> 
                                    </CAPTION>
                                    <tr> 
                                        <td><select name="id_systeme"	onChange="reload_page(this.value,'',''); ">
                                                <?php if(is_array($GLOBALS['systemes']))
												foreach ($GLOBALS['systemes'] as $i => $systemes){
														echo "<option value='".$systemes['id_systeme']."'";
														if ($systemes['id_systeme'] == $GLOBALS['id_systeme']){
																echo " selected";
														}
														echo ">".$systemes['libelle_systeme']."</option>";
												}
								?>
                                            </select> </td>
                                    </tr>
                                </TABLE></td>
                            <td> <TABLE border='1' align="center"  width="100%">
                                    <CAPTION>
                                    <B><?php echo recherche_libelle_page('Chaine');?></B> 
                                    </CAPTION>
                                    <tr> 
                                        <td><select name="id_chaine" onChange="reload_page(id_systeme.value,this.value,'');">
                                 <?php if(is_array($GLOBALS['chaines_systeme']))
												foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
														echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
														if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['id_chaine']){
																echo " selected";
														}
														echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
												}
								?>
                                            </select> </td>
                                    </tr>
                                </TABLE></td>
                            <td> <TABLE border='1' align="center"  width="100%">
                                    <CAPTION>
                                    <B><?php echo recherche_libelle_page('ExportLevel');?></B> 
                                    </CAPTION>
                                    <tr> 
                                      <td><select name="type_reg" onchange="reload_page(id_systeme.value, id_chaine.value, this.value);">
                                        <?php foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
											echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
											if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){ //Cas general
											//if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == 2){ //District: Cas Tanzanie
													echo " selected";
											}
											echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
									}
							?>
                                      </select></td>
                                    </tr>
                                </TABLE></td>
                        </tr>
                    </table>
					<BR/>
					 <TABLE class='espace_2' border='1'  width="98%">
                        <CAPTION>
                        <B><?php echo recherche_libelle_page('Localite');?></B> 
                        </CAPTION>
                        <tr> 
						<?php $arbre	= new arbre($GLOBALS['id_chaine']);
								foreach($arbre->chaine as $i=>$c) {
										if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) { //Cas General
										//if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == 2) { //Cas Tanzanie
												$curdepht = $i;
												break;
										}
								} 
								$arbre->type_access='config';
								$entete = $arbre->create_entete(0, $curdepht, true); 
								if ( isset($entete['code_regroup']) && ($entete['code_regroup'] <> '') ){
									if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){
										$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
																		A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
																	' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
																	WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
																	AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																	C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
																	' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.(int)$entete['code_regroup'].
																	' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
																	' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'].';';
									}else{
										$requete    = ' SELECT A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, 
																		A.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG '.
																	' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS A, '.$GLOBALS['PARAM']['LIAISONS'].' AS B, 
																	'.$GLOBALS['PARAM']['HIERARCHIE'].' AS C 
																	WHERE A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' = B.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
																	AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																	C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].''.
																	' AND B.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].' ='.(int)$entete['code_regroup'].
																	' AND A.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
																	' AND C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' ='.$GLOBALS['id_chaine'].
																	' ORDER BY A.'.$GLOBALS['CHAMP_ORDRE'].';';
									}           
								}else {
									if(!in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs'])){
										$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
										FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
										WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
										' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
									}else{
										$requete='SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS LIB_REG 
										FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
										WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$GLOBALS['type_reg'].
										' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$GLOBALS['type_reg']].')'.
										' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
									}           
								}
								
								$tab_regs 	= $GLOBALS['conn']->GetAll($requete);
								$nb_td_regs =	$GLOBALS['nb_td'];

						if( isset($entete['code_regroup']) and trim($entete['code_regroup']) <> ''){
								$combos_regs = preg_replace('/combo_regroups/', 'Formulaire', preg_replace('/(<form name="combo_regroups">)|(<\/form>)|(<br \/>)/', '', $entete['html']));
								$combos_regs = preg_replace('/<br>([[:blank:]]|[[:space:]])*<\/div>/', '</div>', $combos_regs);
								$combos_regs = str_replace('<select', '<select  style=\'width:100%;\'',$combos_regs); ;
								echo'<td align="center" valign="middle" width="25%"><table width="100%" height="100%"><tr><td align="center">' . $combos_regs . '</td></tr></table></td>';
								if($nb_td_regs > 1){
										$nb_td_regs = $nb_td_regs - 1 ;
								}
						}?>
						<td align="center" valign="middle"  width="75%"> 
								<?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', $nb_td_regs )); ?>
								<INPUT type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>">
						</td>
                        </tr>
                    </TABLE>
                    <br>
                        <TABLE class='espace_2' border='1'  width="98%">
                            <CAPTION><B><?php echo recherche_libelle_page('Periodes');?></B></CAPTION>
                            <tr> 
                                <td align="center" valign="middle">
										<?php $requete    = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' AS LIB_ANN
														FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].';';
										$tab_annees = $GLOBALS['conn']->GetAll($requete);
																										
										print (tableau_check( $tab_annees,$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], 'LIB_ANN', 'ANN', 'toutes', 'ALL_ANN', $GLOBALS['nb_td'] ));
										
										?>
										<INPUT type="hidden" name="nb_annees_dispo" value="<?php echo count($tab_annees);?>">
								</td>
                            </tr>
                        </TABLE>
						 <?php if($_SESSION['FILTRE']['FILTRE_1']==true){ ?>
						 <br>
                        <TABLE class='espace_2' border='1'  width="98%">
                            <CAPTION><B><?php echo recherche_libelle_page('filtr1').' : '.$GLOBALS['PARAM']['TYPE_FILTRE'];?></B></CAPTION>
                            <tr> 
								<td align="left"><a href="administration.php?val=aggregated_export&suppr_filter=1&id_systeme=<?php echo $GLOBALS['id_systeme'] ;?>&id_chaine=<?php echo $GLOBALS['id_chaine'] ;?>&type_reg=<?php echo $GLOBALS['type_reg'] ;?>" ><b><?php echo recherche_libelle_page('suppr_filtre'); ?></b></a></td>
							</tr>
							<tr> 
                                <td align="center" valign="middle">
										<?php $requete    = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'].' AS LIB_PERIOD
															FROM '.$GLOBALS['PARAM']['TYPE_FILTRE'].';';
											$tab_filtres = $GLOBALS['conn']->GetAll($requete);
											print (tableau_check2( $tab_filtres,$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], 'LIB_PERIOD', 'PERIOD', 'toutes', 'ALL_PERIOD', $GLOBALS['nb_td'] ));
											echo '<INPUT type="hidden" name="nb_filtres_dispo" value="'.count($tab_filtres).'">'; 
										?>
								</td>
                            </tr>
                        </TABLE>
						<?php }elseif($GLOBALS['PARAM']['FILTRE'] == true && $_SESSION['FILTRE']['FILTRE_1'] == false){ ?>
						<br/>
						<table border='1' class="espace_2" width="98%">
							<tr>
								<td align="left">
								 <a href="administration.php?val=aggregated_export&activ_filter=1&id_systeme=<?php echo $GLOBALS['id_systeme'] ;?>&id_chaine=<?php echo $GLOBALS['id_chaine'] ;?>&type_reg=<?php echo $GLOBALS['type_reg'] ;?>" ><b><?php echo recherche_libelle_page('ajout_filtre1').' '.$GLOBALS['PARAM']['TYPE_FILTRE']; ?></b></a> 
								</td>
							</tr>
						</table>
						<?php } ?>
						<?php if($_SESSION['FILTRE']['FILTRE_2']){ ?>
						<br>
                        <TABLE class='espace_2' border='1'  width="98%">
                            <CAPTION><B><?php echo recherche_libelle_page('filtre2').' : '.$_SESSION['FILTRE']['TYPE_FILTRE_2'];?></B></CAPTION>
                            <tr>
								<td align="left">
								<a href="administration.php?val=aggregated_export&suppr_filter=2&id_systeme=<?php echo $GLOBALS['id_systeme'] ;?>&id_chaine=<?php echo $GLOBALS['id_chaine'] ;?>&type_reg=<?php echo $GLOBALS['type_reg'] ;?>" ><b><?php echo recherche_libelle_page('suppr_filtre'); ?></b></a>
								</td>
							</tr>
							<tr> 
                                <td align="center" valign="middle">
										<?php $requete    = 'SELECT '.$_SESSION['FILTRE']['CODE_TYPE_FILTRE_2'] .', '.$_SESSION['FILTRE']['LIBELLE_TYPE_FILTRE_2'].' AS LIB_FILTRE2
															FROM '.$_SESSION['FILTRE']['TYPE_FILTRE_2'].';';
											$tab_filtres2 = $GLOBALS['conn']->GetAll($requete);
											print (tableau_check2( $tab_filtres2, $_SESSION['FILTRE']['CODE_TYPE_FILTRE_2'], 'LIB_FILTRE2', 'FILTRE2', 'toutes', 'ALL_FILTRE2', $GLOBALS['nb_td'] ));
											echo '<INPUT type="hidden" name="nb_filtres2_dispo" value="'.count($tab_filtres2).'">'; 
										?>
								</td>
                            </tr>
                        </TABLE>
						<?php }else{ ?>
						<br/>
						<table border='1' class="espace_2" width="98%">
							<tr>
								<td align="left">
								<!--a href="administration.php?val=aggregated_export&id_systeme=<?php echo $GLOBALS['id_systeme'] ;?>&id_chaine=<?php echo $GLOBALS['id_chaine'] ;?>&type_reg=<?php echo $GLOBALS['type_reg'] ;?>"  onclick="javascript:OpenAggregExportFiltre(<?php echo $GLOBALS['id_systeme'].','.$GLOBALS['id_chaine'].','.$GLOBALS['type_reg'] ;?>)" ><b><?php echo recherche_libelle_page('ajout_filtre'); ?></b></a-->
								<a href="javascript:OpenAggregExportFiltre(<?php echo $GLOBALS['id_systeme'].','.$GLOBALS['id_chaine'].','.$GLOBALS['type_reg'] ;?>)"  onclick="" ><b><?php echo recherche_libelle_page('ajout_filtre'); ?></b></a>
								<!-- <a href="administration.php?val=aggregated_export&id_systeme=<?php echo $GLOBALS['id_systeme'] ;?>&id_chaine=<?php echo $GLOBALS['id_chaine'] ;?>&type_reg=<?php echo $GLOBALS['type_reg'] ;?>" ><b><?php echo recherche_libelle_page('ajout_filtre'); ?></b></a> -->
								</td>
							</tr>
						</table>
						<?php } ?>
						<br>
                        <TABLE class='espace_2' border='1'  width="98%">
                            <CAPTION><B><?php echo recherche_libelle_page('Actions');?></B></CAPTION>
                            <tr> 
                                <td> 
									<table id="EXCEL_EXPORT" width="100%" border="1">
									<tr> 
										<td nowrap="nowrap"><?php echo recherche_libelle_page('ExcelFileLoc')?></td>
										<td>
											<div style="position :absolute ;">
											<input type="file" id="chemin" name="chemin" style="filter :alpha(opacity=0); opacity:0" size="50" 
											onchange="document.Formulaire.fichier_excel.value=document.Formulaire.chemin.value" />
											</div>
											<input size="50" type="text" name="fichier_excel" <?php echo 'value="'.$template_excel.'" ';?>/>
											<input type="button" value="..." onclick="document.getElementById('chemin').click();">
										</td>
									</tr>
									<tr> 
										<td nowrap="nowrap"><?php echo recherche_libelle_page('ExportFileName')?></td>
										<td>
										  <input type="text" size="60" name="export_file_name" id="export_file_name" value="<?php if(isset($_POST['export_file_name'])) echo $_POST['export_file_name']; else echo ''; ?>"/>
										</td>
									</tr>
									</table>
                                </td>
                            </tr>
                        </TABLE>
              </td>
            </tr>
            <tr> 
                <td align="center"> 
                        <TABLE class='espace_2' border='1'>
                            <tr> 
                                <td><INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"></td>
                            </tr>
                        </TABLE>
                </td>
            </tr>
        </table>
</div>

</FORM>
<?php if( $do_action_post == true ){
			//echo '<pre>';
			//print_r($_POST);
			$_POST['fichier_zip'] = preg_replace("/[\\|\/|:|*|\?|\"|<|>|\||-]/","_",$_POST['fichier_zip']);
			$_SESSION['cfg_exp'] = $_POST;
			$det_pp = 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=no,status=no,resizable=no,width=320, height=35, left=400, top=200';
			ouvrir_popup('administration.php?val=aggregated_action_export','popup',$det_pp);	
	}
?>
<script type="text/Javascript" language="javascript">
	Montrer_Cacher_Div('XML_EXPORT','');
	<?php if(count($_POST)==0) echo "Nom_Fichier_Export();\n"; ?>
</script>
