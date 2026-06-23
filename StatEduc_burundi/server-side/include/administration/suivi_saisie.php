<?php
set_time_limit(0);
// Fichiers d'includes'
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once 'common.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/suivi_saisie_batch.class.php';

lit_libelles_page('/suivi_saisie.php');
?>
<script language="javascript" type="text/javascript">
	function OpenPopupData() {
			//location.href   = '?val=gestzone&id_zone_active='+id_zone;
			/*var	popup	=	window.open('administration.php?val=PopupExportHtml','PopupIncohenre', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=800, height=600, left=100, top=50')
			popup.document.close();
			popup.focus();*/
			open_dialog('', 850, 620, 'administration.php?val=PopupExportHtml');
	} 
	function OpenPopupExportData() {
        /*var	popup	=	window.open('administration.php?val=PopExportWord','PopupExportWord', 'toolbar=yes,location=yes,directories=no,menubar=yes,scrollbars=yes,status=yes,resizable=1,width=800, height=400, left=100, top=50')
        popup.document.close();
        var chaine_eval = "popup.window.location.href   = 'administration.php?val=PopExportWord';";
        eval(chaine_eval);
        popup.focus();*/
		open_dialog('', 850, 420, 'administration.php?val=PopExportWord');
    }
	derniere_ligne_en_evidence = 1000;
	function MiseEvidenceEtabCtrl(i_TR, nb_TR) {
		if( derniere_ligne_en_evidence != i_TR){
			//for( itr=0; itr < nb_TR; itr++ ){
				//if(itr==i_TR){
				if(document.getElementById( 'ligne-paire_'+i_TR +'_0' ) ){
						SetClasseLigne(i_TR, 'ligne-paire', 'Evidence_Etab_Ctrl')
				}
				else if(document.getElementById( 'ligne-impaire_'+i_TR +'_0' ) ){
						SetClasseLigne(i_TR, 'ligne-impaire', 'Evidence_Etab_Ctrl')
				}
			   // }
			   // else{
						SetClasseLigne(derniere_ligne_en_evidence, 'ligne-paire', 'ligne-paire')
						SetClasseLigne(derniere_ligne_en_evidence, 'ligne-impaire', 'ligne-impaire')
			   // }
			//}
			derniere_ligne_en_evidence = i_TR ;
		}
	}
	
	var xhr = null; 
		
	function getXhr(){
		if(window.XMLHttpRequest){ // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		}
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("<?php echo recherche_libelle_page('no_jajax');?>"); 
		   xhr = false; 
		} 
	}
	
	function load_etabs_suivi(){
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				html_etabs_suivi = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('load_etabs_suivi').innerHTML = html_etabs_suivi;
				unpdateFormElt();
			}
		}

		url="administration.php?val=suivi_list_etabs";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		//setTimeout("load_champs()",1000);	
	}

	var xhr2 = null; 
		
	function getXhr2(){
		if(window.XMLHttpRequest){ // Firefox et autres
		   xhr2 = new XMLHttpRequest(); 
		}
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr2 = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("<?php echo recherche_libelle_page('no_jajax');?>"); 
		   xhr2 = false; 
		} 
	}
		
	function Set_Element(elem, form, val){
		eval('document.'+form+'.'+elem+'.value = '+val+' ;');
	}

//-->
</script>
<style type="text/css">
<!--

.aa{
	position: absolute;
	width:300px;
	height:25px;
	border:1px solid #000000;
	font-family:Verdana;
	font-size:13px;
	color:#000000;
	z-index:3;
	vertical-align: middle;
	left: 1px;
	right: 1px;
	top: 25px;
	visibility: inherit;
}
.bb{
	position: absolute;
	width:0%;
	height:25px;
	background-color:#00FF00;
	z-index:2;
	vertical-align: middle;
	left: 1px;
	right: 1px;
	top: 25px;
	visibility: inherit;
}

	.rg_gr{
		font-weight: bold;
		color: #FF0000;
	}
	.bl_gr {
		font-weight: bold;
		color: #0000FF;
	}
	.alert{
		font-weight: normal;
		color: #FF0000;
		text-decoration: none;
	}
	
	.Evidence_Etab_Ctrl{
		background-color: #00FF00;
	}
	
	div.table_scroll {
		height: 240px;
		overflow: auto;
		width: 100%;
		position:absolute;
		z-index:2;
	}
	
	thead.fix_titre_table{
		background: #CCCCCC;
		height:30px;
	}
	
	thead.fix_titre_table th {
		
		padding: 4px 3px;
		border: 1px solid #FFFFFF;
		color: #000000;
		text-align: center;
		font-weight:bold;
	}
	
	html>body tbody.contenu_tabl {
	display: block;
	overflow: auto;
	width: 100%
}

-->
</style>

<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>

<div>
<table class="center-table" width="98%" style="height:480px">
	<caption><?php echo recherche_libelle_page('CaptionSuivi'); ?></caption>
  <tr>
   <td>
		<!--debut config criteres ctrl -->
		<?php require $GLOBALS['SISED_PATH_INC'] . 'administration/suivi_saisie_criteres.php';
		?>
		<!--fin config criteres ctrl -->
		
		<!--debut liste etabs -->
		<?php if( $do_action_post == true ){
				//require $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_coherence_list_etabs.php';
				echo "<div id='load_etabs_suivi' style='display:inline' style='text-align:center'>" ; 
					include $GLOBALS['SISED_PATH_INC'] . 'administration/progressbarCtrlData.php';
				echo "</div>";
			}
		?>
		<!--fin liste etabs -->
		
	</td>
   </tr>
</table>
</div>
<?php if( $do_action_post == true ){
		echo "<script type='text/javascript'>
			load_etabs_suivi();
		</script>";
	}
?>
