<?php 
  require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
  require_once "Mail.php";
  
  $id_campagne = $_GET['id_campagne'];
  $id_period = $_GET['id_period'];
  $id_traitement = $_GET['id_traitement'];
    
  function get_camp_themes($id_sys, $id_camp, $code_lang) {
       $requete = "SELECT DICO_THEME_SYSTEME.ID_THEME_SYSTEME AS id, DICO_THEME_SYSTEME.ID AS id_theme, DICO_TRADUCTION.LIBELLE AS title, DICO_THEME_SYSTEME.APPARTENANCE AS idcamp, DICO_THEME_SYSTEME.ID_SYSTEME AS idsys, DICO_THEME_SYSTEME.PRECEDENT AS pre
    				FROM DICO_TRADUCTION INNER JOIN DICO_THEME_SYSTEME ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_THEME_SYSTEME.ID_THEME_SYSTEME
    				WHERE (((DICO_THEME_SYSTEME.APPARTENANCE)=".$id_camp.") AND ((DICO_THEME_SYSTEME.ID_SYSTEME)=".$id_sys.") AND ((DICO_TRADUCTION.NOM_TABLE)='DICO_THEME_LIB_MENU') AND ((DICO_TRADUCTION.CODE_LANGUE)='".$code_lang."')) AND (DICO_THEME_SYSTEME.FRAME <> '');";
       
    	 $list_theme = $GLOBALS['conn_dico']->GetAll($requete);
       
       return  $list_theme;
  }
  
  function get_etab_log($id_camp, $id_year, $id_period) {
     $requete = "SELECT DISTINCT CODE_ECOLE as code_etab 
                FROM DATA_SAVING_LOGS 
                WHERE CODE_CAMPAGNE=".$id_camp." AND CODE_ANNEE=".$id_year." AND CODE_PERIODE=".$id_period.";";
     $list_etab =  $GLOBALS['conn_dico']->GetAll($requete);
     
     return $list_etab;          
  }
  
  function get_user_camp($systeme_id, $id_camp, $id_year, $id_period) {
     
     $requete = "SELECT DISTINCT AU.CODE_USER, AU.NOM_LONG_USER & ' (' & AU.NOM_USER & ') '   AS LIB_USER, AU.EMAIL_USER   
  				FROM DICO_FIXE_REGROUPEMENT DFR, ADMIN_USERS AU
  				WHERE DFR.ID_USER=AU.CODE_USER AND DFR.ID_SYSTEME=".$systeme_id." AND DFR.ID_CAMPAGNE=".$id_camp." AND DFR.ID_ANNEE=".$id_year.
          " AND DFR.ID_PERIODE=".$id_period.";";
       
     $users = $GLOBALS['conn_dico']->GetAll($requete);  
     
     return $users;   
  } 
  
  function get_etab_themes_log($id_etab, $id_camp, $id_year, $id_period) {
      $requete = "SELECT DISTINCT ID_THEME_SYSTEME as id_theme 
                FROM DATA_SAVING_LOGS 
                WHERE CODE_ECOLE=".$id_etab." AND CODE_CAMPAGNE=".$id_camp." AND CODE_ANNEE=".$id_year." AND CODE_PERIODE=".$id_period." AND STATUT_OPERATION='OKSAVE';";
      $list_theme =  $GLOBALS['conn_dico']->GetAll($requete);
     
      return $list_theme;
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
                 
  $val_camp_themes_tmp = get_camp_themes($_SESSION['secteur'], $id_campagne, 'fr');
  $val_camp_themes = array();
  foreach($val_camp_themes_tmp as $l=>$camp_theme) {  
      $val_camp_themes[$camp_theme['id_theme']] = $camp_theme;
  }  
                     
  function sendMail($destinataire, $sujet, $msg, $user, $val_camp_themes) {
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
    foreach($user['val_schools'] as $i => $school) {
      $list_schools.= ($i+1)." : ".$school['nom']." (".$school['code'].")".$passage_ligne;
      if (isset($school['val_themes'])) {
         foreach($school['val_themes'] as $j => $theme) {
             $list_schools.= "   - ".$val_camp_themes[$theme['title']]['title']." (".$theme['statut'].") : ".$theme['com'].$passage_ligne;
         }
      } else {
          $list_schools.= "   - All".$passage_ligne;
      }
      $list_schools.= $passage_ligne;
    }
    $message_txt = str_replace('[list_schools]', $list_schools, $message_txt);
    //echo '<textarea name="mail_message" tabindex="0" dir="ltr" style="margin: 4px; width: 98%; height: 170px;">'.$message_txt.'</textarea>';
    $message_html = "<html><head></head><body><b>Salut à tous</b>, voici un e-mail envoyé par un <i>script PHP</i>.</body></html>";
    //==========
                
    $from = "StatEduc2 <stateduc2@mail.kn>";
    $host = "ssl0.ovh.net"; 
    $port = "587";
    $username = "kafarsi@ardive.net"; 
    $password = "qaz123LOG";
     
    //=====Création de la boundary
    $boundary = "-----=".md5(rand());
    //==========
          
    //=====Création du header de l'e-mail.    
    $headers = array ('From' => $from,   'To' => $destinataire,   'Subject' => $sujet);
    //==========
     
    //=====Création du message.
    $message = "";
    //=====Ajout du message au format texte.
    $message.= $passage_ligne.$message_txt.$passage_ligne;
    //========== 
    //=====Envoi de l'e-mail.
    
    $smtp = Mail::factory('smtp',   array ('host' => $host,  'port' => $port,   'auth' => true,     'username' => $username,     'password' => $password));
    $mail = $smtp->send($destinataire, $headers, $message);  
    if (PEAR::isError($mail)) {   
      echo("<p>" . $mail->getMessage() . "</p>");  
    } else {   
      echo("<p>Message successfully sent!</p>");  
    }
    //==========
  }
  
  $final_list_user = array();
  
  $mail_object = "Reminder";  
  $mail_message = "Dear collector,\nPlease remind to send before xx/xx/xxxx collected data of these schools :\n\n[list_schools]\n\nBest regards,\n\nAdministrator StatEduc2";
  //echo $id_campagne."ZZZ".$id_period."ZEZE".$id_traitement; 
  $curr_camp_themes_tmp = get_camp_themes($_SESSION['secteur'], $id_campagne, 'fr');
  $curr_camp_themes = array();
  foreach($curr_camp_themes_tmp as $l=>$camp_theme) {  //print_r($camp_theme); echo "<br><br>";
      $curr_camp_themes[$camp_theme['id']] = $camp_theme;
  } 
  $nb_curr_camp_themes = count($curr_camp_themes);
  $liste_log_etabs = get_etab_log($id_campagne, $_SESSION['annee'], $id_period);   //echo "<br><br>".count($liste_log_etabs)."<br><br>"; print_r($liste_log_etabs);
	$list_user = get_user_camp($_SESSION['secteur'], $id_campagne, $_SESSION['annee'], $id_period);
  
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
            //if (etab_exist($etab['id'], $liste_select_etabs)) { //etablissement de l'utilisateur présent dans la zone géographique
            if ($id_traitement == 1) {
              if (!etab_exist($etab['ID'], $liste_log_etabs)) {  //etablissement non présent dans les logs
                 $user['miss_schools'][] = $etab;
                 $trouv = true;
              } else {   //l'établissement est présent mais on vérifie si tous les themes sont ok
                 $school_themes_ok = get_etab_themes_log($etab['ID'], $id_campagne, $_SESSION['annee'], $id_period);  //liste des themes ok pour cette école
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
            } else if ($id_traitement == 3 || $id_traitement == 4) {
            }
       }
       if ($trouv) {
          $final_list_user[] = $user;
          sendMail($user['EMAIL_USER'], $mail_object, $mail_message, $user, $val_camp_themes);
       }
  }
  /*
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
            $status = "2,1";
        }                                      
        $school_themes_val = get_school_validation_theme($etab['id'], $_SESSION['annee'], $id_period, $status);
        $etab['val_themes'] = $school_themes_val;
        $user['val_schools'][] = $etab;
        $trouv = true;
     }   
  } */
    //} //echo "<br><br>".count($final_list_user[0]['miss_schools'])."<br><br>"; print_r($final_list_user[0]['miss_schools']);
?>