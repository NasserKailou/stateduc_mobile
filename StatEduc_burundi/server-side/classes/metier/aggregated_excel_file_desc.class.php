<?php class aggregated_excel_file_desc{
	
			
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
		* Attribut : $ColTabBD
		* <pre>
		* Contient les colonnes d'une table de la base de données
		* </pre>
		* @var 
		* @access public
		*/		
		public $ColTabBD 	= array(); //
		
		/**
		* Attribut : $liste_tables
		* <pre>
		*  liste des tables de données à exporter
		* </pre>
		* @var 
		* @access public
		*/   
		public $liste_tables = array();
		public $db_structure_defaut = array();
		public $tab_mat_dim_lig = array();
		public $tab_mat_dim_col = array();
		public $tab_mat_liste_checkbox = array();
		public $liste_tab = array();
		public $tab_with_checkbox = array();
		public $table_champ_fils = array();
		public $tables = array();
		public $db_tables_config_details = array();
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
			$this->nomPage		= '/aggregated_db_structure.php';
			
		}
		
		/**
		* METHODE : init()
		* <pre>
		* Enchaine les fonctions dans le bon ordre de traitement de l'objet instancié
		* </pre>
		* @access public
		* 
		*/
		function init(){
			$this->btn_add 	= false;

			lit_libelles_page($this->nomPage);
			$this->printJS();
			$this->get_aggregated_tables();
			$this->gererAff();			
		}
		
		function __wakeup(){
			$this->conn	= $GLOBALS['conn'];
		}
		
		/**
		* METHODE : printJS()
		* <pre>
		* Affiche les fonctions javascript
		* </pre>
		* @access public
		* 
		*/
		function printJS(){
			?>
			<script type=text/javascript>
			 <!-- 
			
				 function AvertirSupp(iTab,iZone,cas_elem,lib_elem){ 
					var TxtAvertSuppZone ="<?php echo $this->TabGestZones['TxtAlert']['AvSupZone']; ?>"; 
					var TxtAvertSuppTabM ="<?php echo $this->TabGestZones['TxtAlert']['AvSupTabM']; ?>"; 
					if(cas_elem=='Zone'){ 
						var TxtAvert = TxtAvertSuppZone + lib_elem ; 
						var Act   = 'SupZone'; 
					} 
					else if(cas_elem=='TabM'){ 
						var TxtAvert = TxtAvertSuppTabM + lib_elem ; 
						var Act   = 'SupTabM'; 
					} 
					if(confirm(TxtAvert)){ 
						Action(iTab,iZone,cas_elem,Act); 
					} 
				 } 
			
				function MessMAJ(action,res_action,lib_champ_err){ 
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
			 //--> 
				function changer_action(todo){
					document.forms['Formulaire'].action = "administration.php?val=aggregated_db_structure&todo="+todo ;
					//alert(document.forms['Formulaire'].action);
				}
				var do_submit = true;
				function do_post(form_name){
					if( do_submit == true ){
						eval('document.'+form_name+'.submit();');
					}
				}
			</script>
		<?php }
		
		/**
		* METHODE : get_tables_meres_theme()
		* <pre>
		* Récupération des tables mères du thème
		* </pre>
		* @access public
		* 
		*/
		function get_aggregated_tables(){
				$requete 	=	' SELECT    * 
									FROM DICO_AGGREGATED_TABLE
									WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND EXPORT = 1
									ORDER BY ORDRE';
		
				$this->TabGestZones['tables_meres']  = $GLOBALS['conn_dico']->GetAll($requete);
				$this->nb_TabM = count($this->TabGestZones['tables_meres']);
				
		} //FIN get_tables_meres_theme()

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
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
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
				
				if ( preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($table))){ // Table de Nomenclature : traduction dans la base courante
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
				//$this->libelle_theme   = $all_res[0]['LIBELLE'];
				return($all_res[0]['LIBELLE']);
		}
		
		/**
		* METHODE : gererAff()
		* <pre>
		* fonction de gestion de l'affichage du Formulaire
		* </pre>
		* @access public
		* 
		*/
		function gererAff(){
		?>
				<FORM name="Formulaire"  method="post" action="administration.php?val=aggregated_db_structure">
				<br/>
				<br/>
				<br/>
				<DIV>
				<table align="center">
				<caption><?php echo recherche_libelle_page('DescExcelFile');?></caption>
				<tr>
						<?php if($this->id_type_theme <> 1){
						?>
						<td  align="right" valign="middle" rowspan="4">
								<SPAN class=""> 
									
									<div name="DivGestTablesMeres0" id="DivGestTablesMeres0">
											<table border="1">
													<tr> 
															<td align="center"><b><?php echo recherche_libelle_page('tab_excel'); ?></b></td>
															<td align="center"><b><?php echo recherche_libelle_page('Fields'); ?></b></td>
													</tr>
													<?php if(is_array($this->TabGestZones['tables_meres']))
															foreach($this->TabGestZones['tables_meres'] as $iTab => $table_mere){
																if($table_mere['EXPORT']==1) $checked = ' CHECKED';
																else $checked = '';
																echo "<tr id='TableMere".$iTab."' onClick=\"MiseEvidenceTablesMeres('".$iTab."')\">
																	  	<td>
																				<input type='text' name='NOM_TABLE_2_".$iTab."' id='NOM_TABLE_2_".$iTab."' style='width:225px' value='".$table_mere['NOM_TABLE_2']."' readonly='1'/>";
																echo	"</td>
																			<td><INPUT type='button' name='' value='".recherche_libelle_page('excel_fields_desc')."' 
																			onclick=\"javascript:MiseEvidenceTablesMeres('".$iTab."'); javascript:OpenPopupExcelAggregTables('".$table_mere['NOM_TABLE']."','".$table_mere['NOM_TABLE_2']."');\" style='width:300px'/>
																	  	</td>
																	</tr>";		
															}
															
															?>
													</table>
												</div>
												
										</SPAN>
								</td>
								<?php }?>
						</tr>
						
				</table>
				<br/>
				<table style="width:20%;" align="center">
					<tr> 
						<td align="center">
						<INPUT   style="width:150px;"  type="button" <?php echo 'value="'.recherche_libelle_page('fermer').'"';?> onClick="javascript:fermer();"/>
						</td>
					</tr>
				</table>
				<br/>
			</DIV>
			</FORM>
			<?php }
		
}	// fin class 
?>

