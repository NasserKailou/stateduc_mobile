<?php class gestion_zone {
	
			
		/**
		* Attribut : $conn
		* <pre>
		* 	Variable de connexion à la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $conn;
			
		/**
		* Attribut : $langue
		* <pre>
		* 	Langue choisie
		* </pre>
		* @var 
		* @access public
		*/   
		public $langue;
			
		/**
		* Attribut : $nomPage
		* <pre>
		* 	Nom de la page à transmettre à la fonction lit_libelles_page
		* </pre>
		* @var 
		* @access public
		*/   
		public $nomPage;
			
		/**
		* Attribut : $id_theme
		* <pre>
		* 	le thème en cours
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme; // 
		public $val_choix_type_list_theme;
			
		/**
		* Attribut : $id_type_theme
		* <pre>
		* 	Type du Thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_type_theme;
			
		/**
		* Attribut : $libelle_type_theme
		* <pre>
		* 	libellé du type thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_type_theme;
			
		/**
		* Attribut : $libelle_theme
		* <pre>
		* 	libellé du thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $libelle_theme;
		
		/**
		* Attribut : $id_theme_systeme
		* <pre>
		* 	id système du thème courant
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_theme_systeme;
		public $id_theme_appart;
			
		/**
		* Attribut : $lib_champ_err
		* <pre>
		* libellé associé au champ en cas d'erreur
		* </pre>
		* @var 
		* @access public
		*/   
		public $lib_champ_err;
			
		/**
		* Attribut : $order_zones
		* <pre>
		* booléen utilisé pour permettre ou non d'ordonner 
		* les zones par secteur
		* </pre>
		* @var 
		* @access public
		*/   
		public $order_zones = false;
			
		/**
		* Attribut :  $iTab
		* <pre>
		* indice tableau de la table mère  ds TabGestZones['tables_meres']
		* </pre>
		* @var 
		* @access public
		*/   
		public $iTab;  // 
			
		/**
		* Attribut : $iZone
		* <pre>
		* indice tableau de la zone  ds TabGestZones['zones'][$iTab]
		* </pre>
		* @var 
		* @access public
		*/   
		public $iZone;  // // 
		
		/**
		* Attribut : $id_Zone
		* <pre>
		* id de la zone  ds TabGestZones['zones'][$iTab] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $id_zone;  // // 
		
		/**
		* Attribut : $i_tabm
		* <pre>
		* indice de la table mere  ds TabGestZones['zones'] pour modification
		* </pre>
		* @var 
		* @access public
		*/   
		public $i_tabm;	
		
		/**
		* Attribut : $nb_TabM
		* <pre>
		* le nombre total de table mères
		* </pre>
		* @var 
		* @access public
		*/   
		public $nb_TabM;  // // 
			
		/**
		* Attribut : $Action
		* <pre>
		* contient le type d'action à effectuer aprés soumission
		* </pre>
		* @var 
		* @access public
		*/   
		public $Action; // 
			
		/**
		* Attribut : $OkAction
		* <pre>
		* indique le resultat de l'action de MAJ ds la base
		* </pre>
		* @var 
		* @access public
		*/   
		public $OkAction; // 
			
		/**
		* Attribut : $ActMAJ
		* <pre>
		* indique la tenue d'une action de MAJ
		* </pre>
		* @var 
		* @access public
		*/   
		public $ActMAJ;  // 
			
		/**
		* Attribut : $champs_zone
		* <pre>
		* les noms des champs relatifs à la ZONE
		* </pre>
		* @var 
		* @access public
		*/   
		public $champs_zone 	= array(); // 
			
		/**
		* Attribut : $btn_add 
		* <pre>
		* controler l'affichage du bouton Ajouter
		* </pre>
		* @var 
		* @access public
		*/   
		public $btn_add 			= false; // 
			
		/**
		* Attribut : $TabValTabM 
		* <pre>
		* Vals tables mères pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValTabM 		= array(); // 
			
		/**
		* Attribut : $TabValZone
		* <pre>
		* Vals zones pour l'affichage
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabValZone		= array(); // 
			
		/**
		* Attribut : $TabGestZones
		* <pre>
		* Contient plusieurs valeurs recuperees de la base pour la manip des zones
		* </pre>
		* @var 
		* @access public
		*/   
		public $TabGestZones 	= array(); // 
		
		/**
		* Attribut : $TabBD
		* <pre>
		* Contient les tables de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $TabBD 	= array(); //
		/**
		* Attribut : $ViewsBD
		* <pre>
		* Contient les vues de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $ViewsBD 	= array(); //
		/**
		* Attribut : $ColTabBD
		* <pre>
		* Contient les colonnes d'une table de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $ColTabBD 	= array(); //
		
		/**
		* Attribut : $TabValZoneSys
		* <pre>
		* Contient la liste des zones systemes
		* </pre>
		* @var 
		* @access public
		*/
		public $TabValZoneSys = array();
		
		/**
		* METHODE : 
		* <pre>
		* Constructeur de la classe
		* </pre>
		* @access public
		* 
		*/
		function __construct(){
			$this->conn			= $GLOBALS['conn'];
			$this->langue    	= $_SESSION['langue'];
			$this->nomPage		= '/gestion_zone.php';
		}
		
		/**
		* METHODE : init()
		* <pre>
		* Enchaine les fonctions dans le bon ordre de traitement de l'objet instancié
		* </pre>
		* @access public
		* 
		*/
		function init() {
			$this->btn_add 	= false;
			
			lit_libelles_page($this->nomPage);

			if (isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']<>''){ 
				if(isset($_GET['choix_affich']) && $_GET['choix_affich'] == 'all_themes'){
					$_SESSION['choix_affich'] = 'all_themes';
					$this->get_themes();
					$_GET['filtre_secteur_T_S_en_cours'] = $this->id_theme_systeme;
				}elseif(isset($_GET['choix_affich']) && $_GET['choix_affich'] <> ''){
					$_SESSION['choix_affich'] = $_GET['choix_affich'];
				}elseif(isset($_POST['choix_affich']) && $_POST['choix_affich'] <> ''){
					//($_POST['choix_affich']==1) ? ($_SESSION['choix_affich'] = 'all_themes') : ($_SESSION['choix_affich'] = 'active_themes');
					if($_POST['choix_affich']==1)
						$_SESSION['choix_affich'] = 'all_themes';
					elseif($_POST['choix_affich']==2)
						$_SESSION['choix_affich'] = 'active_themes';
					elseif($_POST['choix_affich']==3)
						$_SESSION['choix_affich'] = 'active_themes_rattach';
				}elseif(!isset($_GET['choix_affich']) && !isset($_POST['choix_affich'])){
					$_SESSION['choix_affich'] = 'active_themes';
				}
				$_SESSION['filtre_secteur_T_S']     = $_GET['filtre_secteur_T_S_en_cours'];
				if($_GET['filtre_secteur_T_S_en_cours'] != $_SESSION['secteur']){
					$_SESSION['secteur'] = $_GET['filtre_secteur_T_S_en_cours'];
					unset ($_SESSION['hierarchie_regroup']); 
					unset ($_SESSION['infos_etab']);
					unset($_SESSION['theme']);
				}
			} 
			if(!isset($_SESSION['filtre_secteur_T_S'])) $_SESSION['filtre_secteur_T_S'] = $_SESSION['secteur'];
			if(!isset($_SESSION['choix_affich'])) $_SESSION['choix_affich'] = 'active_themes';
			$this->val_choix_type_list_theme=$_SESSION['choix_affich'];

			$this->get_themes();
			$this->printJS();
			if( count($this->TabGestZones['themes'])){
				$this->gererPost();
				if (!isset($_POST['btn_genere'])) { 
					$this->gererAff();				
					$this->MiseEvidenceMAJ();
					$this->AlerteMAJ();
					$this->setSessions();
				}
			}else{
				 print '<BR><BR><BR> ... No Themes  ... <BR>';
			}
		}
		
		/**
		* METHODE : init()
		* <pre>
		* Enchaine les fonctions dans le bon ordre de traitement de l'objet instancié
		* </pre>
		* @access public
		* 
		*/
		function initAjax() {
			$this->btn_add 	= false;			
			lit_libelles_page($this->nomPage);			
			$this->get_themes();
		}
		
		function __wakeup(){
				$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE :  setSessions()
		* <pre>
		* Met en session les variables necessaires
		* </pre>
		* @access public
		* 
		*/
		function setSessions() {
				unset($_SESSION['GestZones']);
				
				$_SESSION['GestZones'] = $this->TabGestZones ;
				$_SESSION['GestZones']['id_theme'] = $this->id_theme ;
				$_SESSION['GestZones']['iTab'] = $this->iTab ;
				//echo '<pre>';
				//print_r($_SESSION['GestZones']);
		}
		
		/**
		* METHODE : getTextAlert()
		* <pre>
		* Récupère les messages d'alertes dans TabGestZones['TxtAlert']
		* </pre>
		* @access public
		* 
		*/
		function getTextAlert(){
				if(!($this->TabGestZones['TxtAlert'])){
						$this->TabGestZones['TxtAlert']['AvSupZone'] 	= $this->recherche_libelle(201,$this->langue,'DICO_MESSAGE');
						$this->TabGestZones['TxtAlert']['AvSupTabM'] 	= $this->recherche_libelle(202,$this->langue,'DICO_MESSAGE');
						
						$this->TabGestZones['TxtAlert']['OkIns']		= $this->recherche_libelle(203,$this->langue,'DICO_MESSAGE');
						$this->TabGestZones['TxtAlert']['OkUpd'] 		= $this->recherche_libelle(204,$this->langue,'DICO_MESSAGE');
						$this->TabGestZones['TxtAlert']['OkSup'] 		= $this->recherche_libelle(205,$this->langue,'DICO_MESSAGE');
						
						$this->TabGestZones['TxtAlert']['PbIns'] 		= $this->recherche_libelle(206,$this->langue,'DICO_MESSAGE');
						$this->TabGestZones['TxtAlert']['PbUpd'] 		= $this->recherche_libelle(207,$this->langue,'DICO_MESSAGE');
						$this->TabGestZones['TxtAlert']['PbSup'] 		= $this->recherche_libelle(208,$this->langue,'DICO_MESSAGE');
				}
		}
		
		/**
		* METHODE : printJS()
		* <pre>
		* Affiche les fonctions javascript
		* </pre>
		* @access public
		* 
		*/
		function printJS() {
			$this->getTextAlert();
			?>
			<script type=text/javascript>
			 <!-- 
			 	<?php 
					$baseTables = $this->get_tables_nomenc_db(); 
					$baseTables = $this->TabBD;
					$nbTables = count($baseTables);
					$idx = 1;
				?>
				var baseTables = [<?php foreach($baseTables as $tab) { echo '"'.$tab.'"'; echo $idx<$nbTables?',':''; $idx++;} ?>];
				<?php		
					$nbCles = count($GLOBALS['PARAM_SYS']['CLES_SIMPLES']);	
					$idx = 1;
				?>	
				var clesSimples = [<?php foreach($GLOBALS['PARAM_SYS']['CLES_SIMPLES'] as $cle) { echo '"'.$cle.'"'; echo $idx<$nbCles?',':''; $idx++;} ?>];	
				<?php		
					$nbElt = count($GLOBALS['PARAM_SYS']['ZONES_LISTE']);	
					$idx = 1;
				?>	
				var zonesListe = [<?php foreach($GLOBALS['PARAM_SYS']['ZONES_LISTE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
				
				var prefixTabRef = '<?php echo $GLOBALS['PARAM']['CODE']; ?>_<?php echo $GLOBALS['PARAM']['TYPE']; ?>_';
				<?php		
					$nbElt = count($GLOBALS['PARAM_SYS']['COMP_CODE_TYPE']);	
					$idx = 1;
				?>	
				// liste des comportements associés au champs commençants par prefixTabRef
				var compCodeType = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_CODE_TYPE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
				<?php		
					$nbElt = count($GLOBALS['PARAM_SYS']['COMP_TYPE_NUMERIQUE']);	
					$idx = 1;
				?>	
				// liste des comportements de type numérique
				var compTypeNumerique = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_TYPE_NUMERIQUE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
				<?php		
					$nbElt = count($GLOBALS['PARAM_SYS']['COMP_TABLE_FILLE']);	
					$idx = 1;
				?>	
				// liste des comportements avec table fille
				var compTableFille = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_TABLE_FILLE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
				<?php		
					$nbElt = count($GLOBALS['PARAM_SYS']['COMP_CHAMP_LIE']);	
					$idx = 1;
				?>	
				// liste des comportements avec table fille
				var compChampLie = [<?php foreach($GLOBALS['PARAM_SYS']['COMP_CHAMP_LIE'] as $elt) { echo '"'.$elt.'"'; echo $idx<$nbElt?',':''; $idx++;} ?>];
				var typeTheme = '<?php echo $this->libelle_type_theme; ?>';
				var lignesTableMere = {};
				var lignesDetails = {};
				function AvertirSupp(iTab,iZone,cas_elem,lib_elem) { 
					var TxtAvertSuppZone ="<?php echo $this->TabGestZones['TxtAlert']['AvSupZone']; ?>"; 
					var TxtAvertSuppTabM ="<?php echo $this->TabGestZones['TxtAlert']['AvSupTabM']; ?>"; 
					if (cas_elem=='Zone') { 
						var TxtAvert = TxtAvertSuppZone + lib_elem ; 
						var Act   = 'SupZone'; 
					} 
					else if(cas_elem=='TabM') { 
						var TxtAvert = TxtAvertSuppTabM + lib_elem ; 
						var Act   = 'SupTabM'; 
					} 
					if (confirm(TxtAvert)) { 
						Action(iTab,iZone,cas_elem,Act); 
					} 
				 } 
			
				function MessMAJ(action,res_action,lib_champ_err) { 
					//var i = 0; 
					//alert (action + '***' + res_action + '***' + lib_champ_err)
					var lib_champ = '';
					if(action=='AddZone' || action=='AddTabM' ) i = 0; 
					else if(action=='UpdZone' || action=='UpdTabM' ) i = 1;
					else if(action=='SupZone' || action=='SupTabM' ) i = 2									
					if(lib_champ_err != '') lib_champ = ' : (' + lib_champ_err + ')';									
					OK00 = " <?php echo $this->TabGestZones['TxtAlert']['PbIns']; ?> "; 
					OK01 = " <?php echo $this->TabGestZones['TxtAlert']['OkIns']; ?> "; 
					OK10 = " <?php echo $this->TabGestZones['TxtAlert']['PbUpd']; ?> "; 
					OK11 = " <?php echo $this->TabGestZones['TxtAlert']['OkUpd']; ?> "; 
					OK20 = " <?php echo $this->TabGestZones['TxtAlert']['PbSup']; ?> "; 
					OK21 = " <?php echo $this->TabGestZones['TxtAlert']['OkSup']; ?> ";

					var OK	= Array([OK00,OK01],[OK10,OK11],[OK20,OK21]); 
					alert(OK[i][res_action] + lib_champ);
				}
				
				$(function() {
					var newTabM = '<?php echo isset($_GET['newTabM'])?$_GET['newTabM']:''; ?>';
					if (newTabM != '' && $('Tab_'+newTabM).exists()) {
						scrollTo('#Tab_'+newTabM);
					}
					displayBtnNonValidZone();
				});
				
				function displayBtnNonValidZone() {
					if ($("img[src*='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_zone_.png']").exists()) {
						$('#btnValidZone').show();
						$('#existNonValidZone').hide();
					} else if ($("img[src*='<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_zone_r.png']").exists()) {
						$('#existNonValidZone').show();
						$('#btnValidZone').hide();
					} else {
						$('#btnValidZone').hide();
						$('#existNonValidZone').hide();
					}
				}
				
				//Ouvre/Ferme(après enregistrement) les infos d'une zone
				function openclosedetails(IDZone, tab, zone) {
					//event.preventDefault();
					var suffix = tab+'_'+zone;
					if ($('#details_tr_'+suffix).is(':visible')) {						
						if (IDZone != null) {		
							if (!validateZone(IDZone, tab, zone, true, true)) {								
								displayBtnNonValidZone();
								return false;
							}
							updateZone(IDZone, tab, zone);				
						} else {
							closedetails(suffix);
							if (lignesTableMere[suffix]) {
								$('#LigneTableMere'+suffix).empty();
								$('#LigneTableMere'+suffix).append(lignesTableMere[suffix]);	
								lignesTableMere[suffix] = '';	
								updateElt('#LigneTableMere'+suffix);
							}
							if (lignesDetails[suffix]) {
								$('#details_tr_'+suffix).empty();
								$('#details_tr_'+suffix).append(lignesDetails[suffix]);	
								lignesDetails[suffix] = '';	
							}
							displayBtnNonValidZone();
						}	
					} else {
						if (!$('#details_tr_'+suffix+' td div').exists()) {
							var params = 'ActionZone=AddZoneDetails';
							params += '&TabMActive='+tab;
							params += '&ZoneActive='+zone;
							
							var funcCallback = adddetails.bind(adddetails, tab, zone, true);
							postZone(params, funcCallback);	
						} else {
							opendetails(suffix)
						}
						//$('#LigneTableMere'+suffix+' select[name=TYPE_ZONE_BASE]').removeClass('disabled');
						//questionEdit(event, 'zone_tr_'+num, num);
						//$('#details_tr_'+num+' .form_button').button();
					}
					return false;
				}
				
				function adddetails(iTab, iZone, isNewZone, details) {
					if (isNewZone) {
						details += ''; 
						//alert(addZoneData());
					}
					$('#details_tr_'+iTab+'_'+iZone).replaceWith(details);
					opendetails(iTab+'_'+iZone);
				}
				
				function opendetails(suffix) {
					$.uniform.restore('#LigneTableMere'+suffix+' select');		
					$('#LigneTableMere'+suffix+' :text').each(function(index) {
						$(this).attr('value', $(this).val());
					});
					$('#LigneTableMere'+suffix+' select').each(function(index) {
						var value = $(this).val(); 
						$(this).find('option').each(function(index) {
							$(this).removeAttr('selected');
						});
						$(this).find('option[value='+value+']').attr('selected','selected');
					});
					lignesTableMere[suffix] = $('#LigneTableMere'+suffix).html();
					$.uniform.restore('#details_tr_'+suffix+' select');	
					$('#details_tr_'+suffix+' :text').each(function(index) {
						$(this).attr('value', $(this).val());
					});
					$('#details_tr_'+suffix+' select').each(function(index) {
						var value = $(this).val(); 
						$(this).find('option').each(function(index) {
							$(this).removeAttr('selected');
						});
						$(this).find('option[value='+value+']').attr('selected','selected');
					});
					lignesDetails[suffix] = $('#details_tr_'+suffix).html();
					$('#details_tr_'+suffix).show(100);
					$('#LigneTableMere'+suffix+' .edit_zone').css('display','none');
					$('#LigneTableMere'+suffix+' .valid_zone').css('display','block');
					$('#LigneTableMere'+suffix+' select[name=CHAMP_PERE]').removeAttr('disabled');
					$('#LigneTableMere'+suffix+' select[name=TYPE_ZONE_BASE]').removeAttr('disabled');
					$('#LigneTableMere'+suffix+' input[name=LIBELLE_ZONE]').removeAttr('readonly');
					$('#LigneTableMere'+suffix+' input[name=LIBELLE_ZONE]').removeClass('readonly_input');
					$('#LigneTableMere'+suffix+' select[name=TYPE_OBJET]').removeAttr('disabled');
					updateElt('#LigneTableMere'+suffix);
					updateElt('#details_tr_'+suffix);
				}
				
				//Ferme les informations d'une zone
				function closedetails(suffix) {
					$('#details_tr_'+suffix).hide(100);	
					$('#LigneTableMere'+suffix+' .edit_zone').css('display','block');
					$('#LigneTableMere'+suffix+' .valid_zone').css('display','none');
					$('#LigneTableMere'+suffix+' select[name=CHAMP_PERE]').attr('disabled','disabled');
					$('#LigneTableMere'+suffix+' select[name=TYPE_ZONE_BASE]').attr('disabled','disabled');
					$('#LigneTableMere'+suffix+' input[name=LIBELLE_ZONE]').attr('readonly','readonly');
					$('#LigneTableMere'+suffix+' input[name=LIBELLE_ZONE]').addClass('readonly_input');
					$('#LigneTableMere'+suffix+' select[name=TYPE_OBJET]').attr('disabled','disabled');	
					updateElt('#LigneTableMere'+suffix);
				}
				
				//Ouvre/Ferme les champs liés
				function openclosechpslies(div_id) {
					if ($('#chps_lies_'+div_id).is(':visible')) {
						$('#zone_tab1_'+div_id).show();
						$('#zone_tab2_'+div_id).show();
						$('#chps_lies_'+div_id).hide();
						$('.hidden_fields_'+div_id).empty();
						$('.hidden_fields_'+div_id).append('=> <?php echo recherche_libelle_page("ChpLiesPlus") ?>');
					} else {
						$('#zone_tab1_'+div_id).hide();
						$('#zone_tab2_'+div_id).hide();
						$('#chps_lies_'+div_id).show();
						$('.hidden_fields_'+div_id).empty();
						$('.hidden_fields_'+div_id).append('<= <?php echo recherche_libelle_page("ChpLiesMoins") ?>');
					}
				}
				
				function insertZone(iTab, iZone) { //addZoneData(iTab, iZone); return false;					
					var ligneMere = $('#ligneAddZone_'+iTab);
					var chpPere = $(ligneMere).find('select[name=CHAMP_PERE]').val();
					var params = 'ActionZone=NewZone';
						params += '&TabMActive='+iTab;
						params += '&ZoneActive='+iZone;
						params += '&CHAMP_PERE='+ chpPere;
						params += '&TYPE_ZONE_BASE='+ $(ligneMere).find('select[name=TYPE_ZONE_BASE]').val();
						params += '&LIBELLE='+ encodeURIComponent($(ligneMere).find('input[name=LIBELLE_ZONE]').val());
						params += '&ORDRE='+ $(ligneMere).find('input[name=ORDRE]').val();
						params += '&ZONE_STATUT='+ $(ligneMere).find('input[name=ZONE_STATUT]').val();
						
						params += '&ORDRE_TRI=';
						params += '&NOM_GROUPE=';
						params += '&ID_ZONE_REF=';
						params += '&CHAMP_FILS=';
						params += '&SQL_REQ=';
						params += '&TYPE_OBJET='+ $(ligneMere).find('select[name=TYPE_OBJET]').val();
						var tableFille = chpPere.substr('<?php echo $GLOBALS['PARAM']['CODE']; ?>_'.length);
						if (inArray(tableFille, baseTables)) {							
							params += '&TABLE_FILLE='+tableFille;
						} else {
							params += '&TABLE_FILLE=';
						}
					
					//alert(params);
					var suffix = iTab+'_'+iZone;
					var funcCallback = insertZoneDetails.bind(insertZoneDetails, iTab, iZone);
					postZone(params, funcCallback);
				}
				
				function insertZoneDetails(iTab, iZone, IDZone) {
					var ligneMere = $('#ligneAddZone_'+iTab);
					var chkVal = '';
					var params = 'ActionZoneSyst=1';
						params += '&TYPE_OBJET='+ $(ligneMere).find('select[name=TYPE_OBJET]').val();
						
						params += '&ATTRIB_OBJET=';
						chkVal = '0';
						params += '&AFFICH_VERTIC_MES='+ chkVal;
						params += '&ORDRE_AFFICHAGE=';
						chkVal = '1';
						params += '&ACTIVER='+ chkVal;
						chkVal = '0';
						params += '&AFFICHE_TOTAL='+ chkVal;
						chkVal = '0';
						params += '&AFFICHE_SOUS_TOTAUX='+ chkVal;
						params += '&EXPRESSION=';
						params += '&LIB_EXPR=';
						chkVal = '0';
						params += '&AFFICHE_TOTAL_VERTIC='+ chkVal;
						params += '&VALEUR_CONSTANTE=';
						params += '&BOUTON_INTERFACE=';
						params += '&TABLE_INTERFACE=';
						params += '&CHAMP_INTERFACE=';
						params += '&FONCTION_INTERFACE=';
						params += '&REQUETE_CHAMP_INTERFACE=';
						params += '&REQUETE_CHAMP_SAISIE=';
						
					//alert(params);
					var funcCallback = addNewZoneDetails.bind(addNewZoneDetails, iTab, iZone, IDZone);
					postZoneSystem(IDZone, params, funcCallback);
				}
				
				function addNewZoneDetails(iTab, iZone, IDZone) {
					var params = 'ActionZone=AddZoneDetails';
						params += '&TabMActive='+iTab;
						params += '&ZoneActive='+iZone;
					var funcCallback = adddetails.bind(adddetails, iTab, iZone, true);
					addZoneData(iTab, iZone, IDZone);
					postZone(params, funcCallback);	
				}
				
				//Met à jour les informations d'une zone
				function updateZone(IDZone, iTab, iZone) {
					var ligneMere = $('#LigneTableMere'+iTab+'_'+iZone);
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);
					var params = 'ActionZone=UpdZone';
						params += '&TabMActive='+iTab;
						params += '&ZoneActive='+iZone;
						params += '&CHAMP_PERE='+ $(ligneMere).find('select[name=CHAMP_PERE]').val();
						params += '&TYPE_ZONE_BASE='+ $(ligneMere).find('select[name=TYPE_ZONE_BASE]').val();
						params += '&LIBELLE='+ encodeURIComponent($(ligneMere).find('input[name=LIBELLE_ZONE]').val());
						params += '&TYPE_OBJET='+ $(ligneMere).find('select[name=TYPE_OBJET]').val();
						params += '&ORDRE='+ $(ligneDetails).find('input[name=ORDRE]').val();
						params += '&ZONE_STATUT='+ $(ligneMere).find('input[name=ZONE_STATUT]').val();
						params += '&ORDRE_TRI='+ $(ligneDetails).find('input[name=ORDRE_TRI]').val();
						params += '&NOM_GROUPE='+ encodeURIComponent($(ligneDetails).find('input[name=NOM_GROUPE]').val());
						params += '&ID_ZONE_REF='+ $(ligneDetails).find('select[name=ID_ZONE_REF]').val();
						params += '&TABLE_FILLE='+ $(ligneDetails).find('select[name=TABLE_FILLE]').val();
						params += '&CHAMP_FILS='+ $(ligneDetails).find('select[name=CHAMP_FILS]').val();
						params += '&SQL_REQ='+ encodeURIComponent($(ligneDetails).find('textarea[name=SQL_REQ]').val());
						
					//alert(params);
					var suffix = iTab+'_'+iZone;
					var funcCallback = updateZoneSystem.bind(updateZoneSystem, IDZone, iTab, iZone);
					postZone(params, funcCallback);	
				}
				
				function dialogDeleteZone(iTab, iZone) {
					var deleteFunc = deleteZone.bind(deleteZone, iTab, iZone);
					confirmDialog("<?php echo recherche_libelle_page('Supprimer'); ?> ?", deleteFunc, {title:"<?php echo recherche_libelle_page('Supprimer'); ?>"});	
				}
				
				function deleteZone(iTab, iZone) {
					var params = 'ActionZone=SupZone';
						params += '&TabMActive='+iTab;
						params += '&ZoneActive='+iZone;
					var funcCallback = deleteZoneData.bind(deleteZoneData, iTab, iZone);
					postZone(params, funcCallback);	
				}
				
				function deleteZoneData(iTab, iZone) {
					$('#LigneTableMere'+iTab+'_'+iZone).remove();
					$('#details_tr_'+iTab+'_'+iZone).remove();
					$('#LigneTableMereScript_'+iTab+'_'+iZone).remove();
					var tabMBase = 'LigneTableMere'+iTab+'_';
					var nbZones = iZone;
					$('#DivTableMere'+iTab+' table.sous_liste_form tr.ligneMere').filter(
						function() {
							return $(this).attr('id').substring(tabMBase.length) > iZone
						}).each(function(index) {
							var currId = $(this).attr('id').substring(tabMBase.length);
							var newId = $(this).attr('id').substring(tabMBase.length) - 1;
							$(this).attr('id', tabMBase + newId);	
							$(this).attr('onclick', "MiseEvidenceLigneZone("+iTab+","+newId+");");
							$(this).find('#NumLigne'+iTab+'_'+currId)
								.attr('id', 'NumLigne'+iTab+'_'+newId)
								.attr('name', 'NumLigne'+iTab+'_'+newId);
							$(this).find('select[name=CHAMP_PERE]')
								.attr('onchange', 'updateChampPereDepend('+iTab+','+newId+')');
							$(this).find('select[name=TYPE_OBJET]')
								.attr('onchange', 'update_champs_ref_type('+iTab+','+newId+')');
							$(this).find('.lien').each(function(index) {
								var re = new RegExp(', ' + currId + '\\)', 'gi');
								var onclickVal = $(this).attr('onclick').replace(re, ', ' + newId + ')');
								$(this).attr('onclick', onclickVal);
							});		
							$(this).find('#zone_statut_'+iTab+'_'+currId)
								.attr('id', 'zone_statut_'+iTab+'_'+newId)
							nbZones++;
						}
					);
					var detailsBase = 'details_tr_'+iTab+'_';
					$('#DivTableMere'+iTab+' table.sous_liste_form tr.details_tr').filter(
						function() {
							return $(this).attr('id').substring(detailsBase.length) > iZone
						}).each(function(index) {
							var currId = $(this).attr('id').substring(detailsBase.length);
							var newId = $(this).attr('id').substring(detailsBase.length) - 1;
							$(this).attr('id', detailsBase + newId);	
							$(this).find('select[name=TABLE_FILLE]')
								.attr('onchange', 'load_champs('+iTab+','+newId+')');
							$(this).find('#chps_lies_'+iTab+'_'+currId)
								.attr('id', 'chps_lies_'+iTab+'_'+newId);	
							$(this).find('#zone_tab1_'+iTab+'_'+currId)
								.attr('id', 'zone_tab1_'+iTab+'_'+newId);	
							$(this).find('#zone_tab2_'+iTab+'_'+currId)
								.attr('id', 'zone_tabZ_'+iTab+'_'+newId);	
							$(this).find('.hidden_fields')
								.attr('href', 'javascript:openclosechpslies(\''+iTab+'_'+newId+'\')');
						}	
					);
					$('#DivTableMere'+iTab+' .img_add_zone').attr("onclick", "javascript:validateZone('null','"+iTab+"','"+nbZones+"',false,true); ");
				}
				
				function postZone(params, callback) {
					var url = 'server-side/include/administration/gestion_zone_service.php';
					<?php if (isset($_GET['id_theme_choisi'])) { ?>
						url += '?id_theme_choisi=<?php echo $_GET['id_theme_choisi']; ?>';
					<?php } ?>
					funcPost(url, params, callback); //voir stateduc.js
				}
				
				//Met à jour les informations d'une zone
				function updateZoneSystem(IDZone, iTab, iZone) {
					var ligneMere = $('#LigneTableMere'+iTab+'_'+iZone);
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);
					var zoneId = $(ligneDetails).find('select[name=ID_ZONE_REF]').val();
					var chkVal = '';
					var params = 'ActionZoneSyst=1';
						params += '&TYPE_OBJET='+ $(ligneMere).find('select[name=TYPE_OBJET]').val();
						params += '&ATTRIB_OBJET='+ encodeURIComponent($(ligneDetails).find('input[name=ATTRIB_OBJET]').val());
						chkVal = $(ligneDetails).find('input[name=AFFICH_VERTIC_MES]').is(':checked')?'1':'0';
						params += '&AFFICH_VERTIC_MES='+ chkVal;
						params += '&ORDRE_AFFICHAGE='+ $(ligneDetails).find('input[name=ORDRE_AFFICHAGE]').val();
						chkVal = $(ligneDetails).find('input[name=ACTIVER]').is(':checked')?'1':'0';
						params += '&ACTIVER='+ chkVal;
						chkVal = $(ligneDetails).find('input[name=AFFICHE_TOTAL]').is(':checked')?'1':'0';
						params += '&AFFICHE_TOTAL='+ chkVal;
						chkVal = $(ligneDetails).find('input[name=AFFICHE_SOUS_TOTAUX]').is(':checked')?'1':'0';
						params += '&AFFICHE_SOUS_TOTAUX='+ chkVal;
						params += '&EXPRESSION='+ encodeURIComponent($(ligneDetails).find('input[name=EXPRESSION]').val());
						params += '&LIB_EXPR='+ encodeURIComponent($(ligneDetails).find('input[name=LIB_EXPR]').val());
						chkVal = $(ligneDetails).find('input[name=AFFICHE_TOTAL_VERTIC]').is(':checked')?'1':'0';
						params += '&AFFICHE_TOTAL_VERTIC='+ chkVal;
						params += '&VALEUR_CONSTANTE='+ $(ligneDetails).find('input[name=VALEUR_CONSTANTE]').val();
						params += '&BOUTON_INTERFACE='+ encodeURIComponent($(ligneDetails).find('input[name=BOUTON_INTERFACE]').val());
						params += '&TABLE_INTERFACE='+ encodeURIComponent($(ligneDetails).find('input[name=TABLE_INTERFACE]').val());
						params += '&CHAMP_INTERFACE='+ encodeURIComponent($(ligneDetails).find('input[name=CHAMP_INTERFACE]').val());
						params += '&FONCTION_INTERFACE='+ encodeURIComponent($(ligneDetails).find('textarea[name=FONCTION_INTERFACE]').val());
						params += '&REQUETE_CHAMP_INTERFACE='+ encodeURIComponent($(ligneDetails).find('textarea[name=REQUETE_CHAMP_INTERFACE]').val());
						params += '&REQUETE_CHAMP_SAISIE='+ encodeURIComponent($(ligneDetails).find('textarea[name=REQUETE_CHAMP_SAISIE]').val());
						
					//alert(params);
					var suffix = iTab+'_'+iZone;
					var funcCallback = closedetails.bind(closedetails, suffix);
					postZoneSystem(IDZone, params, funcCallback);	
				}				
				
				function postZoneSystem(iZone, params, callback) {
					var url = 'server-side/include/administration/gestion_zone_systeme.php?id_zone_active='+iZone+'&id_systeme_choisi=<?php echo $this->id_theme_systeme; ?>';
					funcPost(url, params, callback); //voir js.js
				}
				
				function addZoneData(iTab, iZone, IDZone) {
					$.uniform.restore('#ligneAddZone_'+iTab+' select');	
					var data = $('#ligneAddZone_'+iTab).clone();
					$('#ligneAddZone_'+iTab).addClass('ligneMere');
					$('#ligneAddZone_'+iTab).attr('id', 'LigneTableMere'+iTab+'_'+iZone);
					//lignesTableMere[iTab+'_'+iZone] = $('#LigneTableMere'+iTab+'_'+iZone).html();
					closedetails(iTab+'_'+iZone);	
					addImgTd(iTab, iZone, IDZone);
					updateElt('#LigneTableMere'+iTab+'_'+iZone);	
					iZone++;
					$(data).find('input[name=ORDRE]').val(parseInt($(data).find('input[name=ORDRE]').val()) + 10);
					$(data).find('select[name=CHAMP_PERE]').val('');
					$(data).find('input[name=LIBELLE_ZONE]').val('');
					$(data).find('select[name=TYPE_OBJET]').val('');
					$(data).find('.td_img_1 img').attr("onclick","javascript:validateZone(null,'"+iTab+"','"+iZone+"',false,true);");
					$(data).appendTo('#zone_table_'+iTab+ ' .zone_table_body');
					$('#zone_table_'+iTab+ ' .zone_table_body').append('<tr text-align="center" id="details_tr_'+iTab+'_'+iZone+'" class="details_tr"><td colspan="7" class="details_td"></td></tr>');
					updateElt('#ligneAddZone_'+iTab);	
					return '';
				}
				
				function addImgTd(iTab, iZone, IDZone) {
					var tdImg1 ="<td class='img_td'>"+
									"<div class='edit_zone'><img class='lien' onclick='javascript:openclosedetails("+IDZone+", "+iTab+", "+iZone+");' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_edit.png' title='<?php echo recherche_libelle_page('Modifier'); ?>' />&nbsp;&nbsp;"+
									"<img class='lien' onclick='javascript:dialogDeleteZone("+iTab+", "+iZone+");' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_drop.png'  title='<?php echo recherche_libelle_page('Supprimer'); ?>' /></div>"+
									"<div class='valid_zone'><img class='lien' onclick='javascript:openclosedetails("+IDZone+", "+iTab+", "+iZone+");' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_valider.png'  title='<?php echo recherche_libelle_page('Valider'); ?>'/>&nbsp;&nbsp;"+
									"<img class='lien' onclick='javascript:openclosedetails(null,"+iTab+", "+iZone+");' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_cancel.png' title='<?php echo recherche_libelle_page('Annuler'); ?>' /></div>"+
								"</td>";
					
					var tdImg2 ="<td align='center' class='img_td'>";	
					if ($('#LigneTableMere'+iTab+'_'+iZone).find('input[name=ZONE_STATUT]').exists()) {
						tdImg2 += "<img onclick='javascript:void(0);' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_zone_" + $('#LigneTableMere'+iTab+'_'+iZone).find('input[name=ZONE_STATUT]').val().substring(0,1) + ".png' />";
						tdImg2 += $('#LigneTableMere'+iTab+'_'+iZone).find('input[name=ZONE_STATUT]')[0].outerHTML;
					} else {
						tdImg2 += "<img onclick='javascript:void(0);' src='<?php echo $GLOBALS['SISED_URL_IMG'] ?>b_ok.png' />";
						tdImg2 += "<input type='hidden' name='ZONE_STATUT' value='v:ok'>";
					}																		
					tdImg2 += "</td>";
					$('#LigneTableMere'+iTab+'_'+iZone).find('.td_img_1').replaceWith(tdImg1);
					$('#LigneTableMere'+iTab+'_'+iZone).find('.td_img_2').replaceWith(tdImg2);
					return false;
				}
				
				function dialogUpdateTabM(input, iTab, currentName) {
					var nomTab = jQuery('#TableMere'+iTab+' select[name=NOM_TABLE_MERE_'+iTab+'] option:selected').val();
					var updateFunc = updateTabM.bind(updateTabM, input, iTab, nomTab);
					confirmDialog('<?php echo recherche_libelle_page('Modifier'); ?> '+currentName+' -> '+nomTab+'?', updateFunc, {title:"<?php echo recherche_libelle_page('Modifier'); ?>"});
				}
				
				function updateTabM(input, iTab, nomTab) {
					var tr = jQuery(input).parent().parent();					
					var numTab = jQuery(tr).index() - 1;					
					var prio = jQuery('#TableMere'+iTab+' input[name=PRIORITE_'+iTab+']').val();	
					
					var params = 'ActionTabM=UpdTabM';
						params += '&TabMActive='+numTab;
						params += '&PRIORITE_'+numTab+'='+prio;
						params += '&NOM_TABLE_MERE_'+numTab+'='+nomTab;
					
					var url = 'server-side/include/administration/gestion_zone_service.php';
					<?php if (isset($_GET['id_theme_choisi'])) { ?>
						url += '?id_theme_choisi=<?php echo $_GET['id_theme_choisi']; ?>';
					<?php } ?>
					var callback = updateTabMData.bind(updateTabMData, iTab, nomTab);
					funcPost(url, params, callback);
				}
				
				function toggle_filtre_secteur_T_S() {
					var id_secteur     = Formulaire.filtre_secteur_T_S[Formulaire.filtre_secteur_T_S.selectedIndex].value;
					location.href   = '?val=gestzone&filtre_secteur_T_S_en_cours='+id_secteur;
						
				}
				function clear_filtre_appart_T_S() {
				   //document.form_theme.choix_affich[2].checked=true;
					setTimeout( function() {
							$( "#choix_affich_3" ).click();
						}
						,500);
				}
				function reload(choix) {
					 var id_secteur     = Formulaire.filtre_secteur_T_S[Formulaire.filtre_secteur_T_S.selectedIndex].value;
					 var id_appart     = "";
					 if(Formulaire.filtre_appart_theme){
						id_appart     = Formulaire.filtre_appart_theme[Formulaire.filtre_appart_theme.selectedIndex].value;
					 }
					 if(id_appart!="")
					 	location.href   = '?val=gestzone&filtre_secteur_T_S_en_cours='+id_secteur+'&filtre_appart_theme_en_cours='+id_appart+'&choix_affich='+choix;
					 else
						location.href   = '?val=gestzone&filtre_secteur_T_S_en_cours='+id_secteur+'&choix_affich='+choix;
					 
					 
				}
				
				function updateTabMData(iTab, nomTab) {
					//$('#Tab_'+iTab+' b').replaceWith('<b>'+nomTab+'</b>');
					window.location.reload();
				}
				
				function dialogDeteteTabM(input, iTab, nomTab) {
					var deleteFunc = deleteTabM.bind(deleteTabM, input, iTab);
					confirmDialog('<?php echo recherche_libelle_page('Supprimer'); ?> '+nomTab+'?', deleteFunc, {title:"<?php echo recherche_libelle_page('Supprimer'); ?>"});
				}
	
				function deleteTabM(input, iTab) {
					var tr = jQuery(input).parent().parent();					
					var numTab = jQuery(tr).index() - 1;
					var params = 'ActionTabM=SupTabM';
						params += '&TabMActive='+numTab;
					
					var url = 'server-side/include/administration/gestion_zone_service.php';
					<?php if (isset($_GET['id_theme_choisi'])) { ?>
						url += '?id_theme_choisi=<?php echo $_GET['id_theme_choisi']; ?>';
					<?php } ?>
					var callback = deleteTabMData.bind(deleteTabMData, iTab);
					funcPost(url, params, callback);
				}
				
				function deleteTabMData(iTab) {
					/*$('#TableMere'+iTab).remove();
					$('#Tab_'+iTab).remove();
					$('#DivTableMere'+iTab).remove();*/
					window.location.reload();
				}
				
				
				function load_champs(iTab, iZone) {		
					var ligneMere = $('#LigneTableMere'+iTab+'_'+iZone);
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);
					var params = 'ActionZone=UpdZone';
						params += '&TabMActive='+iTab;
						params += '&ZoneActive='+iZone;
						params += '&CHAMP_PERE='+ $(ligneMere).find('select[name=CHAMP_PERE]').val();
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);
					var table = $(ligneDetails).find('select[name=TABLE_FILLE]').val();
					var chp_pere = $(ligneMere).find('select[name=CHAMP_PERE]').val();
					url="administration.php?val=service_charger_chps&table="+table+"&chp_fils=&type_theme=0";
					url2="administration.php?val=service_charger_chps&val_table=&table="+table+"&chp_sql=&chp_pere="+chp_pere+"&type_theme=0";
					// Ici on va voir comment faire du get
					var funcSetChampFils = setChampFils.bind(setChampFils, iTab, iZone);
					postChamps(url, '', funcSetChampFils);
					var funcSetChampSql = setChampSql.bind(setChampSql, iTab, iZone);
					postChamps(url2, '', funcSetChampSql);
				}
				
				function setChampFils(iTab, iZone, data) {
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);					
					$.uniform.restore('#details_tr_'+iTab+'_'+iZone+' select[name=CHAMP_FILS]');
					$(ligneDetails).find('select[name=CHAMP_FILS]').replaceWith(data);	
					updateElt('#details_tr_'+iTab+'_'+iZone);
				}
				
				function setChampSql(iTab, iZone, data) {
					var ligneDetails = $('#details_tr_'+iTab+'_'+iZone);					
					$.uniform.restore('#details_tr_'+iTab+'_'+iZone+' textarea[name=SQL_REQ]');
					$(ligneDetails).find('textarea[name=SQL_REQ]').replaceWith(data);	
					updateElt('#details_tr_'+iTab+'_'+iZone);
				}
				
				function updateChampPereDepend(iTab, iZone) {
					update_champs_ref(iTab, iZone);
					load_type_champ('LigneTableMere'+iTab+'_'+iZone);
				}
				
				function update_champs_ref_type(iTab, iZone) {
					var typeObjet = $('#LigneTableMere'+iTab+'_'+iZone).find('select[name=TYPE_OBJET]').val();
					var champFille = $('#details_tr_'+iTab+'_'+iZone).find('select[name=TABLE_FILLE]').val();
					if ((champFille == '') && inArray(typeObjet, zonesListe)) {	
						update_champs_ref(iTab, iZone);
					}
				}
				
				function update_champs_ref(iTab, iZone) {
					var chp_pere = $('#LigneTableMere'+iTab+'_'+iZone).find('select[name=CHAMP_PERE]').val();
					var tableFille = chp_pere.substr('<?php echo $GLOBALS['PARAM']['CODE']; ?>_'.length);
					var typeObjet = $('#LigneTableMere'+iTab+'_'+iZone).find('select[name=TYPE_OBJET]').val();
					if (inArray(tableFille, baseTables) &&
						!inArray(chp_pere, clesSimples) &&
						(chp_pere.substring( 0, prefixTabRef.length ) === prefixTabRef) &&
						inArray(typeObjet, zonesListe)) {	
						$('#details_tr_'+iTab+'_'+iZone+' select[name=TABLE_FILLE] option[value="'+tableFille+'"]').prop('selected', true);
						$.uniform.update('#details_tr_'+iTab+'_'+iZone+' select[name=TABLE_FILLE]');
						load_champs(iTab, iZone);
					}
				}
				
				function load_type_champ(idLigMere) {		
					var ligneMere = $('#'+idLigMere);
					var chp_pere = $(ligneMere).find('select[name=CHAMP_PERE]').val();
					if (chp_pere == '') {
						$('#'+id+' select[name=TYPE_ZONE_BASE] option[value="text"]').prop('selected', true);
						$.uniform.update('#'+id+' select[name=TYPE_ZONE_BASE]');
					} else {						
						var id_chp = $(ligneMere).find('select[name=CHAMP_PERE] option:selected').attr('id');
						url="administration.php?val=service_charger_chps&champ="+chp_pere+"&id_chp="+id_chp+"&type_chp=int&type_theme=0";
						var funcSetChampType = setChampType.bind(setChampType, idLigMere);
						postChamps(url, '', funcSetChampType);
					}
				}
				
				function setChampType(id, data) {
					var ligneMere = $('#'+id);					
					$.uniform.restore('#'+id+' select[name=TYPE_ZONE_BASE]');
					$(ligneMere).find('select[name=TYPE_ZONE_BASE]').replaceWith(data);	
					$('#'+id+' select[name=TYPE_ZONE_BASE]').uniform();
				}
				
				// test si les données de la table mère sont correctent avant l'ouverture de la boite de dialogue
				function insererTableMere(iTab, id_theme, id_systeme) {
					var tm = jQuery('#TableMere'+iTab+' select[name=NOM_TABLE_MERE_'+iTab+'] option:selected').val();
					var prio = jQuery('#TableMere'+iTab+' input[name=PRIORITE_'+iTab+']').val();
					
					var params = 'ActionNewTabM=ValidNewTabMData';
						params += '&tableMere='+tm;
						params += '&priorite='+prio;
						params += '&idTheme='+id_theme;
					
					var url = 'server-side/include/administration/gestion_zone_service.php';
					var callback = openTableMereDialog.bind(openTableMereDialog, iTab, id_theme, id_systeme);
					funcPost(url, params, callback);
				}
		
				// Ouvre une boite de dialogue pour afficher les champs de la table à ajouter
				function openTableMereDialog(iTab, id_theme, id_systeme, validRps) {
					if (validRps != 'NewTabOk') {
						$.alert(validRps, '_');
					} else {
						var tm = jQuery('#TableMere'+iTab+' select[name=NOM_TABLE_MERE_'+iTab+'] option:selected').val();
						var prio = jQuery('#TableMere'+iTab+' input[name=PRIORITE_'+iTab+']').val();
						var numTab = jQuery('#table_mere_liste tr').length - 2;
						open_dialog('popinstabmere', 1110, 100, 'administration.php?val=OpenPopupInsererTableMere&numTab='+numTab+'&iTab='+iTab+'&tm='+tm+'&p='+prio+'&th='+id_theme+'&id_sys='+id_systeme+'&typeTheme='+typeTheme);
					}
				}
				
				function validateAllZones() {
					if ($('.details_tr').is(':visible')) {
						$.alert('<?php echo recherche_libelle_page('EnregModifZone'); ?>', '_');
						return false;
					}
					for (var i = 0; i < <?php echo $this->nb_TabM; ?>; i++) {
						if ($('#zone_table_'+i).exists()) {
							$('#zone_table_'+i).find('tr.ligneMere').each(function() {
								var suffix = $(this).attr('id').substr(14);
								var iTab = suffix.substr(0,suffix.indexOf('_'));
								var iZone = suffix.substr(suffix.indexOf('_')+1);
								$('#zone_statut_'+iTab+'_'+iZone).attr('src','<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_zone_loading.gif');
								if (!$('#details_tr_'+suffix+' td div').exists()) {
									var params = 'ActionZone=AddZoneDetails';
									params += '&TabMActive='+iTab;
									params += '&ZoneActive='+iZone;
									
									var funcCallback = adddetailsToValid.bind(adddetailsToValid, iTab, iZone);
									postZone(params, funcCallback);	
								} else {
									if(!validateZone(null, iTab, iZone, true, false)) {										
										displayBtnNonValidZone();
									}
									updateZoneStatut(iTab, iZone);
								}
							});
						}
					}
				}
				
				function adddetailsToValid(iTab, iZone, details) {					
					$('#details_tr_'+iTab+'_'+iZone).replaceWith(details);
					
					if(!validateZone(null, iTab, iZone, true, false)) {										
						displayBtnNonValidZone();
					}
					updateZoneStatut(iTab, iZone);
				}
				
				function updateZoneStatut(iTab, iZone) {
					var IDZone = $('#LigneTableMere'+iTab+'_'+iZone).find('input[name=ID_ZONE]').val();					
					var zoneStatut = $('#LigneTableMere'+iTab+'_'+iZone).find('input[name=ZONE_STATUT]').val();
					
					var params = 'ActionZoneStatut=UpdZoneStatus';
						params += '&ID_ZONE='+IDZone;
						params += '&ZONE_STATUT='+zoneStatut;
					
					var url = 'server-side/include/administration/gestion_zone_service.php';
					funcPost(url, params, null);
				}
				
				// Valide la configuration d'une zone
				function validateZone(IDZone, iTab, iZone, isUpdate, displayMsg) {					
					var tabMereLineId =  isUpdate?'#LigneTableMere'+iTab+'_'+iZone:'#ligneAddZone_'+iTab;
					var ligneMere = $(tabMereLineId);	
					
					var champPere = $(ligneMere).find('select[name=CHAMP_PERE]').val();
					var typeObjet = $(ligneMere).find('select[name=TYPE_OBJET]').val();
					var typeChamp = $(ligneMere).find('select[name=TYPE_ZONE_BASE]').val();
					
					var tableFille = '';
					var champFils = '';
					var sql = '';
					var img = null;
					var imgPath = '<?php echo $GLOBALS['SISED_URL_IMG']; ?>';
					var ligneDetails = null;
					if (isUpdate) {		
						ligneDetails = $('#details_tr_'+iTab+'_'+iZone);
						tableFille = $(ligneDetails).find('select[name=TABLE_FILLE]').val();
						champFils = $(ligneDetails).find('select[name=CHAMP_FILS]').val();
						sql = $(ligneDetails).find('textarea[name=SQL_REQ]').val();
						img = $('#zone_statut_'+iTab+'_'+iZone);
					}
					
					// Hors mis le type libellé affiché, une zone doit avoir un champ père
					if (champPere.trim().length == 0 && typeObjet != 'label') {
						if (displayMsg) {
							$.alert("<?php echo recherche_libelle_page('err_champ_pere'); ?>", '_');
						}
						if (isUpdate) {
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_champ_pere'); ?>");
						}
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_champ_pere');
						return false;
					}
					
					// Un champ père n'est utilisé qu'une fois sauf pour une réutilisation comme un libellé affiché
					if (typeObjet != 'label') {
						var error = false;
						var currentZoneIndex = -1;
						if (isUpdate) {
							currentZoneIndex = $('#zone_table_'+iTab+' .ligneMere').index($(tabMereLineId));
						} else {
							currentZoneIndex = $('#zone_table_'+iTab+' .ligneMere').length;
						}
						$('#zone_table_'+iTab+' select[name=CHAMP_PERE]').each(function(index) {
							if (($(this).val() == champPere) && (currentZoneIndex != index)) {
								var type = $(this).closest('tr').find('select[name=TYPE_OBJET]').val();
								if (type != 'label') {									
									error = true;
									return error;
								}
							}
						});
						if (error) {
							if (displayMsg) {
								$.alert("<?php echo recherche_libelle_page('err_champ_exist'); ?>", '_');
							}
							if (isUpdate) {
								$(img).attr('src', imgPath+'b_zone_r.png');
								$(img).attr('title', "<?php echo recherche_libelle_page('err_champ_exist'); ?>");
							}
							$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_champ_exist');
							return false;
						}
					}
					// Tout champ excepté CODE_TYPE_SYSTEME doit avoir un comportement
					if ((champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>') && (typeObjet == '')) {
						if (displayMsg) {
							$.alert("<?php echo recherche_libelle_page('err_no_comp'); ?>", '_');
						}
						if (isUpdate) {
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_no_comp'); ?>");
						}
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_no_comp');
						return false;
					}
					// Le champ code_regroupement doit avoir le type d'affichage champ_localisation
					if (champPere == '<?php echo $GLOBALS['PARAM']['CODE']."_".$GLOBALS['PARAM']['REGROUPEMENT']; ?>' && typeObjet != 'loc_etab') {
						if (displayMsg) {
							$.alert("<?php echo recherche_libelle_page('err_code_regroupement'); ?>", '_');
						}
						if (isUpdate) {
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_code_regroupement'); ?>");
						}
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_code_regroupement');
						return false;
					}
					
					// Le champ code_type_system ne doit pas avoir de type d'affichage
					if (champPere == '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>' && typeObjet.length > 0) {
						if (displayMsg) {
							$.alert("<?php echo recherche_libelle_page('err_champ_code_type_system'); ?>", '_');
						}
						if (isUpdate) {
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_champ_code_type_system'); ?>");
						}
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_champ_code_type_system');
						return false;
					}
					
					//Comportemants correspondant à un type numérique
					if (inArray(typeObjet, compTypeNumerique)) {
						if(typeChamp != 'int') {
							if (displayMsg) {
								$.alert("<?php echo recherche_libelle_page('err_comp_numerique'); ?>", '_');
							}
							if (isUpdate) {
								$(img).attr('src', imgPath+'b_zone_r.png');
								$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_numerique'); ?>");
							}
							$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_numerique');
							return false;
						}
					}
					//Comportemants devant avoir une table fille
					if (inArray(typeObjet, compTableFille) && isUpdate) {
						if (tableFille.length == 0 || champFils.length == 0 || sql.length == 0) {	
							var error = "";	
							var error_lib = "";	
							if (inArray(typeObjet, compChampLie)) {
								var foncInter = $(ligneDetails).find('textarea[name=FONCTION_INTERFACE]').val();
								var champInter = $(ligneDetails).find('input[name=CHAMP_INTERFACE]').val();
								var requete = $(ligneDetails).find('textarea[name=REQUETE_CHAMP_INTERFACE]').val();
								if (champInter.length == 0 || (foncInter.length == 0 && requete.length == 0)) {
									error = "err_comp_requeteinterface";
									error_lib = "<?php echo recherche_libelle_page('err_comp_requeteinterface'); ?>";
								} else if ((compTableFille == 'liste_radio') && ($(ligneDetails).find('input[name=BOUTON_INTERFACE]').val() != 'popup')) {
									error = "err_comp_liste_radio_popup";
									error_lib = "<?php echo recherche_libelle_page('err_comp_liste_radio_popup'); ?>";
								}
							} else {
								error = "err_comp_tablefille";
								error_lib = "<?php echo recherche_libelle_page('err_comp_tablefille'); ?>";
							}
							if (error.length > 0) {
								if (displayMsg) {
									$.alert(error_lib, '_');
								}
								$(img).attr('src', imgPath+'b_zone_r.png');
								$(img).attr('title', error_lib);
								$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+error);
								return false;
							}
						}
					} else if (inArray(typeObjet, compTableFille) && champPere.substring( 0, prefixTabRef.length ) != prefixTabRef) {
						$(img).attr('src', imgPath+'b_zone_r.png');
						$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_tablefille'); ?>");
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_tablefille');
						insertZone(iTab, iZone);
						return false;
					}
					// Si comportement systeme_valeur_multiple alors mat_grille
					//Modif Hebié: desormais le systeme_valeur_multiple est utilisable dans un thème autre que mat grille
					/*if (typeObjet == 'systeme_valeur_multiple') {
						if(typeTheme != 'Mat_Grille') {
							if (displayMsg) {
								$.alert("<?php echo recherche_libelle_page('err_comp_svmmg'); ?>", '_');
							}
							if (isUpdate) {
								$(img).attr('src', imgPath+'b_zone_r.png');
								$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_svmmg'); ?>");
							}
							$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_svmmg');
							return false;
						}
					}*/
					// Si comportement text_valeur_multiple alors mat_grille
					if (typeObjet == 'text_valeur_multiple' && isUpdate) {
						var zone_ref = $(ligneDetails).find('select[name=ID_ZONE_REF]').val();
						if(typeTheme != 'Mat_Grille' && zone_ref.length == 0) {
							if (displayMsg) {
								$.alert("<?php echo recherche_libelle_page('err_comp_tvm'); ?>", '_');
							}
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_tvm'); ?>");
							$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_tvm');
							return false;
						}
					} else if (typeObjet == 'text_valeur_multiple' && typeTheme != 'Mat_Grille') {
						$(img).attr('src', imgPath+'b_zone_r.png');
						$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_tvm'); ?>");
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_tvm');
						insertZone(iTab, iZone);
						return false;
					}
					
					// Si comportement systeme_valeur_unique alors boutonI<>0 et champI<>0 et fonctionI<>0 et requeteI<>0
					if (typeObjet == 'systeme_valeur_unique' && isUpdate) {
						var boutonI = $(ligneDetails).find('input[name=BOUTON_INTERFACE]').val();
						//var tableI = $(ligneDetails).find('input[name=TABLE_INTERFACE]').val();
						var champI = $(ligneDetails).find('input[name=CHAMP_INTERFACE]').val();
						var fonctionI = $(ligneDetails).find('textarea[name=FONCTION_INTERFACE]').val();
						var requeteI = $(ligneDetails).find('textarea[name=REQUETE_CHAMP_INTERFACE]').val();
						//var requeteS = $(ligneDetails).find('textarea[name=REQUETE_CHAMP_SAISIE]').val();
						if(boutonI.length == 0 || champI.length == 0 || fonctionI.length == 0 || requeteI.length == 0) {
							if (displayMsg) {
								$.alert("<?php echo recherche_libelle_page('err_comp_svucl'); ?>", '_');
							}
							$(img).attr('src', imgPath+'b_zone_r.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_svucl'); ?>");
							$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_svucl');
							return false;
						}
					} else if (typeObjet == 'systeme_valeur_unique') {
						$(img).attr('src', imgPath+'b_zone_r.png');
						$(img).attr('title', "<?php echo recherche_libelle_page('err_comp_svucl'); ?>");
						$(ligneMere).find('input[name=ZONE_STATUT]').val('r:'+'err_comp_svucl');
						insertZone(iTab, iZone);
						return false;
					}
					// Champ contenant CODE_TYPE_*
					if (champPere.substring( 0, prefixTabRef.length ) === prefixTabRef) {		
						var funcConfirm = null;
						if (isUpdate) {
							funcConfirm = updateZone.bind(updateZone, IDZone, iTab, iZone);				
						} else {
							funcConfirm = insertZone.bind(insertZone, iTab, iZone);			
						}
						if(!inArray(champPere, clesSimples) && (champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>') && !inArray(typeObjet, compCodeType)) {
							if (isUpdate) {
								$(img).attr('src', imgPath+'b_zone_o.png');
								$(img).attr('title', "<?php echo recherche_libelle_page('err_code_type_comp'); ?>");
							}
							$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_type_comp');
							if (displayMsg) {
								return confirmDialog("<?php echo recherche_libelle_page('err_code_type_comp'); ?>. <?php echo recherche_libelle_page('Enregistrer'); ?>?", funcConfirm, {title:"_"});
							}
							return false;
						}
						if(typeChamp != 'int') {
							if (isUpdate) {
								$(img).attr('src', imgPath+'b_zone_o.png');
								$(img).attr('title', "<?php echo recherche_libelle_page('err_code_type_type_int'); ?>");
							}
							$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_type_type_int');
							if (displayMsg) {
								return confirmDialog("<?php echo recherche_libelle_page('err_code_type_type_int'); ?>.<br/><?php echo recherche_libelle_page('Enregistrer'); ?> ?", funcConfirm, {title:"_"});
							}
							return false;
						}
						if (isUpdate && !inArray(champPere, clesSimples) && (champPere != '<?php echo $GLOBALS['PARAM_SYS']['CODE_TYPE_SYSTEME']; ?>') && (tableFille.length == 0 || champFils.length == 0 || sql.length == 0)) {
							$(img).attr('src', imgPath+'b_zone_o.png');
							$(img).attr('title', "<?php echo recherche_libelle_page('err_code_type_tablefille'); ?>");
							$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+'err_code_type_tablefille');
							if (displayMsg) {
								return confirmDialog("<?php echo recherche_libelle_page('err_code_type_tablefille'); ?>. <br/> <?php echo recherche_libelle_page('Enregistrer'); ?> ?", funcConfirm, {title:"_"});
							}
							return false;
						}				
					} 	
					if (isUpdate) {
						$(img).attr('src', imgPath+'b_zone_v.png');
						$(img).attr('title', "<?php echo recherche_libelle_page('TitreZoneOk'); ?>");
					} 
					if ($(img).attr('src') == imgPath + 'b_zone_o.png') {
						$(ligneMere).find('input[name=ZONE_STATUT]').val('o:'+$(img).attr('title'));
					} else {
						$(ligneMere).find('input[name=ZONE_STATUT]').val('v:'+"<?php echo recherche_libelle_page('TitreZoneOk'); ?>");
					}
					if (!isUpdate) {
						insertZone(iTab, iZone);
					}
					displayBtnNonValidZone();
					return true;
				}
			 //--> 
			</script>
		<?php }
		
		/**
		* METHODE : get_themes()
		* <pre>
		* fonction de récupération des thèmes 
		* </pre>
		* @access public
		* 
		*/
		function get_themes(){
			if((isset($_SESSION['choix_affich']) && $_SESSION['choix_affich']=='active_themes_rattach')){
				if(isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']!=''){
					$secteur=$_GET['filtre_secteur_T_S_en_cours'];
				}elseif(isset($_SESSION['filtre_secteur_T_S']) && $_SESSION['filtre_secteur_T_S']!='')
					$secteur=$_SESSION['filtre_secteur_T_S'];
				
				$appart = 0;
				if(isset($_GET['filtre_appart_theme_en_cours']) && $_GET['filtre_appart_theme_en_cours']!=''){
					$appart=$_GET['filtre_appart_theme_en_cours'];
				}elseif(isset($_SESSION['filtre_appart_T_S']) && $_SESSION['filtre_appart_T_S']!='')
					$appart=$_SESSION['filtre_appart_T_S'];
					
				$requete 	=	" SELECT    DICO_THEME.ID , 
										DICO_THEME.ID_TYPE_THEME , 
										DICO_TYPE_THEME.LIBELLE AS LIBELLE_TYPE_THEME ,
										DICO_TRADUCTION.LIBELLE ,
										DICO_THEME_SYSTEME.ID_SYSTEME,
										DICO_THEME_SYSTEME.APPARTENANCE
										FROM    DICO_THEME, DICO_TYPE_THEME, DICO_TRADUCTION, DICO_THEME_SYSTEME
										WHERE	DICO_THEME.ID_TYPE_THEME=DICO_TYPE_THEME.ID_TYPE_THEME 
										AND 	DICO_TYPE_THEME.CODE_LANGUE = '".$this->langue."'
										AND 	DICO_THEME.ID=DICO_TRADUCTION.CODE_NOMENCLATURE 		
										AND 	DICO_THEME_SYSTEME.ID=DICO_THEME.ID
										AND 	DICO_THEME_SYSTEME.ID_SYSTEME=".$secteur."
										AND 	DICO_THEME_SYSTEME.APPARTENANCE=".$appart."
										AND		DICO_TRADUCTION.NOM_TABLE = 'DICO_THEME' 
										AND 	DICO_TRADUCTION.CODE_LANGUE = '".$this->langue."' 
										AND 	DICO_THEME.ID_TYPE_THEME <> 8
										ORDER BY DICO_THEME.ID ";				
			}elseif((isset($_SESSION['choix_affich']) && $_SESSION['choix_affich']=='active_themes')){
				if(isset($_GET['filtre_secteur_T_S_en_cours']) && $_GET['filtre_secteur_T_S_en_cours']!=''){
					$secteur=$_GET['filtre_secteur_T_S_en_cours'];
				}elseif(isset($_SESSION['filtre_secteur_T_S']) && $_SESSION['filtre_secteur_T_S']!='')
					$secteur=$_SESSION['filtre_secteur_T_S'];
					
				$requete 	=	" SELECT    DICO_THEME.ID , 
										DICO_THEME.ID_TYPE_THEME , 
										DICO_TYPE_THEME.LIBELLE_TRAD AS LIBELLE_TYPE_THEME ,
										DICO_TRADUCTION.LIBELLE ,
										DICO_THEME_SYSTEME.ID_SYSTEME,
										DICO_THEME_SYSTEME.APPARTENANCE
										FROM    DICO_THEME, DICO_TYPE_THEME, DICO_TRADUCTION, DICO_THEME_SYSTEME
										WHERE	DICO_THEME.ID_TYPE_THEME=DICO_TYPE_THEME.ID_TYPE_THEME 
										AND 	DICO_TYPE_THEME.CODE_LANGUE = '".$this->langue."'
										AND 	DICO_THEME.ID=DICO_TRADUCTION.CODE_NOMENCLATURE 		
										AND 	DICO_THEME_SYSTEME.ID=DICO_THEME.ID
										AND 	DICO_THEME_SYSTEME.ID_SYSTEME=".$secteur."
										AND		DICO_TRADUCTION.NOM_TABLE = 'DICO_THEME' 
										AND 	DICO_TRADUCTION.CODE_LANGUE = '".$this->langue."' 
										AND 	DICO_THEME.ID_TYPE_THEME <> 8
										ORDER BY DICO_THEME.ORDRE_THEME;";
			}else{
			
				$requete 	=	" SELECT    DICO_THEME.ID , 
										DICO_THEME.ID_TYPE_THEME , 
										DICO_TYPE_THEME.LIBELLE_TRAD AS LIBELLE_TYPE_THEME ,
										DICO_TRADUCTION.LIBELLE ,
										DICO_THEME_SYSTEME.ID_SYSTEME,
										DICO_THEME_SYSTEME.APPARTENANCE
										FROM    DICO_THEME, DICO_TYPE_THEME, DICO_TRADUCTION, DICO_THEME_SYSTEME
										WHERE	DICO_THEME.ID_TYPE_THEME=DICO_TYPE_THEME.ID_TYPE_THEME 
										AND 	DICO_TYPE_THEME.CODE_LANGUE = '".$this->langue."'
										AND 	DICO_THEME.ID=DICO_TRADUCTION.CODE_NOMENCLATURE 		
										AND 	DICO_THEME_SYSTEME.ID=DICO_THEME.ID
										AND		DICO_TRADUCTION.NOM_TABLE = 'DICO_THEME' 
										AND 	DICO_TRADUCTION.CODE_LANGUE = '".$this->langue."' 
										AND 	DICO_THEME.ID_TYPE_THEME <> 8
										ORDER BY DICO_THEME.ORDRE_THEME;";
										//ORDER BY DICO_TRADUCTION.LIBELLE ";
			}																		
			//echo $requete.'<br>' ;
			$this->TabGestZones['themes']  = $GLOBALS['conn_dico']->GetAll($requete);
			if(!isset($this->id_theme)){
				$this->id_theme 			= $this->TabGestZones['themes'][0]['ID'];
				$this->id_type_theme 		= $this->TabGestZones['themes'][0]['ID_TYPE_THEME'];
				$this->libelle_type_theme 	= $this->TabGestZones['themes'][0]['LIBELLE_TYPE_THEME'];
				$this->libelle_theme		= $this->TabGestZones['themes'][0]['LIBELLE'];
				$this->id_theme_systeme     = $this->TabGestZones['themes'][0]['ID_SYSTEME'];
				$this->id_theme_appart		= $this->TabGestZones['themes'][0]['APPARTENANCE'];
				$_SESSION['filtre_appart_T_S'] = $this->id_theme_appart;
			}
			if(isset($_GET['id_theme_choisi'])){
				$this->id_theme 			= $_GET['id_theme_choisi'];
				foreach($this->TabGestZones['themes'] as $i => $themes){
					if( $themes['ID'] == $_GET['id_theme_choisi'] ){
						$this->id_type_theme 			= $themes['ID_TYPE_THEME'];
						$this->libelle_type_theme = $themes['LIBELLE_TYPE_THEME'];
						$this->libelle_theme 			= $themes['LIBELLE'];
						$this->id_theme_systeme     = $themes['ID_SYSTEME'];
						$this->id_theme_appart		= $themes['APPARTENANCE'];
						break;	
					}
				}
				$_SESSION['filtre_secteur_T_S'] = $this->id_theme_systeme;
				$_SESSION['filtre_appart_T_S'] = $this->id_theme_appart;
				if($this->id_theme_systeme != $_SESSION['secteur']){
					$_SESSION['secteur'] =  $this->id_theme_systeme;
					unset ($_SESSION['hierarchie_regroup']); 
					unset ($_SESSION['infos_etab']);
				}
				
			//Ajout HEBIE pour gerer l'affichage par defaut du theme en cours de saisie au niveau de la gestion des zones
			}elseif(isset($_SESSION['theme']) && $_SESSION['theme']!='' && (!isset($_GET['filtre_secteur_T_S_en_cours']))){
				$long_syst_id=strlen(''.$_SESSION['secteur']);
				$long_theme_syst_id=strlen(''.$_SESSION['theme']);
				$long_theme_id=$long_theme_syst_id-$long_syst_id;
				$str_theme_id=substr($_SESSION['theme'],0,$long_theme_id);
				$this->id_theme 			= $str_theme_id;
				foreach($this->TabGestZones['themes'] as $i => $themes){
					if( $themes['ID'] == $str_theme_id ){
						$this->id_type_theme 			= $themes['ID_TYPE_THEME'];
						$this->libelle_type_theme = $themes['LIBELLE_TYPE_THEME'];
						$this->libelle_theme 			= $themes['LIBELLE'];
						$this->id_theme_systeme     = $themes['ID_SYSTEME'];
						$this->id_theme_appart		= $themes['APPARTENANCE'];
						$_SESSION['filtre_appart_T_S'] = $this->id_theme_appart;
						break;	
					}
				}
			}
			//Fin Ajout HEBIE
			if($this->id_type_theme == 1){
				$this->get_liste_dimensions();
				$this->get_zone_matrice();
			}
			else{
				$this->get_tables_meres_theme();
				$this->get_zones_saisie(); 
			}
			//$this->get_meta_type_zone();
			//$this->get_systemes();
			$this->set_champs_zone();
			//print_r($this->TabGestZones['themes']);
		} //FIN get_themes()
		
		/**
		* METHODE : get_cles()
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*/
		function get_cles(){/// get_systemes()
			// Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
			// TODO: à virer de l'accueil
			//'.$GLOBALS['PARAM'][''].'
			$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE
										FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										And D_TRAD.NOM_TABLE="'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'" And D_TRAD.CODE_LANGUE="'.$_GET['langue'].'";';
			$this->TabGestZones['systemes'] = $this->conn->GetAll($requete);
		} // FIN  get_systemes()

		/**
		* METHODE : 
		* <pre>
		* 
		* </pre>
		* @access public
		* 
		*//*
		function get_systemes(){/// get_systemes()
			// Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
			// TODO: à virer de l'accueil
			$requete                 = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', D_TRAD.LIBELLE
										FROM '.$GLOBALS['PARAM']['TYPE'].'_'.$GLOBALS['PARAM']['SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
										WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
										And D_TRAD.NOM_TABLE="'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'" And D_TRAD.CODE_LANGUE="'.$_GET['langue'].'";';
			$this->TabGestZones['systemes'] = $this->conn->GetAll($requete);
		} */// FIN  get_systemes()
		
		/**
		* METHODE : get_types_donnees()
		* <pre>
		* fonction de récupération des types de zones <> matrice
		* </pre>
		* @access public
		* 
		*/
		function get_types_donnees(){

				$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
												FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES < 10";

				$_SESSION['GestZones']['type_donnees'] 		= $GLOBALS['conn_dico']->GetAll($requete);

				//echo $requete;
				//echo'<pre>';
				//print_r($_SESSION['GestZones']['zones']);		
		} 	//FIN get_zones()
		
		/**
		* METHODE : get_types_donnees_saisie_mat()
		* <pre>
		*  fonction de récupération des types de zones matrice
		* </pre>
		* @access public
		* 
		*/
		function get_types_donnees_saisie_mat(){

					$requete	="	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
												FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES  >= 10 ";

				$_SESSION['GestZones']['type_donnees'] 		= $GLOBALS['conn_dico']->GetAll($requete);

				//echo $requete;
				//echo'<pre>';
				//print_r($_SESSION['GestZones']['zones']);		
		} 	//FIN get_zones()
		
		/**
		* METHODE : get_liste_dimensions()
		* <pre>
		* fonction de récupération de ttes les dimensions
		* </pre>
		* @access public
		* 
		*/
		function get_liste_dimensions(){/// get_liste_dimensions()
	
			$requete                 = 'SELECT   *  FROM       DICO_DIMENSION';
			$this->TabGestZones['dimensions'] = $GLOBALS['conn_dico']->GetAll($requete);
		} // FIN  get_systemes()
		
		/**
		* METHODE : get_dimensions_matrice()
		* <pre>
		* Récupération des dimensions relatives à la zone matrice
		* </pre>
		* @access public
		* 
		*/
		function get_dimensions_matrice(){
			foreach( $this->TabGestZones['zone_matrice'] as $i => $tab){
				$this->TabGestZones['dimension_matrice'][$tab['TYPE_DIMENSION']]['ID_DIMENSION'] 				= $tab['ID_DIMENSION'];
				$this->TabGestZones['dimension_matrice'][$tab['TYPE_DIMENSION']]['NB_LIGNES_DIMENSION'] = $tab['NB_LIGNES_DIMENSION'];
			}	
		}
		
		/**
		* METHODE : set_champs_zone()
		* <pre>
		* Préparation des champs de la table DICO_ZONE 
		* </pre>
		* @access public
		* 
		*/
		function set_champs_zone() {///// 
				if(!($this->champs_zone)) {
						$this->champs_zone 		= array();
						if($this->id_type_theme <> 1){
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE', 'type' => 'int'		, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'TABLE_MERE', 'type' => 'text'	, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'NOM_GROUPE', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'TABLE_FILLE', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'ORDRE', 	'type' => 'int' 	, 'manip' => true, 'lib'=>'OrdMet', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'SQL_REQ', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'CHAMP_FILS', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'CHAMP_PERE', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ORDRE_TRI', 	'type' => 'int'		, 'manip' => true, 'lib'=>'OrdTri', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE_REF', 'type' => 'int'		, 'manip' => true, 'lib'=>'ZoneRef', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'TYPE_ZONE_BASE', 'type' => 'text'	, 'manip' => true, 'lib'=>'', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE', 'type' => 'text'	, 'manip' => false, 'lib'=>'', 'obli'=>''	);
								$this->champs_zone[] 	= array( 'nom' => 'ZONE_STATUT', 'type' => 'text','manip' => true, 'lib'=>'', 'obli'=>''	);
						}
						else{
								$this->champs_zone[] 	= array( 'nom' => 'ID_ZONE', 'type' => 'int'		, 'manip' => false, 'lib'=>'', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'TABLE_MERE', 'type' => 'text'	, 'manip' => true, 'lib'=>'TabMere', 'obli'=>'1'	);
								$this->champs_zone[] 	= array( 'nom' => 'MESURE1', 'type' => 'text'	, 'manip' => true, 'lib'=>'mes1', 'obli'=>'1'		);
								$this->champs_zone[]	= array( 'nom' => 'MESURE2', 'type' => 'text'	, 'manip' => true, 'lib'=>'mes2', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'TYPE_SAISIE_MESURE1', 'type' => 'text', 'manip' => true, 'lib'=>'mes1', 'obli'=>'1'		);
								$this->champs_zone[]	= array( 'nom' => 'TYPE_SAISIE_MESURE2', 'type' => 'text', 'manip' => true, 'lib'=>'mes2', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'ID_DIMENSION1', 'type' => 'int', 'manip' => false, 'lib'=>'choixdim', 'obli'=>''		);
								$this->champs_zone[] 	= array( 'nom' => 'ID_DIMENSION2', 'type' => 'int', 'manip' => false, 'lib'=>'choixdim', 'obli'=>''		);
								$this->champs_zone[]	= array( 'nom' => 'NB_LIGNES_DIMENSION1', 'type' => 'int', 'manip' => false, 'lib'=>'nblidim', 'obli'=>'');
								$this->champs_zone[] 	= array( 'nom' => 'NB_LIGNES_DIMENSION2', 'type' => 'int', 'manip' => false, 'lib'=>'nblidim', 'obli'=>'');
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE_MESURE1', 'type' => 'text','manip' => false, 'lib'=>'libmes1', 'obli'=>''	);
								$this->champs_zone[] 	= array( 'nom' => 'LIBELLE_MESURE2', 'type' => 'text','manip' => false, 'lib'=>'libmes2', 'obli'=>''	);
								$this->champs_zone[] 	= array( 'nom' => 'ZONE_STATUT', 'type' => 'text','manip' => true, 'lib'=>'', 'obli'=>''	);
						}
				}
		}
		
		/**
		* METHODE : get_tables_db()
		* <pre>
		* Récupération des tables de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_tables_db(){
		
				$this->TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
				$this->ViewsBD = $this->conn->MetaTables("VIEWS");
				sort($this->TabBD);
				$tables = array();
				$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
				$deb_fact_tab = 'FACT_TABLE_';
				$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
				foreach($this->TabBD as $tab)
				{
					//Modif HEBIE 02/02/2017 Bissau: Possibilité d'utiliser une requete comme table mère
					if(isset($GLOBALS['PARAM_SYS']['SHOW_VIEWS_IN_MOTHER_TABLES_LIST']) && $GLOBALS['PARAM_SYS']['SHOW_VIEWS_IN_MOTHER_TABLES_LIST']==true){
						if(!preg_match("/^($deb_fact_tab)/i", $tab) && !preg_match("/^($deb_nomenc)/i", $tab) && !preg_match("/($fin_nomenc_system)$/i", $tab) && !preg_match("/^(DICO_)/i", $tab) && !preg_match("/^(ADMIN_DROITS)/i", $tab) && !preg_match("/^(ADMIN_GROUPES)/i", $tab) && !preg_match("/^(ADMIN_USERS)/i", $tab) && !preg_match("/^(PARAM_DEFAUT)/i", $tab) && !preg_match("/^(SYSTEME)/i", $tab))
						{
							$tables[] = $tab;
						}
					}else{
						if(!in_array($tab,$this->ViewsBD)){
							if(!preg_match("/^($deb_fact_tab)/i", $tab) && !preg_match("/^($deb_nomenc)/i", $tab) && !preg_match("/($fin_nomenc_system)$/i", $tab) && !preg_match("/^(DICO_)/i", $tab) && !preg_match("/^(ADMIN_DROITS)/i", $tab) && !preg_match("/^(ADMIN_GROUPES)/i", $tab) && !preg_match("/^(ADMIN_USERS)/i", $tab) && !preg_match("/^(PARAM_DEFAUT)/i", $tab) && !preg_match("/^(SYSTEME)/i", $tab))
							{
								$tables[] = $tab;
							}
						}
					}
				}
				$this->TabBD = $tables;
				
		} //FIN get_tables_db()
		
		/**
		* METHODE : get_tables_nomenc_db()
		* <pre>
		* Récupération des tables nomenclature de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_tables_nomenc_db(){
		
				$this->TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
				$tables = array();
				$deb_nomenc = $GLOBALS['PARAM']['TYPE'].'_';
				$fin_nomenc_system = '_'.$GLOBALS['PARAM']['SYSTEME'];
				foreach($this->TabBD as $tab)
				{	
					if(preg_match("/^($deb_nomenc)/i", $tab) && !preg_match("/($fin_nomenc_system)$/i", $tab))
					{
						$tables[] = $tab;
					}
				}
				$this->TabBD = $tables;
				
		} //FIN get_tables_nomenc_db()
		
		/**
		* METHODE : get_col_table_db()
		* <pre>
		* Récupération des colonnes d'une table de la base de données 
		* </pre>
		* @access public
		* 
		*/
		function get_col_table_db($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumnNames($table);
				
		} //FIN get_tables_db()
		
		function get_col_table_db_2($table){
		
				$this->ColTabBD	=	$this->conn->MetaColumns($table);
				
		} //FIN get_tables_db_2()
		
		/**
		* METHODE : get_tables_meres_theme()
		* <pre>
		* Récupération des tables mères du thème
		* </pre>
		* @access public
		* 
		*/
		function get_tables_meres_theme(){
				$requete 	=	' SELECT    ID_TABLE_MERE_THEME , 
											NOM_TABLE_MERE , 
											PRIORITE 
											FROM       DICO_TABLE_MERE_THEME
											WHERE      ID_THEME ='.$this->id_theme.'
											ORDER BY 	 PRIORITE';
		
				$this->TabGestZones['tables_meres']  = $GLOBALS['conn_dico']->GetAll($requete);
				$this->nb_TabM = count($this->TabGestZones['tables_meres']);
				
		} //FIN get_tables_meres_theme()
		
		
		/**
		* METHODE : exist_tables_meres_theme()
		* <pre>
		* permet de déterminer l'existence ou non de tables mères pour le thème
		* </pre>
		* @access public
		* 
		*/
		function exist_tables_meres_theme() {
			$exist_TabM = false ;
			$requete 	= ' SELECT  * FROM  DICO_ZONE WHERE  ID_THEME = ' . $this->id_theme;
			$all_test   = $GLOBALS['conn_dico']->GetAll($requete);
			if(is_array($all_test) and count($all_test)){
				$exist_TabM = true ;
			}
			return($exist_TabM);
			//$this->nb_TabM = count($this->TabGestZones['tables_meres']);
		} 	//FIN exist_tables_meres_theme()

		///// fonction de récupération des zones du theme
		
		/**
		* METHODE : get_zone_matrice()
		* <pre>
		*  Récupère les caractéristiques de la matrice
		* </pre>
		* @access public
		* 
		*/
		function get_zone_matrice(){
		
				$requete	=	"	SELECT     *
									FROM       DICO_ZONE, DICO_DIMENSION_ZONE
									WHERE      DICO_ZONE.ID_ZONE = DICO_DIMENSION_ZONE.ID_ZONE
									AND		   DICO_ZONE.ID_THEME = " . $this->id_theme;
				//echo $requete;						
				$all_res  = $GLOBALS['conn_dico']->GetAll($requete);
				$this->TabGestZones['zone_matrice'] = array();
				if( count($all_res) > 0 ){
						$this->TabGestZones['zone_matrice'] = $all_res;
						$this->get_libelles_zone_matrice();
						$this->get_dimensions_matrice();
						$this->TabGestZones['zones'][0][0] = $all_res[0];
				}
				else{
						$this->btn_add = true; 
				}
				
				//echo'<pre>';
				//print_r($this->TabGestZones['zones']);		
		} //FIN get_zones()
		
		
		/**
		* METHODE : get_libelles_zone_matrice()
		* <pre>
		* Récupère les libellés associés à la zone matrice
		* </pre>
		* @access public
		* 
		*/
		function get_libelles_zone_matrice(){
				$id_zone			= $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];
				$elem_trad		= $this->recherche_libelles_matrice($id_zone, $this->langue, 'DICO_ZONE');
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE'] 		= $elem_trad['LIBELLE'];
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE1'] = $elem_trad['LIBELLE_MESURE1'];
				$this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE2'] = $elem_trad['LIBELLE_MESURE2'];
		} //FIN get_zones()

		
		/**
		* METHODE : get_zones_saisie()
		* <pre>
		*  Récupère les caractéristiques de la zone <> matrice
		* </pre>
		* @access public
		* 
		*/
		function get_zones_saisie(){
			if( isset($this->TabGestZones['tables_meres']) and is_array($this->TabGestZones['tables_meres']) ) {
				$iTab = -1;//print_r($this->TabGestZones['tables_meres']); echo "FIN MERE<BR/><BR/><BR/>";
				foreach( $this->TabGestZones['tables_meres'] as $iTab => $tables_meres ) {					
					$requete	=	   "SELECT     DICO_ZONE.*, DICO_ZONE_SYSTEME.TYPE_OBJET
										FROM       DICO_ZONE, DICO_ZONE_SYSTEME
										WHERE      ID_THEME = ".$this->id_theme."
										AND   	   ID_TABLE_MERE_THEME =".$tables_meres['ID_TABLE_MERE_THEME']."	
										AND 	   DICO_ZONE_SYSTEME.ID_SYSTEME=".$this->id_theme_systeme."									
										AND        DICO_ZONE.ID_ZONE=DICO_ZONE_SYSTEME.ID_ZONE
										ORDER BY   DICO_ZONE.ORDRE ";
					$this->TabGestZones['zones'][$iTab]  = $GLOBALS['conn_dico']->GetAll($requete);
					//echo "<BR/><BR/><BR/>".$iTab."<BR/><BR/><BR/>".$requete."<BR/><BR/><BR/>"; print_r($this->TabGestZones['zones'][$iTab]); echo "FIN<BR/><BR/><BR/>";
					if(is_array($this->TabGestZones['zones'][$iTab]) and count($this->TabGestZones['zones'][$iTab])) {
						$this->order_zones = true ;
					}
				}
			}
			if(isset($this->TabGestZones['zones'][$this->i_tabm]) && is_array($this->TabGestZones['zones'][$this->i_tabm])){
				$i_z=0;
				foreach($this->TabGestZones['zones'][$this->i_tabm] as $zone){
					if($zone['ID_ZONE']==$this->id_zone) break;
					else $i_z++;
				}
				$this->iZone=$i_z;
			}
			$this->get_libelles_zones_saisie();
			//echo'<pre>';
			//print_r($this->TabGestZones['zones']);		
		} //FIN get_zones()
		
		/**
		* METHODE : get_libelles_zones_saisie()
		* <pre>
		* Récupère les libellés des différentes zones du thème
		* </pre>
		* @access public
		* 
		*/
		function get_libelles_zones_saisie(){
				if( count($this->TabGestZones['zones']) > 0 ){
						foreach($this->TabGestZones['zones'] as $iTab => $Tab){
								foreach( $Tab as $iZone => $zone){
										$this->TabGestZones['libelles_zones_saisie'][$iTab][$iZone] = $this->recherche_libelle($zone['ID_ZONE'],$this->langue,'DICO_ZONE');
								}
						}
				}
		} //FIN get_zones()

		
		/**
		* METHODE : recherche_libelle_bouton($code, $langue, $page)
		* <pre>
		*  permet de récupérer le libellé dans la table de traduction
		*  en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle_bouton($code, $langue, $page){
				// $page = 'generer_theme.php' ou gestion_zone.php
				$requete 	= " SELECT LIBELLE
								FROM DICO_LIBELLE_PAGE 
								WHERE CODE_LIBELLE='".$code."' AND CODE_LANGUE='".$this->langue."'
								AND NOM_PAGE='".$page."'";
												
				$all_res	= $GLOBALS['conn_dico']->GetAll($requete);                 
				//$this->libelle_theme   = $all_res[0][LIBELLE];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : recherche_libelle($code,$langue,$table)
		* <pre>
		* permet de récupérer le libellé dans la table de traduction
		* en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelle($code,$langue,$table){
				
				if ( preg_match('/' . '^'.$GLOBALS['PARAM']['TYPE'].'_.*$' . '/', $table)){ // Table de Nomenclature : traduction dans la base courante
					$conn                 =   $GLOBALS['conn'];
				} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
					$conn                 =   $GLOBALS['conn_dico']; 
				}
				
				$requete 	= "SELECT LIBELLE
										FROM DICO_TRADUCTION 
										WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
										AND NOM_TABLE='".$table."'";
				
				//echo $requete;
				//print_r($this->conn);
				$all_res	= $conn->GetAll($requete); 
				//$this->libelle_theme   = $all_res[0][LIBELLE];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : recherche_libelles_matrice($code,$langue,$table)
		* <pre>
		* permet de récupérer les libellés zone et mesure dans la table de traduction
		* en fonction de la langue et de la table  aussi
		* </pre>
		* @access public
		* 
		*/
		function recherche_libelles_matrice($code,$langue,$table){
			if ( preg_match('/' . '^'.$GLOBALS['PARAM']['TYPE'].'_.*$' . '/', $table)){ // Table de Nomenclature : traduction dans la base courante
				$conn                 =   $GLOBALS['conn'];
			} else{ // // Autre Table : traduction dans la base de DICO : peut etre externe
				$conn                 =   $GLOBALS['conn_dico']; 
			}
			$requete 	= " SELECT LIBELLE, LIBELLE_MESURE1, LIBELLE_MESURE2
							FROM DICO_TRADUCTION 
							WHERE CODE_NOMENCLATURE=".$code." AND CODE_LANGUE='".$langue."'
							AND NOM_TABLE='".$table."'";
			$all_res	= $conn->GetAll($requete); 
			return($all_res[0]);
		}

		
		/**
		* METHODE : SuppCascadeZone($id_zone)
		* <pre>
		*  fonction de suppression en cascade de la zone
		* </pre>
		* @access public
		* 
		*/
		function SuppCascadeZone($id_zone) { 
			/// Suppression dans DICO_ZONE
			$requete 	= ' DELETE FROM DICO_ZONE WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_ZONE_SYSTEME
			$requete 	= ' DELETE FROM DICO_ZONE_SYSTEME  WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_REGLE_ZONE_ASSOC
			$requete 	= ' DELETE    FROM DICO_REGLE_ZONE_ASSOC  WHERE  ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';

			/// Suppression dans DICO_ZONE_JOINTURE
			$requete 	= ' DELETE    FROM DICO_ZONE_JOINTURE  WHERE ID_THEME = '.$this->id_theme.' 
										AND N_JOINTURE IN
										(SELECT DISTINCT N_JOINTURE FROM ( SELECT * FROM DICO_ZONE_JOINTURE ) AS DZJ 
										 WHERE ID_ZONE = '.$id_zone.' AND ID_THEME = '.$this->id_theme.')';
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_DIMENSION_ZONE CAS : MATRICE
			$requete 	= ' DELETE    FROM   DICO_DIMENSION_ZONE 
										 WHERE ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_INDEXES CAS : MATRICE
			$requete 	= ' DELETE    FROM   DICO_INDEXES 
										 WHERE ID_ZONE = '.$id_zone;
			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
			
			/// Suppression dans DICO_TRADUCTION
			$requete 	= 'DELETE    FROM   DICO_TRADUCTION 
									 WHERE CODE_NOMENCLATURE = '.$id_zone.
									' AND NOM_TABLE = \'DICO_ZONE\''; 

			if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
			if ($this->OkAction == 0) echo $requete.'<br>';
		}

		
		/**
		* METHODE : InsertTabMere($iTab)
		* <pre>
		* Création table mére sous l'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function InsertTabMere($iTab){ /// 
			//$this->ActMAJ = 1;
			// get le ID_TABLE_MERE_THEME à attribuer
			// ID_THEME = $this->id_theme
			// NOM_TABLE_MERE = NOM_TABLE_MERE_$iTab
			// PRIORITE = PRIORITE_$iTab
			$this->ActMAJ = 1;
			$iTab = $this->iTab;

			if (!preg_match("/^(0|([1-9][0-9]*))$/", trim($_POST['PRIORITE_'.$iTab]))){
					$this->OkAction = 0;
					//return 0;
					//echo 'passe 1';
			}
			if ( trim($_POST['NOM_TABLE_MERE_'.$iTab] =='') ){
					$this->OkAction = 0;
					//return 0;
					//echo 'passe 2';
			}
			// on verifie l'existence de la table mère et de la priorité 
			$req_exist = 'SELECT *		FROM   DICO_TABLE_MERE_THEME
									 WHERE ID_THEME = '.$this->id_theme.'
									 AND ( NOM_TABLE_MERE = \''.trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\' 
												 OR PRIORITE = '.trim($_POST['PRIORITE_'.$iTab]).')';
			//echo $req_exist . '<br>';
			$exists 	= $GLOBALS['conn_dico']->GetAll($req_exist);
			if( is_array($exists) and (count($exists) > 0) ){
					$this->OkAction = 0;
					//echo 'passe 3';
			}
			
			if($this->OkAction == 1){ // s'il n'y a  aucun PB dans les données postées
					$req_max = 'SELECT     MAX(ID_TABLE_MERE_THEME) AS Max_Ins
											FROM       DICO_TABLE_MERE_THEME';
					$ID_TABLE_MERE_THEME 	=	$GLOBALS['conn_dico']->getOne($req_max) + 1 ;
					$requete 	= ' INSERT INTO DICO_TABLE_MERE_THEME
												(ID_TABLE_MERE_THEME, ID_THEME, NOM_TABLE_MERE, PRIORITE)
												VALUES ('.$ID_TABLE_MERE_THEME.','.$this->id_theme.',\''.
												trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\','.trim($_POST['PRIORITE_'.$iTab]).')';      
							//
					if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
					if ($this->OkAction == 0) echo $requete.'<br>';
					return $ID_TABLE_MERE_THEME;
			} else {
				return -1;
			}
		}
		
		
		/**
		* METHODE : UpdateTabMere($iTab)
		* <pre>
		* Mise à jour table mére d'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function UpdateTabMere($iTab){ /// 
				$this->ActMAJ = 1;
				$iTab = $this->iTab;

				if (!preg_match("/^(0|([1-9][0-9]*))$/", trim($_POST['PRIORITE_'.$iTab]))){
						$this->OkAction = 0;
						//return 0;
				}
				if ( trim($_POST['NOM_TABLE_MERE_'.$iTab]) ==''){
						$this->OkAction = 0;
						//return 0;
				}
				if($this->OkAction == 1){ // s'il n'y a pas deja une erreur dans les données postées
						$requete 	= ' UPDATE    DICO_TABLE_MERE_THEME
													SET      	PRIORITE ='.trim($_POST['PRIORITE_'.$iTab]).',
																		NOM_TABLE_MERE =\''.trim($_POST['NOM_TABLE_MERE_'.$iTab]).'\'       
													WHERE     ID_THEME = '.$this->id_theme.'
													AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
								//
						if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
						if ($this->OkAction == 0) echo $requete.'<br>';
				}
				if($this->OkAction == 1){
						$requete 	= ' UPDATE    DICO_ZONE
													SET      	PRIORITE ='.$_POST['PRIORITE_'.$iTab].',
																		ZONE_STATUT =\'\',
																		TABLE_MERE =\''.$_POST['NOM_TABLE_MERE_'.$iTab].'\'       
													WHERE     ID_THEME = '.$this->id_theme.'
													AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
								//
						if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
						if ($this->OkAction == 0) echo $requete.'<br>';
				}
 		}
		
		/**
		* METHODE : DeleteTabMere($iTab)
		* <pre>
		* Suppression table mére d'indice iTab
		* </pre>
		* @access public
		* 
		*/
		function DeleteTabMere($iTab){ /// 
				$this->ActMAJ = 1;
				$iTab = $this->iTab;
				$id_tab_mere_supp = $this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
				
				$req_zones_supp 	= 'SELECT ID_ZONE FROM DICO_ZONE 
														 WHERE ID_THEME = '.$this->id_theme.'
														 AND ID_TABLE_MERE_THEME = '.$id_tab_mere_supp;
				$tab_zones_supp   =	$GLOBALS['conn_dico']->GetAll($req_zones_supp);
				$requete 	= ' DELETE    FROM DICO_TABLE_MERE_THEME
											WHERE     ID_THEME = '.$this->id_theme.'
											AND 			ID_TABLE_MERE_THEME = '.$this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
						//
				if ($GLOBALS['conn_dico']->Execute($requete) === false)    	$this->OkAction 	= 0;
				if ($this->OkAction == 0) echo $requete.'<br>';
				
				if(is_array($tab_zones_supp)){
						foreach($tab_zones_supp as $zone){
								$this->SuppCascadeZone($zone['ID_ZONE']);
						}
				}				
		}
		
		/**
		* METHODE : verif_POST()
		* <pre>
		* fonction de vérification du POST
		* </pre>
		* @access public
		* 
		*/
		function verif_POST(){
				$this->set_champs_zone();
				foreach ($this->champs_zone as $champ) {
				
						if( (trim($_POST[$champ['nom']]) == '') and ($champ['manip'] == true) and ($champ['obli'] == '1')){
								// si  champ obli, valeur obli
								$this->OkAction 	= 0;
								$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
								break;
						}

						if( (trim($_POST[$champ['nom']]) <> '') and ($champ['type'] == 'int')) {
								if (!preg_match("/^(0|([1-9][0-9]*))$/", trim($_POST[$champ['nom']]))) {
										$this->OkAction = 0;
										$this->lib_champ_err 	= recherche_libelle_page($champ['lib']);
										break;
								}
						}
				}
				if ($this->OkAction == 1 and $this->id_type_theme == 1) {
						if( (trim($_POST['ID_DIMENSION1']) == '') and  (trim($_POST['ID_DIMENSION2']) == '') ){
								$this->OkAction = 0;
								$this->lib_champ_err 	= recherche_libelle_page(choixdim);
						}
				}
		}
		
		/**
		* METHODE : InsertZone( $iTab )
		* <pre>
		*  Création zone dont indice table mére = iTab 
		* </pre>
		* @access public
		* 
		*/
		function InsertZone( $iTab ) { ///
				// get le ID_ZONE à attribuer par rapport PRIORITE_$iTab
				$this->ActMAJ = 1;
				$this->i_tabm=$iTab;
				/// FAIRE $this->iZone = count($this->TabGestZones['zones'][$iTab]);
				$this->verif_POST();
				
				if($this->OkAction == 1){ // s'il n'y a pas deja une erreur dans les données postées
						$req_max = 'SELECT   MAX(ID_ZONE) AS Max_Ins
												FROM     DICO_ZONE
												WHERE    ID_THEME = '.$this->id_theme;
						$ID_NEW_ZONE 	=	$GLOBALS['conn_dico']->getOne($req_max) ;
						if(!$ID_NEW_ZONE){
							$ID_NEW_ZONE = $this->id_theme * 1000 + 1;
						}
						else{
							$ID_NEW_ZONE++;
						}
						
						//// on test si l'id_zone attribué est valide
						//// sinon on octroie le max de la base
						$req_test_ID_ZONE = 'SELECT * FROM DICO_ZONE WHERE ID_ZONE = '.$ID_NEW_ZONE;
						$exists = $GLOBALS['conn_dico']->GetAll($req_test_ID_ZONE) ;
						if( is_array($exists) and (count($exists) > 0) ){
								$req_max = 'SELECT   MAX(ID_ZONE) AS Max_Ins
														FROM     DICO_ZONE';
								$ID_NEW_ZONE 	=	$GLOBALS['conn_dico']->getOne($req_max) + 1;
						}					
						////////////////////
						if($this->id_type_theme == 1){
								// on efface toute eventuelle zone matrice existante ds le theme
								$req_zones_thm = 'SELECT ID_ZONE FROM DICO_ZONE WHERE ID_THEME = '.$this->id_theme;
								$all_zones_thm = $GLOBALS['conn_dico']->GetAll($req_zones_thm) ;
								if( is_array($all_zones_thm) and (count($all_zones_thm) > 0) ){
										foreach( $all_zones_thm as $zone){
												$this->SuppCascadeZone($zone['ID_ZONE']);
										}
								}
						}
						///////////////
						
						$tab_req = array();
						$tab_req[] = array( 'champ' => 'ID_ZONE', 'val' => $ID_NEW_ZONE,'type' => 'int'	);
						$tab_req[] = array( 'champ' => 'ID_THEME', 'val' => $this->id_theme,'type' => 'int'	);
						if($this->id_type_theme <>1){
								$ID_TABLE_MERE_THEME = $this->TabGestZones['tables_meres'][$iTab]['ID_TABLE_MERE_THEME'];
								$NOM_TABLE_MERE			 = $this->TabGestZones['tables_meres'][$iTab]['NOM_TABLE_MERE'];
								$PRIORITE						 = $this->TabGestZones['tables_meres'][$iTab]['PRIORITE'];
								
								$tab_req[] = array( 'champ' => 'ID_TABLE_MERE_THEME'	, 'val' => $ID_TABLE_MERE_THEME	,'type' => 'int'	);
								$tab_req[] = array( 'champ' => 'TABLE_MERE' 					, 'val' => $NOM_TABLE_MERE			,'type' => 'text'	);
								$tab_req[] = array( 'champ' => 'PRIORITE' 						, 'val' => $PRIORITE						,'type' => 'int'	);
						}
						else{ //  Cas de matrice mettre 255 comme valeur de priorité pour l'utiliser lors du tri des table mères
									//  pour la suppression d'un établissement
								$tab_req[] = array( 'champ' => 'PRIORITE' 						, 'val' => 255						,'type' => 'int'	);
						}
						foreach($this->champs_zone as $champ){
								if( (trim($_POST[$champ['nom']]) <> '') and ($champ['manip'] == true) ){
										$tab_req[] = array( 'champ' => $champ['nom'], 'val' => $_POST[$champ['nom']], 'type' => $champ['type']	);
								}
						}
						$sql_champs = 'INSERT INTO DICO_ZONE (';
						$sql_values = 'VALUES (';
						
						foreach($tab_req as $i => $tab){
								if($i>0){
										$sql_champs .= ', ';
										$sql_values .= ', ';
								}
								$sql_champs .= $tab['champ'];
								if($tab['type']=='int'){
										$sql_values .= trim($tab['val']);
								}
								elseif($tab['type']=='text'){
										$sql_values .= $this->conn->qstr(trim($tab['val']));
								}
						}
						$sql_champs .= ') ';
						$sql_values .= ') ';
						
						$requete = $sql_champs . $sql_values ;
								//
						if ($GLOBALS['conn_dico']->Execute($requete) === false){ // insert ds DICO_ZONE

						   	$this->OkAction 	= 0;
								return $requete.'<br>'.count($this->TabGestZones['tables_meres']);
						}
						else
							$this->get_zones_saisie();
						if ($this->OkAction == 1 and $this->id_type_theme == 1){// insert ds DICO_DIMENSION_ZONE
								// on s'assure que plus aucune dimension n'est rattachée à la valeur de l'ID_ZONE 
								$requete 	= ' DELETE    FROM   DICO_DIMENSION_ZONE 
											 				WHERE ID_ZONE = '.$ID_NEW_ZONE;
								if ($GLOBALS['conn_dico']->Execute($requete) === false){    
										echo 'error : '.$requete.'<br>';
								}
								for( $i = 1; $i <= 2; $i++ ){
										if(trim($_POST['ID_DIMENSION'.$i]) <> ''){
												if(trim($_POST['NB_LIGNES_DIMENSION'.$i]) == ''){
														$_POST['NB_LIGNES_DIMENSION'.$i] = 0 ;
												}
												$requete 	= ' INSERT INTO  DICO_DIMENSION_ZONE 
													(ID_ZONE, ID_DIMENSION, TYPE_DIMENSION, NB_LIGNES_DIMENSION) 
													VALUES('.$ID_NEW_ZONE.','.$_POST['ID_DIMENSION'.$i].','.$i.','.$_POST['NB_LIGNES_DIMENSION'.$i].')';
												if ($GLOBALS['conn_dico']->Execute($requete) === false){ 
														$this->OkAction 	= 0;
														echo $requete.'<br>';
												}
										}
								}
						}
						
						if ($this->OkAction == 1){
								if( !isset($_POST['LIBELLE']) ){
										$_POST['LIBELLE'] = '';
								}
								if( !isset($_POST['LIBELLE_MESURE1']) ){
										$_POST['LIBELLE_MESURE1'] = '';
								}
								if( !isset($_POST['LIBELLE_MESURE2']) ){
										$_POST['LIBELLE_MESURE2'] = '';
								}

									$sql_del='DELETE FROM DICO_TRADUCTION 
														WHERE CODE_NOMENCLATURE='.$ID_NEW_ZONE.' 
														AND NOM_TABLE=\'DICO_ZONE\'';
													
									if ($GLOBALS['conn_dico']->Execute($sql_del) === false) {};

								$requete 	= 'INSERT INTO DICO_TRADUCTION 
														 ( CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE, LIBELLE_MESURE1,  LIBELLE_MESURE2 )
														 VALUES
														 ( '.$ID_NEW_ZONE.', \'DICO_ZONE\', \''.$this->langue.'\','.$this->conn->qstr(utf8_decode($_POST['LIBELLE'])).',
														 '.$this->conn->qstr($_POST['LIBELLE_MESURE1']).','.$this->conn->qstr($_POST['LIBELLE_MESURE2']).' )';
											//
								if ($GLOBALS['conn_dico']->Execute($requete) === false) {   
										$this->OkAction 	= 0;
										echo $requete.'<br>';
								}
								insert_traduction('DICO_TRADUCTION', $ID_NEW_ZONE, 'DICO_ZONE', $this->langue, utf8_decode($_POST['LIBELLE']), 1);
						}
				}
				if($this->OkAction == 0){
						$this->btn_add = true;
				}
				else{
						$this->btn_add = false;
				}
				return $ID_NEW_ZONE;
		}
		
		/**
		* METHODE : UpdateZone( $iZone, $iTab)
		* <pre>
		* Mise à jour zone n° iZone de la table mére n° iTab 
		* </pre>
		* @access public
		* 
		*/
		function UpdateZone($iZone, $iTab) { /// 
			$this->ActMAJ = 1;
			$this->i_tabm = $iTab;
			if ($this->id_type_theme == 1) {
				$ID_ZONE	= $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];
			}
			else {
				$iTab		= $this->iTab;
				$iZone		= $this->iZone;
				$ID_ZONE	= $this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'];
			}
			$this->id_zone=$ID_ZONE;
			$this->set_champs_zone();
			$this->verif_POST();
			if ($this->OkAction == 1) { 
				// s'il n'y a pas deja une erreur dans les données postées
				////////////////////
				/*
						$this->set_champs_zone();
						foreach($this->champs_zone as $champ){
								if( ($_POST[$champ['nom']]=='') and $champ['type']=='int' ){
										$_POST[$champ['nom']] = "NULL" ;
								}
						}
					*/
				////////////////////
				//$tab_req = array();
				$sql = 'UPDATE DICO_ZONE SET '."\n";
				$virg = false;
				foreach($this->champs_zone as $i => $champ) {
						if( $champ['manip'] == true ){
								if($virg == true){
										$sql .= ', '."\n";
								}
								$virg = true;
								if(trim($_POST[$champ['nom']]) == ''){
										$sql .= $champ['nom'].'='."NULL";
								}else{
										if($champ['type']=='int'){
												$sql .= $champ['nom'] . '=' . trim($_POST[$champ['nom']]);
										}
										elseif($champ['type']=='text'){
												$sql .= $champ['nom'] . '=' . $this->conn->qstr(trim($_POST[$champ['nom']]));
										}
								}
						}
				}
				$sql .= "\n";
				$requete = $sql . 'WHERE  ID_ZONE = '.$ID_ZONE; 
				
				if ($GLOBALS['conn_dico']->Execute($requete) === false) {
						$this->OkAction 	= 0;
						echo $requete.'<br>';
				}
				else
					$this->get_zones_saisie();
				if ($this->OkAction == 1 and $this->id_type_theme == 1) {// insert ds DICO_DIMENSION_ZONE
						// on s'assure que plus aucune dimension n'est rattachée à la valeur de l'ID_ZONE 
						$requete 	= ' DELETE    FROM   DICO_DIMENSION_ZONE 
													WHERE ID_ZONE = '.$ID_ZONE;
						if ($GLOBALS['conn_dico']->Execute($requete) === false){    
								echo 'error : '.$requete.'<br>';
						}
						for( $i = 1; $i <= 2; $i++ ) {
								if(trim($_POST['ID_DIMENSION'.$i]) <> ''){
										if(trim($_POST['NB_LIGNES_DIMENSION'.$i]) == ''){
												$_POST['NB_LIGNES_DIMENSION'.$i] = 0 ;
										}
										$requete 	= ' INSERT INTO  DICO_DIMENSION_ZONE 
											(ID_ZONE, ID_DIMENSION, TYPE_DIMENSION, NB_LIGNES_DIMENSION) 
											VALUES('.$ID_ZONE.','.$_POST['ID_DIMENSION'.$i].','.$i.','.$_POST['NB_LIGNES_DIMENSION'.$i].')';
										if ($GLOBALS['conn_dico']->Execute($requete) === false){ 
												$this->OkAction 	= 0;
												echo $requete.'<br>';
										}

								}
						}
				}
				if ($this->OkAction == 1) {
						if( !isset($_POST['LIBELLE']) ){
								$_POST['LIBELLE'] = '';
						}
						if( !isset($_POST['LIBELLE_MESURE1']) ){
								$_POST['LIBELLE_MESURE1'] = '';
						}
						if( !isset($_POST['LIBELLE_MESURE2']) ){
								$_POST['LIBELLE_MESURE2'] = '';
						}						
						/*$requete 	= 'UPDATE DICO_TRADUCTION 
												 SET LIBELLE = \''.$_POST['LIBELLE'].'\',
												 LIBELLE_MESURE1 = \''.$_POST['LIBELLE_MESURE1'].'\',
												 LIBELLE_MESURE2 = \''.$_POST['LIBELLE_MESURE2'].'\'
												 WHERE CODE_NOMENCLATURE='.$ID_ZONE.' 
												 AND  CODE_LANGUE=\''.$this->langue.'\'
												 AND NOM_TABLE=\'DICO_ZONE\'';*/
									//
						$sql_del='DELETE FROM DICO_TRADUCTION 
											WHERE CODE_NOMENCLATURE='.$ID_ZONE.' 
											AND NOM_TABLE=\'DICO_ZONE\'';
										
						if ($GLOBALS['conn_dico']->Execute($sql_del) === false) {};

						$requete = 'INSERT INTO DICO_TRADUCTION 
												 ( CODE_NOMENCLATURE, NOM_TABLE, CODE_LANGUE, LIBELLE, LIBELLE_MESURE1,  LIBELLE_MESURE2 )
												 VALUES
												 ( '.$ID_ZONE.', \'DICO_ZONE\', \''.$this->langue.'\','.$this->conn->qstr(utf8_decode($_POST['LIBELLE'])).',
												 '.$this->conn->qstr($_POST['LIBELLE_MESURE1']).','.$this->conn->qstr($_POST['LIBELLE_MESURE2']).' )';
						
						if ($GLOBALS['conn_dico']->Execute($requete) === false){
							$this->OkAction 	= 0;
								echo $requete.'<br>';
						}
						insert_traduction('DICO_TRADUCTION', $ID_ZONE, 'DICO_ZONE', $this->langue, utf8_decode($_POST['LIBELLE']), 1);
				}
			}
		}
		
		/**
		* METHODE : DeleteZone( $iZone, $iTab)
		* <pre>
		* Suppression zone n°iZone de la table mére n°iTab 
		* </pre>
		* @access public
		* 
		*/
		function DeleteZone( $iZone, $iTab){ /// 
			$this->ActMAJ = 1;
			if($this->id_type_theme == 1){
				$id_zone_supp	= $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];
			}
			else{
				$iTab 	= $this->iTab;
				$iZone 	= $this->iZone;
				$id_zone_supp =	$this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'];
			}
			$this->SuppCascadeZone($id_zone_supp);
		}
		
		/**
		* METHODE : MiseEvidenceMAJ()
		* <pre>
		* gére les alertes d'aprés mise à jour
		* </pre>
		* @access public
		* 
		*/
		function MiseEvidenceMAJ(){
			if($this->Action =='UpdZone'){
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("MiseEvidenceZones('".$this->iTabM."','".$this->iZone."','".$this->nb_TabM."'); \n");
				print("\t //--> \n");
				print("</script>\n");						
			}
			elseif($this->Action =='UpdTabM'){
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("MiseEvidenceTablesMeres('".$this->iTabM."'); \n");
				print("\t //--> \n");
				print("</script>\n");
			}
		}
		
		/**
		* METHODE : setValTabM()
		* <pre>
		* Gére les valeurs d'affichage pour les tables mères
		* </pre>
		* @access public
		* 
		*/
		function setValTabM(){
			$this->TabValTabM = array();
			if( ($_POST['ActionTabM']=='AddTabM') or ($_POST['ActionTabM']=='UpdTabM') ){
				//$mat_val	= $_POST;
				/*
				for( $iTab=0 ; $iTab <= $this->nb_TabM ; $iTab++ ){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 	= $_POST['PRIORITE_'.$iTab];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]  = $_POST['NOM_TABLE_MERE_'.$iTab];
				}
				*/
				if(is_array($this->TabGestZones['tables_meres']))
				foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 = $table_mere['PRIORITE'];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]  = $table_mere['NOM_TABLE_MERE'];
				}
					
			}
			else{
				//$mat_val	= $this->TabGestZones['tables_meres'];
				if(is_array($this->TabGestZones['tables_meres']))
				foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
					$this->TabValTabM['PRIORITE_'.$iTab] 			 = $table_mere['PRIORITE'];
					$this->TabValTabM['NOM_TABLE_MERE_'.$iTab] = $table_mere['NOM_TABLE_MERE'];
				}
				$this->TabValTabM['PRIORITE_'.($iTab + 1)]				= '';
				$this->TabValTabM['NOM_TABLE_MERE_'.($iTab + 1)]	= '';
			}
		}
		
		/**
		* METHODE : setValZone()
		* <pre>
		* Gére les valeurs d'affichage pour les zones de saisie
		* </pre>
		* @access public
		* 
		*/
		function setValZone(){
				$iTab 				= $this->iTab;
				$iZone				=	$this->iZone;
				$this->TabValZone = array();
				
				if($_POST['ActionZone']=='OpenZone') {
					$zone 				= $this->TabGestZones['zones'][$iTab][$iZone];
					$this->set_champs_zone();
					foreach($this->champs_zone as $champ){
							$this->TabValZone[$champ['nom']]	= $zone[$champ['nom']];
					}
					$this->TabValZone['LIBELLE']	= $this->TabGestZones['libelles_zones_saisie'][$iTab][$iZone];
				}
				elseif($_POST['ActionZone']=='NewZone') {
					$this->TabValZone['TABLE_MERE']	= $this->TabGestZones['tables_meres'][$iTab]['NOM_TABLE_MERE'];
					$max_ordre_metier = 0;
					foreach ($this->TabGestZones['zones'][$iTab] as $iZ => $zn){
						if($zn['ORDRE']>$max_ordre_metier) $max_ordre_metier = $zn['ORDRE'];
					}
					$this->TabValZone['ORDRE'] = $max_ordre_metier + 10;
					//$this->TabValZone['ORDRE'] = count($this->TabGestZones['zones'][$iTab])*10 + 10;
					//$this->TabValZone['ORDRE']				=	$this->iZone + 1;
				}
				elseif( $_POST['ActionZone']=='AddZone' or $_POST['ActionZone']=='UpdZone' or $this->OkAction == 0 ) {
					//echo 'passe1';
					$this->set_champs_zone();
					foreach($this->champs_zone as $champ){
							$this->TabValZone[$champ['nom']]	= $_POST[$champ['nom']];
					}
				}
				elseif($this->id_type_theme == 1){
					
					if(count($this->TabGestZones['zone_matrice'])){
					//echo 'passe2';
					foreach($this->TabGestZones['zone_matrice'] as $i => $tab){
						if($i==0){ // les données de DICO_ZONE
							$this->TabValZone['ID_ZONE'] 		= $tab['ID_ZONE'];
							$this->TabValZone['TABLE_MERE'] = $tab['TABLE_MERE'];
							$this->TabValZone['MESURE1'] 		= $tab['MESURE1'];
							$this->TabValZone['TYPE_SAISIE_MESURE1'] = $tab['TYPE_SAISIE_MESURE1'];
							$this->TabValZone['MESURE2'] 		= $tab['MESURE2'];
							$this->TabValZone['TYPE_SAISIE_MESURE2'] 		= $tab['TYPE_SAISIE_MESURE2'];
						}
						$this->TabValZone['ID_DIMENSION'.$tab['TYPE_DIMENSION']] 				= $tab['ID_DIMENSION'];
						$this->TabValZone['NB_LIGNES_DIMENSION'.$tab['TYPE_DIMENSION']] = $tab['NB_LIGNES_DIMENSION'];
					}
					$this->TabValZone['LIBELLE']					= $this->TabGestZones['libelles_zone_matrice']['LIBELLE']; 
					$this->TabValZone['LIBELLE_MESURE1']	= $this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE1'];
					$this->TabValZone['LIBELLE_MESURE2']	= $this->TabGestZones['libelles_zone_matrice']['LIBELLE_MESURE2']; 
				}
			}
			//echo'<pre>';
			//print_r($this->TabValZone);
		}
		
		/**
		* METHODE : AlerteMAJ()
		* <pre>
		* fonction d'alerte aprés mise à jour
		* </pre>
		* @access public
		* 
		*/
		function AlerteMAJ(){
			if( isset($this->ActMAJ)){ // si on est aprés soumission
				print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("MessMAJ('".$this->Action."',".$this->OkAction.",\"".trim($this->lib_champ_err)."\"); \n");
				print("\t //--> \n");
				print("</script>\n");
			}
		}
		
		public function testPost() {
			return "eeeee";
		}
		
		/**
		* METHODE : gererPost()
		* <pre>
		* fonction de gestion du POST
		* </pre>
		* @access public
		* 
		*/
		public function gererPost() {
			//echo '<pre>';
			//print_r($_POST);
			if (isset($_POST['btn_genere'])) { 

				$langues	        	=	array();
				$id_themes	        =	array();
				$id_systemes	      =	array();
				
				set_time_limit(0);
				ini_set("memory_limit", "128M");
				array_push($langues,     $_SESSION['langue']);
				array_push($id_themes,   $this->id_theme);
				if(!isset($_SESSION['filtre_secteur_T_S'])) $_SESSION['filtre_secteur_T_S'] = $_SESSION['secteur'];
				array_push($id_systemes, $_SESSION['filtre_secteur_T_S']);
				
				require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame.class.php';
				$form                   =	new frame( $id_themes, $langues, $id_systemes, '', '' );
				if($GLOBALS['PARAM']['MOBILE_THEME_CONFIG']){
					require_once $GLOBALS['SISED_PATH_CLS'] . 'affichage/frame_mobile.class.php';
					$form_mobile            =	new frame_mobile( $id_themes, $langues, $id_systemes, '', '' );
				}
				//die();
    		} else {
				$this->OkAction 	= 1;
				if( $_POST['ActionTabM']<>'' and isset($_POST['TabMActive'])) { // Actions sur table mère
						
						$this->Action = $_POST['ActionTabM'];
						$this->iTab = $_POST['TabMActive'];
						
						if($_POST['ActionTabM']=='AddTabM'){
							$this->InsertTabMere($_POST['TabMActive']);
						}
						elseif($_POST['ActionTabM']=='UpdTabM'){
							$this->UpdateTabMere($_POST['TabMActive']);
						}
						elseif($_POST['ActionTabM']=='SupTabM'){
							$this->DeleteTabMere($_POST['TabMActive']);
						}
				}// FIN Actions sur table mére
				elseif( $_POST['ActionZone']<>'' and isset($_POST['ZoneActive']) and isset($_POST['TabMActive']) ) { // Actions sur zone
						$this->Action = $_POST['ActionZone'];
						$this->iTab 	= $_POST['TabMActive'];
						$this->iZone 	= $_POST['ZoneActive'];
						
						if($_POST['ActionZone']=='NewZone'){
								//$this->InsertZone( $_POST['ZoneActive'], $_POST['TabMActive'] );
								$this->btn_add = true;
						}
						elseif($_POST['ActionZone']=='AddZone'){
								return $this->InsertZone( $_POST['TabMActive'] );
						}
						elseif($_POST['ActionZone']=='UpdZone'){
								return $this->UpdateZone( $_POST['ZoneActive'], $_POST['TabMActive'] );
						}
						elseif($_POST['ActionZone']=='SupZone'){
								$this->DeleteZone( $_POST['ZoneActive'], $_POST['TabMActive'] );
						}
				}// FIN Actions sur Zone
				if($this->ActMAJ == 1 and $this->OkAction == 1){ // s'il une action sur la BDD est effectuée
						$this->get_themes(); // on recharge les données liées au theme 
						
						if( $_POST['ActionZone'] ==  'AddZone'){
								$this->iZone = count($this->TabGestZones['zones'][$this->iTab]) - 1 ; // iZone du nouvel enr crée
								//echo 'passe3:'.$this->iZone;
						}
				}
			}
		}
		
		/**
		* METHODE : gererAff()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire
		* </pre>
		* @access public
		* 
		*/
		function gererAff() {
		?>
				<FORM name="Formulaire"  method="post" action="administration.php?val=gestzone&id_theme_choisi=<?php echo $this->id_theme; ?>">
				<br>
				<div align="center" id="themePage">
				<table id="themeTabMere">
					<tr>
						<td>
						
								<div name="DivChoixThemes" id="DivChoixThemes"> 
														
									<table>
											<tr> 
												<td><?php echo recherche_libelle_page(FiltreSE); ?></td>
												<td colspan="3"> <select name="filtre_secteur_T_S" onChange="toggle_filtre_secteur_T_S()">
														<?php
															foreach ($_SESSION['tab_secteur'] as $k => $v){
																echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]."'";
																if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $_SESSION['filtre_secteur_T_S']){
																	echo " selected";
																}
																echo ">".$v[LIBELLE]."</option>";
															}
														?>
													</select> 
											  </td>
											</tr>
											<?php if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>""){ ?>
											<tr> 
												<td><?php echo recherche_libelle_page('FiltreRattachTheme'); ?></td>
												<td colspan="3">
													<select name="filtre_appart_theme"  onChange="clear_filtre_appart_T_S()">
														<?php
															$requete = "SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
															$rs_type_entite= $GLOBALS['conn']->GetAll($requete);
															foreach ($rs_type_entite as $k => $v){
																echo "<option value='".$v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."'";
																if ($v[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']] == $_SESSION['filtre_appart_T_S']){
																	echo " selected";
																}
																echo ">".$v[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
															}
														?>
													</select>
												</td>
											</tr>
											<?php } ?>
											<tr> 
													<td nowrap> <?php echo recherche_libelle_page(ChoixThm); ?> 
													</td>
													<td colspan="3"><select name="id_theme" onChange="ChangerTheme(id_theme.value);">
																	<?php foreach ($this->TabGestZones['themes'] as $i => $themes){
														echo "<option value='".$themes['ID']."'";
														if ($themes['ID'] == $this->id_theme){
																echo " selected";
														}
														echo ">".$themes['LIBELLE']."</option>";
												}
										?>
															</select> </td>
											</tr>
											<tr>
												<td colspan="4">
												<table>
												<tr>
													<td>
													<input type="radio" name="choix_affich" value="1" <?php if((isset($this->val_choix_type_list_theme) && $this->val_choix_type_list_theme=='all_themes'))  echo " checked "; ?> onClick="reload('all_themes')">
													<?php echo recherche_libelle_page('all_themes'); ?>
													</td>
													<td>
													<input type="radio" name="choix_affich" value="2" <?php if((isset($this->val_choix_type_list_theme) && $this->val_choix_type_list_theme=='active_themes') || !isset($this->val_choix_type_list_theme))  echo " checked " ?> onClick="reload('active_themes')">
													<?php echo recherche_libelle_page('active_themes'); ?>
													</td>
													<?php if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>""){ ?>
													<td>
													<input type="radio" name="choix_affich" id="choix_affich_3" value="3" <?php if((isset($this->val_choix_type_list_theme) && $this->val_choix_type_list_theme=='active_themes_rattach'))  echo " checked " ?> onClick="reload('active_themes_rattach')">
													<?php echo recherche_libelle_page('active_themes_rattach');?>
													</td>
													<?php } ?>
												</tr>
												</table>
												</td>	
											</tr>
											<tr> 
													<td colspan="2" rowspan="4">
													<br>
													<table><tr><td align="center">																		
													<input style="width:150px;" type="submit" name="btn_genere" <?php echo ' value="'.recherche_libelle_page('genere').'"'; ?>>
													<br>
															<?php if( isset($this->id_theme)){ ?>
																			<input  style="width:150px;" onClick="javascript:location.href= 'administration.php?val=gestheme&theme_from_zones=<?php echo $this->id_theme;?>'" 
																			type="button" <?php echo ' value="'.recherche_libelle_page('gest_thm_z').'"'; ?>>
																	<?php } ?>		
													</td></tr></table>
													</td>
													<td colspan="2" nowrap>&nbsp; </td>
											</tr>
											<tr> 
													<td nowrap>&nbsp; <?php echo recherche_libelle_page(NumThm); ?> 
													</td>
													<td nowrap><?php echo $this->id_theme; ?></b></td>
											</tr>
											<tr> 
													<td nowrap>&nbsp; <?php echo recherche_libelle_page(NomThm); ?> 
													</td>
													<td nowrap><b><?php echo $this->libelle_theme; ?></b></td>
											</tr>
											<tr> 
													<td nowrap>&nbsp; <?php echo recherche_libelle_page(libtypth); ?> 
													</td>
													<td nowrap><b><?php echo $this->libelle_type_theme; ?></b></td>
											</tr>
									</table>
							</div>
						</td>
						<?php if($this->id_type_theme <> 1){
						?>
						<td  align="right" valign="middle" rowspan="4">
								<SPAN class=""> 
									- <a href="javascript:Montrer_Cacher_Div( 'DivGestTablesMeres', '0' )"><?php echo recherche_libelle_page('GestTabM'); ?></a><br />
									<div name="DivGestTablesMeres0" id="DivGestTablesMeres0">
											<table id="table_mere_liste">
													<tr class="ui-widget-header"> 
														<th align="center"><?php echo recherche_libelle_page('Priorite'); ?></th>
														<th align="center"><?php echo recherche_libelle_page('TabMere'); ?></th>
														<th align="center" colspan="3"><?php echo recherche_libelle_page('Actions'); ?></th>
													</tr>
													<?php $this->setValTabM(); // elements tab mere à afficher sur l'ecran
															if(is_array($this->TabGestZones['tables_meres']))
															foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
															 echo "
																	<tr id='TableMere".$iTab."' onClick=\"MiseEvidenceTablesMeres('".$iTab."')\" class=\"";																	
																		if (($iTab % 2) == 0) {
																			echo "ligne-paire-left";
																		} else {
																			echo "ligne-impaire-left";
																		}
																echo "\"> 
																			<td class='table_mere_label'><input type='text' name='PRIORITE_".$iTab."' value='".$this->TabValTabM['PRIORITE_'.$iTab]."' size='1' /></td>																			
																			
																			<td class='table_mere_label'>	
																				<select name='NOM_TABLE_MERE_".$iTab."'>";
																					$this->get_tables_db();
																					echo "<option value=''></option>";
																					foreach ($this->TabBD as $tab){
																						echo "<option value='".$tab."'";
																						if ($this->TabValTabM['NOM_TABLE_MERE_'.$iTab] == $tab){
																							echo " selected";
																						}
																						echo ">".$tab."</option>";
																					}																				
																		echo "	</select >													
																			</td>
																			<td><INPUT type='button' name='' value=\"".recherche_libelle_page('Modifier')."\"
																			onclick=\"javascript:dialogUpdateTabM(this, ".$iTab.", '".$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]."');\"></td>																		
																			<td><INPUT type='button' name='' value=\"".recherche_libelle_page('ListeZones')."\"
																			onclick=\"javascript:scrollTo('#Tab_".$iTab."');\"></td>
																			<td><INPUT type='button' name='' value=\"".recherche_libelle_page(Supprimer)."\" 
																			onclick=\"javascript:MiseEvidenceTablesMeres('".$iTab."'); javascript:dialogDeteteTabM(this,".$iTab.",'".$this->TabValTabM['NOM_TABLE_MERE_'.$iTab]."');\"></td>
																	</tr>
																	";		
															}
															 echo "<tr id='TableMere".$this->nb_TabM."' onClick=\"MiseEvidenceTablesMeres('".$this->nb_TabM."')\" class=\"";																	
																		if (($iTab % 2) == 0) {
																			echo "ligne-impaire-left";
																		} else {
																			echo "ligne-paire-left";
																		}
																echo "\">  
																			<td class='table_mere_label'><INPUT type='text' name='PRIORITE_".$this->nb_TabM."' value='".($this->TabValTabM['PRIORITE_'.$iTab]+1)."' size='1'></td>
																			
																			<td class='table_mere_label'>
																				<select name='NOM_TABLE_MERE_".$this->nb_TabM."'>";
																					$this->get_tables_db();
																					echo "<option value=''></option>";
																					foreach ($this->TabBD as $tab){
																						echo "<option value='".$tab."'>".$tab."</option>";
																					}																				
																		echo "	</select >																		
																			</td>
																			
																			<td colspan='2'><input  style='width:100%;' type='button' name='' value=\"".recherche_libelle_page(Ajouter)."\"
																			onclick=\"javascript:insererTableMere(".$this->nb_TabM.",".$this->id_theme.",".$this->id_theme_systeme.");\" /></td><td></td>
																	</tr>
																	";
															?>
													</table>
												</div>
										</SPAN>
								</td>
								<?php }?>
						</tr>
						<?php if($this->id_type_theme == 1){ ?>
						<tr> 
								<td align="center">
									
									<input type='button' id='btnOrdreZone' style="width:100%;" name='' <?php echo"value=\"".recherche_libelle_page(GestDim)."\"";?>
												 onClick="OpenPopupGestDim();">
	
									
								</td>
						</tr>
						<?php }?>
						<!--
						<tr>
							<td align="center">
								<input type='button' name='' <?php //echo"value='".recherche_libelle_page(GestRegZ)."'";?>
											 onClick="OpenPopupGestRegZone();">
							</td>
						</tr>
						-->
						<tr>
							<td align="center">
								<input  style="width:100%;" type='button' name='' <?php echo"value=\"".recherche_libelle_page(GstRegTh)."\"";?>
											 onClick="OpenPopupGestRegTheme(<?php echo $this->id_theme;?>);">
							</td>
						</tr>
						<?php if($this->exist_tables_meres_theme() == false){ ?>
						<tr> 
								<td align="center">
									
									<input type='button'  style="width:100%;" name='' <?php echo"value=\"".recherche_libelle_page('ImpTheme')."\" ... ";?>
									onClick="OpenPopupImpTheme(<?php echo $this->id_theme .', '.$this->id_type_theme;?>);">
								</td>
						</tr>
						<?php }?>
						<tr><td><script type="text/Javascript">runFormScript("#themeTabMere");</script></td></tr>
						
				</table>

				<INPUT type="hidden" id="ActionZone" name="ActionZone">
				<INPUT type="hidden" id="ActionTabM" name="ActionTabM">
				<INPUT type="hidden" id="ZoneActive" name="ZoneActive">
				<INPUT type="hidden" id="TabMActive" name="TabMActive">
		<?php if($this->id_type_theme == 1){ // META TYPE ZONE = ZONE MATRICE
						$this->Aff_Zone_Matrice();
				}//// FIN META TYPE ZONE = ZONE MATRICE
				else{ // META TYPE ZONE = ZONE DE SAISIE
						$this->gererAff_ZonesSaisie();
				}//// FIN META TYPE ZONE = ZONE DE SAISIE
				?>
					</DIV>
			</FORM>
			<?php }
		
		/**
		* METHODE : Aff_ZonesTabM()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire des Tables Mères
		* </pre>
		* @access public
		* 
		*/
		function Aff_ZonesTabM() {
								?>
				<SPAN> 
				<br />
				<?php if($this->order_zones == true){ ?>
					<br />
						<div>	
							<input type='button' id='btnOrdreZone' name='' <?php echo"value=\"".recherche_libelle_page('OrdZones')."\"";?> onClick="OpenPopupZoneOrdre(<?php echo "'".recherche_libelle_page('OrdZones')."'";?>);" />  
							<input type='button' id='btnValidZone' name='' <?php echo"value=\"".recherche_libelle_page('ValidZones')."\"";?> onclick="validateAllZones()" title="<?php echo recherche_libelle_page('TitreValidZones');?>"/>
							<span id='existNonValidZone'><img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_alert.png" />&nbsp;<?php echo recherche_libelle_page('ExistNonValidZone');?></span>
							<script type="text/Javascript">$("#btnOrdreZone").button();$("#btnValidZone").button();</script></div>
					<br />
				<?php } ?>
				<!--
						- <a href="javascript:MontreTout( 'DivTableMere' )"><?php //echo recherche_libelle_page(ShTabM); ?></a><br />
						- <a href="javascript:CacheTout( 'DivTableMere' )"><?php //echo recherche_libelle_page(ClsTabM); ?></a><br />
				-->
				<?php //- <a href="javascript:InverseTout( 'DivTableMere' )"> //echo recherche_libelle_page(InvTabM); </a><br /> ?>
				<?php //////////////// Récupération des zones par table liée 
						if(is_array($this->TabGestZones['tables_meres'])) {
							foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
								?>
									<br />
									- <a id="Tab_<?php echo $iTab."\" href=\"javascript:Montrer_Cacher_Div( 'DivTableMere', '".$iTab."' )\"";?>><?php echo"".recherche_libelle_page(ActTabM).'<b>'.$table_mere['NOM_TABLE_MERE']."</b>";?></a><br />
										<div <?php echo"name=\"DivTableMere".$iTab."\" id=\"DivTableMere".$iTab."\"";?> > 
											<div id="zone_liste_form_msg_<?php echo $iTab; ?>" class="form_msg">&nbsp;</div>
												<table class="sous_liste_form" id="zone_table_<?php echo $iTab; ?>" cellspacing="0">
													<tbody class="zone_table_body">
														<tr class="zone_table_header ui-widget-header">
															<th class=""><div><?php echo"".recherche_libelle_page('OrdreZone')."";?></div></th>
															<th class=""><div><?php echo"".recherche_libelle_page('ChZone')."";?></div></th>
															<th class=""><div><?php echo"".recherche_libelle_page('TypeZone')."";?></div></th>
															<th class=""><div><?php echo"".recherche_libelle_page('LibZone')."";?></div></th>
															<th class=""><div><?php echo"".recherche_libelle_page('CompZone')."";?></div></th>
															<th class="liste_cmd"><div><?php echo"".recherche_libelle_page('ActionZone')."";?></div></th>
															<th align="center"><div><?php echo"".recherche_libelle_page('StatutZone')."";?></div></th>
														</tr>
														<!--tr> 
															<td align=center><?php echo"".recherche_libelle_page(NumZone)."";?></td>
															<td align=center><?php echo"".recherche_libelle_page(IdZone)."";?></td>
															<td align=center><?php echo"".recherche_libelle_page(ChPere)."";?></td>
															<td align=center><?php echo"".recherche_libelle_page(LibChp)."";?></td>
															<td align=center><?php echo"".recherche_libelle_page(Actions)."";?></td>
													</tr-->
												<?php $iZone = -1;
							//print_r($this->TabGestZones['zones'][$iTab]);													
													$this->get_col_table_db_2($table_mere['NOM_TABLE_MERE']);
													$listChamps = $this->ColTabBD;
													$zone = NULL;
													foreach ($this->TabGestZones['zones'][$iTab] as $iZone => $zone) {															
														$this->Aff_zone($iTab, $iZone, $zone, $listChamps);
														echo "<tr id='LigneTableMereScript_".$iTab.'_'.$iZone."'><td colspan='7'><script type='text/Javascript'>runFormScript('#LigneTableMere".$iTab.'_'.$iZone."');</script></td></tr>";															
													}
												?>
												<tr id="ligneAddZone_<?php echo $iTab; ?>" class="<?php $iZone++;
														if (($iZone % 2) == 0) {
															echo "ligne-paire-left";
														} else {
															echo "ligne-impaire-left";
														}
													?>">
														<td><input readonly="readonly" class="readonly_input" type="button" name="ORDRE" value="<?php echo $zone['ORDRE']+10; ?>" /></td>
														<td>
															<select name="CHAMP_PERE" onchange="load_type_champ('ligneAddZone_<?php echo $iTab; ?>')">
																<option value=""></option>
																<?php foreach ($listChamps as $col) {
																	echo "<option value='".$col->name."' id='".$col->type."'>".$col->name."</option>";
																 }	?>
													   		</select >
														</td>
														<td><?php echo $this->Aff_Type_Donnees('---', ''); ?></td>
														<td><input type="text" name="LIBELLE_ZONE" value=""  size="80"/></td>
														<td><?php echo $this->Aff_Type_Objets('---'); ?></td>
														<td align="center" class="td_img_1"><img class="img_add_zone" onclick="javascript:validateZone('null',<?php echo "'".$iTab."',". "'".$iZone."'"; ?>,false,true); " src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>b_ajouter.png" alt="<?php echo"value=\"".recherche_libelle_page(AjoutColonne)."\"";?>" title="<?php echo recherche_libelle_page(AjZone); ?>"/><input type="hidden" name="ZONE_STATUT" /></td>
														<td align="center" class="td_img_2"></td>
													</tr>
													<tr><td colspan="7"><script type="text/Javascript">runFormScript("#ligneAddZone_<?php echo $iTab; ?>");</script></td></tr>
													<tr text-align="center" id="details_tr_<?php echo $iTab."_".$iZone; ?>" class="details_tr"><td colspan="7" class="details_td"></td></tr>
											<!--tr> 
													<td colspan=5 align='right'><INPUT type='button' name='' 
													<?php echo"value=\"".recherche_libelle_page(AjZone)."\"";?>
													<?php echo"onclick=\"javascript:Action(".$iTab.",".($iZone+1).",'Zone','NewZone');\"";?>></td>
											</tr-->
											 	</tbody>
											</table>
									</div><br />
								<?php }
							}
									//////////////// FIN Récupération des zones par table liée  
						 ?>

		</SPAN> 
		<?php if(isset($_POST['RetourTabM']) and trim($_POST['RetourTabM'])<>''){ ?>
				<script type=text/javascript>
					<!-- 
						///Montrer_Cacher_Div( 'DivTableMere', '<?php echo $_POST['RetourTabM']; ?>' );
						//InverseTout( 'DivTableMere' );
					-->
				</script>		
		<?php }
			
		}
		
		/**
		* METHODE : Aff_SaisieZone()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire des zones de saisies
		* </pre>
		* @access public
		* 
		*/
		function Aff_SaisieZone() {
			$this->setValZone();
			$this->get_types_donnees();
			include $GLOBALS['SISED_PATH_INC'] . 'administration/zone_saisie.php' ;
		}
		
		/**
		* METHODE : gererAff_ZonesSaisie()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire de façon générale
		* </pre>
		* @access public
		* 
		*/
		function gererAff_ZonesSaisie(){

			if(	($this->Action=='NewZone' 
				or $this->Action=='OpenZone'
				or $this->Action=='AddZone' 
				or $this->Action=='UpdZone') && (!isset($_GET['ret'])) ){
				$this->Aff_SaisieZone();
			}/*
			elseif( $this->Action=='AddZone' ){
					
					$iTab 				= $this->iTab;
					$iZone				=	$this->iZone;
					
					print("<script type=\"text/javascript\">\n");
					print("\t <!-- \n");
					print("Action(".$iTab.",".$iZone.",'Zone','OpenZone'); \n");
					print("\t //--> \n");
					print("</script>\n");

			}*/
			else{
				$this->get_types_donnees();
				$this->set_champs_zone_systeme();
				$this->get_types_objets();
				$this->get_systemes(NULL);
				$this->Aff_ZonesTabM();
				/*print("<script type=\"text/javascript\">\n");
				print("\t <!-- \n");
				print("Montrer_Cacher_Div( 'DivGestTablesMeres', '0' ); \n");
				print("\t //--> \n");
				print("</script>\n");*/
			}
		}		
		
		/**
		* METHODE : Aff_Zone_Matrice()
		* <pre>
		*  fonction de gestion de l'affichage du Formulaire des matrices
		* </pre>
		* @access public
		* 
		*/
		function Aff_Zone_Matrice() {
			$this->setValZone();
			$this->get_types_donnees_saisie_mat();
			include $GLOBALS['SISED_PATH_INC'] . 'administration/zone_matrice.php' ;
		}
		
		function Aff_zone($iTab, $iZone, $zone, $listChamps) {
			echo "<tr id='LigneTableMere".$iTab.'_'.$iZone."' class='ligneMere ";
			if (($iZone % 2) == 0) {
				echo "ligne-paire-left";
			} else {
				echo "ligne-impaire-left";
			}
			echo "' onclick='MiseEvidenceLigneZone(".$iTab.",".$iZone.");'>
				<a name=".$zone['ID_ZONE']."></a>
				<td>
					<input type='hidden' name='ID_ZONE' value='".$zone['ID_ZONE']."' />
					<input class='readonly_input' type='button' name='NumLigne".$iTab.'_'.$iZone."' id='NumLigne".$iTab.'_'.$iZone."' value='".($zone['ORDRE'])."'>
				</td>
				<td>
				<select name='CHAMP_PERE' onchange='updateChampPereDepend(".$iTab.",".$iZone.")' disabled='disabled'>";
					echo "<option value=''></option>";
					$chp_pere_exist = false;
					foreach ($listChamps as $col){
						echo "<option value='".$col->name."' id='".$col->type."'";
						if ($zone['CHAMP_PERE'] == $col->name){
							echo " selected='selected'";
							$chp_pere_exist = true;
						}
						echo ">".$col->name."</option>";
					}	
		   echo"</select>	
				</td>																	
				<td>";
			echo $this->Aff_Type_Donnees($zone['TYPE_ZONE_BASE'], $zone['CHAMP_PERE']);
			echo "</td>
				<td><input class='readonly_input' type='text' name='LIBELLE_ZONE' value=\"".$this->TabGestZones['libelles_zones_saisie'][$iTab][$iZone]."\"  readonly='readonly' size='80'></td>
				<td>";
			echo $this->Aff_Type_Objets($zone['TYPE_OBJET'], $iTab, $iZone);
			$zoneStatutMsg = "";
			$zoneStatutInputVal = $this->TabGestZones['zones'][$iTab][$iZone]['ZONE_STATUT'];
			if (!$chp_pere_exist && $zone['TYPE_OBJET'] != "label") {
				$zoneStatutMsg = recherche_libelle_page('err_champ_pere');
				$zoneStatut = "r";
				$zoneStatutInputVal = "r:err_champ_pere";
			} else {
				$zoneStatutMsg = substr($this->TabGestZones['zones'][$iTab][$iZone]['ZONE_STATUT'],2);
				$zoneStatutMsg = ($zoneStatutMsg == '')? recherche_libelle_page('TitreZoneNonVerif'):recherche_libelle_page($zoneStatutMsg);
				$zoneStatut = substr($this->TabGestZones['zones'][$iTab][$iZone]['ZONE_STATUT'],0,1);
			}
			$classImg = (($zoneStatut == NULL) || (strlen($zoneStatut)==0) || (!$chp_pere_exist))?" class='b_zone_nonvalide'":"";
			echo "</td>
				<td class='img_td'>
					<div class='edit_zone'><img class='lien' onclick='javascript:openclosedetails(".$this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'].", ".$iTab.", ".$iZone.");' src='".$GLOBALS['SISED_URL_IMG']."b_edit.png' title=\"".recherche_libelle_page(Modifier)."\" />&nbsp;&nbsp;
					<img class='lien' onclick='javascript:dialogDeleteZone(".$iTab.", ".$iZone.");' src='".$GLOBALS['SISED_URL_IMG']."b_drop.png'  title=\"".recherche_libelle_page(Supprimer)."\" /></div>
					<div class='valid_zone'><img class='lien' onclick='javascript:openclosedetails(".$this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'].", ".$iTab.", ".$iZone.");' src='".$GLOBALS['SISED_URL_IMG']."b_valider.png'  title=\"".recherche_libelle_page(ValEnrZone)."\"/>&nbsp;&nbsp;
					<img class='lien' onclick='javascript:openclosedetails(null,".$iTab.", ".$iZone.");' src='".$GLOBALS['SISED_URL_IMG']."b_cancel.png' title=\"".recherche_libelle_page(Annuler)."\" /></div>
				</td>																	
				<td align='center' class='img_td'>
					<img id='zone_statut_".$iTab."_".$iZone."' onclick='javascript:void(0);' src='".$GLOBALS['SISED_URL_IMG']."b_zone_".$zoneStatut.".png' title=\"".$zoneStatutMsg."\"".$classImg." />
					<input type='hidden' name='ZONE_STATUT' value='".$this->TabGestZones['zones'][$iTab][$iZone]['ZONE_STATUT']."' /> 
				</td>
			</tr>";
			echo '<tr text-align="center" id="details_tr_'.$iTab.'_'.$iZone.'" class="details_tr">
				<td colspan="7" class="details_td"></td></tr>';													
			//echo $this->AffZoneDetails($iTab, $iZone);
		}
		
		function Aff_Type_Donnees($curr_typ, $champ) {
			$type_donnees_chp	= $_SESSION['GestZones']['type_donnees'];
			echo "<select name='TYPE_ZONE_BASE'";
			if ($curr_typ <> '---') {
				echo " disabled='disabled'";
			}
			echo '><option value=\'\'></option>';			
			foreach ($type_donnees_chp as $i => $type_donnees){
				if (in_array($type_donnees['CODE_TYPE_DONNEES'],array(1,2,3))) {
					echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";	
					$type_donnee=array();
					if(trim($type_donnees['TYPE_DONNEES'])=='int') { $type_donnee=array('I','N','bigint','bit','decimal','float','int','numeric','smallint','tinyint','real'); }
					elseif(trim($type_donnees['TYPE_DONNEES'])=='text') $type_donnee=array('C','char','nchar','ntext','nvarchar','text','varchar');
					elseif(trim($type_donnees['TYPE_DONNEES'])=='date') $type_donnee=array('T','datetime','smalldatetime');			
					if (trim($type_donnees['TYPE_DONNEES']) == $curr_typ && trim($champ) <> '' ){
							echo " selected";
					}
					elseif (in_array(trim($_GET['id_chp']),$type_donnee)){
							echo " selected";
					}
					echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
				}
			}		
			echo "</select>";
		}
		
		function Aff_Type_Objets($currType, $iTab=-1, $iZone=-1) {
			echo "<select name='TYPE_OBJET'";
				if ($currType <> '---') {
					echo " disabled='disabled' onchange='update_champs_ref_type(".$iTab.",".$iZone.")'";
					
				}
				echo" ><option value=''></option>";
					foreach ($_SESSION['GestZones']['type_objet'] as $i => $type_objet) {
						echo "<option value='".trim($type_objet['TYPE_OBJET'])."'";
						if (trim($currType) == $type_objet['TYPE_OBJET']){
								echo " selected='selected'";
						}
						echo ">".get_libelle($type_objet['CODE_TYPE_OBJET'],'DICO_TYPE_OBJET',$type_objet['LIBELLE_TYPE_OBJET'])."</option>";
					}
             echo "</select>";
		}
		
		function set_champs_zone_systeme() {///// Préparation des champs de DICO_ZONE 
				if(!($GLOBALS['champs_zone_systeme'])){
						$GLOBALS['champs_zone_systeme'] 		= array();
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ID_ZONE', 'type_champ' => 'int', 'manip' => false	);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ID_SYSTEME', 'type_champ' => 'int'	, 'manip' => false	);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'TYPE_OBJET', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'ORDRE_AFFICHAGE', 'type_champ' => 'int', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'ACTIVER', 'type_champ' => 'int' , 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'ATTRIB_OBJET', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'AFFICHE_TOTAL', 'type_champ' => 'int', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'EXPRESSION', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'LIB_EXPR', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][]	= array( 'nom_champ' => 'AFFICH_VERTIC_MES', 'type_champ' => 'int', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'AFFICHE_TOTAL_VERTIC', 'type_champ' => 'int', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'AFFICHE_SOUS_TOTAUX', 'type_champ' => 'int', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'PRECEDENT_ZONE', 'type_champ' => 'int'	, 'manip' => false		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'BOUTON_INTERFACE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'TABLE_INTERFACE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'CHAMP_INTERFACE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'REQUETE_CHAMP_INTERFACE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'REQUETE_CHAMP_SAISIE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'FONCTION_INTERFACE', 'type_champ' => 'text', 'manip' => true		);
						$GLOBALS['champs_zone_systeme'][] 	= array( 'nom_champ' => 'VALEUR_CONSTANTE', 'type_champ' => 'int', 'manip' => true		);
				}
		}
		
		function get_types_objets() {
			$requete = "	SELECT  TYPE_OBJET, CODE_TYPE_OBJET, LIBELLE_TYPE_OBJET   
				FROM   DICO_TYPE_OBJET
				WHERE CODE_LANGUE = '".$_SESSION['langue']."'";
			
			$_SESSION['GestZones']['type_objet'] = $GLOBALS['conn_dico']->GetAll($requete);
		} 	//FIN get_zones()
		
		function get_systemes($id_systeme_choisi){/// get_systemes()
			// Chargement des codes et libellés des TYPE_SYSTEME_ENSEIGNEMENT
			// TODO: à virer de l'accueil
			$conn 		= $GLOBALS['conn'];
			if(!isset($_SESSION['fixe_secteurs'])){
				$requete     = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
								FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
								WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
								AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
			}else{
				$requete     = 'SELECT T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' as id_systeme, D_TRAD.LIBELLE as libelle_systeme
								FROM '.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' AS T_S_E,  DICO_TRADUCTION AS D_TRAD
								WHERE T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = D_TRAD.CODE_NOMENCLATURE 
								AND T_S_E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' IN ('.implode(',',$_SESSION['fixe_secteurs']).')
								AND D_TRAD.NOM_TABLE=\''.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'\' And D_TRAD.CODE_LANGUE=\''.$_SESSION['langue'].'\';';
			}
			//echo " <br> $requete <br>";
			$_SESSION['GestZones']['systemes'] = $conn->GetAll($requete);
			//echo'&&&&&&&&&&&<pre>';
			//print_r($_SESSION['GestZones']['systemes']);
			//die();
					
					if($id_systeme_choisi != NULL){
							$_SESSION['GestZones']['id_systeme'] 	= $id_systeme_choisi;
					}elseif(isset($_SESSION['secteur'])){
							$_SESSION['GestZones']['id_systeme'] 	= $_SESSION['secteur'];
					}
					if(!isset($_SESSION['GestZones']['id_systeme'])){
							$_SESSION['GestZones']['id_systeme'] 			= $_SESSION['GestZones']['systemes'][0]['id_systeme'];
					}
					
					foreach($_SESSION['GestZones']['systemes'] as $i => $systeme){
							if( $systeme['id_systeme'] == $_SESSION['GestZones']['id_systeme'] ){
									$_SESSION['GestZones']['libelle_systeme'] = $systeme['libelle_systeme'];
									break;	
							}
					}
					//echo $requete .'<br>';
		} // FIN  get_systemes()

			///// fonction de récupération des zones du theme
		function get_zone_systeme($id_zone_active) {
				if($id_zone_active != NULL)
					$_SESSION['GestZones']['id_zone_active'] = $id_zone_active;
				
				$requete	="	SELECT *
											FROM       DICO_ZONE_SYSTEME
											WHERE      ID_SYSTEME = ".$this->id_theme_systeme." 
											AND 			 ID_ZONE = ".$_SESSION['GestZones']['id_zone_active'];
				//echo $requete ;			
				$all_res = $GLOBALS['conn_dico']->GetAll($requete);
				return $all_res[0];
		} //FIN get_zones()
		
		function setTabValZoneSys() {
			/*$TabValZoneSys = array();
			foreach($GLOBALS['champs_zone_systeme'] as $i => $elem){
				if( $elem['manip']==true ){
					$TabValZoneSys[$elem['nom_champ']] = $_POST[$elem['nom_champ']];
				}
			}*/
		}
		
		function AffZoneDetails($iTab, $iZone) {	
			$this->setCurrZoneSaisieDetails($iTab, $iZone);
			$details = '<tr text-align="center" id="details_tr_'.$iTab.'_'.$iZone.'" class="details_tr">
				<td colspan="7" class="details_td">
					<div style="margin:0 auto; width:1010px;">
					<div class="float_left">
						<table cellpadding="0" cellspacing="0" class="details_zone_table float_leftz" style="width:500px;">
							<tbody>
								<tr> 
									<td style="width:110px;">'.recherche_libelle_page(OrdMet).'</td><td><input type="text" size="5" name="ORDRE" value="'.$this->TabValZone['ORDRE'].'"/></td>
									<td style="width:110px;">'.recherche_libelle_page(OrdTri).'</td><td><input type="text" size="5" name="ORDRE_TRI" value="'.$this->TabValZone['ORDRE_TRI'].'"/></td>
								</tr>
								<tr>	
									<td style="width:110px;">'.recherche_libelle_page(NomGrp).'</td><td><input type="text" size="5" name="NOM_GROUPE" value="'.$this->TabValZone['NOM_GROUPE'].'"/></td>
									<td></td><td></td>
								</tr>				
							</tbody>
						</table>
						<table cellpadding="0" cellspacing="0" class="details_zone_table float_rightz" style="width:500px;">';	
							if (($this->id_type_theme == 2) or ($this->id_type_theme == 3) or ($this->id_type_theme == 4) or ($this->id_type_theme == 5) or ($this->id_type_theme == 6)) {
						 $details .='<tr> 
								<td style="width:110px;">'.recherche_libelle_page(ZoneRef).'</td>
								<td>
									<select name="ID_ZONE_REF">
										<option value=""> </option>';
										//$iZone 	= $this->iZone;
										foreach ($this->TabGestZones['zones'][$iTab] as $tZone => $zone){
											$details .= "<option value='".$zone['ID_ZONE']."'";
											if ( trim($zone['ID_ZONE']) == trim($this->TabValZone['ID_ZONE_REF']) ){
													$details .= " selected";
											}
											$details .= ">".$zone['ID_ZONE'].'  ('.$zone['CHAMP_PERE'].')'."</option>";
										}		
								$details .='</select>
								</td>
							</tr>';
							} 
							$nb_zone = count($this->TabGestZones['zones'][$this->iTab]);
							
							$details .='<tr> 
								<td colspan="2">';
							if ($this->nb_TabM > 1) {
								$details .= recherche_libelle_page(ZoneJoin).'&nbsp;<input style="margin-left:30px;" type="button" name="" value="..." onClick="OpenPopupZoneJoin('.$this->TabValZone['ID_ZONE'].','.$iTab.');"/>&nbsp;';
							}
							$details .= '<span ';
							if ($this->nb_TabM > 1) $details .= 'style="margin-left:104px;"';
							$details .= '>'.recherche_libelle_page(Regle).'</span>&nbsp<input type="button" name="" value="..." onClick=" OpenPopupZoneRegle('.$this->TabValZone['ID_ZONE'].');"/></td>
							</tr>
						</table>
						<table cellpadding="0" cellspacing="0" class="details_zone_table float_leftz" style="width:500px;">												
							<tbody>
								<tr> 
									<td style="width:110px;">'.recherche_libelle_page(TabFille).'</td>
									<td>
										<select name="TABLE_FILLE" id="TABLE_FILLE" onchange="load_champs('.$iTab.','.$iZone.')">';
											$this->get_tables_nomenc_db();
											$details .= "<option value=''></option>";
											foreach ($this->TabBD as $tab){
												$details .= "<option value='".$tab."'";
												if ($this->TabValZone['TABLE_FILLE'] == $tab){
													$details .= " selected";
												}
												$details .= ">".$tab."</option>";
											}
									$details .='</select>
									</td>
								</tr>
								<tr>
									<td height="30" nowrap="">'.recherche_libelle_page(ChFils).'</td>
									<td>';
										$details .= $this->setCurrZoneChampFils($this->TabValZone['TABLE_FILLE'], $this->TabValZone['CHAMP_FILS']);
							   $details .='</td>
								</tr>
								<tr> 
									<td valign="top" nowrap="">'.recherche_libelle_page(ReqSql).'</td>
									<td>';
										$details .= $this->setCurrZoneSql($this->TabValZone['TABLE_FILLE'], $this->TabValZone['CHAMP_PERE'], $this->TabValZone['SQL_REQ'], $this->TabValZone['TABLE_FILLE']);
							   $details .='</td>
								</tr>
							</tbody>
						</table>
					</div>';					
					
					$TabValZoneSys = $this->get_zone_systeme($this->TabValZone['ID_ZONE']);
					
				$details .='<div class="float_left" style="">						
						<div id="chps_lies_'.$iTab.'_'.$iZone.'" class="champs_lies"> 
							<div>&nbsp;&nbsp;'.recherche_libelle_page(SaisInt).'</div>
							<table cellpadding="0" cellspacing="0" class="details_zone_table" style="width:500px;">
								<tr> 
									<td nowrap>'.recherche_libelle_page(BoutInt).'</td>
									<td nowrap><input type="text" size="10" name="BOUTON_INTERFACE" value="'.$TabValZoneSys['BOUTON_INTERFACE'].'"></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page(TabInt).'</td>
									<td><input type="text" size="40" name="TABLE_INTERFACE" value="'.$TabValZoneSys['TABLE_INTERFACE'].'"></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page(ChpInt).'</td>
									<td><input type="text" size="40" name="CHAMP_INTERFACE" value="'.$TabValZoneSys['CHAMP_INTERFACE'].'"></td>
								</tr>
								<tr> 
									<td valign="top" nowrap>'.recherche_libelle_page(FoncInt).'</td>								
									<td><textarea name="FONCTION_INTERFACE" style="resize:none" cols="50" rows="2">'.$TabValZoneSys['FONCTION_INTERFACE'].'</textarea></td>
								</tr>
								<tr> 
									<td valign="top">'.recherche_libelle_page(ReqInt).'</td>
									<td><textarea name="REQUETE_CHAMP_INTERFACE" style="resize:none" cols="50" rows="3">'.$TabValZoneSys['REQUETE_CHAMP_INTERFACE'].'</textarea></td>
								</tr>
							</table>
							<br/>
							<div>&nbsp;&nbsp;'.recherche_libelle_page(SaisDir).'</div>
							<table cellpadding="0" cellspacing="0" class="details_zone_table" style="width:500px;">
								<tr> 
									<td valign="top">'.recherche_libelle_page(ReqSais).'</td>							
									<td><textarea name="REQUETE_CHAMP_SAISIE" style="resize:none" cols="50" rows="3">'.$TabValZoneSys['REQUETE_CHAMP_SAISIE'].'</textarea></td>
								</tr>
							</table>
						</div>
						<table id="zone_tab1_'.$iTab.'_'.$iZone.'" cellpadding="0" cellspacing="0" class="details_zone_table float_leftz" style="width:500px;">
							<tbody>
								<tr> 
									<td width="48%">Attribut HTML:</td>
									<td><input type="text" size="30" name="ATTRIB_OBJET" value="'.ereg_replace('"','\'\'',$TabValZoneSys['ATTRIB_OBJET']).'"></td>
								</tr>
								<tr> 
									<td nowrap="">Ordre Affichage:</td>
									<td><input type="text" size="2" name="ORDRE_AFFICHAGE" value="'.$TabValZoneSys['ORDRE_AFFICHAGE'].'"></td>
								</tr>
								<tr> 
									<td>Affichage Verticale de la Measure:</td>
									<td><input name="AFFICH_VERTIC_MES" type="checkbox" value="1" '; if($TabValZoneSys['AFFICH_VERTIC_MES']=='1') $details .= ' checked="checked"'; $details .='></td>
								</tr>
							</tbody>
						</table>
						<table id="zone_tab2_'.$iTab.'_'.$iZone.'" cellpadding="0" cellspacing="0" class="details_zone_table float_leftz" style="width:500px;">
							<tbody>
								<tr> 
									<td width="48%">'.recherche_libelle_page('Activer').'</td><td><input name="ACTIVER" type="checkbox" '; if($TabValZoneSys['ACTIVER']=='1') $details .=' checked="checked"'; $details .='></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page('AffTot').'</td><td><input name="AFFICHE_TOTAL" type="checkbox" value="1"'; if($TabValZoneSys['AFFICHE_TOTAL']=='1') $details .=' checked="checked"'; $details .= '></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page('AffSsTot').'</td><td><input name="AFFICHE_SOUS_TOTAUX" type="checkbox" value="1" '; if($TabValZoneSys['AFFICHE_SOUS_TOTAUX']=='1') $details .=' checked="checked"'; $details .='></td>
								</tr>
								 <tr> 
									<td>'.recherche_libelle_page('Expression').'</td><td><input name="EXPRESSION" type="text" size="30" value="'.$TabValZoneSys['EXPRESSION'].'"></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page('LibExpr').'</td><td><input name="LIB_EXPR" type="text" size="30" value="'.$TabValZoneSys['LIB_EXPR'].'"></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page('TotVert').'</td><td><input name="AFFICHE_TOTAL_VERTIC" type="checkbox" value="1" '; if($TabValZoneSys['AFFICHE_TOTAL_VERTIC']=='1') $details .=' checked="checked"'; $details .='></td>
								</tr>
								<tr> 
									<td>'.recherche_libelle_page('ValCons').'</td><td><input type="text" size="5" name="VALEUR_CONSTANTE" value="'.$TabValZoneSys['VALEUR_CONSTANTE'].'"></td>
								</tr>
							</tbody>
						</table>
						<div><a href="javascript:openclosechpslies(\''.$iTab.'_'.$iZone.'\')" class="hidden_fields hidden_fields_'.$iTab.'_'.$iZone.'">=> '.recherche_libelle_page('ChpLiesPlus').'</a></div>
					</div>
					<div class="clear"></div>
					</div>	
				</td>
			</tr>';
			return $details;
		}
		
		function setCurrZoneSaisieDetails($iTab, $iZone) {
			$this->TabValZone = array();
			$zone = $this->TabGestZones['zones'][$iTab][$iZone];
			$this->set_champs_zone();
			foreach($this->champs_zone as $champ){
				$this->TabValZone[$champ['nom']] = $zone[$champ['nom']];
			}
			$this->TabValZone['LIBELLE'] = $this->TabGestZones['libelles_zones_saisie'][$iTab][$iZone];
		}
		
		function setCurrZoneChampFils($table, $chp_fils) {
			$chpFils = "<select name='CHAMP_FILS' id='CHAMP_FILS'>";
			if (trim($table) <> '') {
				$this->get_col_table_db($table);				
				//echo '<option value=\'\'></option>';
				foreach ($this->ColTabBD as $i => $champ) {
					$chpFils .= "<option value='".$champ."'";
					if ($chp_fils == $champ){
						$chpFils .= " selected";
					}
					elseif($champ==$GLOBALS['PARAM']['CODE'].'_'.$table)
					{
						$chpFils .= " selected";
					}
					$chpFils .= ">".$champ."</option>\n";
				}
			} else {
				$chpFils .= '<option value=\'\'></option>';
			}
			$chpFils .= "</select>";
			return $chpFils;
		}
		
		function setCurrZoneSql($table, $chp_pere, $chp_sql, $val_table) {
			$zoneSql = "<textarea name='SQL_REQ' style='width:370px' cols='68' rows='8' style='resize:none'>";
            $db_tables = array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
            if (in_array($table.'_'.$GLOBALS['PARAM']['SYSTEME'],$db_tables))
                    $lien_systeme  =  ','.$table.'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
                        $table.'.'.$GLOBALS['PARAM']['CODE']."_".$table.' = '.
                        $table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$table.
                        "\n AND ".$table.'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
                    
            else
                    $lien_systeme  =   ''; 
            //echo '<br>lien systeme ='.$lien_systeme ;
			if(trim($table) <> ''){
				if(trim($chp_sql)<>'' &&  trim($val_table)==trim($table))
				{
					$val_sql = urldecode($chp_sql);
				}
				else
				{
					$alias = '';
					if($chp_pere <> '')
					{
						if($chp_pere != $GLOBALS['PARAM']['CODE']."_".$table)
						{
							$alias = " AS ".$chp_pere;
						}
					}
					if($lien_systeme!='')
						$prefix=$table.'.';
					else
						$prefix='';
					$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$table.$alias. "\n".
								"FROM ". $table. $lien_systeme."\n".
								"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$table;
				}			
				$zoneSql .= $val_sql;
			}
			$zoneSql .= "</textarea>";	
			return $zoneSql;
		}
}	// fin class 
?>

