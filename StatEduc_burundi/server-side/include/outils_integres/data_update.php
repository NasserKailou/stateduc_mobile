<?php 
lit_libelles_page('/data_update.php');
?>
<script language="JavaScript" src="<?php echo $GLOBALS['../administration/SISED_URL_JSC']; ?>js.js"></script>
<style type="text/css">
<!--
.Style1 {
	color: #FF0000;
	font-weight: bold;
}
-->
</style>
<br />
<FORM name="Formulaire"  method="post" action="">
  <div align="center">
	      <table class='espace_2' border='1'  width="50%">
            <CAPTION>
            <B><?php echo recherche_libelle_page('TraitementCodes'); ?></B>
            </CAPTION>
            <tr class="espace_2"> 
                <td><p><br>
				<?php echo recherche_libelle_page('RecodifVal'); ?><strong><?php echo recherche_libelle_page('ChpRecodif'); ?> :</strong>  
		          <INPUT type="text"  style="width:200px;" name="champ_maj" value="<?php echo $_POST['champ_maj'];?>">
			      <span class="Style1">(*)</span>,</p>
                  <p><?php echo recherche_libelle_page('ComprisesEntre'); ?> <strong><?php echo recherche_libelle_page('MinRecodif'); ?>:</strong>
                    <INPUT type="text"  style="width:100px;" name="min_val" value="<?php echo $_POST['min_val'];?>">
                    <span class="Style1">(*)</span> 
				  <?php echo recherche_libelle_page('EtRecodif'); ?> <strong><?php echo recherche_libelle_page('MaxRecodif'); ?>:</strong>      
				  <INPUT type="text"  style="width:100px;" name="max_val" value="<?php echo $_POST['max_val'];?>"> 
				  <em>(<?php echo recherche_libelle_page('RecodifFacult'); ?>),</em></p>
                  <p><?php echo recherche_libelle_page('DepartRecodif'); ?> : 
                    <INPUT type="text"  style="width:100px;" name="start_val" value="<?php echo $_POST['start_val'];?>">
                    <span class="Style1">(*)</span> <?php echo recherche_libelle_page('RecodifValInit'); ?>. <br />
              </p><br /></td>
            </tr>
            <tr> 
                <td align="center"> 
                        <TABLE class='espace_2' border='1'->
                            <tr> 
                                <td><INPUT name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"></td>
								 <td class="like_caption"><?php echo recherche_libelle_page('RecodifExec'); ?></td>
                            </tr>
                        </TABLE>                </td>
            </tr>
        </table><br />
  <span class="Style1">(*)</span> : <strong><?php echo recherche_libelle_page('RecodifChpsOblig'); ?></strong></div>
  <br /><br />

</FORM>

<?php if( count($_POST) > 0 ){
			
			if( (!$_POST['champ_maj']) or (!$_POST['min_val']) or (!$_POST['start_val']) ){
					$GLOBALS['msg_global'] = '\'Tous les Champs (*) sont Obligatoires\'';
			}elseif( ($_POST['max_val'] > 0) && ($_POST['max_val'] < $_POST['min_val']) ){
					$GLOBALS['msg_global'] = '\'La valeur Max doit etre supérieur à Min\'';
			}
			else{
					set_time_limit(0);
					ini_set("memory_limit", "128M");
	
					include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/data_update.class.php'; 	 
					
					$settings = array();
					
					$tab_secteurs = array();
					
					foreach($_SESSION['tab_secteur'] as $tab){
						$tab_secteurs[] = $tab[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
					}
					
					$settings['tab_secteurs']	=	$tab_secteurs ;
					
					$settings['champ_maj']		=	$_POST['champ_maj'] ;
					$settings['min_val']		=	$_POST['min_val'] ;
					$settings['max_val']		=	$_POST['max_val'] ;
					$settings['start_val']		=	$_POST['start_val'] ;
					
					$data_maj	=	new data_update($settings);
			}
	}

 
			
?>
