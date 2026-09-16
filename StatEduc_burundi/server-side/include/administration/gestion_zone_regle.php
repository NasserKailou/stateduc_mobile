<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				
				print(" function MessMAJ(action,res_action){ \n");
		
				print(" 		var i = 0; \n");
				print(" 		if(action=='AddZone' || action=='AddTabM' ) i = 0; \n");
				print(" 		else if(action=='UpdZone' || action=='UpdTabM' ) i = 1; \n");
				print(" 		else if(action=='SupZone' || action=='SupTabM' ) i = 2; \n");
						
				print(" 		OK00 = \"".$_SESSION['GestZones']['TxtAlert']['PbIns']."\"; \n");
				print(" 		OK01 = \"".$_SESSION['GestZones']['TxtAlert']['OkIns']."\"; \n");
				print(" 		OK10 = \"".$_SESSION['GestZones']['TxtAlert']['PbUpd']."\"; \n");
				print(" 		OK11 = \"".$_SESSION['GestZones']['TxtAlert']['OkUpd']."\"; \n");
				print(" 		OK20 = \"".$_SESSION['GestZones']['TxtAlert']['PbSup']."\"; \n");
				print(" 		OK21 = \"".$_SESSION['GestZones']['TxtAlert']['OkSup']."\"; \n");
				
				print(" 		var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); \n");		
				
				print(" 		alert(OK[i][res_action]); \n");
				print(" } \n");
				
				print("\t //--> \n");
				
				print("function reload(choix) {\n");
				print("\tlocation.href   = '?val=OpenPopupZoneRegle&id_zone_active=".$_GET['id_zone_active']."&choix_affich='+choix;\n");
				print("}\n");
				print("</script>\n");
				
			
			function get_regles(){
					if(isset($_GET['choix_affich']) && $_GET['choix_affich']=='active_regles')
						$requete	=	"SELECT   DICO_REGLE_ZONE.* FROM   DICO_REGLE_ZONE, DICO_REGLE_ZONE_ASSOC 
											WHERE DICO_REGLE_ZONE.ID_REGLE_ZONE =  DICO_REGLE_ZONE_ASSOC.ID_REGLE_ZONE 
											AND	ID_ZONE = ".$_SESSION['GestZones']['id_zone_active'];
					else
						$requete	=	"SELECT   * FROM   DICO_REGLE_ZONE";

					$GLOBALS['regles']	= $GLOBALS['conn_dico']->GetAll($requete);
			}
			function get_regles_zone(){
					$requete	=	"	SELECT    ID_REGLE_ZONE
												FROM      DICO_REGLE_ZONE_ASSOC
												WHERE     ID_ZONE = ".$_SESSION['GestZones']['id_zone_active'];

					$GLOBALS['regles_zone'] = $GLOBALS['conn_dico']->GetAll($requete);
			}
			function set_champs_regle(){
					$GLOBALS['champs_regle'] = array();
					$GLOBALS['champs_regle'][] = array('idTrad'=>'TypeData','champ'=>'TYPE_DONNEES');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'SizeData','champ'=>'TAILLE_DONNEES');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'FrmData','champ'=>'FORMAT_DONNEES');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'IntVal','champ'=>'INTERVALLE_VALEURS');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'ValMin','champ'=>'VALEUR_MINIMALE');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'ValMax','champ'=>'VALEUR_MAXIMALE');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'CtrlPres','champ'=>'CONTROLE_PRESENCE');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'CtrlParu','champ'=>'CONTROLE_PARUTION');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'CtrlObli','champ'=>'CONTROLE_OBLIGATION');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'IntRef','champ'=>'CONTROLE_INTEGRITE_REF');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'CtrlEdit','champ'=>'CONTROLE_EDITION');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'val_enum','champ'=>'VALEURS_ENUM');
					$GLOBALS['champs_regle'][] = array('idTrad'=>'ctrl_uni','champ'=>'CONTROLE_UNICITE');
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
		
		function UpdateZoneRegle(){ /// Mise à jour zone n°iZone de la table mére n°iTab 
				$conn 								= $GLOBALS['conn_dico'];
				$GLOBALS['Action']		= 'UpdZone';
				$GLOBALS['OkAction'] 	= 1;
				$GLOBALS['post'] 			= 1;
					
				// on enlève toutes les regles 
				$requete 	= " DELETE FROM   DICO_REGLE_ZONE_ASSOC
											WHERE     ID_ZONE  =	".$_SESSION['GestZones']['id_zone_active'];
					//echo '<br>'.$requete.'<br>';
				if ($GLOBALS['conn_dico']->Execute($requete) === false){
						$GLOBALS['OkAction'] 	= 0;
						echo '<br>'.$requete.'<br>';
				}
				
				//echo'TabMs_join<br><pre>';
				//print_r($GLOBALS['TabMs_join']);
				foreach($GLOBALS['regles'] as $i => $regle){
						if( isset( $_POST['AppRegle_'.$regle['ID_REGLE_ZONE']] ) and  ( $_POST['AppRegle_'.$regle['ID_REGLE_ZONE']] =='1') ){
									
									$requete 	= ' INSERT INTO DICO_REGLE_ZONE_ASSOC (ID_ZONE,ID_REGLE_ZONE)
															VALUES ('.$_SESSION['GestZones']['id_zone_active'].','.$regle['ID_REGLE_ZONE'].')';
								
								if ($GLOBALS['conn_dico']->Execute($requete) === false){
										$GLOBALS['OkAction'] 	= 0;
										echo '<br>'.$requete.'<br>';
								}
								//// FIN INSERTION DE LA 2ème LIGNE DE JOINTURE
						}
				}
				 //exit;
		}
		function AlerteMAJ(){
				if($GLOBALS['post'] ==1){ // si on est aprés soumission
						print("<script type=\"text/javascript\">\n");
						print("\t <!-- \n");
						print("MessMAJ('".$GLOBALS['Action']."',".$GLOBALS['OkAction']."); \n");
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
		
		lit_libelles_page('/gestion_zone.php');
		get_regles();
		set_champs_regle();
			
		if( $_POST['post'] =='1'){ // Actions 
				
				UpdateZoneRegle();
				get_regles();
				$GLOBALS['Val'] =	array();
				foreach($GLOBALS['regles'] as $i => $regle){
						if( isset( $_POST['AppRegle_'.$regle['ID_REGLE_ZONE']] ) and  ( $_POST['AppRegle_'.$regle['ID_REGLE_ZONE']] <> '') ){
								$GLOBALS['Val'][$regle['ID_REGLE_ZONE']]	= $_POST['AppRegle_'.$regle['ID_REGLE_ZONE']];
						}
				}

		}// FIN Actions 
		else{
					if(isset($_GET['id_zone_active'])){
							$_SESSION['GestZones']['id_zone_active'] = $_GET['id_zone_active'];
					}
					//echo $_SESSION['GestZones']['id_zone_active'];
					//exit;
					get_regles_zone();
					//get_joins_exists();
					$GLOBALS['Val'] =	array();
					foreach($GLOBALS['regles_zone'] as $i => $regle){
							$GLOBALS['Val'][$regle['ID_REGLE_ZONE']] = '1';
					}
		}
	
		//echo'POST<pre>';
		//print_r($_POST);
		//echo'GLOBALS[Val]<br><pre>';
		//print_r($GLOBALS['Val']);		
?>
<body>
						

<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
 		<div  style="position:absolute; top: 46px; left: 15px; width: 706px; height: 30px;"> 
        <table><tr><td>
				<?php $i 			= get_izone_itab($_SESSION['GestZones']['id_zone_active']);
							$iTab		= $i['iTab'];
							$iZone	= $i['iZone'];
							$zone		=	$_SESSION['GestZones']['zones'][$iTab][$iZone];
							echo recherche_libelle_page('TitRegle').'&nbsp;<b>'.$zone['CHAMP_PERE'].'</b>'; 
						?>
				</td>
				<td>&nbsp;&nbsp;</td>
          		<td>
            	<input type="radio" name="choix_affich" value="1" <?php if((isset($_GET['choix_affich']) && $_GET['choix_affich']=='all_regles') || !isset($_GET['choix_affich']))  echo " checked "; ?> onClick="reload('all_regles')">
            	<b><?php echo recherche_libelle_page('all_regles'); ?></b>
				</td>
		        <td>
            	<input type="radio" name="choix_affich" value="2" <?php if(isset($_GET['choix_affich']) && $_GET['choix_affich']=='active_regles')  echo " checked " ?> onClick="reload('active_regles')">
				<b><?php echo recherche_libelle_page('active_regles'); ?></b>
				</td>
				</tr>
				</table>
		</div>    
		 
    <div  style="position:absolute; left: 15px; top: 81px; width: 700px; height: 400px; overflow: scroll;">
       
        <table><tr> <td align="center" valign="middle">
								<?php
								foreach($GLOBALS['regles'] as $i => $regle){
									?>
										<table width="98%">
													<tr> 
													<td align=center><?php echo"".recherche_libelle_page('AppReg')."";?></td>
                                                    <td align=center>RULE NAME <?php //echo"".recherche_libelle_page('Rule_Name')."";?></td>
													<?php foreach($GLOBALS['champs_regle'] as $i => $champ){
																if( trim($regle[$champ['champ']]) <> '' ){
													?>
																		<td align=center><?php echo"".recherche_libelle_page($champ['idTrad'])."";?></td>
														<?php }
														} 
														?>
													</tr>
													<tr> 
													<td align=center nowrap> <?php echo $regle['LIBELLE_REGLE_ZONE'] ;?><INPUT type="checkbox" <?php echo'name="AppRegle_'.$regle['ID_REGLE_ZONE'].'"';?> value="1"
													<?php if( isset($GLOBALS['Val'][$regle['ID_REGLE_ZONE']]) and $GLOBALS['Val'][$regle['ID_REGLE_ZONE']]=='1' ) echo ' checked';?> >
													</td>
                                                    <td align=center nowrap="nowrap"><span style="color: #0000FF; font-weight:bold;"><?php echo recherche_libelle($regle['ID_REGLE_ZONE'],$_SESSION['langue'],'DICO_REGLE_ZONE');?></span></td>
													<?php foreach($GLOBALS['champs_regle'] as $i => $champ){
																if( trim($regle[$champ['champ']]) <> '' ){
													?>
																		<td align=center><b><?php echo"".$regle[$champ['champ']]."";?></b></td>
														<?php }
														} 
														?>
													</tr>
							
		  </table><br />
													<?php }
												?>
												</td></tr>
	  </table>
  </div> 
    <div  style="position:absolute; left: 200px; top: 502px; width: 153px; height: 38px;"> 
        <table>
            <tr> 
                <td align='center' nowrap> <INPUT type='hidden' id='post' name='post' value='1'> 
                    <INPUT type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Enrgstr').'"';?>>
                    &nbsp;&nbsp; <INPUT type="button" <?php echo 'value="'.recherche_libelle_page('Close').'"';?>
													 onClick="javascript:fermer();"> </td>
            </tr>
        </table>
  </div>
  
</FORM>
</body>
<?php AlerteMAJ();
?>


