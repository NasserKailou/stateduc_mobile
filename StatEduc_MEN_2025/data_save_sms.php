<?php
require_once 'common_ws.php';

require $GLOBALS['SISED_PATH_LIB'] . 'Curl/Curl.php';

use \Curl\Curl;
$curl = new Curl();
$curl->setHeader('User-Agent', 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
$curl->setHeader('Accept', '*/*');
$curl->setHeader('Accept-Encoding', 'gzip,deflate');
$curl->setHeader('Accept-Language', 'fr-FR,fr;q=0.8,en-US;q=0.6,en;q=0.4');
$curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');

$app = new \Slim\Slim();

$lib_status =  $GLOBALS['PARAM_WS']['LIB_STATUS'];
$lib_message = $GLOBALS['PARAM_WS']['LIB_MESSAGE'];
$lib_data = $GLOBALS['PARAM_WS']['LIB_DATA'];

$status_ok = $GLOBALS['PARAM_WS']['STATUS_OK'];
$status_ko = $GLOBALS['PARAM_WS']['STATUS_KO'];

//$app->add(new \HttpAuth());

$app->post('/sync', function () use ($app, $lib_status, $lib_message, $lib_data, $status_ok, $status_ko, $curl) {
	$status = $GLOBALS['PARAM_WS']['OK'];
    $req = $app->request();
	
	// Get the phone number that sent the SMS.
	$error = null;
	$from = $req->post('from');
	if (!isset($from) || empty($from)) {
		$error = 'The from variable was not set';	 
	}
	 
	// Get the SMS aka the message sent.
	$message = $req->post('message');
	if (!isset($message)) {
		$error = 'The message variable was not set';
	}
	 
	// Set success to false as the default success status
	$success = false;
	 
	// Get the secret key set on SMSSync side
	// for matching on the server side.
	$secret = $req->post('secret');
	
	// Get the timestamp of the SMS
	$sent_timestamp = $req->post('sent_timestamp');
	
	// Get the phone number of the device SMSSync is installed on.
	$sent_to = $req->post('sent_to');
	
	// Get the unique message id
	$message_id = $req->post('message_id');
	 
	/**
	 * Now we have retrieved the data sent over by SMSSync
	 * via HTTP. Next thing to do is to do something with
	 * the data. Either echo it or write it to a file or even
	 * store it in a database. This is entirely up to you.
	 * After, return a JSON string back to SMSSync to know
	 * if the web service received the message successfully or not.
	 *
	 * In this demo, we are just going to save the data
	 * received into a text file.
	 *
	 */
	if ((strlen($from) > 0) AND (strlen($message))) {
		// The screte key set here is 123456. Make sure you enter that on SMSSync.
		if (($secret == '123456')) {
			$success = true;
		} else {
			$error = "The secret value sent from the device does not match the one on the server";
		}
		// now let's write the info sent by SMSSync
		// to a file called test.txt
		//$string = $message."\n";
		$string = "From: ".$from."\n";
		$string .= "Message: ".$message."\n";
		$string .= "Timestamp: ".$sent_timestamp."\n";
		$string .= "Messages Id:" .$message_id."\n";
		$string .= "Sent to: ".$sent_to."\n\n\n";
		$myFile = "test.txt";
		$fh = fopen($myFile, 'a') or die("can't open file");
		@fwrite($fh, $string);
		@fclose($fh);
	 
		/**
		* Now send a JSON formatted string to SMSSync to
		 * acknowledge that the web service received the message
		*/
	   // echo json_encode(array("payload"=>array(
		 //   "success"=>$success, "error" => $error)));
	 
	} else {
		echo json_encode(array("payload"=>array("success"=>$success, "error" => $error)));
		return;
	}
	$msg = $message;
/*	$idx = strpos( $msg, ' ');
	$serv_id = substr($msg, 0, $idx );
	$msg = substr($msg, $idx + 1 );   */
	 
	$idx = strpos( $msg, '/');
	$save_code = substr($msg, 0, $idx );
	$msg = substr($msg, $idx + 1 );
	
	$msg_comp = explode("#", $msg);
	//echo $msg_comp[0]."____".$msg_comp[1]; 
	if (!$msg_comp[1] || empty($msg_comp[1])) {
		$requete = "SELECT MESSAGE
					FROM SMS_DATA
					WHERE PHONE_NUM='".$from."'
					AND SAVE_CODE='".$save_code."' 
					ORDER BY MESSAGE_IDX;";
   
		$sms_datas = $GLOBALS['conn_dico']->GetAll($requete);
		$theme_data = "";
		if (count($sms_datas) > 0) {
			foreach ($sms_datas as $sms_data) {
				$theme_data .= $sms_data['MESSAGE'];
			}
			
			$theme_data .= $msg_comp[0];
			
			$idx = strpos( $theme_data, '/');
			$id_camp = substr($theme_data, 0, $idx );
			$theme_data = substr($theme_data, $idx + 1 );
			
			$idx = strpos( $theme_data, '/');
			$id_sector = substr($theme_data, 0, $idx );
			$theme_data = substr($theme_data, $idx + 1 );
			 
			$idx = strpos( $theme_data, '/');
			$id_theme = substr($theme_data, 0, $idx );
			$theme_data = substr($theme_data, $idx + 1 );
			 
			$idx = strpos( $theme_data, '/');
			$id_etab = substr($theme_data, 0, $idx );
			$theme_data = substr($theme_data, $idx + 1 );
			 
			$idx = strpos( $theme_data, '/');
			$id_filter = substr($theme_data, 0, $idx );
			$theme_data = substr($theme_data, $idx + 1 );
			
			$curl->success(function($instance) {
				
			});
			$curl->error(function($instance) {
				$f = "+221774906209";
				$s = true;
				$reply[0] = array("to" => $f, "message" => $instance->error_code." : ".$instance->error_message);
				echo json_encode(array("payload"=>array("success"=>$s,"task"=>"send","messages"=>array_values($reply))));				
			});
			$curl->complete(function($instance) {
				//echo 'call completed' . "<br/>";
			});	
			
			$data_array = explode('&', $theme_data);
			
			$data_to_send = array();
			
			foreach ($data_array as $row) {
				$row_tab = explode('=', $row);
				$data_to_send[$row_tab[0]] = str_replace("_slh_", "/", $row_tab[1]);
			}
			$urlBase = $GLOBALS['SISED_AURL'].'questionnaire_ws.php?sector='.$id_sector.'&theme='.$id_teme.'&code_etab='.$id_etab.'&type_ent_stat='.$id_camp;
			if ($id_filter != null) {
				$urlBase .= '&filtre='.$id_filter;
			}
			$curl->post($urlBase, $data_to_send);
		}
	} else {	
		$req = "INSERT INTO SMS_DATA (PHONE_NUM, SAVE_CODE, MESSAGE_IDX, MESSAGE) VALUES ('".$from."', '".$save_code."', '".$msg_comp[1]."', '".$msg_comp[0]."')";
		
		$ok = $GLOBALS['conn_dico']->Execute($req);
			
		if (!$ok) {		
			/**
			 * Log error and send error message to user.
			 *
			 * This feature requires the "Get reply from server" checked on SMSSync.
			 */
			$m = "SMS not saved";
			$f = "+221774906209";
			$s = true;
			$reply[0] = array("to" => $f, "message" => $m);
			$myFile = "sms_errors.txt";
			$fh = fopen($myFile, 'a') or die("can't open file");
			@fwrite($fh, $m." : ".$from." / ".$message."\n\n");
			@fclose($fh);
		 
			echo json_encode(array("payload"=>array("success"=>$s,"task"=>"send","messages"=>array_values($reply))));
		}
	}

});

$app->run();
 
function str_starts_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, 0, strlen($needle)) 
           === 0;
}
function str_ends_with($haystack, $needle)
{
    return substr_compare($haystack, $needle, -strlen($needle)) 
           === 0;
}

?>