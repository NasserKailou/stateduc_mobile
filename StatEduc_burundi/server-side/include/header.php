<?php 
lit_libelles_page('/accueil.php');

$curpage = $_SERVER['PHP_SELF'];

$gets = '';

foreach($_GET as $i=>$g) {
    $catch = false;
    if($i != 'style') {
        $catch = true;
        $gets .= $i . '=' . $g;
    }

    if($catch && $i<(count($_GET))) {
        $gets .= '&';
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<HTML xmlns="http://www.w3.org/1999/xhtml">
<HEAD>
<TITLE>StatEduc 2 - <?php echo recherche_libelle_page('Accueil'); ?></TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<?php if ( strpos($gets, "gest_rpt") !== false ) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>easy-responsive-tabs.css" />
<?php } ?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS'] . $_SESSION['style']; ?>">
<?php if (strpos($gets, "OpenPopupZoneOrdre") !== false) { ?>
<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>ordre_zone.css" />
<?php } ?>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/jquery-ui/base/jquery.ui.all.css"/>
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/uniform/default/css/uniform.default.css"/>
<!--LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>themes/uniform/aristo/css/uniform.aristo.css"/-->
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>base.css"/>
<!--?php if ((strpos($gets, "param") !== false) || (strpos($gets, "connexion") !== false)) { ?-->
<LINK rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>plupload/css/jquery.ui.plupload.css"/>
<!--?php } ?-->
<script type="text/Javascript">
function centre(page,largeur,hauteur,options)
{
var top=(screen.height-hauteur)/2;
var left=(screen.width-largeur)/2;
window.open(page,"","top="+top+",left="+left+",width="+largeur+",height="+hauteur+","+options);
}

function ouvrir() {
	var index=document.forms[0].elements[0].options.selectedIndex;
	if (index>0) {
		var page=document.forms[0].elements[0].options[index].value;
		window.open(page,"","");
	}
}

function set_style(style) {
<?php echo "\t".'document.location.href = \''.$curpage.'?'.$gets.'style=\' + style;'."\n"; ?>
}

</script>                                                                                                            
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>cron.js-master/cron.cc.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery-1.9.1.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery-migrate-1.2.1.js"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>uniform/jquery.uniform.js"></script>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/ui/jquery-ui.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>jquery/jquery.blockUI.js"></script>
<script type="text/javascript" src="<?php if(isset($GLOBALS['PARAM']['UNIFORM_STYLE_ACTIVATED']) && $GLOBALS['PARAM']['UNIFORM_STYLE_ACTIVATED']==false) echo $GLOBALS['SISED_URL_JSC'].'stateduc_without_uniform_style.js'; else echo $GLOBALS['SISED_URL_JSC'].'stateduc.js' ?>"></script>
	
<?php if (strpos($gets, "OpenPopupZoneOrdre") !== false) { ?>
<script type="text/Javascript">
	$(function() {
		$( "#sortable" ).sortable({
			create: function(event, ui ) {initializeZone(); sortZone( event, ui );},
			stop: function(event, ui ) {sortZone( event, ui )},
			placeholder: "ui-state-highlight"
		}); 
		$( "#sortable" ).disableSelection();
		}
	);
	
	/** Initialise les zones en placant les nouvelles zones en fin de page **/
	function initializeZone() {
		$($( "#sortable li input[name*='ORDRE_AFFICHAGE_'][value='']" ).get().reverse()).each(function( index ) {			
			$(this).val(0);
			var li = $(this).closest("li");
			$(li).remove();
			$( "#sortable" ).append($(li));
		});
	}
	
	/** Met les bons numéros aux zones **/
	function sortZone( event, ui ) {		
		$( "#sortable li input[name*='ORDRE_AFFICHAGE_']" ).each(function( index ) {
			$(this).val(index + 1);
		});
	}
</script>		
<?php } ?>
<!--?php if ((strpos($gets, "param") !== false) || (strpos($gets, "connexion") !== false)) { ?-->
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/plupload.full.min.js" charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/jquery.ui.plupload/jquery.ui.plupload.min.js"  charset="UTF-8"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>plupload/i18n/<?php echo $_SESSION['langue']; ?>.js" charset="UTF-8"></script>
<!--?php } ?-->
</HEAD>

<BODY<?php if ( strpos($gets, "list_rpt") == false && strpos($gets, "id_rpt") !== false ) { ?> class="no_bg" <?php } else { echo " class=\"".$gets."\"";} ?>>
<script type="text/Javascript">	
	/*$.blockUI({ 
		css: { 
			border: 'none', 
			padding: '5px', 
			backgroundColor: '#000', 
			'-webkit-border-radius': '10px', 
			'-moz-border-radius': '10px', 
			opacity: .5, 
			color: '#fff'
		}, 
		message: "<h3><?php echo recherche_libelle_page('pageLoading'); ?></h3>"
	});*/
	
/*
$(function() {
	$.get( "http://192.168.43.107:9090/StatEduc_Madagascar_MEN_FRE_Demo/data_reload.php/theme_data/kaf/3/703/1/26189/1", function(formData) {
		var formDataTab = null;
		try {
			formDataTab = JSON.parse(formData);	
			if (formDataTab.se_status == 400) {
				$.mobile.loading( 'hide' );
				alert(formDataTab.se_data);
			} else {
				$.each(formDataTab, function(key, formElt) {
					console.log(key + ":" +formElt[0]+"/"+formElt[1]);						 
				});	
			}
		} catch(e) {
			formDataTab = JSON.parse(formData); 
			console.log("ppp"+formDataTab.length);
			$.each(formDataTab, function(index, formElt) {
				console.log(formElt);						 
			});				
		}
	});		
});*/
</script>
<div id="div_dialog">
	<!--p id="dialog_content" class="ui-widget-content ui-corner-all"></p-->
	<iframe id="dialog_content" class="ui-widget-content ui-corner-all" src="" ></iframe>
</div>
<div id="div_sousdialog">
	<!--p id="dialog_content" class="ui-widget-content ui-corner-all"></p-->
	<iframe id="sousdialog_content" class="ui-widget-content ui-corner-all" src="" ></iframe>
</div>
<div id="div_confirm" title="<?php echo recherche_libelle_page('confirme');?>">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 0px 0;"></span><span id="confirm_content"></span></p>
</div>
<?php 
if(isset($GLOBALS['cacher_interface']) && $GLOBALS['cacher_interface']) {
?>
<!--script language="JavaScript1.2">$(function() {jQuery("body").addClass('isPopup'); /*jQuery("body br:eq(0)").remove();*/ jQuery("body br:eq(0)").remove();});</script-->
<?php }
if(!isset($GLOBALS['cacher_interface']) || !$GLOBALS['cacher_interface']) {
?>

<div id="bandeau"></div>

<!-- Drapeau -->
<div id="drapeau"><img src="<?php echo $GLOBALS['SISED_URL_IMG']; ?>drapeaux/<?php print trim($_SESSION['DRAPEAU_PAYS']); ?>.gif" width="44" height="29" border="0"></div>
<!-- Fin Drapeau -->

<!-- Affichage du menu -->
<?php
if(isset($_GET['type_ent_stat'])){
	$type_ent_stat = $_GET['type_ent_stat'];
}elseif(isset($_SESSION['type_ent_stat'])){
	$type_ent_stat = $_SESSION['type_ent_stat'];
}
?>

<?php

if(isset($GLOBALS['afficher_menu_cubes']) || ($GLOBALS['afficher_menu_cubes'])){

?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>menus/coolmenus4.js"></script>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur'];?>/_menu_cubes.js"></script>
<?php }

elseif(isset($GLOBALS['afficher_menu_rpt']) || ($GLOBALS['afficher_menu_rpt'])){

?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>menus/coolmenus4.js"></script>
<?php if(isset($type_ent_stat) && $type_ent_stat<>""){ ?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur']. '/' . $type_ent_stat;?>/_menu_report.js"></script>
<?php }else{ ?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur'];?>/_menu_report.js"></script>
<?php }
}

elseif(!isset($GLOBALS['afficher_menu_saisie']) || !$GLOBALS['afficher_menu_saisie']) {

?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>menus/coolmenus4.js"></script>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue']; ?>/_menu_princ.js"></script>
<?php } 

else {

?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>menus/coolmenus4.js"></script>
<?php if(isset($type_ent_stat) && $type_ent_stat<>""){ ?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur']. '/' . $type_ent_stat;?>/_menu_saisie.js"></script>
<?php }else{ ?>
<script language="JavaScript1.2" src="<?php echo $GLOBALS['SISED_URL_JSC'] . 'menus/' . $_SESSION['groupe'] . '/' . $_SESSION['langue'] . '/' . $_SESSION['secteur'];?>/_menu_saisie.js"></script>
<?php }

}
?>

<!-- FIN Affichage du menu  -->

<div id="contenu">
<?php 
	}
?>