<?php

/**
 * params_ws.php
 *
 * Fichier de configuration des constantes des Web Services mobiles.
 * Definit les cles JSON communes (se_status, se_message, se_data),
 * les codes de statut HTTP (200 = OK, 500 = KO) et les parametres globaux
 * utilises par tous les WS REST de l'application mobile StatEduc Burundi.
 *
 * @auteur  kailounasser@gmail.com - Abdoul Nasser Kailou
 * @projet  StatEduc Burundi -- Application mobile de collecte scolaire
 * @sessions 1-17
 * @modifie Modifie par kailounasser@gmail.com Abdoul Nasser Kailou
 *          Toutes les modifications et nouveautes sont documentees
 *          directement dans le code avec des commentaires en francais.
 */

	$GLOBALS['PARAM_WS']                                           	=   array();

	$GLOBALS['PARAM_WS']['LIB_STATUS']	                           	=	'se_status';
	$GLOBALS['PARAM_WS']['LIB_MESSAGE']	            				=	'se_message';
	$GLOBALS['PARAM_WS']['LIB_DATA']	               				=	'se_data';
	$GLOBALS['PARAM_WS']['STATUS_OK']	                       		=	200;
	$GLOBALS['PARAM_WS']['STATUS_KO']	                       		=	400;
    
	$GLOBALS['PARAM_WS']['LOGIN_OK']	                        	=	'log_ok';
	$GLOBALS['PARAM_WS']['LOGIN_KO']	            				=	'log_ko';
	$GLOBALS['PARAM_WS']['OK']	            						=	'ok';
	$GLOBALS['PARAM_WS']['KO']	            						=	'ko';
?>