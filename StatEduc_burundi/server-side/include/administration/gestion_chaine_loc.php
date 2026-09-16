<?php //include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; 	

// generation JS
				getTextAlert();
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				
				print(" function MessMAJ(action,res_action){ \n");
		
				print(" 		var i = 0; \n");
				print(" 		if(action=='AddZone' || action=='AddTabM' ) i = 0; \n");
				print(" 		else if(action=='UpdZone' || action=='UpdTabM' ) i = 1; \n");
				print(" 		else if(action=='SupZone' || action=='SupTabM' ) i = 2; \n");
						
				print(" 		OK00 = \"".$GLOBALS['TxtAlert']['PbIns']."\"; \n");
				print(" 		OK01 = \"".$GLOBALS['TxtAlert']['OkIns']."\"; \n");
				print(" 		OK10 = \"".$GLOBALS['TxtAlert']['PbUpd']."\"; \n");
				print(" 		OK11 = \"".$GLOBALS['TxtAlert']['OkUpd']."\"; \n");
				print(" 		OK20 = \"".$GLOBALS['TxtAlert']['PbSup']."\"; \n");
				print(" 		OK21 = \"".$GLOBALS['TxtAlert']['OkSup']."\"; \n");
				
				print(" 		var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); \n");		
				
				print(" 		alert(OK[i][res_action]); \n");
				print(" } \n");
				
				print("\t //--> \n");
				print("</script>\n");
?>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php function getTextAlert(){
				if(!($GLOBALS['TxtAlert'])){
						$GLOBALS['TxtAlert']['AvSupZone'] 	= get_libelle_trad(201,'DICO_MESSAGE');
						$GLOBALS['TxtAlert']['AvSupTabM'] 	= get_libelle_trad(202,'DICO_MESSAGE');
						
						$GLOBALS['TxtAlert']['OkIns']				= get_libelle_trad(203,'DICO_MESSAGE');
						$GLOBALS['TxtAlert']['OkUpd'] 			= get_libelle_trad(204,'DICO_MESSAGE');
						$GLOBALS['TxtAlert']['OkSup'] 			= get_libelle_trad(205,'DICO_MESSAGE');
						
						$GLOBALS['TxtAlert']['PbIns'] 			= get_libelle_trad(206,'DICO_MESSAGE');
						$GLOBALS['TxtAlert']['PbUpd'] 			= get_libelle_trad(207,'DICO_MESSAGE');
						$GLOBALS['TxtAlert']['PbSup'] 			= get_libelle_trad(208,'DICO_MESSAGE');
				}
				//echo'<pre>';
				//print_r($GLOBALS['TxtAlert']);
		}
			
		function get_systemes(){/// get_systemes()
				$conn 		= $GLOBALS['conn'];
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
        if(!(isset($_SESSION['fixe_secteurs']) && count($_SESSION['fixe_secteurs'])>0)){
			$requete             = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
                                    FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
                                    AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}else{
			$requete             = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
                                    FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
                                    WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
                                    AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')
									AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
		}
		//echo $requete;
        $GLOBALS['systemes'] = $conn->GetAll($requete);
		//print_r($GLOBALS['systemes']);
				
				if(isset($_GET['id_systeme_choisi'])){
						$GLOBALS['id_systeme'] 	= $_GET['id_systeme_choisi'];
				}elseif(isset($_POST['id_systeme'])){
						$GLOBALS['id_systeme'] 	= $_POST['id_systeme'];
				}elseif(isset($_SESSION['secteur'])){
						$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
				}
				if(!isset($GLOBALS['id_systeme'])){
						$GLOBALS['id_systeme'] 	= $GLOBALS['systemes'][0]['ID_SYSTEME']; 
				}
				
				foreach($GLOBALS['systemes'] as $i => $systeme){
						if( $systeme['ID_SYSTEME'] == $GLOBALS['id_systeme'] ){
								$GLOBALS['libelle_systeme'] = $systeme['LIBELLE_SYSTEME'];
								break;	
						}
				}
				//echo $requete .'<br>';
    } // FIN  get_systemes()

		function get_chaines_systeme(){
				$conn 		= $GLOBALS['conn'];
        $requete     = 'SELECT    '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' as CODE_TYPE_CH,
                        '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 	as LIBELLE_TYPE_CH
                        FROM      '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
                        WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$GLOBALS['id_systeme'] ;
        $GLOBALS['chaines_systeme'] = $conn->GetAll($requete);
        $GLOBALS['nb_ch_sys']       = count($GLOBALS['chaines_systeme']);
		
		//echo $requete .'<br>';
		} 

		function get_chaines_loc_systeme(){
				
				$GLOBALS['tab_chaines_loc'] = array();
        $requete     = "SELECT * FROM DICO_CHAINE_LOCALISATION WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT = ".$GLOBALS['id_systeme'] ;
				//echo  $requete ;
        $all_chaines_loc = $GLOBALS['conn_dico']->GetAll($requete);
				
				if(is_array($all_chaines_loc)){
						foreach($all_chaines_loc as $i => $chaine_loc){
						//echo'<pre>';
						//print_r($chaine_loc);
								$GLOBALS['tab_chaines_loc'][$chaine_loc['CODE_TYPE_CHAINE']] = $chaine_loc;
						}
				}
		} 
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
		}
		*/
		///// fonction de récupération des zones du theme
		function MAJ_Chaine_Loc(){ /// Mise à jour zone n°iZone de la table mére n°iTab 
				$conn 								= $GLOBALS['conn_dico'];
				$GLOBALS['OkAction'] 	= 1;
				$GLOBALS['post'] 			= 1;
				$GLOBALS['Action']		= 'UpdZone';
				
				$requete 	= " DELETE FROM   DICO_CHAINE_LOCALISATION
											WHERE     CODE_TYPE_SYSTEME_ENSEIGNEMENT  =	".$GLOBALS['id_systeme'];
				if ($GLOBALS['conn_dico']->Execute($requete) === false){	
						echo '<br>ERROR: '.$requete.'<br>';
						$GLOBALS['OkAction'] 	= 0;
				}

				foreach( $GLOBALS['chaines_systeme'] as $i => $ch_sys ){
				
						$requete 	= " DELETE FROM   DICO_TRADUCTION
													WHERE CODE_NOMENCLATURE=".$_POST['code_ch_'.$i]." 
													AND NOM_TABLE='DICO_CHAINE_LOCALISATION'";

						if ($GLOBALS['conn_dico']->Execute($requete) === false){	
								echo '<br>ERROR: '.$requete.'<br>';
								$GLOBALS['OkAction'] 	= 0;
						}
						
						if( isset($_POST['choisir_ch_'.$i]) and ($_POST['choisir_ch_'.$i] == 1) ){ // ajout ou modif 
								if(!$_POST['ordre_ch_'.$i]){
										$_POST['ordre_ch_'.$i] = 0;
								}
								$requete 	= ' INSERT INTO DICO_CHAINE_LOCALISATION
																(CODE_TYPE_CHAINE,CODE_TYPE_SYSTEME_ENSEIGNEMENT,ORDRE_TYPE_CHAINE)
															VALUES
																('.$_POST['code_ch_'.$i].','.$GLOBALS['id_systeme'].','.$_POST['ordre_ch_'.$i].')
														';
		
								if ($GLOBALS['conn_dico']->Execute($requete) === false){	
										echo '<br>ERROR: '.$requete.'<br>';
										$GLOBALS['OkAction'] 	= 0;
								}
								
								$requete 	= ' INSERT INTO  DICO_TRADUCTION
																(CODE_NOMENCLATURE,NOM_TABLE,CODE_LANGUE,LIBELLE)
															VALUES
																('.$_POST['code_ch_'.$i].",'DICO_CHAINE_LOCALISATION','".$_SESSION['langue']."',".$conn->qstr($_POST['libelle_ch_'.$i]).")";
		
								if ($GLOBALS['conn_dico']->Execute($requete) === false){	
										echo '<br>ERROR: '.$requete.'<br>';
										$GLOBALS['OkAction'] 	= 0;
								}
								insert_traduction('DICO_TRADUCTION', $_POST['code_ch_'.$i], 'DICO_CHAINE_LOCALISATION', $_SESSION['langue'], $_POST['libelle_ch_'.$i], 1);
						}
				}
		}

		function AlerteMAJ(){
				if($GLOBALS['post'] ==1){ // si on est aprés soumission
						print("<script type=\"text/javascript\">\n");
						print("\t <!-- \n");
						print("MessMAJ('".$GLOBALS['Action']."',".$GLOBALS['OkAction']."); \n");
						if($GLOBALS['OkAction'] == 1){
								//print("fermer(); \n");
						}
						print("\t //--> \n");
						print("</script>\n");
				}
		}
		function Alerter($mess){
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("alert(\"$mess\"); \n");
				print("\t //--> \n");
				print("</script>\n");
		}
		
		get_systemes();
		get_chaines_systeme();
		get_chaines_loc_systeme();
		
		if( $_POST['ActionChaineLoc'] =='1'){ // Actions 
				$GLOBALS['ActionPost'] = 1;
				MAJ_Chaine_Loc();
				get_chaines_systeme();
				get_chaines_loc_systeme();

		}// FIN Actions 

		lit_libelles_page('/gestion_zone.php');
		
?>
<body>

<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
<table align="center">
<caption><?php echo recherche_libelle_page('TitChLoc'); ?></caption>
<tr><td><br>
        <table>
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page('ChoixSyst'); ?> 
								</td>
								<td><select name="id_systeme"	onChange="ChangerSystemeLoc(id_systeme.value);">
							<?php foreach ($GLOBALS['systemes'] as $i => $systemes){
									echo "<option value='".$systemes['ID_SYSTEME']."'";
									if ($systemes['ID_SYSTEME'] == $GLOBALS['id_systeme']){
											echo " selected";
									}
									echo ">".$systemes['LIBELLE_SYSTEME']."</option>";
							}
					?>
										</select> </td>
						</tr>
				</table>
        <br>            
        <table>
							<tr> 
								<td nowrap> <?php echo recherche_libelle_page('NumSyst'); ?> 
								</td>
								<td nowrap><b><?php echo $GLOBALS['id_systeme']; ?></b></td>
						</tr>
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page('NomSyst'); ?> 
								</td>
								<td nowrap><b><?php echo $GLOBALS['libelle_systeme']; ?></b></td>
						</tr>
				</table>
				<?php if($GLOBALS['nb_ch_sys'] == 0){ ?>
				
        <?php echo '<br><span style="color : red">'. recherche_libelle_page('nochsys').'</span><br>'; 
				?>
			<?php }else{
							$val = array();
							//echo '<pre>';
							//print_r($GLOBALS['tab_chaines_loc']);
							/*if( $_POST['ActionChaineLoc'] =='1'){
									foreach( $GLOBALS['chaines_systeme'] as $i => $ch_sys ){
											$val['choisir_ch'][$i] 	= $_POST['choisir_ch_'.$i];
											$val['ordre_ch'][$i] 		= $_POST['ordre_ch_'.$i];
											$val['libelle_ch'][$i]	= $_POST['libelle_ch_'.$i];
											$val['code_ch'][$i] 		= $_POST['code_ch_'.$i];
									}
							}else{*/
                            if (is_array($GLOBALS['chaines_systeme'] )){
									foreach( $GLOBALS['chaines_systeme'] as $i => $ch_sys ){
											$code_ch 		=	$ch_sys['CODE_TYPE_CH'];
											$libelle_ch =	$ch_sys['LIBELLE_TYPE_CH'];	
											if(isset($GLOBALS['tab_chaines_loc'][$code_ch])){
													$val['choisir_ch'][$i] 	= 1;
													$val['ordre_ch'][$i] 		= $GLOBALS['tab_chaines_loc'][$code_ch]['ORDRE_TYPE_CHAINE'];
											}
											$val['libelle_ch'][$i]	= get_libelle($code_ch,'DICO_CHAINE_LOCALISATION',$libelle_ch);
											$val['code_ch'][$i] 		= $code_ch;
									}
                                }
							//}
		?>
        <br>
				<table>
				<caption><?php echo recherche_libelle_page('ListChLoc'); ?></caption>
            <tr> 
                <td nowrap align="center"><?php echo recherche_libelle_page('Choisir_Ch');?></td>
								<td nowrap align="center"><?php echo recherche_libelle_page('Code_Ch');?></td>
								<td nowrap align="center"><?php echo recherche_libelle_page('Lib_Ch');?></td>
								<td nowrap align="center"><?php echo recherche_libelle_page('Ordre_Ch');?></td>
            </tr>
						<?php if (is_array($GLOBALS['chaines_systeme'])){
						   foreach( $GLOBALS['chaines_systeme'] as $i => $ch_sys ){
						?>
                <tr>
								<td align="center"><INPUT type="checkbox" value="1" name="<?php echo 'choisir_ch_'.$i;?>"
								<?php if( isset($val['choisir_ch'][$i]) and ($val['choisir_ch'][$i]==1) ) echo ' CHECKED';?>></td>
								<td align="center"><INPUT value="<?php echo $val['code_ch'][$i];?>" size="2" name="<?php echo 'code_ch_'.$i;?>" readonly="1"></td>
								<td align="center"><INPUT value="<?php echo $val['libelle_ch'][$i];?>" size="35" name="<?php echo 'libelle_ch_'.$i;?>"></td>
								<td align="center"><INPUT value="<?php echo $val['ordre_ch'][$i];?>" size="2" name="<?php echo 'ordre_ch_'.$i;?>"></td>
								</tr>
						<?php }
							 }
						?>
						<tr>
							<td colspan="4" height="30" align="center">
							<INPUT type='hidden' id='ActionChaineLoc' name='ActionChaineLoc' value='1'> 
							<INPUT type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Enrgstr').'"';?>> 
							</td>
						</tr>
        </table>
				
		
		<?php }
		?>
		
		
		
    </span> 
		</td></tr>
</table>

</FORM>
</body>
<?php AlerteMAJ();
?>


