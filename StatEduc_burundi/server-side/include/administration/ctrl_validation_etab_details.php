<?php
//Vérification de la validité de la session.
/*session_start();
if (!isset($_SESSION['valide']) || !$_SESSION['valide']) {
	sendError('session_end');
	return;
}
require_once '../../../common.php'; 
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php'; */

require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';
use \Curl\Curl;
//$arbre = new arbre($_GET['id_chaine']);
  
//lit_libelles_page('/gestion_zone.php');

//$rootPath = $GLOBALS['SISED_PATH'];
//$connexion_dico = $GLOBALS['conn_dico'];

$form1 = get_etab_forms($_GET['code_theme1'], $_GET['code_etab'], 5, $_GET['code_periode']);
$form2 = get_etab_forms($_GET['code_theme2'], $_GET['code_etab'], 12, $_GET['code_periode']);
$result_val = check_etab_form($_GET['nom_etab'], $_GET['idx'], $form1, $form2);
echo $result_val;
 
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

  function check_inputs($td1, $td2) {
    $inputs1 = $td1->getElementsByTagName('input');
    $inputs2 = $td2->getElementsByTagName('input');
    $i = 0;
    $nbKo = 0;
    $isData = 0;
    foreach ($inputs1 as $input1) {
      $input2 = $inputs2->item($i); 
      $type = $input1->getAttribute('type');
      if ($type == 'text') {
        if (($input1->getAttribute('value') != $input2->getAttribute('value')) && !startsWith($input1->getAttribute('name'), 'TOT')) {
          $nbKo++;           
        }
      } else if ($type == 'radio' || $type == 'checkbox') {
         if ($input1->getAttribute('checked') != $input2->getAttribute('checked')) {
          $nbKo++;           
        }
      }
      $i++;
    } 
    if ($i > 0) {
      $isData = 1;
    }
    $i = 0;
    $selects1 = $td1->getElementsByTagName('select');
    $selects2 = $td2->getElementsByTagName('select');
    foreach ($selects1 as $select1) {
      $select2 = $selects2->item($i);
      if ($select1->hasAttribute('onchange')) {
        $select1->removeAttribute('onchange');
      }
      if ($select2->hasAttribute('onchange')) {
        $select2->removeAttribute('onchange');
      } 
      $htmlSelect1 = $td1->ownerDocument->saveHTML($select1);
      $htmlSelect2 = $td2->ownerDocument->saveHTML($select2);
      if (strcasecmp($htmlSelect1, $htmlSelect2) !== 0) {
        $nbKo++;
      }
      $i++;
    }     
    if (($i > 0) || $isData) {
      return $nbKo;
    } else {
      return -1;
    }
  }
    
  function check_etab_form($nom_etab, $idx, $form1, $form2) {
    $tds1 = $form1->getElementsByTagName('td');
    $tds2 = $form2->getElementsByTagName('td');
    //echo "<pre>"; 
    $html1 = "";
    $html2 = "";
    $html = "";
    $i = 0;     
    $nbElt = 0;
    $nbKo = 0;
    foreach ($tds1 as $td1) {
      $td2 = $tds2->item($i);
      $nbInputKo = check_inputs($td1, $td2);
      if ($nbInputKo > 0) { 
        $nbKo++;   
        $nbElt++;
        $html1 .= "<td><div class='ctrl_elt'>".str_replace('&amp;#13;','',str_replace('Â','',get_inner_html($td1)))."</div></td>";
        $html2 .= "<td><div class='ctrl_elt'>".str_replace('&amp;#13;','',str_replace('Â','',get_inner_html($td2)))."</div></td>";
      } else if ($nbInputKo > -1) {
        $nbElt++;
      }
      $i++;
    } 
    if ($nbKo > 0) { 
      $html = "<br/><br/><br/><br/><div><b>&nbsp;".$idx." : ".$nom_etab."</b></div><br/><table border='1'><tr><td rowspan='2'>".$nbKo."/".$nbElt."</td><td>Standard</td>".$html1."</tr><tr><td>Validation</td>".$html2."</tr></table><br/>";
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