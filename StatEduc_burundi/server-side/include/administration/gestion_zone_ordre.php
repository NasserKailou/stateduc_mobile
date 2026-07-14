<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; ?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print(" function AvertirSupp(iTab,iZone,cas_elem,lib_elem){ \n");
				print(" 		var TxtAvertSuppZone =\"".$_SESSION['GestZones']['TxtAlert']['AvSupZone']."\"; \n");
				print(" 		var TxtAvertSuppTabM =\"".$_SESSION['GestZones']['TxtAlert']['AvSupTabM']."\"; \n");
				print(" 		if(cas_elem=='Zone'){ \n");
				print(" 				var TxtAvert = TxtAvertSuppZone + lib_elem ; \n");
				print(" 				var Act   = 'SupZone'; \n");
				print(" 		} \n");
				print(" 		else if(cas_elem=='TabM'){ \n");
				print(" 				var TxtAvert = TxtAvertSuppTabM + lib_elem ; \n");
				print(" 				var Act   = 'SupTabM'; \n");
				print(" 		} \n");
				print(" 		if(confirm(TxtAvert)){ \n");
				print(" 				Action(iTab,iZone,cas_elem,Act); \n");
				print(" 		} \n");
				print(" } \n\n");
											

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
				print(" 		if (OK[i][res_action] == OK11) {window.parent.location.reload();} \n");
				print(" } \n");
				
				print("\t //--> \n");
				print("</script>\n");
		
		function get_systemes(){/// get_systemes()
        // Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
        // TODO: à virer de l'accueil
      	$conn 		= $GLOBALS['conn'];
		if(!isset($_SESSION['fixe_secteurs']) || (count($_SESSION['fixe_secteurs']) == 0)){
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
		$_SESSION['GestZones']['systemes'] = $conn->GetAll($requete);
				
				if(isset($_GET['id_systeme_choisi'])){
						$_SESSION['GestZones']['id_systeme'] 	= $_GET['id_systeme_choisi'];
				}elseif(isset($_SESSION['secteur'])){
						$_SESSION['GestZones']['id_systeme'] 	= $_SESSION['secteur'];
				}
				if(!isset($_SESSION['GestZones']['id_systeme'])){
						$_SESSION['GestZones']['id_systeme'] 			= $_SESSION['GestZones']['systemes'][0]['id_systeme'];
				}
				
				if (is_array($_SESSION['GestZones']['systemes'] )){
                    foreach($_SESSION['GestZones']['systemes'] as $i => $systeme){
                            if( $systeme['ID_SYSTEME'] == $_SESSION['GestZones']['id_systeme'] ){
                                    $_SESSION['GestZones']['libelle_systeme'] = $systeme['LIBELLE_SYSTEME'];
                                    break;	
                            }
                    }
                }

				//echo $requete .'<br>';
    } // FIN  get_systemes()

			///// fonction de récupération des zones du theme
		function get_zones_systeme(){
				get_systemes();
				
				$requete =" SELECT   DICO_ZONE_SYSTEME.ID_ZONE, DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE, DICO_ZONE.CHAMP_PERE, DICO_ZONE_SYSTEME.ACTIVER
										FROM     DICO_ZONE_SYSTEME, DICO_ZONE 
										WHERE 	 DICO_ZONE_SYSTEME.ID_ZONE = DICO_ZONE.ID_ZONE
										AND    	 DICO_ZONE.ID_THEME = ".$_SESSION['GestZones']['id_theme']."
										AND 		 DICO_ZONE_SYSTEME.ID_SYSTEME = ".$_SESSION['GestZones']['id_systeme']." 
										AND      DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme'
                                        ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE
				";
				//echo $requete ;			
				$_SESSION['GestZones']['zones_systeme'] = $GLOBALS['conn_dico']->GetAll($requete);
				//$_SESSION['GestZones']['zones_systeme'] = $all_res[0];
				//$GLOBALS['ValZoneSys']	= $_SESSION['GestZones']['zone_systeme'];
				/*
				$GLOBALS['ValZoneSys'] 														= array();
				$GLOBALS['ValZoneSys']['TYPE_OBJET']							= $zone_systeme['TYPE_OBJET'];
				$GLOBALS['ValZoneSys']['ORDRE_AFFICHAGE']					= $zone_systeme['ORDRE_AFFICHAGE'];
				$GLOBALS['ValZoneSys']['ACTIVER']									= $zone_systeme['ACTIVER'];
				$GLOBALS['ValZoneSys']['ATTRIB_OBJET']						= $zone_systeme['ATTRIB_OBJET'];
				$GLOBALS['ValZoneSys']['AFFICHE_TOTAL']						= $zone_systeme['AFFICHE_TOTAL'];
				$GLOBALS['ValZoneSys']['SAUT_LIGNE']							= $zone_systeme['SAUT_LIGNE'];
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
		
		function UpdateZonesSysteme(){ /// Mise à jour zone n°iZone de la table mére n°iTab 
				$GLOBALS['Action']		= 'UpdZone';
				$GLOBALS['OkAction'] 	= 1;
				$GLOBALS['post'] 			= 1;
				
				$id_syst = $_SESSION['GestZones']['id_systeme'];
				
				foreach ($_SESSION['GestZones']['zones_systeme'] as $i => $zone){
						if(!$_POST['ACTIVER_'.$i]){
								$_POST['ACTIVER_'.$i] = 0;
						}
						if(!$_POST['ORDRE_AFFICHAGE_'.$i]){
								$_POST['ORDRE_AFFICHAGE_'.$i] = 0;
						}
						$requete 	= " UPDATE    DICO_ZONE_SYSTEME
													SET       ORDRE_AFFICHAGE	=".$_POST['ORDRE_AFFICHAGE_'.$i]."
																		,ACTIVER =	".$_POST['ACTIVER_'.$i]."
													WHERE     ID_ZONE  = 	".$_POST['ID_ZONE_'.$i]."
													AND ID_SYSTEME = ".$_SESSION['GestZones']['id_systeme'];
						
						//echo $requete.'<br>';
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
						  	$GLOBALS['OkAction'] 	= 0;
						}
				 }
				 //exit;
		}

		lit_libelles_page('/gestion_zone.php');
		if(isset($_POST['post']) && count($_POST['post']) > 0){ // Actions 
				UpdateZonesSysteme();
				get_zones_systeme();
				foreach ($_SESSION['GestZones']['zones_systeme'] as $i => $zone){
						$GLOBALS['Val'][$i]['ID_ZONE'] 					= $zone['ID_ZONE'];
						$GLOBALS['Val'][$i]['CHAMP_PERE']				= $zone['CHAMP_PERE']; 
						$GLOBALS['Val'][$i]['LIBELLE']					= recherche_libelle($zone['ID_ZONE'],$_SESSION['langue'],'DICO_ZONE');
						$GLOBALS['Val'][$i]['ORDRE_AFFICHAGE'] 	= $zone['ORDRE_AFFICHAGE'];	
						$GLOBALS['Val'][$i]['ACTIVER']					= $zone['ACTIVER'];
				}

		}// FIN Actions 
		else{
					
					get_zones_systeme();
					foreach ($_SESSION['GestZones']['zones_systeme'] as $i => $zone){
					

							$GLOBALS['Val'][$i]['ID_ZONE'] 					= $zone['ID_ZONE'];
							$GLOBALS['Val'][$i]['CHAMP_PERE']				= $zone['CHAMP_PERE']; 
							$GLOBALS['Val'][$i]['LIBELLE']					= recherche_libelle($zone['ID_ZONE'],$_SESSION['langue'],'DICO_ZONE');
							$GLOBALS['Val'][$i]['ORDRE_AFFICHAGE'] 	= $zone['ORDRE_AFFICHAGE'];	
							$GLOBALS['Val'][$i]['ACTIVER'] 					= $zone['ACTIVER'];
					}

		}
		function AlerteMAJ(){
				if(isset($_POST['post']) && ($GLOBALS['post'] ==1)){ // si on est aprés soumission
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
	
		//echo'POST<pre>';
		//print_r($_POST);
		
?>
<body>
<br/><br/>
<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
    <span style="position:absolute; width:720px; height:90px; left: 15px; overflow: no; top: 50px;" class="classe_table"> 
    <?php //$TabValZoneSys = $_SESSION['GestZones']['zone_systeme'];
		?>
    <div  style="position:absolute; top: 10px; left: 10px;"> 
        <table><tr><td>
				<b><?php echo recherche_libelle_page(TitOrdZ); ?></b>
				</td></tr></table>
		</div>
				
    <div  style="position:absolute; top: 41px; width: 86px; height: 37px; left: 10px;"> 
        <table>
						<tr> 
								<td nowrap> <?php echo recherche_libelle_page(ChoixSyst); ?> 
								</td>
								<td><select name="id_systeme"	onChange="ChangerSystemeOrdre($(this).val());">
												<?php foreach ($_SESSION['GestZones']['systemes'] as $i => $systemes){
									echo "<option value='".$systemes['ID_SYSTEME']."'";
									if ($systemes['ID_SYSTEME'] == $_SESSION['GestZones']['id_systeme']){
											echo " selected";
									}
									echo ">".$systemes['LIBELLE_SYSTEME']."</option>";
							}
					?>
										</select> </td>
						</tr>
				</table>
                    
    </div>
		<div  style="position:absolute; top: 10px; right: 5px;"> 
        <table>
            <tr> 
                <td> <div name="DivChoixThemes" id="DivChoixThemes"> 
                        <table>
                              <tr> 
                                <td nowrap> <?php echo recherche_libelle_page(NumSyst); ?> 
                                </td>
                                <td nowrap><b><?php echo $_SESSION['GestZones']['id_systeme']; ?></b></td>
                            </tr>
                            <tr> 
                                <td nowrap> <?php echo recherche_libelle_page(NomSyst); ?> 
                                </td>
                                <td nowrap><b><?php echo $_SESSION['GestZones']['libelle_systeme']; ?></b></td>
                            </tr>
                        </table>
                    </div></td>
            </tr>
        </table>
    </div>
  </span> 
	<?php if( count($_SESSION['GestZones']['zones_systeme']) > 0){
	?>
	  <div id="zone_container"> 
        <div>
						<div class="ui-widget-header">	
								<div class="float_left" style="width:69px; text-align:center; line-height:30px;"><?php echo"".recherche_libelle_page(IdZone)."";?></div>
								<div class="float_left" style="width:219px; text-align:center; line-height:30px;"><?php echo"".recherche_libelle_page(ChPere)."";?></div>
								<div class="float_left" style="width:298px; text-align:center; line-height:30px;"><?php echo"".recherche_libelle_page(LibChp)."";?></div>
								<div class="float_left" style="width:50px; text-align:center;"><?php echo"". recherche_libelle_page(OrdAff)."";?></div>
								<div class="float_left" style="width:50px; text-align:center; line-height:30px;"><?php echo"".recherche_libelle_page('Activer')."";?></div>
								<div class="float_clear"></div>
						</div>
						<div>
															<ul id="sortable">

												<?php foreach ($_SESSION['GestZones']['zones_systeme'] as $i => $zone){
														$ID_ZONE 					= $GLOBALS['Val'][$i]['ID_ZONE'] ;
														$CHAMP_PERE 			= $GLOBALS['Val'][$i]['CHAMP_PERE'] ;
														$LIBELLE 					= $GLOBALS['Val'][$i]['LIBELLE'] ;
														$ORDRE_AFFICHAGE 	= $GLOBALS['Val'][$i]['ORDRE_AFFICHAGE'] ;
														$ACTIVER 					= $GLOBALS['Val'][$i]['ACTIVER'] ;
														// echo recherche_libelle($ID_ZONE,$_SESSION['langue'],'DICO_ZONE');
												?>
														 <li class="ui-state-default">


																	<input type='hidden' name="<?php echo'ID_ZONE_'.$i;?>" value="<?php echo $ID_ZONE;?>" readonly size='4'/>
																	<input type='hidden' name="<?php echo'CHAMP_PERE_'.$i;?>" value="<?php echo $CHAMP_PERE;?>"readonly size='35'/>
																	<input type='hidden' name="<?php echo'LIBELLE_'.$i;?>" value="<?php echo $LIBELLE;?>" readonly size='50'/>
																	
																	<?php $nbBr = strlen(trim($LIBELLE)) / 60; $br = ''; for ($j = 1; $j <= $nbBr; $j++) {$br .= '<br/><br/>';} ?>
																	<label class="zone_id"><?php echo $ID_ZONE;?><?php echo $br; ?></label>
																	<label class="zone_champ_pere"><?php echo strlen($CHAMP_PERE)>0?$CHAMP_PERE:'&nbsp;';?><?php echo $br; ?></label>
																	<label class="zone_libelle"><?php echo strlen($LIBELLE)>0?$LIBELLE:'&nbsp;';?></label>
																	<input type="text" name="<?php echo'ORDRE_AFFICHAGE_'.$i;?>" value="<?php echo $ORDRE_AFFICHAGE;?>" class="zone_ordre_affichage_input"/><span>&nbsp;&nbsp;</span>
																	<input type="checkbox" name="<?php echo'ACTIVER_'.$i;?>"   value="1" <?php if($ACTIVER=='1') echo' checked';?> class="zone_activer_input"/><span class="ui-icon ui-icon-arrowthick-2-n-s" style="vertical-align:top;"></span>
															</li>

														<?php }
													?>
													</ul>
													</div>
												<!--tr> 


													<td colspan=5 align='center'>&nbsp;
													
													</td>
											</tr-->

								
											<div align="center"> <INPUT type='hidden' id='post' name='post' value='1'/> 
													<!--td colspan=5 align='center' nowrap="nowrap"-->
														
														<INPUT   style="width:59%;"  type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Enrgstr').'"';?>/>
														<INPUT   style="width:40%;"  type="button" <?php echo 'value="'.recherche_libelle_page('Close').'"';?> onClick="javascript:fermer();"/>
													<!--/td-->
											</div>


											 
											</div>

		</div>
		<?php }else{		
		?>
		
    <div  style="position:absolute; left: 15px; top: 207px; width: 650px; height: 114px;"> 
        <table width="100%">
													
											<tr> 
													<td colspan=2 align='center'>
													<br> 
													<div >
														<?php echo '<span style="color: #FF0000;">'. recherche_libelle_page('NoZoneSys').' : </span>
														<br/> <span style="font-weight: bold; color: #FF0000;">'.
														$_SESSION['GestZones']['libelle_systeme'] .'</span>';?>
													</div>
													<br>
													</td>
											</tr>
								
											<tr> 
													<td align='center'>
													<input type='button'  style="width:100%;" name='' <?php echo"value='".recherche_libelle_page('ImpZonSys')."'";?>
													onClick="OpenPopupImpZonesSys(<?php echo $_SESSION['GestZones']['id_theme'].', '.$_SESSION['GestZones']['id_systeme'];?>);"/>
													</td>
													<td align='center'>
													<INPUT type="button" style="width:100%;" <?php echo 'value="'.recherche_libelle_page('ClsWind').'"';?>
													 onClick="javascript:fermer();"/>
													</td>
											</tr>
											 
											</table>
		</div>
		<?php //Alerter(recherche_libelle_page(NoZoneSys)) ;
				}		
		?>
</FORM>
</body>
<?php AlerteMAJ();
?>


