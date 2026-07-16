<?php
set_time_limit(0);
// Fichiers d'includes'
include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
require_once 'common.php';
require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';

lit_libelles_page('/controle_theme.php');
?>
<script language="javascript" type="text/javascript">
	function OpenPopupData() {
			//location.href   = '?val=gestzone&id_zone_active='+id_zone;
			var	popup	=	window.open('administration.php?val=PopupExportHtml','PopupIncohenre', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=800, height=600, left=100, top=50')
			popup.document.close();
			popup.focus();
	} 
	function OpenPopupExportData() {
        var	popup	=	window.open('administration.php?val=PopExportWord','PopupExportWord', 'toolbar=yes,location=yes,directories=no,menubar=yes,scrollbars=yes,status=yes,resizable=1,width=800, height=400, left=100, top=50')
        popup.document.close();
        var chaine_eval = "popup.window.location.href   = 'administration.php?val=PopExportWord';";
        eval(chaine_eval);
        popup.focus();
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
	
	function load_etabs_inchr(){
		getXhr();
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				html_etabs_inchr = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('load_etabs_inchr').innerHTML = html_etabs_inchr;
				updateElt("#load_etabs_inchr");
			}
		}

		url="administration.php?val=incohr_list_etabs";
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
		

	function run_imputation(cible, annee, check_action){
		getXhr2();
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				run_imput_txt = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				//document.getElementById('imput_progress').innerHTML = run_imput_txt;
				//document.location.href='accueil.php';
				document.Formulaire.submit();
				
			}
		}

		//url="administration.php?val=imputation";
		url="administration.php?val=imputation&cible_imput="+cible+"&annee_imput="+annee+"&action_imput="+check_action;
		// Ici on va voir comment faire du get
		xhr2.open("GET",url,true);
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr2.send(null);
		//setTimeout("load_champs()",1000);	
	}
		
	function AlertImput(cible, annee){
		if(annee != "" ){
			//alert('check_action='+check_action+' cible='+cible+' annee='+annee);
			var check_action = "" ;
			var tab_lib_ann	 = new Array();
			
			<?php $requete = 'SELECT * FROM '.$GLOBALS['PARAM']['TYPE_ANNEE'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
				$list_ann = $GLOBALS['conn']->GetAll($requete);
								
				foreach ($list_ann as $i_ann => $ann){
					if($ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] == $_SESSION['annee']){
						$ord_curr_ann = $ann[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] ;
					}
					print('tab_lib_ann['.$ann[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']].']='."'".$ann[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."';");
				}
			?>
			if(document.getElementById('id_imputer').checked == true){check_action='imputer'}
			else if(document.getElementById('id_lever_impute').checked == true){check_action='lever_impute'}
			
			if(check_action != "" ){
				var msg_cible = " <?php echo recherche_libelle_page('allschlist');?> ";
				
				if(check_action == 'imputer'){var msg_alert = "<?php echo recherche_libelle_page('estimfor');?> "+msg_cible+" <?php echo recherche_libelle_page('whithyear');?> ("+ tab_lib_ann[annee] +") ?";}
				else if(check_action == 'lever_impute'){var msg_alert = "<?php echo recherche_libelle_page('cancelestim');?> " + msg_cible +' ?';}
				
				if(confirm(msg_alert)){
					MM_displayLayers('imput_running','','block');
					MM_showHideLayers('form_imput','','hide','imput_running','','show');
					run_imputation(cible, annee, check_action);
				}
			}else{alert("<?php echo recherche_libelle_page('tickaction');?>");}
		}else{
			alert("<?php echo recherche_libelle_page('yearoblig');?>");
		}
	}
	function Set_Element(elem, form, val){
		eval('document.'+form+'.'+elem+'.value = '+val+' ;');
	}

	function MM_findObj(n, d) { //v4.01
	  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
		d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
	  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
	  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
	  if(!x && d.getElementById) x=d.getElementById(n); return x;
	}
	
	function cacher(){
		document.getElementById('form_imput').style.display = 'block' ;
	}
	
	
	function MM_displayLayers() { //v6.0
	  var i,p,v,obj,args=MM_displayLayers.arguments;
	  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
		if (obj.style) { obj=obj.style; }
		obj.display=v; }
	}
	
	
	function MM_showHideLayers() { //v6.0
	  var i,p,v,obj,args=MM_showHideLayers.arguments;
	  for (i=0; i<(args.length-2); i+=3) if ((obj=MM_findObj(args[i]))!=null) { v=args[i+2];
		if (obj.style) { obj=obj.style; v=(v=='show')?'visible':(v=='hide')?'hidden':v;}
		obj.visibility=v; }
	}
	
	function MM_callJS(jsStr) { //v2.0
	  return eval(jsStr)
	}
  
//-->
</script>
<style type="text/css">
<!--
.StyleImputData {
	color: #FF0000;
	font-weight: bold;
	background-color: #FFFF00;
	border: medium solid #FF0000;
	z-index: 3;
	width: auto;
	float: right;
	height: 30px;
	font-style: italic;
	text-transform: capitalize;
	text-align: center;
	vertical-align: middle;
	padding-top: 3px;
	padding-right: 2px;
	padding-bottom: 2px;
	padding-left: 2px;
	letter-spacing: 2px;
	font-size: 14px;
}
#info_imput {
	vertical-align: middle;
	height: 49px;
	top: 20px;
	right: 257px;
	position: absolute;
	z-index: 3;
}
#show_form_imput {
	height: 21px;
	position: absolute; float:right;
	z-index: 2;
}
#hide_form_imput {
	vertical-align: middle;
	height: 21px;
	position: absolute; float:right;
	visibility: hidden;
	z-index: 4;
}
#form_imput {
	visibility: hidden;
	position:absolute;
	display:none;
	top:30px;
	z-index:6;
	text-align:center;
	width: 300px;
	height: auto;
}
#form_imput #tabl_imput {
	border-top-width: 1px;
	border-right-width: 1px;
	border-bottom-width: 1px;
	border-left-width: 1px;
	border-top-style: solid;
	border-right-style: solid;
	border-bottom-style: solid;
	border-left-style: solid;
	visibility: inherit;
}
#form_imput #tabl_imput td {
	visibility: inherit;
}
#imput_running {
	visibility: hidden;
	display:none;
	text-align: center;
	z-index: 5;
	top:30px;
	position: absolute; float:right;
	width: 275px;
}
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
		height: 280px;
		overflow: auto;
		width: 98%;
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
<div>
<div id="result_val_diag"><div id="result_val_diag_ctt"></div></div>
<table class="center-table" width="98%" style="height:550px">
	<caption><?php echo recherche_libelle_page('CaptionVal'); ?></caption>
  <tr>
    <td>
		<!--debut config criteres ctrl -->
		<?php require $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_validation_criteres.php';
		?>
		<!--fin config criteres ctrl -->
		
		<!--debut liste etabs -->
		<?php if( $do_action_post == true ){
				ini_set("memory_limit", "128M");
				//require $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_coherence_list_etabs.php';
				echo "<div id='load_etabs_inchr' style='display:inline' style='text-align:center'>" ; 
					include $GLOBALS['SISED_PATH_INC'] . 'administration/progressbarCtrlData.php';
				echo "</div>";        
        echo "<table border='1' width='100%' id='valid_result'>" ; 
        echo "</table>";
				//include('C:\wamp\www\test\test_ctrl_list_etabs.html'); 
			}
		?>
		<!--fin liste etabs -->
		
	</td>
   </tr>
</table>
</div>
