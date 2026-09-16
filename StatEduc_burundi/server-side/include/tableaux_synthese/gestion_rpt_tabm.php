<br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		
		$champs	= array();
		$champs[] = array('nom'=>'ID', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[ID]', 'lib'=>'ID', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_RPT_TABLE_MERE', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_tabm', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'NOM_TABLE_MERE', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'nom_tabm', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		$champs[] = array('nom'=>'NOM_ALIAS', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'alias', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		
		$table 						= new gestion_table_simple() ;
		
		$table->table				= 'DICO_RPT_TABLE_MERE';
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'NOM_TABLE_MERE';
		$table->taille_combo		= '250';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'tableaux_synthese/frame_gestion_rpt_tabm.php';
		$table->taille_ecran		= '600';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesRptTab';
		
		$tab_concat = array();
		$tab_concat[] = array( 'champ' => 'NOM_ALIAS', 'table_cible' => 'DICO_RPT_TABLE_MERE', 'champ_cible' => 'NOM_ALIAS', 'champ_extract' => 'NOM_ALIAS', 'separatorDeb' => '(', 'separatorFin' => ')');

		$table->tab_concat_combo = $tab_concat ;

		$table->run();
		
?>
<!--
<table align="center" border="0" width="500">
    <tr> 
        <td width="50%"><?php echo recherche_libelle_page('import_rpt_table'); ?></td>
      <td width="50%"><input style="width:100%;" onClick="OpenPopupImportRptTable(<?php echo $_GET['id_rpt'];?>);" type="button" 
	  <?php echo ' value="'.recherche_libelle_page('BtnImpRptTable').'"'; ?>></td>
    </tr>
</table>
-->