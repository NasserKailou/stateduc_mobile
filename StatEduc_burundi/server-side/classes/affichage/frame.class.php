<?php /** 
	 * Classe frame : g�n�ration des frames
	 * <pre>
	 * Gen�re un fichier "frame" contenant la structure HTML du th�me sous la
	 * forme d'un formulaire proche du questionnaire d'enqu�te
	 * </pre>
	 * @access public
	*/
class frame{
	/**
	 * Attribut : id_themes
	 * <pre>
	 * variable associ�e au th�me : contient un tableau de th�mes pour 
	 * lesquels on cherche � g�n�rer les templates correspondants
	 * </pre>
	 * @var array
	 * @access public
	 */	
  	public $id_themes	=	array();
		
		/**
	 * Attribut : langues
	 * <pre>
	 * variable associ�e � la langue choisie : Les templates 
	 * seront g�n�r�s suivant les langues contenues dans la variable
	 * </pre>
	 * @var array
	 * @access public
	 */
		public $langues		=	array();   

		/**
	 * Attribut : id_systemes
	 * <pre>
	 * Contient les codes systeme d'enseignement pour lesquels
	 * on cherche  � g�n�rer les frames
	 * </pre>
	 * @var array
	 * @access public
	 */
		public $id_systemes		=	array();   

	 /**
	 * Attribut : conn
	 * <pre>
	 * Dans cette variable on extrait la variable globale de connexion � la 
	 * base pour en faire une propri�t� de la classe
	 * </pre>
 	 * @var string
	 * @access public
	 */			
		public $conn;	   

		/**
	 * Attribut : dico
	 * <pre>
	 * variable dico: re�oit les �l�ments tels que les zones et leurs descriptions
	 * </pre>
	 * @var array
	 * @access public
	 */		
		public $dico;

		/**
	 * Attribut : libelle_long
	 * <pre>
	 * variable contenant le libell� ou le titre complet traduit du FRAME
	 * </pre>
	 * @var string
	 * @access public
	 */				
		public $libelle_long;	// 
	
		public $mat_nomenc_col;	// Pour gerer les libelles affiches comme colonne de la grille
    	public $mat_dim_col;
		public $type_theme;
		public $nb_themes_to_generate;//nombre de themes � g�n�rer
		public $is_effectif = false;
//----------------------------------------------------------------------
//----------------------------------------------------------------------
	 /**
	 * METHODE :  __construct(id_themes, langues)
	 * <pre>
 	 * Constructeur de la classe :
	 * </pre>
	 * @access public
	 * @param numeric $id_themes :tableau de th�mes
	 * @param numeric $langues : tableau de langues
	 */ 	 
    function __construct( $id_themes, $langues, $id_systemes, $code_annee='', $code_etablissement=''){ 
    	$this->conn 	      = $GLOBALS['conn'];
		$this->id_themes    = $id_themes;
		$this->langues    	= $langues;
		$this->id_systemes  = $id_systemes;

		$this->nb_themes_to_generate = count($langues)*count($id_systemes)*count($id_themes);
		$this->generer_frame($code_annee, $code_etablissement);
		
    }
	
	function get_html_buttons($langue){

		$html_buttons	=  '<BR />
							<DIV align=center id="submit_actions">
								<TABLE border="0">
									<TR>
										<TD width="30%" align="center"><input style="width:98%; text-align:center;" type="button" id="btn_save_and_prev" name="btn_save_and_prev" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('save_prev',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="Set_Element(\'save_and_prev\', \'form1\', 1); changer_action(); do_post(\'form1\');"/></TD>
										<TD width="40%" align="center"><input style="width:98%; text-align:center;" type="button" id="btn_save_only" name="btn_save_only" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('soumettre',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="javascript:changer_action(); do_post(\'form1\')"/></TD>
										<TD width="30%" align="center"><input style="width:98%; text-align:center;" type="button" id="btn_save_and_next" name="btn_save_and_next" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('save_next',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="Set_Element(\'save_and_next\', \'form1\', 1); changer_action(); do_post(\'form1\');"/></TD>
									</TR>
								</TABLE>
							</DIV>
							<INPUT type="hidden" name="switch_theme_id" id="switch_theme_id">
							<INPUT type="hidden" name="save_and_prev" id="save_and_prev">
							<INPUT type="hidden" name="save_and_next" id="save_and_next">
							';
		return($html_buttons);
	}
	function get_html_buttons_form_gril($langue){

		$html_buttons	=  '<BR />
							<DIV align=center id="submit_actions">
								<TABLE border="0">
									<TR>
										<TD width="35%" align="center"><input style="width:98%; text-align:center; color:#0000FF; font:bold; font-size:12px" type="button" id="btn_save_and_prev" name="btn_save_and_prev" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('print_current',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="export_form_curr();"/></TD>
										<TD width="30%" align="center"><input style="width:98%; text-align:center; color:#0000FF; font:bold; font-size:12px" type="button" id="btn_save_only" name="btn_save_only" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('soumettre',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="javascript:changer_action(); do_post(\'form1\')"/></TD>
										<TD width="35%" align="center"><input style="width:98%; text-align:center; color:#0000FF; font:bold; font-size:12px" type="button" id="btn_save_and_next" name="btn_save_and_next" value="&nbsp;&nbsp;'.$this->recherche_libelle_page('print_history',$langue,'generer_theme.php').'&nbsp;&nbsp;" onclick="AjusteFormExport(); MM_showHideLayers(\'form_export\',\'\',\'show\',\'export_action\',\'\',\'show\');"/></TD>
									</TR>
								</TABLE>
							</DIV>
							';
		return($html_buttons);
	}
	function js_Post_Form($id_theme, $id_systeme){
		$operateurs = array("==","<",">","<=",">=", "si");
		$requete = "
			SELECT	DICO_ZONE.ID_ZONE, DICO_ZONE.CHAMP_PERE, DICO_ZONE_SYSTEME.TYPE_OBJET, DICO_ZONE_SYSTEME.CTRL_SAISIE_OPERATEUR, DICO_ZONE_SYSTEME.CTRL_SAISIE_EXPRESSION, DICO_ZONE_SYSTEME.CTRL_SAISIE_MESSAGE
			FROM		DICO_ZONE , DICO_ZONE_SYSTEME
			WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
			AND			DICO_ZONE.ID_THEME = " . $id_theme . 
			" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = " . $id_systeme .
			" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
			AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
			AND DICO_ZONE_SYSTEME.CTRL_SAISIE_EXPRESSION <>  'NULL'
			ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";

		// Traitement Erreur Cas : GetAll / GetRow
		try {
			$ctrlList	= $GLOBALS['conn_dico']->GetAll($requete);
			if(!is_array($ctrlList)){                    
				throw new Exception('ERR_SQL');  
			} 
		}
		catch(Exception $e) {
			$erreur = new erreur_manager($e,$requete);
		}		
		
		$ctrlJsElt = 'var ctrlSaisieLst = new Array();'."\n";
		
		foreach($ctrlList as $i_elem => $element){
			$ctrlJsElt .= '				ctrlSaisieLst['.$i_elem.'] = new Array('.$element['ID_ZONE'].', "'.$element['CHAMP_PERE'].'", "'.$element['TYPE_OBJET'].'", "'.$operateurs[$element['CTRL_SAISIE_OPERATEUR']].'", "'.$element['CTRL_SAISIE_EXPRESSION'].'", "'.$element['CTRL_SAISIE_MESSAGE'].'");'."\n";
		}
				   
		$js = '<script language="javascript" type="text/javascript">
				'.$ctrlJsElt.'
				var do_submit = true;
				function do_post(form_name){
					/*requiredInputs = $(\'input[required]:not(:disabled)\');
					for(var i= 0; i < requiredInputs.length; i++)
					{
						 var cinput = requiredInputs[i];
						 if(!$(cinput).val()) {
							 return false;
						 }
					}*/
					ctrl_saisie();
					if( do_submit == true ){';
					/*if ($this->is_effectif) {
			$js .=	'		$("input[name*=NB_EFF_], input[name*=RED_]").each(function(index) {
							if ($(this).val() == "") {
								$(this).val(0);
							}
						});	';
					 }*/
			$js .= '			eval(\'document.\'+form_name+\'.submit();\');
					}
				}
				
				$(function() {
					$.each(ctrlSaisieLst, function(idx, ctrlSaisie) {
						if (ctrlSaisie[3] == "si") {
							$("FORM").find(\'input[id^="\'+ctrlSaisie[1]+\'"]\').click(function(index) {
								desactiver_zone($(this).val(), ctrlSaisie[4].split(";")[0].split(","), ctrlSaisie[4].split(";")[1]);
							});
							$("FORM").find(\'input[id^="\'+ctrlSaisie[1]+\'"]\').each(function(index) {
								if ($(this).is(":checked")) {
									desactiver_zone($(this).val(), ctrlSaisie[4].split(";")[0].split(","), ctrlSaisie[4].split(";")[1]);
								}
							});
							if($("FORM").find(\'input[id^="\'+ctrlSaisie[1]+\'"]:checked\').length == 0) {
								desactiver_zone(\'\', ctrlSaisie[4].split(";")[0].split(","), ctrlSaisie[4].split(";")[1]);
							}
						}
					});	
				});
				</script>';	
		return($js)	;			
	}
	//-------------------------------------------------------------------------
	//-------------------------------------------------------------------------
	
	function get_chaines_loc($id_systeme){
	
			$tab_chaines_loc = array();
			$all_chaines_loc = array();
			$requete        = 'SELECT CODE_TYPE_CHAINE FROM DICO_CHAINE_LOCALISATION
												 WHERE CODE_TYPE_SYSTEME_ENSEIGNEMENT='.$id_systeme.' 
												 ORDER BY ORDRE_TYPE_CHAINE';
													
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$all_chaines_loc  = $GLOBALS['conn_dico']->GetAll($requete);
					if(!is_array($all_chaines_loc)){                    
							throw new Exception('ERR_SQL');  
					} 
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow

			if( count($all_chaines_loc) == 0 ){
					$tab_chaines_loc[] = array( 'numCh' => 2, 'nbRegs' => 4 );
			}else{
					foreach( $all_chaines_loc as $i => $tab ){
							$req_niv  	=  'SELECT    COUNT(*) 
															FROM      '.$GLOBALS['PARAM']['HIERARCHIE'].'
															WHERE     '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$tab['CODE_TYPE_CHAINE'];
							
							//echo $req_niv;
							
							// Traitement Erreur Cas : Execute / GetOne
							try {            
									$niv_chaine = 	$this->conn->getOne($req_niv);
									if($niv_chaine ===false){                
											 throw new Exception('ERR_SQL');   
									}
									$tab_chaines_loc[] = array( 'numCh' => $tab['CODE_TYPE_CHAINE'], 'nbRegs' => $niv_chaine );								 
							}
							catch (Exception $e) {
									 $erreur = new erreur_manager($e,$req_niv);
							}        
							// Fin Traitement Erreur Cas : Execute / GetOne
					}
			}
			return $tab_chaines_loc;
	}

//----------------------------------------------------------------------
//----------------------------------------------------------------------
	/**
	* METHODE :  recherche_libelle_zone(id_zone,langue):
	* Permet de rechercher le LIBELLE trauit ds la langue langue
	* de la zone id_zone dans la table DICO_TRADUCTION
	*
	*<pre>
	*DEBUT
	* ... ALGO ...
	*FIN
	*</pre>
	* @access public
	* @param numeric $id_zone : la zone � traduire
	* @param string  $langue	: la langue de traduction
	* 
	*/
    function recherche_libelle_zone($id_zone,$langue){
        //Recherche du LIBELLE dans la table TRADUCTION
        $requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
                            WHERE CODE_NOMENCLATURE=".$id_zone." 
							AND NOM_TABLE='DICO_ZONE'
							AND CODE_LANGUE='".$langue."';";
        
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						return ($aresult[0]['LIBELLE'] ?? '');									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
    }

	function recherche_libelle($code,$langue,$table){
			// permet de r�cup�rer le libell� dans la table de traduction
			// en fonction de la langue et de la table  aussi
			
					// Positionnement de la connexion 
				    // Modif pour externalisation de DICO
				    if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
					 	$conn                 =   $GLOBALS['conn'];
				    } else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					 	$conn                 =   $GLOBALS['conn_dico']; 
				    } 			
					$requete 	= "SELECT LIBELLE
									FROM DICO_TRADUCTION 
									WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
									AND NOM_TABLE='".$table."'";
			
			// Traitement Erreur Cas : GetAll / GetRow
			try {
					$all_res	= $conn->GetAll($requete);
					if(!is_array($all_res)){                    
							throw new Exception('ERR_SQL');  
					} 
					return($all_res[0]['LIBELLE']);									
			}
			catch(Exception $e){
					$erreur = new erreur_manager($e,$requete);
			}
			// Fin Traitement Erreur Cas : GetAll / GetRow
	}
	
	/**
	* METHODE :  get_champ_extract($nom_champ):
	* Retourne les 30 ou 31 premiers caract�res de $nom_champ en fonction du type de SGBD 
    * pour extraire la valeur du champ $nom_champ dans un recordset
	*
	* @access public
	* 
	*/
	function get_champ_extract($nom_champ){
        $champ_extract = $nom_champ;
        if (strlen($nom_champ)>30) {
            //if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') {
			if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql' || $GLOBALS['conn']->databaseType == 'postgres9' || $GLOBALS['conn']->databaseType == 'mysql' || $GLOBALS['conn']->databaseType == 'access') {
                    $taille_max_extract=strlen($nom_champ);                
            }else if ($GLOBALS['conn']->databaseType == 'postgres9') {
					$taille_max_extract=strlen($nom_champ);
			}else{
                    $taille_max_extract=31;                
            }
            $champ_extract = substr(trim($nom_champ) ,0,$taille_max_extract); 
        }
        return($champ_extract);
	}

    function recherche_libelle_page($code,$langue,$table){
				// permet de r�cup�rer le libell� dans la table de traduction
				// en fonction de la langue et de la table  aussi
				$requete 	= "SELECT LIBELLE
										FROM DICO_LIBELLE_PAGE 
										WHERE CODE_LIBELLE='".$code."' And CODE_LANGUE='".$langue."'
										AND NOM_PAGE='".$table."'";
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$all_res	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($all_res)){                    
								throw new Exception('ERR_SQL');  
						} 
						return($all_res[0]['LIBELLE']);									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
		}
        
        function limiter_taille_text($text, $taille) {            
                if (strlen($text)>$taille){
                    $text =substr($text,0,$taille);
                }else{
                    $diff = $taille - strlen($text);
                    for ($compteur=0; $compteur<$diff; $compteur++) {
                        $text .='+';
                    }
                }
                return $text;
        }

//----------------------------------------------------------------------
//----------------------------------------------------------------------
  function requete_nomenclature_bool($champ_pere, $langue){


		$req_bool       = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_BOOLEEN.CODE_DICO_BOOLEEN AS '.$champ_pere.'
							FROM DICO_BOOLEEN ,  DICO_TRADUCTION
							WHERE DICO_BOOLEEN.CODE_DICO_BOOLEEN = DICO_TRADUCTION.CODE_NOMENCLATURE 
							AND DICO_TRADUCTION.NOM_TABLE=\'DICO_BOOLEEN\' 
							AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
							ORDER BY DICO_BOOLEEN.ORDRE_DICO_BOOLEEN';  
		try {
				$all_res	= $GLOBALS['conn_dico']->GetAll($req_bool);
				if(!is_array($all_res)){                    
						throw new Exception('ERR_SQL');  
				} 
				return $all_res;									
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$req_bool);
		}
				// Fin Traitement Erreur Cas : GetAll / GetRow
    }
	
		/**
	* METHODE :  requete_nomenclature(table_nomenclature, champ_fils, champ_pere, langue ,$id_systeme):
	* Cette fonction est utilis�e pour les champs "combos" et "liste_radio"
	* Elle retourne les libell�s d'une table de nomenclature
	* en tenant compte de l'existance �ventuelle d'une table "NOMENCLATURE"_SYSTEME
	* et la triant par ordre d'affichage (ORDRE_"NOMENCLATURE")
 	*
	*<pre>
	*DEBUT
	* ... ALGO ...
	*FIN
	*</pre>
	* @access public
	* @param string $table_nomenclature : la table de nomenclature
	* @param string $champ_fils	: le champ nomenclature � traduire
	* @param string $champ_pere : le champ correspondant au champ 
	* nomenclature dans la table m�re
	* @param string $langue	: la langue de traduction
	* 
	*/
	function requete_nomenclature($table_nomenclature, $champ_fils, $champ_pere, $langue, $id_systeme, $sql, $code_annee='', $code_etablissement=''){
		if((!preg_match('/\$code_annee/',$sql) && !preg_match('/\$code_etablissement/',$sql)) || (preg_match('/\$code_annee/',$sql) && preg_match('/\$code_etablissement/',$sql) && $code_annee<>'' && $code_etablissement<>'')){
			if( (!$champ_pere) or (trim($champ_pere) == '') ){
				$champ_pere =  $champ_fils ;
			}
			//Ajout Hebie pr rendre possible l'utilisation de nom de variables globales dans les requetes
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']} = $id_systeme;
			${$GLOBALS['PARAM']['CODE_ETABLISSEMENT']} = $code_etablissement;
			${$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']} = $code_annee;
			//Fin ajout Hebie
			$chaine_eval ="\$sql_eval =\"$sql\";";	
			eval ($chaine_eval);
			
			

			//echo '<br>#'. $sql_eval . '#<br>';		
			//Ajout HEBIE pour pouvoir afficher les options de combo dans l'ordre que l'on veut
			$pos_order_by = stripos($sql,'ORDER');
			if($pos_order_by > 0)
				$order_by = substr($sql,$pos_order_by);
			else
				$order_by = 'ORDER BY '.$table_nomenclature.'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$table_nomenclature;			
			try {            
				$all_res	= $this->conn->GetAll($sql_eval);
				if(!is_array($all_res)){              
					throw new Exception('ERR_SQL');   
				} 
				$code		=		array();
				if(is_array($all_res))
				foreach( $all_res as $i => $res) {
					$code[] = $res[$this->get_champ_extract($champ_pere)];
				}
				//Modif Hebie pour recuperation eventuelle de codes nomenclature parent
				$ColTab	=	$this->conn->MetaColumnNames($table_nomenclature);  
				$champs=array_keys($ColTab);
				$code_parent = array();
				if(count($champs)>3) {
					$champ_parent = '';
					$table_parent = '';
					foreach ($champs as $chp){            
						if ( (preg_match('/^'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE'].'_.*$/',$chp)) && ($chp <> $GLOBALS['PARAM']['CODE'].'_'.$table_nomenclature) ){               
							$champ_parent =   $chp ;
							$table_parent = substr($chp,strlen($GLOBALS['PARAM']['CODE'].'_')); 
							foreach( $all_res as $i => $res) {
								if(isset($res[$this->get_champ_extract($champ_parent)]) && $res[$this->get_champ_extract($champ_parent)]<>'' && !in_array($res[$this->get_champ_extract($champ_parent)],$code_parent)){
									$code_parent[] = $res[$this->get_champ_extract($champ_parent)];
								}
							}
							break;
						}
					}
				}
				if(count($code)>0){
					if($code_annee=='' && $code_etablissement==''){
						if(count($code_parent)>0){
							$req_nomenc_trad = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_TRADUCTION.CODE_NOMENCLATURE AS '.$champ_pere.', '.$table_nomenclature.'.'.$champ_parent.'
												FROM '.$table_nomenclature.', DICO_TRADUCTION
												WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = '.$table_nomenclature.'.'.$champ_fils.'
												AND DICO_TRADUCTION.NOM_TABLE=\''.$table_nomenclature.'\' 
												AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
												AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code).')
												 '.$order_by;  
							
							$req_parent_nomenc_trad = 'SELECT req_nomenc_trad.LIBELLE, req_nomenc_trad.'.$champ_pere.', req_nomenc_trad.'.$champ_parent.' AS CODE_PARENT, DICO_TRADUCTION.LIBELLE AS LIBELLE_PARENT
														FROM ('.$req_nomenc_trad.') AS req_nomenc_trad, DICO_TRADUCTION
														WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = req_nomenc_trad.'.$champ_parent.'
														AND DICO_TRADUCTION.NOM_TABLE=\''.$table_parent.'\' 
														AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
														AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code_parent).')
														GROUP BY req_nomenc_trad.'.$champ_parent.', DICO_TRADUCTION.LIBELLE,  req_nomenc_trad.'.$champ_pere.', req_nomenc_trad.LIBELLE';  
							$req_nomenc_trad = $req_parent_nomenc_trad;
						//Fin modif Hebie pour recuperation eventuelle de codes nomenclature parent
						}else{
							$req_nomenc_trad = 'SELECT DICO_TRADUCTION.LIBELLE, DICO_TRADUCTION.CODE_NOMENCLATURE AS '.$champ_pere.'
												FROM '.$table_nomenclature.', DICO_TRADUCTION
												WHERE DICO_TRADUCTION.CODE_NOMENCLATURE = '.$table_nomenclature.'.'.$champ_fils.'
												AND DICO_TRADUCTION.NOM_TABLE=\''.$table_nomenclature.'\' 
												AND DICO_TRADUCTION.CODE_LANGUE=\''.$langue.'\'
												AND DICO_TRADUCTION.CODE_NOMENCLATURE IN ('.implode(', ',$code).')
												 '.$order_by;
						}
					}else{
						$req_nomenc_trad = 'SELECT '.$table_nomenclature.'.'.$champ_fils.' AS LIBELLE, '.$table_nomenclature.'.'.$champ_pere.' AS '.$champ_pere.'
											FROM '.$table_nomenclature.'
											WHERE '.$table_nomenclature.'.'.$champ_pere.' IN ('.implode(', ',$code).')
											AND '.$table_nomenclature.'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$code_etablissement.'
											AND '.$table_nomenclature.'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].'='.$code_annee.'
											ORDER BY '.$table_nomenclature.'.'.$champ_pere;  
					}
					try {
						$all_res	= $this->conn->GetAll($req_nomenc_trad);
						if(!is_array($all_res)){                    
							throw new Exception('ERR_SQL');  
						} 
						return $all_res;									
					}
					catch(Exception $e){
						$erreur = new erreur_manager($e,$req_nomenc_trad);
					}
				}
			}
			catch (Exception $e) {
				$erreur = new erreur_manager($e,$sql_eval);
			}
		}else{
			echo $this->recherche_libelle_page('no_data',$langue,'generer_theme.php');
		}     
		 //Fin Traitement Erreur Cas : GetAll / GetRow
	}
	
//----------------------------------------------------------------------
//----------------------------------------------------------------------
		/**
		* METHODE :  generer_frame_matrice_2D(id_theme, langue):
		* Cette fonction est utilis�e pour g�n�rer les matrices � deux dimensions
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
    function generer_frame_matrice_2D($id_theme, $langue ,$id_systeme){	
    
        $Ligne			= -1;
        // Entete de tableau 
        $html 	  	 	= "<BR/>"; 
				
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
		$affiche_totaux_mat_2Dim 	= $this->dico[0]['AFFICHE_TOTAL'];
		$affiche_vertic_mes	= $this->dico[0]['AFFICH_VERTIC_MES'];
		
		$elements_ligne 	= $this->requete_nomenclature($this->dico[0][TABLE_REF], $this->dico[0][CHAMP], '', $langue ,$id_systeme, $this->dico[0]['DIM_SQL']);
        $elements_col 		= $this->requete_nomenclature($this->dico[1][TABLE_REF], $this->dico[1][CHAMP], '' ,$langue ,$id_systeme, $this->dico[1]['DIM_SQL']);
		$req_lib_mes        = "	SELECT DICO_TRADUCTION.LIBELLE_MESURE1,DICO_ZONE.TYPE_SAISIE_MESURE1,DICO_TRADUCTION.LIBELLE_MESURE2, DICO_ZONE.TYPE_SAISIE_MESURE2 
								FROM DICO_TRADUCTION  INNER JOIN DICO_ZONE ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_ZONE.ID_ZONE
								WHERE CODE_NOMENCLATURE=".$this->dico[0][ID_ZONE]." 
								AND NOM_TABLE='DICO_ZONE'	AND CODE_LANGUE='".$langue."';";
		// Traitement Erreur Cas : GetAll / GetRow
		try {
				$all_lib_mes 	= $GLOBALS['conn_dico']->GetAll($req_lib_mes ); 
				if(!is_array($all_lib_mes)){                    
						throw new Exception('ERR_SQL');  
				} 
				$libelle_mesure1			= $all_lib_mes[0]['LIBELLE_MESURE1'];
				$type_mesure1				= $all_lib_mes[0]['TYPE_SAISIE_MESURE1'];
				
				
				if($this->dico[0][MESURE2] <> ''){
					$libelle_mesure2	= $all_lib_mes[0]['LIBELLE_MESURE2'];
					$type_mesure2	= $all_lib_mes[0]['TYPE_SAISIE_MESURE2'];
				}
		}
		catch(Exception $e){
				$erreur = new erreur_manager($e,$req_lib_mes);
		}
		// Fin Traitement Erreur Cas : GetAll / GetRow
		
		if($affiche_totaux_mat_2Dim){
			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
			$html	.= 'var tab_mes 	= new Array ("'.$this->dico[0][MESURE1].'"';
			if ($this->dico[0][MESURE2]) {
				$html	.= ', "'.$this->dico[0][MESURE2].'"';
			}
			$html	.= ');'."\n";
			
			$html	.= 'var expression 	= "'.trim($this->dico[0]['EXPRESSION']).'";'."\n";
			$html	.= 'var tot_tab_mes = new Array();'."\n";
			$html	.= 'var tot_tab_col = new Array();'."\n";
			$html	.= 'var tab_mes_expr 	= new Array ("'.$libelle_mesure1.'"';
			if ($this->dico[0][MESURE2]) {
				$html	.= ', "'.$libelle_mesure2.'"';
			}
			$html	.= ');'."\n";
			$html	.= 'var tab_ligne	= new Array (';
			
			$html_var_li = '';
			foreach($elements_ligne as $li => $elem){
				$html_var_li	.= ',"'.$li.'"';
			}
	
			$html	.= str_replace(',"0"', '"0"' , $html_var_li).');'."\n";
			
			
			$html	.= 'var tab_col	= new Array (';
			
			$html_var_col = '';
			foreach($elements_col as $col => $elem){
				$html_var_col	.= ',"'.$col.'"';
			}
			
			$html	.= str_replace(',"0"' , '"0"' , $html_var_col).');'."\n";
				
			$html	.= "</script>"."\n";
		}


        //$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
        //$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
        $html 			.= "<TABLE class='table-questionnaire' border=1>" . '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>';
        
        // La dimension Colonne (TYPE_DIMENSION=2) est dans $this->dico[1] puisque la requete
        // sur le dico est tri� par ordre croissant du champ TYPE_DIMENSION
        // Lecture des libell�s de la table de nomenclature
		//echo '<pre>';
		//print_r($this->dico);
        // On compte le nombre de colonnes (nombre d'occurence de la nomenclature)
        $nb_col			 = count($elements_col);
        $type_saisie_mat = $this->dico[0]['TYPE_SAISIE_MATRICE'];
		
		//die('2 Dims = '.$affiche_totaux_mat_2Dim);
        
        if ($this->dico[0][MESURE2]) {
			$colspan =' COLSPAN=2 ';
		}
		
		if($affiche_totaux_mat_2Dim){
			$set_TOTAL_2Dim	= ' onchange="set_TOTAL_ThemeMat2D(\'form1\', tab_mes, tab_ligne, tab_col);" ';
			$fnc_TOTAL_2Dim	= ' set_TOTAL_ThemeMat2D(\'form1\', tab_mes, tab_ligne, tab_col)';
			if ($this->dico[0][MESURE2]) {
				$colspan_Total_2Dim = ' COLSPAN=3 ';
			}else{
				$colspan_Total_2Dim = '';
			}
		}	
				
				//echo"<br>$req_lib_mes<br>";
				if($libelle_mesure1 or $libelle_mesure2)
					$rowspan =' ROWSPAN=2 ';
				$html 			.= "\n\t<TR>\n\t\t<TD $rowspan  CLASS='ligne-titre'>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions

				// Pour chaque valeur de la dimension colonne
        foreach ($elements_col as $element){
        // On imprime le libell� des colonnes
            $html		.="\t\t<TD $colspan CLASS='ligne-titre' align='center'>";
						
						if( isset($this->dico[1]['AFF_MAT_LIB_VERTIC']) and ($this->dico[1]['AFF_MAT_LIB_VERTIC'] == 1) ) {
								if( $this->dico[1]['TAILLE_LIB_VERTICAUX'] ) $taille_max = $this->dico[1]['TAILLE_LIB_VERTICAUX']; 
								else $taille_max 		=   20;
								$libelle_afficher   =   $this->limiter_taille_text($element['LIBELLE'],$taille_max);
								$libelle_afficher		.=	'+';
								$html   .= "<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
						}else{
								$html 		.= $element['LIBELLE'];
						}
						
						$html		.="</TD>\n";

        }
		if($affiche_totaux_mat_2Dim){
			 $html		.="\t\t<TD  CLASS='ligne-titre' align='center' ".$colspan_Total_2Dim.">";
			 $html 		.= $this->recherche_libelle_page('total', $langue, 'questionnaire.php');
			 $html		.="</TD>\n";
		}
		
        $html 			.= "\n\t</TR>\n";
				
		if($libelle_mesure1 or $libelle_mesure2){
			  $html 			.= "\n\t<TR>\n";	// Zone vide d'intersection des 2 dimensions
			
			foreach ($elements_col as $element){
			// On imprime le libell� des colonnes
					$html		.="\t\t<TD  align='center'>".$libelle_mesure1."</TD>\n";
					if($this->dico[0][MESURE2] <> '')
					$html		.="\t\t<TD  align='center'>".$libelle_mesure2."</TD>\n";
			}
			if($affiche_totaux_mat_2Dim){
				$html		.="\t\t<TD  align='center'>".$libelle_mesure1."</TD>\n";
				if ($this->dico[0][MESURE2]) {
					$html		.="\t\t<TD  align='center'>".$libelle_mesure2."</TD>\n";
					if($this->dico[0]['LIB_EXPR']<>'')
						$html		.="\t\t<TD  align='center'>".trim($this->dico[0]['LIB_EXPR'])."</TD>\n";
					elseif(trim($this->dico[0]['EXPRESSION'])=='')
						$html		.="\t\t<TD  align='center'>".$libelle_mesure1.' + '.$libelle_mesure2."</TD>\n";
					else
						$html		.="\t\t<TD  align='center'>".trim($this->dico[0]['EXPRESSION'])."</TD>\n";
				}	
			}
			$html 			.= "\n\t</TR>\n";
		}
            
        // La dimension Ligne (TYPE_DIMENSION=1) est dans $this->dico[0] puisque la requete
        // sur le dico est tri� par ordre croissant du champ TYPE_DIMENSION
        // Lecture des libell�s de la table de nomenclature
        
        // Pour chaque ligne
        foreach ($elements_ligne as $element){
            $Ligne++;

			if(!isset($classe_fond)) {
				$classe_fond = 'ligne-paire';
			} else {
				if($classe_fond == 'ligne-paire') {
					$classe_fond = 'ligne-impaire';
				} else {
					$classe_fond = 'ligne-paire';
				}
			}

            $html 		        .= "\n\t<TR>\n";
					  // On imprime l'ent�te de ligne
            $html 		.= "\n\t\t<TD CLASS='".$classe_fond."'>".$element['LIBELLE']."</TD>\n";
            for ($i=0; $i<$nb_col; $i++){					
               // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
                $html		.= "\t\t<TD CLASS='".$classe_fond."'>";
                $html		.= "\t\t<INPUT TYPE= '".$type_mesure1."'";
                $html		.= " ".' ID=\''. $this->dico[0][MESURE1].'_'.$Ligne."_".$i .'\' '."NAME='".$this->dico[0][MESURE1].'_'.$Ligne."_".$i."' ";
                if ($type_mesure1=='checkbox'){
                    $html		.= "VALUE=$".$this->dico[0][MESURE1].'_'.$Ligne."_".$i.' ';
                }else{
                    $html		.= "VALUE='$".$this->dico[0][MESURE1].'_'.$Ligne."_".$i."' ".$set_TOTAL_2Dim;
                }
                if ($this->dico[0][ATTRIB_OBJET] != ''){
                    //	Affichage des attributs (SIZE, MAXLENGTH,...)
                    $html 		.=$this->dico[0][ATTRIB_OBJET];
                }
                $html		.= ">\n";
                $html		.= "</TD>\n";
                if ($this->dico[0][MESURE2] != ''){
                    $html		.= "\t\t<TD CLASS='".$classe_fond."'>";
                    $html		.= "\t\t<INPUT TYPE= '".$type_mesure2."'" ;
                    $html		.= " ".' ID=\''. $this->dico[0][MESURE2].'_'.$Ligne."_".$i .'\' '."NAME='".$this->dico[0][MESURE2].'_'.$Ligne."_".$i."' ";
                if ($type_mesure2=='checkbox'){
                    $html		.= "VALUE=$".$this->dico[0][MESURE2].'_'.$Ligne."_".$i.' ';
                }else{
                    $html		.= "VALUE='$".$this->dico[0][MESURE2].'_'.$Ligne."_".$i."' ".$set_TOTAL_2Dim;
                }
                    if ($this->dico[0][ATTRIB_OBJET] != ''){
                        //	Affichage des attributs (SIZE, MAXLENGTH,...)
                        $html 		.=$this->dico[0][ATTRIB_OBJET];
                    }
                        $html		.= ">\n";
                        ///////// fin type saisie =text
                    $html	        .= "</TD>\n";
                }
            }
			
			if($affiche_totaux_mat_2Dim){
				$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE1]."_LIGNE_".$Ligne."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				$html	.= "<script language='javascript' type='text/javascript'>"."\n";
				$html	.= 'tot_tab_mes['.$Ligne.']	= new Array();'."\n";
				$html	.= 'tot_tab_mes['.$Ligne.'][0] 	= "TOT_'.$this->dico[0][MESURE1].'_LIGNE_'.$Ligne.'";'."\n";
				$html	.= "</script>"."\n";
			
				if ($this->dico[0][MESURE2]) {
					$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE2]."_LIGNE_".$Ligne."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
					$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_ALL_MES_LIGNE_".$Ligne."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
					$html	.= "<script language='javascript' type='text/javascript'>"."\n";
					$html	.= 'tot_tab_mes['.$Ligne.'][1] 	= "TOT_'.$this->dico[0][MESURE2].'_LIGNE_'.$Ligne.'";'."\n";
					$html	.= "</script>"."\n";
				}	
			}
            $html 		        .= "\n\t</TR>\n";
        }
		
		if($affiche_totaux_mat_2Dim){
			if ($this->dico[0][MESURE2]){
				$html 		    .= "\n\t<TR>\n";
				$html 		    .= "\n\t<TD align='center' rowspan='2' bgcolor='#CCCCCC'><br>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
				
				for ($col=0; $col < $nb_col; $col++){
					$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE1]."_COLONNE_".$col."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
					$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE2]."_COLONNE_".$col."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				}
				$html		.="\t\t<td align='center' rowspan='2' bgcolor='#CCCCCC'  style='vertical-align:middle'><br>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE1]."_COLONNE' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				$html	.= "<script language='javascript' type='text/javascript'>"."\n";
				$html	.= 'tot_tab_col[0]	= "TOT_'.$this->dico[0][MESURE1].'_COLONNE";'."\n";
				$html	.= "</script>"."\n";
				$html		.="\t\t<td align='center' rowspan='2' bgcolor='#CCCCCC'  style='vertical-align:middle'><br>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$this->dico[0][MESURE2]."_COLONNE' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				$html	.= "<script language='javascript' type='text/javascript'>"."\n";
				$html	.= 'tot_tab_col[1]	= "TOT_'.$this->dico[0][MESURE2].'_COLONNE";'."\n";
				$html	.= "</script>"."\n";
				$html		.="\t\t<td align='center' rowspan='2' bgcolor='#CCCCCC'  style='vertical-align:middle'><br>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_ALL_MES' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";

				$html 		    .="\n\t</TR>\n";
				$html 		    .= "\n\t<TR>\n";
				for ($col = 0; $col < $nb_col; $col++){
					$html		.="\t\t<TD align='center' colspan='2' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='width:100%; background-color:#CCCCCC; font-weight:bold' NAME='TOT_ALL_MES_COLONNE_".$col."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				}
				
				$html 		    .="\n\t</TR>\n";
			}else{
				$html 		    .= "\n\t<TR>\n";
            	$html 			.= "\n\t\t<TD align='center' bgcolor='#CCCCCC'>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
				for ($col = 0; $col < $nb_col; $col++){
					$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_ALL_MES_COLONNE_".$col."' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				}
				$html			.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_ALL_MES' ".$this->dico[0]['ATTRIB_OBJET']."></TD>\n";
				$html 		    .="\n\t</TR>\n";
			}	
		}
        $html		.= "\n</TABLE>";
		
		$html		.= "\n" . $this->get_html_buttons($langue);
	
        $html 	.= "</FORM>";
		$html	.= "</div>"."\n";
		if($affiche_totaux_mat_2Dim){
			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
			$html	.= $fnc_TOTAL_2Dim . "\n";
			$html	.= "</script>"."\n";
		}
		
	

        //$html 			.= "\n</Form>";

        //print '<BR>'.$this->dico[0]['FRAME'];
        // Cr�ation du fichier frame
				//echo '$fdgfdfgfgdf='.$element['FRAME'] ;
                //echo '<pre>';
                //print_r($element);
                if (trim($this->dico[0]['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'], $html);
						echo $html;
				}else{
                        if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'] . ' : Incorrect !<BR>';
				}
    	//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'], $html);	
        //echo $html;

    }//	Fin generer_frame_matrice_2D

//----------------------------------------------------------------------
//----------------------------------------------------------------------
		/**
		* METHODE :  generer_frame_matrice_1D(id_theme, langue):
		* Cette fonction est utilis�e pour g�n�rer les matrices � une dimension
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/

    function generer_frame_matrice_1D($id_theme, $langue ,$id_systeme){	
        
   	// (Ligne par ligne)
        $html 	  	 = "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
							
        //$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
        //$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
        $html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
        $html 			.= "<TABLE class='table-questionnaire' border=1>" . '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>';
				$affiche_totaux_mat_c = $this->dico[0]['AFFICHE_TOTAL'];
				$affiche_vertic_mes	= $this->dico[0]['AFFICH_VERTIC_MES'];
				$tab_mesures = array();
				$tab_mesures[] = $this->dico[0]['MESURE1'];
				if($this->dico[0]['MESURE2']<>'')
						$tab_mesures[] = $this->dico[0]['MESURE2'];
				
				if($affiche_totaux_mat_c){		
						$html.= "\t <script language='javascript' type='text/javascript'>\n";
						$html.= "\t <!--\n";
							$html.= "\t\t function Calcul_Total(mesure){\n";
								$html.= "\t\t\t var tab_champ_op = new Array();\n";
								$html.= "\t\t\t var chaine_eval;\n";
								$html.= "\t\t\t var total = 0;\n";
							  $nb_code		=	-1;
							 //$nb_champs	=	-1;
							  $codes = $this->requete_nomenclature($this->dico[0][TABLE_REF], $this->dico[0][CHAMP], '', $langue ,$id_systeme, $this->dico[0]['DIM_SQL']);
							 //	foreach ($tab_mesures as $champ_mesure){
									 foreach($codes as $code){
											$nb_code++;
											$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = parseInt(document.form1.'+mesure+'_".$nb_code.".value);';\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										} 
								//}
								$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
									$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
										$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
										$html.= "\t\t\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t\t\t	}\n";
								$html.= "\t\t\t }\n";
								$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+mesure+'.value='+total+';';\n";
								$html.= "\t\t\t eval(chaine_eval);\n";
							$html.= "\t\t }\n";
						$html.= "\t -->\n";
						$html.= "\t </script>\n";
				}
        if ($this->dico[0][TYPE_DIMENSION] == 2){	//	Dimension Colonne

            // TODO: il reste toujours � r�gler le probl�me du syst�me d'enseignement
            // DIM_SQL et DIM_LIBELLE ne sont donc plus utilis�s
            /*$sql_col            = "SELECT D_TRAD.LIBELLE
                                    FROM ".$this->dico[0][TABLE_REF]." AS NOM,  DICO_TRADUCTION AS D_TRAD
                                    WHERE NOM.CODE_LIBELLE = D_TRAD.CODE_LIBELLE 
                                    And D_TRAD.NOM_TABLE='".$this->dico[0][TABLE_REF]."' 
                                    And D_TRAD.CODE_LANGUE='".$langue."';";
            $elements_col	= $this->conn->GetAll($sql_col);*/
            // Lecture des libell�s de la table de nomenclature
            $elements_col = $this->requete_nomenclature($this->dico[0][TABLE_REF], $this->dico[0][CHAMP], '', $langue ,$id_systeme, $this->dico[0]['DIM_SQL']);
						
						$req_lib_mes        = "SELECT  DICO_TRADUCTION.LIBELLE_MESURE1,DICO_ZONE.TYPE_SAISIE_MESURE1,DICO_TRADUCTION.LIBELLE_MESURE2, DICO_ZONE.TYPE_SAISIE_MESURE2 
                                        FROM DICO_TRADUCTION  INNER JOIN DICO_ZONE ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_ZONE.ID_ZONE
										WHERE CODE_NOMENCLATURE=".$this->dico[0][ID_ZONE]." 
										AND NOM_TABLE='DICO_ZONE'	AND CODE_LANGUE='".$langue."';";
						
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$all_lib_mes 	= $GLOBALS['conn_dico']->GetAll($req_lib_mes ); 
								if(!is_array($all_lib_mes)){                    
										throw new Exception('ERR_SQL');  
								} 
								$libelle_mesure1	= $all_lib_mes[0]['LIBELLE_MESURE1'];									
						        $type_mesure1	= $all_lib_mes[0]['TYPE_SAISIE_MESURE1'];
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$req_lib_mes);
						}
						// Fin Traitement Erreur Cas : GetAll / GetRow
						
						if($this->dico[0][MESURE2] <> '') {
							$libelle_mesure2	= $all_lib_mes[0]['LIBELLE_MESURE2'];
                            $type_mesure2	= $all_lib_mes[0]['TYPE_SAISIE_MESURE2'];
                        }
						$ligne = 0;
						
						if($libelle_mesure1 or $libelle_mesure2){
								$html 		.= "\n\t<TR>";
								$html			.="\n\t\t<TD CLASS='ligne-impaire'>&nbsp;</TD>\n";
								$html			.="\n\t\t<TD CLASS='ligne-impaire' align='center'>$libelle_mesure1</TD>\n";
								if($this->dico[0][MESURE2] <> '')
									$html		.="\n\t\t<TD CLASS='ligne-impaire'>$libelle_mesure2</TD>\n";	
								
								$html 		.= "\n\t</TR>";
						}
						
            foreach ($elements_col as $element){

							if(!isset($classe_fond)) {
									$classe_fond = 'ligne-paire';
							} else {
									if($classe_fond == 'ligne-paire') {
											$classe_fond = 'ligne-impaire';
									} else {
											$classe_fond = 'ligne-paire';
									}
							}

								//$html 		.= "\n\t<TR CLASS='$classe' >";
                $html 		.= "\n\t<TR>";
								
								//libellle
							  $html			.="\n\t\t<TD CLASS='".$classe_fond."'  align='center'>".$element['LIBELLE']."</TD>\n";
                
								$fonction_somme ='';
								if($affiche_totaux_mat_c){
									$fonction_somme = " onBlur=Calcul_Total('".$this->dico[0][MESURE1]."'); ";
								}

								// zone de saisie de la mesure1
								$html		.= "\t\t<TD class='".$classe_fond."'>";
                                $html		.= "<INPUT TYPE= '".$type_mesure1."'";
                                $html		.= " ".' ID=\''. $this->dico[0][MESURE1].'_'.$ligne .'\' '."NAME='".$this->dico[0][MESURE1].'_'.$ligne."' ";
                                if ($type_mesure1 =='checkbox'){
                                    $html		.= "VALUE=$".$this->dico[0][MESURE1].'_'.$ligne."  ";
                                }else{
                                    $html		.= "VALUE='$".$this->dico[0][MESURE1].'_'.$ligne."' ";
                                }
                                if ($this->dico[0][ATTRIB_OBJET] != ''){
                                // Affichage des attributs (SIZE, MAXLENGTH,...)
                                $html 	.=$this->dico[0][ATTRIB_OBJET];
                                }
                                $html		.= $fonction_somme.'>';
                                $html		.= "</TD>\n";
								
								if( $this->dico[0][MESURE2] <> '' ){
								//echo 'BOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOOONNNNNNNNNNNNNNNNNnn';
									// zone de saisie de la mesure2
                                    $fonction_somme ='';
                                    if($affiche_totaux_mat_c){
                                            $fonction_somme = " onBlur=Calcul_Total('".$this->dico[0][MESURE2]."'); ";
                                    }
									$html		.= "\t\t<TD class='".$classe_fond."'> ";
                                    /////debut text
                                    $html		.= "<INPUT TYPE='".$type_mesure2."'";
                                    $html		.= " ".' ID=\''. $this->dico[0][MESURE2].'_'.$ligne .'\' '."NAME='".$this->dico[0][MESURE2].'_'.$ligne."' ";
                                    if ($type_mesure2=='checkbox'){
                                        $html		.= "VALUE=$".$this->dico[0][MESURE2].'_'.$ligne." ";
                                    }else{
                                        $html		.= "VALUE='$".$this->dico[0][MESURE2].'_'.$ligne."' ";
                                    }
                                    if ($this->dico[0][ATTRIB_OBJET] != ''){
                                            // Affichage des attributs (SIZE, MAXLENGTH,...)
                                            $html 	.=$this->dico[0][ATTRIB_OBJET];
                                    }
                                    $html		.= "$fonction_somme>";
									$html		.= "</TD>\n";
								}
								
            		$html		  .="\t</TR>\n";
								
								$ligne++;
						}

            if($affiche_totaux_mat_c){

                if($classe_fond == 'ligne-paire') {
                    $classe_fond = 'ligne-impaire';
                } else {
                    $classe_fond = 'ligne-paire';
                }

                $html 		.= "\n\t<TR>";
	
                                $html			.= "\n\t\t<TD class='".$classe_fond."'>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</TD>\n";
                                $html			.= "\t\t<TD class='".$classe_fond."'> ";
                                $html			.= "<INPUT TYPE='".$type_mesure1."'";
                                $html			.= " NAME='TOTAL_".$this->dico[0][MESURE1]."' ";
                                $html			.= "VALUE='' ";
                                $html 		.= $this->dico[0][ATTRIB_OBJET];
                                $html			.= ">";
                                $html			.= "</TD>\n";
								if( $this->dico[0][MESURE2] <> '' ){
                                    $html			.= "\t\t<TD class='".$classe_fond."'> ";
                                    $html			.= "<INPUT TYPE= '".$type_mesure2."'";
                                    $html			.= " NAME='TOTAL_".$this->dico[0][MESURE2]."' ";
                                    $html			.= "VALUE='' ";
                                    $html 		.= $this->dico[0][ATTRIB_OBJET];
                                    $html			.= ">";
                                    $html			.= "</TD>\n";
								}
								
            		$html		  .="\t</TR>\n";
						
								$html.= "\t <script language='javascript' type='text/javascript'>\n";
								$html.= "\t <!--\n";
								foreach($tab_mesures as $mesure){
										$html.= "\t Calcul_Total('$mesure');\n";
								}
								$html.= "\t -->\n";
								$html.= "\t </script>\n";
						}
        }    

        if ($this->dico[0][TYPE_DIMENSION] == 1) {	// Dimension Ligne
            // TODO: il reste toujours � r�gler le probl�me du syst�me d'enseignement
            // DIM_SQL et DIM_LIBELLE ne sont donc plus utilis�s
            /*$sql_ligne            = "SELECT D_TRAD.LIBELLE
                                    FROM ".$this->dico[0][TABLE_REF]." AS NOM,  DICO_TRADUCTION AS D_TRAD
                                    WHERE NOM.CODE_LIBELLE = D_TRAD.CODE_LIBELLE 
                                    And D_TRAD.NOM_TABLE='".$this->dico[0][TABLE_REF]."' 
                                    And D_TRAD.CODE_LANGUE='".$langue."';";
            $elements_ligne	= $this->conn->GetAll($sql_ligne);*/
            // Lecture des libell�s de la table de nomenclature
            $elements_ligne 	= $this->requete_nomenclature($this->dico[0][TABLE_REF], $this->dico[0][CHAMP], '', $langue ,$id_systeme, $this->dico[0]['DIM_SQL']);
            $nb_elements_ligne = count($elements_ligne);
            $req_lib_mes        = "	SELECT  DICO_TRADUCTION.LIBELLE_MESURE1,DICO_ZONE.TYPE_SAISIE_MESURE1,DICO_TRADUCTION.LIBELLE_MESURE2, DICO_ZONE.TYPE_SAISIE_MESURE2 
                                    FROM DICO_TRADUCTION  INNER JOIN DICO_ZONE ON DICO_TRADUCTION.CODE_NOMENCLATURE = DICO_ZONE.ID_ZONE
                                    WHERE CODE_NOMENCLATURE=".$this->dico[0][ID_ZONE]." 
                                    AND NOM_TABLE='DICO_ZONE'	AND CODE_LANGUE='".$langue."';";
                        // Traitement Erreur Cas : GetAll / GetRow
						try {
								$all_lib_mes 	= $GLOBALS['conn_dico']->GetAll($req_lib_mes );
								if(!is_array($all_lib_mes)){                    
										throw new Exception('ERR_SQL');  
								} 
								$libelle_mesure1	= $all_lib_mes[0]['LIBELLE_MESURE1'];
                                $type_mesure1	= $all_lib_mes[0]['TYPE_SAISIE_MESURE1'];
								if($this->dico[0][MESURE2] <> ''){
									$libelle_mesure2	= $all_lib_mes[0]['LIBELLE_MESURE2'];
                                    $type_mesure2	= $all_lib_mes[0]['TYPE_SAISIE_MESURE2'];
                                }
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$req_lib_mes);
						}
						// Fin Traitement Erreur Cas : GetAll / GetRow
            // Cr�ation d'un tableau 1D sur plusieurs colonnes
            if ($this->dico[0][NB_LIGNE]>1){
            $html 		.= "\n\t<TR>";
                $nb_ligne_colonne	= $nb_elements_ligne / $this->dico[0][NB_LIGNE];
                for ($Ligne=0; $Ligne<$this->dico[0][NB_LIGNE]; $Ligne++){
                    // Pour chaque ligne
                    for ($i=$Ligne; $i<$nb_elements_ligne; $i+=$this->dico[0][NB_LIGNE]){
                        // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
                        $html 		.= "\n\t\t<TD>";
                        $html 		.= "\n\t\t<TD  align='center'>".$elements_ligne[$i]['LIBELLE']."</TD>\n";
                        //$html 		.= $elements_ligne[$i]['LIBELLE'];
                        $html 		.= "</TD>\n";
                        $html		.= "\t\t<TD> ";
                        $html		.= "<INPUT TYPE='".$type_mesure1."'";
                        $html		.= " ".' ID=\''. $this->dico[0][MESURE1]."_".$Ligne .'\' '."NAME='".$this->dico[0][MESURE1]."_".$Ligne."' ";
                        if ($type_mesure1 == 'checkbox'){
                            $html		.= "VALUE=$".$this->dico[0][MESURE1]."_".$Ligne." ";
                        }else{
                            $html		.= "VALUE='$".$this->dico[0][MESURE1]."_".$Ligne."' ";
                        }
                        if ($this->dico[0][ATTRIB_OBJET] != ''){
                            // Affichage des attributs (SIZE, MAXLENGTH,...)
                            $html 	.=$this->dico[0][ATTRIB_OBJET];
										    }
                        $html		.= ">";
                        $html		.= "</TD>\n";
                    }
                    $html 			.= "\n\t</TR>\n\t<TR>";
                }
            }
            else{
                $colonne = 0;
                $html 		.= "\n\t<TR>";
                $pass = -1 ;
                if( $this->dico[0][MESURE2] != '')  $colspan ='COLSPAN=2 ';	
                foreach ($elements_ligne as $element){
											$pass++;
										  if( ($pass%2)==0 ) $classe ='ligne-titre'; //$classe ='#FFCCFF';
											else $classe ='ligne-paire';	
								
									 // On pr�pare l'ent�te de ligne

									$html 		.= "\n\t\t<TD $colspan  CLASS='$classe' >";

									if( isset($this->dico[0]['AFF_MAT_LIB_VERTIC']) and ($this->dico[0]['AFF_MAT_LIB_VERTIC'] == 1) ) {
											if( $this->dico[0]['TAILLE_LIB_VERTICAUX'] ) $taille_max = $this->dico[0]['TAILLE_LIB_VERTICAUX']; 
											else $taille_max =   20;
											$libelle_afficher   =   $this->limiter_taille_text($element['LIBELLE'],$taille_max);
											$libelle_afficher.='+';
											$html   .= "<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
									}else{
											$html 		.= $element['LIBELLE'];
									}
									
									$html 		.= "</TD>\n";
								}
								$html 		.= "\n\t</TR>";
								if($libelle_mesure1 or $libelle_mesure2){
										$html 		.= "\n\t<TR>";
										
										foreach ($elements_ligne as $element){
												 // On pr�pare l'ent�te de ligne
											$html			.="\n\t\t<TD CLASS='fond_gris'>$libelle_mesure1</TD>\n";
											if( $this->dico[0][MESURE2] != '')
												$html			.="\n\t\t<TD CLASS='fond_gris'>$libelle_mesure2</TD>\n";	
										}
										$html 		.= "\n\t</TR>";
								}

								$html 		.= "\n\t<TR>";
								foreach ($elements_ligne as $element){
                    // Pour chaque colonne, on imprime la (ou les) zone(s) de texte pour mesure1
                    $html		.= "\t\t<TD CLASS='ligne-paire'>";
                    $html		.= "<INPUT TYPE=".$type_mesure1	;
                    $html		.= " ".' ID=\''. $this->dico[0][MESURE1].'_'.$colonne .'\' '."NAME='".$this->dico[0][MESURE1].'_'.$colonne."' ";
                    if ($type_mesure1	=='checkbox'){
                        $html		.= "VALUE=$".$this->dico[0][MESURE1].'_'.$colonne.' ';
                    }else{
                        $html		.= "VALUE='$".$this->dico[0][MESURE1].'_'.$colonne."' ";
                    }
                    if ($this->dico[0][ATTRIB_OBJET] != ''){
                        // Affichage des attributs (SIZE, MAXLENGTH,...)
                        $html 	.=$this->dico[0][ATTRIB_OBJET];
                    }
                    $html		.= ">";
                    $html		.= "</TD>\n";
                    if( $this->dico[0][MESURE2] != ''){
                        //echo "<br>m".$this->dico[0][MESURE2]."m<br>";
                        // Pour chaque colonne, on imprime la (ou les) zone(s) de texte pour mesure2
                        $html		.= "\t\t<TD CLASS='ligne-paire'>";
                        $html		.= "<INPUT TYPE='".$type_mesure2."'";
                        $html		.= " ".' ID=\''. $this->dico[0][MESURE2].'_'.$colonne .'\' '."NAME='".$this->dico[0][MESURE2].'_'.$colonne."' ";
                        if ($type_mesure2	=='checkbox'){
                            $html		.= "VALUE=$".$this->dico[0][MESURE2].'_'.$colonne.' ';
                        }else{
                            $html		.= "VALUE='$".$this->dico[0][MESURE2].'_'.$colonne."' ";
                        }
                            if ($this->dico[0][ATTRIB_OBJET] != ''){
                                    // Affichage des attributs (SIZE, MAXLENGTH,...)
                                    $html 	.=$this->dico[0][ATTRIB_OBJET];
                            }
                        $html		.= ">";
                        $html		.= "</TD>\n";
                    }// 
																				
										$colonne++;
                } // fin foreach
								$html 		.= "\n\t</TR>";
            } // fin else :
        }
        $html		.= "\n</TABLE>";
		
		$html		.= "\n" . $this->get_html_buttons($langue);
		
        $html 	.= "</FORM>";
		$html	.= "</div>"."\n";

        //$html 			.= "\n</Form>";
        
        //print '<BR>affichage du template= '.$this->dico[0]['FRAME'];
        
        //file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'], $html);	
                if (trim($this->dico[0]['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'], $html);
						echo $html;
				}else{
                        if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$this->dico[0]['FRAME'] . ' : Incorrect !<BR>';
				}
				//echo $html;
        
    }//	Fin generer_frame_matrice_1D

//----------------------------------------------------------------------
//----------------------------------------------------------------------
		/**
		* METHODE :  generer_frame_matrice(id_theme, langue):
		* Cette fonction identifie le type de matrice 
		* et appele la fonction de g�naration correspondante 
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
    function generer_frame_matrice($id_theme, $langue ,$id_systeme){
							$requete	="	
													SELECT			DICO_DIMENSION.*,			
																			DICO_DIMENSION_ZONE.TYPE_DIMENSION, 
																			DICO_DIMENSION_ZONE.NB_LIGNES_DIMENSION, 
																			DICO_ZONE.ID_ZONE,
																			DICO_ZONE.MESURE1, 
																			DICO_ZONE.MESURE2, 
																			DICO_ZONE_SYSTEME.ATTRIB_OBJET, 
																			DICO_ZONE_SYSTEME.AFFICHE_TOTAL, 
																			DICO_ZONE_SYSTEME.EXPRESSION,
																			DICO_ZONE_SYSTEME.LIB_EXPR,
																			DICO_ZONE_SYSTEME.AFFICH_VERTIC_MES,
																			DICO_THEME.ACTION_THEME AS ACTION_POST, 
																			DICO_THEME_SYSTEME.*
													FROM				DICO_DIMENSION, DICO_DIMENSION_ZONE, DICO_ZONE, DICO_THEME, DICO_THEME_SYSTEME, DICO_ZONE_SYSTEME
													
													WHERE 			DICO_ZONE.ID_THEME = DICO_THEME.ID 
													AND					DICO_THEME.ID = DICO_THEME_SYSTEME.ID 
													AND					DICO_ZONE.ID_ZONE = DICO_DIMENSION_ZONE.ID_ZONE 
													AND					DICO_DIMENSION.ID_DIMENSION = DICO_DIMENSION_ZONE.ID_DIMENSION 
													AND					DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE
													AND		     	DICO_THEME.ID = ".$id_theme."
													AND 				DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme."
													AND 				DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme."
													AND         DICO_ZONE_SYSTEME.ACTIVER = 1
													ORDER BY 		DICO_DIMENSION_ZONE.TYPE_DIMENSION";
				//echo "<br>---$requete---<br>";
                
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

                /*echo '<pre>';
                print_r($aresult);
                 echo '</pre>';
                 die();*/

        switch (count($this->dico)){
            case 1 :
                $this->generer_frame_matrice_1D($id_theme, $langue ,$id_systeme);
                break;
            case 2 :
                $this->generer_frame_matrice_2D($id_theme, $langue ,$id_systeme);
                break;
            default :
                print 'Attention! Dico';
                break;
        }
    }//	Fin generer_frame_matrice

//----------------------------------------------------------------------
//----------------------------------------------------------------------
		/**
		* METHODE :  tri_fils(dico):
		* Cette fonction permet de trier les zones suivant 
		* la'ordre de pr�c�dence attribu� aux objets d'affichage
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param array $dico : variable dico � trier
		* 
		*/

    function tri_fils($dico) {
    	/**
         * Trie les elements fils d'un tableau (dico) selon l'ordre de
         * pr�cedence
         */
				//echo 'dico<br><pre>';
				//print_r($dico);
 				/*
        $dico_trie = array();
				
        foreach ($dico as $row){
            //print_r($row);
            if ($row[PRECEDENT_ZONE] == 0){ // On a trouv� le premier
                array_push ($dico_trie, $row);
                break;
            }
        }
        $nb_dico = count($dico);
        for ($i=0; $i<$nb_dico; $i++)
        {
            foreach ($dico as $row){
                if ($row[PRECEDENT_ZONE] == $dico_trie[$i][ID_ZONE]){ // On a trouv� le premier
                    array_push ($dico_trie, $row);
                    break;
                }
            }
        }
				//echo 'dico_trie<br><pre>';
				//print_r($dico_trie);
        //return $dico_trie;
				*/
				
        /*foreach ($dico as $row){
            //print_r($row);
            if (startsWith($row['CHAMP_PERE'], "NB_EFF") || startsWith($row['CHAMP_PERE'], "RED_")) { //Cas scepifique Madagascar
                $this->is_effectif = true;
            }
        }*/
		return $dico ;
    }
        

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		function formulaire_concat_zone_html($element,$langue,$id_systeme){
				$html ='';
				static $deja_aff_sys = array(); // permet de ne pas repeter un objet de type cl� � l'affichage
				// c'est le cas de CODE_REGROUPEMENT(type:systeme_valeur_multiple)ds ENV_SOCIO et ds ECONOMIE_ENV_SOCIO
				if($element['TYPE_OBJET']=='booleen'){

						$html.= $GLOBALS['libelle_oui'].'&nbsp;';
						$html.= "<INPUT NAME='".$element['CHAMP_PERE']."_0"."' TYPE='radio' ".$element['ATTRIB_OBJET']." ";
						$html.= "".' ID=\''. $element['CHAMP_PERE']."_0_1" .'\' '."VALUE=$".$element['CHAMP_PERE']."_0_1".">\n";
						
						$html.= "\t\t&nbsp;&nbsp;&nbsp;&nbsp;";
						$html.= $GLOBALS['libelle_non'].'&nbsp;';
						$html.= "<INPUT NAME='".$element['CHAMP_PERE']."_0"."' TYPE='radio' ".$element['ATTRIB_OBJET']." ";
						$html.= "".' ID=\''. $element['CHAMP_PERE']."_0_0" .'\' '."VALUE=$".$element['CHAMP_PERE']."_0_0".">\n";
						
				}
				elseif($element['TYPE_OBJET']=='checkbox'){
						//////////////////////
						
						$html.= "<INPUT NAME='".$element['CHAMP_PERE']."_0"."' TYPE='checkbox' ".$element['ATTRIB_OBJET']." ";
						$html.= "".' ID=\''. $element['CHAMP_PERE']."_0" .'\' '."VALUE=$".$element['CHAMP_PERE']."_0".">\n";
						//////////////////////
				}
				elseif($element['TYPE_OBJET']=='text'){
						//////////////////////
						if($element['AFFICHE_TOTAL'] && $element['EXPRESSION']<>''){
							$TOTAL_ChpsFrml 	= ' onchange="set_Total_ChpsFrml(\'form1\',\''.$element['EXPRESSION'].'\');" ';
						}else{
							$TOTAL_ChpsFrml = '';
						}
						
						$html.= "<INPUT NAME='".$element['CHAMP_PERE']."_0"."' TYPE='text' ".$element['ATTRIB_OBJET']." ";
						$html.= "".' ID=\''. $element['CHAMP_PERE']."_0" .'\' '."VALUE=\"\$".$element['CHAMP_PERE']."_0"."\" $TOTAL_ChpsFrml>\n";
						
						//////////////////////
				}
				elseif($element['TYPE_OBJET']=='label'){
						//////////////////////
						$html.= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);						
						//////////////////////
				}
				elseif($element['TYPE_OBJET']=='combo'){
						//////////////////////
							
						$html		.= "\t\t<SELECT ".$element['ATTRIB_OBJET']."".' ID=\''. $element['CHAMP_PERE']."_0" .'\' '." NAME=".$element['CHAMP_PERE']."_0".">\n";
						$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n";
						
						$dynamic_content_object = false ;
						if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
							//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
								$dynamic_content_object = true ;
							//}
						}
						if($dynamic_content_object == true){
							$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_0'.'-->'."\n"; 
						}else{
							$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
							foreach($result_nomenc as $element_result_nomenc){
									//Un valeur du combo pour chaque valeur de la nomenclature 
									$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_0_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
									// Lecture des libell�s de la table de nomenclature
					
									$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
							}
						}
						
						//$html   .= ">";
						$html 	.= "\t\t</SELECT>\n";
						//////////////////////
				}
				elseif( $element['TYPE_OBJET'] == 'systeme_valeur_unique' and !isset($deja_aff_sys[$element['CHAMP_PERE']]) ){
						/////////// caracteristiques systeme d�j� 
						$deja_aff_sys[$element['CHAMP_PERE']] = 1;
						///////////////// 
						if( $element['BOUTON_INTERFACE']=='popup' ){
								if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
										$html .= "<INPUT ID='".$element['CHAMP_PERE']."_0' name='".$element['CHAMP_PERE']."_0' type='hidden' value='$".$element['CHAMP_PERE']."0' >"; 
									$html .= "<INPUT name='".$element['CHAMP_INTERFACE']."_0' type='text' ".$element['ATTRIB_OBJET']."
																value=\"$".$element['CHAMP_INTERFACE']."_0\" readonly> ";
								}
								else{
										$html .= "<INPUT name='".$element['CHAMP_PERE']."_0' type='text' ".$element['ATTRIB_OBJET']."
															 value=\"$".$element['CHAMP_PERE']."_0\" readonly> ";
								}
								$fonction	=	str_replace("\$ligne",'0',$element['FONCTION_INTERFACE']);
								$html .= "<input type='button' onClick=".$fonction."; value='...'>";
		
						}
						else{
									$html.= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_0" .'\' '."NAME='".$element['CHAMP_PERE']."_0"."' TYPE='text' ".$element['ATTRIB_OBJET']." ";
									$html.= "VALUE=$".$element['CHAMP_PERE']."_0".">\n";
						}
				}elseif($element['TYPE_OBJET']=='hidden_field'){//Ajout HEBIE pour gestion champs cach�s
						$html.= "<INPUT NAME='".$element['CHAMP_PERE']."_0"."' TYPE='hidden' ";
						$html.= "".' ID=\''. $element['CHAMP_PERE']."_0" .'\' ';
						if($element['VALEUR_CONSTANTE']<>'')
							$html.="VALUE=\"".$element['VALEUR_CONSTANTE']."\"/>\n";
						else
							$html.="VALUE=\"\$".$element['CHAMP_PERE']."_0"."\"/>\n";
				}
				return $html ;
		}

		
		function get_cell_matrice($ligne ,$code_dims, $element, $fonc_total, $langue, $id_systeme){
			
				if (!isset($GLOBALS['cell_mat_index'])) $GLOBALS['cell_mat_index'] = 0; // PHP8 fix S17g
				$html 		= '';

				if($element['TYPE_OBJET']=='booleen'){

						$html.= $GLOBALS['libelle_oui'];
						$html.= "<INPUT".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims."_1" .'\' '." NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='radio' ".$element['ATTRIB_OBJET']." ";
						$html.= "VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_1 tabindex='".$GLOBALS['cell_mat_index']."'>\n";
						$html.= "&nbsp;";
						$html.= $GLOBALS['libelle_non'];
						$html.= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims."_0" .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='radio' ".$element['ATTRIB_OBJET']." ";
						$html.= "VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_0 tabindex='".$GLOBALS['cell_mat_index']."'>\n";
						
				}
				elseif($element['TYPE_OBJET']=='checkbox'){
						//////////////////////
						
						$html.= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='checkbox' ".$element['ATTRIB_OBJET']." ";
						$html.= "VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims." tabindex='".$GLOBALS['cell_mat_index']."'>\n";
						//////////////////////
				}
				elseif($element['TYPE_OBJET']=='text'){
						//////////////////////
	
						$html.= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='text' ".$element['ATTRIB_OBJET']." ";
						$html.= "VALUE=\"\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."\" ".$fonc_total." tabindex='".$GLOBALS['cell_mat_index']."'>\n";
						
						//////////////////////
				}
				elseif($element['TYPE_OBJET']=='combo'){
						//////////////////////
						$html	.= "\t\t<SELECT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims .'\' '."".$element['ATTRIB_OBJET']." NAME=".$element['CHAMP_PERE']."_".$ligne."_".$code_dims." tabindex='".$GLOBALS['cell_mat_index']."'>\n";
						$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n";
							
						////
						$dynamic_content_object = false ;
						if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
							//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
								$dynamic_content_object = true ;
							//}
						}
						if($dynamic_content_object == true){
							$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$ligne.'_'.$code_dims.'-->'."\n"; 
						}else{
							$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
							foreach($result_nomenc as $element_result_nomenc){
									//Un valeur du combo pour chaque valeur de la nomenclature 
									$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
									// Lecture des libell�s de la table de nomenclature
					
									$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
							}
						}
						$html 		.= "\t\t</SELECT>\n";
						//////////////////////
				}elseif($element['TYPE_OBJET']=='liste_radio'){
						//////////////////////
						$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
						
						$html		.= "\t\t<table width='100%' valign='top'>\n";
						
						foreach($result_nomenc as $element_result_nomenc){
							$html		.= "\t\t\t<tr>\n";
							$html		.= "\t\t\t\t<td nowrap='nowrap'>".$element_result_nomenc['LIBELLE']."</td>\n";
							$html		.= "\t\t\t\t<td>"."<INPUT".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '." NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='radio' 
											VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]." tabindex='".$GLOBALS['cell_mat_index']."'></td>\n"; 
							$html		.= "\t\t\t</tr>\n";
						}
						 
						$html		.= "\t\t</table>\n";

				//Ajout HEBIE pour gestion champs cach�s
				}elseif($element['TYPE_OBJET']=='hidden_field'){
						$html.= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='hidden' ";
						$html.= "VALUE=\"\$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."\"/>\n";
				}
				else{
					$html 		.= "\t\t --- \n";
				}
				return $html ;
		}
		
		function get_dims_zone_matricielle($type_zone, $id_tabm, $dico){
			foreach($dico as $element){
				if( ($element['TYPE_OBJET'] == $type_zone) && ($element['ID_TABLE_MERE_THEME'] == $id_tabm) ){
					return($element);
				}
			}
		}
		
		function get_tabms_matricielles($dico){
			$tab_tabms_mat = array();
			if($this->type_theme<>4){
				foreach($dico as $element){
					if( ($element['TYPE_OBJET'] == 'dimension_ligne') || ($element['TYPE_OBJET'] == 'dimension_colonne') ){
						if(!in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tab_tabms_mat)){
							$tab_tabms_mat[] = $element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME'];
						}
					}
				}
			}else{
				foreach($dico as $element){
					if( ($element['TYPE_OBJET'] == 'dimension_ligne') ){
							if(!in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tab_tabms_mat)){
							$tab_tabms_mat[] = $element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME'];
						}
					}
				}
			}
			return($tab_tabms_mat);
		}
		
		//----------------------------------------------------------------------
		//----------------------------------------------------------------------
		/**
		* METHODE :  generer_frame_formulaire(id_theme, langue):
		* Effectue la g�n�ration des templates comme les formulaires 
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
	function generer_frame_formulaire($id_theme, $langue ,$id_systeme, $code_annee='', $code_etablissement=''){
					$requete				="
									SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = " . $id_theme . 
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = " . $id_systeme .
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = " . $id_systeme .
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
									AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
									ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";

				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;
						$dico = $this->tri_fils($this->dico);
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

				//Recuperation du type de theme
				$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
				$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
				//Fin Recuperation du type de theme

				// PHP8 fix S17f: retour anticipe si le dico est vide (theme sans zones pour ce systeme)
				if (empty($this->dico)) {
					return; // Pas de zones pour ce theme/systeme - rien a generer
				}
		
		$html = ''; // PHP8 fix S17f: initialisation explicite avant premier .=
		$html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";
		$html			.= "<script language='javascript' type='text/javascript'>"."\n";
		$html			.= 'var tab_chps'.' = new Array();'."\n";
		$html			.= "</script>\n";				
		$kkk = 0;					
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
		$html 			.= "<TABLE class='table-questionnaire' border='1'>\n". '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>'."\n";
		//$html 			.="\t\t<ligne-titre>".$this->libelle_long."</ligne-titre>\n";
				
		//Recherche du LIBELLE BOOLEEN ex: 1) oui
		
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
							WHERE CODE_NOMENCLATURE=1 
							AND NOM_TABLE='DICO_BOOLEEN'
							AND CODE_LANGUE='".$langue."';";
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
				
		//echo $requete.'<br>';
		
		//Recherche du LIBELLE BOOLEEN ex: 2) non
		$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
							WHERE CODE_NOMENCLATURE=0 
							AND NOM_TABLE='DICO_BOOLEEN'
							AND CODE_LANGUE='".$langue."';";
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

		$tab_elem_grp 			= array();
		$nom_grp_deja_aff 		= array();
		$html_fonc_TotMatFrml 	= '';
		$html_fonc_TotChpsFrml	= '';
		$tab_fonc_Desact_Zones_Themes = array();
		$tab_champ_cache = array();
		/* echo '<pre>';
		print_r($dico);
		die();*/
		$tabms_matricielles = $this->get_tabms_matricielles($dico);
		//echo '<pre>';
		//print_r($tabms_matricielles);
		//die();
		
		foreach($dico as $i_elem => $element){
			// Mettre un nom de groupe par defaut s'il en existe pas pour le type matriciel
			if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
				
				if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
					if( trim($element['NOM_GROUPE']) == '' ){
						$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
						$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
					}else{
						$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
						$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
					}
					$dico[$i_elem]['OBJET_MATRICIEL']	= true;
					$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
				}
			}
			elseif( ($element['NOM_GROUPE'] <> '') and ($element['TYPE_OBJET'] <> 'liste_radio') and ($element['TYPE_OBJET'] <> 'liste_checkbox') ){
				$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
				$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
				$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
			}
			if (trim($element['TYPE_OBJET']) == 'hidden_field'){
					$tab_champ_cache[] 	= $element['CHAMP_PERE'] ;
			}
		}//Fin de parcours du dico
				$pass_ligne = -1;
				$class_ligne = 'ligne-impaire-left'; // PHP8 fix S17g: init avant le foreach
				
					foreach($dico as $element){
						if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
							//echo'<pre>';
							//echo '"'.$element['TYPE_OBJET'].'"<br>';
							if($element['CHAMP_PERE']	==$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']){
								//$html .= '<INPUT name=\''.$element['CHAMP_PERE'].'_0\' type=\'hidden\' 
								//								value=$_SESSION[\'secteur\'] >';
							}
							elseif( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) && (!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)) ){
									
									//echo '<pre>-------------- ZONE MAT <br>';
									//print_r($element);
									//echo '<br>';
									//echo '<br> type matriciel = ' ;
									//echo $element['CHAMP_PERE'];
									//$affiche_vertic_mes	=	$element['AFFICH_VERTIC_MES'];
									$affiche_vertic_mes = false;
									if( is_array($tab_elem_grp[$element['NOM_GROUPE']]) ){
										foreach($tab_elem_grp[$element['NOM_GROUPE']] as $i_vertic => $element_vertic ){
											if( $element_vertic['AFFICH_VERTIC_MES'] == 1){
												$affiche_vertic_mes = true;
												break;
											}
										}
									}
									$affiche_sous_totaux	=	$element['AFFICHE_SOUS_TOTAUX'];
									$nom_grp_deja_aff[]			=	$element['NOM_GROUPE'];
									$affiche_totaux_mat_Frml 	= 	array();
									$mat_dim_ligne 		=	$this->get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
									$mat_dim_colonne	=	$this->get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);

									$mat_codes_ligne	= array();
									if(is_array($mat_dim_ligne) && count($mat_dim_ligne)){
										//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
										if ($mat_dim_ligne['TABLE_FILLE']=='') $mat_dim_ligne['TABLE_FILLE']=$mat_dim_ligne['TABLE_INTERFACE'];
										if ($mat_dim_ligne['CHAMP_FILS']=='') $mat_dim_ligne['CHAMP_FILS']=$mat_dim_ligne['CHAMP_INTERFACE'];
										if ($mat_dim_ligne['SQL_REQ']=='') $mat_dim_ligne['SQL_REQ']=$mat_dim_ligne['REQUETE_CHAMP_INTERFACE'];
										$mat_nomenc_ligne	= $this->requete_nomenclature($mat_dim_ligne['TABLE_FILLE'], $mat_dim_ligne['CHAMP_FILS'], $mat_dim_ligne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_ligne['SQL_REQ'], $code_annee, $code_etablissement) ;
										if(is_array($mat_nomenc_ligne)){
											foreach($mat_nomenc_ligne as $mat_i => $mat_val){
												if(count(array_keys($mat_val)) == 2)//Cas dimension sans parent
													$mat_codes_ligne[] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_ligne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE']);
												elseif(count(array_keys($mat_val)) == 4)//Cas dimension avec parent
													$mat_codes_ligne[] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_ligne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'], 'code_parent' => $mat_val['CODE_PARENT'], 'libelle_parent' => $mat_val['LIBELLE_PARENT']);
											}
										}
										//Fin modif Hebie
									}
									
									$mat_codes_colonne	= array();
									if(is_array($mat_dim_colonne) && count($mat_dim_colonne)){
										
										//Ajout hebie pour gerer une table de donnees comme table fille (nomenclature) pour generation de theme pendant la saisie
										if ($mat_dim_colonne['TABLE_FILLE']=='') $mat_dim_colonne['TABLE_FILLE']=$mat_dim_colonne['TABLE_INTERFACE'];
										if ($mat_dim_colonne['CHAMP_FILS']=='') $mat_dim_colonne['CHAMP_FILS']=$mat_dim_colonne['CHAMP_INTERFACE'];
										if ($mat_dim_colonne['SQL_REQ']=='') $mat_dim_colonne['SQL_REQ']=$mat_dim_colonne['REQUETE_CHAMP_INTERFACE'];
										//Fin modif
										$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne['TABLE_FILLE'], $mat_dim_colonne['CHAMP_FILS'], $mat_dim_colonne['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne['SQL_REQ'], $code_annee, $code_etablissement) ;
										if(is_array($mat_nomenc_colonne)){
											foreach($mat_nomenc_colonne as $mat_i => $mat_val){
												$mat_codes_colonne[] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
											}
										}
									}
									
									$set_TOTAL_MatFrml_ALL = ''; // PHP8 fix S17g: init avant usage conditionnel
									$mat_liste_mes 			= array();
									$tab_libelles_mesures 	= array();
									$tab_types_mesures 		= array();
									$attrib_obj_mesures 	= array();
									$vertic_aff_mesures		= array();
									$elem_obj_mesures 		= array();
									$tab_lib_mes_tot 		= array();
									$types_mes_matr_att 	= array('booleen', 'checkbox', 'text', 'combo', 'liste_radio');
									$tab_index_mes_aff_tot	= array();
									$labels_tabl_matr		= '';
									$vertic_for_all_mes		= true;
									
									foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
										if($mat_val['TYPE_OBJET'] == 'label'){
											$labels_tabl_matr .= $this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue) . '<br>';
										}else{
											if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
												$tab_libelles_mesures[$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
											}
											$mat_liste_mes[$mat_i]		= $mat_val['CHAMP_PERE'];
											$tab_types_mesures[$mat_i] 	= $mat_val['TYPE_OBJET'];
											$attrib_obj_mesures[$mat_i] = $mat_val['ATTRIB_OBJET'];
											$vertic_aff_mesures[$mat_i] = $mat_val['TAILLE_LIB_VERTICAUX'];
											
											$elem_obj_mesures[$mat_i] 	= $mat_val ;
											if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
												$affiche_totaux_mat_Frml[$mat_i] 	= true;
												$tab_lib_mes_tot[$mat_i]	=	($tab_libelles_mesures[$mat_i] ?? ''); // PHP8 fix S17g
												$tab_index_mes_aff_tot[] = $mat_i ;
											}
										}
									}
									$nb_mesures =  count($mat_liste_mes);
									
									//$html.= "\t<TR class='$class_ligne'><TD colspan=4 valign='top'>&nbsp;</TD></TR>\n";
									
									$html.= "\t<TR class='$class_ligne'>\n";
									$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
									if(trim($labels_tabl_matr) <> ''){
										$html.= '<b>'.$labels_tabl_matr.'</b>';
									}
									
									if( count($mat_codes_ligne) || count($mat_codes_colonne) ){
										//echo '<br> NOM_GROUPE = ' .$element['NOM_GROUPE'] ;
										//$affiche_totaux_mat_Frml 	= $element['AFFICHE_TOTAL'];
										$html_js_tot = '' ;
										if(count($affiche_totaux_mat_Frml)){
											$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
											$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION']).'";'."\n";
											$html_js_tot	.= 'var tot_mes_ligne_'.$element['ID_ZONE'].' 	= new Array();'."\n";
											$html_js_tot	.= 'var tot_mes_col_'.$element['ID_ZONE'].' 	= new Array();'."\n";
											$html_js_tot	.= 'var affich_vertic_mes_'.$element['ID_ZONE'].' 	= '.$element['AFFICH_VERTIC_MES'].';'."\n";
											$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' 	= '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
											$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
											
											foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
												$html_js_tot	.= ',"'.$mat_liste_mes[$i_mes].'"';
											}
											$html_js_tot	.= ');'."\n";
											
											$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array (';
											
											if(count($mat_codes_ligne)){
												foreach($mat_codes_ligne as $li => $code_li){
													$html_js_tot	.= ',"'.$code_li['code'].'"';
												}
											}
											$html_js_tot	.= ');'."\n";
									
											$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
											
											if(count($mat_codes_colonne)){
												foreach($mat_codes_colonne as $col => $code_col){
													$html_js_tot	.= ',"'.$code_col['code'].'"';
												}
											}
											$html_js_tot	.= ');'."\n";
											
											$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
												
											$html_js_tot	.= "</script>"."\n";
											
											$html.= $html_js_tot ;
		
											$set_TOTAL_MatFrml_ALL 	= ' onchange="set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
											$fnc_TOTAL_MatFrml 		= ' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' ;
											
											$html_fonc_TotMatFrml 	.= $fnc_TOTAL_MatFrml . "\n";
											if(!$affiche_vertic_mes	){/////Cas d'affichage horizontal des mesures
												if ( (count($affiche_totaux_mat_Frml) > 1) && count($mat_codes_colonne) ) {
													$colspan_Total_2Dim = ' COLSPAN='.(count($affiche_totaux_mat_Frml) + 1).' ';
												}else{
													$colspan_Total_2Dim = '';
												}
											}else{
												if(!$affiche_sous_totaux)
													$colspan_Total_2Dim =  ' COLSPAN=\'2\' ';
												else
													$colspan_Total_2Dim =  ' ';
											}
										}	
										
										$html.= "<TABLE class='table-questionnaire' border=1>";
										///////////////////////////////////////////////////////////////////////////////	
										/////Cas d'affichage horizontal des mesures //////////////////////////////////
										/////////////////////////////////////////////////////////////////////////////	
										if(!$affiche_vertic_mes	){
											if ($nb_mesures > 1) {
												if($affiche_sous_totaux)
													$colspan =' COLSPAN='.($nb_mesures+1).' ';
												else
													$colspan =' COLSPAN='.$nb_mesures.' ';
											}else{
												$colspan =' ';
											}
											if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
												$rowspan =' ROWSPAN=2 ';
											}else{
												$rowspan =' ';
											}
											$html 			.= "\n\t<TR>\n\t\t<TD $rowspan  CLASS='ligne-titre'>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
										
										if(count($mat_codes_colonne)){
											foreach ($mat_codes_colonne as $col => $elem_col){
												// On imprime le libell� des colonnes
												$html		.="\t\t<TD $colspan CLASS='ligne-titre' align='center'>";
												
												if( ($mat_dim_colonne['TAILLE_LIB_VERTICAUX'] > 0)){
													$libelle_afficher   =   $this->limiter_taille_text($elem_col['libelle'], $mat_dim_colonne['TAILLE_LIB_VERTICAUX']);
													//$libelle_afficher	.=	'+++';
													$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
												}else{
													$html 		.= $elem_col['libelle'];
												}
												
												$html		.="</TD>\n";
											}
										}else{
											$html		.="\t\t<TD $colspan CLASS='ligne-titre' align='center'>";
											$html 		.= '&nbsp;';
											$html		.="</TD>\n";
										}
			
										if( ( (count($affiche_totaux_mat_Frml))  && (count($mat_codes_colonne)) ) || ( count($affiche_totaux_mat_Frml) > 1) ){
											 $html		.="\t\t<TD  CLASS='ligne-titre' align='center' ".$colspan_Total_2Dim.">";
											 $html 		.= $this->recherche_libelle_page('total', $langue, 'questionnaire.php');
											 $html		.="</TD>\n";
										}

										$html 			.= "\n\t</TR>\n";
										
										
										if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
											$html 			.= "\n\t<TR>\n";	// Zone vide d'intersection des 2 dimensions
											if(count($mat_codes_colonne)){
												foreach ($mat_codes_colonne as $col => $elem_col){
													// On imprime le libell� des colonnes
													foreach($mat_liste_mes as $i_mes => $mes){
														$html		.="\t\t<TD  align='center'>";
														
														if( $vertic_aff_mesures[$i_mes] > 0){
															$libelle_afficher   =   $this->limiter_taille_text($tab_libelles_mesures[$i_mes], $vertic_aff_mesures[$i_mes]);
															//$libelle_afficher	.=	'+++';
															$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
															
														}else{
															$html		.=$tab_libelles_mesures[$i_mes];
															
														}
														$html		.="</TD>\n";
													}
													if($affiche_sous_totaux){
														if($element['LIB_EXPR']<>'')
															$html		.="\t\t<TD  align='center'>".trim($element['LIB_EXPR'])."</TD>\n";	
														else
															$html		.="\t\t<TD  align='center'>".$this->recherche_libelle_page('sous_total', $langue, 'questionnaire.php')."</TD>\n";
													}											
												}
											}else{
												foreach($mat_liste_mes as $i_mes => $mes){
													$html		.="\t\t<TD  align='center'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
												}
											}
											$max_vertic_tot_size = 0 ;
										$vertic_for_totals = false; // PHP8 fix S17g
											if(count($affiche_totaux_mat_Frml)){
												if(count($mat_codes_colonne)){
													foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
														$html		.="\t\t<TD  align='center'>";
														
														if( $vertic_aff_mesures[$i_mes] > 0){
															$libelle_afficher   =   $this->limiter_taille_text($tab_lib_mes_tot[$i_mes], $vertic_aff_mesures[$i_mes]);
															//$libelle_afficher	.=	'+++';
															$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
															$vertic_for_totals  = true ;
															if($max_vertic_tot_size < $vertic_aff_mesures[$i_mes]){
																$max_vertic_tot_size = $vertic_aff_mesures[$i_mes] ;
															}
														}else{
															$html		.=$tab_lib_mes_tot[$i_mes];
															$vertic_for_all_mes = false ;
														}
														$html		.="</TD>\n";
													}
												}
												
												if (count($affiche_totaux_mat_Frml) > 1) {
													$html		.="\t\t<TD  align='center'>";
													if($element['LIB_EXPR']<>'')
														if( ($vertic_for_totals == true) and  ($vertic_for_all_mes == true)){
															$libelle_afficher   =   $this->limiter_taille_text(trim($element['LIB_EXPR']), $max_vertic_tot_size);
															$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
														}else{
															$html		.= trim($element['LIB_EXPR']);
														}
													elseif(trim($element['EXPRESSION'])=='')
														if( ($vertic_for_totals == true) and  ($vertic_for_all_mes == true)){
															$libelle_afficher   =   $this->limiter_taille_text((implode(' & ', $tab_lib_mes_tot)), $max_vertic_tot_size);
															$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
														}else{
															$html		.= implode(' + ', $tab_lib_mes_tot);
														}
													else
														if( ($vertic_for_totals == true) and  ($vertic_for_all_mes == true)){
															$libelle_afficher   =   $this->limiter_taille_text(trim($element['EXPRESSION']), $max_vertic_tot_size);
															$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
														}else{
															$html		.= trim($element['EXPRESSION']);
														}
													$html		.="</TD>\n";
												}	
											}
											$html 			.= "\n\t</TR>\n";
										}
										
										// traitements des lignes 
										if(count($mat_codes_ligne)){
											//echo "<pre>";
											//print_r($mat_dim_ligne);
											$tab_nomenc_parent = array();
											$nbsp = '';
											foreach($mat_codes_ligne as $li => $elem_li){
												$Ligne = $li;
									
												if(!isset($classe_fond)) {
													$classe_fond = 'ligne-paire';
												} else {
													if($classe_fond == 'ligne-paire') {
														$classe_fond = 'ligne-impaire';
													} else {
														$classe_fond = 'ligne-paire';
													}
												}
												//Modif Hebie pour affichage libell� nomenclature parent
												if(count(array_keys($elem_li))==4){
													if(!in_array($elem_li['code_parent'],$tab_nomenc_parent)){
														$tab_nomenc_parent[] = $elem_li['code_parent'];
														$html 		        .= "\n\t<TR>\n";
														$html 		.= "\n\t\t<TD CLASS='ligne-nomenc-parent' ".$mat_dim_ligne['ATTRIB_OBJET'].">".$elem_li['libelle_parent']."</TD>\n";
														$html 		        .= "\n\t</TR>\n";
														$nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
													}
												}
												//Fin Modif Hebie pour affichage libell� nomenclature parent
												$html 		        .= "\n\t<TR>\n";
												// On imprime l'ent�te de ligne
												$html 		.= "\n\t\t<TD CLASS='".$classe_fond."-left"."' ".$mat_dim_ligne['ATTRIB_OBJET'].">$nbsp".$elem_li['libelle']."</TD>\n";
												if(count($mat_codes_colonne)){
													$tmp_i_mes=1;
													$cpt_i_mes=1;
													foreach($mat_codes_colonne as $col => $elem_col){
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															if($cpt_i_mes==1) {$tmp_i_mes=$i_mes; $cpt_i_mes++;}
															if($affiche_totaux_mat_Frml[$i_mes])
																$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', 0, \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$elem_li['code'].', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
															else
																$set_TOTAL_MatFrml_Chp 	= '';
															$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
															
															$html		.= $this->get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
															
															$html		.= "</TD>\n";
														}
														if($affiche_sous_totaux){
															$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$elem_li['code']."_".$elem_col['code']."' ";
															if($attrib_obj_mesures[$i_mes]<>'') $html		.=$attrib_obj_mesures[$i_mes];
															else $html		.=$attrib_obj_mesures[$tmp_i_mes];
															$html		.="></TD>\n";
														}
													}
												}
												else{ // Pas de dimension Colonne
	
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
													   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
															
															$html		.= $this->get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_ALL, $langue, $id_systeme);
															
															$html		.= "</TD>\n";
													}
																									
												}
												
												if(count($affiche_totaux_mat_Frml)){
													
													if(count($mat_codes_colonne)){
														
														if(count($affiche_totaux_mat_Frml) == 1){
															$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' " .$attrib_obj_mesures[$tab_index_mes_aff_tot[0]]. " NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_li['code']."'></TD>\n";
														}else{
															$html	.= "<script language='javascript' type='text/javascript'>"."\n";
															$html	.= 'tot_mes_ligne_'.$element['ID_ZONE'].'['.$Ligne.']	= new Array();'."\n";
															$html	.= "</script>"."\n";
															foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
																$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_LIGNE_0_".$elem_li['code']."'></TD>\n";
																$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																$html	.= 'tot_mes_ligne_'.$element['ID_ZONE'].'['.$Ligne.']['.$i_mes.']	= "TOT_'.$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_LIGNE_0_".$elem_li['code'].'";'."\n";
																$html	.= "</script>"."\n";
															}
														}
													}
													if (count($affiche_totaux_mat_Frml) > 1) {
														$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_li['code']."' " .$attrib_obj_mesures[$tab_index_mes_aff_tot[0]]. "></TD>\n";
													}	
												}
												$html 		        .= "\n\t</TR>\n";
											}// Fin parcours des lignes
										} // fin si exist  dimension Ligne
										else{ // pas de dimensions lignes
	
												if(!isset($classe_fond)) {
													$classe_fond = 'ligne-paire';
												} else {
													if($classe_fond == 'ligne-paire') {
														$classe_fond = 'ligne-impaire';
													} else {
														$classe_fond = 'ligne-paire';
													}
												}

												$html 		        .= "\n\t<TR>\n";
												if( count($affiche_totaux_mat_Frml) > 1){
													$rowspan_chp_tot = 'rowspan=\'2\'';
													$br_chp_tot = '<br>';
												}else{
													$rowspan_chp_tot = '';
													$br_chp_tot = '';
												}
												$html 		.= "\n\t\t<TD  CLASS='".$classe_fond."'>&nbsp;</TD>\n";
												if(count($mat_codes_colonne)){
													foreach($mat_codes_colonne as $col => $elem_col){
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
														  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
															$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
															
															$html		.= $this->get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_ALL, $langue, $id_systeme);
															
															$html		.= "</TD>\n";
														}
													}
												}
												
												if(count($affiche_totaux_mat_Frml)){
	
													if(count($mat_codes_colonne)){
														
														foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
															$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_LIGNE_0'></TD>\n";
														}
													}
													if (count($affiche_totaux_mat_Frml) > 1) {
														$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
													}	
	
												}
												$html 		        .= "\n\t</TR>\n";
																				
										}
										
										if(count($affiche_totaux_mat_Frml)){
											if (count($affiche_totaux_mat_Frml) > 1) {
												if(count($mat_codes_ligne)){
													$html 		    .= "\n\t<TR>\n";
													
													if( (count($mat_codes_colonne) > 1) && (!$affiche_sous_totaux)){
														$rowspan_chp_tot = 'rowspan=\'2\'';
														$br_chp_tot = '<br>';
													}else{
														$rowspan_chp_tot = '';
														$br_chp_tot = '';
													}
													
													$html 		    .= "\n\t<TD align='center' $rowspan_chp_tot bgcolor='#CCCCCC'>$br_chp_tot".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
													
													if(count($mat_codes_colonne)){
														$tmp_i_mes=1;
														$cpt_i_mes=1;
														foreach($mat_codes_colonne as $col => $elem_col){
																$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																$html	.= 'tot_mes_col_'.$element['ID_ZONE'].'['.$col.']	= new Array();'."\n";
																$html	.= "</script>"."\n";
																foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
																	if($cpt_i_mes==1) {$tmp_i_mes=$i_mes; $cpt_i_mes++;}
																	if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																		$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0_".$elem_col['code']."'></TD>\n";
																		$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																		$html	.= 'tot_mes_col_'.$element['ID_ZONE'].'['.$col.']['.$i_mes.']	= "TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$i_mes].'_COLONNE_0_'.$elem_col['code'].'";'."\n";
																		$html	.= "</script>"."\n";
																	}else{
																		$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																			if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																				$html.="&nbsp;";
																			$html.="</TD>\n";
																	}
																}
																if($affiche_sous_totaux){
																	$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' ";
																	if($attrib_obj_mesures[$i_mes]<>'') $html		.=$attrib_obj_mesures[$i_mes];
																	else $html		.=$attrib_obj_mesures[$tmp_i_mes];
																	$html		.="></TD>\n";
																}
															}
														}else{
															foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
																if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																	$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold; '  " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0"."'></TD>\n";
																}else{
																	$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																	if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																		$html.="&nbsp;";
																	$html.="</TD>\n";
															}
														}
													}
													if(count($mat_codes_colonne)){
														foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
															$html		.="\t\t<td align='center' $rowspan_chp_tot bgcolor='#CCCCCC'  style='vertical-align:middle'>$br_chp_tot"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'  " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0'></TD>\n";
														}
													}
													$html		.="\t\t<td align='center' $rowspan_chp_tot bgcolor='#CCCCCC'  style='vertical-align:middle'>$br_chp_tot"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
													$html 		    .="\n\t</TR>\n";
													$html 		    .= "\n\t<TR>\n";
													
													if(count($mat_codes_colonne)){
														if(!$affiche_sous_totaux){
															foreach($mat_codes_colonne as $col => $elem_col){
																$html		.="\t\t<TD align='center' colspan='".$nb_mesures."' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
															}
														}
														$html 		    .="\n\t</TR>\n";
													}
												}else{
													$html 		    .= "\n\t<TR>\n";
													$html 		    .= "\n\t<TD align='center'  bgcolor='#CCCCCC'>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
	
													if(count($mat_codes_colonne)){
														foreach($mat_codes_colonne as $col => $elem_col){
															$html		.="\t\t<TD align='center' colspan='".$nb_mesures."' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
														}
													}
													
													$html 		    .="\n\t</TR>\n";
												}
											}elseif(count($mat_codes_ligne)){
												$html 		    .= "\n\t<TR>\n";
												$html 			.= "\n\t\t<TD align='center' bgcolor='#CCCCCC'>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
												if(count($mat_codes_colonne)){
													foreach($mat_codes_colonne as $col => $elem_col){
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
															if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'  " .$attrib_obj_mesures[$i_mes]. " NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."'></TD>\n";
															}else{
																$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																	$html.="&nbsp;";
																$html.="</TD>\n";
															}
														}
													}
													$html			.="\t\t<TD colspan='".count($affiche_totaux_mat_Frml)."' align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
												}else{
													$html		.="\t\t<TD colspan='".count($affiche_totaux_mat_Frml)."' align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0"."' " .$attrib_obj_mesures[$i_mes]. "></TD>\n";
												}
												$html 		    .="\n\t</TR>\n";
											}	
										}

										$html.= "</TABLE>";
										///////////////////////////////////////////////////////////////////////////////	
										/////Cas d'affichage vertical des mesures ////////////////////////////////////
										/////////////////////////////////////////////////////////////////////////////	
										}else{
											if ($nb_mesures > 1) {
												if($affiche_sous_totaux){
													$rowspan =' ROWSPAN='.($nb_mesures + 2).' ';
												}else{
													$rowspan =' ROWSPAN='.($nb_mesures + 1).' ';
												}
											}else{
												$rowspan =' ';
											}
										
											if(is_array($tab_libelles_mesures) && count($tab_libelles_mesures)){
												$colspan =' COLSPAN=2 ';
												//$colspan =' ';
											}else{
												$colspan =' ';
											}
											
											$html 			.= "\n\t<TR>\n\t\t<TD $colspan  CLASS='ligne-titre'>&nbsp</TD>\n";	// Zone vide d'intersection des 2 dimensions
											if(count($mat_codes_colonne)){
												foreach ($mat_codes_colonne as $col => $elem_col){
													// On imprime le libell� des colonnes
													$html		.="\t\t<TD CLASS='ligne-titre' align='center'>";
													$html 		.= $elem_col['libelle'];
													$html		.="</TD>\n";
												}
											}else{
												$html		.="\t\t<TD CLASS='ligne-titre' align='center'>";
												$html 		.= '&nbsp;';
												$html		.="</TD>\n";
											}
				
											if( ( (count($affiche_totaux_mat_Frml))  && (count($mat_codes_colonne)) ) || ( count($affiche_totaux_mat_Frml) > 1) ){
												 $html		.="\t\t<TD  CLASS='ligne-titre' align='center' ".$colspan_Total_2Dim.">";
												 $html 		.= $this->recherche_libelle_page('total', $langue, 'questionnaire.php');
												 $html		.="</TD>\n";
											}
											
											$html 			.= "\n\t</TR>\n";
											
											// traitements des lignes 
											if(count($mat_codes_ligne)){
												$tab_nomenc_parent = array();
												$nbsp = '';
												foreach($mat_codes_ligne as $li => $elem_li){
													$Ligne = $li;
										
													if(!isset($classe_fond)) {
														$classe_fond = 'ligne-paire';
													} else {
														if($classe_fond == 'ligne-paire') {
															$classe_fond = 'ligne-impaire';
														} else {
															$classe_fond = 'ligne-paire';
														}
													}
													
													//Modif Hebie pour affichage libell� nomenclature parent
													if(count(array_keys($elem_li))==4){
														if(!in_array($elem_li['code_parent'],$tab_nomenc_parent)){
															$tab_nomenc_parent[] = $elem_li['code_parent'];
															$html 		        .= "\n\t<TR>\n";
															$html 		.= "\n\t\t<TD CLASS='ligne-nomenc-parent' ".$mat_dim_ligne['ATTRIB_OBJET'].">".$elem_li['libelle_parent']."</TD>\n";
															$html 		        .= "\n\t</TR>\n";
															$nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
														}
													}
													//Fin Modif Hebie pour affichage libell� nomenclature parent
										
													$html 		        .= "\n\t<TR>\n";
		
															  // On imprime l'ent�te de ligne
													$html 		.= "\n\t\t<TD $rowspan style='vertical-align:middle' CLASS='".$classe_fond."' ".$mat_dim_ligne['ATTRIB_OBJET'].">$nbsp".$elem_li['libelle']."</TD>\n";
													$k=1;
													
													$html	.= "<script language='javascript' type='text/javascript'>"."\n";
													$html	.= 'tot_mes_ligne_'.$element['ID_ZONE'].'['.$li.']	= new Array();'."\n";
													$html	.= "</script>"."\n";

													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
														$html 			.= "\n\t<TR>\n";
														// On imprime le libell� des mesures
														$html		.="\t\t<TD  nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
																									
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
																   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	if($affiche_totaux_mat_Frml[$i_mes])
																		$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', 0, \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$elem_li['code'].', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
																	else
																		$set_TOTAL_MatFrml_Chp 	= ' ';
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
	
																	$html		.= $this->get_cell_matrice(0 ,$elem_li['code'].'_'.$elem_col['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
																	
																	$html		.= "</TD>\n";
																//}
															}
														}
														else{ // Pas de dimension Colonne
			
															//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
															   // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
																	
																	$html		.= $this->get_cell_matrice(0 ,$elem_li['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_ALL, $langue, $id_systeme);
																	
																	$html		.= "</TD>\n";
															//}
														}
														
													
														if(count($affiche_totaux_mat_Frml)){
															
															if(count($mat_codes_colonne)){
																
																if(count($affiche_totaux_mat_Frml) == 1){
																	$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_li['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
																}else{
																	//foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
																		$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																		if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																			$html	.="<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_LIGNE_0_".$elem_li['code']."' ".$element['ATTRIB_OBJET'].">";
																		$html	.="</TD>\n";
																		$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																		$html	.= 'tot_mes_ligne_'.$element['ID_ZONE'].'['.$li.']['.$i_mes.']	= "TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$i_mes].'_LIGNE_0_'.$elem_li['code'].'";'."\n";
																		$html	.= "</script>"."\n";
																	//}
																}
															}
															if (count($affiche_totaux_mat_Frml) > 1) {
																$rowspan2 =' ROWSPAN='.($nb_mesures).' ';
																if(($k==1) && (!$affiche_sous_totaux))
																	$html		.="\t\t<TD $rowspan2 align='center' bgcolor='#CCCCCC' style='vertical-align:middle'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_li['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
																$k++;
															}	
														}
														$html 		    .= "\n\t</TR>\n";
													}
													
													if($affiche_sous_totaux){
														$html 			.= "\n\t<TR>\n";
															// On imprime le libell� des mesures
															if($element['LIB_EXPR']<>'')
																$html		.="\t\t<TD  align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>".trim($element['LIB_EXPR'])."</TD>\n";	
															else
																$html		.="\t\t<TD  align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>".$this->recherche_libelle_page('sous_total', $langue, 'questionnaire.php')."</TD>\n";
															if(count($mat_codes_colonne)){
																foreach($mat_codes_colonne as $col => $elem_col){
																	$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$elem_li['code']."_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
																}
																$html		.="\t\t<TD align='center' bgcolor='#CCCCCC' style='vertical-align:middle'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_li['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
															}
														$html 		    .= "\n\t</TR>\n";
													}
													
												}// Fin parcours des lignes
											} // fin si exist  dimension Ligne
											else{ // pas de dimensions lignes
		
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
														if(!isset($classe_fond)) {
															$classe_fond = 'ligne-paire';
														} else {
															if($classe_fond == 'ligne-paire') {
																$classe_fond = 'ligne-impaire';
															} else {
																$classe_fond = 'ligne-paire';
															}
														}
											
														$html 			.= "\n\t<TR>\n";	
														// On imprime le libell� des mesures
														$html		.="\t\t<TD $colspan nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
														
														if( count($affiche_totaux_mat_Frml) > 1){
															//$rowspan_chp_tot = 'rowspan=\'2\'';
															//$br_chp_tot = '<br>';
															$rowspan_chp_tot = '';
															$br_chp_tot = '';
														}else{
															$rowspan_chp_tot = '';
															$br_chp_tot = '';
														}
														//$html 		.= "\n\t\t<TD  CLASS='".$classe_fond."'>&nbsp;</TD>\n";
														
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){					
																  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
																	$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
																	
																	$html		.= $this->get_cell_matrice(0 , $elem_col['code'], $elem_obj_mesures[$i_mes], $set_TOTAL_MatFrml_ALL, $langue, $id_systeme);
																	
																	$html		.= "</TD>\n";
																//}
															}
														}
														
														if(count($affiche_totaux_mat_Frml)){
			
															if(count($mat_codes_colonne)){
																
																//foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
																	$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_LIGNE_0' ".$element['ATTRIB_OBJET']."></TD>\n";
																//}
															}
															if (count($affiche_totaux_mat_Frml) > 1) {
																//$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_0' ".$element['ATTRIB_OBJET']."></TD>\n";
															}	
			
														}
														$html 		        .= "\n\t</TR>\n";
													}								
											}
											
											if(count($affiche_totaux_mat_Frml)){
												if (count($affiche_totaux_mat_Frml) > 1) {
													if(count($mat_codes_ligne)){
														
														if( (count($mat_codes_colonne) > 1) ){
															$rowspan_chp_tot = 'rowspan=\''.($nb_mesures + 1).'\'';
															if(!$affiche_sous_totaux)
																$colspan_chp_tot = 'colspan=\'2\'';
															else
																$colspan_chp_tot = ' ';
															//$br_chp_tot = '<br>';
															$br_chp_tot = '';
														}else{
															//$rowspan_chp_tot = '';
															//$br_chp_tot = '';
															$colspan_chp_tot = ' ';
															$rowspan_chp_tot = 'rowspan=\''.($nb_mesures + 1).'\'';
															//$br_chp_tot = '<br>';
															$br_chp_tot = '';
														}
														$k=1;
														foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
															$html 		    .= "\n\t<TR>\n";
															if($k==1)
																$html 		    .= "\n\t<TD align='center' $rowspan_chp_tot style='vertical-align:middle' bgcolor='#CCCCCC'>$br_chp_tot".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
															//$k++;
															$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>".$tab_libelles_mesures[$i_mes]."</TD>\n";
															if(count($mat_codes_colonne)){
																
																$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																$html	.= 'tot_mes_col_'.$element['ID_ZONE'].'['.$i_mes.']	= new Array();'."\n";
																$html	.= "</script>"."\n";
																	
																foreach($mat_codes_colonne as $col => $elem_col){
																	//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
																		if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																			$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
																			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
																			$html	.= 'tot_mes_col_'.$element['ID_ZONE'].'['.$i_mes.']['.$col.']	= "TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$i_mes].'_COLONNE_0_'.$elem_col['code'].'";'."\n";
																			$html	.= "</script>"."\n";
																		}else{
																			$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																			if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																				$html.="&nbsp;";
																			$html.="</TD>\n";
																		}
																	//}
																}
															}else{
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
																	if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																		$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																		if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																			$html	.="<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0"."' ".$element['ATTRIB_OBJET'].">";
																		$html	.="</TD>\n";
																	}else{
																		$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>";
																		if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																			$html.="&nbsp;";
																		$html.="</TD>\n";
																	}
																//}
															}
															if(count($mat_codes_colonne)){
																//foreach($affiche_totaux_mat_Frml as $i_mes => $tot_mes){
																	$html		.="\t\t<td $colspan_chp_tot align='center' bgcolor='#CCCCCC'  style='vertical-align:middle'>$br_chp_tot";
																	if(!in_array($mat_liste_mes[$i_mes],$tab_champ_cache))
																		$html		.="<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$i_mes]."_COLONNE_0' ".$element['ATTRIB_OBJET'].">";
																	$html		.="</TD>\n";
																//}
															}else{
																if($k==1)
																	$html		.="\t\t<td $rowspan_chp_tot align='center' bgcolor='#CCCCCC'  style='vertical-align:middle'>$br_chp_tot"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' ".$element['ATTRIB_OBJET']."></TD>\n";
															}
															$k++;
															$html 		    .="\n\t</TR>\n";
														}
														
														$html 		    .= "\n\t<TR>\n";
														
														if(count($mat_codes_colonne)){
															if($element['LIB_EXPR']<>'')
																$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>".trim($element['LIB_EXPR'])."</TD>\n";
															elseif(trim($element['EXPRESSION'])=='')
																$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>".(implode(' + ', $tab_lib_mes_tot))."</TD>\n";
															else
																$html		.="\t\t<TD  align='center' bgcolor='#CCCCCC'>".trim($element['EXPRESSION'])."</TD>\n";
															foreach($mat_codes_colonne as $col => $elem_col){
																$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
															}
															$html		.="\t\t<td $colspan_chp_tot align='center' bgcolor='#CCCCCC' style='vertical-align:middle'>$br_chp_tot"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' ".$element['ATTRIB_OBJET']."></TD>\n";
														}
														$html 		    .="\n\t</TR>\n";
															
													}else{
														$html 		    .= "\n\t<TR>\n";
														$html 		    .= "\n\t<TD $colspan align='center'  bgcolor='#CCCCCC'>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
		
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
															}
														}
														$html		.="\t\t<td align='center' bgcolor='#CCCCCC'  style='vertical-align:middle'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' ".$element['ATTRIB_OBJET']."></TD>\n";
														
														$html 		    .="\n\t</TR>\n";
													}
												}elseif(count($mat_codes_ligne)){
													foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
														$html 		    .= "\n\t<TR>\n";
														$html 			.= "\n\t\t<TD align='center' bgcolor='#CCCCCC'>".$this->recherche_libelle_page('total', $langue, 'questionnaire.php')."</TD>\n";
														if(count($mat_codes_colonne)){
															foreach($mat_codes_colonne as $col => $elem_col){
																//foreach($mat_liste_mes as $i_mes => $nom_champ_mes){
																	if( $affiche_totaux_mat_Frml[$i_mes] == true ){
																		$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_0_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
																	}else{
																		$html.="\t\t<TD  align='center' bgcolor='#CCCCCC'>&nbsp;</TD>\n";
																	}
																//}
															}
															$html			.="\t\t<TD colspan='".count($affiche_totaux_mat_Frml)."' align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0' ".$element['ATTRIB_OBJET']."></TD>\n";
														}else{
															$html		.="\t\t<TD colspan='".count($affiche_totaux_mat_Frml)."' align='center' bgcolor='#CCCCCC'>"."<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; width:50px; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_0"."' ".$element['ATTRIB_OBJET']."></TD>\n";
														}
														$html 		    .="\n\t</TR>\n";
													}
												}	
											}
											$html.= "</TABLE>";
										}
									}// fin si exist des dimensions								
									
									$html.= "</TD>\n";
									$html.= "\t</TR>\n";					
									
							}
							elseif( ($element['TYPE_OBJET']=='liste_radio' or $element['TYPE_OBJET']=='liste_checkbox' or $element['TYPE_OBJET']=='text_valeur_multiple') && !(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
									$pass_ligne++;
									if($pass_ligne%2==0) $class_ligne = 'ligne-impaire-left'; else  $class_ligne = 'ligne-paire-left';
	
									if($pass_ligne > 0)
											$html 		.="\t\t"."<TR><TD COLSPAN='4' class='td_space_blanc'></TD></TR>\n";
									if($element['TYPE_OBJET']=='liste_radio'){
											$type_objet ='radio';
											$liste_choix 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									}
									elseif($element['TYPE_OBJET']=='liste_checkbox'){
											$type_objet ='checkbox';
											$liste_choix 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
											$type_objet ='text';
											echo $element['ID_ZONE_REF'];
											
											$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
											
											$liste_choix 	= $this->requete_nomenclature($zone_ref['TABLE_FILLE'], $zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
									}
									//Modif Hebie pour affichage libell� nomenclature parent
									//var_dump($liste_choix);exit;
									// PHP8 fix S17h: $liste_choix peut être null si requete_nomenclature() échoue
									if (!is_array($liste_choix) || empty($liste_choix)) {
										$html .= "\t<TR class='$class_ligne'><TD COLSPAN='4'>&nbsp;</TD></TR>\n";
										continue; // nomenclature vide pour ce système — ignorer
									}
									$champs_nomenc = array_keys($liste_choix[0]); // safe: $liste_choix[0] est garanti non-null
									$tab_code_parent = array();
									$tab_liste_choix = array();
									if(count((array)$champs_nomenc)>3){
										$cpt_liste_choix = count($liste_choix);
										for( $cpt_occ = 0; $cpt_occ < $cpt_liste_choix; $cpt_occ++ ){
											$tab_liste_choix[$liste_choix[$cpt_occ]['CODE_PARENT']][$cpt_occ][$this->get_champ_extract($element['CHAMP_PERE'])] = $liste_choix[$cpt_occ][$this->get_champ_extract($element['CHAMP_PERE'])];
											$tab_liste_choix[$liste_choix[$cpt_occ]['CODE_PARENT']][$cpt_occ]['LIBELLE'] = $liste_choix[$cpt_occ]['LIBELLE'];
											$tab_liste_choix[$liste_choix[$cpt_occ]['CODE_PARENT']][$cpt_occ]['CODE_PARENT'] = $liste_choix[$cpt_occ]['CODE_PARENT'];
											$tab_liste_choix[$liste_choix[$cpt_occ]['CODE_PARENT']][$cpt_occ]['LIBELLE_PARENT'] = $liste_choix[$cpt_occ]['LIBELLE_PARENT'];
											if(!in_array($liste_choix[$cpt_occ]['CODE_PARENT'],$tab_code_parent)){
												$tab_code_parent[] = $liste_choix[$cpt_occ]['CODE_PARENT'];
											}
										}
										$tmp_tab_liste_choix = array();
										foreach($tab_liste_choix as $code_parent=>$tab_choix){
											$cpt=0;
											foreach($tab_choix as $choix){
												$tmp_tab_liste_choix[$code_parent][$cpt][$this->get_champ_extract($element['CHAMP_PERE'])] = $choix[$this->get_champ_extract($element['CHAMP_PERE'])];
												$tmp_tab_liste_choix[$code_parent][$cpt]['LIBELLE'] = $choix['LIBELLE'];
												$tmp_tab_liste_choix[$code_parent][$cpt]['CODE_PARENT'] = $choix['CODE_PARENT'];
												$tmp_tab_liste_choix[$code_parent][$cpt]['LIBELLE_PARENT'] = $choix['LIBELLE_PARENT'];
												$cpt++;
											}
										}
										$tab_liste_choix = $tmp_tab_liste_choix;
										$tab_nb_choix = array();
										foreach($tab_code_parent as $code_parent){
											$tab_nb_choix[$code_parent]	= count($tab_liste_choix[$code_parent]);
											$tab_nb_tr[$code_parent] = intval($tab_nb_choix[$code_parent] / 3) + 1;
											$tab_reste_div[$code_parent] = $tab_nb_choix[$code_parent] % 3;
											if( $tab_reste_div[$code_parent] > 0 ) $tab_rowspan[$code_parent]	= $tab_nb_tr[$code_parent];
											else $tab_rowspan[$code_parent]	=	$tab_nb_tr[$code_parent] - 1;
											$tab_rowspan_tr[$code_parent] = '';
											if($tab_nb_tr[$code_parent] > 1) $tab_rowspan_tr[$code_parent] = " rowspan='".$tab_rowspan[$code_parent]."' ";
										}
									}else{
										$tab_code_parent[0] = 0;
										$tab_liste_choix[0] = $liste_choix;
										$tab_nb_choix[0] = count($liste_choix);
										$tab_nb_tr[0] = intval($tab_nb_choix[0] / 3) + 1;
										$tab_reste_div[0] = $tab_nb_choix[0] % 3;
										if( $tab_reste_div[0] > 0 ) $tab_rowspan[0]	=	$tab_nb_tr[0];
										else $tab_rowspan[0]	=	$tab_nb_tr[0] - 1;
										//echo"<br>rowspan=$rowspan";
										$tab_rowspan_tr[0] = '';
										if($tab_nb_tr[0] > 1) $tab_rowspan_tr[0] = " rowspan='".$tab_rowspan[0]."' ";
									}
									//Fin Modif Hebie pour affichage libell� nomenclature parent
									
									foreach($tab_code_parent as $code_parent){//Modif Hebie pour affichage libell� nomenclature parent
										$liste_choix = $tab_liste_choix[$code_parent];
										$html.= "\t<TR class='$class_ligne'>\n";
										$html.= "\t\t<TD ".$tab_rowspan_tr[$code_parent]." valign=top class='police_gras'>&nbsp;";
										//Modif Hebie pour affichage libell� nomenclature parent
										if(count((array)$champs_nomenc)>3)	$html.= $liste_choix[0]['LIBELLE_PARENT'];
										else $html.= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
										//Fin Modif Hebie pour affichage libell� nomenclature parent
										$html.= "&nbsp;</TD>\n";
										for( $tr=0; $tr<$tab_rowspan[$code_parent]; $tr++ ){
												if($tr > 0)
														$html.= "\t<TR class='$class_ligne'>\n";
												for($j=0; $j<3; $j++){
														$ordre = 3 * $tr + $j ;
														if(isset($liste_choix[$ordre])){
																$html.= "\t\t<TD class='td_right' nowrap>&nbsp;";
																//$html.= "<span class='td_left'>";
																$html.= $liste_choix[$ordre]['LIBELLE'];
																if($element['TYPE_OBJET']=='liste_radio'){
																		$name				=	$element['CHAMP_PERE']."_0"; 
																}
																elseif($element['TYPE_OBJET']=='liste_checkbox' ){
																		$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])];
																}elseif($element['TYPE_OBJET']=='text_valeur_multiple'){
																		$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																		$name				=	$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($zone_ref['CHAMP_FILS'])];
																}
		
																$html.= "&nbsp;<INPUT NAME='$name' TYPE='".$type_objet."' ".$element['ATTRIB_OBJET']." ";
																//$html.= "VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$element['CHAMP_PERE']].">";
																
																// Modif Alassane
																if (strlen($element['CHAMP_PERE'])>30){
																	if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql'){
																		$taille_max_car =   30;                                                               
																	}else{
																		$taille_max_car =   31;
																	}
																}else{
																	 $taille_max_car = strlen($element['CHAMP_PERE']);
																}                                                        
																
																if($element['TYPE_OBJET']=='text_valeur_multiple'){
																	$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
																	$html.= "".' ID=\''. $element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($zone_ref['CHAMP_FILS'])] .'\' '."VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($zone_ref['CHAMP_FILS'])].">";
																}else{
																	$html.= "".' ID=\''. $element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."VALUE=$".$element['CHAMP_PERE']."_0_".$liste_choix[$ordre][$this->get_champ_extract($element['CHAMP_PERE'])].">";
																}
																// Fin Modif Alassane
																
																//$html.= "</span>";
																$html.= "&nbsp;</TD>\n";
														}
														else{
																if($j==1) $cols_rest = 2;
																else $cols_rest = 1;
																$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
																break;
														}
												}
												$html.= "\t</TR>\n";	
										}// fin for : parcours des lignes 
										if($class_ligne=='ligne-impaire-left') $class_ligne = 'ligne-paire-left'; else  $class_ligne = 'ligne-impaire-left';
									}
									//Remplissage tableaux de fonctions de (des)activation de zones/themes
									$regs = array();
									if(preg_match('/desactiver(.*)\)/i', $element['ATTRIB_OBJET'],$regs)){
										$tab_fonc_Desact_Zones_Themes[$element['CHAMP_PERE']] = preg_replace("/this.value/","eval('\''+val_zone+'\'')",$regs[0]);
									}
							}
							elseif($element['TYPE_OBJET']=='loc_etab'){ /// LOCALISATION ETABLISSEMENT
									lit_libelles_page('/questionnaire.php');
									//print_r($this->conn);
									//echo'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
									$html.= "\t<TR class='$class_ligne'>\n";
									$html.= "\t\t<TD colspan=4 valign='top'>&nbsp;";
									///  donn�es � prendre de quelque part
									$tab_chaines = $this->get_chaines_loc($id_systeme);
									//echo '<pre>';
									//print_r($tab_chaines);
									//$tab_chaines = array();
									//$tab_chaines[] = array( 'numCh' => 2, 'nbRegs' => 4 );
									/// 
									//die();
									$html.= "<table><tr>\n";
									// pour chaque chaine mettre dans un TD
									foreach($tab_chaines as $iLoc => $chaine){
											$requete        = 'SELECT     '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', 
																										'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', 
																										'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', 
																				'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																				FROM        '.$GLOBALS['PARAM']['HIERARCHIE'].' , '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
																				WHERE				'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = 
																										'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
																				AND		      '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$chaine['numCh'].'
																				ORDER BY 		'.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC';
											//echo $requete;
											//exit;
											$aresult = array();
											// Traitement Erreur Cas : GetAll / GetRow
											try {
													$aresult	= $this->conn->GetAll($requete);
													if(!is_array($aresult)){                    
															throw new Exception('ERR_SQL');  
													} 
																						
											}
											catch(Exception $e){
													$erreur = new erreur_manager($e,$requete);
											}
											// Fin Traitement Erreur Cas : GetAll / GetRow
											 
											//$nbTypeReg = count($aresult);
											$html.= "<td valign='top'><table>\n";
											$html.='<tr>'."\n";
											$html.='<td colspan="2" nowrap="nowrap"><b>'.$this->recherche_libelle($chaine['numCh'],$langue,'DICO_CHAINE_LOCALISATION').'</b>&nbsp;&nbsp;&nbsp;</td>'."\n";
											$html.='</tr>'."\n";	
											
											$html.='<tr>'."\n";
											$html.='<td>&nbsp;</td>'."\n";
											$html.='<td nowrap>'."\n";
										   
											$html.= "<INPUT type='text' name='LOC_REG_".$iLoc."' id='LOC_REG_".$iLoc."' value=\"\$LOC_REG_".$iLoc."\" size=5 readonly>\n";    
											$html.= "<INPUT type='button' onClick='OpenPopupLocEtab(".$iLoc.",".$chaine['numCh'].",".$chaine['nbRegs'].");' style='background-image: url(".$GLOBALS['SISED_URL_IMG']."sav_loc.gif); height: 25px; width: 25px;'>\n";
											$html.= "<INPUT type='button' onClick='SuppLocEtab(".$iLoc.",".$chaine['nbRegs'].");' style='background-image: url(".$GLOBALS['SISED_URL_IMG']."sup_loc.gif); height: 25px; width: 25px;'>\n";
											$html.= "</td>\n"; 
											$html.= "</tr>\n";	
												
											foreach($aresult as $iTypeReg => $ligne_type_reg){
											$html.= "<tr>\n"; 
													//$html.= "<td><div style='margin-left: 10px;'>".$this->recherche_libelle($ligne_type_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']],$langue,$GLOBALS['PARAM']['TYPE_REGROUPEMENT'])."</div></td>\n";
													$html.= "<td><div style='margin-left: 10px;'>".$ligne_type_reg[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]."</div></td>\n";
													$html.= "<td><INPUT type='text' value=\"\$LIBELLE_REG_".$iLoc.'_'.$iTypeReg."\" name='LIBELLE_REG_".$iLoc.'_'.$iTypeReg."' id='LIBELLE_REG_".$iLoc.'_'.$iTypeReg."' size='20' readonly></td>\n";
											$html.= "</tr>\n";												
											}
											$html.= "</table></td>\n";
									}
									$html.= "</tr></table>\n";
									$html.= "</TD>\n";
									$html.= "\t</TR>\n";
							}// FIN /// LOCALISATION ETABLISSEMENT
	
							elseif( ((trim($element['NOM_GROUPE']=='')) or (isset($tab_elem_grp[$element['NOM_GROUPE']]) and ( count($tab_elem_grp[$element['NOM_GROUPE']])==1 ) )) && (!isset($element['OBJET_MATRICIEL'])) ){
									// zone non group�e � afficher sur une ligne
									$pass_ligne++;
									if($pass_ligne%2==0) $class_ligne = 'ligne-impaire-left'; else  $class_ligne = 'ligne-paire-left';
									
									if($pass_ligne > 0)
											$html 		.="\t\t"."<TR><TD COLSPAN='4' class='td_space_blanc'></TD></TR>\n";
									$html.= "\t<TR class='$class_ligne'>\n";
									
									if($element['TYPE_OBJET']=='label'){
											$html.= "\t\t<TD colspan='4'>&nbsp;";
											$html.= $this->formulaire_concat_zone_html($element,$langue,$id_systeme);
											$html.= "&nbsp;</TD>\n";
									}
									else{
											$html.= "\t\t<TD colspan='4'>";
											if($element['TYPE_OBJET']<>'hidden_field'){
												$html.= "&nbsp;".$this->recherche_libelle_zone($element['ID_ZONE'],$langue);
												$html.= "&nbsp;&nbsp;&nbsp;";
											}
											$html.= $this->formulaire_concat_zone_html($element,$langue,$id_systeme);
											($element['TYPE_OBJET']<>'hidden_field')? ($html.= "&nbsp;</TD>\n"):($html.= "</TD>\n");
									}
									
									$html.= "\t</TR>\n";
									
									//Remplissage tableaux de fonctions de (des)activation de zones/themes
									$regs = array();
									if(preg_match('/desactiver(.*)\)/i', $element['ATTRIB_OBJET'],$regs)){
										$tab_fonc_Desact_Zones_Themes[$element['CHAMP_PERE']] = preg_replace("/this.value/","eval('\''+val_zone+'\'')",$regs[0]);
									}
							}
	
							elseif( !in_array($element['NOM_GROUPE'], $nom_grp_deja_aff) ){// plusieurs zones group�es
							///
								//echo '<pre>';
								//print_r($element);
								//echo '<br>'.$element['NOM_GROUPE'];
								//die('BASS');
	 
									$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
									$pass_ligne++;
									if($pass_ligne%2==0) $class_ligne = 'ligne-impaire-left'; else  $class_ligne = 'ligne-paire-left';
	
									if($pass_ligne > 0)
											$html 		.="\t\t"."<TR><TD COLSPAN='4' class='td_space_blanc'></TD></TR>\n";
									////////////////////////////////
									$liste_obj		= $tab_elem_grp[$element['NOM_GROUPE']];
									$nb_obj		 		= count($liste_obj) ;
									if($nb_obj==2){ // pour faire 2 colspan de 2
											$html.= "\t<TR class='$class_ligne'>\n";
											for($ordre=0; $ordre<2; $ordre++){
													$police_gras ='';
													if($liste_obj[$ordre]['TYPE_OBJET']=='label')
															$police_gras ="class='police_gras'";
															
													//////////////////////////////////
													/*
													$html.= "\t\t<TD  class='td_left' colspan=2 $police_gras>&nbsp;";
													if($liste_obj[$ordre]['TYPE_OBJET']<>'label'){
															$html.= $this->recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
															$html.= "&nbsp;&nbsp;&nbsp;";
													}
													$html.= $this->formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
													$html.= "&nbsp;</TD>\n";
													*/												
													/////////////////////////////////
													if($liste_obj[$ordre]['TYPE_OBJET']=='label'){
															$html.= "\t\t<TD  class='td_left' colspan=2 $police_gras>&nbsp;";
															$html.= $this->formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
															$html.= "&nbsp;</TD>\n";
													}
													else{ 
															$html.= "\t\t<TD  class='td_left' colspan='2'>&nbsp;";
															$html.= $this->recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
															$html.= "&nbsp;&nbsp;&nbsp;";
															//$html.= "\t\t<TD  class='td_left'>&nbsp;";
															$html.= $this->formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
															$html.= "&nbsp;</TD>\n";
													}
											}
											$html.= "\t</TR>\n";
									}
									else{ 
											$nb_tr 				= intval( ($nb_obj-1) / 3) + 1; // $nb_obj-1 car le premier element est pris comme maitre 
											$reste_div 		= ($nb_obj-1) % 3;
											if( $reste_div > 0 ) $rowspan	=	$nb_tr;
											else $rowspan	=	$nb_tr - 1;
											if($nb_tr > 1) $rowspan_tr = " rowspan='$rowspan' ";
											$html.= "\t<TR class='$class_ligne'>\n";
											$police_gras ='';
													if($liste_obj[0]['TYPE_OBJET']=='label')
															$police_gras ="class='police_gras'";
															
											$html.= "\t\t<TD $rowspan_tr  valign=top $police_gras>&nbsp;";
											if($liste_obj[0]['TYPE_OBJET']<>'label'){
													$html.= $this->recherche_libelle_zone($liste_obj[0]['ID_ZONE'],$langue);
													$html.= "&nbsp;&nbsp;&nbsp;";
											}
											$html.= $this->formulaire_concat_zone_html($liste_obj[0],$langue,$id_systeme);
											$html.= "&nbsp;</TD>\n";
			
											for( $tr=0; $tr<$rowspan; $tr++ ){
													if($tr > 0)
															$html.= "\t<TR class='$class_ligne'>\n";
													for($j=0; $j<3; $j++){
															$ordre = 3 * $tr + $j + 1;
															if(isset($liste_obj[$ordre])){
																	$html.= "\t\t<TD nowrap  class='td_right'>";
																	$html.= $this->recherche_libelle_zone($liste_obj[$ordre]['ID_ZONE'],$langue);
																	if( $liste_obj[$ordre]['TYPE_OBJET'] <> 'label'){
																		$html.= "&nbsp;&nbsp;&nbsp;";
																		$html.= $this->formulaire_concat_zone_html($liste_obj[$ordre],$langue,$id_systeme);
																	}
																	$html.= "&nbsp;</TD>\n";
															}
															else{
																	if($j==1) $cols_rest = 2;
																	else $cols_rest = 1;
																	$html 		.="\t\t"."<TD COLSPAN='$cols_rest'>&nbsp;</TD>\n";
																	break;
															}
													}
											}
											$html.= "\t</TR>\n";	
									}// fin for : parcours des lignes 
									/////////////////////////////////
								}
								
								////Pour les champs calcules dans les formulaires
								if(!(isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true))){
									if($element['TYPE_OBJET']=='text' && $element['AFFICHE_TOTAL']){
										$html_js_tot_chp	= "<script language='javascript' type='text/javascript'>"."\n";
										$html_js_tot_chp	.= 'tab_chps['.$kkk.']'.' = \''.$element['CHAMP_PERE'].'\';'."\n";
										$html_js_tot_chp	.= "</script>\n";
										$kkk++;
										
										$html	.= $html_js_tot_chp ;
										if($element['EXPRESSION']<>''){
											$html_fonc_TotChpsFrml .= "set_Total_ChpsFrml('form1', '".$element['EXPRESSION']."');\n";
										}
									}
								}
								////Fin Pour les champs calcules dans les formulaires
								
								
							}// fin : si type dimension on ignore
			} //Fin de parcours du dico
		

		$html	.= "</TABLE>";

		if(!isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) || (isset($GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && !in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']))){
			$html		.= "\n" . $this->get_html_buttons($langue);
		}else{
			$html		.= "\n" . $this->get_html_buttons_form_gril($langue);
		}
		
		$html 	.= "</FORM>";
		$html	.= "</div>"."\n";
		if(trim($html_fonc_TotMatFrml) <> ''){
			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
			$html	.= $html_fonc_TotMatFrml ;
			$html	.= "</script>"."\n";
		}
		if(isset($html_fonc_TotChpsFrml) && trim($html_fonc_TotChpsFrml) <> ''){
			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
			$html	.= $html_fonc_TotChpsFrml ;
			$html	.= "</script>"."\n";
		}
		/*if(isset($tab_fonc_Desact_Zones_Themes) && count($tab_fonc_Desact_Zones_Themes)>0){
			$html	.= "<script language='javascript' type='text/javascript'>"."\n";
			foreach($tab_fonc_Desact_Zones_Themes as $nom_zone => $fonc_desact){
				$html	.= "var radio     = form1.".$nom_zone."_0;\n";
				$html	.= "var val_zone;\n";
				$html	.= "for (var i=0; i<radio.length;i++) {\n";
				$html	.= "	 if (radio[i].checked) {\n";
				$html	.= "		val_zone=radio[i].value;\n";
				$html	.= "		break;\n";
				$html	.= "	 }\n";
				$html	.= "}\n";
				$html	.= $fonc_desact.";\n" ;
			}
			$html	.= "</script>"."\n";
		}*/
		//$html 			.= "\n</Form>";
		// print '<BR>'.$element['FRAME'];
				// PHP8 fix S17f: $element est garanti defini ici (early return si dico vide)
				if (isset($element) && trim($element['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
						if($code_annee=='' && $code_etablissement=='')	echo $html;
				} elseif (isset($element)) {
						if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
				}
		//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
		//if($code_annee=='' && $code_etablissement=='')	echo $html;
	}//Fin function generer_frame_formulaire

//----------------------------------------------------------------------
//----------------------------------------------------------------------
 		/**
		* METHODE :  generer_frame_grille(id_theme, langue):
		* Effectue la g�n�ration des templates de type grille
		* comme les LOCAUX et les PERSONNELS
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/

		function generer_frame_grille($id_theme, $langue ,$id_systeme){
			
						$requete	="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
											AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
					// Traitement Erreur Cas : GetAll / GetRow
					//echo $requete;
					//exit;
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$this->dico	= $aresult;									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					// Fin Traitement Erreur Cas : GetAll / GetRow
					
					//Recuperation du type de theme
					$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
					$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
					//Fin Recuperation du type de theme	 
											
			// Les Locaux par exemple
			$NB_TOTAL_COL 	= 0;
			$NB_LIGNE_ECRAN	= $this->dico[0]['NB_LIGNES_FRAME']; //Nombre de lignes � afficher
			$dico        	= $this->tri_fils($this->dico);
			$affiche_vertic_mes=0;
			$style_align_middle = '';
			foreach($dico as $dc){
				if($dc['AFFICH_VERTIC_MES']){
					$affiche_vertic_mes=1;
					$style_align_middle = " style='vertical-align:middle;' ";
					break;
				}
			}
			
			$tab_elem_grp 				= array();
			
			$nom_grp_deja_aff 			= array();
			$html_fonc_TotMat_in_Gril 	= '';
			$affiche_totaux_mat_in_Gril	= array();
			$colspan_Total_2Dim			= array();
			$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
	
			$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
			$mess_alert			= addslashes($mess_alert);
			
			$html				  = "<script language='javascript' type='text/javascript'>\n";
			$html 	  	  .= "<!--\n";
			$html 	  	  .= "function Alert_Supp(checkbox){\n";
			$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
			$html					.= "if (eval(chaine_eval)){\n";
			$html 	  	  .= "alert ('$mess_alert');\n";
			$html				  .= "}\n";
			$html				  .= "}\n";
			$html 	  	  .= "//-->\n";
			$html 	  	  .= "</script>\n";
			
			
			$html	.= '<style type="text/css">' . "\n" ;
			$html	.= '	.total {' . "\n" ;
			$html	.= '		background-color:#CCCCCC; ' . "\n" ;
			$html	.= '		font-weight:bold;' . "\n" ;
			$html	.= '	}' . "\n" ;
			$html	.= '</style>' . "\n" ;
							
			//$html 	  	 .= "<BR/>"; 
			$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
					
			//$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
			//$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
			$html 			.= "<div align=center>"."\n";
			$html 			.= "<FORM NAME='form1' ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST'>"."\n";
			$html 			.= "<TABLE class='table-questionnaire' border='1'>"."\n\t";
			$html           .= '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION><TR  style="height:25px;">'."\n\t\t";
			// Calcul du nombre de lignes de libell�s
			$nb_niv_1             = 1;
			$nb_niv_2             = 1;
			foreach($dico as $i_elem => $element){
				if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
					if($nb_niv_1 < 3){
						$nb_niv_1	= 3;
						$nb_niv_2   = 2;
					}
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
						if( trim($element['NOM_GROUPE'] == '') ){
							$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
							$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
						}else{
							$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
							$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
						}
						$dico[$i_elem]['OBJET_MATRICIEL']	= true;
						$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
						
						if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int')){
							$aff_total_vertic = true ;
						}
					}
				}elseif ($element['TYPE_OBJET'] == 'liste_radio' or ($element['TYPE_OBJET'] == 'booleen') 
					or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple') ){
					// S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
					if($nb_niv_1 < 2) $nb_niv_1	= 2;
					//break;
				}
			}
			
			if(!isset($classe_fond)) {
				$classe_fond = 'ligne-paire';
			} else {
				if($classe_fond == 'ligne-paire') {
					$classe_fond = 'ligne-impaire';
				} else {
					$classe_fond = 'ligne-paire';
				}
			}
			if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
				$mat_dim_colonne 		=	array();
				$mat_liste_mes 			= 	array();
				$tab_libelles_mesures 	= 	array();
				$tab_types_mesures 		= 	array();
				$attrib_obj_mesures 	= 	array();
				$elem_obj_mesures 		= 	array();
				$mat_dim_colonne		= 	array();
				$mat_codes_ligne		= 	array();
				$mat_codes_colonne		= 	array();
				$colspan_cell_tot		= 	array();
				
				$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=1 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					
				$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=0 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
			}
			// Affichage de la colonne contenant l'ic�ne de s�lection des �l�ments � supprimer
			$html 			.= "\t\t<TD ROWSPAN=".$nb_niv_1." class='".$classe_fond."' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
			
			//Ajout HEBIE pour affichage de la colonne contenant l'ic�ne de modification d'un �l�ment de la grille
			if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
				$position = array_search(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']);
				if (array_key_exists($position, $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position]<>''){
					$html 			.= "\t\t<TD ROWSPAN=".$nb_niv_1." class='".$classe_fond."' title='".$this->recherche_libelle_page('add_modif_row',$langue,'generer_theme.php')."'><img src='".$GLOBALS['SISED_URL_IMG']."b_edit.png' width='16' height='16'></TD>";
				}
			}
			//Fin Ajout HEBIE
			
			$plus_last_tr_tot 	= 0 ;
			$last_tr_tot = '';
			if( $aff_total_vertic == true ){
				$last_tr_tot 		.= '<tr><td></td>';
				$plus_last_tr_tot 	= 1 ;
			}

			//$html.="\t\t<TD class='ligne-titre' ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1)."></TD>\n";
			// Affichage les entetes
			
			foreach($dico as $i_elem => $element){
				if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
					// Affichage de la colonne vide entre chaque �l�ments de la grille
					 
					// Pour chacun des �l�ments du tableau
					// imprimer l'�ventuel libell� d'ent�te
					// et pr�voir les colspan pour cadrer avec la seconde ligne de libell�s (horizontaux)
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						//r�cup�ration des
						
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							$html	.=	"\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
							
							$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
							if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
								$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
							}
							$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
							
							if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
								$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
								foreach($mat_nomenc_colonne as $mat_i => $mat_val){
									$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
								}
							}
							if( count((array)$mat_codes_ligne[$grp_mat]) || count((array)$mat_codes_colonne[$grp_mat]) ){
								$mat_liste_mes[$grp_mat] 			= array();
								$tab_libelles_mesures[$grp_mat] 	= array();
								$tab_lib_mes_tot[$grp_mat] 			= array();
								$tab_types_mesures[$grp_mat] 		= array();
								$attrib_obj_mesures[$grp_mat] 		= array();
								$elem_obj_mesures[$grp_mat] 		= array();
								$nb_cell_cols_tot = 0 ;
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
										$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
									}
									$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
									$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
									$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
									
									$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
									
									if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
										$affiche_totaux_mat_in_Gril[$grp_mat][$mat_i] 	= true;
										$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
									}
								}
								$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
								$colspan_cell_tot[$grp_mat] = 0 ;
								
								if( count($affiche_totaux_mat_in_Gril[$grp_mat]) ){
									$colspan_cell_tot[$grp_mat] = '' ;
									$nb_cell_cols_tot = 1 ;
									if( count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1 ){
										$colspan_cell_tot[$grp_mat] = ' colspan='.( count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1);
										$nb_cell_cols_tot =  count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1 ;
									}
									$html_js_tot = '' ;
									$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
									$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION']).'";'."\n";
									$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' = '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
									$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
									
									foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
										$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array (';
									
									//if(count($mat_codes_ligne[$grp_mat])){
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$html_js_tot	.= ',"'.$ligne.'"';
									}
									//}
									$html_js_tot	.= ');'."\n";
							
									$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
									
									if(count($mat_codes_colonne[$grp_mat])){
										foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
											$html_js_tot	.= ',"'.$code_col['code'].'"';
										}
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										
									$html_js_tot	.= "</script>"."\n";
									
									$html			.= $html_js_tot ;
																
								}
								
								$colspan_entete_mat = $nb_cell_cols_tot + ( $nb_mesures[$grp_mat] * count($mat_codes_colonne[$grp_mat]) ) ;
								//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
								//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' ;
								
							}			
							// libelle
							
							$html 	.= 	"\t\t<TD style='height:25px;'";
							$html 	.=	" COLSPAN=" . $colspan_entete_mat ;
							$html 	.= 	" class='".$classe_fond."'>";
							$html   .= 	$libelle_mat ;
							$html   .= 	"</TD>\n";
						}
						//die('ici');
					}elseif ( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') 
							or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
							
							$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
							$html 			        .= "\t\t<TD style='height:25px;'";
							
						// Lecture des libell�s de la table de nomenclature
						if ( $element['TYPE_OBJET'] == 'booleen' ) {               
							$result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
						}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
							$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
							$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
						}else{
							$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
						}
						$html 	.= " COLSPAN=" . count($result_nomenc);
						$html 	.= " class='".$classe_fond."'>";
						$html   .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
						$html   .= "</TD>\n";
					}elseif($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text' or $element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' or $element['TYPE_OBJET'] == 'label' or $element['TYPE_OBJET'] == 'checkbox') {              
	
						$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
						$html 		.= "\t\t<TD";
						$html       .= " ROWSPAN=".$nb_niv_1;
						$html 		.= " class='".$classe_fond."'>";
						// Modification Hebie
						//( $element['TAILLE_LIB_VERTICAUX'] ) ? ($taille_max = $element['TAILLE_LIB_VERTICAUX']) : ($taille_max =   30);
						if($element['TAILLE_LIB_VERTICAUX'] <> 0){
							$taille_max = $element['TAILLE_LIB_VERTICAUX'];
							$libelle_afficher   =   $this->limiter_taille_text($this->recherche_libelle_zone($element['ID_ZONE'],$langue),$taille_max);
							$libelle_afficher	.=	'+++';
							$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
							$html               .= 	"</TD>\n";
						}else{
							$html               .= 	$this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
						}
						// Fin Modification Hebie
					}
					//$html                       .= $element['LIBELLE']."</TD>\n";
					// Affichage de la colonne vide entre chaque �l�ments de la grille
					//$html                   .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1)."></TD>\n";
				}
			}
			$html .= "\t</TR>\n";
			$nom_grp_deja_aff 		= array();
			if($nb_niv_1 > 1){
				if(!isset($classe_fond)) {
					$classe_fond = 'ligne-paire';
				} else {
					if($classe_fond == 'ligne-paire') {
						$classe_fond = 'ligne-impaire';
					} else {
						$classe_fond = 'ligne-paire';
					}
				}
				$html .= "\t<TR  style='height:25px;'>\n";
				$niv_3_entete = false ;
				foreach($dico as $i_elem => $element){
					//Pour chacun des �l�ments du tableau (de type liste_radio)
					//imprimer les libell� des nomenclatures (verticaux)
					$cpt++;
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( $nb_mesures[$grp_mat] > 1 ){
								if(count($mat_codes_colonne[$grp_mat])){
									foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
										$html 		.="\t\t"."<TD class='$classe_fond' align='center' COLSPAN=". $nb_mesures[$grp_mat] .">".$elem_col['libelle']."</TD>\n";
									}
								}
								$niv_3_entete = true ;
								$rowspan_tot = '';
							}else{
								if(isset($element['TAILLE_LIB_VERTICAUX'])) 
									$taille_max = $element['TAILLE_LIB_VERTICAUX']; 
								else $taille_max =   30;
								if(count((array)$mat_codes_colonne[$grp_mat])){
									foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
										if($taille_max <> 0){
											$libelle_afficher   =   $this->limiter_taille_text($elem_col['libelle'], $taille_max);
											$html 		.=	"\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."' align='center'></TD>\n";
										}else{
											$html 		.=	"\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'>".$elem_col['libelle']."</TD>\n";
										
										}
									}
								}
								$rowspan_tot = ' ROWSPAN='.$nb_niv_2;
							}
							if(count((array)$affiche_totaux_mat_in_Gril[$grp_mat])){
								$html		.="\t\t<TD bgcolor='#CCCCCC' $rowspan_tot  align='center' ".$colspan_cell_tot[$grp_mat]."><b>";
								$html 		.= $this->recherche_libelle_page('total', $langue, 'questionnaire.php');
								$html		.="</b></TD>\n";
							}
						}
					}elseif( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
						// Lecture des libell�s de la table de nomenclature	
						if($element['TYPE_OBJET']=='booleen'){
							$result_nomenc 	= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
						}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
							$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
							$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
						}else{
							$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
						}
						//print_r($result_nomenc);
						//print '<BR><BR>';
						if(isset($element['TAILLE_LIB_VERTICAUX'])) $taille_max = $element['TAILLE_LIB_VERTICAUX']; 
						else $taille_max =   30;
						
						foreach($result_nomenc as $element_result_nomenc){		
							//Pour chaque libell� de la nomenclature
							// Modification Alassane
							if($taille_max <> 0){
								$libelle_afficher   =   $this->limiter_taille_text($element_result_nomenc['LIBELLE'],$taille_max);
								$html 		.="\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."' align='center'></TD>\n";
							}else{
								$html 		.="\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'>".$element_result_nomenc['LIBELLE']."</TD>\n";
							}
							// Fin Modification alassane
							//$html 		.="\t\t"."<TD nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($element_result_nomenc['LIBELLE'])."' alt='".$element_result_nomenc['LIBELLE']."' align='center'></TD>\n";
						}
					}
				}// Fin de parcours du Dico pour affichage des entetes
				$html .= "\t</TR>\n";
			}
			
			$nom_grp_deja_aff 		= array();
			if( isset($niv_3_entete) && ($niv_3_entete == true)){
				
				if(!isset($classe_fond)) {
					$classe_fond = 'ligne-paire';
				} else {
					if($classe_fond == 'ligne-paire') {
						$classe_fond = 'ligne-impaire';
					} else {
						$classe_fond = 'ligne-paire';
					}
				}
				$html .= "\t<TR>\n";
				foreach($dico as $i_elem => $element){
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( $nb_mesures[$grp_mat] > 1 ){
								foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $mes){
										if(!$element['AFFICH_VERTIC_MES'])
											$html		.="\t\t<TD class='$classe_fond'  align='center'  nowrap='nowrap'>".$tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
										else
											$html		.="\t\t<TD class='$classe_fond'  align='center'  nowrap='nowrap'></TD>\n";
									}
								}
								if(count($affiche_totaux_mat_in_Gril[$grp_mat])){
									foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
										if(!$element['AFFICH_VERTIC_MES'])
											$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'>".$tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
										else
											$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'></TD>\n";
									}
									if(!$element['AFFICH_VERTIC_MES'])
										$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'>".(implode(' + ',$tab_lib_mes_tot[$grp_mat]))."</TD>\n";
									else
										$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'></TD>\n";
								}
							}
						}
					}
				}
				$html .= "\t</TR>\n";
			}elseif($nb_niv_1 > 2){
				$html .= "\t<TR></TR>\n";
			}
			
			
			// Affichage des zones de saisie
			// Autant de fois que lignes � affcher (nbre d�fini dans DICO_THEME.NB_LIGNES)
			for ($j = 0; $j < $NB_LIGNE_ECRAN; $j++){
	
				if(!isset($classe_fond)) {
						$classe_fond = 'ligne-paire';
				} else {
						if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
						} else {
								$classe_fond = 'ligne-paire';
						}
				}
				$nom_grp_deja_aff 		= array();
				$html .= "\t<TR>\n";
	
				// Pour chaque Ligne
				$i_TD = 0;
				if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']))
					$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><INPUT NAME=DELETE"."_".$j." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name); \$disabled></TD>";
				else
					$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><INPUT NAME=DELETE"."_".$j." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
				//$nb_TD = count($dico);
				
				//Ajout HEBIE pour affichage du bouton de modification pour chaque ligne de la grille
				if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
					$position = array_search(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']);
					if (array_key_exists($position, $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position]<>''){
						$thm_fils = $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position];
						//$html .= "<a name='".$j."'></a>";
						$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><a href=\"questionnaire.php?theme_pere=".$id_theme.$id_systeme."&theme=".$thm_fils."&ligne=".$j."\" style=\"text-decoration:none;\"><img src='".$GLOBALS['SISED_URL_IMG']."b_edit.png' width='16' height='16' alt='".$this->recherche_libelle_page('add_modif_row',$langue,'generer_theme.php')."'></a></TD>";
					}
				}
				//Fin Ajout HEBIE
				
							$i_TD++;
			foreach($dico as $element){
									//$i_TD++;
					// Pour chacun des �l�ments du tableau
					// Lecture des libell�s de la table de nomenclature
					// Modif Alassane
					if (strlen($element['CHAMP_PERE'])>30){
						if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql'){
							$taille_max_car =   30;
						}else{
							$taille_max_car =   31;
						}
					}else{
						 $taille_max_car = strlen($element['CHAMP_PERE']);
					}
					// Fin Modif Alassane
				   
				   // Ajout d'une condition de test Alassane
					if ( trim($element['TABLE_FILLE'])<>'' and trim($element['CHAMP_FILS'])<>'' ) {
						$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
					}
					elseif (trim($element['TYPE_OBJET']) == 'booleen') {
						$result_nomenc 	= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
					}
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( count((array)$affiche_totaux_mat_in_Gril[$grp_mat]) && ($j == 0) ){	
								$html_fonc_TotMat_in_Gril 		.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
							}
							$cpte_col = 0;
							foreach((array)$mat_codes_colonne[$grp_mat] as $col => $elem_col){
								$cpte_col++;
								//si affichage horizontal
								if(!$element['AFFICH_VERTIC_MES']){
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
									  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
										$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
										$html		.= $this->get_cell_matrice($j , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
										$html		.= "</TD>\n";
										if( $affiche_totaux_mat_in_Gril[$grp_mat][$i_mes] == true ){	
											$set_TOTAL_MatFrml_Chp 		= 	' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$j.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
											
											if( $aff_total_vertic == true && ($j == 0) ){
												$last_tr_tot .= '<td align="center" bgcolor="#CCCCCC"><INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'></td>'  ;
											}
										}elseif( $aff_total_vertic == true && ($j == 0) ){
											$last_tr_tot .= '<td></td>'  ;
										}
										$i_TD++ ;
									}
								}else{
									//si affichage vertical
									$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle;' COLSPAN=". $nb_mesures[$grp_mat] ." CLASS='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
									$cpt_mes = 0;
									$debut_last_tr_tot = true;
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
									  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
									    if($cpte_col==1) $html .= "&nbsp;".$tab_libelles_mesures[$grp_mat][$i_mes]."&nbsp;";
										$html		.= $this->get_cell_matrice($j , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
										if($cpt_mes++ < $nb_mesures[$grp_mat]) $html .= '<br/>';
										if( $affiche_totaux_mat_in_Gril[$grp_mat][$i_mes] == true ){	
											$set_TOTAL_MatFrml_Chp 		= 	' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$j.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
											
											if( $aff_total_vertic == true && ($j == 0) ){
												if($debut_last_tr_tot){
													$last_tr_tot .= '<td nowrap="nowrap" style="vertical-align:middle;" align="center" bgcolor="#CCCCCC" COLSPAN='. $nb_mesures[$grp_mat] .' ><INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'>'  ;
													$debut_last_tr_tot = false;
												}else{
													$last_tr_tot .= '<INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'>'  ;
												}
												if($cpt_mes < $nb_mesures[$grp_mat]) $last_tr_tot .= '<br/>';
											}
										}elseif( $aff_total_vertic == true && ($j == 0) ){
											$last_tr_tot .= '<td></td>'  ;
										}
									}
									if( ($debut_last_tr_tot == false) && ($aff_total_vertic == true) && ($j == 0) ){
										$last_tr_tot .= '</td>'  ;
									}
									$html		.= "</TD>\n";
									$i_TD++ ;
									//
								}
							}
							
							//si affichage horizontal
							if(!$element['AFFICH_VERTIC_MES']){
								if(count((array)$affiche_totaux_mat_in_Gril[$grp_mat])){
									if (count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1) {
										foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
											$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td><INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
										}
									}
									$html	.= "\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td><INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
								}
							}else{
							//si affichage vertical
								if(count((array)$affiche_totaux_mat_in_Gril[$grp_mat])){
									if (count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1) {
										$html		.="\t\t<TD nowrap='nowrap'  style='vertical-align:middle;' $rowspan_chp_tot COLSPAN=". $nb_mesures[$grp_mat] ." align='center' bgcolor='#CCCCCC'>";
										$cpt_mes = 0;
										$debut_last_tr_tot = true;
										foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
											$html		.=" $br_chp_tot <INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">\n";
											if($cpt_mes++ < $nb_mesures[$grp_mat]) $html .= '<br/>';
											if($debut_last_tr_tot){
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td nowrap='nowrap'  style='vertical-align:middle;'align='center' bgcolor='#CCCCCC' COLSPAN=". $nb_mesures[$grp_mat] ." ><INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">" ) : ( $last_tr_tot .= '' ) ;
												$debut_last_tr_tot = false;
											}else{
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">" ) : ( $last_tr_tot .= '' ) ;
											}
											if($cpt_mes < $nb_mesures[$grp_mat]) $last_tr_tot .= '<br/>';
										}
										if( ($debut_last_tr_tot == false) && ($aff_total_vertic == true) && ($j == 0) ){
											$last_tr_tot .= '</td>'  ;
										}
										$html	.= "</TD>\n";
										
									}
									$html	.= "\t\t<TD nowrap='nowrap' $rowspan_chp_tot align='center'  style='vertical-align:middle;' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td style='vertical-align:middle;' align='center' bgcolor='#CCCCCC' ><INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
								}
							}
							//
						}
					}
					else{
						switch ($element['TYPE_OBJET']){
							case 'liste_radio':
							case 'booleen':
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='radio'";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									// Fin Modif Alassane
									$html       .= ">";
															  $html       .= "</TD>\n";
																$i_TD++;
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							case 'liste_checkbox':
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]." TYPE='checkbox'";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									// Fin Modif Alassane
									$html       .= ">";
															  $html       .= "</TD>\n";
																$i_TD++;
								( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							case 'text_valeur_multiple':{
								$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
								$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])]." TYPE='text' ";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= ' VALUE="$'.$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])].'"'; 
									// Fin Modif Alassane
									//if ($element['ATTRIB_OBJET'] != '') {
									$html   .= ' ' .$element['ATTRIB_OBJET'];
									//}
									$html       .= ">";
									  $html       .= "</TD>\n";
										$i_TD++;
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							}
							case 'combo':
							case 'systeme_combo':
															
								$html		.= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><SELECT ".$element['ATTRIB_OBJET']." ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j.">\n";
														$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n";
								////
								$dynamic_content_object = false ;
								if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
									//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
										$dynamic_content_object = true ;
									//}
								}
								if($dynamic_content_object == true){
									$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$j.'-->'."\n"; 
								}else{
									foreach($result_nomenc as $element_result_nomenc){
										//Un valeur du combo pour chaque valeur de la nomenclature 
										$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
								
										$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
									}
								}
								/////						
														$html 		.= "\t\t</SELECT></TD>\n";
														$i_TD++;
														( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								break;
						case 'text':
						case 'systeme_text':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."'  id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='text'";
							if ($element['ATTRIB_OBJET'] != '') {
								$html           .= $element['ATTRIB_OBJET'];
							}
							$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$j."\""; 
							$html       		.= ">";
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
												
						case 'checkbox':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."'  id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='checkbox'";
							$html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$j.""; 
							$html       		.= ">";
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
					   
						case 'label':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
							$html 	        .= '$'.$element['CHAMP_PERE']."_".$j.""; 
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
						case 'hidden_field':
							$html 	        .= "\t\t<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='hidden'";
							$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$j."\""; 
							$html       		.= ">";
							break;
						}// Fin switch
					} // fin else
				}// Fin de parcours du Dico pour affichage des zones de saisie
							$html .= "\t</TR>\n";
							
			}
					( $aff_total_vertic == true ) ? ( $last_tr_tot .= '</tr>' ) : ( $last_tr_tot .= '' ) ;
					if( trim($last_tr_tot) <> '' ){
						if (preg_match ("/#([^#]*)#/", $last_tr_tot, $elem)){
							
						}
						//$html		.= $last_tr_tot;//A revoir modif (mise en commentaire) benin 24 02 2015
					}
					$html		.= "</TABLE>";
	
			$html		.= "\n" . $this->get_html_buttons($langue);
			
			$html 	.= "</FORM>";
			$html	.= "</div>"."\n";
			if(trim($html_fonc_TotMat_in_Gril) <> ''){
				$html	.= "<script language='javascript' type='text/javascript'>"."\n";
				$html	.= $html_fonc_TotMat_in_Gril ;
				$html	.= "</script>"."\n";
			}
	
			//print '<BR>'.$element['FRAME'];
					if (trim($element['FRAME']) <> '') {
							file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
							echo $html;
					}else{
							if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
					}
				//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
			//echo $html; 
		}//Fin function generer_frame_grille_ligne

//---------------------------------------------------------------------
//---------------------------------------------------------------------
/**
		* METHODE :  generer_frame_grille_fix_lig(id_theme, langue):
		* Effectue la g�n�ration des templates de type grille
		* comme les LOCAUX et les PERSONNELS
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/

		function generer_frame_grille_fix_lig($id_theme, $langue ,$id_systeme){
			
						$requete	="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
											AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
					// Traitement Erreur Cas : GetAll / GetRow
					//echo $requete;
					//exit;
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$this->dico	= $aresult;									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					// Fin Traitement Erreur Cas : GetAll / GetRow
					
					//Recuperation du type de theme
					$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
					$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
					//Fin Recuperation du type de theme	 
											
			// Les Locaux par exemple
			$NB_TOTAL_COL 	= 0;
			$NB_LIGNE_ECRAN	= $this->dico[0][NB_LIGNES_FRAME]; //Nombre de lignes � afficher
			$dico        	= $this->tri_fils($this->dico);
			$affiche_vertic_mes=0;
			$style_align_middle = '';
			foreach($dico as $dc){
				if($dc['AFFICH_VERTIC_MES']){
					$affiche_vertic_mes=1;
					$style_align_middle = " style='vertical-align:middle;' ";
					break;
				}
			}
			
			$tab_elem_grp 				= array();
			
			$nom_grp_deja_aff 			= array();
			$html_fonc_TotMat_in_Gril 	= '';
			$affiche_totaux_mat_in_Gril	= array();
			$colspan_Total_2Dim			= array();
			$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
	
			$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
			$mess_alert			= addslashes($mess_alert);
			
			$html				  = "<script language='javascript' type='text/javascript'>\n";
			$html 	  	  .= "<!--\n";
			$html 	  	  .= "function Alert_Supp(checkbox){\n";
			$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
			$html					.= "if (eval(chaine_eval)){\n";
			$html 	  	  .= "alert ('$mess_alert');\n";
			$html				  .= "}\n";
			$html				  .= "}\n";
			$html 	  	  .= "//-->\n";
			$html 	  	  .= "</script>\n";
			
			
			$html	.= '<style type="text/css">' . "\n" ;
			$html	.= '	.total {' . "\n" ;
			$html	.= '		background-color:#CCCCCC; ' . "\n" ;
			$html	.= '		font-weight:bold;' . "\n" ;
			$html	.= '	}' . "\n" ;
			$html	.= '</style>' . "\n" ;
							
			//$html 	  	 .= "<BR/>"; 
			$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
					
			//$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
			//$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
			$html 			.= "<div align=center>"."\n";
			$html 			.= "<FORM NAME='form1' ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST'>"."\n";
			$html 			.= "<TABLE class='table-questionnaire' border='1'>"."\n\t";
			$html           .= '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION><TR  style="height:25px;">'."\n\t\t";
			// Calcul du nombre de lignes de libell�s
			$nb_niv_1             = 1;
			$nb_niv_2             = 1;
			foreach($dico as $i_elem => $element){
				if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
					if($nb_niv_1 < 3){
						$nb_niv_1	= 3;
						$nb_niv_2   = 2;
					}
					if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
						if( trim($element['NOM_GROUPE'] == '') ){
							$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
							$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
						}else{
							$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
							$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
						}
						$dico[$i_elem]['OBJET_MATRICIEL']	= true;
						$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
						
						if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int')){
							$aff_total_vertic = true ;
						}
					}
				}elseif ($element['TYPE_OBJET'] == 'liste_radio' or ($element['TYPE_OBJET'] == 'booleen') 
					or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple') ){
					// S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
					if($nb_niv_1 < 2) $nb_niv_1	= 2;
					//break;
				}
			}
			
			if(!isset($classe_fond)) {
				$classe_fond = 'ligne-paire';
			} else {
				if($classe_fond == 'ligne-paire') {
					$classe_fond = 'ligne-impaire';
				} else {
					$classe_fond = 'ligne-paire';
				}
			}
			if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
				$mat_dim_colonne 		=	array();
				$mat_liste_mes 			= 	array();
				$tab_libelles_mesures 	= 	array();
				$tab_types_mesures 		= 	array();
				$attrib_obj_mesures 	= 	array();
				$elem_obj_mesures 		= 	array();
				$mat_dim_colonne		= 	array();
				$mat_codes_ligne		= 	array();
				$mat_codes_colonne		= 	array();
				$colspan_cell_tot		= 	array();
				
				$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=1 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					
				$requete        = "SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=0 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
			}
			// Affichage de la colonne contenant l'ic�ne de s�lection des �l�ments � supprimer
			$html 			.= "\t\t<TD ROWSPAN=".$nb_niv_1." class='".$classe_fond."' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
			
			//Ajout HEBIE pour affichage de la colonne contenant l'ic�ne de modification d'un �l�ment de la grille
			if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
				$position = array_search(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']);
				if (array_key_exists($position, $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position]<>''){
					$html 			.= "\t\t<TD ROWSPAN=".$nb_niv_1." class='".$classe_fond."' title='".$this->recherche_libelle_page('add_modif_row',$langue,'generer_theme.php')."'><img src='".$GLOBALS['SISED_URL_IMG']."b_edit.png' width='16' height='16'></TD>";
				}
			}
			//Fin Ajout HEBIE
			
			$plus_last_tr_tot 	= 0 ;
			$last_tr_tot = '';
			if( $aff_total_vertic == true ){
				$last_tr_tot 		.= '<tr><td></td>';
				$plus_last_tr_tot 	= 1 ;
			}

			//$html.="\t\t<TD class='ligne-titre' ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1)."></TD>\n";
			// Affichage les entetes
			
			foreach($dico as $i_elem => $element){
				if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){ // si type dimension on ignore
					// Affichage de la colonne vide entre chaque �l�ments de la grille
					 
					// Pour chacun des �l�ments du tableau
					// imprimer l'�ventuel libell� d'ent�te
					// et pr�voir les colspan pour cadrer avec la seconde ligne de libell�s (horizontaux)
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						//r�cup�ration des
						
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							$html	.=	"\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
							
							$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
							if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
								$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
							}
							$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
							
							if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
								$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
								foreach($mat_nomenc_colonne as $mat_i => $mat_val){
									$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
								}
							}
							if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
								$mat_liste_mes[$grp_mat] 			= array();
								$tab_libelles_mesures[$grp_mat] 	= array();
								$tab_lib_mes_tot[$grp_mat] 			= array();
								$tab_types_mesures[$grp_mat] 		= array();
								$attrib_obj_mesures[$grp_mat] 		= array();
								$elem_obj_mesures[$grp_mat] 		= array();
								$nb_cell_cols_tot = 0 ;
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
										$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
									}
									$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
									$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
									$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
									
									$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
									
									if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
										$affiche_totaux_mat_in_Gril[$grp_mat][$mat_i] 	= true;
										$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
									}
								}
								$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
								$colspan_cell_tot[$grp_mat] = 0 ;
								
								if( count($affiche_totaux_mat_in_Gril[$grp_mat]) ){
									$colspan_cell_tot[$grp_mat] = '' ;
									$nb_cell_cols_tot = 1 ;
									if( count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1 ){
										$colspan_cell_tot[$grp_mat] = ' colspan='.( count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1);
										$nb_cell_cols_tot =  count($affiche_totaux_mat_in_Gril[$grp_mat]) + 1 ;
									}
									$html_js_tot = '' ;
									$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
									$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION']).'";'."\n";
									$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' = '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
									$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
									
									foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
										$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array (';
									
									//if(count($mat_codes_ligne[$grp_mat])){
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$html_js_tot	.= ',"'.$ligne.'"';
									}
									//}
									$html_js_tot	.= ');'."\n";
							
									$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
									
									if(count($mat_codes_colonne[$grp_mat])){
										foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
											$html_js_tot	.= ',"'.$code_col['code'].'"';
										}
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										
									$html_js_tot	.= "</script>"."\n";
									
									$html			.= $html_js_tot ;
																
								}
								
								$colspan_entete_mat = $nb_cell_cols_tot + ( $nb_mesures[$grp_mat] * count($mat_codes_colonne[$grp_mat]) ) ;
								//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
								//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' ;
								
							}			
							// libelle
							
							$html 	.= 	"\t\t<TD style='height:25px;'";
							$html 	.=	" COLSPAN=" . $colspan_entete_mat ;
							$html 	.= 	" class='".$classe_fond."'>";
							$html   .= 	$libelle_mat ;
							$html   .= 	"</TD>\n";
						}
						//die('ici');
					}elseif ( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') 
							or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
							
							$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
							$html 			        .= "\t\t<TD style='height:25px;'";
							
						// Lecture des libell�s de la table de nomenclature
						if ( $element['TYPE_OBJET'] == 'booleen' ) {               
							$result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
						}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
							$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
							$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
						}else{
							$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
						}
						$html 	.= " COLSPAN=" . count($result_nomenc);
						$html 	.= " class='".$classe_fond."'>";
						$html   .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
						$html   .= "</TD>\n";
					}elseif($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text' or $element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' or $element['TYPE_OBJET'] == 'label' or $element['TYPE_OBJET'] == 'checkbox') {              
	
						$html .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1+$plus_last_tr_tot)." style='width: 1px; padding: 1px;'></TD>\n";
						$html 		.= "\t\t<TD";
						$html       .= " ROWSPAN=".$nb_niv_1;
						$html 		.= " class='".$classe_fond."'>";
						// Modification Hebie
						//( $element['TAILLE_LIB_VERTICAUX'] ) ? ($taille_max = $element['TAILLE_LIB_VERTICAUX']) : ($taille_max =   30);
						if($element['TAILLE_LIB_VERTICAUX'] <> 0){
							$taille_max = $element['TAILLE_LIB_VERTICAUX'];
							$libelle_afficher   =   $this->limiter_taille_text($this->recherche_libelle_zone($element['ID_ZONE'],$langue),$taille_max);
							$libelle_afficher	.=	'+++';
							$html               .= 	"<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
							$html               .= 	"</TD>\n";
						}else{
							$html               .= 	$this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
						}
						// Fin Modification Hebie
					}
					//$html                       .= $element['LIBELLE']."</TD>\n";
					// Affichage de la colonne vide entre chaque �l�ments de la grille
					//$html                   .="\t\t<TD ROWSPAN=".($NB_LIGNE_ECRAN+$nb_niv_1)."></TD>\n";
				}
			}
			$html .= "\t</TR>\n";
			$nom_grp_deja_aff 		= array();
			if($nb_niv_1 > 1){
				if(!isset($classe_fond)) {
					$classe_fond = 'ligne-paire';
				} else {
					if($classe_fond == 'ligne-paire') {
						$classe_fond = 'ligne-impaire';
					} else {
						$classe_fond = 'ligne-paire';
					}
				}
				$html .= "\t<TR  style='height:25px;'>\n";
				$niv_3_entete = false ;
				foreach($dico as $i_elem => $element){
					//Pour chacun des �l�ments du tableau (de type liste_radio)
					//imprimer les libell� des nomenclatures (verticaux)
					$cpt++;
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( $nb_mesures[$grp_mat] > 1 ){
								if(count($mat_codes_colonne[$grp_mat])){
									foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
										$html 		.="\t\t"."<TD class='$classe_fond' align='center' COLSPAN=". $nb_mesures[$grp_mat] .">".$elem_col['libelle']."</TD>\n";
									}
								}
								$niv_3_entete = true ;
								$rowspan_tot = '';
							}else{
								if(isset($element['TAILLE_LIB_VERTICAUX'])) 
									$taille_max = $element['TAILLE_LIB_VERTICAUX']; 
								else $taille_max =   30;
								if(count($mat_codes_colonne[$grp_mat])){
									foreach ($mat_codes_colonne[$grp_mat] as $col => $elem_col){
										if($taille_max <> 0){
											$libelle_afficher   =   $this->limiter_taille_text($elem_col['libelle'], $taille_max);
											$html 		.=	"\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."' align='center'></TD>\n";
										}else{
											$html 		.=	"\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'>".$elem_col['libelle']."</TD>\n";
										
										}
									}
								}
								$rowspan_tot = ' ROWSPAN='.$nb_niv_2;
							}
							if(count($affiche_totaux_mat_in_Gril[$grp_mat])){
								$html		.="\t\t<TD bgcolor='#CCCCCC' $rowspan_tot  align='center' ".$colspan_cell_tot[$grp_mat]."><b>";
								$html 		.= $this->recherche_libelle_page('total', $langue, 'questionnaire.php');
								$html		.="</b></TD>\n";
							}
						}
					}elseif( ($element['TYPE_OBJET'] == 'liste_radio') or ($element['TYPE_OBJET'] == 'booleen') or ($element['TYPE_OBJET']=='liste_checkbox') or ($element['TYPE_OBJET']=='text_valeur_multiple')){
						// Lecture des libell�s de la table de nomenclature	
						if($element['TYPE_OBJET']=='booleen'){
							$result_nomenc 	= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
						}elseif ( $element['TYPE_OBJET'] == 'text_valeur_multiple' ) { 
							$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
							$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
						}else{
							$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
						}
						//print_r($result_nomenc);
						//print '<BR><BR>';
						if(isset($element['TAILLE_LIB_VERTICAUX'])) $taille_max = $element['TAILLE_LIB_VERTICAUX']; 
						else $taille_max =   30;
						
						foreach($result_nomenc as $element_result_nomenc){		
							//Pour chaque libell� de la nomenclature
							// Modification Alassane
							if($taille_max <> 0){
								$libelle_afficher   =   $this->limiter_taille_text($element_result_nomenc['LIBELLE'],$taille_max);
								$html 		.="\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."' align='center'></TD>\n";
							}else{
								$html 		.="\t\t"."<TD class='$classe_fond' style='height:25px;' ROWSPAN=".$nb_niv_2." nowrap  align='center'>".$element_result_nomenc['LIBELLE']."</TD>\n";
							}
							// Fin Modification alassane
							//$html 		.="\t\t"."<TD nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($element_result_nomenc['LIBELLE'])."' alt='".$element_result_nomenc['LIBELLE']."' align='center'></TD>\n";
						}
					}
				}// Fin de parcours du Dico pour affichage des entetes
				$html .= "\t</TR>\n";
			}
			
			$nom_grp_deja_aff 		= array();
			if( isset($niv_3_entete) && ($niv_3_entete == true)){
				
				if(!isset($classe_fond)) {
					$classe_fond = 'ligne-paire';
				} else {
					if($classe_fond == 'ligne-paire') {
						$classe_fond = 'ligne-impaire';
					} else {
						$classe_fond = 'ligne-paire';
					}
				}
				$html .= "\t<TR>\n";
				foreach($dico as $i_elem => $element){
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( $nb_mesures[$grp_mat] > 1 ){
								foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $mes){
										if(!$element['AFFICH_VERTIC_MES'])
											$html		.="\t\t<TD class='$classe_fond'  align='center'  nowrap='nowrap'>".$tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
										else
											$html		.="\t\t<TD class='$classe_fond'  align='center'  nowrap='nowrap'></TD>\n";
									}
								}
								if(count($affiche_totaux_mat_in_Gril[$grp_mat])){
									foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
										if(!$element['AFFICH_VERTIC_MES'])
											$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'>".$tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
										else
											$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'></TD>\n";
									}
									if(!$element['AFFICH_VERTIC_MES'])
										$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'>".(implode(' + ',$tab_lib_mes_tot[$grp_mat]))."</TD>\n";
									else
										$html		.="\t\t<TD bgcolor='#CCCCCC' class='$classe_fond' align='center' nowrap='nowrap'></TD>\n";
								}
							}
						}
					}
				}
				$html .= "\t</TR>\n";
			}elseif($nb_niv_1 > 2){
				$html .= "\t<TR></TR>\n";
			}
			
			
			// Affichage des zones de saisie
			// Autant de fois que lignes � affcher (nbre d�fini dans DICO_THEME.NB_LIGNES)
			for ($j = 0; $j < $NB_LIGNE_ECRAN; $j++){
	
				if(!isset($classe_fond)) {
						$classe_fond = 'ligne-paire';
				} else {
						if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
						} else {
								$classe_fond = 'ligne-paire';
						}
				}
				$nom_grp_deja_aff 		= array();
				$html .= "\t<TR>\n";
	
				// Pour chaque Ligne
				$i_TD = 0;
				if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']))
					$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><INPUT NAME=DELETE"."_".$j." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name); \$disabled></TD>";
				else
					$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)' title='".$this->recherche_libelle_page('delete_row',$langue,'generer_theme.php')."'><INPUT NAME=DELETE"."_".$j." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
				//$nb_TD = count($dico);
				
				//Ajout HEBIE pour affichage du bouton de modification pour chaque ligne de la grille
				if(isset($GLOBALS['PARAM']['TEACHERS_MANAGEMENT']) && $GLOBALS['PARAM']['TEACHERS_MANAGEMENT'] && in_array(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES'])){
					$position = array_search(''.$id_theme.$id_systeme,$GLOBALS['PARAM']['TEACHERS_LIST_THEMES']);
					if (array_key_exists($position, $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES']) && $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position]<>''){
						$thm_fils = $GLOBALS['PARAM']['TEACHER_DETAILS_THEMES'][$position];
						//$html .= "<a name='".$j."'></a>";
						$html .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><a href=\"questionnaire.php?theme_pere=".$id_theme.$id_systeme."&theme=".$thm_fils."&ligne=".$j."\" style=\"text-decoration:none;\"><img src='".$GLOBALS['SISED_URL_IMG']."b_edit.png' width='16' height='16' alt='".$this->recherche_libelle_page('add_modif_row',$langue,'generer_theme.php')."'></a></TD>";
					}
				}
				//Fin Ajout HEBIE
				
							$i_TD++;
			foreach($dico as $element){
									//$i_TD++;
					// Pour chacun des �l�ments du tableau
					// Lecture des libell�s de la table de nomenclature
					// Modif Alassane
					if (strlen($element['CHAMP_PERE'])>30){
						if ($this->conn->databaseType == 'mssqlnative' || $this->conn->databaseType == 'mssql'){
							$taille_max_car =   30;
						}else if ($this->conn->databaseType == 'postgres9') {
							$taille_max_car=strlen($element['CHAMP_PERE']);
						}else{
							$taille_max_car =   31;
						}
					}else{
						 $taille_max_car = strlen($element['CHAMP_PERE']);
					}
					// Fin Modif Alassane
				   
				   // Ajout d'une condition de test Alassane
					if ( trim($element['TABLE_FILLE'])<>'' and trim($element['CHAMP_FILS'])<>'' ) {
						$result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
					}
					elseif (trim($element['TYPE_OBJET']) == 'booleen') {
						$result_nomenc 	= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
					}
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) ){
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							if( count($affiche_totaux_mat_in_Gril[$grp_mat]) && ($j == 0) ){	
								$html_fonc_TotMat_in_Gril 		.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
							}
							$cpte_col = 0;
							foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){
								$cpte_col++;
								//si affichage horizontal
								if(!$element['AFFICH_VERTIC_MES']){
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
									  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
										$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
										$html		.= $this->get_cell_matrice($j , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
										$html		.= "</TD>\n";
										if( $affiche_totaux_mat_in_Gril[$grp_mat][$i_mes] == true ){	
											$set_TOTAL_MatFrml_Chp 		= 	' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$j.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
											
											if( $aff_total_vertic == true && ($j == 0) ){
												$last_tr_tot .= '<td align="center" bgcolor="#CCCCCC"><INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'></td>'  ;
											}
										}elseif( $aff_total_vertic == true && ($j == 0) ){
											$last_tr_tot .= '<td></td>'  ;
										}
										$i_TD++ ;
									}
								}else{
									//si affichage vertical
									$html		.= "\t\t<TD nowrap='nowrap' align='center' style='vertical-align:middle;' COLSPAN=". $nb_mesures[$grp_mat] ." CLASS='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
									$cpt_mes = 0;
									$debut_last_tr_tot = true;
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
									  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
									    if($cpte_col==1) $html .= "&nbsp;".$tab_libelles_mesures[$grp_mat][$i_mes]."&nbsp;";
										$html		.= $this->get_cell_matrice($j , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
										if($cpt_mes++ < $nb_mesures[$grp_mat]) $html .= '<br/>';
										if( $affiche_totaux_mat_in_Gril[$grp_mat][$i_mes] == true ){	
											$set_TOTAL_MatFrml_Chp 		= 	' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$j.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
											
											if( $aff_total_vertic == true && ($j == 0) ){
												if($debut_last_tr_tot){
													$last_tr_tot .= '<td nowrap="nowrap" style="vertical-align:middle;" align="center" bgcolor="#CCCCCC" COLSPAN='. $nb_mesures[$grp_mat] .' ><INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'>'  ;
													$debut_last_tr_tot = false;
												}else{
													$last_tr_tot .= '<INPUT TYPE= "text" readonly="1" class="total" NAME="TOT_'.$element['ID_ZONE'].'_'.$mat_liste_mes[$grp_mat][$i_mes].'_COLONNE_'.$elem_col['code'].'" '.$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].'>'  ;
												}
												if($cpt_mes < $nb_mesures[$grp_mat]) $last_tr_tot .= '<br/>';
											}
										}elseif( $aff_total_vertic == true && ($j == 0) ){
											$last_tr_tot .= '<td></td>'  ;
										}
									}
									if( ($debut_last_tr_tot == false) && ($aff_total_vertic == true) && ($j == 0) ){
										$last_tr_tot .= '</td>'  ;
									}
									$html		.= "</TD>\n";
									$i_TD++ ;
									//
								}
							}
							
							//si affichage horizontal
							if(!$element['AFFICH_VERTIC_MES']){
								if(count($affiche_totaux_mat_in_Gril[$grp_mat])){
									if (count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1) {
										foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
											$html		.="\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td><INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
										}
									}
									$html	.= "\t\t<TD $rowspan_chp_tot align='center' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td><INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
								}
							}else{
							//si affichage vertical
								if(count($affiche_totaux_mat_in_Gril[$grp_mat])){
									if (count($affiche_totaux_mat_in_Gril[$grp_mat]) > 1) {
										$html		.="\t\t<TD nowrap='nowrap'  style='vertical-align:middle;' $rowspan_chp_tot COLSPAN=". $nb_mesures[$grp_mat] ." align='center' bgcolor='#CCCCCC'>";
										$cpt_mes = 0;
										$debut_last_tr_tot = true;
										foreach($affiche_totaux_mat_in_Gril[$grp_mat] as $i_mes => $tot_mes){
											$html		.=" $br_chp_tot <INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">\n";
											if($cpt_mes++ < $nb_mesures[$grp_mat]) $html .= '<br/>';
											if($debut_last_tr_tot){
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td nowrap='nowrap'  style='vertical-align:middle;'align='center' bgcolor='#CCCCCC' COLSPAN=". $nb_mesures[$grp_mat] ." ><INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">" ) : ( $last_tr_tot .= '' ) ;
												$debut_last_tr_tot = false;
											}else{
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<INPUT TYPE= 'text' readonly='1'  class='total' NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET'].">" ) : ( $last_tr_tot .= '' ) ;
											}
											if($cpt_mes < $nb_mesures[$grp_mat]) $last_tr_tot .= '<br/>';
										}
										if( ($debut_last_tr_tot == false) && ($aff_total_vertic == true) && ($j == 0) ){
											$last_tr_tot .= '</td>'  ;
										}
										$html	.= "</TD>\n";
										
									}
									$html	.= "\t\t<TD nowrap='nowrap' $rowspan_chp_tot align='center'  style='vertical-align:middle;' bgcolor='#CCCCCC'>"." $br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$j."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= "<td style='vertical-align:middle;' align='center' bgcolor='#CCCCCC' ><INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;' NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></td>" ) : ( $last_tr_tot .= '' ) ;
								}
							}
							//
						}
					}
					else{
						switch ($element['TYPE_OBJET']){
							case 'liste_radio':
							case 'booleen':
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='radio'";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									// Fin Modif Alassane
									$html       .= ">";
															  $html       .= "</TD>\n";
																$i_TD++;
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							case 'liste_checkbox':
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]." TYPE='checkbox'";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									// Fin Modif Alassane
									$html       .= ">";
															  $html       .= "</TD>\n";
																$i_TD++;
								( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							case 'text_valeur_multiple':{
								$zone_ref = get_zone_ref_of($element['ID_ZONE_REF']);
								$result_nomenc 		= $this->requete_nomenclature($zone_ref['TABLE_FILLE'],$zone_ref['CHAMP_FILS'], $zone_ref['CHAMP_FILS'], $langue ,$id_systeme, $zone_ref['SQL_REQ']);
								foreach($result_nomenc as $element_result_nomenc){
									//Un bouton radio pour chaque valeur de la nomenclature
									$html       .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])]." TYPE='text' ";
									//TODO: voir si on ne peut pas �valuer: '$element_result_nomenc[CODE_'.$element[TABLE_MERE]]'
									//Cela permettrai de se passer de "CHAMP_PERE"
									//$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$element['CHAMP_PERE']]; 
									// Modif Alassane
									$html       .= ' VALUE="$'.$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($zone_ref['CHAMP_FILS'])].'"'; 
									// Fin Modif Alassane
									//if ($element['ATTRIB_OBJET'] != '') {
									$html   .= ' ' .$element['ATTRIB_OBJET'];
									//}
									$html       .= ">";
									  $html       .= "</TD>\n";
										$i_TD++;
									( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								}
								break;
							}
							case 'combo':
							case 'systeme_combo':
															
								$html		.= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><SELECT ".$element['ATTRIB_OBJET']." ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j.">\n";
														$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n";
								////
								$dynamic_content_object = false ;
								if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
									//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
										$dynamic_content_object = true ;
									//}
								}
								if($dynamic_content_object == true){
									$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$j.'-->'."\n"; 
								}else{
									foreach($result_nomenc as $element_result_nomenc){
										//Un valeur du combo pour chaque valeur de la nomenclature 
										$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$j."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
								
										$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
									}
								}
								/////						
														$html 		.= "\t\t</SELECT></TD>\n";
														$i_TD++;
														( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
								break;
						case 'text':
						case 'systeme_text':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."'  id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='text'";
							if ($element['ATTRIB_OBJET'] != '') {
								$html           .= $element['ATTRIB_OBJET'];
							}
							$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$j."\""; 
							$html       		.= ">";
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
												
						case 'checkbox':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."'  id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='checkbox'";
							$html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$j.""; 
							$html       		.= ">";
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
					   
						case 'label':
							$html 	        .= "\t\t<TD ".$style_align_middle." class='".$classe_fond."' id='".$classe_fond.'_'.$j.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>";
							$html 	        .= '$'.$element['CHAMP_PERE']."_".$j.""; 
												$html 	        .= "</TD>\n";
												$i_TD++;
												( ($aff_total_vertic == true)  && ($j == 0) ) ? ( $last_tr_tot .= '<td></td>' ) : ( $last_tr_tot .= '' ) ;
							break;
						case 'hidden_field':
							$html 	        .= "\t\t<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$j .'\' '."NAME=".$element['CHAMP_PERE']."_".$j." TYPE='hidden'";
							$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$j."\""; 
							$html       		.= ">";
							break;
						}// Fin switch
					} // fin else
				}// Fin de parcours du Dico pour affichage des zones de saisie
							$html .= "\t</TR>\n";
							
			}
					( $aff_total_vertic == true ) ? ( $last_tr_tot .= '</tr>' ) : ( $last_tr_tot .= '' ) ;
					if( trim($last_tr_tot) <> '' ){
						if (preg_match ("/#([^#]*)#/", $last_tr_tot, $elem)){
							
						}
						//$html		.= $last_tr_tot;//A revoir modif (mise en commentaire) benin 24 02 2015
					}
					$html		.= "</TABLE>";
	
			$html		.= "\n" . $this->get_html_buttons($langue);
			
			$html 	.= "</FORM>";
			$html	.= "</div>"."\n";
			if(trim($html_fonc_TotMat_in_Gril) <> ''){
				$html	.= "<script language='javascript' type='text/javascript'>"."\n";
				$html	.= $html_fonc_TotMat_in_Gril ;
				$html	.= "</script>"."\n";
			}
	
			//print '<BR>'.$element['FRAME'];
					if (trim($element['FRAME']) <> '') {
							file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
							echo $html;
					}else{
							if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
					}
				//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
			//echo $html; 
		}//Fin function generer_frame_grille_ligne_fix_lig

//---------------------------------------------------------------------
//---------------------------------------------------------------------
/**
		* METHODE :  generer_frame_grille_eff_1(id_theme, langue):
		* Effectue la g�n�ration des templates � l'image
		* de celui des EFFECTIFS (02 mesures )pr�sent� en un seul bloc
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
	function generer_frame_grille_eff_1($id_theme, $langue ,$id_systeme){
	$requete				="		SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = ".$id_theme.
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
										AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
										
				//echo"$requete";
				//exit;
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
									
				$affiche_eff = 0;
				$NB_TOTAL_COL = 0;
				$NB_LIGNE_ECRAN	= $this->dico[0]['NB_LIGNES_FRAME']; //Nombre de lignes � afficher
				$dico        = $this->tri_fils($this->dico);
				$affiche_vertic_mes=0;
				foreach($dico as $dc){
					if($affiche_vertic_mes==0){
						$affiche_vertic_mes=$dc['AFFICH_VERTIC_MES'];
					}
				}
				$affiche_sous_totaux=0;
				foreach($dico as $dc){
					if($affiche_sous_totaux==0){
						$affiche_sous_totaux=$dc['AFFICHE_SOUS_TOTAUX'];
					}
				}
				//echo "<pre>";
				//print_r($dico );
				//e?xit;

				$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
				$mess_alert			= addslashes($mess_alert);
				
				$html				  = "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html 	  	  .= "function Alert_Supp(checkbox){\n";
				$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
				$html					.= "if (eval(chaine_eval)){\n";
				$html 	  	  .= "alert ('$mess_alert');\n";
				$html				  .= "}\n";
				$html				  .= "}\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";
		$html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
		//$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
		//$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php' METHOD='POST' NAME='form1'>"."\n";
		$html 			.= "<TABLE class='table-questionnaire' border='1'>". '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>' . "\n";

		//////////////////////////////////////////////////////////////////
		//////////////////Affichage horizontal des mesures///////////////
		////////////////////////////////////////////////////////////////
		if(!$affiche_vertic_mes	){
			// Calcul du nombre de lignes de libell�s
			$nb_colspan_2 = 1;
			$nb_chp_eff = 0;
			$nb_colspan_1 = 1;
			$tab_champ_val_multi = array();
			$tab_groupe = array();
			$tab_elem_zone_ref =array();
			$tab_zone_ref = array();
			
			$tab_elem_grp 				= array();
				
			$nom_grp_deja_aff 			= array();
			$html_fonc_TotMatEff_1 		= '';
			$affiche_totaux_mat_in_Eff_1	= array();
			$html_fonc_TotMatFrml 	= '';
			$colspan_Total_2Dim			= array();
			$tab_nb_zone_mat_par_grp	= array();
			$aff_total_vertic			= array();
			$grd_total_vertic 			= 0 ;
			$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
					
			$tabindex = array();
				foreach($dico as $i_elem => $element){
					$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
					if ( trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'booleen'  or trim($element['TYPE_OBJET']) == 'systeme_liste_radio' ){
						 // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
						$nb_colspan_1       = 2;
					}  
						//echo"<br>".$element['TYPE_OBJET'] ."<br>";
					if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
						if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
							if( trim($element['NOM_GROUPE']) == '' ){
								$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
								$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
							}else{
								$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
								$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
							}
							$dico[$i_elem]['OBJET_MATRICIEL']	= true;
							$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
							if(!isset($tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']])){
								$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']] = 1 ;
							}else{
								$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']]++;
								
							}
							if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int') && ($element['AFFICHE_TOTAL_VERTIC']) ){
								$grd_total_vertic = 1 ;
							}
						}
					}
					elseif ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
						$tab_champ_val_multi[$element['NOM_GROUPE']][] = array('CHAMP_PERE' =>$element['CHAMP_PERE'],
																				'ATTRIB_OBJET' =>$element['ATTRIB_OBJET'],
																				'ID_ZONE' =>$element['ID_ZONE'],
																				'AFFICHE_TOTAL' =>$element['AFFICHE_TOTAL']
																				);
						if(!isset($tab_zone_ref[$element['NOM_GROUPE']])){
								//$id_zone_ref	=	$element['ID_ZONE_REF'] ;
								$tab_zone_ref[$element['NOM_GROUPE']] = $element['ID_ZONE_REF'];
								$requete				="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
										" AND		DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
								// Traitement Erreur Cas : GetAll / GetRow
								try {
										$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
										if(!is_array($element_eff)){                    
												throw new Exception('ERR_SQL');  
										} 
																			
								}
								catch(Exception $e){
										$erreur = new erreur_manager($e,$requete);
								}
								// Fin Traitement Erreur Cas : GetAll / GetRow

								$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
								$inc	=	-1;
								$tab_elem_zone_ref = array();
								foreach($result_nomenc as $element_result_nomenc){
										$inc++;
										//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];
										$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
										//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
								}
								$html.= "\t <script language='javascript' type='text/javascript'>\n";
								$html.= "\t <!--\n";
									$html.= "\t\t function Calcul_Total_Ligne_".$element['NOM_GROUPE']."(champ_op,ligne){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 $nb_code	=	-1;
									 foreach($tab_elem_zone_ref as $code){
											$nb_code++;
											$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = 	parseFloat(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
											//$html.= "\t\t\t alert(chaine_eval);\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										} 
										$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n";
								$html.= "\t -->\n";
							  $html.= "\t </script>\n";
						}
						$nb_colspan_2++;
						$nb_chp_eff++;	
				}
				elseif( ($element['TYPE_OBJET'] == 'text') and ( trim($element['NOM_GROUPE']) <> '' ) ){
						$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
						$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	    = $element['ATTRIB_OBJET'] ;
						$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 			= $element['ID_ZONE'] ;
				}
		}
				if($nb_colspan_2 > 3) $nb_colspan_2 = 2;
				// recherche de la valeur de colspan des champs type effectifs
				if(is_array($tab_champ_val_multi)){
					foreach($tab_champ_val_multi as $grp_type_eff => $_temp_tab){
						if(count($tab_champ_val_multi[$grp_type_eff]) > 1){
							$nb_colspan_2 = 2;
						}else{
							$nb_colspan_2 = 1;
						}
					}
				}
				$nb_cols_zone_mat = 1;
				if(count($tab_nb_zone_mat_par_grp)){
					eval ('$nb_cols_zone_mat = ' . implode(' * ', $tab_nb_zone_mat_par_grp) . ' ;') ;
				}else{
					//A revoir pour dynamisation
					$nb_cols_zone_mat = 2;
				}
				if($affiche_sous_totaux){
					$nb_cols_zone_mat = $nb_cols_zone_mat + 1;
				}
				$key_tab_groupe 		= array_keys($tab_groupe);
				$NB_CHAMPS_MULTI		= $nb_colspan_2;
				$NB_TOTAL_COL		 	= $nb_colspan_1 + $nb_colspan_2 * $nb_cols_zone_mat * $NB_LIGNE_ECRAN;
				if($grd_total_vertic){
					$NB_TOTAL_COL		+= $nb_cols_zone_mat + $grd_total_vertic ;
				}
				$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
				$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
				//echo'<pre>'; 
				//print_r($tab_groupe);
				// Affichage de la colonne contenant l'ic�ne ?????????Ade s�lection des �l�ments � supprimer
				$html 			.= "\t"."<TR>"."\n";
		$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='ligne-impaire'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
		for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
			$tabindex[$ligne] = ($ligne * 1000) +1 ;
			$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='ligne-impaire'>"."<INPUT tabindex='".$tabindex[$ligne]."' NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
			$html .="\t\t<TD ROWSPAN='100' style='width: 1px; padding: 1px;'></TD>\n";
		}
		
				
		$html 			.= "\t".'</TR>'."\n";
		$ii = 0;
		
			if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
				$mat_dim_colonne 		=	array();
				$mat_liste_mes 			= 	array();
				$tab_libelles_mesures 	= 	array();
				$tab_types_mesures 		= 	array();
				$attrib_obj_mesures 	= 	array();
				$elem_obj_mesures 		= 	array();
				$mat_dim_colonne		= 	array();
				$mat_codes_ligne		= 	array();
				$mat_codes_colonne		= 	array();
				
				$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=1 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					
				$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=0 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
			}

				foreach($dico as $element){
					$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
					if(!isset($classe_fond)) {
						$classe_fond = 'ligne-paire';
					} else {
						if($classe_fond == 'ligne-paire') {
							$classe_fond = 'ligne-impaire';
						} else {
							$classe_fond = 'ligne-paire';
						}
					}
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) &&  ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
						
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							
							$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
							if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
								$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
							}
							$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
							
							if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
								$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
								foreach($mat_nomenc_colonne as $mat_i => $mat_val){
									$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
								}
							}
							if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
								$mat_liste_mes[$grp_mat] 			= array();
								$tab_libelles_mesures[$grp_mat] 	= array();
								$tab_lib_mes_tot[$grp_mat]			= array();
								$tab_types_mesures[$grp_mat] 		= array();
								$attrib_obj_mesures[$grp_mat] 		= array();
								$elem_obj_mesures[$grp_mat] 		= array();
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
										$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
									}
									$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
									$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
									$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
									
									$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
									if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
										$affiche_totaux_mat_in_Eff_1[$grp_mat][$mat_i] 	= true;
										$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
										if( $mat_val['AFFICHE_TOTAL_VERTIC'] ){
											$aff_total_vertic[$grp_mat][$mat_i] 	= true;
										}
									}
								}
								$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
								//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
								//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' ;
								$html_js_tot = '' ;
								if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
									$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
									$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION'])."\" ;\n";
									$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' 	= '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
									$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
									
									foreach($affiche_totaux_mat_in_Eff_1[$grp_mat] as $i_mes => $tot_mes){
										$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array (';
									
									//if(count($mat_codes_ligne[$grp_mat])){
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html_js_tot	.= ',"'.$ligne.'"';
										}
									//}
									$html_js_tot	.= ');'."\n";
							
									$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
									
									if(count($mat_codes_colonne[$grp_mat])){
										foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
											$html_js_tot	.= ',"'.$code_col['code'].'"';
										}
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										
									$html_js_tot	.= "</script>"."\n";
									
									$html			.= $html_js_tot ;
									
								}
								
								$html		.=	"\t".'<TR>'."\n";		
								$html 		.=	"\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html		.=	"\t".'</TR>'."\n";
		
								$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
								$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
								$html     	.= $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue)."</b></TD>\n";
								//echo "<br>$requete<br>";
								//echo'<pre>';
								//print_r($tab_champ_val_multi);

								$colspan_cur_zone_mat 	= ($nb_colspan_2 * $nb_cols_zone_mat) / count($mat_liste_mes[$grp_mat]) ;
								$colspan_vertic_tot 	= $nb_cols_zone_mat / count($mat_liste_mes[$grp_mat]) ;	
								//$with_cur_zone_mat 		= floor(100  / count($mat_liste_mes[$grp_mat])) .  '%' ; 
								
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $mes){
										$html .= "\t\t<TD colspan='".($colspan_cur_zone_mat)."' class='".$classe_fond."' nowrap>";
										$html .= $tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
									}
									if($affiche_sous_totaux){
										$html .= "\t\t<TD class='".$classe_fond."'>";
										$html .=  "". $this->recherche_libelle_page('sous_total',$langue,'questionnaire.php')."</TD>\n";
									}
								}
									if(count($aff_total_vertic[$grp_mat])){	
										foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
											$html .= "\t\t<TD colspan='".($colspan_vertic_tot)."' class='".$classe_fond."'>";
											$html .=  $this->recherche_libelle_page('total',$langue,'questionnaire.php') . ' <b>' . $tab_libelles_mesures[$grp_mat][$i_mes]."</b></TD>\n";
										}
										if(count($aff_total_vertic[$grp_mat]) > 1){
											$html .= "\t\t<TD colspan='".($nb_cols_zone_mat +1)."' class='".$classe_fond."'>";
											//$html .=  $this->recherche_libelle_page('total',$langue,'questionnaire.php') . ' <b>' . implode(' + ', $tab_lib_mes_tot[$grp_mat])."</b></TD>\n";
											$html .=  "". $this->recherche_libelle_page('total',$langue,'questionnaire.php')."</TD>\n";
										}else{
											//$html .= "\t\t<TD colspan='".($colspan_vertic_tot)."' class='".$classe_fond."'>";
											$html .= "\t\t<TD colspan='".($nb_cols_zone_mat)."' class='".$classe_fond."'>";
											$html .=   "".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</TD>\n";
										}
									}else{
										//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
									}
								$html 			.= "\t".'</TR>'."\n";
								$html			.= "\t".'<TR>'."\n";		
								$html 			.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";		
								if(is_array($mat_codes_colonne[$grp_mat])){
									foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){	
	
										if(!isset($classe_fond)) {
											$classe_fond = 'ligne-paire';
										} else {
											if($classe_fond == 'ligne-paire') {
												$classe_fond = 'ligne-impaire';
											} else {
												$classe_fond = 'ligne-paire';
											}
										}
	
										$html			.= "\t".'<TR>'."\n";		
										$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
										//$affiche_total	=	false;
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											
											foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
											  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
												if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){	
													//$set_TOTAL_MatFrml_ALL 	=	' onchange="set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
													$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$ligne.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
												}
												$GLOBALS['cell_mat_index'] = $tabindex[$ligne]++;
												$html		.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."' nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
												
												$html		.= $this->get_cell_matrice($ligne , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
												
												$html		.= "</TD>\n";
											}
											if($affiche_sous_totaux){
												$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'>"."  
														<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$ligne."_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
											}
										}
											if(count($aff_total_vertic[$grp_mat])){	
												//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
												foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
													$html	.="\t\t<TD  colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
															<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE_".$elem_col['code']."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
												}
												if( count($aff_total_vertic[$grp_mat]) > 1){
													$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'>"."  
															<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$elem_col['code']."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
												}
											}else{
												//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
											}
										$html 			.= "\t".'</TR>'."\n";
									}
								}
								if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
									$html		.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
									$html		.= "\t".'</TR>'."\n";
									
									if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 && (!$affiche_sous_totaux) ){
										$rowspan_chp_tot = 'rowspan=\'2\'';
										$br_chp_tot = '<br>';
									}else{
										$rowspan_chp_tot = '';
										$br_chp_tot = '';
									}
									
									$html		.= "\t"."<TR  style='background-color:#CCCCCC; text-align: right; font-weight:bold;'>\n";	
										
									$html 		.="\t\t"."<TD align='center' COLSPAN='$nb_colspan_1' nowrap $rowspan_chp_tot style='background-color:#CCCCCC; text-align: right; font-weight:bold;'><b>$br_chp_tot".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
									
									$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
											if( $affiche_totaux_mat_in_Eff_1[$grp_mat][$i_mes] == true ){
												$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>"."  
																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																 NAME='TOT_".$element['ID_ZONE']."_".$nom_champ_mes."_LIGNE_".$ligne."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											}else{
												$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>&nbsp;</TD>\n";
											}
										}
										if($affiche_sous_totaux){
											$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>".
														"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
														NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$ligne."' ".$element['ATTRIB_OBJET']."></TD>\n";
										}
									}
										if(count($aff_total_vertic[$grp_mat])){	
											//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
											foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
												$html	.="\t\t<TD $rowspan_chp_tot colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
														$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$element['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											}
											if(count($aff_total_vertic[$grp_mat]) > 1){
												$html	.="\t\t<TD $rowspan_chp_tot  align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
														<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$element['ID_ZONE']."_ALL_MES'  ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											}
										}elseif(count($aff_total_vertic)){
											//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
										}
									$html 			.= "\t".'</TR>'."\n";
									
									
									if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){				
										$html 		    .= "\n\t<TR>\n";
										if(!$affiche_sous_totaux){
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html		.="\t\t<TD align='center' COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." bgcolor='#CCCCCC'>".
															"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
															NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$ligne."' ".$elem_obj_mesures[$grp_mat][$i_mes]['ATTRIB_OBJET']."></TD>\n";
											}
											$html 		    .="\n\t</TR>\n";
										}
									}
								}// fin if($affiche_total==true)
							}	
						}
					}elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='popup') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 			.= "\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       	.= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 		.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT  ID='".$element['CHAMP_PERE']."_$ligne'name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\" readonly> ";
											}
							else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\" readonly> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
							$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
											$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='saisie') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
									//////
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\"
																			onBlur=SAISIE_".$element['CHAMP_PERE']."('".$element['CHAMP_INTERFACE']."_".$ligne."','".$element['CHAMP_PERE']."_".$ligne."');> ";
																			
											}
										else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\"> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											//$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
							//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
											$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'liste_radio' or $element['TYPE_OBJET'] == 'systeme_liste_radio'){
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
								$html 						.= "\t".'<TR>'."\n";
								//echo 'param ' .$element['TABLE_FILLE'].$element['TYPE_OBJET'].'<br>';
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
								
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
							//Pour chaque libell� de la nomenclature
								if($pass > 0) $html.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
												$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
												$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
										}
										$html 					.= "\t".'</TR>'."\n";
										$pass ++;
									}
								}
						
						elseif ( ($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text') and ( trim($element['NOM_GROUPE']) == '' ) ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
										$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
										$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
										$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
										$html 	        .= "".$element['ATTRIB_OBJET']."></TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'label'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
									$html 			.= "</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
											$html .= '$'.$element['CHAMP_PERE']."_".$ligne;
										$html .= "</TD>\n";
										
									}
									$html 			.= "\t</TR>\n";
								//break;
						}
						elseif ( $element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$dynamic_content_object = false ;
									if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
										//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
											$dynamic_content_object = true ;
										//}
									}
									if($dynamic_content_object <> true){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									}
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
											$html	.= "<SELECT ".$element['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."'>\n";
											$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
											
											if($dynamic_content_object == true){
												$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$ligne.'-->'."\n"; 
											}else{
												foreach($result_nomenc as $element_result_nomenc){
													//Un valeur du combo pour chaque valeur de la nomenclature 
													$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
													$html 	.= $element_result_nomenc[LIBELLE]."</OPTION>\n"; 
												}
											}
											
											$html 		.= "\t\t</SELECT></TD>\n";
										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and ( trim($element['NOM_GROUPE']) <> '' ) ){
							
							$nom_groupe = $element['NOM_GROUPE'];
							if ( isset($element['ID_ZONE_REF']) and (!in_array($nom_groupe,$nom_groupe_val_multi_affiche)) ){ // cas de G et/ou F sans les redoublants
								$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
								$requete				="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
										" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
								// Traitement Erreur Cas : GetAll / GetRow
								try {
										$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
										if(!is_array($element_eff)){                    
												throw new Exception('ERR_SQL');  
										} 
																			
								}
								catch(Exception $e){
										$erreur = new erreur_manager($e,$requete);
								}
								// Fin Traitement Erreur Cas : GetAll / GetRow

								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
		
								$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
								$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
								$html     .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</b></TD>\n";
								//echo "<br>$requete<br>";
								//echo'<pre>';
								//print_r($tab_champ_val_multi);
								
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
												$html .= $this->recherche_libelle_zone($champ['ID_ZONE'],$langue)."</TD>\n";
										}
								}
								$html 			.= "\t".'</TR>'."\n";
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";			
								$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
								foreach($result_nomenc as $element_result_nomenc){		

									if(!isset($classe_fond)) {
										$classe_fond = 'ligne-paire';
									} else {
										if($classe_fond == 'ligne-paire') {
												$classe_fond = 'ligne-impaire';
										} else {
												$classe_fond = 'ligne-paire';
										}
									}

									$html			.= "\t".'<TR>'."\n";		
									$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
									$affiche_total	=	false;
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											$fonction_somme ='';
											if($champ['AFFICHE_TOTAL']){
													$affiche_total	=	true;
													$fonction_somme = " onBlur=Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."',"."'$ligne'); ";
											}
											$tabindex[$ligne]++;
											$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
											$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
											$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
											$html .= $champ['ATTRIB_OBJET']."$fonction_somme></TD>\n";
										}
									}
									$html 			.= "\t".'</TR>'."\n";
								}
								if($affiche_total==true){
									$html		.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
									$html		.= "\t".'</TR>'."\n";
									$html		.= "\t"."<TR  class='ligne-titre'>\n";		
									$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='ligne-titre'><b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											$tabindex[$ligne]++;
											if($champ['AFFICHE_TOTAL']){
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='ligne-titre' nowrap>";
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$champ['CHAMP_PERE']."_".$ligne."' TYPE='text' VALUE='' readonly style='font-weight:bold;'";
												$html .= $champ['ATTRIB_OBJET']."></TD>\n";
											}else{
												$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
											}
										}
									}
									$html 			.= "\t".'</TR>'."\n";
									$html.= "\t <script language='javascript' type='text/javascript'>\n";
									$html.= "\t <!--\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											if($champ['AFFICHE_TOTAL']){
												$html.= "\t Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."','$ligne');\n";
											}
										}
									}
									$html.= "\t -->\n";
									$html.= "\t </script>\n";
								}// fin if($affiche_total==true)
							}
							$affiche_eff = 1;
						}
					elseif ($element['TYPE_OBJET'] == 'text' and ( trim($element['NOM_GROUPE']) <> '' ) ){
						///
						$set_TOTAL_Chp = '';
						$aff_total	= false;
						if( ($element['AFFICHE_TOTAL'] || $element['AFFICHE_TOTAL_VERTIC']) && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int')){
							$aff_total	= true;
							$set_TOTAL_Chp 	= ' onchange="set_TOTAL_MatFrml(\'form1\', '.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
						}
						$aff_sous_total	= false;
						if( ($element['AFFICHE_SOUS_TOTAUX']) && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int')){
							$aff_sous_total	= true;
						}
						$nom_groupe = $element['NOM_GROUPE'];
						$html_js_tot = '' ;
						$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
						$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION'])."\" ;\n";
						$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' 	= '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
						//$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array ("'.$dico[$i_elem]['CHAMP_PERE'].'");'."\n";
						$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;
						$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
						for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
							$html_js_tot	.= ',"'.$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre].'"';
						}
						$html_js_tot	.= ');'."\n";
						
						$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
						for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
							$html_js_tot	.= ',"'.$ligne.'"';
						}
						$html_js_tot	.= ');'."\n";
						$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array ();'."\n";
						$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
						$html_js_tot	.= "</script>"."\n";
						$html			.= $html_js_tot ;
						
						///
						
						$jj = 0;
						//$nom_groupe = $element['NOM_GROUPE'];
						if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
								$nom_groupe_tot_affiche[]	=	$nom_groupe;
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
								
								$html			.= "\t".'<TR>'."\n";
								/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
								else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
								$jj++;*/
								$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>"; 
								$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
								$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;

								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
											$tabindex[$ligne]++;
											$html .= "\t\t<TD colspan='".($nb_colspan_2 * $nb_cols_zone_mat / $nb_elem_grp)."' class='".$classe_fond."' nowrap>";

											$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne .'\' '."NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."' TYPE='text' ";
											$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."\""; 
											$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]." $set_TOTAL_Chp></TD>\n";
										}
										
										if($aff_sous_total){	
											$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$ligne."' ".$element['ATTRIB_OBJET']."></TD>\n";
										}elseif($affiche_sous_totaux){
											$html	.="\t\t<TD>&nbsp;</TD>\n";
										}
										
								}
								if($aff_total){
									if($aff_sous_total)
									for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
										$html	.="\t\t<TD $rowspan_chp_tot colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
												$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
												 NAME='TOT_".$element['ID_ZONE']."_".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_LIGNE' ".$element['ATTRIB_OBJET']."></TD>\n";
									}
									if($nb_elem_grp > 1){
										$html	.="\t\t<TD $rowspan_chp_tot  align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
												<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
												 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE' ".$element['ATTRIB_OBJET']."></TD>\n";
									}
									$html_fonc_TotMatFrml 		.= ' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');'. "\n" ;
								}
								$html 			.= "\t".'</TR>'."\n";
						}
						
					}elseif($element['TYPE_OBJET'] == 'booleen' and ( trim($element['NOM_GROUPE']) == '' ) ){
							$result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
							$rowspan		 			= count($result_nomenc);
							$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
							$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
							$pass = 0;
							foreach($result_nomenc as $element_result_nomenc){		
								//Pour chaque libell� de la nomenclature
								if($pass > 0) $html.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									$tabindex[$ligne]++;
									$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
								}
								$html 					.= "\t".'</TR>'."\n";
								$pass ++;
							}
							//echo 'test';
							//exit;
						}elseif($element['TYPE_OBJET'] == 'checkbox' and ( trim($element['NOM_GROUPE']) == '' ) ){
							$html			.= "\t".'<TR>'."\n";		
							$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
							$html			.= "\t".'</TR>'."\n";

							$html 			.= "\t".'<TR>'."\n";
							$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
							$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
							for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
								$tabindex[$ligne]++;
								$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
								$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='checkbox' ";
								$html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne; 
								$html 	        .= $element['ATTRIB_OBJET']."></TD>\n";
							}
							$html 			.= "\t".'</TR>'."\n";
						}
				}// fin parcours des �l�ments de dico
				//// generation des lignes comme total ou nbre redoublants
				$html		.= "</TABLE>";
		
	////////////////////////////////////////////////////////////////////
	///////////////////Affichage verticale des mesures//////////////////
	///////////////////////////////////////////////////////////////////
	}else{
		// Calcul du nombre de lignes de libell�s
		$nb_colspan_2 = 1;
		$nb_chp_eff = 0;
		//$nb_colspan_1 = 1;
		$nb_colspan_1 = 2;
		$tab_champ_val_multi = array();
		$tab_groupe = array();
		$tab_elem_zone_ref =array();
		$tab_zone_ref = array();
		
		$tab_elem_grp 				= array();
			
		$nom_grp_deja_aff 			= array();
		$html_fonc_TotMatEff_1 		= '';
		$affiche_totaux_mat_in_Eff_1	= array();
		$colspan_Total_2Dim			= array();
		$tab_nb_zone_mat_par_grp	= array();
		$aff_total_vertic			= array();
		$grd_total_vertic 			= 0 ;
		$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
				
		$tabindex = array();
				foreach($dico as $i_elem => $element){
					$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
					if ( trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'booleen'  or trim($element['TYPE_OBJET']) == 'systeme_liste_radio' ){
						 // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
						$nb_colspan_1       = 2;
					}  
						//echo"<br>".$element['TYPE_OBJET'] ."<br>";
					if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
						if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
							if( trim($element['NOM_GROUPE']) == '' ){
								$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
								$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
							}else{
								$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
								$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
							}
							$dico[$i_elem]['OBJET_MATRICIEL']	= true;
							$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
							
							if(!isset($tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']])){
								$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']] = 1 ;
							}else{
								$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']]++;
								
							}
							if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int') && ($element['AFFICHE_TOTAL_VERTIC']) ){
								$grd_total_vertic = 1 ;
							}
						}
					}
					elseif ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
						$tab_champ_val_multi[$element['NOM_GROUPE']][] = array('CHAMP_PERE' =>$element['CHAMP_PERE'],
																				'ATTRIB_OBJET' =>$element['ATTRIB_OBJET'],
																				'ID_ZONE' =>$element['ID_ZONE'],
																				'AFFICHE_TOTAL' =>$element['AFFICHE_TOTAL']
																				);
						if(!isset($tab_zone_ref[$element['NOM_GROUPE']])){
								//$id_zone_ref	=	$element['ID_ZONE_REF'] ;
								$tab_zone_ref[$element['NOM_GROUPE']] = $element['ID_ZONE_REF'];
								$requete				="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
										" AND		DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
								// Traitement Erreur Cas : GetAll / GetRow
								try {
										$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
										if(!is_array($element_eff)){                    
												throw new Exception('ERR_SQL');  
										} 
																			
								}
								catch(Exception $e){
										$erreur = new erreur_manager($e,$requete);
								}
								// Fin Traitement Erreur Cas : GetAll / GetRow

								$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
								$inc	=	-1;
								$tab_elem_zone_ref = array();
								foreach($result_nomenc as $element_result_nomenc){
										$inc++;
										//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];
										$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
										//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
								}
								$html.= "\t <script language='javascript' type='text/javascript'>\n";
								$html.= "\t <!--\n";
									$html.= "\t\t function Calcul_Total_Ligne_".$element['NOM_GROUPE']."(champ_op,ligne){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 $nb_code	=	-1;
									 foreach($tab_elem_zone_ref as $code){
											$nb_code++;
											$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = 	parseFloat(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
											//$html.= "\t\t\t alert(chaine_eval);\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										} 
										$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n";
								$html.= "\t -->\n";
							  $html.= "\t </script>\n";
						}
						$nb_colspan_2++;
						$nb_chp_eff++;	
				}
				elseif( ($element['TYPE_OBJET'] == 'text') and ( trim($element['NOM_GROUPE']) <> '' ) ){
						$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
						$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	    = $element['ATTRIB_OBJET'] ;
						$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 			= $element['ID_ZONE'] ;
				}
		}
				if($nb_colspan_2 > 3) $nb_colspan_2 = 2;
				// recherche de la valeur de colspan des champs type effectifs
				if(is_array($tab_champ_val_multi)){
					foreach($tab_champ_val_multi as $grp_type_eff => $_temp_tab){
						if(count($tab_champ_val_multi[$grp_type_eff]) > 1){
							$nb_colspan_2 = 2;
						}else{
							$nb_colspan_2 = 1;
						}
					}
				}
				$nb_cols_zone_mat = 1;
				//if(count($tab_nb_zone_mat_par_grp)){
				//	eval ('$nb_cols_zone_mat = ' . implode(' * ', $tab_nb_zone_mat_par_grp) . ' ;') ;
				//}

				$key_tab_groupe 		= array_keys($tab_groupe);
				$NB_CHAMPS_MULTI		= $nb_colspan_2;
				$NB_TOTAL_COL		 	= $nb_colspan_1 + $nb_colspan_2 * $nb_cols_zone_mat * $NB_LIGNE_ECRAN;
				if($grd_total_vertic){
					$NB_TOTAL_COL		+= $nb_cols_zone_mat + $grd_total_vertic ;
				}
				$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
				$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
				//echo'<pre>'; 
				//print_r($tab_groupe);
				// Affichage de la colonne contenant l'ic�ne ?????????Ade s�lection des �l�ments � supprimer
				$html 			.= "\t"."<TR>"."\n";
		$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='ligne-impaire'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
		for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
			$tabindex[$ligne] = ($ligne * 1000) +1 ;
			$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='ligne-impaire'>"."<INPUT tabindex='".$tabindex[$ligne]."' NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
			$html .="\t\t<TD ROWSPAN='100' style='width: 1px; padding: 1px;'></TD>\n";
		}
		
				
		$html 			.= "\t".'</TR>'."\n";
		$ii = 0;
		
			if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
				$mat_dim_ligne 		=	array();
				$mat_liste_mes 			= 	array();
				$tab_libelles_mesures 	= 	array();
				$tab_types_mesures 		= 	array();
				$attrib_obj_mesures 	= 	array();
				$elem_obj_mesures 		= 	array();
				$mat_dim_colonne		= 	array();
				$mat_codes_ligne		= 	array();
				$mat_codes_colonne		= 	array();
				
				$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=1 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
					
				$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
									WHERE CODE_NOMENCLATURE=0 
									AND NOM_TABLE='DICO_BOOLEEN'
									AND CODE_LANGUE='".$langue."';";
					// Traitement Erreur Cas : GetAll / GetRow
					try {
							$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
							if(!is_array($aresult)){                    
									throw new Exception('ERR_SQL');  
							} 
							$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
					}
					catch(Exception $e){
							$erreur = new erreur_manager($e,$requete);
					}
			}

				foreach($dico as $element){
					$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
					if(!isset($classe_fond)) {
						$classe_fond = 'ligne-paire';
					} else {
						if($classe_fond == 'ligne-paire') {
							$classe_fond = 'ligne-impaire';
						} else {
							$classe_fond = 'ligne-paire';
						}
					}
					if( (isset($element['OBJET_MATRICIEL']) && ($element['OBJET_MATRICIEL'] == true)) &&  ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
						
						if(!in_array($element['NOM_GROUPE'], $nom_grp_deja_aff)){
							$nom_grp_deja_aff[]	=	$element['NOM_GROUPE'];
							$grp_mat = $element['NOM_GROUPE'];
							
							$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $element['ID_TABLE_MERE_THEME'], $dico);
							if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
								$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $element['ID_TABLE_MERE_THEME'], $dico);
							}
							$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
							
							if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
								$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
								foreach($mat_nomenc_colonne as $mat_i => $mat_val){
									$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
								}
							}
							if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
								$mat_liste_mes[$grp_mat] 			= array();
								$tab_libelles_mesures[$grp_mat] 	= array();
								$tab_lib_mes_tot[$grp_mat]			= array();
								$tab_types_mesures[$grp_mat] 		= array();
								$attrib_obj_mesures[$grp_mat] 		= array();
								$elem_obj_mesures[$grp_mat] 		= array();
								
								foreach($tab_elem_grp[$element['NOM_GROUPE']] as $mat_i => $mat_val){
									if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
										$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
									}
									$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
									$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
									$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
									
									$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
									if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
										$affiche_totaux_mat_in_Eff_1[$grp_mat][$mat_i] 	= true;
										$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
										if( $mat_val['AFFICHE_TOTAL_VERTIC'] ){
											$aff_total_vertic[$grp_mat][$mat_i] 	= true;
										}
									}
								}
								$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
								//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
								//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$element['ID_ZONE'].', 0, tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' ;
								$html_js_tot = '' ;
								if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
									$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
									$html_js_tot	.= 'var expression_'.$element['ID_ZONE'].' 	= "'.trim($element['EXPRESSION'])."\" ;\n";
									$html_js_tot	.= 'var affiche_sous_totaux_'.$element['ID_ZONE'].' 	= '.$element['AFFICHE_SOUS_TOTAUX'].';'."\n";
									$html_js_tot	.= 'var tab_mes_'.$element['ID_ZONE'].' 	= new Array (';
									
									foreach($affiche_totaux_mat_in_Eff_1[$grp_mat] as $i_mes => $tot_mes){
										$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	.= 'var tab_ligne_'.$element['ID_ZONE'].'	= new Array (';
									
									//if(count($mat_codes_ligne[$grp_mat])){
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html_js_tot	.= ',"'.$ligne.'"';
										}
									//}
									$html_js_tot	.= ');'."\n";
							
									$html_js_tot	.= 'var tab_col_'.$element['ID_ZONE'].'	= new Array (';
									
									if(count($mat_codes_colonne[$grp_mat])){
										foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
											$html_js_tot	.= ',"'.$code_col['code'].'"';
										}
									}
									$html_js_tot	.= ');'."\n";
									
									$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										
									$html_js_tot	.= "</script>"."\n";
									
									$html			.= $html_js_tot ;
									
								}
								
								$html		.=	"\t".'<TR>'."\n";		
								$html 		.=	"\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html		.=	"\t".'</TR>'."\n";
		
								$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
								$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
								$html     	.= $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue)."</b></TD>\n";
								//echo "<br>$requete<br>";
								//echo'<pre>';
								//print_r($tab_champ_val_multi);

								$colspan_cur_zone_mat 	= ($nb_colspan_2 * $nb_cols_zone_mat) / count($mat_liste_mes[$grp_mat]) ;
								$colspan_vertic_tot 	= $nb_cols_zone_mat / count($mat_liste_mes[$grp_mat]) ;	
								//$with_cur_zone_mat 		= floor(100  / count($mat_liste_mes[$grp_mat])) .  '%' ; 
								
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
								}
								
								if(count($aff_total_vertic[$grp_mat])){	
									if(count($aff_total_vertic[$grp_mat]) > 1){
										$html .= "\t\t<TD COLSPAN='2' class='".$classe_fond."'>";
										$html .=  "".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</TD>\n";
									}else{
										$html .= "\t\t<TD  class='".$classe_fond."'>";
										$html .=  "".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</TD>\n";
									}
								}else{
									//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
								}
								$html 			.= "\t".'</TR>'."\n";
								$html			.= "\t".'<TR>'."\n";		
								$html 			.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";			
								if(is_array($mat_codes_colonne[$grp_mat])){
									foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){	
	
										if(!isset($classe_fond)) {
											$classe_fond = 'ligne-paire';
										} else {
											if($classe_fond == 'ligne-paire') {
												$classe_fond = 'ligne-impaire';
											} else {
												$classe_fond = 'ligne-paire';
											}
										}
	
										$html			.= "\t".'<TR>'."\n";		
										//$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
										if(!$affiche_sous_totaux){
											$rowspan = count($mat_liste_mes[$grp_mat]);
										}else{
											$rowspan = count($mat_liste_mes[$grp_mat]) + 1;
										}
										$html 			.="\t\t"."<TD style='vertical-align:middle' ROWSPAN='$rowspan' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
										//$affiche_total	=	false;
										$k=1;
										foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
											$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
											$html .= $tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												
												//foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
												  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
													if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){	
														//$set_TOTAL_MatFrml_ALL 	=	' onchange="set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');" ';
														$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$element['ID_ZONE'].', \'\', \''.$nom_champ_mes.'\', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].', '.$ligne.', '.$elem_col['code'].', tab_mes_'.$element['ID_ZONE'].');" ';
													}
													$GLOBALS['cell_mat_index'] = $tabindex[$ligne]++;
													$html		.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."' nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
													
													$html		.= $this->get_cell_matrice($ligne , $elem_col['code'], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
													
													$html		.= "</TD>\n";
												//}
											}
											if(count($aff_total_vertic[$grp_mat])){	
												//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
												//foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
													$html	.="\t\t<TD  colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
															<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$element['ID_ZONE']."_".$nom_champ_mes."_COLONNE_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
												//}
												if( count($aff_total_vertic[$grp_mat]) > 1){
													if(($k==1) && (!$affiche_sous_totaux))
														$html	.="\t\t<TD style='vertical-align:middle' ROWSPAN='$rowspan'  align='center' bgcolor='#CCCCCC'>"."  
																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
													$k++;
												}
											}else{
												//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
											}
											$html 			.= "\t".'</TR>'."\n";
										}
										if( $affiche_sous_totaux ){
											$html 		    .= "\n\t<TR>\n";
											$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
											$html .= $this->recherche_libelle_page('sous_total',$langue,'questionnaire.php')."</TD>\n";			
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>".
															"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
															NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$ligne."_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
											}
											$html	.="\t\t<TD style='vertical-align:middle' align='center' bgcolor='#CCCCCC'>"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$element['ID_ZONE']."_ALL_MES_COLONNE_".$elem_col['code']."' ".$element['ATTRIB_OBJET']."></TD>\n";
											$html 		    .="\n\t</TR>\n";
										}
									}
								}
								
								if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
									$html		.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
									$html		.= "\t".'</TR>'."\n";
									
									if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){
										//$rowspan_chp_tot = 'rowspan=\'2\'';
										$rowspan_chp_tot = 'rowspan=\''.($rowspan + 1).'\'';
										$br_chp_tot = '';
									}else{
										//$rowspan_chp_tot = '';
										$rowspan_chp_tot = 'rowspan=\''.$rowspan.'\'';
										$br_chp_tot = '';
									}
									
									$html		.= "\t"."<TR  style='background-color:#CCCCCC; text-align: right; font-weight:bold;'>\n";	
										
									$html 		.="\t\t"."<TD  style='vertical-align:middle'  align='center' COLSPAN='$nb_colspan_1' nowrap $rowspan_chp_tot style='background-color:#CCCCCC; text-align: right; font-weight:bold;'><b>$br_chp_tot".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
									
									$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', \'\', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
									$k=1;
									foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											//foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
												if( $affiche_totaux_mat_in_Eff_1[$grp_mat][$i_mes] == true ){
													$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>"."  
																	<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																	 NAME='TOT_".$element['ID_ZONE']."_".$nom_champ_mes."_LIGNE_".$ligne."' ".$element['ATTRIB_OBJET']."></TD>\n";
												}else{
													$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>&nbsp;</TD>\n";
												}
											//}
										}
									
										if(count($aff_total_vertic[$grp_mat])){	
											//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$element['ID_ZONE'].', '.$ligne.', tab_mes_'.$element['ID_ZONE'].', tab_ligne_'.$element['ID_ZONE'].', tab_col_'.$element['ID_ZONE'].');' . "\n";
											if(count($aff_total_vertic[$grp_mat])>1){
												$html	.="\t\t<TD colspan='2'  align='center' bgcolor='#CCCCCC'>"."  
														$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$element['ID_ZONE']."_".$nom_champ_mes."_COLONNE' ".$element['ATTRIB_OBJET']."></TD>\n";
											}else{
												$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."  
														$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$element['ID_ZONE']."_".$nom_champ_mes."_COLONNE' ".$element['ATTRIB_OBJET']."></TD>\n";
											
											}
											
											//if(count($aff_total_vertic[$grp_mat]) > 1){
//													if($k==1)
//														$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
//																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
//																 NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$element['ATTRIB_OBJET']."></TD>\n";
//													$k++;
//												}
										}elseif(count($aff_total_vertic)){
											//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
										}
										$html 			.= "\t".'</TR>'."\n";
									}
									
									if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){				
										$html 		    .= "\n\t<TR>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html		.="\t\t<TD align='center' COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." bgcolor='#CCCCCC'>".
														"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
														NAME='TOT_".$element['ID_ZONE']."_ALL_MES_LIGNE_".$ligne."' ".$element['ATTRIB_OBJET']."></TD>\n";
										}
										$html	.="\t\t<TD colspan='2' align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$element['ID_ZONE']."_ALL_MES' ".$element['ATTRIB_OBJET']."></TD>\n";
										
										$html 		    .="\n\t</TR>\n";
									}
								}// fin if($affiche_total==true)
							}	
						}
					}elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='popup') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 			.= "\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       	.= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 		.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT  ID='".$element['CHAMP_PERE']."_$ligne'name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\" readonly> ";
											}
							else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\" readonly> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
							$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
											$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='saisie') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
									//////
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\"
																			onBlur=SAISIE_".$element['CHAMP_PERE']."('".$element['CHAMP_INTERFACE']."_".$ligne."','".$element['CHAMP_PERE']."_".$ligne."');> ";
																			
											}
										else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\"> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											//$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
							//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
											$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'liste_radio' or $element['TYPE_OBJET'] == 'systeme_liste_radio'){

								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
								$html 						.= "\t".'<TR>'."\n";
								//echo 'param ' .$element['TABLE_FILLE'].$element['TYPE_OBJET'].'<br>';
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
								
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
							//Pour chaque libell� de la nomenclature
								if($pass > 0) $html.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
												$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
												$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
										}
										$html 					.= "\t".'</TR>'."\n";
										$pass ++;
									}
								}
						
						elseif ( ($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text') and ( trim($element['NOM_GROUPE']) == '' ) ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
										$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
										$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
										$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
										$html 	        .= "".$element['ATTRIB_OBJET']."></TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'label'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
									$html 			.= "</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
											$html .= '$'.$element['CHAMP_PERE']."_".$ligne;
										$html .= "</TD>\n";
										
									}
									$html 			.= "\t</TR>\n";
								//break;
						}
						elseif ( $element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$dynamic_content_object = false ;
									if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
										//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
											$dynamic_content_object = true ;
										//}
									}
									if($dynamic_content_object <> true){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']); 
									}
									
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
											$html	.= "<SELECT ".$element['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."'>\n";
											$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
											
											if($dynamic_content_object == true){
												$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$ligne.'-->'."\n"; 
											}else{
												foreach($result_nomenc as $element_result_nomenc){
													//Un valeur du combo pour chaque valeur de la nomenclature 
													$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
													$html 	.= $element_result_nomenc[LIBELLE]."</OPTION>\n"; 
												}
											}
											
											$html 		.= "\t\t</SELECT></TD>\n";
										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and ( trim($element['NOM_GROUPE']) <> '' ) ){
							
							$nom_groupe = $element['NOM_GROUPE'];
							if ( isset($element['ID_ZONE_REF']) and (!in_array($nom_groupe,$nom_groupe_val_multi_affiche)) ){ // cas de G et/ou F sans les redoublants
								$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
								$requete				="
										SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
										FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
										WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
										AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
										" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
										AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
										AND			DICO_ZONE.ID_THEME = ".$id_theme.
										" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
										" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
											
								// Traitement Erreur Cas : GetAll / GetRow
								try {
										$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
										if(!is_array($element_eff)){                    
												throw new Exception('ERR_SQL');  
										} 
																			
								}
								catch(Exception $e){
										$erreur = new erreur_manager($e,$requete);
								}
								// Fin Traitement Erreur Cas : GetAll / GetRow

								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
		
								$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
								$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
								$html     .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</b></TD>\n";
								//echo "<br>$requete<br>";
								//echo'<pre>';
								//print_r($tab_champ_val_multi);
								
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
												$html .= $this->recherche_libelle_zone($champ['ID_ZONE'],$langue)."</TD>\n";
										}
								}
								$html 			.= "\t".'</TR>'."\n";
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";			
								$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
								foreach($result_nomenc as $element_result_nomenc){		

									if(!isset($classe_fond)) {
										$classe_fond = 'ligne-paire';
									} else {
										if($classe_fond == 'ligne-paire') {
												$classe_fond = 'ligne-impaire';
										} else {
												$classe_fond = 'ligne-paire';
										}
									}

									$html			.= "\t".'<TR>'."\n";		
									$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
									$affiche_total	=	false;
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											$fonction_somme ='';
											if($champ['AFFICHE_TOTAL']){
													$affiche_total	=	true;
													$fonction_somme = " onBlur=Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."',"."'$ligne'); ";
											}
											$tabindex[$ligne]++;
											$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
											$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
											$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
											$html .= $champ['ATTRIB_OBJET']."$fonction_somme></TD>\n";
										}
									}
									$html 			.= "\t".'</TR>'."\n";
								}
								if($affiche_total==true){
									$html		.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
									$html		.= "\t".'</TR>'."\n";
									$html		.= "\t"."<TR  class='ligne-titre'>\n";		
									$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='ligne-titre'><b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											$tabindex[$ligne]++;
											if($champ['AFFICHE_TOTAL']){
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='ligne-titre' nowrap>";
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$champ['CHAMP_PERE']."_".$ligne."' TYPE='text' VALUE='' readonly style='font-weight:bold;'";
												$html .= $champ['ATTRIB_OBJET']."></TD>\n";
											}else{
												$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
											}
										}
									}
									$html 			.= "\t".'</TR>'."\n";
									$html.= "\t <script language='javascript' type='text/javascript'>\n";
									$html.= "\t <!--\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($tab_champ_val_multi[$nom_groupe] as $champ){
											if($champ['AFFICHE_TOTAL']){
												$html.= "\t Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."','$ligne');\n";
											}
										}
									}
									$html.= "\t -->\n";
									$html.= "\t </script>\n";
								}// fin if($affiche_total==true)
							}
							$affiche_eff = 1;
						}
					elseif ($element['TYPE_OBJET'] == 'text' and ( trim($element['NOM_GROUPE']) <> '' ) ){
						$jj = 0;
						$nom_groupe = $element['NOM_GROUPE'];
						if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
								$nom_groupe_tot_affiche[]	=	$nom_groupe;
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
								
								$html			.= "\t".'<TR>'."\n";
								/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
								else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
								$jj++;*/
								$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;
								$html .=  "\t\t"."<TD style='vertical-align:middle' ROWSPAN='$nb_elem_grp' nowrap class='".$classe_fond."'>"; 
								$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
																
								
								for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
									$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
									if(isset($tab_libelles_mesures[$grp_mat])) $html .= $tab_libelles_mesures[$grp_mat][$ordre];
									$html .= "</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											//for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
												$tabindex[$ligne]++;
												$html .= "\t\t<TD colspan='".($nb_colspan_2 * $nb_cols_zone_mat / $nb_elem_grp)."' class='".$classe_fond."' nowrap>";
	
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne .'\' '."NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."' TYPE='text' ";
												$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."\""; 
												$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]."></TD>\n";
											//}
									}
									$html 			.= "\t".'</TR>'."\n";
								}
								
						}
					}elseif($element['TYPE_OBJET'] == 'booleen' and ( trim($element['NOM_GROUPE']) == '' ) ){
							$result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
							$rowspan		 			= count($result_nomenc);
							$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
							$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
							$pass = 0;
							foreach($result_nomenc as $element_result_nomenc){		
								//Pour chaque libell� de la nomenclature
								if($pass > 0) $html.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc[LIBELLE]."</TD>\n";
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									$tabindex[$ligne]++;
									$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
									$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
									$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
								}
								$html 					.= "\t".'</TR>'."\n";
								$pass ++;
							}
							//echo 'test';
							//exit;
						}elseif($element['TYPE_OBJET'] == 'checkbox' and ( trim($element['NOM_GROUPE']) == '' ) ){
							$html			.= "\t".'<TR>'."\n";		
							$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
							$html			.= "\t".'</TR>'."\n";

							$html 			.= "\t".'<TR>'."\n";
							$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
							$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
							for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
								$tabindex[$ligne]++;
								$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
								$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='checkbox' ";
								$html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne; 
								$html 	        .= $element['ATTRIB_OBJET']."></TD>\n";
							}
							$html 			.= "\t".'</TR>'."\n";
						}
				}// fin parcours des �l�ments de dico
				//// generation des lignes comme total ou nbre redoublants
				$html		.= "</TABLE>";

	}
	$html		.= "\n" . $this->get_html_buttons($langue);
		
		$html 	.= "</FORM>";
		$html	.= "</div>"."\n";

		$html		  .= "<script language='javascript' type='text/javascript'>\n";
		$html 	  	  .= "<!--\n";
		$html         .= "document.form1.elements[0].focus();\n";
		if(trim($html_fonc_TotMatEff_1) <> ''){
			$html	.= $html_fonc_TotMatEff_1 ;
		}
		if($html_fonc_TotMatFrml<>''){
			$html	.= $html_fonc_TotMatFrml;
		}
		$html 	  	  .= "//-->\n";
		$html 	  	  .= "</script>\n";

			//print '<BR>'.$element['FRAME'];
		if (trim($element['FRAME']) <> '') {
			file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
			echo $html;
		}else{
			if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
		}
		//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
		//echo $html; 	
	}//Fin function generer_frame_grille_ligne


//----------------------------------------------------------------------
//----------------------------------------------------------------------
 		/**
		* METHODE :  generer_frame_grille_eff_1_fix_col(id_theme, langue):
		* Effectue la g�n�ration des templates � l'image avec des libelles affiches comme dimension colonne
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
	function generer_frame_grille_eff_1_fix_col($id_theme, $langue ,$id_systeme){
	$requete				="		SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = ".$id_theme.
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
										AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
										
				//echo"$requete";
				//exit;
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow
				
				//Recuperation du type de theme
				$req_type_theme = "SELECT ID_TYPE_THEME FROM DICO_THEME WHERE ID=".$id_theme;
				$this->type_theme	= $GLOBALS['conn_dico']->GetOne($req_type_theme);
				//Fin Recuperation du type de theme	 
									
				$affiche_eff = 0;
				$NB_TOTAL_COL = 0;
				$NB_LIGNE_ECRAN	= $this->dico[0]['NB_LIGNES_FRAME']; //Nombre de lignes � afficher
				$dico        = $this->tri_fils($this->dico);
				$affiche_vertic_mes=0;
				foreach($dico as $dc){
					if($affiche_vertic_mes==0){
						$affiche_vertic_mes=$dc['AFFICH_VERTIC_MES'];
					}
				}
				$affiche_sous_totaux=0;
				foreach($dico as $dc){
					if($affiche_sous_totaux==0){
						$affiche_sous_totaux=$dc['AFFICHE_SOUS_TOTAUX'];
					}
				}
				//echo "<pre>";
				//print_r($dico );
				//e?xit;

				$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
				$mess_alert			= addslashes($mess_alert);
				
				$html				  = "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html 	  	  .= "function Alert_Supp(checkbox){\n";
				$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
				$html					.= "if (eval(chaine_eval)){\n";
				$html 	  	  .= "alert ('$mess_alert');\n";
				$html				  .= "}\n";
				$html				  .= "}\n";
				$html 	  	  .= "//-->\n";
				//$html 	  	  .= "var expression='';\n";
				$html 	  	  .= "</script>\n";
		$html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
		//$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
		//$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php' METHOD='POST' NAME='form1'>"."\n";
		$html 			.= "<TABLE class='table-questionnaire' border='1'>". '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>' . "\n";
		
		//////////////////////////////////////////////////////////////////
		//////////////////Affichage horizontal des mesures///////////////
		////////////////////////////////////////////////////////////////
		if(!$affiche_vertic_mes	){
			// Calcul du nombre de lignes de libell�s
			$nb_colspan_2 = 1;
			$nb_chp_eff = 0;
			$nb_colspan_1 = 1;
			$tab_champ_val_multi = array();
			$tab_groupe = array();
			$tab_elem_zone_ref =array();
			$tab_zone_ref = array();
			
			$tab_elem_grp 				= array();
				
			$nom_grp_deja_aff 			= array();
			$html_fonc_TotMatEff_1 		= '';
			$affiche_totaux_mat_in_Eff_1	= array();
			$html_fonc_TotMatFrml 	= '';
			$colspan_Total_2Dim			= array();
			$tab_nb_zone_mat_par_grp	= array();
			$aff_total_vertic			= array();
			$grd_total_vertic 			= 0 ;
			$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
					
			$tabindex = array();
				foreach($dico as $i_elem => $element){
						$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
						if ( trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'booleen'  or trim($element['TYPE_OBJET']) == 'systeme_liste_radio' ){
							 // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
							$nb_colspan_1       = 2;
						}  
							//echo"<br>".$element['TYPE_OBJET'] ."<br>";
						if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
							if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
								if( trim($element['NOM_GROUPE']) == '' ){
									$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
									$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
								}else{
									$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
									$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
								}
								$dico[$i_elem]['OBJET_MATRICIEL']	= true;
								$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
								
								if(!isset($tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']])){
									$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']] = 1 ;
								}else{
									$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']]++;
									
								}
								if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int') && ($element['AFFICHE_TOTAL_VERTIC']) ){
									$grd_total_vertic = 1 ;
								}
							}
						}
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
							$tab_champ_val_multi[$element['NOM_GROUPE']][] = array('CHAMP_PERE' =>$element['CHAMP_PERE'],
																					'ATTRIB_OBJET' =>$element['ATTRIB_OBJET'],
																					'ID_ZONE' =>$element['ID_ZONE'],
																					'AFFICHE_TOTAL' =>$element['AFFICHE_TOTAL']
																					);
							if(!isset($tab_zone_ref[$element['NOM_GROUPE']])){
									//$id_zone_ref	=	$element['ID_ZONE_REF'] ;
									$tab_zone_ref[$element['NOM_GROUPE']] = $element['ID_ZONE_REF'];
									$requete				="
											SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
											FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
											WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
											AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
											" AND		DICO_ZONE.ID_THEME = DICO_THEME.ID				
											AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
											AND			DICO_ZONE.ID_THEME = ".$id_theme.
											" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
												
									// Traitement Erreur Cas : GetAll / GetRow
									try {
											$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
											if(!is_array($element_eff)){                    
													throw new Exception('ERR_SQL');  
											} 
																				
									}
									catch(Exception $e){
											$erreur = new erreur_manager($e,$requete);
									}
									// Fin Traitement Erreur Cas : GetAll / GetRow
	
									$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
									$inc	=	-1;
									$tab_elem_zone_ref = array();
									foreach($result_nomenc as $element_result_nomenc){
											$inc++;
											//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];
											$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
											//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
									}
									$html.= "\t <script language='javascript' type='text/javascript'>\n";
									$html.= "\t <!--\n";
										$html.= "\t\t function Calcul_Total_Ligne_".$element['NOM_GROUPE']."(champ_op,ligne){\n";
											$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
											$html.= "\t\t\t var chaine_eval;\n";
											$html.= "\t\t\t var total = 0;\n";
										 $nb_code	=	-1;
										 foreach($tab_elem_zone_ref as $code){
												$nb_code++;
												$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = 	parseFloat(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
												//$html.= "\t\t\t alert(chaine_eval);\n";
												$html.= "\t\t\t eval(chaine_eval);\n";
											} 
											$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
												$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
													$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
													$html.= "\t\t\t\t\t eval(chaine_eval);\n";
												$html.= "\t\t\t\t	}\n";
											$html.= "\t\t\t }\n";
											$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										$html.= "\t\t }\n";
									$html.= "\t -->\n";
								  $html.= "\t </script>\n";
							}
							$nb_colspan_2++;
							$nb_chp_eff++;	
						}
						elseif( ($element['TYPE_OBJET'] == 'text') and ( trim($element['NOM_GROUPE']) <> '' ) ){
								$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	    = $element['ATTRIB_OBJET'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 			= $element['ID_ZONE'] ;
						}
					}
					if($nb_colspan_2 > 3) $nb_colspan_2 = 2;
					// recherche de la valeur de colspan des champs type effectifs
					if(is_array($tab_champ_val_multi)){
						foreach($tab_champ_val_multi as $grp_type_eff => $_temp_tab){
							if(count($tab_champ_val_multi[$grp_type_eff]) > 1){
								$nb_colspan_2 = 2;
							}else{
								$nb_colspan_2 = 1;
							}
						}
					}
					$nb_cols_zone_mat = 1;
					if(count($tab_nb_zone_mat_par_grp)){
						eval ('$nb_cols_zone_mat = ' . implode(' * ', $tab_nb_zone_mat_par_grp) . ' ;') ;
					}else{
						//A revoir pour dynamisation
						$nb_cols_zone_mat = 2;
					}
					if($affiche_sous_totaux){
						$nb_cols_zone_mat = $nb_cols_zone_mat + 1;
					}
					$key_tab_groupe 		= array_keys($tab_groupe);
					$NB_CHAMPS_MULTI		= $nb_colspan_2;
					$NB_TOTAL_COL		 	= $nb_colspan_1 + $nb_colspan_2 * $nb_cols_zone_mat * $NB_LIGNE_ECRAN;
					if($grd_total_vertic){
						$NB_TOTAL_COL		+= $nb_cols_zone_mat + $grd_total_vertic ;
					}
					$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
					$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
					//echo'<pre>'; 
					//print_r($tab_groupe);
					// Affichage de la colonne contenant l'ic�ne ?????????Ade s�lection des �l�ments � supprimer
					/*$html 			.= "\t"."<TR>"."\n";
			$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='ligne-impaire'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
			for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
				$tabindex[$ligne] = ($ligne * 1000) +1 ;
				$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='ligne-impaire'>"."<INPUT tabindex='".$tabindex[$ligne]."' NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
				$html .="\t\t<TD ROWSPAN='100' style='width: 1px; padding: 1px;'></TD>\n";
			}
			
					
			$html 			.= "\t".'</TR>'."\n";*/
			for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
				$tabindex[$ligne] = ($ligne * 1000) +1 ;
			}
			$ii = 0;
			
				if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
					$mat_dim_colonne 		=	array();
					$mat_liste_mes 			= 	array();
					$tab_libelles_mesures 	= 	array();
					$tab_types_mesures 		= 	array();
					$attrib_obj_mesures 	= 	array();
					$elem_obj_mesures 		= 	array();
					$mat_dim_colonne		= 	array();
					$mat_codes_ligne		= 	array();
					$mat_codes_colonne		= 	array();
					
					$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
										WHERE CODE_NOMENCLATURE=1 
										AND NOM_TABLE='DICO_BOOLEEN'
										AND CODE_LANGUE='".$langue."';";
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
								if(!is_array($aresult)){                    
										throw new Exception('ERR_SQL');  
								} 
								$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$requete);
						}
						
					$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
										WHERE CODE_NOMENCLATURE=0 
										AND NOM_TABLE='DICO_BOOLEEN'
										AND CODE_LANGUE='".$langue."';";
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
								if(!is_array($aresult)){                    
										throw new Exception('ERR_SQL');  
								} 
								$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$requete);
						}
				}
					
					foreach($dico as $i_elem => $element){
						
						$dico[$i_elem]['NOM_GROUPE'] = trim($dico[$i_elem]['NOM_GROUPE']);
						if(!isset($classe_fond)) {
							$classe_fond = 'ligne-paire';
						} else {
							if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
							} else {
								$classe_fond = 'ligne-paire';
							}
						}
						
						//Pour recuperer la var mat_nomenc_colonne mat_dim_col
						$mat_dim_col	=	$this->get_dims_zone_matricielle('dimension_colonne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
						if(is_array($mat_dim_col) && count($mat_dim_col)){
							$this->mat_dim_col = $mat_dim_col;
							$this->mat_nomenc_col	= $this->requete_nomenclature($this->mat_dim_col['TABLE_FILLE'], $this->mat_dim_col['CHAMP_FILS'], $this->mat_dim_col['CHAMP_PERE'], $langue ,$id_systeme, $this->mat_dim_col['SQL_REQ']) ;
						}
						//Fin Pour recuperer la var mat_nomenc_colonne
												
						if( (isset($dico[$i_elem]['OBJET_MATRICIEL']) && ($dico[$i_elem]['OBJET_MATRICIEL'] == true)) &&  ( $dico[$i_elem]['TYPE_OBJET'] <> 'dimension_ligne')  && ( $dico[$i_elem]['TYPE_OBJET'] <> 'dimension_colonne') ){
							
							if(!in_array($dico[$i_elem]['NOM_GROUPE'], $nom_grp_deja_aff)){
								$nom_grp_deja_aff[]	=	$dico[$i_elem]['NOM_GROUPE'];
								$grp_mat = $dico[$i_elem]['NOM_GROUPE'];
								
								$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
								if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
									$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
								}
								$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
								
								if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
									$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
									foreach($mat_nomenc_colonne as $mat_i => $mat_val){
										$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
									}
								}
								if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
									$mat_liste_mes[$grp_mat] 			= array();
									$tab_libelles_mesures[$grp_mat] 	= array();
									$tab_lib_mes_tot[$grp_mat]			= array();
									$tab_types_mesures[$grp_mat] 		= array();
									$attrib_obj_mesures[$grp_mat] 		= array();
									$elem_obj_mesures[$grp_mat] 		= array();
									
									foreach($tab_elem_grp[$dico[$i_elem]['NOM_GROUPE']] as $mat_i => $mat_val){
										if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
											$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
										}
										$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
										$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
										$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
										
										$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
										if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
											$affiche_totaux_mat_in_Eff_1[$grp_mat][$mat_i] 	= true;
											$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
											if( $mat_val['AFFICHE_TOTAL_VERTIC'] ){
												$aff_total_vertic[$grp_mat][$mat_i] 	= true;
											}
										}
									}
									$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
									//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
									//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' ;
									$html_js_tot = '' ;
									if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
										$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
										$html_js_tot	.= 'var expression_'.$dico[$i_elem]['ID_ZONE'].' 	= "'.trim($dico[$i_elem]['EXPRESSION'])."\" ;\n";
										$html_js_tot	.= 'var affiche_sous_totaux_'.$dico[$i_elem]['ID_ZONE'].' 	= '.$dico[$i_elem]['AFFICHE_SOUS_TOTAUX'].';'."\n";
										$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array (';
										
										foreach($affiche_totaux_mat_in_Eff_1[$grp_mat] as $i_mes => $tot_mes){
											$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
										}
										$html_js_tot	.= ');'."\n";
										
										$html_js_tot	.= 'var tab_ligne_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										
										if(count($mat_codes_colonne[$grp_mat])){
											foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
												$html_js_tot	.= ',"'.$code_col['code'].'"';
											}
										}
										$html_js_tot	.= ');'."\n";
								
										$html_js_tot	.= 'var tab_col_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										//if(count($mat_codes_ligne[$grp_mat])){
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html_js_tot	.= ',"'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'"';
											}
										//}
										$html_js_tot	.= ');'."\n";
										
										$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
											
										$html_js_tot	.= "</script>"."\n";
										
										$html			.= $html_js_tot ;
										
									}
									
									$html		.=	"\t".'<TR>'."\n";		
									$html 		.=	"\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html		.=	"\t".'</TR>'."\n";
			
									$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
									$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
									$html     	.= $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue)."</b></TD>\n";
									//echo "<br>$requete<br>";
									//echo'<pre>';
									//print_r($tab_champ_val_multi);
	
									$colspan_cur_zone_mat 	= ($nb_colspan_2 * $nb_cols_zone_mat) / count($mat_liste_mes[$grp_mat]) ;
									$colspan_vertic_tot 	= $nb_cols_zone_mat / count($mat_liste_mes[$grp_mat]) ;	
									//$with_cur_zone_mat 		= floor(100  / count($mat_liste_mes[$grp_mat])) .  '%' ; 
									
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										foreach($mat_liste_mes[$grp_mat] as $i_mes => $mes){
											$html .= "\t\t<TD colspan='".($colspan_cur_zone_mat)."' class='".$classe_fond."' nowrap>";
											$html .= $tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
										}
										if($affiche_sous_totaux){
											$html .= "\t\t<TD class='".$classe_fond."'>";
											$html .=  "". $this->recherche_libelle_page('sous_total',$langue,'questionnaire.php')."</TD>\n";
										}
									}
										if(count($aff_total_vertic[$grp_mat])){	
												
											if(count($aff_total_vertic[$grp_mat]) > 1){
												$html .= "\t\t<TD colspan='".($nb_cols_zone_mat +1)."' class='".$classe_fond."'>";
												//$html .=  $this->recherche_libelle_page('total',$langue,'questionnaire.php') . ' <b>' . implode(' + ', $tab_lib_mes_tot[$grp_mat])."</b></TD>\n";
												$html .=  "<b>". $this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
											}else{
												//$html .= "\t\t<TD colspan='".($colspan_vertic_tot)."' class='".$classe_fond."'>";
												$html .= "\t\t<TD colspan='".($nb_cols_zone_mat)."' class='".$classe_fond."'>";
												$html .=   "<b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
											}
											
										}else{
											//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
										}
									$html 			.= "\t".'</TR>'."\n";
									$html			.= "\t".'<TR>'."\n";		
									$html 			.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";			
									if(is_array($mat_codes_colonne[$grp_mat])){
										$cpt=0;
										foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){	
		
											if(!isset($classe_fond)) {
												$classe_fond = 'ligne-paire';
											} else {
												if($classe_fond == 'ligne-paire') {
													$classe_fond = 'ligne-impaire';
												} else {
													$classe_fond = 'ligne-paire';
												}
											}
		
											$html			.= "\t".'<TR>'."\n";		
											$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
											//$affiche_total	=	false;
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												
												foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
												  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
													if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){	
														//$set_TOTAL_MatFrml_ALL 	=	' onchange="set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
														$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$dico[$i_elem]['ID_ZONE'].', 0, \''.$nom_champ_mes.'\', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].', '.$elem_col['code'].', '.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].', tab_mes_'.$dico[$i_elem]['ID_ZONE'].');" ';
													}
													$GLOBALS['cell_mat_index'] = $tabindex[$ligne]++;
													$html		.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."' nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
													
													$html		.= $this->get_cell_matrice(0 , $elem_col['code'].'_'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
													
													$html		.= "</TD>\n";
												}
												if($affiche_sous_totaux){
													$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'>"."  
															<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_COLONNE_".$elem_col['code']."_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}
											}
												if(count($aff_total_vertic[$grp_mat])){	
													//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
													foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
														$html	.="\t\t<TD  colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_LIGNE_0_".$elem_col['code']."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
													}
													if( count($aff_total_vertic[$grp_mat]) > 1){
														$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'>"."  
																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_col['code']."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
													}
												}else{
													//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
												}
											$html 			.= "\t".'</TR>'."\n";
										}
									}
									
									if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
										$html		.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
										$html		.= "\t".'</TR>'."\n";
										
										if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 && (!$affiche_sous_totaux) ){
											$rowspan_chp_tot = 'rowspan=\'2\'';
											//$br_chp_tot = '<br>';
											$br_chp_tot = '';
										}else{
											$rowspan_chp_tot = '';
											$br_chp_tot = '';
										}
										
										$html		.= "\t"."<TR  style='background-color:#CCCCCC; text-align: right; font-weight:bold;'>\n";	
											
										$html 		.="\t\t"."<TD align='center' COLSPAN='$nb_colspan_1' nowrap $rowspan_chp_tot style='background-color:#CCCCCC; text-align: right; font-weight:bold;'><b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										
										$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
												if( $affiche_totaux_mat_in_Eff_1[$grp_mat][$i_mes] == true ){
													$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>"."  
																	<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																	 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$nom_champ_mes."_COLONNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}else{
													$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>&nbsp;</TD>\n";
												}
											}
											if($affiche_sous_totaux){
												$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>".
															"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
															NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_COLONNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											}
										}
											if(count($aff_total_vertic[$grp_mat])){	
												//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
												foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
													$html	.="\t\t<TD $rowspan_chp_tot colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
															$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$mat_liste_mes[$grp_mat][$i_mes]."_COLONNE_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}
												if(count($aff_total_vertic[$grp_mat]) > 1){
													$html	.="\t\t<TD $rowspan_chp_tot  align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
															<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}
											}elseif(count($aff_total_vertic)){
												//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
											}
										$html 			.= "\t".'</TR>'."\n";
										
										
										/*if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){				
											$html 		    .= "\n\t<TR>\n";
											if($affiche_sous_totaux){
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													$html		.="\t\t<TD align='center' COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." bgcolor='#CCCCCC'>".
																"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
																NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_LIGNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}
												$html 		    .="\n\t</TR>\n";
											}
										}*/
									}// fin if($affiche_total==true)
								}	
							}
						}elseif( ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio') and ($dico[$i_elem]['BOUTON_INTERFACE']=='popup') ){
										//echo"<br>ICI<br>";
										$html			.= "\t".'<TR>'."\n";		
										$html 			.= "\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       	.= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html 		.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
												if( trim($dico[$i_elem]['CHAMP_INTERFACE'])<>trim($dico[$i_elem]['CHAMP_PERE']) ){
														$html .= "<INPUT  ID='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='hidden' value='$".$dico[$i_elem]['CHAMP_PERE']."_$ligne' >"; 
														$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																				value=\"$".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne\" readonly> ";
												}
								else{
														$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																			 value=\"$".$dico[$i_elem]['CHAMP_PERE']."_$ligne\" readonly> ";
												}
												//eval("\$fonction_click	=	".$dico[$i_elem]['FONCTION_INTERFACE'].";");
												$fonction	=	str_replace("\$ligne","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);
												//$dico[$i_elem]['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);	
								$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
												$html .= "\t\t</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif( ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio') and ($dico[$i_elem]['BOUTON_INTERFACE']=='saisie') ){
										//echo"<br>ICI<br>";
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
										//////
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
												$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
												if( trim($dico[$i_elem]['CHAMP_INTERFACE'])<>trim($dico[$i_elem]['CHAMP_PERE']) ){
														$html .= "<INPUT ID='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='hidden' value='$".$dico[$i_elem]['CHAMP_PERE']."_$ligne' >"; 
														$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																				value=\"$".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne\"
																				onBlur=SAISIE_".$dico[$i_elem]['CHAMP_PERE']."('".$dico[$i_elem]['CHAMP_INTERFACE']."_".$ligne."','".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."');> ";
																				
												}
											else{
														$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																			 value=\"$".$dico[$i_elem]['CHAMP_PERE']."_$ligne\"> ";
												}
												//eval("\$fonction_click	=	".$dico[$i_elem]['FONCTION_INTERFACE'].";");
												//$fonction	=	str_replace("\$ligne","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);
												//$dico[$i_elem]['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);	
								//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
												$html .= "\t\t</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_liste_radio'){
	
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 						.= "\t".'<TR>'."\n";
									//echo 'param ' .$dico[$i_elem]['TABLE_FILLE'].$dico[$i_elem]['TYPE_OBJET'].'<br>';
									$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
									$rowspan		 			= count($result_nomenc);
									$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
									$html             .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
									
									$pass = 0;
									foreach($result_nomenc as $element_result_nomenc){		
								//Pour chaque libell� de la nomenclature
									if($pass > 0) $html.= "\t".'<TR>'."\n";		
											$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													$tabindex[$ligne]++;
													$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])] .'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='radio'";
													$html       .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])]; 
													$html       .= "".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											}
											$html 					.= "\t".'</TR>'."\n";
											$pass ++;
										}
									}
									/*
									$html		.= "\t\t\t<tr>\n";
									$html		.= "\t\t\t\t<td nowrap='nowrap'>".$element_result_nomenc['LIBELLE']."</td>\n";
									foreach($result_nomenc as $element_result_nomenc){
										$html		.= "\t\t\t\t<td>"."<INPUT".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '." NAME='".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."' TYPE='radio' 
														VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$code_dims."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."></td>\n"; 
										$html		.= "\t\t\t</tr>\n";
									}
									*/
							
							elseif ( ($dico[$i_elem]['TYPE_OBJET'] == 'text' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_text') and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
										
										$set_TOTAL_Chp = '';
										$aff_total	= false;
										if( ($dico[$i_elem]['AFFICHE_TOTAL'] || $dico[$i_elem]['AFFICHE_TOTAL_VERTIC']) && ($dico[$i_elem]['TYPE_OBJET'] == 'text') && ($dico[$i_elem]['TYPE_ZONE_BASE'] == 'int')){
											$aff_total	= true;
											$set_TOTAL_Chp 	= ' onchange="set_TOTAL_MatFrml(\'form1\', '.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
										}
									
										$html_js_tot = '' ;
										$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
										$html_js_tot	.= 'var expression_'.$dico[$i_elem]['ID_ZONE'].' 	= "'.trim($dico[$i_elem]['EXPRESSION'])."\" ;\n";
										$html_js_tot	.= 'var affiche_sous_totaux_'.$dico[$i_elem]['ID_ZONE'].' 	= '.$dico[$i_elem]['AFFICHE_SOUS_TOTAUX'].';'."\n";
										$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array ("'.$dico[$i_elem]['CHAMP_PERE'].'");'."\n";
										$html_js_tot	.= 'var tab_col_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html_js_tot	.= ',"'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'"';
										}
										$html_js_tot	.= ');'."\n";
										$html_js_tot	.= 'var tab_ligne_'.$dico[$i_elem]['ID_ZONE'].'	= new Array ();'."\n";
										$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										$html_js_tot	.= "</script>"."\n";
										$html			.= $html_js_tot ;
										
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
											$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='text' ";
											$html 	        .= " VALUE=\"$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
											$html 	        .= "".$dico[$i_elem]['ATTRIB_OBJET']." $set_TOTAL_Chp ></TD>\n";
										}
										
										if($aff_total){	
											$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_0"."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
										
											$html_fonc_TotMatFrml 		.= ' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');'. "\n" ;
										}
										
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'label'){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue);
										$html 			.= "</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
												$html .= '$'.$dico[$i_elem]['CHAMP_PERE']."_".$ligne;
											$html .= "</TD>\n";
											
										}
										$html 			.= "\t</TR>\n";
									//break;
							}
							elseif ( $dico[$i_elem]['TYPE_OBJET'] == 'combo' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_combo' ){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t".'<TR>'."\n";
										$dynamic_content_object = false ;
										if( (trim($dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($dico[$i_elem]['CHAMP_INTERFACE']) <> '') && (trim($dico[$i_elem]['TABLE_INTERFACE']) <> '') ){
											//if( ereg('\$code_annee', $dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) ){
												$dynamic_content_object = true ;
											//}
										}
										if($dynamic_content_object <> true){
											$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
										}
										
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
												$html		.= "<SELECT ".$dico[$i_elem]['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''.$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."'>\n";
												$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 

												if($dynamic_content_object == true){
													$html 	.= '<!--DynContentObj_'.$dico[$i_elem]['ID_ZONE'].'_'.$dico[$i_elem]['CHAMP_PERE'].'_'.$ligne.'-->'."\n"; 
												}else{
													foreach($result_nomenc as $element_result_nomenc){
															//Un valeur du combo pour chaque valeur de la nomenclature 
															$html 	.= "\t\t\t<OPTION VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].">"; 
															$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
													}
												}
												
												$html 		.= "\t\t</SELECT></TD>\n";
											
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ( $dico[$i_elem]['TYPE_OBJET'] == 'dimension_colonne' ){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										$html 			.= "\t".'<TR>'."\n";
										$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."' ".$dico[$i_elem]['ATTRIB_OBJET'].">";
											//Un champ cacher pr assurer le post avec un libelle afficher pour chaque valeur de la nomenclature
											$html .= "<INPUT TYPE='hidden' tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])] .'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ";
											$html .= " VALUE=\"$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
											$html .= " />\n".$result_nomenc[$ligne]['LIBELLE']."</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'valeur_multiple' and ( trim($dico[$i_elem]['NOM_GROUPE']) <> '' ) ){
								
								$nom_groupe = $dico[$i_elem]['NOM_GROUPE'];
								if ( isset($dico[$i_elem]['ID_ZONE_REF']) and (!in_array($nom_groupe,$nom_groupe_val_multi_affiche)) ){ // cas de G et/ou F sans les redoublants
									$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
									$requete				="
											SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
											FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
											WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
											AND			DICO_ZONE.ID_ZONE = ".$dico[$i_elem]['ID_ZONE_REF'].
											" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
											AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
											AND			DICO_ZONE.ID_THEME = ".$id_theme.
											" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
												
									// Traitement Erreur Cas : GetAll / GetRow
									try {
											$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
											if(!is_array($element_eff)){                    
													throw new Exception('ERR_SQL');  
											} 
																				
									}
									catch(Exception $e){
											$erreur = new erreur_manager($e,$requete);
									}
									// Fin Traitement Erreur Cas : GetAll / GetRow
	
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
			
									$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
									$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
									$html     .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</b></TD>\n";
									//echo "<br>$requete<br>";
									//echo'<pre>';
									//print_r($tab_champ_val_multi);
									
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
													$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($champ['ID_ZONE'],$langue)."</TD>\n";
											}
									}
									$html 			.= "\t".'</TR>'."\n";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";			
									$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
									foreach($result_nomenc as $element_result_nomenc){		
	
										if(!isset($classe_fond)) {
											$classe_fond = 'ligne-paire';
										} else {
											if($classe_fond == 'ligne-paire') {
													$classe_fond = 'ligne-impaire';
											} else {
													$classe_fond = 'ligne-paire';
											}
										}
	
										$html			.= "\t".'<TR>'."\n";		
										$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
										$affiche_total	=	false;
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$fonction_somme ='';
												if($champ['AFFICHE_TOTAL']){
														$affiche_total	=	true;
														$fonction_somme = " onBlur=Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."',"."'$ligne'); ";
												}
												$tabindex[$ligne]++;
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
												$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
												$html .= $champ['ATTRIB_OBJET']."$fonction_somme></TD>\n";
											}
										}
										$html 			.= "\t".'</TR>'."\n";
									}
									if($affiche_total==true){
										$html		.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
										$html		.= "\t".'</TR>'."\n";
										$html		.= "\t"."<TR  class='ligne-titre'>\n";		
										$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='ligne-titre'><b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$tabindex[$ligne]++;
												if($champ['AFFICHE_TOTAL']){
													$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='ligne-titre' nowrap>";
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$champ['CHAMP_PERE']."_".$ligne."' TYPE='text' VALUE='' readonly style='font-weight:bold;'";
													$html .= $champ['ATTRIB_OBJET']."></TD>\n";
												}else{
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
												}
											}
										}
										$html 			.= "\t".'</TR>'."\n";
										$html.= "\t <script language='javascript' type='text/javascript'>\n";
										$html.= "\t <!--\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												if($champ['AFFICHE_TOTAL']){
													$html.= "\t Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."','$ligne');\n";
												}
											}
										}
										$html.= "\t -->\n";
										$html.= "\t </script>\n";
									}// fin if($affiche_total==true)
								}
								$affiche_eff = 1;
							}
						elseif ($dico[$i_elem]['TYPE_OBJET'] == 'text' and ( trim($dico[$i_elem]['NOM_GROUPE']) <> '' ) ){
							$set_TOTAL_Chp = '';
							$aff_total	= false;
							if( ($dico[$i_elem]['AFFICHE_TOTAL'] || $dico[$i_elem]['AFFICHE_TOTAL_VERTIC']) && ($dico[$i_elem]['TYPE_OBJET'] == 'text') && ($dico[$i_elem]['TYPE_ZONE_BASE'] == 'int')){
								$aff_total	= true;
								$set_TOTAL_Chp 	= ' onchange="set_TOTAL_MatFrml(\'form1\', '.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
							}
							$aff_sous_total	= false;
							if( ($dico[$i_elem]['AFFICHE_SOUS_TOTAUX']) && ($dico[$i_elem]['TYPE_OBJET'] == 'text') && ($dico[$i_elem]['TYPE_ZONE_BASE'] == 'int')){
								$aff_sous_total	= true;
							}
							$nom_groupe = $dico[$i_elem]['NOM_GROUPE'];
							$html_js_tot = '' ;
							$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
							$html_js_tot	.= 'var expression_'.$dico[$i_elem]['ID_ZONE'].' 	= "'.trim($dico[$i_elem]['EXPRESSION'])."\" ;\n";
							$html_js_tot	.= 'var affiche_sous_totaux_'.$dico[$i_elem]['ID_ZONE'].' 	= '.$dico[$i_elem]['AFFICHE_SOUS_TOTAUX'].';'."\n";
							//$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array ("'.$dico[$i_elem]['CHAMP_PERE'].'");'."\n";
							$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;
							$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array (';
							for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
								$html_js_tot	.= ',"'.$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre].'"';
							}
							$html_js_tot	.= ');'."\n";
							
							$html_js_tot	.= 'var tab_col_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
							for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
								$html_js_tot	.= ',"'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'"';
							}
							$html_js_tot	.= ');'."\n";
							$html_js_tot	.= 'var tab_ligne_'.$dico[$i_elem]['ID_ZONE'].'	= new Array ();'."\n";
							$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
							$html_js_tot	.= "</script>"."\n";
							$html			.= $html_js_tot ;
							
							$jj = 0;
							if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
									$nom_groupe_tot_affiche[]	=	$nom_groupe;
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									
									$html			.= "\t".'<TR>'."\n";
									/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
									else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
									$jj++;*/
									$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>"; 
									$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
									$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;
	
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
												$tabindex[$ligne]++;
												$html .= "\t\t<TD colspan='".($nb_colspan_2 * $nb_cols_zone_mat / $nb_elem_grp)."' class='".$classe_fond."' nowrap>";
	
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='text' ";
												$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
												$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]." $set_TOTAL_Chp></TD>\n";
											}
											
											if($aff_sous_total){	
												$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."  
														<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_COLONNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											}elseif($affiche_sous_totaux){
												$html	.="\t\t<TD>&nbsp;</TD>\n";
											}
											
									}
									if($aff_total){
										if($aff_sous_total)
										for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
											$html	.="\t\t<TD $rowspan_chp_tot colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
													$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_LIGNE_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
										}
										if($nb_elem_grp > 1){
											$html	.="\t\t<TD $rowspan_chp_tot  align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_LIGNE_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
										}
										$html_fonc_TotMatFrml 		.= ' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');'. "\n" ;
									}
									$html 			.= "\t".'</TR>'."\n";
							}
						}elseif($dico[$i_elem]['TYPE_OBJET'] == 'booleen' and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
								$result_nomenc 		= $this->requete_nomenclature_bool($dico[$i_elem]['CHAMP_PERE'], $langue );
								$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
									//Pour chaque libell� de la nomenclature
									if($pass > 0) $html.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
										$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])] .'\' '."NAME=".$dico[$i_elem]['CHAMP_PERE']."_".$ligne." TYPE='radio'";
										$html       .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])]; 
										$html       .= "".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
									}
									$html 					.= "\t".'</TR>'."\n";
									$pass ++;
								}
								//echo 'test';
								//exit;
							}elseif($dico[$i_elem]['TYPE_OBJET'] == 'checkbox' and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
	
								$html 			.= "\t".'<TR>'."\n";
								$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
								$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									$tabindex[$ligne]++;
									$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
									$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='checkbox' ";
									$html 	        .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]; 
									$html 	        .= $dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
								}
								$html 			.= "\t".'</TR>'."\n";
							}
					}// fin parcours des �l�ments de dico
					//// generation des lignes comme total ou nbre redoublants
					$html		.= "</TABLE>";
		
		////////////////////////////////////////////////////////////////////
		///////////////////Affichage verticale des mesures//////////////////
		///////////////////////////////////////////////////////////////////
		}else{
						
			// Calcul du nombre de lignes de libell�s
			$nb_colspan_2 = 1;
			$nb_chp_eff = 0;
			//$nb_colspan_1 = 1;
			$nb_colspan_1 = 2;
			$tab_champ_val_multi = array();
			$tab_groupe = array();
			$tab_elem_zone_ref =array();
			$tab_zone_ref = array();
			
			$tab_elem_grp 				= array();
				
			$nom_grp_deja_aff 			= array();
			$html_fonc_TotMatEff_1 		= '';
			$affiche_totaux_mat_in_Eff_1	= array();
			$html_fonc_TotMatFrml 		= '';
			$colspan_Total_2Dim			= array();
			$tab_nb_zone_mat_par_grp	= array();
			$aff_total_vertic			= array();
			$grd_total_vertic 			= 0 ;
			$tabms_matricielles 		= $this->get_tabms_matricielles($dico);
					
			$tabindex = array();
					foreach($dico as $i_elem => $element){
						$element['NOM_GROUPE'] = trim($element['NOM_GROUPE']);
						if ( trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'booleen'  or trim($element['TYPE_OBJET']) == 'systeme_liste_radio' ){
							 // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
							$nb_colspan_1       = 2;
						}  
							//echo"<br>".$element['TYPE_OBJET'] ."<br>";
						if( in_array( ($element['TABLE_MERE'] . '_' . $element['ID_TABLE_MERE_THEME']), $tabms_matricielles )){
							if( ( $element['TYPE_OBJET'] <> 'dimension_ligne')  && ( $element['TYPE_OBJET'] <> 'dimension_colonne') ){
								if( trim($element['NOM_GROUPE']) == '' ){
									$element['NOM_GROUPE'] 			= $element['ID_TABLE_MERE_THEME'];
									$dico[$i_elem]['NOM_GROUPE']	= $element['ID_TABLE_MERE_THEME'];
								}else{
									$element['NOM_GROUPE'] 			.= '_' . $element['ID_TABLE_MERE_THEME'];
									$dico[$i_elem]['NOM_GROUPE']	= $element['NOM_GROUPE'];
								}
								$dico[$i_elem]['OBJET_MATRICIEL']	= true;
								$tab_elem_grp[$element['NOM_GROUPE']][] = $element;
								
								if(!isset($tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']])){
									$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']] = 1 ;
								}else{
									$tab_nb_zone_mat_par_grp[$element['NOM_GROUPE']]++;
									
								}
								if( $element['AFFICHE_TOTAL'] && ($element['TYPE_OBJET'] == 'text') && ($element['TYPE_ZONE_BASE'] == 'int') && ($element['AFFICHE_TOTAL_VERTIC']) ){
									$grd_total_vertic = 1 ;
								}
							}
						}
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
							$tab_champ_val_multi[$element['NOM_GROUPE']][] = array('CHAMP_PERE' =>$element['CHAMP_PERE'],
																					'ATTRIB_OBJET' =>$element['ATTRIB_OBJET'],
																					'ID_ZONE' =>$element['ID_ZONE'],
																					'AFFICHE_TOTAL' =>$element['AFFICHE_TOTAL']
																					);
							if(!isset($tab_zone_ref[$element['NOM_GROUPE']])){
									//$id_zone_ref	=	$element['ID_ZONE_REF'] ;
									$tab_zone_ref[$element['NOM_GROUPE']] = $element['ID_ZONE_REF'];
									$requete				="
											SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
											FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
											WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
											AND			DICO_ZONE.ID_ZONE = ".$element['ID_ZONE_REF'].
											" AND		DICO_ZONE.ID_THEME = DICO_THEME.ID				
											AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
											AND			DICO_ZONE.ID_THEME = ".$id_theme.
											" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
												
									// Traitement Erreur Cas : GetAll / GetRow
									try {
											$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
											if(!is_array($element_eff)){                    
													throw new Exception('ERR_SQL');  
											} 
																				
									}
									catch(Exception $e){
											$erreur = new erreur_manager($e,$requete);
									}
									// Fin Traitement Erreur Cas : GetAll / GetRow
	
									$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
									$inc	=	-1;
									$tab_elem_zone_ref = array();
									foreach($result_nomenc as $element_result_nomenc){
											$inc++;
											//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];
											$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
											//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
									}
									$html.= "\t <script language='javascript' type='text/javascript'>\n";
									$html.= "\t <!--\n";
										$html.= "\t\t function Calcul_Total_Ligne_".$element['NOM_GROUPE']."(champ_op,ligne){\n";
											$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
											$html.= "\t\t\t var chaine_eval;\n";
											$html.= "\t\t\t var total = 0;\n";
										 $nb_code	=	-1;
										 foreach($tab_elem_zone_ref as $code){
												$nb_code++;
												$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = 	parseFloat(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
												//$html.= "\t\t\t alert(chaine_eval);\n";
												$html.= "\t\t\t eval(chaine_eval);\n";
											} 
											$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
												$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
													$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
													$html.= "\t\t\t\t\t eval(chaine_eval);\n";
												$html.= "\t\t\t\t	}\n";
											$html.= "\t\t\t }\n";
											$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										$html.= "\t\t }\n";
									$html.= "\t -->\n";
								  $html.= "\t </script>\n";
							}
							$nb_colspan_2++;
							$nb_chp_eff++;	
					}
					elseif( ($element['TYPE_OBJET'] == 'text') and ( trim($element['NOM_GROUPE']) <> '' ) ){
							$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
							$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	    = $element['ATTRIB_OBJET'] ;
							$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 			= $element['ID_ZONE'] ;
					}
			}
					if($nb_colspan_2 > 3) $nb_colspan_2 = 2;
					// recherche de la valeur de colspan des champs type effectifs
					if(is_array($tab_champ_val_multi)){
						foreach($tab_champ_val_multi as $grp_type_eff => $_temp_tab){
							if(count($tab_champ_val_multi[$grp_type_eff]) > 1){
								$nb_colspan_2 = 2;
							}else{
								$nb_colspan_2 = 1;
							}
						}
					}
					$nb_cols_zone_mat = 1;
					//if(count($tab_nb_zone_mat_par_grp)){
					//	eval ('$nb_cols_zone_mat = ' . implode(' * ', $tab_nb_zone_mat_par_grp) . ' ;') ;
					//}
	
					$key_tab_groupe 		= array_keys($tab_groupe);
					$NB_CHAMPS_MULTI		= $nb_colspan_2;
					$NB_TOTAL_COL		 	= $nb_colspan_1 + $nb_colspan_2 * $nb_cols_zone_mat * $NB_LIGNE_ECRAN;
					if($grd_total_vertic){
						$NB_TOTAL_COL		+= $nb_cols_zone_mat + $grd_total_vertic ;
					}
					$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
					$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
					for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
						$tabindex[$ligne] = ($ligne * 1000) +1 ;
					}
					$ii = 0;
			
				if( is_array($tabms_matricielles) && count($tabms_matricielles) ){
					$mat_dim_ligne 		=	array();
					$mat_liste_mes 			= 	array();
					$tab_libelles_mesures 	= 	array();
					$tab_types_mesures 		= 	array();
					$attrib_obj_mesures 	= 	array();
					$elem_obj_mesures 		= 	array();
					$mat_dim_colonne		= 	array();
					$mat_codes_ligne		= 	array();
					$mat_codes_colonne		= 	array();
					
					$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
										WHERE CODE_NOMENCLATURE=1 
										AND NOM_TABLE='DICO_BOOLEEN'
										AND CODE_LANGUE='".$langue."';";
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
								if(!is_array($aresult)){                    
										throw new Exception('ERR_SQL');  
								} 
								$GLOBALS['libelle_oui'] = ($aresult[0]['LIBELLE'] ?? '');									
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$requete);
						}
						
					$requete        = " SELECT LIBELLE FROM DICO_TRADUCTION
										WHERE CODE_NOMENCLATURE=0 
										AND NOM_TABLE='DICO_BOOLEEN'
										AND CODE_LANGUE='".$langue."';";
						// Traitement Erreur Cas : GetAll / GetRow
						try {
								$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
								if(!is_array($aresult)){                    
										throw new Exception('ERR_SQL');  
								} 
								$GLOBALS['libelle_non'] = ($aresult[0]['LIBELLE'] ?? '');									
						}
						catch(Exception $e){
								$erreur = new erreur_manager($e,$requete);
						}
				}
					
					foreach($dico as $i_elem => $element){
										
						$dico[$i_elem]['NOM_GROUPE'] = trim($dico[$i_elem]['NOM_GROUPE']);
						if(!isset($classe_fond)) {
							$classe_fond = 'ligne-paire';
						} else {
							if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
							} else {
								$classe_fond = 'ligne-paire';
							}
						}
						
						//Pour recuperer la var mat_nomenc_colonne mat_dim_col
						$mat_dim_col	=	$this->get_dims_zone_matricielle('dimension_colonne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
						if(is_array($mat_dim_col) && count($mat_dim_col)){
							$this->mat_dim_col = $mat_dim_col;
							$this->mat_nomenc_col	= $this->requete_nomenclature($this->mat_dim_col['TABLE_FILLE'], $this->mat_dim_col['CHAMP_FILS'], $this->mat_dim_col['CHAMP_PERE'], $langue ,$id_systeme, $this->mat_dim_col['SQL_REQ']) ;
						}
						//Fin Pour recuperer la var mat_nomenc_colonne
						
						if( (isset($dico[$i_elem]['OBJET_MATRICIEL']) && ($dico[$i_elem]['OBJET_MATRICIEL'] == true)) &&  ( $dico[$i_elem]['TYPE_OBJET'] <> 'dimension_ligne')  && ( $dico[$i_elem]['TYPE_OBJET'] <> 'dimension_colonne')){
							if(!in_array($dico[$i_elem]['NOM_GROUPE'], $nom_grp_deja_aff)){
								$nom_grp_deja_aff[]	=	$dico[$i_elem]['NOM_GROUPE'];
								$grp_mat = $dico[$i_elem]['NOM_GROUPE'];
								
								$mat_dim_colonne[$grp_mat] 	=	$this->get_dims_zone_matricielle('dimension_ligne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
								if(!(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat]))){
									$mat_dim_colonne[$grp_mat]	=	$this->get_dims_zone_matricielle('dimension_colonne', $dico[$i_elem]['ID_TABLE_MERE_THEME'], $dico);
								}
								$libelle_mat = $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue);
								
								if(is_array($mat_dim_colonne[$grp_mat]) && count($mat_dim_colonne[$grp_mat])){
									$mat_nomenc_colonne	= $this->requete_nomenclature($mat_dim_colonne[$grp_mat]['TABLE_FILLE'], $mat_dim_colonne[$grp_mat]['CHAMP_FILS'], $mat_dim_colonne[$grp_mat]['CHAMP_PERE'], $langue ,$id_systeme, $mat_dim_colonne[$grp_mat]['SQL_REQ']) ;
									foreach($mat_nomenc_colonne as $mat_i => $mat_val){
										$mat_codes_colonne[$grp_mat][] = array('code' => $mat_val[$this->get_champ_extract($mat_dim_colonne[$grp_mat]['CHAMP_PERE'])], 'libelle' => $mat_val['LIBELLE'] );
									}
								}
								if( count($mat_codes_ligne[$grp_mat]) || count($mat_codes_colonne[$grp_mat]) ){
									$mat_liste_mes[$grp_mat] 			= array();
									$tab_libelles_mesures[$grp_mat] 	= array();
									$tab_lib_mes_tot[$grp_mat]			= array();
									$tab_types_mesures[$grp_mat] 		= array();
									$attrib_obj_mesures[$grp_mat] 		= array();
									$elem_obj_mesures[$grp_mat] 		= array();
									
									foreach($tab_elem_grp[$dico[$i_elem]['NOM_GROUPE']] as $mat_i => $mat_val){
										if($this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue)){
											$tab_libelles_mesures[$grp_mat][$mat_i]	= 	$this->recherche_libelle_zone($mat_val['ID_ZONE'], $langue);
										}
										$mat_liste_mes[$grp_mat][$mat_i]		= $mat_val['CHAMP_PERE'];
										$tab_types_mesures[$grp_mat][$mat_i] 	= $mat_val['TYPE_OBJET'];
										$attrib_obj_mesures[$grp_mat][$mat_i] 	= $mat_val['ATTRIB_OBJET'];
										
										$elem_obj_mesures[$grp_mat][$mat_i] 	= $mat_val ;
										if( $mat_val['AFFICHE_TOTAL'] && ($mat_val['TYPE_OBJET'] == 'text') && ($mat_val['TYPE_ZONE_BASE'] == 'int')){
											$affiche_totaux_mat_in_Eff_1[$grp_mat][$mat_i] 	= true;
											$tab_lib_mes_tot[$grp_mat][$mat_i]			=	$tab_libelles_mesures[$grp_mat][$mat_i] ;
											if( $mat_val['AFFICHE_TOTAL_VERTIC'] ){
												$aff_total_vertic[$grp_mat][$mat_i] 	= true;
											}
										}
									}
									$nb_mesures[$grp_mat] =  count($mat_liste_mes[$grp_mat]);
									//$set_TOTAL_Mat_in_Gril 		= ' onchange="set_TOTAL_MatFrml_ALL(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
									//$fnc_TOTAL_Mat_in_Gril  	= ' set_TOTAL_MatFrml_ALL(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' ;
									$html_js_tot = '' ;
									if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
										$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
										$html_js_tot	.= 'var expression_'.$dico[$i_elem]['ID_ZONE'].' 	= "'.trim($dico[$i_elem]['EXPRESSION'])."\" ;\n";
										$html_js_tot	.= 'var affiche_sous_totaux_'.$dico[$i_elem]['ID_ZONE'].' 	= '.$dico[$i_elem]['AFFICHE_SOUS_TOTAUX'].';'."\n";
										$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array (';
										
										foreach($affiche_totaux_mat_in_Eff_1[$grp_mat] as $i_mes => $tot_mes){
											$html_js_tot	.= ',"'.$mat_liste_mes[$grp_mat][$i_mes].'"';
										}
										$html_js_tot	.= ');'."\n";
										
										$html_js_tot	.= 'var tab_col_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										//if(count($mat_codes_ligne[$grp_mat])){
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html_js_tot	.= ',"'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'"';
											}
										//}
										$html_js_tot	.= ');'."\n";
								
										$html_js_tot	.= 'var tab_ligne_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										
										if(count($mat_codes_colonne[$grp_mat])){
											foreach($mat_codes_colonne[$grp_mat] as $col => $code_col){
												$html_js_tot	.= ',"'.$code_col['code'].'"';
											}
										}
										$html_js_tot	.= ');'."\n";
										
										$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
											
										$html_js_tot	.= "</script>"."\n";
										
										$html			.= $html_js_tot ;
										
									}
									
									$html		.=	"\t".'<TR>'."\n";		
									$html 		.=	"\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html		.=	"\t".'</TR>'."\n";
			
									$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
									$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
									$html     	.= $this->recherche_libelle_zone($mat_dim_colonne[$grp_mat]['ID_ZONE'], $langue)."</b></TD>\n";
									//echo "<br>$requete<br>";
									//echo'<pre>';
									//print_r($tab_champ_val_multi);
	
									$colspan_cur_zone_mat 	= ($nb_colspan_2 * $nb_cols_zone_mat) / count($mat_liste_mes[$grp_mat]) ;
									$colspan_vertic_tot 	= $nb_cols_zone_mat / count($mat_liste_mes[$grp_mat]) ;	
									//$with_cur_zone_mat 		= floor(100  / count($mat_liste_mes[$grp_mat])) .  '%' ; 
									
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
									}
									
									if(count($aff_total_vertic[$grp_mat])){	
										if(count($aff_total_vertic[$grp_mat]) > 1){
											$html .= "\t\t<TD COLSPAN='2' class='".$classe_fond."'>";
											$html .=  "<b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										}else{
											$html .= "\t\t<TD  class='".$classe_fond."'>";
											$html .=  "<b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										}
									}else{
										//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
									}
									$html 			.= "\t".'</TR>'."\n";
									$html			.= "\t".'<TR>'."\n";		
									$html 			.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";			
									if(is_array($mat_codes_colonne[$grp_mat])){
										foreach($mat_codes_colonne[$grp_mat] as $col => $elem_col){	
		
											if(!isset($classe_fond)) {
												$classe_fond = 'ligne-paire';
											} else {
												if($classe_fond == 'ligne-paire') {
													$classe_fond = 'ligne-impaire';
												} else {
													$classe_fond = 'ligne-paire';
												}
											}
		
											$html			.= "\t".'<TR>'."\n";		
											//$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
											if(!$affiche_sous_totaux){
												$rowspan = count($mat_liste_mes[$grp_mat]);
											}else{
												$rowspan = count($mat_liste_mes[$grp_mat]) + 1;
											}
											$html 			.="\t\t"."<TD style='vertical-align:middle' ROWSPAN='$rowspan' nowrap class='".$classe_fond."'>". $elem_col['libelle'] ."</TD>\n";
											//$affiche_total	=	false;
											$k=1;
											foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
												$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
												$html .= $tab_libelles_mesures[$grp_mat][$i_mes]."</TD>\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													
													//foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){					
													  // Pour chaque colonne, on imprime la (ou les) zone(s) de texte
														if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){	
															//$set_TOTAL_MatFrml_ALL 	=	' onchange="set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
															$set_TOTAL_MatFrml_Chp 	= ' onchange="set_TOTAL_MatFrml_Champ(\'form1\', '.$dico[$i_elem]['ID_ZONE'].', 0, \''.$nom_champ_mes.'\', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].', '.$elem_col['code'].', '.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].', tab_mes_'.$dico[$i_elem]['ID_ZONE'].');" ';
														}
														$GLOBALS['cell_mat_index'] = $tabindex[$ligne]++;
														$html		.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."' nowrap='nowrap' align='center' style='vertical-align:middle' CLASS='".$classe_fond."'>";
														
														$html		.= $this->get_cell_matrice(0 , $elem_col['code'].'_'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])], $elem_obj_mesures[$grp_mat][$i_mes], $set_TOTAL_MatFrml_Chp, $langue, $id_systeme);
														
														$html		.= "</TD>\n";
													//}
												}
												if(count($aff_total_vertic[$grp_mat])){	
													//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
													//foreach($aff_total_vertic[$grp_mat] as $i_mes => $tot_mes){
														$html	.="\t\t<TD  colspan='".($colspan_vertic_tot)."'  align='center' bgcolor='#CCCCCC'>"."  
																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$nom_champ_mes."_LIGNE_0_".$elem_col['code']."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
													//}
													if( count($aff_total_vertic[$grp_mat]) > 1){
														if(($k==1) && (!$affiche_sous_totaux))
															$html	.="\t\t<TD style='vertical-align:middle' ROWSPAN='$rowspan'  align='center' bgcolor='#CCCCCC'>"."  
																	<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																	 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_COLONNE_".$elem_col['code']."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
														$k++;
													}
												}else{
													//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
												}
												$html 			.= "\t".'</TR>'."\n";
											}
											if( $affiche_sous_totaux ){
												$html 		    .= "\n\t<TR>\n";
												$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
												$html .= $this->recherche_libelle_page('sous_total',$langue,'questionnaire.php')."</TD>\n";			
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													$html		.="\t\t<TD align='center' bgcolor='#CCCCCC'>".
																"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
																NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_LIGNE_".$elem_col['code'].'_'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}
												$html	.="\t\t<TD style='vertical-align:middle' align='center' bgcolor='#CCCCCC'>"."  
														<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_LIGNE_0_".$elem_col['code']."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												$html 		    .="\n\t</TR>\n";
											}
										}
									}
									
									if(count($affiche_totaux_mat_in_Eff_1[$grp_mat])){
										$html		.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
										$html		.= "\t".'</TR>'."\n";
										
										if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){
											//$rowspan_chp_tot = 'rowspan=\'2\'';
											$rowspan_chp_tot = 'rowspan=\''.($rowspan + 1).'\'';
											$br_chp_tot = '';
										}else{
											//$rowspan_chp_tot = '';
											$rowspan_chp_tot = 'rowspan=\''.$rowspan.'\'';
											$br_chp_tot = '';
										}
										
										$html		.= "\t"."<TR  style='background-color:#CCCCCC; text-align: right; font-weight:bold;'>\n";	
											
										$html 		.="\t\t"."<TD  style='vertical-align:middle'  align='center' COLSPAN='$nb_colspan_1' nowrap $rowspan_chp_tot style='background-color:#CCCCCC; text-align: right; font-weight:bold;'><b>$br_chp_tot".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										
										$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
										$k=1;
										foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												//foreach($mat_liste_mes[$grp_mat] as $i_mes => $nom_champ_mes){
													if( $affiche_totaux_mat_in_Eff_1[$grp_mat][$i_mes] == true ){
														$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>"."  
																		<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
																		 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$nom_champ_mes."_COLONNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
													}else{
														$html	.= "\t\t<TD  colspan='".($colspan_cur_zone_mat)."'  align='center' bgcolor='#CCCCCC'>&nbsp;</TD>\n";
													}
												//}
											}
										
											if(count($aff_total_vertic[$grp_mat])){	
												//$html_fonc_TotMatEff_1 	.=	' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', '.$ligne.', tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');' . "\n";
												if(count($aff_total_vertic[$grp_mat])>1){
													$html	.="\t\t<TD colspan='2'  align='center' bgcolor='#CCCCCC'>"."  
															$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$nom_champ_mes."_COLONNE_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												}else{
													$html	.="\t\t<TD align='center' bgcolor='#CCCCCC'>"."  
															$br_chp_tot <INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
															 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_".$nom_champ_mes."_COLONNE_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
												
												}
												
												//if(count($aff_total_vertic[$grp_mat]) > 1){
//													if($k==1)
//														$html	.="\t\t<TD   align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
//																<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
//																 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
//													$k++;
//												}
											}elseif(count($aff_total_vertic)){
												//$html .= "\t\t<TD COLSPAN=".($nb_cols_zone_mat + $grd_total_vertic)." class='".$classe_fond."'></TD>";
											}
											$html 			.= "\t".'</TR>'."\n";
										}
										
										if( count($affiche_totaux_mat_in_Eff_1[$grp_mat]) > 1 ){				
											$html 		    .= "\n\t<TR>\n";
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html		.="\t\t<TD align='center' COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." bgcolor='#CCCCCC'>".
															"<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold' 
															NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_COLONNE_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											}
											$html	.="\t\t<TD colspan='2' align='center' bgcolor='#CCCCCC'> $br_chp_tot"."  
														<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
														 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_0' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											
											$html 		    .="\n\t</TR>\n";
										}
									}// fin if($affiche_total==true)
								}	
							}
						}elseif( ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio') and ($dico[$i_elem]['BOUTON_INTERFACE']=='popup') ){
										//echo"<br>ICI<br>";
										$html			.= "\t".'<TR>'."\n";		
										$html 			.= "\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       	.= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html 		.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
												if( trim($dico[$i_elem]['CHAMP_INTERFACE'])<>trim($dico[$i_elem]['CHAMP_PERE']) ){
														$html .= "<INPUT  ID='".$dico[$i_elem]['CHAMP_PERE']."_$ligne'name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='hidden' value='$".$dico[$i_elem]['CHAMP_PERE']."_$ligne' >"; 
														$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																				value=\"$".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne\" readonly> ";
												}
								else{
														$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																			 value=\"$".$dico[$i_elem]['CHAMP_PERE']."_$ligne\" readonly> ";
												}
												//eval("\$fonction_click	=	".$dico[$i_elem]['FONCTION_INTERFACE'].";");
												$fonction	=	str_replace("\$ligne","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);
												//$dico[$i_elem]['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);	
								$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
												$html .= "\t\t</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif( ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio') and ($dico[$i_elem]['BOUTON_INTERFACE']=='saisie') ){
										//echo"<br>ICI<br>";
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
										//////
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
												$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." nowrap class='".$classe_fond."'>";
												if( trim($dico[$i_elem]['CHAMP_INTERFACE'])<>trim($dico[$i_elem]['CHAMP_PERE']) ){
														$html .= "<INPUT ID='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='hidden' value='$".$dico[$i_elem]['CHAMP_PERE']."_$ligne' >"; 
														$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																				value=\"$".$dico[$i_elem]['CHAMP_INTERFACE']."_$ligne\"
																				onBlur=SAISIE_".$dico[$i_elem]['CHAMP_PERE']."('".$dico[$i_elem]['CHAMP_INTERFACE']."_".$ligne."','".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."');> ";
																				
												}
											else{
														$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$dico[$i_elem]['CHAMP_PERE']."_$ligne' type='text' ".$dico[$i_elem]['ATTRIB_OBJET']."
																			 value=\"$".$dico[$i_elem]['CHAMP_PERE']."_$ligne\"> ";
												}
												//eval("\$fonction_click	=	".$dico[$i_elem]['FONCTION_INTERFACE'].";");
												//$fonction	=	str_replace("\$ligne","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);
												//$dico[$i_elem]['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$dico[$i_elem]['FONCTION_INTERFACE']);	
								//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
												$html .= "\t\t</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'liste_radio' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_liste_radio'){
	
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 						.= "\t".'<TR>'."\n";
									//echo 'param ' .$dico[$i_elem]['TABLE_FILLE'].$dico[$i_elem]['TYPE_OBJET'].'<br>';
									$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
									$rowspan		 			= count($result_nomenc);
									$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
									$html             .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
									
									$pass = 0;
									foreach($result_nomenc as $element_result_nomenc){		
								//Pour chaque libell� de la nomenclature
									if($pass > 0) $html.= "\t".'<TR>'."\n";		
											$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
											for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													$tabindex[$ligne]++;
													$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])] .'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='radio'";
													$html       .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])]; 
													$html       .= "".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											}
											
											$html 					.= "\t".'</TR>'."\n";
											$pass ++;
										}
									}
							
							elseif ( ($dico[$i_elem]['TYPE_OBJET'] == 'text' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_text') and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
										
										$set_TOTAL_Chp = '';
										$aff_total	= false;
										if( ($dico[$i_elem]['AFFICHE_TOTAL'] || $dico[$i_elem]['AFFICHE_TOTAL_VERTIC']) && ($dico[$i_elem]['TYPE_OBJET'] == 'text') && ($dico[$i_elem]['TYPE_ZONE_BASE'] == 'int')){
											$aff_total	= true;
											$set_TOTAL_Chp 	= ' onchange="set_TOTAL_MatFrml(\'form1\', '.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');" ';
										}
									
										$html_js_tot = '' ;
										$html_js_tot	.= "<script language='javascript' type='text/javascript'>"."\n";
										$html_js_tot	.= 'var expression_'.$dico[$i_elem]['ID_ZONE'].' 	= "'.trim($dico[$i_elem]['EXPRESSION'])."\" ;\n";
										$html_js_tot	.= 'var affiche_sous_totaux_'.$dico[$i_elem]['ID_ZONE'].' 	= '.$dico[$i_elem]['AFFICHE_SOUS_TOTAUX'].';'."\n";
										$html_js_tot	.= 'var tab_mes_'.$dico[$i_elem]['ID_ZONE'].' 	= new Array ("'.$dico[$i_elem]['CHAMP_PERE'].'");'."\n";
										$html_js_tot	.= 'var tab_col_'.$dico[$i_elem]['ID_ZONE'].'	= new Array (';
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html_js_tot	.= ',"'.$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'"';
										}
										$html_js_tot	.= ');'."\n";
										$html_js_tot	.= 'var tab_ligne_'.$dico[$i_elem]['ID_ZONE'].'	= new Array ();'."\n";
										$html_js_tot	= str_replace('(,' , '(' , $html_js_tot)."\n";
										$html_js_tot	.= "</script>"."\n";
										$html			.= $html_js_tot ;
										
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t".'<TR>'."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
										
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html .= "\t\t<TD COLSPAN='".($nb_colspan_2 * $nb_cols_zone_mat)."' class='".$classe_fond."'>";
											$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_O_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='text' ";
											$html 	        .= " VALUE=\"$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
											$html 	        .= "".$dico[$i_elem]['ATTRIB_OBJET']." $set_TOTAL_Chp ></TD>\n";
										}
										
										if($aff_total){	
											$html	.="\t\t<TD  align='center' bgcolor='#CCCCCC'>"."  
													<INPUT TYPE= 'text' readonly='1' style='background-color:#CCCCCC; text-align: right; font-weight:bold;'
													 NAME='TOT_".$dico[$i_elem]['ID_ZONE']."_ALL_MES_0"."' ".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
											
											$html_fonc_TotMatFrml 		.= ' set_TOTAL_MatFrml(\'form1\','.$dico[$i_elem]['ID_ZONE'].', 0, tab_mes_'.$dico[$i_elem]['ID_ZONE'].', tab_ligne_'.$dico[$i_elem]['ID_ZONE'].', tab_col_'.$dico[$i_elem]['ID_ZONE'].');'. "\n" ;
										}
										
										$html 			.= "\t".'</TR>'."\n";
							
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'label'){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue);
										$html 			.= "</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
												$html .= '$'.$dico[$i_elem]['CHAMP_PERE']."_".$ligne;
											$html .= "</TD>\n";
											
										}
										$html 			.= "\t</TR>\n";
									//break;
							}
							elseif ( $dico[$i_elem]['TYPE_OBJET'] == 'combo' or $dico[$i_elem]['TYPE_OBJET'] == 'systeme_combo' ){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
	
										$html 			.= "\t".'<TR>'."\n";
										$dynamic_content_object = false ;
										if( (trim($dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($dico[$i_elem]['CHAMP_INTERFACE']) <> '') && (trim($dico[$i_elem]['TABLE_INTERFACE']) <> '') ){
											//if( ereg('\$code_annee', $dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $dico[$i_elem]['REQUETE_CHAMP_INTERFACE']) ){
												$dynamic_content_object = true ;
											//}
										}
										if($dynamic_content_object <> true){
											$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
										}
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												$tabindex[$ligne]++;
												$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
												$html		.= "<SELECT ".$dico[$i_elem]['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."'>\n";
												$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
												
												if($dynamic_content_object == true){
													$html 	.= '<!--DynContentObj_'.$dico[$i_elem]['ID_ZONE'].'_'.$dico[$i_elem]['CHAMP_PERE'].'_'.$ligne.'-->'."\n"; 
												}else{
													foreach($result_nomenc as $element_result_nomenc){
															//Un valeur du combo pour chaque valeur de la nomenclature 
															$html 	.= "\t\t\t<OPTION VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])].">"; 
															$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
													}
												}
												
												$html 		.= "\t\t</SELECT></TD>\n";
											
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ( $dico[$i_elem]['TYPE_OBJET'] == 'dimension_colonne' ){
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										
										$html 			.= "\t".'<TR>'."\n";
										$result_nomenc 		= $this->requete_nomenclature($dico[$i_elem]['TABLE_FILLE'],$dico[$i_elem]['CHAMP_FILS'], $dico[$i_elem]['CHAMP_PERE'], $langue ,$id_systeme, $dico[$i_elem]['SQL_REQ']);
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
										$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."' ".$dico[$i_elem]['ATTRIB_OBJET'].">";
											//Un champ cacher pr assurer le post avec un libelle afficher pour chaque valeur de la nomenclature
											$html .= "<INPUT TYPE='hidden' tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' ";
											$html .= " VALUE=\"$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
											$html .= " />\n".$result_nomenc[$ligne]['LIBELLE']."</TD>\n";
										}
										$html 			.= "\t".'</TR>'."\n";
							}
							elseif ($dico[$i_elem]['TYPE_OBJET'] == 'valeur_multiple' and ( trim($dico[$i_elem]['NOM_GROUPE']) <> '' ) ){
								
								$nom_groupe = $dico[$i_elem]['NOM_GROUPE'];
								if ( isset($dico[$i_elem]['ID_ZONE_REF']) and (!in_array($nom_groupe,$nom_groupe_val_multi_affiche)) ){ // cas de G et/ou F sans les redoublants
									$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
									$requete				="
											SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
											FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
											WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
											AND			DICO_ZONE.ID_ZONE = ".$dico[$i_elem]['ID_ZONE_REF'].
											" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
											AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
											AND			DICO_ZONE.ID_THEME = ".$id_theme.
											" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
											" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
											ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
												
									// Traitement Erreur Cas : GetAll / GetRow
									try {
											$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
											if(!is_array($element_eff)){                    
													throw new Exception('ERR_SQL');  
											} 
																				
									}
									catch(Exception $e){
											$erreur = new erreur_manager($e,$requete);
									}
									// Fin Traitement Erreur Cas : GetAll / GetRow
	
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
			
									$html 		.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
									$html 		.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'> <b>";
									$html     .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</b></TD>\n";
									//echo "<br>$requete<br>";
									//echo'<pre>';
									//print_r($tab_champ_val_multi);
									
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
													$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($champ['ID_ZONE'],$langue)."</TD>\n";
											}
									}
									$html 			.= "\t".'</TR>'."\n";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";			
									$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
									foreach($result_nomenc as $element_result_nomenc){		
	
										if(!isset($classe_fond)) {
											$classe_fond = 'ligne-paire';
										} else {
											if($classe_fond == 'ligne-paire') {
													$classe_fond = 'ligne-impaire';
											} else {
													$classe_fond = 'ligne-paire';
											}
										}
	
										$html			.= "\t".'<TR>'."\n";		
										$html 			.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
										$affiche_total	=	false;
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$fonction_somme ='';
												if($champ['AFFICHE_TOTAL']){
														$affiche_total	=	true;
														$fonction_somme = " onBlur=Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."',"."'$ligne'); ";
												}
												$tabindex[$ligne]++;
												$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='".$classe_fond."' nowrap>";
												$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
												$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
												$html .= $champ['ATTRIB_OBJET']."$fonction_somme></TD>\n";
											}
										}
										$html 			.= "\t".'</TR>'."\n";
									}
									if($affiche_total==true){
										$html		.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='ligne-titre'></TD>\n";
										$html		.= "\t".'</TR>'."\n";
										$html		.= "\t"."<TR  class='ligne-titre'>\n";		
										$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='ligne-titre'><b>".$this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												$tabindex[$ligne]++;
												if($champ['AFFICHE_TOTAL']){
													$html .= "\t\t<TD  colspan='".$nb_cols_zone_mat."' class='ligne-titre' nowrap>";
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$champ['CHAMP_PERE']."_".$ligne."' TYPE='text' VALUE='' readonly style='font-weight:bold;'";
													$html .= $champ['ATTRIB_OBJET']."></TD>\n";
												}else{
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>&nbsp;</TD>\n";
												}
											}
										}
										$html 			.= "\t".'</TR>'."\n";
										$html.= "\t <script language='javascript' type='text/javascript'>\n";
										$html.= "\t <!--\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											foreach($tab_champ_val_multi[$nom_groupe] as $champ){
												if($champ['AFFICHE_TOTAL']){
													$html.= "\t Calcul_Total_Ligne_".$nom_groupe."('".$champ['CHAMP_PERE']."','$ligne');\n";
												}
											}
										}
										$html.= "\t -->\n";
										$html.= "\t </script>\n";
									}// fin if($affiche_total==true)
								}
								$affiche_eff = 1;
							}
						elseif ($dico[$i_elem]['TYPE_OBJET'] == 'text' and ( trim($dico[$i_elem]['NOM_GROUPE']) <> '' ) ){
							$jj = 0;
							$nom_groupe = $dico[$i_elem]['NOM_GROUPE'];
							if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
									$nom_groupe_tot_affiche[]	=	$nom_groupe;
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									
									$html			.= "\t".'<TR>'."\n";
									/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
									else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
									$jj++;*/
									$nb_elem_grp = count($tab_groupe[$nom_groupe]['CHAMP_PERE']) ;
									$html .=  "\t\t"."<TD style='vertical-align:middle' ROWSPAN='$nb_elem_grp' nowrap class='".$classe_fond."'>"; 
									$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
									
									for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
										$html .= "\t\t<TD  style='vertical-align:middle'  class='".$classe_fond."' nowrap>";
										if(isset($tab_libelles_mesures[$grp_mat])) $html .= $tab_libelles_mesures[$grp_mat][$ordre];
										$html .= "</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												//for( $ordre=0; $ordre < $nb_elem_grp; $ordre++ ){
													$tabindex[$ligne]++;
													$html .= "\t\t<TD colspan='".($nb_colspan_2 * $nb_cols_zone_mat / $nb_elem_grp)."' class='".$classe_fond."' nowrap>";
		
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='text' ";
													$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."\""; 
													$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]."></TD>\n";
												//}
										}
										$html 			.= "\t".'</TR>'."\n";
									}
									
							}
						}elseif($dico[$i_elem]['TYPE_OBJET'] == 'booleen' and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
								$result_nomenc 		= $this->requete_nomenclature_bool($dico[$i_elem]['CHAMP_PERE'], $langue );
								$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
									//Pour chaque libell� de la nomenclature
									if($pass > 0) $html.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
										$tabindex[$ligne]++;
										$html       .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])] .'\' '."NAME=".$dico[$i_elem]['CHAMP_PERE']."_".$ligne." TYPE='radio'";
										$html       .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($dico[$i_elem]['CHAMP_PERE'])]; 
										$html       .= "".$dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
									}
									$html 					.= "\t".'</TR>'."\n";
									$pass ++;
								}
								//echo 'test';
								//exit;
							}elseif($dico[$i_elem]['TYPE_OBJET'] == 'checkbox' and ( trim($dico[$i_elem]['NOM_GROUPE']) == '' ) ){
								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
	
								$html 			.= "\t".'<TR>'."\n";
								$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
								$html       .= $this->recherche_libelle_zone($dico[$i_elem]['ID_ZONE'],$langue)."</TD>\n";
								
								for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
									$tabindex[$ligne]++;
									$html .= "\t\t<TD COLSPAN=".($nb_colspan_2 * $nb_cols_zone_mat)." class='".$classe_fond."'>";
									$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])].'\' '."NAME='".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]."' TYPE='checkbox' ";
									$html 	        .= " VALUE=$".$dico[$i_elem]['CHAMP_PERE']."_0_".$this->mat_nomenc_col[$ligne][$this->get_champ_extract($this->mat_dim_col['CHAMP_FILS'])]; 
									$html 	        .= $dico[$i_elem]['ATTRIB_OBJET']."></TD>\n";
								}
								$html 			.= "\t".'</TR>'."\n";
							}
					}// fin parcours des �l�ments de dico
					//// generation des lignes comme total ou nbre redoublants
					$html		.= "</TABLE>";

		}
		$html		.= "\n" . $this->get_html_buttons($langue);
		
		$html 	.= "</FORM>";
		$html	.= "</div>"."\n";

		$html		  .= "<script language='javascript' type='text/javascript'>\n";
		$html 	  	  .= "<!--\n";
		//$html         .= "document.form1.elements[0].focus();\n";
		if(trim($html_fonc_TotMatEff_1) <> ''){
			$html	.= $html_fonc_TotMatEff_1 ;
		}
		if($html_fonc_TotMatFrml<>''){
			$html	.= $html_fonc_TotMatFrml;
		}
		$html 	  	  .= "//-->\n";
		$html 	  	  .= "</script>\n";

			//print '<BR>'.$element['FRAME'];
		if (trim($element['FRAME']) <> '') {
			file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
			echo $html;
		}else{
			if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
		}
		//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
		//echo $html; 	
	}//Fin function generer_frame_grille_ligne

//----------------------------------------------------------------------
//----------------------------------------------------------------------

 		/**
		* METHODE :  generer_frame_grille_eff_2(id_theme, langue):
		* Effectue la g�n�ration des templates � l'image
		* de celui des EFFECTIFS (04 mesures ) pr�sent� en un seul bloc
		* o� chaque ligne d'effectifs est suivie d'une ligne de redoublants
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/

    function generer_frame_grille_eff_2($id_theme, $langue, $id_systeme){
		
					$requete				="
									SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = ".$id_theme.
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
										AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
										
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

				//echo"$requete";
									
		// Les Locaux par exemple
				$affiche_eff 	= 0;
     		$NB_TOTAL_COL = 0;
        $NB_LIGNE_ECRAN	= $this->dico[0][NB_LIGNES_FRAME]; //Nombre de lignes � afficher
        $dico        = $this->tri_fils($this->dico);
				//echo "<pre>";
				//print_r($dico );
				//exit;

				$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
				$mess_alert			= addslashes($mess_alert);
				
				$html				  = "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html 	  	  .= "function Alert_Supp(checkbox){\n";
				$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
				$html					.= "if (eval(chaine_eval)){\n";
				$html 	  	  .= "alert ('$mess_alert');\n";
				$html				  .= "}\n";
				$html				  .= "}\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";
		
        $html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";					
		
        //$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
        //$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
        $html 			.= "<TABLE class='table-questionnaire' border='1'>" . '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>' . "\n";

        // Calcul du nombre de lignes de libell�s
        $nb_colspan_2 = 2;
				$nb_colspan_1 = 1;
				$tab_champ_val_multi = array();
				$tab_groupe = array();
				$tab_elem_zone_ref =array();
                $tabindex = array();
				foreach($dico as $element){
            if ( trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'systeme_liste_radio'){
            // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
                $nb_colspan_1       = 2;
            }  
	 					if ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
								$tab_champ_val_multi[$element['NOM_GROUPE']]['CHAMP_PERE'][] 		= $element['CHAMP_PERE'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	= $element['ATTRIB_OBJET'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['ID_ZONE'][]				= $element['ID_ZONE'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['AFFICHE_TOTAL'][]	= $element['AFFICHE_TOTAL'] ;
								//$nb_colspan_2++;
								if(!isset($id_zone_ref)){
										$id_zone_ref	=	$element['ID_ZONE_REF'] ;
										$requete				="
												SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
												FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
												WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
												AND			DICO_ZONE.ID_ZONE = ".$id_zone_ref.
												" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
												AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
												AND			DICO_ZONE.ID_THEME = ".$id_theme.
												" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
												ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
													
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
												if(!is_array($element_eff)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow

										$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
										$inc	=	-1;
										foreach($result_nomenc as $element_result_nomenc){
												$inc++;
												//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];	
												$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
												//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
										}
										$html.= "\t <script language='javascript' type='text/javascript'>\n";
										$html.= "\t <!--\n";
											$html.= "\t\t function Calcul_Total_Ligne(champ_op,ligne){\n";
												$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
												$html.= "\t\t\t var chaine_eval;\n";
											 	$html.= "\t\t\t var total = 0;\n";
											 $nb_code	=	-1;
											 foreach($tab_elem_zone_ref as $code){
											 		$nb_code++;
													$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = parseInt(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
													//$html.= "\t\t\t alert(chaine_eval);\n";
													$html.= "\t\t\t eval(chaine_eval);\n";
												} 
											 	$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
													$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
														$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
														$html.= "\t\t\t\t\t eval(chaine_eval);\n";
													$html.= "\t\t\t\t	}\n";
											 	$html.= "\t\t\t }\n";
											 	$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
											 	$html.= "\t\t\t eval(chaine_eval);\n";
										 	$html.= "\t\t }\n";
										$html.= "\t -->\n";
									  $html.= "\t </script>\n";
								}	
						}
						elseif( ($element['TYPE_OBJET'] == 'text') and ($element['NOM_GROUPE']) ){
								$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	= $element['ATTRIB_OBJET'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 				= $element['ID_ZONE'] ;
						}
        }
  			$key_tab_groupe 				= array_keys($tab_groupe);
				$nom_groupe_val_multi 	= array_keys($tab_champ_val_multi);
				//echo'nom_groupe_val_multi<pre>';
				//print_r($nom_groupe_val_multi);
				$NB_CHAMPS_MULTI		= $nb_colspan_2;
				$NB_TOTAL_COL		 		= $nb_colspan_1 + $nb_colspan_2*$NB_LIGNE_ECRAN;
				$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
				$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
				//echo'<pre>'; 
				//print_r($tab_groupe);
				// Affichage de la colonne contenant l'ic�ne ?????????Ade s�lection des �l�ments � supprimer
                // $tabindex = array(); $tabindex[$ligne] = $ligne * 1000 ; $tabindex[$ligne]++;  tabindex='".$tabindex[$ligne]."';
				$html 			.= "\t"."<TR>"."\n";
        $html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
        for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                        $tabindex[$ligne] = ($ligne * 1000) + 1;
						$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>"."<INPUT tabindex='".$tabindex[$ligne]."' NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
				}
				$html 			.= "\t".'</TR>'."\n";
        $ii = 0;
				foreach($dico as $element){

if(!isset($classe_fond)) {
    $classe_fond = 'ligne-paire';
} else {
    if($classe_fond == 'ligne-paire') {
        $classe_fond = 'ligne-impaire';
    } else {
        $classe_fond = 'ligne-paire';
    }
}

						if( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='popup') ){

									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT  ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\" readonly> ";
											}
                							else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\" readonly> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
                			$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
										 	$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='saisie') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
									//////
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\"
																			onBlur=SAISIE_".$element['CHAMP_PERE']."('".$element['CHAMP_INTERFACE']."_".$ligne."','".$element['CHAMP_PERE']."_".$ligne."');> ";
																			
											}
                							else{
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\"> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											//$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
                			//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
										 	$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
            elseif ($element['TYPE_OBJET'] == 'liste_radio' or trim($element['TYPE_OBJET']) == 'systeme_liste_radio'){

								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
								$html			.= "\t".'</TR>'."\n";
								$html 						.= "\t".'<TR>'."\n";
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
                				$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
								
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
                            //Pour chaque libell� de la nomenclature
                		if($pass > 0) $html.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                                $tabindex[$ligne]++;
												$html       .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
												$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
												$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
										}
										$html 					.= "\t".'</TR>'."\n";
										$pass ++;
                }
            }
						elseif ( $element['TYPE_OBJET'] == 'text' and !($element['NOM_GROUPE']) ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
											$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
                      $html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
		  								$html 	        .= "".$element['ATTRIB_OBJET']."></TD>\n";
										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'label'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
									$html 			.= "</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
											$html .= '$'.$element['CHAMP_PERE']."_".$ligne;
		  								$html .= "</TD>\n";
										
									}
									$html 			.= "\t</TR>\n";
								//break;
						}
						elseif ($element['TYPE_OBJET'] == 'combo' or trim($element['TYPE_OBJET']) == 'systeme_combo'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</b></TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
	                    $html		.= "<SELECT ".$element['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."'>\n";
											$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
											foreach($result_nomenc as $element_result_nomenc){
													//Un valeur du combo pour chaque valeur de la nomenclature 
													$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
													$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
											}
											$html 		.= "\t\t</SELECT></TD>\n";
										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ( $element['TYPE_OBJET'] == 'dimension_colonne' ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</b></TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."' ".$element['ATTRIB_OBJET'].">";
	                    					//Un champ cacher pr assurer le post avec un libelle afficher pour chaque valeur de la nomenclature
											$html .= "<INPUT TYPE='hidden' tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' ";
											$html .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."_".$result_nomenc[$ligne][$this->get_champ_extract($element['CHAMP_PERE'])]."\""; 
											$html .= " />\n".$result_nomenc[$ligne]['LIBELLE']."</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						//elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and (!$affiche_eff) ){
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and ($element['NOM_GROUPE']) ){
								//if ( $id_zone_ref and ($NB_CHAMPS_MULTI<=2) ){ // cas de G et/ou F sans les redoublants
								//$nom_groupe = $element['NOM_GROUPE'];
								if ( $id_zone_ref and !$affiche_eff ){ // cas de G et/ou F sans les redoublants
										//$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
										$requete				="
												SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
												FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
												WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
												AND			DICO_ZONE.ID_ZONE = ".$id_zone_ref.
												" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
												AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
												AND			DICO_ZONE.ID_THEME = ".$id_theme.
												" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
												ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
													
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
												if(!is_array($element_eff)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow

										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
				
										$html 			.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</TD>\n";
										//echo "<br>$requete<br>";
										//echo'<pre>';
										//print_r($tab_champ_val_multi);
										
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												//foreach($tab_champ_val_multi[$nom_groupe_val_multi[0]]'CHAMP_PERE'[] as $champ){
													//$entete1	= 	
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($tab_champ_val_multi[$nom_groupe_val_multi[0]]['ID_ZONE'][0],$langue)."</TD>\n";
													
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($tab_champ_val_multi[$nom_groupe_val_multi[0]]['ID_ZONE'][1],$langue)."</TD>\n";

												//}
										}
										$html 			.= "\t".'</TR>'."\n";
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										$pass = 0;
										$affiche_total = array();
										$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
										foreach($result_nomenc as $element_result_nomenc){		
												
												/// affichage de la ligne comme moins de 6ans				
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>".$element_result_nomenc['LIBELLE']."</TD>\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
														for($chps_multi=0; $chps_multi<2; $chps_multi++){
															$fonction_somme ='';
															if($tab_champ_val_multi[$nom_groupe_val_multi[0]]['AFFICHE_TOTAL'][$chps_multi]){
																	$affiche_total[$nom_groupe_val_multi[0]]	=	true;
																	$fonction_somme = " onBlur=Calcul_Total_Ligne"."('".$tab_champ_val_multi[$nom_groupe_val_multi[0]]['CHAMP_PERE'][$chps_multi]."',"."'$ligne'); ";
															}
                                                            $tabindex[$ligne]++;
															$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
															$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_champ_val_multi[$nom_groupe_val_multi[0]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$tab_champ_val_multi[$nom_groupe_val_multi[0]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
															$html .= " VALUE=\"$".$tab_champ_val_multi[$nom_groupe_val_multi[0]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
															$html .= "".$tab_champ_val_multi[$nom_groupe_val_multi[0]]['ATTRIB_OBJET'][$chps_multi]."$fonction_somme></TD>\n";
														}
												}
												$html 			.= "\t".'</TR>'."\n";
												
												/// affichage de la ligne comme dont_redoublants				
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>".$this->recherche_libelle_page('dt_red',$langue,'questionnaire.php')."</TD>\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
														for($chps_multi=0;$chps_multi<2;$chps_multi++){
															$fonction_somme ='';
															if($tab_champ_val_multi[$nom_groupe_val_multi[1]]['AFFICHE_TOTAL'][$chps_multi]){
																	$affiche_total[$nom_groupe_val_multi[1]]	=	true;
																	$fonction_somme = " onBlur=Calcul_Total_Ligne"."('".$tab_champ_val_multi[$nom_groupe_val_multi[1]]['CHAMP_PERE'][$chps_multi]."',"."'$ligne'); ";
															}
														    $tabindex[$ligne]++;
															$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
															$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_champ_val_multi[$nom_groupe_val_multi[1]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$tab_champ_val_multi[$nom_groupe_val_multi[1]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
															$html .= " VALUE=\"$".$tab_champ_val_multi[$nom_groupe_val_multi[1]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
															$html .= "".$tab_champ_val_multi[$nom_groupe_val_multi[1]]['ATTRIB_OBJET'][$chps_multi]."$fonction_somme></TD>\n";
														}
												}
												$html 			.= "\t".'</TR>'."\n";
												
										}
										//echo'<pre>';
										//print_r($affiche_total);
										$pass1 = -1 ;
										if(count($affiche_total)){
												foreach($affiche_total as $nom_groupe=>$value){ // affichage totaux eff et resoublants
														$pass1++;
														$html			.= "\t".'<TR>'."\n";		
														$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
														$html			.= "\t".'</TR>'."\n";
														
														if($pass1==0) {
															$code_lib = 'total';
															$class_td_1 = "class='td_center ligne-impaire police_gras'";
															$class_td_2 = "class='td_center ligne-impaire police_gras'";
															$class_tr = "class='ligne-impaire'";
														}
														elseif($pass1==1) {
															$code_lib = 'dt_red';
															$class_td_1 = "class='td_right ligne-impaire police_gras'";
															$class_td_2 = "class='td_center ligne-impaire police_gras'";
															$class_tr = "class='ligne-impaire'";
														}
														else{
															$code_lib = '0';
															$class_td = '';
														}

														$html			.= "\t"."<TR $class_tr>"."\n";
														/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
														else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
														$jj++;*/
														$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap $class_td_1>"; 
														$html 		.= $this->recherche_libelle_page($code_lib,$langue,'questionnaire.php')."</TD>\n";
														for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
																for($ordre=0; $ordre<2; $ordre++){
                                                                        $tabindex[$ligne]++;
																		if($tab_champ_val_multi[$nom_groupe]['AFFICHE_TOTAL'][$ordre]){
																				$html .= "\t\t<TD $class_td_2 nowrap>";
																				$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$tab_champ_val_multi[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."' TYPE='text' readonly";
																				$html .= " VALUE='' $class_td_2 "; 
																				$html .= "".$tab_champ_val_multi[$nom_groupe]['ATTRIB_OBJET'][$ordre]."></TD>\n";
																		}
																		else{
																				$html .= "\t\t<TD class='td_center' nowrap>&nbsp;</TD>\n";
																		}
																}
														}
														$html 			.= "\t".'</TR>'."\n";
												}
												$html.= "\t <script language='javascript' type='text/javascript'>\n";
												$html.= "\t <!--\n";
												foreach($affiche_total as $nom_groupe=>$value){ 
														for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
																for($ordre=0; $ordre<2; $ordre++){
																		if($tab_champ_val_multi[$nom_groupe]['AFFICHE_TOTAL'][$ordre]){
																				$html.= "\t Calcul_Total_Ligne('".$tab_champ_val_multi[$nom_groupe]['CHAMP_PERE'][$ordre]."','$ligne');\n";
																		}
																}
														}
												}
												$html.= "\t -->\n";
												$html.= "\t </script>\n";
										}
								}
								$affiche_eff = 1;
						}
						elseif ($element['TYPE_OBJET'] == 'text' and ($element['NOM_GROUPE']) ){
								$jj = 0;
								$nom_groupe = $element['NOM_GROUPE'];
								if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
										$nom_groupe_tot_affiche[]	=	$nom_groupe;
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='td_space_gris'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										
										$html			.= "\t".'<TR>'."\n";
										/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
										else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
										$jj++;*/
										$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
										$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												for($ordre=0; $ordre<2; $ordre++){
                                                    $tabindex[$ligne]++;
													$html .= "\t\t<TD class='td_center' nowrap>";
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."'".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne .'\' '." NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."' TYPE='text' ";
													$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."\""; 
													$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]."></TD>\n";
												}
										}
										$html 			.= "\t".'</TR>'."\n";
								}
						}elseif($element['TYPE_OBJET'] == 'booleen' and !($element['NOM_GROUPE']) ){
                            $result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue);
                            $rowspan		 			= count($result_nomenc);
                            $html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
                            $html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
                            $pass = 0;
                            foreach($result_nomenc as $element_result_nomenc){		
                                //Pour chaque libell� de la nomenclature
                                if($pass > 0) $html.= "\t".'<TR>'."\n";		
                                $html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
                                for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                    $tabindex[$ligne]++;
                                    $html       .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
                                    $html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
                                    $html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
                                }
                                $html 					.= "\t".'</TR>'."\n";
                                $pass ++;
                            }
                            //echo 'test';
                            //exit;
                        }elseif($element['TYPE_OBJET'] == 'checkbox' and !($element['NOM_GROUPE']) ){
                            $html			.= "\t".'<TR>'."\n";		
                            $html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
                            $html			.= "\t".'</TR>'."\n";

                            $html 			.= "\t".'<TR>'."\n";
                            $html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
                            $html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
                            for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                $tabindex[$ligne]++;
                                $html.= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
                                $html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='checkbox' ";
                                $html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne; 
                                $html 	        .= $element['ATTRIB_OBJET']."></TD>\n";
                            }
                            $html 			.= "\t".'</TR>'."\n";
                        }
				}// fin parcours des �l�ments de dico
				//// generation des lignes comme total ou nbre redoublants
				$html		.= "</TABLE>";

		$html		.= "\n" . $this->get_html_buttons($langue);
		
        $html 	.= "</FORM>";
		$html	.= "</div>"."\n";

				$html		  .= "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html         .= "document.form1.elements[0].focus();\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";

					//print '<BR>'.$element['FRAME'];
				if (trim($element['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
						echo $html;
				}else{
						if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
				}
				//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
				//echo $html; 
    }//Fin function generer_frame_grille_ligne
		

//----------------------------------------------------------------------
//----------------------------------------------------------------------
 		/**
		* METHODE :  generer_frame_grille_eff_3(id_theme, langue):
		* Effectue la g�n�ration des templates � l'image
		* de celui des EFFECTIFS (04 mesures ) pr�sent� deux blocs
		* o� le bloc des effectifs est suivi de celui des redoublants
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/

    function generer_frame_grille_eff_3($id_theme, $langue ,$id_systeme){
		
					$requete				="
									SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = ".$id_theme.
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
										AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme' 
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
										
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete);
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

				//echo"$requete";
									
		// Les Locaux par exemple
				$affiche_eff = 0;
     		$NB_TOTAL_COL = 0;
        $NB_LIGNE_ECRAN	= $this->dico[0][NB_LIGNES_FRAME]; //Nombre de lignes � afficher
        $dico        = $this->tri_fils($this->dico);
				//echo "<pre>";
				//print_r($dico );
				//exit;

				$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
				$mess_alert			= addslashes($mess_alert);
				
				$html				  = "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html 	  	  .= "function Alert_Supp(checkbox){\n";
				$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
				$html					.= "if (eval(chaine_eval)){\n";
				$html 	  	  .= "alert ('$mess_alert');\n";
				$html				  .= "}\n";
				$html				  .= "}\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";
				
        $html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";	
        //$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
        //$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
        $html 			.= "<TABLE class='table-questionnaire' border='1'>" . '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>' . "\n";

        // Calcul du nombre de lignes de libell�s
        $nb_colspan_2 = 2;
				$nb_colspan_1 = 1;
				$tab_champ_val_multi = array();
				$tab_groupe = array();
				$tab_elem_zone_ref =array();
                $tabindex = array();
				foreach($dico as $element){
            if (trim($element['TYPE_OBJET']) == 'liste_radio' or trim($element['TYPE_OBJET']) == 'systeme_liste_radio'){
            // S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
                $nb_colspan_1       = 2;
            }  
	 					if ($element['TYPE_OBJET'] == 'valeur_multiple'){ 
								$tab_champ_val_multi[$element['NOM_GROUPE']]['CHAMP_PERE'][] 		= $element['CHAMP_PERE'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	= $element['ATTRIB_OBJET'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['ID_ZONE'][]				= $element['ID_ZONE'] ;
								$tab_champ_val_multi[$element['NOM_GROUPE']]['AFFICHE_TOTAL'][]	= $element['AFFICHE_TOTAL'] ;
								//$nb_colspan_2++;
								if(!isset($id_zone_ref)){
										$id_zone_ref	=	$element['ID_ZONE_REF'] ;
										$requete				="
												SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
												FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
												WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
												AND			DICO_ZONE.ID_ZONE = ".$id_zone_ref.
												" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
												AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
												AND			DICO_ZONE.ID_THEME = ".$id_theme.
												" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
												ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
													
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
												if(!is_array($element_eff)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow

										$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
										$inc	=	-1;
										foreach($result_nomenc as $element_result_nomenc){
												$inc++;
												//$tab_elem_zone_ref[$inc]['ID_ZONE']	= $element_result_nomenc[$element_eff[0]['ID_ZONE']];	
												$tab_elem_zone_ref[$inc]['CODE'] 		= $element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])];
												//$tab_elem_zone_ref[$inc]['LIBELLE'] = $element_result_nomenc['LIBELLE'];
										}
										$html.= "\t <script language='javascript' type='text/javascript'>\n";
										$html.= "\t <!--\n";
											$html.= "\t\t function Calcul_Total_Ligne(champ_op,ligne){\n";
												$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
												$html.= "\t\t\t var chaine_eval;\n";
											 	$html.= "\t\t\t var total = 0;\n";
											 $nb_code	=	-1;
											 foreach($tab_elem_zone_ref as $code){
											 		$nb_code++;
													$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = parseInt(document.form1.'+champ_op+'_'+ligne+'_".$code['CODE'].".value);';\n";
													//$html.= "\t\t\t alert(chaine_eval);\n";
													$html.= "\t\t\t eval(chaine_eval);\n";
												} 
											 	$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
													$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
														$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
														$html.= "\t\t\t\t\t eval(chaine_eval);\n";
													$html.= "\t\t\t\t	}\n";
											 	$html.= "\t\t\t }\n";
											 	$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ_op+'_'+ligne+'.value='+total+';';\n";
											 	$html.= "\t\t\t eval(chaine_eval);\n";
										 	$html.= "\t\t }\n";
										$html.= "\t -->\n";
									  $html.= "\t </script>\n";
								}	
						}
						elseif( ($element['TYPE_OBJET'] == 'text') and ($element['NOM_GROUPE']) ){
								$tab_groupe[$element['NOM_GROUPE']]['CHAMP_PERE'][]		 	= $element['CHAMP_PERE'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ATTRIB_OBJET'][] 	= $element['ATTRIB_OBJET'] ;
								$tab_groupe[$element['NOM_GROUPE']]['ID_ZONE'][] 				= $element['ID_ZONE'] ;
						}
        }
  			$key_tab_groupe 				= array_keys($tab_groupe);
				$nom_groupe_val_multi 	= array_keys($tab_champ_val_multi);
				//echo'nom_groupe_val_multi<pre>';
				//print_r($nom_groupe_val_multi);
				$NB_CHAMPS_MULTI		= $nb_colspan_2;
				$NB_TOTAL_COL		 		= $nb_colspan_1 + $nb_colspan_2*$NB_LIGNE_ECRAN;
				$nom_groupe_tot_affiche = array(); // pour regrouper le totaux selon le nom_groupe
				$nom_groupe_val_multi_affiche  = array(); // pour regrouper les champs de type valeur_multiple suivant le nom_groupe								
				//echo'<pre>'; 
				//print_r($tab_groupe);
				// Affichage de la colonne contenant l'ic�ne ?????????Ade s�lection des �l�ments � supprimer
				$html 			.= "\t"."<TR>"."\n";
        $html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='td_center ligne-titre'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";
        for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                        $tabindex[$ligne] = ($ligne * 1000) + 1;
						$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='td_center'>"."<INPUT tabindex='".$tabindex[$ligne]."' NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
				}
				$html 			.= "\t".'</TR>'."\n";
        $ii = 0;
				$ordre_eff =	-1;
				foreach($dico as $element){

if(!isset($classe_fond)) {
    $classe_fond = 'ligne-paire';
} else {
    if($classe_fond == 'ligne-paire') {
        $classe_fond = 'ligne-impaire';
    } else {
        $classe_fond = 'ligne-paire';
    }
}

						if( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='popup') ){

									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\" readonly> ";
											}
                							else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\" readonly> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
                			$html .= "<input tabindex='".$tabindex[$ligne]."' type='button' onClick=".$fonction."; value='...'>";
										 	$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif( ($element['TYPE_OBJET'] == 'liste_radio') and ($element['BOUTON_INTERFACE']=='saisie') ){
									//echo"<br>ICI<br>";
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";
									/////// FONCTION DE TRAITEMENT DU CHAMP DE SAISIE POUR LE CHAMP ASSOCIE
									//////
									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' nowrap class='".$classe_fond."'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT  tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\"
																			onBlur=SAISIE_".$element['CHAMP_PERE']."('".$element['CHAMP_INTERFACE']."_".$ligne."','".$element['CHAMP_PERE']."_".$ligne."');> ";
																			
											}
                							else{
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\"> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											//$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
                			//$html .= "<input type='button' onClick=".$fonction."; value='...'>";
										 	$html .= "\t\t</TD>\n";
									}
									$html 			.= "\t".'</TR>'."\n";
						}
            elseif ($element['TYPE_OBJET'] == 'liste_radio' or trim($element['TYPE_OBJET']) == 'systeme_liste_radio'){

								$html			.= "\t".'<TR>'."\n";		
								$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";

								$html			.= "\t".'</TR>'."\n";
								$html 						.= "\t".'<TR>'."\n";
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
                				$rowspan		 			= count($result_nomenc);
								$html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
								$html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
								
								$pass = 0;
								foreach($result_nomenc as $element_result_nomenc){		
                            //Pour chaque libell� de la nomenclature
                		if($pass > 0) $html.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                                $tabindex[$ligne]++;
												$html       .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
												$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
												$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
										}
										$html 					.= "\t".'</TR>'."\n";
										$pass ++;
                }
            }
						
						elseif ( $element['TYPE_OBJET'] == 'text' and !($element['NOM_GROUPE']) ){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
											$html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
                      $html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
		  								$html 	        .= "".$element['ATTRIB_OBJET']."></TD>\n";
										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'label'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t"."<TR CLASS='ligne-impaire'>"."\n";
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
									$html 			.= "</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
											$html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
											$html .= '$'.$element['CHAMP_PERE']."_".$ligne;
		  								$html .= "</TD>\n";
										
									}
									$html 			.= "\t</TR>\n";
								//break;
						}
						elseif ($element['TYPE_OBJET'] == 'combo' or trim($element['TYPE_OBJET']) == 'systeme_combo'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap><b>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</b></TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
	                    $html		.= "<SELECT  ".$element['ATTRIB_OBJET']." tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."'>\n";
											$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
											foreach($result_nomenc as $element_result_nomenc){
													//Un valeur du combo pour chaque valeur de la nomenclature 
													$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
													$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
											}
											$html 		.= "\t\t</SELECT></TD>\n";										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						elseif ($element['TYPE_OBJET'] == 'dimension_colonne'){
									$html			.= "\t".'<TR>'."\n";		
									$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
									$html			.= "\t".'</TR>'."\n";

									$html 			.= "\t".'<TR>'."\n";
									$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
									$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
									$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
									for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                            $tabindex[$ligne]++;
											$html 	.= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."' ".$element['ATTRIB_OBJET'].">";
	                    					//Un champ cacher pr assurer le post avec un libelle afficher pour chaque valeur de la nomenclature
											$html .= "<INPUT TYPE='hidden' tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' ";
											$html .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."_".$result_nomenc[$ligne][$this->get_champ_extract($element['CHAMP_PERE'])]."\""; 
											$html .= " />\n".$result_nomenc[$ligne]['LIBELLE']."</TD>\n";										
									}
									$html 			.= "\t".'</TR>'."\n";
						}
						//elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and (!$affiche_eff) ){
						elseif ($element['TYPE_OBJET'] == 'valeur_multiple' and ($element['NOM_GROUPE']) ){
								//if ( $id_zone_ref and ($NB_CHAMPS_MULTI<=2) ){ // cas de G et/ou F sans les redoublants
								$nom_groupe = $element['NOM_GROUPE'];
								if ( $id_zone_ref and !in_array($nom_groupe,$nom_groupe_val_multi_affiche) ){ // cas de G et/ou F sans les redoublants
										$nom_groupe_val_multi_affiche[]	=	$nom_groupe;
										$ordre_eff++;
										$requete				="
												SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
												FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
												WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
												AND			DICO_ZONE.ID_ZONE = ".$id_zone_ref.
												" AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
												AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
												AND			DICO_ZONE.ID_THEME = ".$id_theme.
												" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
												" AND		DICO_ZONE_SYSTEME.ACTIVER = 1
												ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE";
												
												//echo $requete;	
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$element_eff	= $GLOBALS['conn_dico']->GetAll($requete); 
												if(!is_array($element_eff)){                    
														throw new Exception('ERR_SQL');  
												} 
																					
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow
										//echo '<br><pre>';
										//print_r($element_eff);
 
	
										if($ordre_eff ==1){
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'>&nbsp;</TD>\n";
												$html			.= "\t".'</TR>'."\n";
												
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'>";
												$html     .= $this->recherche_libelle_page('dt_red',$langue,'questionnaire.php')."</TD>\n";
												$html			.= "\t".'</TR>'."\n";
										}
										else{
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
												$html			.= "\t".'</TR>'."\n";
										}
										$html 			.= "\t"."<TR class='ligne-impaire' nowrap>"."\n";
										$html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."' nowrap>";
										$html       .= $this->recherche_libelle_zone($element_eff[0]['ID_ZONE'],$langue)."</TD>\n";
										//echo "<br>$requete<br>";
										//echo'<pre>';
										//print_r($tab_champ_val_multi);
										
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												//foreach($tab_champ_val_multi[$nom_groupe_val_multi[0]]'CHAMP_PERE'[] as $champ){
													//$entete1	= 	
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($tab_champ_val_multi[$nom_groupe_val_multi[0]]['ID_ZONE'][0],$langue)."</TD>\n";
													
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
													$html .= $this->recherche_libelle_zone($tab_champ_val_multi[$nom_groupe_val_multi[0]]['ID_ZONE'][1],$langue)."</TD>\n";
												//}
										}
										$html 			.= "\t".'</TR>'."\n";
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										$pass = 0;
										$affiche_total = array();
										$result_nomenc 		= $this->requete_nomenclature($element_eff[0]['TABLE_FILLE'],$element_eff[0]['CHAMP_FILS'], $element_eff[0]['CHAMP_PERE'], $langue ,$id_systeme, $element_eff[0]['SQL_REQ']);							
										//echo '<br><pre>';
										///print_r($element_eff);
										foreach($result_nomenc as $element_result_nomenc){		
												
												/// affichage de la ligne comme moins de 6ans				
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>".$element_result_nomenc['LIBELLE']."</TD>\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
														for($chps_multi=0; $chps_multi<2; $chps_multi++){
                                                                $tabindex[$ligne]++;
																$fonction_somme ='';
																if($tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['AFFICHE_TOTAL'][$chps_multi]){
																		$affiche_total[$nom_groupe_val_multi[$ordre_eff]]	=	true;
																		$fonction_somme = " onBlur=Calcul_Total_Ligne"."('".$tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['CHAMP_PERE'][$chps_multi]."',"."'$ligne'); ";
																}
	
																$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
																$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])] .'\' '."NAME='".$tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."' TYPE='text' ";
																$html .= " VALUE=\"$".$tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['CHAMP_PERE'][$chps_multi]."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element_eff[0]['CHAMP_PERE'])]."\""; 
																$html .= "".$tab_champ_val_multi[$nom_groupe_val_multi[$ordre_eff]]['ATTRIB_OBJET'][$chps_multi]."$fonction_somme></TD>\n";
														}
												}
												$html 			.= "\t".'</TR>'."\n";
										}
										if($affiche_total[$nom_groupe_val_multi[$ordre_eff]]	==	true){
										
												$html			.= "\t".'<TR>'."\n";		
												$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
												$html			.= "\t".'</TR>'."\n";

												$html			.= "\t"."<TR class='ligne-impaire'>"."\n";
												$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'><b>"; 
												$html 		.= $this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
														for($chps_multi=0; $chps_multi<2; $chps_multi++){
                                                                $tabindex[$ligne]++;
																if($tab_champ_val_multi[$nom_groupe]['AFFICHE_TOTAL'][$chps_multi]){
																		$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
																		$html .= "<INPUT tabindex='".$tabindex[$ligne]."' NAME='TOTAL_".$tab_champ_val_multi[$nom_groupe]['CHAMP_PERE'][$chps_multi]."_".$ligne."' TYPE='text' readonly";
																		$html .= " VALUE='' class='ligne-impaire police_gras'"; 
																		$html .= "".$tab_champ_val_multi[$nom_groupe]['ATTRIB_OBJET'][$chps_multi]."></TD>\n";
																}
																else{
																		$html .= "\t\t<TD class='".$classe_fond."'>&nbsp;</TD>\n";
																}
														}
												}
												$html 			.= "\t".'</TR>'."\n";
												$html.= "\t <script language='javascript' type='text/javascript'>\n";
												$html.= "\t <!--\n";
												for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
													for($ordre=0; $ordre<2; $ordre++){
															if($tab_champ_val_multi[$nom_groupe]['AFFICHE_TOTAL'][$ordre]){
																	$html.= "\t Calcul_Total_Ligne('".$tab_champ_val_multi[$nom_groupe]['CHAMP_PERE'][$ordre]."','$ligne');\n";
															}
													}
												}
												$html.= "\t -->\n";
												$html.= "\t </script>\n";
										}
										// affiche totaux
								}
									$affiche_eff = 1;
						}
						elseif ($element['TYPE_OBJET'] == 'text' and ($element['NOM_GROUPE']) ){
								$jj = 0;
								$nom_groupe = $element['NOM_GROUPE'];
								if( !in_array($nom_groupe,$nom_groupe_tot_affiche) ){
										$nom_groupe_tot_affiche[]	=	$nom_groupe;
										$html			.= "\t".'<TR>'."\n";		
										$html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
										$html			.= "\t".'</TR>'."\n";
										
										$html			.= "\t".'<TR>'."\n";
										/*if (($jj % 2) == 0) $html .= "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center ligne-impaire'>"; 
										else $html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='td_center fond_gris'>"; 
										$jj++;*/
										$html .=  "\t\t"."<TD COLSPAN='$nb_colspan_1' nowrap class='".$classe_fond."'>"; 
										$html 		.= $this->recherche_libelle_zone($tab_groupe[$nom_groupe]['ID_ZONE'][0],$langue)."</TD>\n";
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												for($ordre=0; $ordre<2; $ordre++){
                                                    $tabindex[$ligne]++;
													$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
													$html .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne .'\' '."NAME='".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."' TYPE='text' ";
													$html .= " VALUE=\"$".$tab_groupe[$nom_groupe]['CHAMP_PERE'][$ordre]."_".$ligne."\""; 
													$html .= "".$tab_groupe[$nom_groupe]['ATTRIB_OBJET'][$ordre]."></TD>\n";
												}
										}
										$html 			.= "\t".'</TR>'."\n";
								}
						}elseif($element['TYPE_OBJET'] == 'booleen' and !($element['NOM_GROUPE']) ){
                            $result_nomenc 		= $this->requete_nomenclature_bool($element['CHAMP_PERE'], $langue );
                            $rowspan		 			= count($result_nomenc);
                            $html 						.= "\t\t"."<TD rowspan='$rowspan'  class='".$classe_fond."'>";
                            $html             .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
                            $pass = 0;
                            foreach($result_nomenc as $element_result_nomenc){		
                                //Pour chaque libell� de la nomenclature
                                if($pass > 0) $html.= "\t".'<TR>'."\n";		
                                $html 		.="\t\t"."<TD nowrap  class='".$classe_fond."'>". $element_result_nomenc['LIBELLE']."</TD>\n";
                                for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                    $tabindex[$ligne]++;
                                    $html       .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'><INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
                                    $html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
                                    $html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
                                }
                                $html 					.= "\t".'</TR>'."\n";
                                $pass ++;
                            }
                            //echo 'test';
                            //exit;
                        }elseif($element['TYPE_OBJET'] == 'checkbox' and !($element['NOM_GROUPE']) ){
                            $html			.= "\t".'<TR>'."\n";		
                            $html 		.="\t\t"."<TD COLSPAN='$NB_TOTAL_COL' class='".$classe_fond."'></TD>\n";
                            $html			.= "\t".'</TR>'."\n";

                            $html 			.= "\t".'<TR>'."\n";
                            $html 			.= "\t\t<TD COLSPAN='$nb_colspan_1' class='".$classe_fond."'>";
                            $html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
                            for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
                                $tabindex[$ligne]++;
                                $html .= "\t\t<TD COLSPAN='$nb_colspan_2' class='".$classe_fond."'>";
                                $html 	        .= "<INPUT tabindex='".$tabindex[$ligne]."' ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='checkbox' ";
                                $html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne; 
                                $html 	        .= $element['ATTRIB_OBJET']."></TD>\n";
                            }
                            $html 			.= "\t".'</TR>'."\n";
                        }
				}// fin parcours des �l�ments de dico
				//// generation des lignes comme total ou nbre redoublants
				$html		.= "</TABLE>";

		$html		.= "\n" . $this->get_html_buttons($langue);
		
        $html 	.= "</FORM>";
		$html	.= "</div>"."\n";

				$html		  .= "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html         .= "document.form1.elements[0].focus();\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";

					//print '<BR>'.$element['FRAME'];
				if (trim($element['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
						echo $html;
				}else{
						if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
				}
				//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
				//echo $html; 
    }//Fin function generer_frame_grille_ligne
	
//----------------------------------------------------------------------
//----------------------------------------------------------------------
 		/**
		* METHODE :  generer_frame_mat_grille(id_theme, langue):
		* Effectue la g�n�ration des templates comme
		* les AIRES DE RECRUTEMENT, ORIGINE DES NOUVEAUX ELEVES  
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* @access public
		* @param numeric $id_theme : le th�me choisi
		* @param string $langue		: la langue associ�e
		* 
		*/
   function generer_frame_mat_grille($id_theme, $langue ,$id_systeme){
		
					$requete				="
									SELECT	DICO_THEME.*, DICO_THEME_SYSTEME.*, DICO_ZONE.*, DICO_ZONE_SYSTEME.*
									FROM		DICO_ZONE , DICO_ZONE_SYSTEME , DICO_THEME , DICO_THEME_SYSTEME
									WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
									AND			DICO_ZONE.ID_THEME = DICO_THEME.ID				
									AND			DICO_THEME.ID = DICO_THEME_SYSTEME.ID
									AND			DICO_ZONE.ID_THEME = ".$id_theme.
									" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_THEME_SYSTEME.ID_SYSTEME = ".$id_systeme.
									" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
										AND		DICO_ZONE_SYSTEME.TYPE_OBJET <> 'systeme'
										ORDER BY DICO_ZONE_SYSTEME.ORDRE_AFFICHAGE ";
										
				
				// Traitement Erreur Cas : GetAll / GetRow
				try {
						$aresult	= $GLOBALS['conn_dico']->GetAll($requete); 
						if(!is_array($aresult)){                    
								throw new Exception('ERR_SQL');  
						} 
						$this->dico	= $aresult;									
				}
				catch(Exception $e){
						$erreur = new erreur_manager($e,$requete);
				}
				// Fin Traitement Erreur Cas : GetAll / GetRow

				//echo "<br>$requete<br>";					
				// Les Locaux par exemple
     		$NB_TOTAL_COL = 0;
        $NB_LIGNE_ECRAN	= $this->dico[0][NB_LIGNES_FRAME]; //Nombre de lignes � afficher
        $dico        = $this->tri_fils($this->dico);
				//echo "<pre>";
				//print_r($dico );
				//exit;

				$mess_alert		 	= $this->recherche_libelle(110,$langue,'DICO_MESSAGE');
				$mess_alert			= addslashes($mess_alert);
				
				$html				  = "<script language='javascript' type='text/javascript'>\n";
				$html 	  	  .= "<!--\n";
				$html 	  	  .= "function Alert_Supp(checkbox){\n";
				$html				  .= "var chaine_eval ='document.form1.'+checkbox+'.checked == true';\n";
				$html					.= "if (eval(chaine_eval)){\n";
				$html 	  	  .= "alert ('$mess_alert');\n";
				$html				  .= "}\n";
				$html				  .= "}\n";
				$html 	  	  .= "//-->\n";
				$html 	  	  .= "</script>\n";
		
        $html 	  	 .= "<BR/>"; 
		
		$html 			.= $this->js_Post_Form($id_theme, $id_systeme)."\n";	
        //$html 			.= "<link href='../css/formulaire_senegal.css' rel='stylesheet' type='text/css'>"."\n"; 
        //$html 			.= "<style type='text/css' media='screen'>@import '../css/formulaire_senegal.css';</style>"."\n";
		$html 			.= "<div align=center>"."\n";
		$html 			.= "<FORM ACTION='questionnaire.php?theme_frame=".$id_theme.$id_systeme."' METHOD='POST' NAME='form1'>"."\n";
        $html 			.= "<TABLE class='table-questionnaire' border='1'>" . '<CAPTION><B>' . $this->libelle_long . '</B></CAPTION>' . "\n";

        // Calcul du nombre de lignes de libell�s
        $nb_colspan 		= 0;
				$nb_rowspan_1 	= 2;
				$nb_rowspan_2 	= 1;
				$tab_champ_val_multi = array();
				foreach($dico as $element){
            			if (trim($element['TYPE_OBJET']) == 'text_valeur_multiple'){
								// S'il s'agit d'une liste_radio, on a besoin de deux lignes (1 hor. et 1 vert.)
								$tab_champ_val_multi[$nb_colspan]['CHAMP_PERE'] 	= $element['CHAMP_PERE'] ;
								$tab_champ_val_multi[$nb_colspan]['ATTRIB_OBJET'] = $element['ATTRIB_OBJET'] ;
								$tab_champ_val_multi[$nb_colspan]['ID_ZONE'] 			= $element['ID_ZONE'] ;
								
								$nb_colspan++;
						}
						if (trim($element['TYPE_OBJET']) == 'hidden_field'){
								$tab_champ_val_multi[$nb_colspan]['CHAMP_PERE'] 	= $element['CHAMP_PERE'] ;
								$tab_champ_val_multi[$nb_colspan]['TYPE_OBJET'] = 'hidden_field' ;
								$tab_champ_val_multi[$nb_colspan]['ID_ZONE'] 			= $element['ID_ZONE'] ;
								
								$nb_colspan++;
						}
        		}
				//echo"<br>-->nb_colspan=$nb_colspan";
				if( $nb_colspan >1 ){
						$nb_rowspan_1++;
						$nb_rowspan_2++;
				}

				if(!isset($classe_fond)) {
						$classe_fond = 'ligne-impaire';
				} else {
						if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
						} else {
								$classe_fond = 'ligne-paire';
						}
				}

				/// 1er TR
				$affiche_totaux_champ_text = array();
  			$html 			.= "\t"."<TR>"."\n";
        $html 			.= "\t\t<TD ROWSPAN='$nb_rowspan_1' class='".$classe_fond."'><img src='".$GLOBALS['SISED_URL_IMG']."b_drop.png' width='16' height='16'></TD>";				
				foreach($dico as $element){
            if ( (trim($element['TYPE_OBJET']) == 'systeme_valeur_unique') or (trim($element['TYPE_OBJET']) == 'combo') or (trim($element['TYPE_OBJET']) == 'systeme_combo') ){
								$html 	.= "\t\t<TD ROWSPAN='$nb_rowspan_1' class='".$classe_fond."'>";
								// Modification Bass
                
                if($element['TAILLE_LIB_VERTICAUX'] > 0){
					//if( $element['TAILLE_LIB_VERTICAUX'] ) $taille_max = $element['TAILLE_LIB_VERTICAUX']; 
					//else $taille_max =   20;
					$taille_max = $element['TAILLE_LIB_VERTICAUX'];
	
					$libelle_afficher   =   $this->limiter_taille_text($this->recherche_libelle_zone($element['ID_ZONE'],$langue),$taille_max);
					$libelle_afficher.='+';
					$html                       .= "<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
                }else{
					$html                       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
				}
				// Fin Modification Bass

								//$html   .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
								$html   .= "</TD>\n";
						}
						elseif ( trim($element['TYPE_OBJET']) == 'systeme_liste_radio' 
											or trim($element['TYPE_OBJET']) == 'liste_radio' 
											or trim($element['TYPE_OBJET']) == 'booleen'){
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
                				$colspan		 = count($result_nomenc);
								$html 	.= "\t\t<TD COLSPAN='$colspan' class='".$classe_fond."'>";
								$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
						}
						elseif ( trim($element['TYPE_OBJET']) == 'systeme_valeur_multiple' ){
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
                				$colspan		 = count($result_nomenc)*$nb_colspan;
								$html 			.= "\t\t<TD COLSPAN='$colspan' class='".$classe_fond."'>";
								$html       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue)."</TD>\n";
								$affiche_totaux_val_multi = $element['AFFICHE_TOTAL'];
						}
						elseif ( trim($element['TYPE_OBJET']) == 'text' or trim($element['TYPE_OBJET']) == 'checkbox'  or $element['TYPE_OBJET'] == 'systeme_text'){
								$html 	.= "\t\t<TD ROWSPAN='$nb_rowspan_1' class='".$classe_fond."'>";
								//$html   .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
								///
							// Modification Bass
                
				if( $element['TAILLE_LIB_VERTICAUX'] > 0){
					//if( $element['TAILLE_LIB_VERTICAUX'] ) $taille_max = $element['TAILLE_LIB_VERTICAUX']; 
					//else $taille_max =   20;
					$taille_max = $element['TAILLE_LIB_VERTICAUX'];
	
					$libelle_afficher   =   $this->limiter_taille_text($this->recherche_libelle_zone($element['ID_ZONE'],$langue),$taille_max);
					$libelle_afficher.='+';
                	$html                       .= "<img src='" . $GLOBALS['SISED_URL_IMG'] . "text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."'>";
				}else{
					$html                       .= $this->recherche_libelle_zone($element['ID_ZONE'],$langue);
				}
                // Fin Modification Bass
								$html   .= "</TD>\n";
								///
								$affiche_totaux_champ_text[$element['CHAMP_PERE']] = $element['AFFICHE_TOTAL'];
								if($element['AFFICHE_TOTAL'])
										$bool_affiche_totaux_champ_text = true ;
										//echo "<br>BON";
						}

        }
				//echo'<pre>';
				//print_r($affiche_totaux_champ_text);
				if($affiche_totaux_val_multi){
						$html 	.= "\t\t<TD ROWSPAN='$nb_rowspan_1' class='".$classe_fond."'><b>";
						$html   .= $this->recherche_libelle_page('total',$langue,'questionnaire.php')."</b></TD>\n";
				}
				$html 			.= "\t".'</TR>'."\n";
				
				/// 2�me TR
  			$html 			.= "\t"."<TR  class='ligne-impaire'>"."\n";
				foreach($dico as $element){
						if ( trim($element['TYPE_OBJET']) == 'systeme_liste_radio' 
											or trim($element['TYPE_OBJET']) == 'liste_radio' 
											or trim($element['TYPE_OBJET']) == 'booleen'){
                            // Lecture des libell�s de la table de nomenclature
                            $result_nomenc 	= $this->requete_nomenclature($element['TABLE_FILLE'], $element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
                            //print_r($result_nomenc);
                            //print '<BR><BR>';
                            foreach($result_nomenc as $element_result_nomenc){		
                                    //Pour chaque libell� de la nomenclature
                                
                                // Modification Alassane
                             	if($element['TAILLE_LIB_VERTICAUX'] > 0){
									//if(isset($element['TAILLE_LIB_VERTICAUX'])) $taille_max = $element['TAILLE_LIB_VERTICAUX']; 
									//else $taille_max =   30;
									$taille_max = $element['TAILLE_LIB_VERTICAUX'];
	
									$libelle_afficher   =   $this->limiter_taille_text($element_result_nomenc['LIBELLE'],$taille_max);
									$html 		.="\t\t"."<TD ROWSPAN='$nb_rowspan_2' nowrap  align='center'><img src='".$GLOBALS['SISED_URL_IMG']."text.php?text=".urlencode($libelle_afficher)."' alt='".$libelle_afficher."' align='center'></TD>\n";
									// Fin Modification alassane
								}else{
									$html 		.= $element_result_nomenc['LIBELLE'];
								}
								
                            }
						}
						elseif ( trim($element['TYPE_OBJET']) == 'systeme_valeur_multiple' ){
								$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
								foreach($result_nomenc as $element_result_nomenc){		
									 $html 		.="\t\t"."<TD nowrap COLSPAN='$nb_colspan' class='".$classe_fond."'>".$element_result_nomenc['LIBELLE']."</TD>\n";
							 	}
						}
        }
				$html 			.= "\t".'</TR>'."\n";
				
				/// 3 �me TR si plusieurs champs de saisies associ�s comme G et F
										
				if($nb_colspan >0){
						foreach($dico as $element){
								if ( trim($element['TYPE_OBJET']) == 'systeme_valeur_multiple' ){
										$html 			.= "\t"."<TR class='ligne-paire'>"."\n";
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
										$inc	=	-1;
										foreach($result_nomenc as $element_result_nomenc){
												$inc++;
												$tab_elem_zone_ref[$inc]['CODE'] = $element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])];		
												foreach($tab_champ_val_multi as $champ){
														$html .= "\t\t<TD class='".$classe_fond."' nowrap>";
														$html .= $this->recherche_libelle_zone($champ['ID_ZONE'],$langue)."</TD>\n";
												}						
										}
										$html 			.= "\t".'</TR>'."\n";
								}
						}
				}
				if($affiche_totaux_val_multi or $bool_affiche_totaux_champ_text ){		
								$html.= "\t <script language='javascript' type='text/javascript'>\n";
								$html.= "\t <!--\n";
									if($affiche_totaux_val_multi ){
									/// fonction de calcul des totaux pour les champs textes val multi par ligne
									$html.= "\t\t function Calcul_Total_Ligne(ligne){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 $nb_code		=	-1;
									 //$nb_champs	=	-1;
										foreach($tab_champ_val_multi as $champ){
											 //$nb_champs++;
											 if(!isset($champ['TYPE_OBJET']) || $champ['TYPE_OBJET']<>'hidden_field'){
												 foreach($tab_elem_zone_ref as $code){
													$nb_code++;
													$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_code] = parseInt(document.form1.".$champ['CHAMP_PERE']."_'+ligne+'_".$code['CODE'].".value);';\n";
													$html.= "\t\t\t eval(chaine_eval);\n";
												}
											}
										}
										$html.= "\t\t\t for(j=0; j<=$nb_code; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+ligne+'.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n"; 
									/// FIN fonction de calcul des totaux pour les champs textes val multi par ligne
									
									/// fonction de calcul des totaux pour les champs textes val multi par code nomeclature
									$html.= "\t\t function Calcul_Total_Code(champ,code){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 $nb_code		=	-1;
									 //$nb_champs	=	-1;
										//foreach($tab_champ_val_multi as $champ){
											 //$nb_champs++;
											 for( $num_ligne=0; $num_ligne < $NB_LIGNE_ECRAN; $num_ligne++){
													//$nb_code++;
														$html.= "\t\t\t chaine_eval = 'tab_champ_op[$num_ligne] = parseInt(document.form1.'+champ+'_".$num_ligne."_'+code+'.value);';\n";
														$html.= "\t\t\t eval(chaine_eval);\n";
												} 
										//}
										$html.= "\t\t\t for(j=0; j<$NB_LIGNE_ECRAN; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ+'_'+code+'.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n"; 
									/// FIN fonction de calcul des totaux pour les champs textes val multi par code nomeclature
																		/// fonction de calcul total des totaux pour les champs val multi
									$html.= "\t\t function Calcul_Total_Val_Multi(){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 $nb_elem		=	-1;
										foreach($tab_champ_val_multi as $champ){
											 if(!isset($champ['TYPE_OBJET']) || $champ['TYPE_OBJET']<>'hidden_field'){
												 foreach($tab_elem_zone_ref as $code){
														for( $num_ligne=0; $num_ligne < $NB_LIGNE_ECRAN; $num_ligne++){
																$nb_elem++;
																$html.= "\t\t\t chaine_eval = 'tab_champ_op[$nb_elem] = parseInt(document.form1.".$champ['CHAMP_PERE']."_".$num_ligne."_".$code['CODE'].".value);';\n";
																$html.= "\t\t\t eval(chaine_eval);\n";
														} 
												}
											}
										}
										//}
										$html.= "\t\t\t for(j=0; j<=$nb_elem; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_VAL_MULTI.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n"; 
									/// FIN fonction de calcul total des totaux pour les champs val multi

									
									/// fonction qui appele les precedentes
									$html.= "\t\t function Calcul_Total_Ligne_Code(ligne,champ,code){\n";
									$html.= "\t\t\t Calcul_Total_Ligne(ligne);\n";
									$html.= "\t\t\t Calcul_Total_Code(champ,code);\n";
									$html.= "\t\t\t Calcul_Total_Val_Multi();\n";
									$html.= "\t\t }\n"; 

							}		
							if($bool_affiche_totaux_champ_text){											
									/// fonction de calcul des totaux pour les champs textes simples
									$html.= "\t\t function Calcul_Total_Ch_Txt(champ){\n";
										$html.= "\t\t\t var tab_champ_op 	 = new Array();\n";
										$html.= "\t\t\t var chaine_eval;\n";
										$html.= "\t\t\t var total = 0;\n";
									 //$nb_code		=	-1;
									 //$nb_champs	=	-1;
										//foreach($tab_champ_val_multi as $champ){
											 //$nb_champs++;
									 for( $num_ligne=0; $num_ligne < $NB_LIGNE_ECRAN; $num_ligne++){
											//$nb_code++;
											$html.= "\t\t\t chaine_eval = 'tab_champ_op[$num_ligne] = parseInt(document.form1.'+champ+'_".$num_ligne.".value);';\n";
											$html.= "\t\t\t eval(chaine_eval);\n";
										} 
										//}
										$html.= "\t\t\t for(j=0; j<$NB_LIGNE_ECRAN; j++){\n";
											$html.= "\t\t\t\t	if(tab_champ_op[j]){\n";
												$html.= "\t\t\t\t\t chaine_eval='total+=tab_champ_op[j];';\n";
												$html.= "\t\t\t\t\t eval(chaine_eval);\n";
											$html.= "\t\t\t\t	}\n";
										$html.= "\t\t\t }\n";
										$html.= "\t\t\t chaine_eval='document.form1.TOTAL_'+champ+'.value='+total+';';\n";
										$html.= "\t\t\t eval(chaine_eval);\n";
									$html.= "\t\t }\n"; /// FIN fonction de calcul des totaux pour les champs textes simples
							}
						$html.= "\t -->\n";
						$html.= "\t </script>\n";
				}
				for ($ligne = 0; $ligne < $NB_LIGNE_ECRAN; $ligne++){
					// Un peu de couleur
					// TODO: remonter dans la css

						if(!isset($classe_fond)) {
							$classe_fond = 'ligne-paire';
						} else {
							if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
							} else {
								$classe_fond = 'ligne-paire';
							}
						}

                        $html.= "\t<TR>\n";
                        
						// Pour chaque Ligne
						$i_TD = 0;
						$html .= "\t\t<TD class='".$classe_fond."'  id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'><INPUT NAME=DELETE"."_".$ligne." TYPE='checkbox' VALUE=1 onClick=Alert_Supp(this.name);></TD>";
						$i_TD++;
						foreach($dico as $element){
								//$i_TD++;
								if( ($element['TYPE_OBJET'] == 'systeme_valeur_unique') and ($element['BOUTON_INTERFACE']=='popup') ){
											$html 	.= "\t\t<TD nowrap class='".$classe_fond."' id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
											if( trim($element['CHAMP_INTERFACE'])<>trim($element['CHAMP_PERE']) ){
													$html .= "<INPUT ID='".$element['CHAMP_PERE']."_$ligne' name='".$element['CHAMP_PERE']."_$ligne' type='hidden' value='$".$element['CHAMP_PERE']."_$ligne' >"; 
													$html .= "<INPUT name='".$element['CHAMP_INTERFACE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																			value=\"$".$element['CHAMP_INTERFACE']."_$ligne\" readonly> ";
											}
											else{
													$html .= "<INPUT name='".$element['CHAMP_PERE']."_$ligne' type='text' ".$element['ATTRIB_OBJET']."
																		 value=\"$".$element['CHAMP_PERE']."_$ligne\" readonly> ";
											}
											//eval("\$fonction_click	=	".$element['FONCTION_INTERFACE'].";");
											$fonction	=	str_replace("\$ligne","$ligne",$element['FONCTION_INTERFACE']);
											$fonction	=	stripslashes($fonction);
											//$element['FONCTION_INTERFACE']	=	str_replace("\$code_etablissement","$ligne",$element['FONCTION_INTERFACE']);	
											$html .= "<input type='button' onClick=".$fonction."; value='...'>";
											$html .= "\t\t</TD>\n";
											$i_TD++;
								}
								elseif($element['TYPE_OBJET'] == 'systeme_valeur_unique'){
											$html 					.= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
											$html 	        .= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
                      						$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
		  									$html 	        .= "".$element['ATTRIB_OBJET']."></TD>\n";
											$i_TD++;
								}
								elseif($element['TYPE_OBJET'] == 'text' or $element['TYPE_OBJET'] == 'systeme_text'){
											$fonction_somme ='';
											if($affiche_totaux_champ_text[$element['CHAMP_PERE']]){
													$fonction_somme = " onBlur=Calcul_Total_Ch_Txt('".$element['CHAMP_PERE']."'); ";
											}

											$html 			.= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
											$html 	        .= "<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."' TYPE='text' ";
                      						$html 	        .= " VALUE=\"$".$element['CHAMP_PERE']."_".$ligne."\""; 
		  									$html 	        .= "".$element['ATTRIB_OBJET']."$fonction_somme></TD>\n";
											$i_TD++;
								}
								elseif($element['TYPE_OBJET'] == 'checkbox'){
											$html 	        .= "\t\t<TD class='".$classe_fond."'  id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($j,$NB_LIGNE_ECRAN)'>
																<INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='checkbox'";
											$html 	        .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne.""; 
											$html       		.= ">";
											$html 	        .= "</TD>\n";
											$i_TD++;
								}

								elseif($element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo'){
										
										$html 	.= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
										$html		.= "<SELECT ".$element['ATTRIB_OBJET']." ".' ID=\''. $element['CHAMP_PERE']."_".$ligne .'\' '."NAME='".$element['CHAMP_PERE']."_".$ligne."'>\n";
										$html 	.= "\t\t\t<OPTION VALUE=''>---</OPTION>\n"; 
										
										////Ajout Hebi� Mars 2021 pour int�gration des listes deroulantes dynamique au mat_grille
										$dynamic_content_object = false ;
										if( (trim($element['REQUETE_CHAMP_INTERFACE']) <> '') && (trim($element['CHAMP_INTERFACE']) <> '') && (trim($element['TABLE_INTERFACE']) <> '')){
											//if( ereg('\$code_annee', $element['REQUETE_CHAMP_INTERFACE']) || ereg('\$code_etablissement', $element['REQUETE_CHAMP_INTERFACE']) ){
												$dynamic_content_object = true ;
											//}
										}
										if($dynamic_content_object == true){
											$html 	.= '<!--DynContentObj_'.$element['ID_ZONE'].'_'.$element['CHAMP_PERE'].'_'.$ligne.'-->'."\n"; 
										}else{
											$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
											foreach($result_nomenc as $element_result_nomenc){
													//Un valeur du combo pour chaque valeur de la nomenclature 
													$html 	.= "\t\t\t<OPTION VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])].">"; 
													$html 	.= $element_result_nomenc['LIBELLE']."</OPTION>\n"; 
											}
										}
										/////
										
										$html 		.= "\t\t</SELECT></TD>\n";
										$i_TD++;
								}
								elseif($element['TYPE_OBJET'] == 'systeme_liste_radio'
											or trim($element['TYPE_OBJET']) == 'liste_radio' 
											or trim($element['TYPE_OBJET']) == 'booleen'){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
										foreach($result_nomenc as $element_result_nomenc){		
												$html       .= "\t\t<TD class='".$classe_fond."' id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'><INPUT ".' ID=\''. $element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME=".$element['CHAMP_PERE']."_".$ligne." TYPE='radio'";
												$html       .= " VALUE=$".$element['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]; 
												$html       .= "".$element['ATTRIB_OBJET']."></TD>\n";
												$i_TD++;
										}
								}
								elseif($element['TYPE_OBJET'] == 'systeme_valeur_multiple'){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
										foreach($result_nomenc as $element_result_nomenc){		
												foreach($tab_champ_val_multi as $champ){
													if(isset($champ['TYPE_OBJET']) && $champ['TYPE_OBJET']=='hidden_field'){
														$html .= "\t\t<TD class='".$classe_fond."' nowrap id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
														$html .= "<INPUT ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."' TYPE='hidden' ";
														$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."\""; 
														$html .= "/></TD>\n";
														$i_TD++;
													}else{
														$fonction_somme ='';
														if($affiche_totaux_val_multi){
																$fonction_somme = " onBlur=Calcul_Total_Ligne_Code($ligne,'".$champ['CHAMP_PERE']."','".$element_result_nomenc[$element['CHAMP_PERE']]."') ";
														}
														$html .= "\t\t<TD class='".$classe_fond."' nowrap id='".$classe_fond.'_'.$ligne.'_'.$i_TD."' onClick='MiseEvidenceLigneFrame($ligne,$NB_LIGNE_ECRAN)'>";
														$html .= "<INPUT ".' ID=\''. $champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])] .'\' '."NAME='".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."' TYPE='text' ";
														$html .= " VALUE=\"$".$champ['CHAMP_PERE']."_".$ligne."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."\""; 
														$html .= "".$champ['ATTRIB_OBJET']."$fonction_somme></TD>\n";
														$i_TD++;
													}
												}
										}
								}								
						}/// fin parcours des elements de DICO
												/// affiche les totaux
						if($affiche_totaux_val_multi){

								if(!isset($classe_fond)) {
									$classe_fond = 'ligne-paire';
								} else {
									if($classe_fond == 'ligne-paire') {
										$classe_fond = 'ligne-impaire';
									} else {
										$classe_fond = 'ligne-paire';
									}
								}

								$html .= "\t\t<TD class='".$classe_fond."'>";
								$html .= "<INPUT NAME='TOTAL_".$ligne."' TYPE='text' readonly";
								$html .= " VALUE=''"; 
								$html .= " size=2 style='background-color:#CCCCCC; text-align: right; font-weight:bold;'></TD>\n";
						}
						$html 			.= "\t".'</TR>'."\n";
				}// fin parcours des lignes
				/*
				if($affiche_totaux_val_multi){
						$html.= "\t <script language='javascript' type='text/javascript'>\n";
						$html.= "\t <!--\n";
						for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
								 $html.= "\t Calcul_Total_Ligne($ligne);\n";
						}
						$html.= "\t -->\n";
						$html.= "\t </script>\n";
				}*/
				if($bool_affiche_totaux_champ_text or $affiche_totaux_val_multi){

						if(!isset($classe_fond)) {
							$classe_fond = 'ligne-paire';
						} else {
							if($classe_fond == 'ligne-paire') {
								$classe_fond = 'ligne-impaire';
							} else {
								$classe_fond = 'ligne-paire';
							}
						}

						//echo"<br>AZAZAZ<br>";
						$colspan_total = 1;
						$html 			.= "\t".'<TR>'."\n";
						//$html 					.= "\t\t<TD class='ligne-paire td_ss_rebords_1'>&nbsp;</TD>\n";
						foreach($dico as $element){
								if($element['TYPE_OBJET'] == 'systeme_valeur_unique'){
											//$html 					.= "\t\t<TD class='ligne-paire td_ss_rebords_1'>&nbsp;</TD>\n";
											$colspan_total+=1;
								}
								elseif($element['TYPE_OBJET'] == 'text' or  $element['TYPE_OBJET'] == 'systeme_text'){
											if($affiche_totaux_champ_text[$element['CHAMP_PERE']]){
													if($colspan_total){
															$html .= "\t\t<TD class='".$classe_fond."' colspan='$colspan_total'><b>";
															$html .= $this->recherche_libelle_page('total',$langue,'questionnaire.php');
															$html .= "</b></TD>\n";
															
															$colspan_total = 0;
															//$text_total_deja_affiche =true;
													}
													$html .= "\t\t<TD class='".$classe_fond."'>";
													//$html .= $this->recherche_libelle_page('total',$langue,'questionnaire.php')."<BR>\n";
													$html .= "<INPUT NAME='TOTAL_".$element['CHAMP_PERE']."' TYPE='text' readonly";
													$html .= " VALUE='' "; 
													$html .= "".$element['ATTRIB_OBJET']."></TD>\n";
											}
											else{
													$html 					.= "\t\t<TD class='".$classe_fond."'>&nbsp;</TD>\n";
											}
								}
								elseif($element['TYPE_OBJET'] == 'combo' or $element['TYPE_OBJET'] == 'systeme_combo' or $element['TYPE_OBJET'] == 'checkbox'){
										//$html 					.= "\t\t<TD class='ligne-paire td_ss_rebords_1'>&nbsp;</TD>\n";
										$colspan_total+=1;
								}
								elseif($element['TYPE_OBJET'] == 'systeme_liste_radio'
											or trim($element['TYPE_OBJET']) == 'liste_radio' 
											or trim($element['TYPE_OBJET']) == 'booleen'){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
										$colspan		 = count($result_nomenc);
										//$html 	.= "\t\t<TD COLSPAN='$colspan' class='td_center td_ss_rebords_1'>&nbsp;</TD>\n";
										//$html .= $this->recherche_libelle_page('total',$langue,'questionnaire.php')."<BR>\n";
										$colspan_total+=$colspan;
								}
								elseif($element['TYPE_OBJET'] == 'systeme_valeur_multiple'){
										$result_nomenc 		= $this->requete_nomenclature($element['TABLE_FILLE'],$element['CHAMP_FILS'], $element['CHAMP_PERE'], $langue ,$id_systeme, $element['SQL_REQ']);
										$colspan		 = count($result_nomenc)*$nb_colspan;
										//$html 			.= "\t\t<TD COLSPAN='$colspan' class='td_center td_ss_rebords_1'>&nbsp;</TD>\n";
										//$colspan_total+=$colspan;
										if($affiche_totaux_val_multi){
												if($colspan_total){
														$html .= "\t\t<TD class='".$classe_fond."' colspan='$colspan_total'><b>";
														$html .= $this->recherche_libelle_page('total',$langue,'questionnaire.php');
														$html .= "</b></TD>\n";
														
														$colspan_total = 0;
														//$text_total_deja_affiche =true;
												}
												foreach($result_nomenc as $element_result_nomenc){		
														foreach($tab_champ_val_multi as $champ){
																$html .= "\t\t<TD class='".$classe_fond."'>";
															//$html .= $this->recherche_libelle_page('total',$langue,'questionnaire.php')."<BR>\n";
															if(!isset($champ['TYPE_OBJET']) || $champ['TYPE_OBJET']<>'hidden_field'){
																$html .= "<INPUT NAME='TOTAL_".$champ['CHAMP_PERE']."_".$element_result_nomenc[$this->get_champ_extract($element['CHAMP_PERE'])]."' TYPE='text' readonly";
																$html .= " VALUE=''"; 
																$html .= "".$element['ATTRIB_OBJET']." style='background-color:#CCCCCC; text-align: right; font-weight:bold;'>";
															}	
															$html .= "</TD>\n";
														}
												}
										}
										else{
												//$html 					.= "\t\t<TD class='ligne-paire'>&nbsp;</TD>\n";
												$colspan_total+=$colspan;
										}

								}								
						}
						if($affiche_totaux_val_multi){
								$html .= "\t\t<TD class='".$classe_fond."'>";
								$html .= "<INPUT NAME='TOTAL_VAL_MULTI' TYPE='text' readonly style='background-color:#CCCCCC; text-align: right; font-weight:bold;'";
								$html .= " VALUE='' "; 
								$html .= "size=2></TD>\n";
						}

						$html 			.= "\t".'</TR>'."\n";
						////
						if($bool_affiche_totaux_champ_text or $affiche_totaux_val_multi){
								$html.= "\t <script language='javascript' type='text/javascript'>\n";
								$html.= "\t <!--\n";
								
								if($affiche_totaux_val_multi){
											//// les totaux des champs val multi par ligne
										for( $ligne=0; $ligne < $NB_LIGNE_ECRAN; $ligne++ ){
												 $html.= "\t Calcul_Total_Ligne($ligne);\n";
										}
											//// les totaux des champs val multi par code
										foreach($tab_champ_val_multi as $champ){
											 //$nb_champs++;
											 if(!isset($champ['TYPE_OBJET']) || $champ['TYPE_OBJET']<>'hidden_field'){
												 foreach($tab_elem_zone_ref as $code){
															$html.= "\t Calcul_Total_Code('".$champ['CHAMP_PERE']."','".$code['CODE']."');\n";
												 } 
											}
										}
										/// le total des val multi
										$html.= "\t Calcul_Total_Val_Multi();\n";
								}								
								//// les totaux des champs de type text
								if($bool_affiche_totaux_champ_text){
										foreach($affiche_totaux_champ_text as $nom_champ=>$aff_totaux){
												if($aff_totaux){
														 $html.= "\t Calcul_Total_Ch_Txt('$nom_champ');\n";
												}
										}
								}

								$html.= "\t -->\n";
								$html.= "\t </script>\n";
						}
						
						//// 
						/*
						$html.= "\t <script language='javascript' type='text/javascript'>\n";
						$html.= "\t <!--\n";				
						foreach($affiche_totaux_champ_text as $nom_champ=>$aff_totaux){
								if($aff_totaux){
										 $html.= "\t Calcul_Total_Ch_Txt('$nom_champ');\n";
								}
						}
						$html.= "\t -->\n";
						$html.= "\t </script>\n";
						*/
				}
				
				$html		.= "</TABLE>";

		$html		.= "\n" . $this->get_html_buttons($langue);
		
        $html 	.= "</FORM>";
		$html	.= "</div>"."\n";

        //print '<BR>'.$element['FRAME'];
    		//file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);	
				if (trim($element['FRAME']) <> '') {
						file_put_contents($GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'], $html);
						echo $html;
				}else{
						if($this->nb_themes_to_generate==1) print ' Template : ' . $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/'.$element['FRAME'] . ' : Incorrect !<BR>';
				}
	      //echo $html; 
    }//Fin function generer_frame_grille_ligne

 		/**
		* METHODE :  generer_frame:
		* Parcourt les themes, recup�re le type de frame � g�n�rer
		* et app�le la fonction associ�e
		*
		*<pre>
		*DEBUT
		* ... ALGO ...
		*FIN
		*</pre>
		* 
		*/
    function generer_frame($code_annee='', $code_etablissement=''){

				foreach ($this->langues as $langue){
						//$this->langue = $langue;
						if($code_annee=='' && $code_etablissement=='')	print '<H2>langue = '.$langue.'</H2>';
						$rep_frames = $GLOBALS['SISED_PATH'] . 'questionnaire/'.$langue.'/';
						if (!file_exists($rep_frames)){ // Cr�ation du r�pertoire

								if ( !mkdir($rep_frames) ){}
								else{
										chmod($rep_frames, 0770); // pour LINUX
								}
						}
						foreach ($this->id_systemes as $id_systeme){
								if($code_annee=='' && $code_etablissement=='')	print '<H1>id_systeme = '.$id_systeme.'</H1>';
						foreach ($this->id_themes as $id_theme){
										//$this->id_theme = $id_theme;
										$type_frame = ''; // PHP8 fix S17d : réinitialiser pour éviter valeur périmée du thème précédent
										$requete        = "SELECT LIBELLE
															FROM DICO_TRADUCTION 
															WHERE CODE_NOMENCLATURE=".$id_theme.$id_systeme." AND CODE_LANGUE='".$langue."'
															AND NOM_TABLE='DICO_THEME_LIB_LONG';";
										 
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$aresult				= $GLOBALS['conn_dico']->GetAll($requete);
												if(!is_array($aresult)){                    
														throw new Exception('ERR_SQL');  
												} 
												$this->libelle_long   = ($aresult[0]['LIBELLE'] ?? '');									
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow
										//echo "<br> $requete <br>";

										if(($code_annee=='' && $code_etablissement=='') && ($this->nb_themes_to_generate==1 || count($aresult)))
											print '<H3>id_theme = '.$id_theme.' : <input onClick="javascript:location.href= \'administration.php?val=gestzone&id_theme_choisi='.$id_theme.'\'" type="button" value=" Manage Zones ">&nbsp;&nbsp;
											                                      <input onclick="javascript:location.href= \'administration.php?val=gestheme&theme_from_zones='.$id_theme.'\'" type="button" value=" Manage Theme">
												   </H3>';
										
										//$requete        = "SELECT TYPE FROM DICO_THEME WHERE ID =".$id_theme;
										$requete					=	"SELECT     DICO_TYPE_THEME.ID_TYPE_THEME,
																					DICO_TYPE_THEME.LIBELLE
																		 FROM       DICO_TYPE_THEME , DICO_THEME
																		 WHERE 		DICO_TYPE_THEME.ID_TYPE_THEME = DICO_THEME.ID_TYPE_THEME
																		 AND 		DICO_TYPE_THEME.CODE_LANGUE = '".$langue."'
																		 AND	    DICO_THEME.ID =".$id_theme;
										 
										// Traitement Erreur Cas : GetAll / GetRow
										try {
												$aresult				= $GLOBALS['conn_dico']->GetAll($requete);
												if(!is_array($aresult)){                    
														throw new Exception('ERR_SQL');  
												} 
												$type_frame		  = trim(($aresult[0]['LIBELLE'] ?? ''));									
										}
										catch(Exception $e){
												$erreur = new erreur_manager($e,$requete);
										}
										// Fin Traitement Erreur Cas : GetAll / GetRow

										switch ($type_frame){
												case "Formulaire" :
													$this->generer_frame_formulaire($id_theme, $langue ,$id_systeme, $code_annee, $code_etablissement);
													 break;
												case "Grille" :
													$this->generer_frame_grille($id_theme, $langue ,$id_systeme);
													break;
												case "Grille_eff_1" :{
													$requete="	SELECT	DICO_ZONE.*, DICO_ZONE_SYSTEME.*
																FROM		DICO_ZONE , DICO_ZONE_SYSTEME 
																WHERE		DICO_ZONE.ID_ZONE = DICO_ZONE_SYSTEME.ID_ZONE 
																AND			DICO_ZONE.ID_THEME = ".$id_theme.
																" AND		DICO_ZONE_SYSTEME.ID_SYSTEME = ".$id_systeme.
																" AND		DICO_ZONE_SYSTEME.ACTIVER = 1												
													 			  AND		DICO_ZONE_SYSTEME.TYPE_OBJET = 'dimension_colonne' ";
													
													$dico	= $GLOBALS['conn_dico']->GetAll($requete);
													if(is_array($dico) && count($dico)>0)
														$this->generer_frame_grille_eff_1_fix_col($id_theme, $langue ,$id_systeme);
													else
														$this->generer_frame_grille_eff_1($id_theme, $langue ,$id_systeme);
													break;
													}
												case "Grille_eff_2" :
													$this->generer_frame_grille_eff_2($id_theme, $langue ,$id_systeme);
													break;
												case "Grille_eff_3" :
													$this->generer_frame_grille_eff_3($id_theme, $langue ,$id_systeme);
													break;
												case "Mat_Grille" :
													$this->generer_frame_mat_grille($id_theme, $langue ,$id_systeme);
													break;
				
												case "Matrice" :
													$this->generer_frame_matrice($id_theme, $langue ,$id_systeme);
													break;
												default :
													print 'Attention! Dico <BR>';
													break;
										}
								}
						}
				}
		}		
}//Fin class gestion_theme

?>
