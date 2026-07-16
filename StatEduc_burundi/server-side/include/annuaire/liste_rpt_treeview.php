<?php lit_libelles_page('/liste_rpt_treeview.php');
	( isset($_GET['id_syst']) && (trim($_GET['id_syst']) <> '') ) ? ( $GLOBALS['id_syst'] = $_GET['id_syst'] ) : ( $GLOBALS['id_syst'] = $_SESSION['secteur'] ) ;
	
	$requete            = ' SELECT SYSTEME.ID_SYSTEME as id_systeme,
							SYSTEME.LIBELLE_SYSTEME as libelle_systeme
							FROM SYSTEME;';
	$GLOBALS['all_systemes'] = $GLOBALS['conn_dico']->GetAll($requete);

?>
<script type="text/Javascript">
	
	function recharger(id_syst) {
		location.href   = 'annuaire.php?val=viewer&id_syst='+id_syst;
	}

	function throw_rpt(id_rpt, id_syst) {
		//var chaine_eval ='parent.parent.principal.location.href ="'+url+'";';
		//eval(chaine_eval);
		if( id_rpt != '' ){
			if(id_rpt == 'no_rpt'){
				alert('<?php echo recherche_libelle_page('no_rpt');?>' + ' ...');
			}else{
				var	popup	=	window.open('annuaire.php?val=pop_rpt_view&id_rpt='+id_rpt+'&id_syst='+id_syst,'pop_rpt_view', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=0,width=700, height=500, left=200, top=100')
				popup.document.close();
				popup.focus();
			}
		}
	}
</script>
<style type="text/css">
	<!--
	.treeview {
	height: 90%;
	width: 650px;
	top: 80px;
	left: 200px;
	overflow: auto;
	position: absolute;
	}
	table {
		border: 0px;
		border-collapse:collapse;
		padding:0px;
		margin:auto;
		background-color: transparent;
	}
	td {
		padding:3px;
		margin:0px;
		vertical-align:top;
		background-color: transparent;
	}
	
	tr { 
		padding:0px;
		margin:0px;
		background-color: transparent;
	}
	
	#pos_secteur {
		position:absolute; 
		top: 83px; 
		width: 86px; 
		height: 25px; 
		left: 554px;"
	}
	
	-->
</style>

<br><br><br>
<div id="pos_secteur" class="border_table"> 
        <table>
				<tr> 
						<td nowrap> 
							<b><?php echo recherche_libelle_page('choix_syst'); ?> </b>
						</td>
						<td>
						<select  style="width : 200; height:22px" name="ID_SYSTEME" onChange="recharger(this.value);">
						  <?php foreach ($GLOBALS['all_systemes'] as $i => $systemes){
										echo "<option value='".$systemes['id_systeme']."'";
										if ($systemes['id_systeme'] == $GLOBALS['id_syst']){
											echo " selected";
										}
										echo ">".$systemes['libelle_systeme']."</option>";
									}
							?>
						 </select> 
					</td>
				</tr>
		</table>
                    
</div>

<?php $tab_menu 			= array();

	$req_comp  =   'SELECT DICO_COMPOSANT.ID_COMPOSANT, DICO_COMPOSANT.ID_TYPE_COMPOSANT, DICO_COMPOSANT.LIBELLE_COMPOSANT, 
					DICO_COMPOSANT_SYSTEME.NOM_COMPOSANT_SYSTEME
					FROM DICO_COMPOSANT, DICO_COMPOSANT_SYSTEME
					WHERE DICO_COMPOSANT.ID_COMPOSANT = DICO_COMPOSANT_SYSTEME.ID_COMPOSANT
					AND DICO_COMPOSANT_SYSTEME.ACTIVER =1
					AND DICO_COMPOSANT_SYSTEME.ID_SYSTEME = '.$GLOBALS['id_syst'].'
					ORDER BY DICO_COMPOSANT_SYSTEME.ORDRE;';
    $all_comp = $GLOBALS['conn_dico']->GetAll($req_comp);
	if(is_array($all_comp)){
		foreach( $all_comp as $i_comp => $comp ){
			
			$tab_menu[$i_comp]['comp_name'] 	=  $comp['NOM_COMPOSANT_SYSTEME'];
			
			$req_fonc	= 'SELECT DICO_FONCTIONNALITE.ID_FONCTIONNALITE, DICO_FONCTIONNALITE.ID_TYPE_FONCTIONNALITE, 
							 DICO_FONCTIONNALITE.ID, DICO_FONCTIONNALITE.LIBELLE_FONCTIONNALITE, 
							 DICO_FONCTIONNALITE_SYSTEME.NOM_FONCTIONNALITE_SYSTEME
							 FROM DICO_COMP_FONCTIONNALITE, DICO_FONCTIONNALITE, DICO_FONCTIONNALITE_SYSTEME
							 WHERE DICO_COMP_FONCTIONNALITE.ID_FONCTIONNALITE = DICO_FONCTIONNALITE.ID_FONCTIONNALITE
							 AND DICO_FONCTIONNALITE.ID_FONCTIONNALITE = DICO_FONCTIONNALITE_SYSTEME.ID_FONCTIONNALITE
							 AND DICO_COMP_FONCTIONNALITE.ID_COMPOSANT = '.$comp['ID_COMPOSANT'].' 
							 AND DICO_FONCTIONNALITE_SYSTEME.ID_SYSTEME = '.$GLOBALS['id_syst'].' 
							 AND DICO_FONCTIONNALITE_SYSTEME.ACTIVER = 1;';
			//echo '<br>'.$req_ss_menu;
			$all_fonc = $GLOBALS['conn_dico']->GetAll($req_fonc);
			
			if( is_array($all_fonc) && (count($all_fonc) > 0) ){
				$tab_menu[$i_comp]['tab_fonc'] = array();
				foreach( $all_fonc as $i_fonc => $fonc ){
					$tab_menu[$i_comp]['tab_fonc'][$i_fonc]['fonc_name'] 	=  $fonc['NOM_FONCTIONNALITE_SYSTEME'];					
					if( isset($fonc['ID']) &&(trim($fonc['ID']) <> '') ){
						$tab_menu[$i_comp]['tab_fonc'][$i_fonc]['id_rpt'] = $fonc['ID'];
					}else{
						$tab_menu[$i_comp]['tab_fonc'][$i_fonc]['tab_rub'] = array();
						
						$req_rub_fonc	= ' SELECT DICO_FONC_RUBRIQUE.ID_RUBRIQUE, DICO_FONC_RUBRIQUE.NOM_FONC_RUBRIQUE, 
											DICO_RUBRIQUE.ID
											FROM DICO_FONC_RUBRIQUE, DICO_RUBRIQUE 
											WHERE DICO_FONC_RUBRIQUE.ID_RUBRIQUE = DICO_RUBRIQUE.ID_RUBRIQUE
											AND DICO_FONC_RUBRIQUE.ID_FONCTIONNALITE = '.$fonc['ID_FONCTIONNALITE'].'
											ORDER BY DICO_FONC_RUBRIQUE.ORDRE;';
						$all_rub_fonc = $GLOBALS['conn_dico']->GetAll($req_rub_fonc);
						
						if( is_array($all_rub_fonc) && (count($all_rub_fonc) > 0) ){
							foreach( $all_rub_fonc as $i_rub => $rub ){
								$tab_menu[$i_comp]['tab_fonc'][$i_fonc]['tab_rub'][$i_rub]['rub_name'] = $rub['NOM_FONC_RUBRIQUE'] ;
								if( isset($rub['ID']) && (trim($rub['ID']) <> '') ){
									$tab_menu[$i_comp]['tab_fonc'][$i_fonc]['tab_rub'][$i_rub]['id_rpt'] = $rub['ID'] ;	
								}							
							}
						}
					}
				}
			}
		}
	}
    /*echo '<pre>';
    print_r($tab_ss_menu);
    exit;*/
   
    if( is_array($tab_menu) && (count($tab_menu) > 0) ){

		print('<script language="JavaScript" src="'. $GLOBALS['SISED_URL_JSC'].'treeview.js"></script>'."\n");
		print('<SPAN class="treeview border_table" style="text-align:center">'."\n");
		print("<script>\n");
		print('var menu = new Arborescence(11,2,"'.$GLOBALS['SISED_URL_IMG'] .'/treeview/");'."\n");
		print("menu.cookieName = \"Exemple d'arborescence\";\n");
		print('var racine = menu.addItem("<b>'.recherche_libelle_page('year_book').'</b>","javascript:throw_rpt(\'\','.$GLOBALS['id_syst'].');");'."\n");
		
		foreach($tab_menu as $i_comp => $comp){
			if( is_array( $comp['tab_fonc'] ) && (count($comp['tab_fonc']) > 0) ){
				print('var m_'.$i_comp.' = racine.addItem("'.$comp['comp_name'].'","javascript:throw_rpt(\'\','.$GLOBALS['id_syst'].');");'."\n");
				foreach( $comp['tab_fonc'] as $i_fonc => $fonc){
					if( isset($fonc['id_rpt']) && (trim($fonc['id_rpt']) <> '') ){
						print('var m_'.$i_comp.'_'.$i_fonc.' = m_'.$i_comp.'.addItem("'.$fonc['fonc_name'].'","javascript:throw_rpt('.$fonc['id_rpt'].','.$GLOBALS['id_syst'].');");'."\n");
					}elseif( is_array( $fonc['tab_rub'] ) && (count($fonc['tab_rub']) > 0) ){
						print('var m_'.$i_comp.'_'.$i_fonc.' = m_'.$i_comp.'.addItem("'.$fonc['fonc_name'].'","javascript:throw_rpt(\'\','.$GLOBALS['id_syst'].');");'."\n");
						foreach( $fonc['tab_rub'] as $i_rub => $rub ){
							if( isset($rub['id_rpt']) && (trim($rub['id_rpt']) <> '') ){
								print('var m_'.$i_comp.'_'.$i_fonc.'_'.$i_rub.' = m_'.$i_comp.'_'.$i_fonc.'.addItem("'.$rub['rub_name'].'","javascript:throw_rpt('.$rub['id_rpt'].','.$GLOBALS['id_syst'].');");'."\n");
							}else{
								print('var m_'.$i_comp.'_'.$i_fonc.'_'.$i_rub.' = m_'.$i_comp.'_'.$i_fonc.'.addItem("'.$rub['rub_name'].'","javascript:throw_rpt(\'no_rpt\','.$GLOBALS['id_syst'].');");'."\n");
							}							
						}
					}
					else{
						print('var m_'.$i_comp.'_'.$i_fonc.' = m_'.$i_comp.'.addItem("'.$fonc['fonc_name'].'","javascript:throw_rpt(\'no_rpt\','.$GLOBALS['id_syst'].');");'."\n");
					}
				}
			}else{
				print('var m_'.$i_comp.' = racine.addItem("'.$comp['comp_name'].'","javascript:throw_rpt(\'\','.$GLOBALS['id_syst'].');");'."\n");
			}
		}
		print("menu.loadCookie();\n");
		print("</script></SPAN>\n");  
	}else{
		echo '<br> '.recherche_libelle_page('no_comp').'<br>';
	}
?>
