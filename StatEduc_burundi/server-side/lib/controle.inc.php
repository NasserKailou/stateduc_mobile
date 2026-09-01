<?php /*
<script language="JavaScript" type="text/javascript">
<!--
		function corriger(champ) {				
				//var chaine_eval = 'document.location.href="test.php?champ_erreur='+champ+'";';//";';
				var chaine_eval = 'document.location.href="javascript:history.go(-1);";';//";';
				//alert(chaine_eval);
				eval(chaine_eval);
				//fermer();	
		}
//-->
</script>*/
?>
<?php 
//Recuperation du type de theme
$req_theme = "SELECT ID FROM DICO_THEME_SYSTEME WHERE ID_THEME_SYSTEME=".$_SESSION['id_theme_systeme'];
$id_theme	= $GLOBALS['conn_dico']->GetOne($req_theme);
$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
$_SESSION['type_theme']	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
//echo $_SESSION['type_theme'];
//Fin Recuperation du type de theme	

function mettre_en_evidence_champ_erreur($tab_champs_err){
		/*print("<script language='JavaScript' type='text/javascript'>\n");
		print("<!--\n");
				print("function Mise_en_Evidence(champ){\n");
						print("var chaine_eval = 'document.form1.'+champ+'.style.border =\"1px solid #ff0000\";';\n");
						print("eval(chaine_eval);\n");
				print("}\n");
	  print("Mise_en_Evidence('".$champ_erreur."');\n");
		print("//-->\n");
		print("</script>\n");
		*/
		print("<script language='JavaScript'>\n");
		print("<!--\n");
		
		print('var tab_chp 	= new Array (');
		
		foreach( $tab_champs_err as $i_err => $champ_erreur ){
			($i_err == 0) ? (print('"'.$champ_erreur.'"')) : (print(',"'.$champ_erreur.'"')) ; 
		}
		print(');' ."\n");
		
		print('$(function() {' ."\n");
		print('for (var i = 0; i < tab_chp.length; i++){' ."\n");
		print('var prefix = \'uniform-\';' ."\n");
		print('if(!document.getElementById(prefix+tab_chp[i])){' ."\n");
			print('prefix = \'\';' ."\n");
		print('}' ."\n");
		print("if (eval('document.getElementById(\"'+prefix+tab_chp[i]+'\")')){\n");
		print("eval('document.getElementById(\"'+prefix+tab_chp[i]+'\").style.border =\"1px inset red\"');\n");
		print("eval('document.getElementById(\"'+prefix+tab_chp[i]+'\").focus()');\n");
		print('}' ."\n");
		print('}' ."\n");
		print('' ."\n");
		print("setInterval(\"flash()\", 700)\n");
		print('});' ."\n");
		//print("var chaine_eval = 'document.form1.".$champ_erreur.".style.border =\"1px solid red\";';\n");
		//print("eval(chaine_eval);\n");
	//	myexample.style.border=='border:1px solid red';
		print("function flash(){\n");
		//print("if (!document.all)\n");
		//print("return\n");
		print('' ."\n");
		print('' ."\n");
		
		print('for (var i = 0; i < tab_chp.length; i++){' ."\n");
			print('var prefix = \'uniform-\';' ."\n");
			print('if(!document.getElementById(prefix+tab_chp[i])){' ."\n");
				print('prefix = \'\';' ."\n");
			print('}' ."\n");
			print("if (eval('document.getElementById(\"'+prefix+tab_chp[i]+'\")')){\n");
				print("if (eval('document.getElementById(\"'+prefix+tab_chp[i]+'\").style.borderColor==\"red\"'))\n");
				print("eval('document.getElementById(\"'+prefix+tab_chp[i]+'\").style.borderColor=\"white\"');\n");
				print("else\n");
				print("eval('document.getElementById(\"'+prefix+tab_chp[i]+'\").style.borderColor=\"red\"');\n");
				print("}\n");
			print('}' ."\n");
		print('}' ."\n");
		
		
		print("//-->\n");
		print("</script>\n");
		print("<script language='javascript' src='client-side/js/js.js'></script>\n");
		

}
function recherche_message_erreur($id_mess,$langue){
		//Recherche du LIBELLE dans la table TRADUCTION
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
												WHERE CODE_NOMENCLATURE=".$id_mess." 
												AND NOM_TABLE='DICO_MESSAGE'
												AND CODE_LANGUE='".$langue."';";
		$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
		//print '<BR>'.$id_zone.'-->'.count($aresult);
		return $aresult[0]['LIBELLE'];
}

function All_Controle($matr, $all_regles, $nom_champ, $concat_tab_code=array(), $ligne="", $type_gril_eff_fix_col=false){	
		//Modif HEBIE
		$_SESSION['type_gril_eff_fix_col'] = $type_gril_eff_fix_col;
		if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']){
			if( (isset($concat_tab_code)) && (count($concat_tab_code) > 0) ){
				foreach( $concat_tab_code as $i_val => $val ){
					$concat_tab_code[$i_val] = '_' . $val ;
				}
			}else{
				$concat_tab_code[0] = '' ;
			}
		}else{
			$_SESSION['tab_alert_code'] = array();
			if( (isset($concat_tab_code)) && (count($concat_tab_code) > 0) ){
				$cpt = 0;
				foreach( $concat_tab_code as $i_val => $val ){
					$concat_tab_code[$i_val] = '_' . $val.'_'.$ligne ;
					if($cpt==0) $_SESSION['tab_alert_code'][0] = '_'.$val.'_'.$ligne;
					$cpt++;
				}
			}else{
				$concat_tab_code[0] = '_'.$ligne ;
				$_SESSION['tab_alert_code'][0] = '_'.$ligne;
			}
		}
		//echo '<pre>';
		//print_r($tab_alert_code);
		//Fin Modif HEBIE
		foreach($all_regles as $type_verif){
				if($type_verif['type_donnees']){
						//// 
						$cas_verif = $type_verif['type_donnees'];
						switch($cas_verif){
								case 'int' :{
										$mess_err = recherche_message_erreur('1',$_SESSION['langue']);
										$erreur = controle_type_donnees($matr, $nom_champ, $cas_verif, $mess_err, $concat_tab_code);
										if(trim($erreur) <> ''){
											$tab_err 			= array();
											$tab_err['err']		= true ;
											$tab_err['chp'][]	= $erreur ;
											return $tab_err;
										} 
								break;
								}
								case 'date' :{
										$mess_err = recherche_message_erreur('2',$_SESSION['langue']);
										$format_date = 'dd/mm/yyyy';
										if($type_verif['format_donnees']<>''){
											$format_date = $type_verif['format_donnees'];
										}
										$erreur = controle_type_donnees($matr, $nom_champ, $cas_verif, $mess_err, $concat_tab_code, $format_date);
										if(trim($erreur) <> ''){
											$tab_err 			= array();
											$tab_err['err']		= true ;
											$tab_err['chp'][]	= $erreur ;
											return $tab_err;
										}
								break;
								}
								case 'decimal' :{
										$mess_err = recherche_message_erreur('7',$_SESSION['langue']);
										$erreur = controle_type_donnees($matr, $nom_champ, $cas_verif, $mess_err, $concat_tab_code);
										if(trim($erreur) <> ''){
											$tab_err 			= array();
											$tab_err['err']		= true ;
											$tab_err['chp'][]	= $erreur ;
											return $tab_err;
										}
								break;
								}
						}// fin switch des cas de vérifs
				}
				if($type_verif['taille_donnees']){
						//// 
						$taille = $type_verif['taille_donnees'];
						$mess_err = recherche_message_erreur('3',$_SESSION['langue']) .' ('.$taille.')';
						$erreur = controle_taille_donnees($matr, $nom_champ, $taille, $mess_err, $concat_tab_code);
						if(trim($erreur) <> ''){
							$tab_err 			= array();
							$tab_err['err']		= true ;
							$tab_err['chp'][]	= $erreur ;
							return $tab_err;
						}
				}
				if(is_array($type_verif['controle_unicite']) && count($type_verif['controle_unicite'])){
						//// 
						$params_champ = $type_verif['controle_unicite'];
						$mess_err = recherche_message_erreur('8', $_SESSION['langue']) ;
						$erreur = controle_unicite($matr, $nom_champ, $params_champ, $mess_err, $concat_tab_code);
						if(trim($erreur) <> ''){
							$tab_err 			= array();
							$tab_err['err']		= true ;
							$tab_err['chp'][]	= $erreur ;
							return $tab_err;
						}
				}
				if($type_verif['format_donnees'] && $type_verif['type_donnees'] <> 'date'){
					//// 
					$expr_reg = $type_verif['format_donnees'];
					$mess_err = recherche_message_erreur('4',$_SESSION['langue']).' ('.$expr_reg.') ';
					$erreur = controle_format_donnees($matr, $nom_champ, $expr_reg, $mess_err, $concat_tab_code);
					if(trim($erreur) <> ''){
						$tab_err 			= array();
						$tab_err['err']		= true ;
						$tab_err['chp'][]	= $erreur ;
						return $tab_err;
					}
				}
				if($type_verif['intervalle_valeurs']){
						//// 
						$min = $type_verif['valeur_minimale'];
						$max = $type_verif['valeur_maximale'];
						$mess_err = recherche_message_erreur('5',$_SESSION['langue']);
						$erreur = controle_intervalle_valeurs($matr, $nom_champ, $min, $max, $mess_err, $concat_tab_code);
						if(trim($erreur) <> ''){
							$tab_err 			= array();
							$tab_err['err']		= true ;
							$tab_err['chp'][]	= $erreur ;
							return $tab_err;
						}

				}
				if($type_verif['controle_parution']){
						//// 
						$cas_verif = $type_verif['controle_parution'];
				}
				if($type_verif['controle_obligation']){
					//// 
					//$cas_verif = $type_verif['controle_obligation'];
					$mess_err = recherche_message_erreur('6',$_SESSION['langue']);
					$erreur = controle_obligation($matr, $nom_champ, $mess_err, $concat_tab_code);
					if($erreur){
						$tab_err 			= array();
						$tab_err['err']		= true ;
						
						if( $GLOBALS['ctrl_loc_etab'] == 1 ){
							foreach($GLOBALS['tab_chaines'] as $iLoc => $chaine){
								$tab_err['chp'][]	= 'LOC_REG_' . $iLoc ;
							}
						}else{
							foreach( $concat_tab_code as $i_val => $val ){
								$tab_err['chp'][]= $nom_champ . $val ;
							}
						}
						return $tab_err;
					}
				}
				if($type_verif['vals_enum']){
						//// 
						//$cas_verif = $type_verif['controle_obligation'];
						//die($type_verif['vals_enum']);	
						$list_vals = $type_verif['vals_enum'];
						$mess_err = recherche_message_erreur('5',$_SESSION['langue']);
						$erreur = controle_vals_enum($matr, $nom_champ, $mess_err, $list_vals, $concat_tab_code);
						if(trim($erreur) <> ''){
							$tab_err 			= array();
							$tab_err['err']		= true ;
							$tab_err['chp'][]	= $erreur ;
							return $tab_err;
						}

				}
				if($type_verif['controle_integrite_ref']){
						//// 
						$cas_verif = $type_verif['controle_integrite_ref'];
				}
				if($type_verif['controle_parution']){
						//// 
						$cas_verif = $type_verif['controle_parution'];
				}
				if($type_verif['controle_edition']){
						//// 
						$cas_verif = $type_verif['controle_edition'];
				}
		}
}


function Avertissement($nom_champ, $mess_err){
		print("<script language=\"JavaScript\" type=\"text/javascript\">\n");
		print("<!--\n");
				//print("popup=window.open('../avertissement.php?champ=".$nom_champ."&mess_err=".$mess_err."','', 'toolbar=no,location=no,directories=no,menubar=no, scrollbars=yes,status=no,resizable=0,width=500, height=200')\n");
				print("alert(\"$mess_err : ($nom_champ)\");\n");
				//print("corriger('$nom_champ');\n");
		print("//-->\n");
		print("</script>\n");
		
		//return
		//exit;
}
function is_valid_date($value, $format){ 
    if(strlen($value) >= 6 && strlen($format) == 10){ 
		// find separator. Remove all other characters from $format 
        $separator_only = str_replace(array('m','d','y'),'',$format); 
        $separator = $separator_only[0]; // separator is first character 
		if($separator == '-'){
			$separator = '/';
			$format = str_replace('-','/',$format);
			$value = str_replace('-','/',$value);
		}
		if($separator && strlen($separator_only) == 2){ 
			// make regex 
            $regexp = str_replace('mm', '(0?[1-9]|1[0-2])', $format); 
            $regexp = str_replace('dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp); 
            $regexp = str_replace('yyyy', '(19|20)?[0-9][0-9]', $regexp); 
            $regexp = str_replace($separator, "\\" . $separator, $regexp); 
            if($regexp != $value && preg_match('/'.$regexp.'\z/', $value)){ 
                // check date
				$tab_date = explode($separator,$format);
				foreach($tab_date as $i=>$elt_date){
					if($elt_date=='dd'){
						$pos_day = $i;
					}elseif($elt_date=='mm'){
						$pos_month = $i;
					}elseif($elt_date=='yyyy'){
						$pos_year = $i;
					}
				}
                $arr=explode($separator,$value); 
                $day=$arr[$pos_day]; 
                $month=$arr[$pos_month]; 
                $year=$arr[$pos_year]; 
                if(@checkdate($month, $day, $year)) 
					return true; 
            } 
        } 
    }elseif(strlen($value) >= 4 && strlen($format) == 7){//le cas mm/yyyy
		// find separator. Remove all other characters from $format 
        $separator_only = str_replace(array('m','y'),'',$format); 
        $separator = $separator_only[0]; // separator is first character 
		if($separator == '-'){
			$separator = '/';
			$format = str_replace('-','/',$format);
			$value = str_replace('-','/',$value);
		}
		if($separator && strlen($separator_only) == 1){ 
			// make regex 
            $regexp = str_replace('mm', '(0?[1-9]|1[0-2])', $format); 
            $regexp = str_replace('yyyy', '(19|20)?[0-9][0-9]', $regexp); 
            $regexp = str_replace($separator, "\\" . $separator, $regexp); 
            if($regexp != $value && preg_match('/'.$regexp.'\z/', $value)){ 
                // check date
				$tab_date = explode($separator,$format);
				foreach($tab_date as $i=>$elt_date){
					if($elt_date=='mm'){
						$pos_month = $i;
					}elseif($elt_date=='yyyy'){
						$pos_year = $i;
					}
				}
                $arr=explode($separator,$value); 
                $month=$arr[$pos_month]; 
                $year=$arr[$pos_year]; 
                if(@checkdate($month, 1, $year)) 
					return true; 
            } 
        } 
    }
    return false; 
} 
function controle_type_donnees($matr, $nom_champ, $type, $mess_err, $concat_tab_code=array(), $format=''){
	foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);
		if(trim($val_champ ) <> ''){ // fin si le champ a une valeur 
				switch($type){
						case 'int':{
								if (!preg_match ("/^(0|([1-9][0-9]*))$/", $val_champ)){
										// INCOHERENCE
										// echo"***$mess_err***";
										if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
										else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
										return ($nom_champ . $val);
								}
								break;
						}
						case 'decimal':{
								if (!preg_match ("/^((0|([1-9][0-9]*))([.]([0-9]*))?)$/", $val_champ)){
										// INCOHERENCE
										// echo"***$mess_err***";
										if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
										else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
										return ($nom_champ . $val);
								}
								break;
						}
						case 'date':{
								//if ( (!ereg ("^([0-9]{2}/[0-9]{2}/(([0-9]{2})|([1-9][0-9]{3})))$", $val_champ))){
								/*if ( // mois <> 02
                                    !ereg ("^(((0[1-9])|([1-2][0-9])|(3[0-1]))(-|/)((0(1|[3-9]))|(1[0-2]))(-|/)(([0-9]{2})|([1-9][0-9]{3})))$", $val_champ) and
                                    !ereg ("^(((0(1|[3-9]))|(1[0-2]))(-|/)((0[1-9])|([1-2][0-9])|(3[0-1]))(-|/)(([0-9]{2})|([1-9][0-9]{3})))$", $val_champ) and
                                    !ereg ("^(((([0-9]{2})|([1-9][0-9]{3}))(-|/)((0(1|[3-9]))|(1[0-2]))(-|/)((0[1-9])|([1-2][0-9])|(3[0-1]))))$", $val_champ) and
                                    // mois == 02 bissex
                                    !ereg ("^(((0[1-9])|([1-2][0-9]))(-|/)(02)(-|/)((([1-9][0-9])*(((0|2|4|8)(0|4|8))|((1|3|5|7|9)(2|6))))))$", $val_champ) and
                                    !ereg ("^((02)(-|/)((0[1-9])|([1-2][0-9]))(-|/)((([1-9][0-9])*(((0|2|4|8)(0|4|8))|((1|3|5|7|9)(2|6))))))$", $val_champ) and
                                    !ereg ("^((((([1-9][0-9])*(((0|2|4|8)(0|4|8))|((1|3|5|7|9)(2|6)))))(-|/)(02)(-|/)((0[1-9])|([1-2][0-9]))))$", $val_champ)and
                                    // mois == 02 non bissex
                                    !ereg ("^(((0[1-9])|(1[0-9])|(2[0-8]))(-|/)(02)(-|/)((([1-9][0-9])*(((0|2|4|8)(1|2|3|5|6|7|9))|((1|3|5|7|9)(0|1|3|4|5|7|8|9))))))$", $val_champ) and
                                    !ereg ("^((02)(-|/)((0[1-9])|(1[0-9])|(2[0-8]))(-|/)((([1-9][0-9])*(((0|2|4|8)(1|2|3|5|6|7|9))|((1|3|5|7|9)(0|1|3|4|5|7|8|9))))))$", $val_champ) and
                                    !ereg ("^((((([1-9][0-9])*(((0|2|4|8)(1|2|3|5|6|7|9))|((1|3|5|7|9)(0|1|3|4|5|7|8|9)))))(-|/)(02)(-|/)((0[1-9])|(1[0-9])|(2[0-8]))))$", $val_champ)
                                    
                                ){
										 // INCOHERENCE
										 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
										else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
										 return ($nom_champ . $val);
								}*/
								if(!is_valid_date($val_champ, $format)){
									// INCOHERENCE
									if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
									else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
									 return ($nom_champ . $val);
								}
								break;
						}
				} // fin switch($type)
		}// fin si le champ a une valeur 
	}
}// fin function controle_type_donnees($val,$type)

function controle_taille_donnees($matr, $nom_champ, $taille, $mess_err, $concat_tab_code=array()){
	foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);	
        if(trim($val_champ ) <> ''){
            if ( strlen($val_champ) <> $taille ){
				 // INCOHERENCE
				 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
				 else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
				 return ($nom_champ . $val);
            }
        }
	}
}// fin controle_taille_donnees($matr, $nom_champ, $taille, $mess_err)


function controle_unicite($matr, $nom_champ, $params_champ, $mess_err, $concat_tab_code=array()){
	foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);	
        if(trim($val_champ ) <> ''){
			$plus_crit =  '';
			if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ETABLISSEMENT'], $params_champ['table_mere'])  ){
				$plus_crit .= ' AND '  .$GLOBALS['PARAM']['CODE_ETABLISSEMENT'] . ' <> ' . $_SESSION['code_etab'];
			}
			if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'], $params_champ['table_mere'])  ){
				$plus_crit .= ' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' <> ' . $_SESSION['annee'];
			}
			(trim($params_champ['type'])=='int') ? ( $val_crit = $val_champ ) : ($val_crit = $GLOBALS['conn']->qstr($val_champ)) ;
            $req_exist =	'SELECT DISTINCT ' . $params_champ['champ'] . ' FROM ' . $params_champ['table_mere'] . ' WHERE ' . $params_champ['champ'] . ' = ' . $val_crit . $plus_crit;
			$exists 	= $GLOBALS['conn']->GetAll($req_exist);
			if( is_array($exists) and (count($exists) > 0) ){
				 // INCOHERENCE
				 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
				 else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
				 return ($nom_champ . $val);
            }
        }
	}
}// fin controle_unicite($matr, $nom_champ, $taille, $mess_err)

function controle_format_donnees($matr, $nom_champ, $expr_reg, $mess_err, $concat_tab_code=array()){
	foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);
		if(trim($val_champ ) <> ''){ // fin si le champ a une valeur 
			if ( (!preg_match ('/'.$expr_reg.'/', $val_champ))){
				 // INCOHERENCE
				 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
				 else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
				 return ($nom_champ . $val);
			}
		}// fin si le champ a une valeur 
	}
}// fin function controle_format_donnees($val,$type)

function controle_intervalle_valeurs($matr, $nom_champ, $min, $max, $mess_err, $concat_tab_code=array()){
    foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);
		if(trim($val_champ ) <> ''){
			if ( ($val_champ < $min) or ($val_champ > $max) ){
					 // INCOHERENCE
					 if( !$min or (trim($min)=='') ) $min = '0';
					 $mess_err.=" [$min - $max] ";
					 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
					 else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
					 return ($nom_champ . $val);
			}
		}
	}
}// fin controle_intervalle_valeurs($matr, $nom_champ, $min, $max, $mess_err)

function est_ds_tableau($elem,$tab){
	if(is_array($tab)){
		foreach($tab as $elements){
			if( $elements == $elem ){
				return true;
			}	
		}	
	}	
}
	
function controle_vals_enum($matr, $nom_champ, $mess_err, $list_vals, $concat_tab_code=array()){
	$tab_vals = explode( ',' , $list_vals );
    foreach( $concat_tab_code as $i_val => $val ){
		$val_champ = trim($matr[$nom_champ . $val]);
		if(trim($val_champ ) <> ''){
			if ( !est_ds_tableau($val_champ, $tab_vals) ){
					 // INCOHERENCE
					 $mess_err.=' : { '.str_replace(',','; ',$list_vals).' } ';
					 if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
					 else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
					 return ($nom_champ . $val);
			}
		}
	}
}// fin controle_intervalle_valeurs($matr, $nom_champ, $min, $max, $mess_err)

function controle_obligation($matr, $nom_champ, $mess_err, $concat_tab_code=array()){
		$val_champ = trim($matr[$nom_champ]);
		$erreur = 1;
		/*if($GLOBALS['ctrl_li_ch'] == 1){
				foreach( $GLOBALS['tab_val_li_ch'] as $val ){
						$val_champ = trim($matr[$nom_champ.'_'.$val]);
						if( trim($val_champ) <> '' ){
								$erreur = 0;
								break;
						}
				}
				if( $erreur == 1 ){
						if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
						else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
				 		return 1;
				}														
		}*/
		if( $GLOBALS['ctrl_loc_etab'] == 1 ){
				$erreur = 1;
				foreach($GLOBALS['tab_chaines'] as $iLoc => $chaine){
						$v	=	'LOC_REG_'.$iLoc;
						if( isset($matr[$v]) and trim($matr[$v]) <>'' ){
								$erreur = 0;
								break;
						}
				}
				if( $erreur == 1 ){
						Avertissement('Localisation', $mess_err);
				 		return 1;
				}	
				$GLOBALS['ctrl_loc_etab'] = 0 ;
		}
		else{
				$val_champ = trim($matr[$nom_champ]);
					
				if( (isset($val_champ)) && (!ctype_space($val_champ))  && (trim($val_champ) <> '')  ){
						$erreur = 0;
				}else{
					foreach( $concat_tab_code as $i_val => $val ){
							$val_champ = trim($matr[$nom_champ.$val]);
							//Modif Hebie pour rendre obligatoire toutes les zones d'une structure matricielle
							if( (isset($val_champ)) && (!ctype_space($val_champ))  && (trim($val_champ) <> '')  ){
									$erreur = 0;
									break;
							}
							/*if( !(isset($val_champ)) || (ctype_space($val_champ))  || (trim($val_champ) == '')  ){
									$erreur = 1;
									break;
							}elseif( (isset($val_champ)) && (!ctype_space($val_champ))  && (trim($val_champ) <> '')  ){
								$erreur = 0;
							}*/
							//Fin modif Hebie pour rendre obligatoire toutes les zones d'une structure matricielle
					}
				}
				
				if( $erreur == 1 ){
						if($_SESSION['type_theme']<>4 || !$_SESSION['type_gril_eff_fix_col']) Avertissement($nom_champ, $mess_err);
						else Avertissement($nom_champ.$_SESSION['tab_alert_code'][0], $mess_err);
				 		return 1;
				}														
		}
				
}// fin controle_obligation($matr, $nom_champ, $mess_err)



?>
