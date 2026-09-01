<?php 

require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;
$curl = new Curl();
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

  try {
    require_once "Mail.php";
  } catch (Exception $e) {
  
  }
  
  function get_criteres_where($champ,$table,$array){
		if(count($array)){
			$crit_in = array();
			foreach($array as $i_elem => $elem){
				if(!est_ds_tableau($elem[$champ], $crit_in)){
					$crit_in[] = $elem[$champ] ;
				}
			}
			$where = ' '.$table.'.'.$champ . ' IN ( '.implode(', ',$crit_in).' ) ';
			return $where ;
		}	
	}
  $id_camp = 1;
  if (isset($_GET['id_camp']) && $_GET['id_camp'] != '') {
  		$id_camp = $_GET['id_camp'];
  }
  $id_period = 1;
  if (isset($_GET['id_periode']) && $_GET['id_periode'] != '') {
  		$id_period = $_GET['id_periode'];
  }  
  $id_camp_c = 1;
  if (isset($_GET['id_camp_c']) && $_GET['id_camp_c'] != '') {
  		$id_camp_c = $_GET['id_camp_c'];
  }             
  $val_camp_themes_tmp = get_camp_themes($_SESSION['secteur'], $id_camp, 'fr');
  $val_camp_themes = array();
  foreach($val_camp_themes_tmp as $l=>$camp_theme) {  
      $val_camp_themes[$camp_theme['id']] = $camp_theme;
  }   
	$final_list_user_mail = array();    
                     
  function sendMail($destinataire, $sujet, $msg, $user, $val_camp_themes, $id_period, $id_camp_c) {
    
    $host = $GLOBALS['PARAM']['MAIL_HOST'];
    if ($host == "") {
      return "No HOST value";
    }  
    $from = $GLOBALS['PARAM']['MAIL_FROM'];
    $port = $GLOBALS['PARAM']['MAIL_PORT'];
    $username = $GLOBALS['PARAM']['MAIL_USERNAME']; 
    $password = $GLOBALS['PARAM']['MAIL_PASSWORD'];
    
    //$mail = 'weaponsb@mail.fr'; // Déclaration de l'adresse de destination.
    if (!preg_match("#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#", $mail)) // On filtre les serveurs qui rencontrent des bogues.
    {
    	$passage_ligne = "\r\n";
    }
    else
    {
    	$passage_ligne = "\n";
    }
    //=====Déclaration des messages au format texte et au format HTML.
    $message_txt = $msg.$passage_ligne;
    $list_schools = ""; 
    foreach($user['miss_schools'] as $i => $school) {
      $list_schools.= ($i+1)." : ".$school['nom']." (".$school['code'].")".$passage_ligne;
      if (isset($school['miss_themes'])) {
         foreach($school['miss_themes'] as $j => $theme) {
             $list_schools.= "   - ".$theme['title'].$passage_ligne;
         }
      } else {
          $list_schools.= "   - All".$passage_ligne;
      }
      $list_schools.= $passage_ligne;
    }
    foreach($user['ok_schools'] as $i => $school) {
      $list_schools.= ($i+1)." : ".$school['nom']." (".$school['code'].")".$passage_ligne;
      $list_schools.= $passage_ligne;
    }
	$collect_users = array();
    foreach($user['val_schools'] as $i => $school) {
	  $collect_users = array_merge(get_collect_users($school['id'], $_SESSION['annee'], $id_period, $id_camp_c), $collect_users);
      $list_schools.= ($i+1)." : ".$school['nom']." (".$school['code'].")".$passage_ligne;
      if (isset($school['val_themes'])) {
         foreach($school['val_themes'] as $j => $theme) {
             $list_schools.= "   - ".$val_camp_themes[$theme['title']]['title']." (".$theme['statut'].")";
			 $list_schools.= ($theme['com'] != '')?" : ".$theme['com'].$passage_ligne:$passage_ligne;
         }
      } else {
          $list_schools.= "   - Tous".$passage_ligne;
      }
      $list_schools.= $passage_ligne;
    }
    $message_txt = str_replace('[list_schools]', $list_schools, $message_txt);
    //echo '<textarea name="mail_message" tabindex="0" dir="ltr" style="margin: 4px; width: 98%; height: 170px;">'.$message_txt.'</textarea>';
    //$message_html = "<html><head></head><body><b>Salut à tous</b>, voici un e-mail envoyé par un <i>script PHP</i>.</body></html>";
    //==========
     //echo "<pre>"; print_r($collect_users);   return;        
     
    //=====Création de la boundary
    $boundary = "-----=".md5(rand());
    //==========
          
    //=====Création du header de l'e-mail.
    /*$header = "From: \"StatEduc2\"<stateduc2@mail.kn>".$passage_ligne;
    $header.= "Reply-to: \"StatEduc2\"<stateduc2@mail.kn>".$passage_ligne;
    $header.= "MIME-Version: 1.0".$passage_ligne;
    $header.= "Content-Type: multipart/alternative;".$passage_ligne." boundary=\"$boundary\"".$passage_ligne; */
    
    $headers = array ('From' => $from,   'To' => $destinataire,   'Subject' => $sujet);
    //==========
     
    //=====Création du message.
    //$message = $passage_ligne."--".$boundary.$passage_ligne; 
    $message = "";
    //=====Ajout du message au format texte.
    //$message.= "Content-Type: text/plain; charset=\"ISO-8859-1\"".$passage_ligne;
    //$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
    $message.= $passage_ligne.$message_txt.$passage_ligne;
    //==========
    //$message.= $passage_ligne."--".$boundary.$passage_ligne;
    //=====Ajout du message au format HTML
    //$message.= "Content-Type: text/html; charset=\"ISO-8859-1\"".$passage_ligne;
    //$message.= "Content-Transfer-Encoding: 8bit".$passage_ligne;
    //$message.= $passage_ligne.$message_html.$passage_ligne;
    //==========
    //$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
    //$message.= $passage_ligne."--".$boundary."--".$passage_ligne;
    //==========
    //echo $mail."<br/><br/>".$message; 
    //=====Envoi de l'e-mail.
    
    $smtp = Mail::factory('smtp',   array ('host' => $host,  'port' => $port,   'auth' => true,     'username' => $username,     'password' => $password));
	if (count($collect_users) > 0) {
		$emails = array();
		foreach($collect_users as $collect_user) {
			if (!in_array($collect_user['EMAIL_USER'], $emails)) {
				$mail = $smtp->send($collect_user['EMAIL_USER'], $headers, $message);  
				$emails[] = $collect_user['EMAIL_USER'];
			}
		}
	} else {
    	$mail = $smtp->send($destinataire, $headers, $message); 
	} 
    if (PEAR::isError($mail)) {   
      return $mail->getMessage();  
    } else {   
      return 1;  
    } 
    /*if (mail($destinataire,$sujet,$message,$header)) {           
      echo "Envoyé";
    } else {
      echo "non envoyé";
    }*/
    //==========
  }
  	
	function sendSms($phone_number, $user, $id_period, $id_camp) use ($curl) {
		
		$curl->success(function($instance) {
				
		});
		$curl->error(function($instance) {
					
		});
		$curl->complete(function($instance) {
			//echo 'call completed' . "<br/>";
		});	
		$camp_name = "";
		foreach ($GLOBALS['campagnes'] as $i => $campagnes) {
			if ($campagnes['id_campagne'] == $id_camp) {
				 $camp_name = $campagnes['libelle_campagne'];			
			}
		}
		$msg = "M./Mme ".$user['NOM_LONG_USER'].", un mail vous a été envoyé a propos des données collectées pour ".$camp_name;
		$msg = encodeURIComponent($msg);
		
		$urlSmsService = $GLOBALS['SMS_GATEWAY_URL']."?number=". $phone_number. "&message=". $msg;
		
		$curl->get($urlSmsService);
	}
  
	$mail_object = "";
  	$mail_message = "[list_schools]";	  
	$is_set_regs 	= false ;
	if( count($_POST) > 0 ){
		$is_set_ctrls 	= false ;
		for( $i = 0 ; $i < $_POST['nb_regs_dispo'] ; $i++ ){
				if(isset($_POST['REGS_'.$i]) and trim($_POST['REGS_'.$i]) <> ''){
						$is_set_regs = true ;
						break;
				}
		}
		
		if(!$is_set_regs){
			$GLOBALS['msg_global'] = '"'.recherche_libelle_page('no_regs').'"';
		}
    $mail_object = $_POST['mail_object'];  
    $mail_message = $_POST['mail_message'];  
    if (isset($_POST['sendmailButton'])) {
      $final_list_user_post = $_POST['final_list_user'];
      $final_list_user_tab = unserialize( stripslashes( htmlspecialchars_decode($final_list_user_post)));
      foreach($final_list_user_tab as $i=>$user) {
          if (isset($_POST['USERS_'.$i])) {
            $result = sendMail($user['EMAIL_USER'], $mail_object, $mail_message, $user, $val_camp_themes, $id_period, $id_camp_c);
            if ($result == 1) {  //echo "dddd";
               $final_list_user_mail[$user['CODE_USER']] = 1;
			   sendSms();
            }  else {    
               $final_list_user_mail[$user['CODE_USER']] = $result;
            }
          }
      }
      //echo $final_list_user_post; 
      //print_r($final_list_user_tab);
    }
  }
?>
<script type="text/Javascript">

		function reload_page(syst, ch, type_reg, id_camp, id_periode, id_trait, id_camp_c) {
			location.href= '?val=feedbacks&id_systeme='+syst+'&id_chaine='+ch+'&type_reg='+type_reg+'&id_camp='+id_camp+'&id_periode='+id_periode+'&id_trait='+id_trait+'&id_camp_c='+id_camp_c;
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

  function get_school_validation_theme($id_etab, $id_year, $id_period, $statut) {
      $requete = "SELECT THV.".$GLOBALS['PARAM']['VALIDATION_ID_THEME']." AS title, TYV.".$GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_VALIDATION']." AS statut,  THV.".$GLOBALS['PARAM']['VALIDATION_COMMENTAIRES']." AS com 
    				FROM ".$GLOBALS['PARAM']['VALIDATION_THEME']." THV, ".$GLOBALS['PARAM']['TYPE_VALIDATION']." TYV
    				WHERE THV.".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_VALIDATION']." = TYV.".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_VALIDATION']."
            AND (THV.".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$id_etab.")  
            AND (THV.".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_VALIDATION']." IN (".$statut."))  
            AND (THV.".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_ANNEE']."=".$id_year.") 
            AND (THV.".$GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['TYPE_PERIODE']."=".$id_period.")
            ORDER BY THV.".$GLOBALS['PARAM']['VALIDATION_ID_THEME'].";";
                                               
    	 $list_theme = $GLOBALS['conn']->GetAll($requete);
       
       return  $list_theme;
  }
/*
  function get_camp_themes($id_sys, $id_camp, $code_lang) {
       $requete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME AS id, DICO_THEME_SYSTEME.ID AS id_theme, DICO_TRADUCTION.LIBELLE AS title, DICO_THEME_SYSTEME.APPARTENANCE AS idcamp, DICO_THEME_SYSTEME.ID_SYSTEME AS idsys, DICO_THEME_SYSTEME.PRECEDENT AS pre
    				FROM DICO_TRADUCTION INNER JOIN DICO_THEME_SYSTEME ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_THEME_SYSTEME.ID_THEME_SYSTEME
    				WHERE (((DICO_THEME_SYSTEME.APPARTENANCE)=".$id_camp.") AND ((DICO_THEME_SYSTEME.ID_SYSTEME)=".$id_sys.") AND ((DICO_TRADUCTION.NOM_TABLE)='DICO_THEME_LIB_MENU') AND ((DICO_TRADUCTION.CODE_LANGUE)='".$code_lang."')) AND (DICO_THEME_SYSTEME.FRAME <> '');";
       
    	 $list_theme = $GLOBALS['conn_dico']->GetAll($requete);
       
       return  $list_theme;
  }
  */
  function get_etab_themes_log($id_etab, $id_camp, $id_year, $id_period) {
      $requete = "SELECT DISTINCT ID_THEME_SYSTEME as id_theme 
                FROM DATA_SAVING_LOGS 
                WHERE CODE_ECOLE=".$id_etab." AND CODE_CAMPAGNE=".$id_camp." AND CODE_ANNEE=".$id_year." AND CODE_PERIODE=".$id_period." AND STATUT_OPERATION='OKSAVE';";
      $list_theme =  $GLOBALS['conn_dico']->GetAll($requete);
     
      return $list_theme;
  }

  function get_etab_log($id_camp, $id_year, $id_period) {
     $requete = "SELECT DISTINCT CODE_ECOLE as code_etab 
                FROM DATA_SAVING_LOGS 
                WHERE CODE_CAMPAGNE=".$id_camp." AND CODE_ANNEE=".$id_year." AND CODE_PERIODE=".$id_period.";";
     $list_etab =  $GLOBALS['conn_dico']->GetAll($requete);
     
     return $list_etab;          
  }
  
  function get_user_camp($systeme_id, $id_camp, $id_year, $id_period) {
     
     $requete = "SELECT DISTINCT AU.CODE_USER, AU.NOM_LONG_USER, AU.NOM_LONG_USER & ' (' & AU.NOM_USER & ') '   AS LIB_USER, AU.EMAIL_USER, AU.TEL_USER   
  				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
  				WHERE DFR.ID_USER=AU.CODE_USER AND DFR.ID_SYSTEME=".$systeme_id." AND DFR.ID_CAMPAGNE=".$id_camp." AND DFR.ID_ANNEE=".$id_year.
          " AND DFR.ID_PERIODE=".$id_period.";";
       
     $users = $GLOBALS['conn_dico']->GetAll($requete);  
     
     return $users;   
  }
  
  function get_collect_users($id_school, $id_year, $id_period, $id_camp) {
  	$requete = "SELECT DISTINCT DSL.CODE_USER as user_login, AU.EMAIL_USER 
                FROM DATA_SAVING_LOGS DSL, ADMIN_USERS AU
                WHERE DSL.CODE_USER=AU.NOM_USER AND DSL.CODE_ECOLE=".$id_school." AND DSL.CODE_ANNEE=".$id_year." AND DSL.CODE_PERIODE=".$id_period." AND DSL.CODE_CAMPAGNE=".$id_camp.";";
     $list_users =  $GLOBALS['conn_dico']->GetAll($requete);
	 
	 return $list_users;
  }
  
  function get_user_camp_etab($systeme_id, $user_id, $id_camp, $id_year, $id_period) {
    
    $etab_list = array();
    
    $requete = "SELECT ID_CHAINE, ID_SYSTEME, ID_TYPE_REGROUP, ID_REGROUP
  				FROM DICO_FIXE_REGROUPEMENT
  				WHERE ID_SYSTEME=".$systeme_id." AND ID_USER=".$user_id." AND ID_CAMPAGNE=".$id_camp." AND ID_ANNEE=".$id_year." AND ID_PERIODE=".$id_period.";";
  	
  	$regroups = $GLOBALS['conn_dico']->GetAll($requete);
  		
  	$with_code_admin =  '';
    $with_status = '';
  	if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
  		$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' AS code ' ;
  	}
    if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT'])){
  		$with_status = ', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' AS status ' ;
  	}
  	
  	if (count($regroups) > 0) {
  		foreach ($regroups as $row) {
  			$ch = $row["ID_CHAINE"];
  			$sys = $row["ID_SYSTEME"];
  			$arbre = new arbre($ch);
  			$type_reg = $row["ID_TYPE_REGROUP"];
  			$regs = preg_split("/,/", $row["ID_REGROUP"]);
  			if ($type_reg == 0) {
  				foreach ($regs as $etab_id) { 
  					$requete_etab   = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom'. $with_status . $with_code_admin .'
  									 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R'.$from_periode.'  
  									 WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].$where_periode.'
  									 AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$etab_id.';';
  					//echo $requete_etab;
            $etab = $GLOBALS['conn']->GetAll($requete_etab);
            $etab = $etab[0];
  					if (!in_array($etab, $etab_list) && $etab != null) {
  						$etab_list[] = $etab;                                       
  					}
  				}
  			} else {
  				foreach ($regs as $reg) {
            $niv_profond = $arbre->get_depht_regroup($reg);
  					$etabs = $arbre->get_list_etabs($niv_profond, $reg, $sys, $id_camp, $id_year, $id_period);
            			//print_r($regroups);echo "<br/>";
  					foreach ($etabs as $etab) { 
  						if (!in_array($etab, $etab_list) && $etab != null) {
  							$etab_list[] = $etab;
  						}
  					}
  				}
  			}
  		}
  	}
    return $etab_list;
  }
  
  function get_list_etab() {  
    
    $liste_etab = array();
          
    if( count($_POST) > 0 ) {
  		$is_set_regs 	= false ;
  		//$is_set_ctrls 	= true ;
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
  		} else {
  			
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
  				$list_code_reg = $arbre->getchildsid($depht, $code_regroup, $_POST['id_chaine']);
  
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
              
  				$requete    = ' SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT']. ' as code_etab,'. 
  										$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' as nom_etab,'.$get_code_admin.$get_type_ent_stat.
  										$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' as code_regroup
  										FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' , '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].$from_periode.'   
  										WHERE '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].$where_periode.'
  										AND '.$where.' AND '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].'
  										ORDER BY '.$GLOBALS['PARAM']['ETABLISSEMENT'].'.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'];
  				//echo '<br>'.$requete.'<br>'; //die();
  				$liste_etab = array_merge($GLOBALS['conn']->GetAll($requete), $liste_etab); 
  				//print_r($requete); echo "<br/><br/>";
  			} //fin foreach
        //echo "NB : ".count($liste_etab);
  		} //fin else  
  	}  //fin if
    
    return $liste_etab;
  }

  function get_campagnes(){
		$conn = $GLOBALS['conn'];
   /* $requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as id_campagne, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as libelle_campagne
					FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
          WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' > 2 
					ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].';';*/
	$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as id_campagne, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' as libelle_campagne
					FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
          WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' <> 2 
					ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].';';				
					
		try {            
				$GLOBALS['campagnes'] = $conn->GetAll($requete);
				if(!is_array($GLOBALS['campagnes'])){                
						 throw new Exception('ERR_SQL');   
				}
				unset($GLOBALS['id_campagne']);
				if( isset($_GET['id_camp']) and (trim($_GET['id_camp'])<>'') ){
						$GLOBALS['id_campagne'] 	= $_GET['id_camp'];
				}elseif(isset($_SESSION['user_fixe_campagne']) && $_SESSION['user_fixe_campagne']<>''){
						$GLOBALS['id_campagne'] 	= $_SESSION['user_fixe_campagne'];
				}
				if(!isset($GLOBALS['id_campagne'])){
						$GLOBALS['id_campagne'] 	= $GLOBALS['campagnes'][0]['id_campagne'];
				}
		} catch (Exception $e) {
				 $erreur = new erreur_manager($e,$requete);
		} 
	}
  
  function get_periodes(){
      $conn = $GLOBALS['conn'];
      $requete     = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].' as id_periode, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].' as libelle_periode
			FROM '.$GLOBALS['PARAM']['TYPE_PERIODE'].'
			ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_PERIODE'].';';
      try {            
		      $GLOBALS['periodes'] = $conn->GetAll($requete);
          if(!is_array($GLOBALS['periodes'])){                
							 throw new Exception('ERR_SQL');   
					}
          unset($GLOBALS['id_periode']);
					if( isset($_GET['id_periode']) and (trim($_GET['id_periode'])<>'') ){
							$GLOBALS['id_periode'] 	= $_GET['id_periode'];
					}
					if(!isset($GLOBALS['id_periode'])){
							$GLOBALS['id_periode'] 	= $GLOBALS['periodes'][0]['id_periode'];
					}
      }catch (Exception $e) {
					 $erreur = new erreur_manager($e,$requete);
			}     
  }
  
  function get_traitements() {
      $GLOBALS['traitements'] = array();
      $GLOBALS['traitements'][] = array("id_trait"=>1, "libelle_trait"=>"Rappel d'envoie de données"); 
      $GLOBALS['traitements'][] = array("id_trait"=>2, "libelle_trait"=>"Confirmation réception de données");
      $GLOBALS['traitements'][] = array("id_trait"=>3, "libelle_trait"=>"Confirmation validation de données");
      $GLOBALS['traitements'][] = array("id_trait"=>4, "libelle_trait"=>"Information rejet de données");
  }
      
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
		
		function tableau_check( $tab=array(), $code, $lib, $chp_check, $lib_all_check, $chp_all_check, $nb_td=1, $nowrap='', $final_list_user_mail ){
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
                        $nb_miss_schools = "";
                        if (isset($tab[$i]['miss_schools'])) {
                           $nb_miss_schools = " : ".count($tab[$i]['miss_schools']);
                        }
                        $nb_ok_schools = "";
                        if (isset($tab[$i]['ok_schools'])) {
                           $nb_ok_schools = " : ".count($tab[$i]['ok_schools']);
                        }
												$html .= "\n\t\t" . '<td align="right" '.$nowrap.'><span';
                        if ($final_list_user_mail[$tab[$i][$code]] && $final_list_user_mail[$tab[$i][$code]] != 1) {
                           $html .= ' class="mail_error" title="'.$final_list_user_mail[$tab[$i][$code]].'"' ;
                        }                                                      
                        $html .= '>'.substr($tab[$i][$lib], 0, 200).$nb_miss_schools.$nb_ok_schools.'</span> <INPUT type="checkbox" id="'.$chp_check.'_'.$i.'" name="'.$chp_check.'_'.$i.'" value="'.$tab[$i][$code].'"';
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
						if( count($tab) > 0 ){
								if($_POST[$chp_all_check]==1) $checked = ' CHECKED';
								else $checked = '';
								//$html .= "\n" . '<tr><td nowrap colspan="' . $nb_td . '" align="center">' . recherche_libelle_page($lib_all_check) . ' <INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'></td></tr>';
                $GLOBALS[$chp_all_check] =	'<INPUT type="checkbox" id="' . $chp_all_check . '" name="' . $chp_all_check . '" value="1" onclick="manage_check(\''.$chp_all_check.'\', \''.$chp_check.'\');"'.$checked.'>' ;
						}
						$html .= "\n" . '</TABLE>';
				}
				return ($html);
		}
    
    function etab_exist($id_etab, $list_etab) {
      $idx = 0;
      $trouv = false;
      while(!$trouv && isset($list_etab[$idx])){
          $etab = $list_etab[$idx];
          if ($id_etab == $etab['CODE_ETAB']) {
              $trouv = true;
          }
          $idx++;
      }
      return $trouv;
    }
    
		get_campagnes();
    get_periodes();
		get_chaines_systeme();
		get_type_regs_chaine();
    get_traitements();

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

<FORM name="Formulaire"  method="post" action="">

<div>
<?php echo recherche_libelle_page('curr_year');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></b> 
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php //if(isset($GLOBALS['PARAM']['FILTRE']) && $GLOBALS['PARAM']['FILTRE']) echo recherche_libelle_page('curr_period').' : <b>'.get_libelle_from_array($_SESSION['tab_filtres'], $_SESSION['filtre'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']); ?>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_sect');?> :
<select name="id_systeme"	onChange="reload_page(this.value,id_chaine.value,type_reg.value,id_campagne.value,id_periode.value,id_traitement.value,id_campagne_cible.value); ">
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
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_camp'); $lst_camp = $GLOBALS['campagnes'];?> : 
<select name="id_campagne"	onChange="reload_page(id_systeme.value,id_chaine.value,type_reg.value,this.value,id_periode.value,id_traitement.value,id_campagne_cible.value);">
<?php 
$id_campagne = $GLOBALS['campagnes'][0]['id_campagne'];
if(is_array($GLOBALS['campagnes'])){
	foreach ($GLOBALS['campagnes'] as $i => $campagnes){
		if($_GET['id_camp']<>'' && $_GET['id_camp']==$campagnes['id_campagne']){
			echo "<option value='".$campagnes['id_campagne']."' selected>".$campagnes['libelle_campagne']."</option>";
      $id_campagne = $campagnes['id_campagne'];
		} else {
			echo "<option value='".$campagnes['id_campagne']."'>".$campagnes['libelle_campagne']."</option>\n";
		}
	}
}

?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_camp_cible'); ?> : 
<select name="id_campagne_cible"	onChange="reload_page(id_systeme.value,id_chaine.value,type_reg.value,this.value,id_periode.value,id_traitement.value,this.value);">
<?php 
if(is_array($lst_camp)){
	foreach ($lst_camp as $i => $campagnes){
		if($_GET['id_camp_c']<>'' && $_GET['id_camp_c']==$campagnes['id_campagne']){
			echo "<option value='".$campagnes['id_campagne']."' selected>".$campagnes['libelle_campagne']."</option>";
		} else {
			echo "<option value='".$campagnes['id_campagne']."'>".$campagnes['libelle_campagne']."</option>\n";
		}
	}
}

?>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php echo recherche_libelle_page('curr_periode');?> : 
<select name="id_periode"	onChange="">
<?php 
$id_period = $GLOBALS['periodes'][0]['id_periode'];
if(is_array($GLOBALS['periodes'])){
	foreach ($GLOBALS['periodes'] as $i => $periodes){
		if($_GET['id_periode']<>'' && $_GET['id_periode']==$periodes['id_periode']){
			echo "<option value='".$periodes['id_periode']."' selected>".$periodes['libelle_periode']."</option>";
		} else {
			echo "<option value='".$periodes['id_periode']."'>".$periodes['libelle_periode']."</option>\n";
		}
	}
}

?>
</select>
<br/>
<?php echo recherche_libelle_page('curr_traitement');?> : 
<select name="id_traitement"	onChange="">
<?php 
$id_traitement = 1;
if(is_array($GLOBALS['traitements'])){      
  $id_traitement = $GLOBALS['traitements'][0]['id_trait'];
	foreach ($GLOBALS['traitements'] as $i => $traitement){
		if(($_POST['id_traitement']<>'' && $_POST['id_traitement']==$traitement['id_trait']) || ((count($_POST)==0) && $_GET['id_trait']<>'' && $_GET['id_trait']==$traitement['id_trait'])){
			echo "<option value='".$traitement['id_trait']."' selected>".$traitement['libelle_trait']."</option>";
      $id_traitement = $traitement['id_trait'];
		} else {
			echo "<option value='".$traitement['id_trait']."'>".$traitement['libelle_trait']."</option>\n";
		}
	}
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
        <td rowspan="2" width="45%"><table border='1' align="center"  width="100%">
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
				  <select style="width:100px" name="id_chaine" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>,this.value,type_reg.value,id_campagne.value,id_periode.value,id_traitement.value,id_campagne_cible.value);">
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
                  <select style="width:100px" name="type_reg" onchange="reload_page(<?php echo $GLOBALS['id_systeme'];?>, id_chaine.value, this.value,id_campagne.value,id_periode.value,id_traitement.value,id_campagne_cible.value);">
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
						$combos_regs = preg_replace('/<select/', '<select  style=\'width:100px;\'',$combos_regs); 
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
                    <td align="center" valign="middle"  width="100%"><div style="height:140px; max-height:140px; overflow:auto" class="border_table" >
                        <?php print (tableau_check( $tab_regs, 'CODE_REG', 'LIB_REG', 'REGS', 'toutes', 'ALL_REGS', 2 , 'nowrap', $final_list_user_mail)); ?>
                        <input type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>" />
                    </div></td>
                  </tr>
                </table>
                <div class="class_table" style="vertical-align:bottom; height:24px; text-align:center"> &nbsp;<span class="class_table" style="vertical-align:bottom; height:24px; text-align:center"><u><?php echo recherche_libelle_page('all_regs'); ?></u> :<?php echo $GLOBALS['ALL_REGS'];?></span></div></td>
          </tr>
        </table></td>
        <td width="55%"><table border='1' align="center"  width="100%">
            <caption>
            <b><?php echo recherche_libelle_page('ListUsers');?>  </b>
            </caption>
          <tr>
              <td><div style="height:200px; max-height:200px; overflow:auto" class="border_table" >
                  <?php 
                  $final_list_user = array();
                  if ( count($_POST) > 0 && $is_set_regs) {
                      $curr_camp_themes_tmp = get_camp_themes($_SESSION['secteur'], $id_campagne, 'fr');
                      $curr_camp_themes = array();
                      foreach($curr_camp_themes_tmp as $l=>$camp_theme) {  //print_r($camp_theme); echo "<br><br>";
                          $curr_camp_themes[$camp_theme['id']] = $camp_theme;
                      } 
                      $nb_curr_camp_themes = count($curr_camp_themes);
                      $liste_select_etabs = get_list_etab();    //echo "<br><br>".count($liste_select_etabs)."<br><br>"; print_r($liste_select_etabs);    
                      $liste_log_etabs = get_etab_log($id_campagne, $_SESSION['annee'], $id_period);   //echo "<br><br>".count($liste_log_etabs)."<br><br>"; print_r($liste_log_etabs);
          						$list_user = get_user_camp($_SESSION['secteur'], $id_campagne, $_SESSION['annee'], $id_period);   //echo "<br><br>".count($list_user);
                      
                      foreach($list_user as $i=>$user) { //pour chaque utilisateur ayant des droits sur cette campagne
                           $list_user_etabs = get_user_camp_etab($_SESSION['secteur'], $user['CODE_USER'], $id_campagne, $_SESSION['annee'], $id_period);  //echo "<br><br>".count($list_user_etabs)."<br><br>"; print_r($list_user_etabs);
                           $trouv = false;  
                           
                           if ($id_traitement == 1) {
                              $user['miss_schools'] = array();
                           } else if ($id_traitement == 2) {
                              $user['ok_schools'] = array();
                           } else if ($id_traitement == 3) {
                              $user['val_schools'] = array();
                           }
                           foreach($list_user_etabs as $j=>$etab) {
                                if (etab_exist($etab['id'], $liste_select_etabs)) { //etablissement de l'utilisateur présent dans la zone géographique
                                     if ($id_traitement == 1) { //Données non envoyés ou incomplètes
                                         if (!etab_exist($etab['id'], $liste_log_etabs)) {  //etablissement non présent dans les logs
                                             $user['miss_schools'][] = $etab;
                                             $trouv = true;
                                         } else {   //l'établissement est présent mais on vérifie si tous les themes sont ok
                                             $school_themes_ok = get_etab_themes_log($etab['id'], $id_campagne, $_SESSION['annee'], $id_period);  //liste des themes ok pour cette école
                                             if ($nb_curr_camp_themes != count($school_themes_ok)) {  //le nombre de theme ok doit etre le meme que le nombre de themes total
                                                $etab['miss_themes'] = array();     //print_r($curr_camp_themes); echo "<br><br>THEME OK:";print_r($school_themes_ok); echo "<br><br>";
                                                foreach($curr_camp_themes as $l=>$camp_theme) {  //print_r($camp_theme); echo "<br><br>";
                                                   if (!in_array(array("id_theme"=>$camp_theme['id']), $school_themes_ok)) { //si le theme n'est pas présent dans les themes ok
                                                       $etab['miss_themes'][] = $camp_theme;
                                                   }
                                                }
                                                $user['miss_schools'][] = $etab;
                                                $trouv = true;
                                             }
                                         }
                                     } else if ($id_traitement == 2) {
                                         $school_themes_ok = get_etab_themes_log($etab['id'], $id_campagne, $_SESSION['annee'], $id_period);  //liste des themes ok pour cette école
                                         if ($nb_curr_camp_themes == count($school_themes_ok)) {  //le nombre de theme ok doit etre le meme que le nombre de themes total
                                            $user['ok_schools'][] = $etab;
                                            $trouv = true;
                                         }
                                     } else if ($id_traitement == 3 || $id_traitement == 4) {
                                         $school_themes_ok = get_etab_themes_log($etab['id'], $id_campagne, $_SESSION['annee'], $id_period);
                                         if ($nb_curr_camp_themes == count($school_themes_ok)) { 
                                            $status = 3;
                                            if ($id_traitement == 4) {
                                                $status = "2";
                                            }                                      
                                            $school_themes_val = get_school_validation_theme($etab['id'], $_SESSION['annee'], $id_period, $status);
                                            if (count($school_themes_val) > 0) {
                                              $etab['val_themes'] = $school_themes_val;
                                              $user['val_schools'][] = $etab;
                                              $trouv = true;
                                            }
                                         }   
                                     }                                
                                }
                           }
                           if ($trouv) {
                              $final_list_user[] = $user;
                           }
                      } //echo "<br><br>".count($final_list_user[0]['miss_schools'])."<br><br>"; print_r($final_list_user[0]['miss_schools']);
                      print (tableau_check( $final_list_user, 'CODE_USER', 'LIB_USER', 'USERS', 'toutes', 'ALL_USERS', 4 , 'nowrap', $final_list_user_mail)); //echo "<pre>"; print_r($final_list_user);
                    }
                    $final_list_user_s = serialize($final_list_user);
        					?>    
                  <input type="hidden" name="final_list_user" value='<?php echo htmlspecialchars($final_list_user_s); ?>' />
                  <input type="hidden" name="nb_regs_dispo" value="<?php echo count($tab_regs);?>" />
              </div></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><div style="position:relative; vertical-align:baseline; height:22px" >
            <div class="class_table" style="position: absolute; text-align:left; overflow:auto"> &nbsp;&nbsp;<u><?php echo recherche_libelle_page('all_users');?></u> :<?php echo $GLOBALS['ALL_USERS'];?></div>
            <table border='1' align="right">
              <tr>
                <td><input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="19px" height="17px" border="0"  value="Envoyer" class="envoyer" /></td>
                <td class="like_caption"><?php echo recherche_libelle_page('exec');?></td>
              </tr>
            </table>
        </div></td>
      </tr>
      <tr>
        <td width="45%">
          <div style="position:relative; vertical-align:baseline;" >
               <table cellpadding="10" width="100%">
                <caption>Mail Box</caption>
                <tr>
                  <td style="padding: 4px;"><label>Object : </label></td>
                  <td width="90%"><input type="text" name="mail_object" value="<?php echo $mail_object ?>" style="margin: 4px; width: 98%;"/></td>
                </tr>       
                <tr>
                  <td style="padding: 4px;"><label>Message : </label></td>
                  <td width="90%"><textarea name="mail_message" wrap="SOFT" tabindex="0" dir="ltr" spellcheck="false" style="margin: 4px; width: 98%; height: 170px;"><?php echo $mail_message ?></textarea></td>
                </tr>
              </table> 
              <div style="text-align:right;">
                <input name="sendmailButton" id="sendmailButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="19px" height="17px" border="0"  value="Send" class="envoyer" />  
              </div>
          </div>
        </td>
        <td style="vertical-align: middle; padding:10px;">
            <div> The string [list_schools] will be replaced in the mail by list of schools concerned by the message.</div><br/>
            <div> <u>Exemple of message</u> :</div><br/>
            <div> Dear collector,<br/> Please remind to send before xx/xx/xxxx collected data of these schools :<br/><br/>[list_schools]<br/><br/>Best regards,<br/><br/>Administrator StatEduc2</div>
        </td>
      </tr>
    </table>
</div>
</FORM>
