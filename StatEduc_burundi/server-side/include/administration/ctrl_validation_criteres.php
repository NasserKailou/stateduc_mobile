<?php function get_criteres_where($champ,$table,$array){
		if(count($array)){
			$crit_in = array();
			foreach($array as $i_elem => $elem){
				if(!est_ds_tableau($elem[$champ], $crit_in)){
					$crit_in[] = $elem[$champ] ;
				}
			}
			$where               = ' '.$table.'.'.$champ . ' IN ( '.implode(', ',$crit_in).' ) ';
			return $where ;
		}	
	}
  
  require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';
  use \Curl\Curl; 
  $html_etab_incr = "<table border='1' width='100%'>"; 
  $niveau_ch = "";
  $arbre = null;  
  $liste_etab = array();
  function get_etab_forms($id_teme, $id_etab, $id_camp, $id_filter) {
    $curl = new Curl();
    $curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
    $curl->setHeader('Accept', '*/*');
    $curl->setHeader('Accept-Encoding', 'gzip,deflate');
    $curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
    $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
      
    $curl->success(function($instance) {  		
  		//echo $instance->response;
  	});
  	$curl->error(function($instance) {
  		$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['KO'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$instance->error_code." : ".$instance->error_message);		
  		echo json_encode($rps);
  	});
  	$curl->complete(function($instance) {
  		//echo 'call completed' . "<br/>";
  	});
  	
  	$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector=1&theme='.$id_teme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp.'&filtre='.$id_filter;
  	//echo $urlBase;
  	$result = $curl->get($urlBase);
    $dom = new DOMDocument;
		$dom->loadHTML($result);
		$forms = $dom->getElementsByTagName('form');
		foreach ($forms as $form) {
			if (strpos($form->getAttribute('action'), 'questionnaire.php?theme_frame=') !== FALSE) {
				//$rps = array($GLOBALS['PARAM_WS']['LIB_STATUS']=>$GLOBALS['PARAM_WS']['STATUS_OK'],$GLOBALS['PARAM_WS']['LIB_MESSAGE']=>$GLOBALS['PARAM_WS']['OK'],$GLOBALS['PARAM_WS']['LIB_DATA']=>$dom->saveXML($form));
				//echo json_encode($rps);
				$result = $form;
				break;
			}
		}
    return $result;	
  }         
  $code_periode = -1;
		
	if( count($_POST) > 0 ) {
		$is_set_regs 	= false ;
		$is_set_ctrls 	= true ;
		for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ) {
				if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> '') {
						$is_set_regs = true ;
						break;
				}
		}
		/*if(isset($_POST['MISS_SCH']) and trim($_POST['MISS_SCH']) <> ''){
				$is_set_ctrls = true ;
		}else{
			for( $i = 0 ; $i < $_POST['nb_ctrls_dispo'] ; $i++ ){
					if(isset($_POST['CTRLS_'.$i]) and trim($_POST['CTRLS_'.$i]) <> ''){
							$is_set_ctrls = true ;
							break;
					}
			}
		}  */
		if(!$is_set_regs){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
		}elseif(!$is_set_ctrls){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_ctrls').'"';
		}elseif( ($is_set_regs == true) and ($is_set_ctrls == true) ){
			
			$do_action_post = true;
      
      $requete ='SELECT COUNT(*)
					FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
					WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_POST['id_chaine'];
	    $niveau_ch = $GLOBALS['conn']->GetOne($requete)-1; 
			$arbre = new arbre($_POST['id_chaine']);
			$tab_etabs_run	= array();
			$tab_regs_run 	= array();
			$tab_ctrls_run	= array();
			
			for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
				if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
					$tab_regs_run[]	= $_POST['REGS_'.$i] ;
				}
			}
			
			$arbre = new arbre($_POST['id_chaine']);
				
			foreach($tab_regs_run as $code_regroup){

				$depht	=	$arbre->get_depht_regroup($code_regroup);
				
				//création de la liste des coderegs enfants
				$list_code_reg = $arbre->getchildsid($depht, $code_regroup);

				// On fabrique le "where" de la requête sur ETABLISSEMENT_REGROUPEMENT
				$where = get_criteres_where($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'], $list_code_reg);
				
				$get_code_admin = '';
				if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
					$get_code_admin	= ' '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' as code_admin, ' ;
				}
				
				$get_type_ent_stat = '';
				if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
					$get_type_ent_stat	= ' '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as code_type_ent_stat, ' ;
				} 
        $from_periode = '';   
        $where_periode = '';  
        $code_periode = $_POST[get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'])];
        if (exist_table($GLOBALS['PARAM']['ETABLISSEMENT'].'_USAGE')) { 
          $from_periode = ', '.$GLOBALS['PARAM']['ETABLISSEMENT'].'_USAGE'.' AS P';
          $where_periode = ' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = P.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']; 
          $where_periode .= ' AND P.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = 12';
          $where_periode .= ' AND P.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
          switch($code_periode) {
             case '2': 
                $where_periode .= ' AND P.'.$GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM1_Y_N=1';
                break;
             case '3': 
                $where_periode .= ' AND P.'.$GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM2_Y_N=1';
                break;
             case '4': 
                $where_periode .= ' AND P.'.$GLOBALS['PARAM']['ETABLISSEMENT'].'_TERM3_Y_N=1';
                break;
             default :
                $where_periode .= ' AND P.'.$GLOBALS['PARAM']['ETABLISSEMENT'].'_ANNUAL_Y_N=1';      
          } 
        }        
				$requete    = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'. 
										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.$get_code_admin.$get_type_ent_stat.
										$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup
										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].$from_periode.'   
										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].$where_periode.'
										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
				//echo '<br>'.$requete.'<br>'; //die();
				$liste_etab = array_merge($GLOBALS['conn']->GetAll($requete), $liste_etab); 
				/*if(is_array($liste_etab)){
          $camp1 = $_POST['camp1'];
          $camp2 = $_POST['camp2'];      
					foreach( $liste_etab as $ii => $etab ){
						$form1 = get_etab_forms(401, $etab['CODE_ETAB'], 5, $code_periode);
            $form2 = get_etab_forms(901, $etab['CODE_ETAB'], 12, $code_periode);
            $html_etab_incr .= check_etab_form($etab, $code_periode, $form1, $form2, $arbre, $niveau_ch, $ii+1);
            $p++;
            //if ($p > 1) break; 
					}
				}*/	
		//echo "<pre>";
    //print_r($arbre);
			} 
      //echo "NB : ".count($liste_etab);
		}
		$_SESSION['ctrl_cohr']['tab_etabs_run'] = $tab_etabs_run;
		$_SESSION['ctrl_cohr']['id_chaine'] = $_POST['id_chaine'];   
	}  
  $html_etab_incr .= "</table>";
  function check_etab_form($etab, $id_filter, $form1, $form2, $arbre, $niveau_ch, $idx) {
    $inputs1 = $form1->getElementsByTagName('td');
    $inputs2 = $form2->getElementsByTagName('td');
    //echo "<pre>"; 
    $html = "";
    $i = 0;
    $nbKo = 0;
    foreach ($inputs1 as $input1) {
        $input2 = $inputs2->item($i);
        $htmlInput1 = $form1->ownerDocument->saveHTML($input1);
        $htmlInput2 = $form2->ownerDocument->saveHTML($inputs2->item($i));
        if (strcasecmp($htmlInput1, $htmlInput2) !== 0) {
          $nbKo++;
          $html .= "<td><div class='ctrl_elt'>".str_replace('Â','',get_inner_html($input1))."</div><div>".str_replace('Â','',get_inner_html($input2))."</div></td>";
        }
        $i++;
    } 
    if ($nbKo > 0) { 
      //echo "<pre>";
      //print_r($arbre);
      $hierarchie = $arbre->getparentsid($niveau_ch, $etab['code_regroup']);				
      $hierarchie_regroup = '';
      foreach($hierarchie as $j=>$h) {
      	$hierarchie_regroup .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
      	if($j<(count($hierarchie)-1)) {
      		$hierarchie_regroup .= '<b> / </b>';
      	}
      }
      $htmlFin = "<tr><td>".$idx."</td>";
      $htmlFin .= "<td><b>".htmlentities($etab['nom_etab'])."</b><em>(".$hierarchie_regroup.")</em></td>";
      $htmlFin .= "<td><a href='questionnaire.php?theme=401&code_etab=".$etab['CODE_ETAB']."&type_ent_stat=5&filtre=".$id_filter."' target='theme1'><b>Standard</b></a>&nbsp;";
      $htmlFin .= "&nbsp;&nbsp;&nbsp;<a href='questionnaire.php?theme=901&code_etab=".$etab['CODE_ETAB']."&type_ent_stat=12&filtre=".$id_filter."' target='theme1'><b>Validation</b></a>&nbsp;";
			$htmlFin .= "</td><td> Status : ".$nbKo."/".$i."</td></tr>";
      //$html = "<table><tr>".$html."</tr></table><br/>";
      $html = $htmlFin;
    }
    return $html; 
  }
  
  function get_inner_html($node) {
    $innerHTML = "";
    $children = $node->childNodes;
    foreach($children as $child) {
      $innerHTML .= $child->ownerDocument->saveXML($child);
    }
    return $innerHTML;
  }
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg) {
      var period = jQuery('#<?php echo get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE']); ?>').val();
      var camp1 = 5; //jQuery('#camp1').val();
      var camp2 = 12; //jQuery('#camp2').val();
			location.href= '?val=controle_val&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&camp1='+camp1+'&camp2='+camp2+'&period='+period;
		}

		function manage_check(var1, var2){
				var chaine_eval1 ='document.getElementById("' + var1 + '").checked == 1;';
				if(var1 != 'MISS_SCH'){
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
					document.getElementById("MISS_SCH").checked = 0;
					$("#MISS_SCH").uniform('update');
				}else if(var1 == 'MISS_SCH' && document.getElementById("MISS_SCH").checked == 1){
					var i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").checked = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").checked = 0;
					$("#ALL_CTRLS").uniform('update');
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 1;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 1;
					$("#ALL_CTRLS").uniform('update');
				}else if(var1 == 'MISS_SCH' && document.getElementById("MISS_SCH").checked == 0){
					i = 0 ;
					while ( document.getElementById( var2 + '_' + i ) ){
							var chaine_eval2 = 'document.getElementById("' + var2 + '_' + i + '").disabled = 0;';
							eval(chaine_eval2);
							$("#" + var2 + "_" + i).uniform('update');
							i++;
					}
					document.getElementById("ALL_CTRLS").disabled = 0;
					$("#ALL_CTRLS").uniform('update');
				}
		}

</script>

<?php 
	if(isset($_GET['id_systeme']) && $_GET['id_systeme'] <> ''){
		$_SESSION['secteur'] = $_GET['id_systeme'];
	}
	$GLOBALS['id_systeme'] 	= $_SESSION['secteur'];
		
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
				
				$arb = new arbre($GLOBALS['id_chaine']);
				$GLOBALS['type_regs_chaine'] = $arb->chaine ;

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
				unset($arb);
		} 
		
		function tableau_check( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1, $nowrap='' ){
				$html 	= '';
				if( trim($nowrap) <> 'nowrap' ) {
					$nowrap = '' ;
				} 
				if(is_array($tab)){
						$html .= "\n" . '<TABLE class = \'no_border\' border="1"  width="100%">';
						$i_tr = 0 ;
						while(isset($tab[$i_tr])){
								$html .= "\n\t" . '<tr>';
								$i_td = 1;
								for( $i = $i_tr ; $i < $i_tr + $nb_td ; $i++ ){

									 if(isset($tab[$i])){
												$html .= "\n\t\t" . '<td align="right" '.$nowrap.'>'.substr($tab[$i][$lib], 0, 200).' <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
												if(isset($_POST[$chp_check.'_'.$i]) and trim($_POST[$chp_check.'_'.$i]) <> ''){
													$html .= ' CHECKED';
												}elseif(isset($_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && in_array(trim($tab[$i][$code]),$_SESSION['fixe_reg'][$GLOBALS['type_reg']]) && !count($_POST)){
													$html .= ' CHECKED';
												}elseif(in_array($GLOBALS['type_reg'],$_SESSION['fixe_type_regs']) && $chp_check=='REGS' && !in_array(trim($tab[$i][$code]),(array)($_SESSION['fixe_reg'][$GLOBALS['type_reg']]))){
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
						if( count($tab) > 1 ){
								if($_POST[$chp_all_check]==1) $checked = ' CHECKED';
								else $checked = '';
								//$html .= "\n" . '<tr><td nowrap colspan="' . $nb_td . '" align="center">' . recherche_libelle_page($lib_all_check) . ' <INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'></td></tr>';
							$GLOBALS[$chp_all_check] =	'<INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'>' ;
						}
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
		
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
.no_border {
	border-top-style: none;
	border-right-style: none;
	border-bottom-style: none;
	border-left-style: none;
}
</style>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php if( $do_action_post == true ){ ?>
<script type="text/Javascript">

  function validate_etab_forms(code_etab, nom_etab, code_regroup, code_periode, niveau_ch, id_chaine, idx, nb) {
    var params = 'code_etab='+code_etab+'&nom_etab='+nom_etab+'&code_regroup='+code_regroup+'&code_periode='+code_periode+'&niveau_ch='+niveau_ch+'&id_chaine='+id_chaine+'&idx='+idx;
    $.ajax({type:'get', url: 'server-side/include/administration/ctrl_validation_etab_service.php', data: params, 
  		success: function(response) {
  			if (response.se_statut == 101) {
  				if (response.se_message == 'session_end') {
  					window.document.location = 'index.php';	
  				} else {
  					$.alert(response.se_message, '_');
  				}
  			} else if (response.se_statut == 200) {
  				//console.log(response.se_data);
  				if (response.se_type == 'CtrlEtab') {
  					 jQuery('#valid_result').append(response.se_data);  
             if (response.se_idx == nb) {
                jQuery('#load_etabs_inchr').empty();
             }				
          }
  			}
  		},
  		error: function(XMLHttpRequest, textStatus, errorThrown) {
  			$.alert('ERREUR : ' + textStatus + '-' + errorThrown, '_');      
  		}, 
  		dataType:'json',
  		timeout: 3600000
  	}); 
  }
  
  $(function() {
     var lstEtab = <?php echo json_encode($liste_etab); ?>; 
     if (<?php echo count($liste_etab); ?> == 0) {
       jQuery('#load_etabs_inchr').empty();
     } else {
       for (var i = 0; i < <?php echo count($liste_etab); ?>; i++) {
         etab = lstEtab[i];
         validate_etab_forms(etab.code_etab, etab.nom_etab, etab.code_regroup, <?php echo $code_periode; ?>, <?php echo $niveau_ch; ?>, <?php echo $_POST['id_chaine']; ?>, i+1, lstEtab.length);
       }
     }
  });
</script>
<?php } ?>
<FORM name="Formulaire" id="formulaire"  method="post" action="">

<div>
<?php echo recherche_libelle_page('curr_year');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></b> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php //if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE']) echo recherche_libelle_page('curr_period').' : <b>'.get_libelle_from_array($_SESSION['tab_filtres'], $_SESSION['filtre'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']); ?></b>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_sect');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>

<select name="id_systeme"	onChange="reload_page(this.value,'',''); ">
<?php 
if(is_array($_SESSION['tab_secteur']))
foreach ($_SESSION['tab_secteur'] as $i => $systeme){
	echo "<option value='".$systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]."'";
	if ($systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']){
			echo " selected";
	}
	echo ">".$systeme['LIBELLE']."</option>";
}
?>
</select>

</div>

<div align="center">
    <table border='1' style="overflow: auto;" width="100%">
      <caption>
      <b><?php echo recherche_libelle_page('ConfExt');?></b>
      </caption>
      <tr>
        <td rowspan="2" width="75%"><table border='1' align="center"  width="100%">
            <caption>
            <b><?php echo recherche_libelle_page('tit_atlas');?></b>
            </caption>
          <tr>
              <td width="21%" class="border_table" style="padding:3px">
				  <?php 
					foreach ($_SESSION['tab_secteur'] as $k => $v){
						if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['secteur']){
							echo "<b>".$v['LIBELLE']."</b><br/>";
						}
					} 
					echo "<u>".recherche_libelle_page('chaine')."</u><br/>";
				  ?>
				  <select style="width:100px" name="id_chaine" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>,this.value,'');">
                    <?php if(is_array($GLOBALS['chaines_systeme']))
												foreach ($GLOBALS['chaines_systeme'] as $i => $chaine_systeme){
														echo "<option value='".$chaine_systeme['CODE_TYPE_CH']."'";
														if ($chaine_systeme['CODE_TYPE_CH'] == $GLOBALS['id_chaine']){
																echo " selected";
														}
														echo ">".$chaine_systeme['LIBELLE_TYPE_CH']."</option>";
												}
								?>
                  </select>
                  <hr width="75%"/>
                <u><?php echo recherche_libelle_page('type_reg');?></u> <br />
                  <select style="width:100px" name="type_reg" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>, id_chaine.value, this.value);">
                    <?php foreach ($GLOBALS['type_regs_chaine'] as $i => $type_regs){
											echo "<option value='".$type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."'";
											if ($type_regs[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']){
													echo " selected";
											}
											echo ">".$type_regs[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</option>";
									}
							?>
                  </select>
			</td>
            <td width="79%" ><table  style="border:none" cellspacing="0"  cellpadding="0"  width="100%">
                  <tr>
                    <?php $arb	= new arbre($GLOBALS['id_chaine']);
						foreach($arb->chaine as $i=>$c) {
								if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $GLOBALS['type_reg']) {
										$curdepht = $i;
										break;
								}
						} 
						$arb->type_access='config';
						$entete = $arb->create_entete(0, $curdepht, true); 

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
						//$nb_td_regs =	$GLOBALS['nb_td'];

				if( isset($entete['code_regroup']) and trim($entete['code_regroup']) <> ''){
						$combos_regs = preg_replace('/combo_regroups/', 'Formulaire', preg_replace("/(<form name=\"combo_regroups\">)|(</form>)|(<br />)/", '', $entete['html']));
						$combos_regs = preg_replace('/<br>([[:blank:]]|[[:space:]])*</div>/', '</div>', $combos_regs);
						$combos_regs = str_replace('<select', '<select  style=\'width:100px;\'',$combos_regs); 
						?>
                    <td align="center" valign="middle" width="30%"><div style="overflow:visible; width:100%; ">
                        <table width="100%">
                          <tr>
                            <td align="center"><?php echo $combos_regs ;?> </td>
                          </tr>
                        </table>
                    </div></td>
                    <?php if($nb_td_regs > 1){
																		$nb_td_regs = $nb_td_regs - 1 ;
																}
														}?>
                    <td align="center" valign="middle"  width="100%"><div style="height:195px; max-height:195px; overflow:auto" class="border_table" >
                        <?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', 2 , 'nowrap')); ?>
                        <input type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>" />
                    </div></td>
                  </tr>
                </table>
                <div class="class_table" style="vertical-align:bottom; height:24px;"> &nbsp;<span class="class_table" style="vertical-align:bottom; height:24px; text-align:center"><u><?php echo recherche_libelle_page('all_regs'); ?></u> :<?php echo $GLOBALS['ALL_REGS'];?></span></div></td>
          </tr>
        </table></td>
        <td width="25%"><table border='1' align="center"  width="100%">
            <caption>
            <b><?php //echo recherche_libelle_page('ListRegles');?>  </b>
            </caption>
          <tr>
              <td>
                <div style="height:200px; max-height:200px; overflow:auto" class="border_table" >
                <!--  <select name="camp1" id="camp1">
    								<?php
    									$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
            							$rs_type_entite= $GLOBALS['conn']->GetAll($requete);
            							$nb = count($rs_type_entite);
    									$i = 1;
    									$trouv = false;
                      $curr_camp_1 = 5;
    									if(isset($_GET['camp1'])) { $curr_camp_1 = $_GET['camp1'];}
    									foreach ($rs_type_entite as $k => $v){
    										echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."'";
    										if ((($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] == $curr_camp_1) || ($nb == $i)) && !$trouv){
    											echo " selected";
    											$trouv = true;
    										}
    										echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
    										$i++;
    									}
    								?>
                </select>
              <br/> <br/>
                <select name="camp2" id="camp2">
								<?php
									$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
        							$rs_type_entite= $GLOBALS['conn']->GetAll($requete);
        							$nb = count($rs_type_entite);
									$i = 1;
									$trouv = false;
                  $curr_camp_2 = 12;
									if(isset($_GET['camp2'])) { $curr_camp_2 = $_GET['camp2'];}
									foreach ($rs_type_entite as $k => $v){
										echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."'";
										if ((($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] == $curr_camp_2) || ($nb == $i)) && !$trouv){
											echo " selected";
											$trouv = true;
										}
										echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
										$i++;
									}
								?>
                </select>
                <br/> <br/> -->
                <?php 
                  $tab_periode =  array();
                  $nom_combo_periode = get_champ_extraction($GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE']); 
                  $nom_code_periode = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'];
              		foreach ($_SESSION['tab_periodes'] as $row) { 
                    if ($row[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE']] != 1 ) {		
              			   array_push($tab_periode, $row); 		
                    }
              		}
              		
                  if ($_GET['period'] && $code_periode == -1) { 		
              			$code_periode = $_GET['period'];                		
              		} else if ($code_periode == -1) {
              			$code_periode = $tab_periode[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE']] ;              		
              		}
                  echo '<b>'.recherche_libelle_page('curr_period').'</b>'.' : '.create_combo($tab_periode, $nom_combo_periode, $nom_code_periode, $code_periode, '');
                ?>
                </div>
              </td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><div style="position:relative; vertical-align:baseline; height:22px" >
          
		  <table border='1' align="right">
              <tr>
                <td><input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="19px" height="17px" border="0"  value="Envoyer" class="envoyer" /></td>
                <td class="like_caption"><?php echo recherche_libelle_page('exec');?></td>
              </tr>
            </table>
        </div></td>
      </tr>
    </table>
</div>
</FORM>