<?php
set_time_limit(0);
// Fichiers d'includes'
if ($_GET['val'] == 'feedbacks_auto_delete') { 
  require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks_delete.php'; 
} else {  
  include_once $GLOBALS['SISED_PATH_LIB'] . 'lib.inc.php';
  include_once $GLOBALS['SISED_PATH_LIB'] . 'navigation.inc.php';
  include_once $GLOBALS['SISED_PATH_LIB'] . 'controle.inc.php';
  unset($_SESSION['reg_parents']);
  require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
  require_once 'common.php';
  require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/controle_theme_batch.class.php';
  
  lit_libelles_page('/controle_theme.php');
?>
<div>
<table class="center-table" width="98%" style="height:550px">
	<caption><?php echo recherche_libelle_page('CaptionFeedback'); ?></caption>
  <tr>
    <td>
		<!--debut config criteres ctrl -->
		<?php 
    if ($_GET['val'] == 'feedbacks') {
      require $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_collectors_feedbacks.php'; 
    } else if ($_GET['val'] == 'feedbacks_auto') { 
      require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks.php'; 
    } else if ($_GET['val'] == 'feedbacks_auto_add') { 
      require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks_add.php'; 
    } else if ($_GET['val'] == 'feedbacks_auto_modify') { 
      require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks_modify.php'; 
    } else if ($_GET['val'] == 'feedbacks_auto_firepjs') { 
      require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks_firepjs.php'; 
    } else if ($_GET['val'] == 'feedbacks_auto_error') { 
      require $GLOBALS['SISED_PATH_INC'] . 'administration/auto_collectors_feedbacks_error.php'; 
    }
		?>
		<!--fin config criteres ctrl -->
		
		<!--debut liste etabs -->
		<?php if( $do_action_post == true ){
				ini_set("memory_limit", "128M");
				//require $GLOBALS['SISED_PATH_INC'] . 'administration/ctrl_coherence_list_etabs.php';
				echo "<div id='load_etabs_inchr' style='display:inline' style='text-align:center'>" ; 
					include $GLOBALS['SISED_PATH_INC'] . 'administration/progressbarCtrlData.php';
				echo "</div>";
				//include('C:\wamp\www\test\test_ctrl_list_etabs.html'); 
			}
		?>
		<!--fin liste etabs -->
		
	</td>
   </tr>
</table>
</div>
<?php } ?>