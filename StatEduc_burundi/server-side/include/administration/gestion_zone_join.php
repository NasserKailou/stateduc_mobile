<?php 
session_start();
include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; 	
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php 
	print("<script type=\"text/javascript\">\n");
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
	print(" } \n");
	
	if(!isset($_GET['isrefresh']) && (!isset($_POST['post']) || $_POST['post'] !='1')) { 
		print("$(function() { 
			var url = document.location.href + '&isrefresh=ok'; 
			document.location = url;
			}); \n");
	}
	
	print("\t //--> \n");
	print("</script>\n");
		
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
	function get_joins_exists(){
		$conn 		= $GLOBALS['conn_dico'];
		$requete	=	"	SELECT     TJoin_1.ID_ZONE, TJoin_1.N_JOINTURE
									FROM       DICO_ZONE_JOINTURE as TJoin_1,
														 DICO_ZONE_JOINTURE as TJoin_2
									WHERE      TJoin_1.ID_THEME 	= TJoin_2.ID_THEME
									AND 			 TJoin_1.N_JOINTURE = TJoin_2.N_JOINTURE
									AND		     TJoin_1.ID_THEME = ".$_SESSION['GestZones']['id_theme']." 
									AND   		 TJoin_2.ID_ZONE 	= ".$_SESSION['GestZones']['id_zone_active']."
									AND  			 TJoin_1.ID_ZONE <> ".$_SESSION['GestZones']['id_zone_active'];
						
		//echo'<br>'.$requete;
		$all_res = array();
		$all_res 	= $GLOBALS['conn_dico']->GetAll($requete);
		$GLOBALS['joins_exists'] = array();
		if(is_array($all_res)){
			foreach($all_res as $i => $join){
						$iTab_iZone = get_izone_itab($join['ID_ZONE']);
						$iTab				= $iTab_iZone['iTab'];
						$iZone			= $iTab_iZone['iZone'];
						$GLOBALS['joins_exists'][$iTab][$iZone] = $join['N_JOINTURE'];
						//echo '<br>join='.$_SESSION['GestZones']['zones'][$iTab][$iZone]['CHAMP_PERE']."($iTab,$iZone)";
			}
		}
	//	echo '<pre>';
		//print_r($GLOBALS['joins_exists']);
		/*$rs	= $conn->Execute($requete);
		while (!$rs->EOF){
				$ID_ZONE_JOIN = $rs->fields['ID_ZONE'];
				$rs->MoveNext();
		}*/
		//$GLOBALS['joins_exists']
	}
	function get_TabMs_join(){
			//$conn 		= $GLOBALS['conn'];
			$idTab = -1;
			if(isset($_GET['id_tab_active'])){
				$idTab = $_GET['id_tab_active'];
			}
			$GLOBALS['TabMs_join'] = array();
			foreach($_SESSION['GestZones']['tables_meres'] as $iTab => $table_mere) {
					if ($iTab <> $idTab) {
							$GLOBALS['TabMs_join'][] = $iTab;
					}
			}			
	}
	function get_Zones_join(){
			
			$GLOBALS['Zones_join'] = array();
			foreach($GLOBALS['TabMs_join'] as $i => $iTab){
					foreach( $_SESSION['GestZones']['zones'][$iTab] as  $iZone => $zone ){
							$GLOBALS['Zones_join'][] = array( 'iTab' => $iTab , 'iZone' => $iZone );
					}
			}			
	}

	function get_max_join_theme(){
			$requete	=	"SELECT  MAX(N_JOINTURE) FROM  DICO_ZONE_JOINTURE";
									 //WHERE   ID_THEME 	= ".$_SESSION['GestZones']['id_theme'];
							
			return ($GLOBALS['conn_dico']->getOne($requete));
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
			//$this->libelle_theme   = $all_res[0][LIBELLE];
			return($all_res[0]['LIBELLE']);
	}
	
	function UpdateZoneJoin(){ /// Mise à jour zone n°iZone de la table mére n°iTab 
			$GLOBALS['Action']		= 'UpdZone';
			$GLOBALS['OkAction'] 	= 1;
			$GLOBALS['post'] 		= 1;
				
			// on enlève toutes les jointures existantes pour ce champ
			foreach($GLOBALS['joins_exists'] as $iTab => $Tab){
				foreach($Tab as $iZone => $num_join){
					$requete 	= " DELETE FROM   DICO_ZONE_JOINTURE
									WHERE   ID_THEME	=	".$_SESSION['GestZones']['id_theme']."
									AND  	N_JOINTURE	= ".$num_join;
					if ($GLOBALS['conn_dico']->Execute($requete) === false){
						$GLOBALS['OkAction'] 	= 0;
						echo '<br>'.$requete.'<br>';
					}
				}
			}
			$num_join = get_max_join_theme();
			
			//echo'TabMs_join<br><pre>';
			//print_r($GLOBALS['TabMs_join']);
			foreach($GLOBALS['TabMs_join'] as $i => $iTab){
					if( $_POST['JoinTabM_'.$iTab]<>'' ){
							$num_join++;
							
							//// INSERTION DE LA 1ère LIGNE DE JOINTURE
							$requete 	= ' INSERT INTO DICO_ZONE_JOINTURE (ID_THEME,ID_ZONE,N_JOINTURE)
														VALUES ('.$_SESSION['GestZones']['id_theme'].','.$_SESSION['GestZones']['id_zone_active'].','.$num_join.')';
							
							//echo $requete.'<br>';
							if ($GLOBALS['conn_dico']->Execute($requete) === false){
									$GLOBALS['OkAction'] 	= 0;
									echo '<br>'.$requete.'<br>';
							}
							//// FIN INSERTION DE LA 1ère LIGNE DE JOINTURE
							
							//// INSERTION DE LA 2ème LIGNE DE JOINTURE
							$zone 		= $_SESSION['GestZones']['zones'][$iTab][$_POST['JoinTabM_'.$iTab]];
							$requete 	= ' INSERT INTO DICO_ZONE_JOINTURE (ID_THEME,ID_ZONE,N_JOINTURE)
														VALUES ('.$_SESSION['GestZones']['id_theme'].','.$zone['ID_ZONE'].','.$num_join.')';
							
						//	echo $requete.'<br>';
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
	get_TabMs_join();
	get_joins_exists();
	if( $_POST['post'] =='1'){ // Actions 
	   //echo'POST<br><pre>';
			//print_r($_POST);
			
			UpdateZoneJoin();
			$GLOBALS['Val'] =	array();
			foreach($GLOBALS['TabMs_join'] as $i => $iTab){
				$GLOBALS['Val'][$iTab]	= $_POST['JoinTabM_'.$iTab];
			}

	}// FIN Actions 
	else{
			if(isset($_GET['id_zone_active'])){
					$_SESSION['GestZones']['id_zone_active'] = $_GET['id_zone_active'];
			}
			//echo $_SESSION['GestZones']['id_zone_active'];
			//exit;
			//get_joins_exists();
			$GLOBALS['Val'] =	array();
			foreach($GLOBALS['joins_exists'] as $iTab => $Tab){
					foreach($Tab as $iZone => $val){
							$GLOBALS['Val'][$iTab] = $iZone;
					}
			}
	}

	//echo'POST<pre>';
	//print_r($_POST); 
	//echo'$GLOBALS[Val]<br><pre>';
	//print_r($GLOBALS['Val']);
?>
<body>

<FORM name="Formulaire"  method="post" action="<?php echo $PHP_SELF; ?>" >
    <div  style="position:absolute; top: 45px; left: 20px; width: 408px; height: 30px;"> 
        <table width="65%">
            <tr>
                <td width="85%" nowrap valign="middle"> 
                    <?php $i 			= get_izone_itab($_SESSION['GestZones']['id_zone_active']);
							$iTab		= $i['iTab'];
							$iZone	= $i['iZone'];
							//echo $_SESSION['GestZones']['id_zone_active'];
							//exit;
							//echo'<pre>';
							//print_r($i);exit;
							$zone		=	$_SESSION['GestZones']['zones'][$iTab][$iZone];
							echo recherche_libelle_page(TitJoin).'&nbsp;<b>'.$zone['CHAMP_PERE'].'</b>'; 
						?>
				</td></tr></table>
		</div>
		  
    <div  style="position:absolute; left: 21px; top: 81px; width: 406px; height: 114px;"> 
        <table>
							<tr> 
								<td align=center><?php echo"".recherche_libelle_page(TabMere)."";?></td>
								<td align=center><?php echo"".recherche_libelle_page(ChJoin)."";?></td>
							</tr>
												<?php //echo '<pre>';
														//print_r($GLOBALS['Val']);
												foreach($GLOBALS['TabMs_join'] as $i => $iTab){
														
														$table_mere = $_SESSION['GestZones']['tables_meres'][$iTab];
														$zones			= $_SESSION['GestZones']['zones'][$iTab]; 
														//echo '<pre>';
														//print_r($zones);
														
														/*$ID_ZONE 					= $GLOBALS['Val'][$i]['ID_ZONE'] ;
														$CHAMP_PERE 			= $GLOBALS['Val'][$i]['CHAMP_PERE'] ;
														$LIBELLE 					= $GLOBALS['Val'][$i]['LIBELLE'] ;
														$ORDRE_AFFICHAGE 	= $GLOBALS['Val'][$i]['ORDRE_AFFICHAGE'] ;
														$ACTIVER 					= $GLOBALS['Val'][$i]['ACTIVER'] ;*/
														// echo recherche_libelle($ID_ZONE,$_SESSION['langue'],'DICO_ZONE');
														/*
														foreach ($zones as $iZone => $zone){
																//echo "<option value='$iZone'";
																if ( "{$GLOBALS['Val'][$iTab]}" == "$iZone" ){
																		//echo " selected";
																		echo 'val='.$GLOBALS['Val'][$iTab].'<br>';
																		echo 'iZone='.$iZone.'<br>';
																}
															//	echo ">".$zone['CHAMP_PERE']."</option>";
														}*/
														
												?>
															<tr> 
																	<td><INPUT type='text' name="<?php echo'TabM_'.$iTab;?>" value="<?php echo $table_mere['NOM_TABLE_MERE'];?>" readonly size='35'></td>
																	<td>
																	<select name="<?php echo"JoinTabM_$iTab";?>">
																			<option value=''></option>
																				<?php foreach ($zones as $iZone => $zone){
																									echo "<option value='$iZone'";
																									if ( trim($GLOBALS['Val'][$iTab]) == trim($iZone) ){
																											echo " selected";
																									}
																																																			
																									echo ">".$zone['CHAMP_PERE']."</option>";
																							}
																					
																					?>
																		</select>
																	</td>
															</tr>
														<?php }
													?>
													
												<tr> 
													
                <td colspan=2 align='center'>&nbsp; </td>
											</tr>
								
											<tr> 
													<td colspan=2 align='center'>
													<INPUT type='hidden' id='post' name='post' value='1'> 
                    			<INPUT type='submit' name='Input' <?php echo 'value="'.recherche_libelle_page('Enrgstr').'"';?>>&nbsp;&nbsp;
													<INPUT type="button" <?php echo 'value="'.recherche_libelle_page('ClsWind').'"';?>
													 onClick="javascript:fermer();"><!--
													 &nbsp;&nbsp;&nbsp;
													 <INPUT name="button" type="button" onClick="document.location.reload();return(false)" <?php echo "value=\"".recherche_libelle_page(Refresh)."\"";?>> -->
													</td>
											</tr>
											 
					</table>
		</div>
  
</FORM>
</body>
<?php AlerteMAJ();
?>


