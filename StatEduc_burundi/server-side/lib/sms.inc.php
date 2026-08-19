<?php

class SmsSender {


	var $sms_service_url; //url du service qui envoie les sms
	var $token; //security token
	var $curl; //class pour envoyer des requetes POST et GET
	
	function __construct($url_service, $token, $curl) {
		$this->sms_service_url = $url_service;
		$this->token = $token;
		$this->curl = $curl;
	}
	
	function sendSms($phone_number, $msg) { 
		$urlSms = $this->sms_service_url."?number=". $phone_number. "&message=". $msg. "&token=". $this->token;
		$this->curl->success(function($instance) {
				
		});
		$this->curl->error(function($instance) {
					
		});
		$this->curl->complete(function($instance) {
			//echo 'call completed' . "<br/>";
		});
		$this->curl->get($urlSms);
	}
	
}

?>