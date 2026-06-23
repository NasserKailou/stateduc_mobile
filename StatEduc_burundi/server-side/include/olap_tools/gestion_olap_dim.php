<br><br><br>
<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_table_simple.class.php'; 
?> 
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<?php //$GLOBALS['PARAM']['']
		$champs = array();
		$champs[] = array('nom'=>'ID_OLAP', 'type'=>'int', 'cle'=>'1', 'incr'=>'', 'val'=>'$_GET[id_olap]', 'lib'=>'id_olap', 'obli'=>'1', 'filtre'=>'1', 'ordre'=>'');
		$champs[] = array('nom'=>'ID_DIMENSION', 'type'=>'int', 'cle'=>'1', 'incr'=>'1', 'val'=>'', 'lib'=>'id_dim', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_DIMENSION', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'lib_dim', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'LIBELLE_ENTETE_DIM', 'type'=>'text', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ent_dim', 'obli'=>'', 'filtre'=>'', 'ordre'=>'');
		$champs[] = array('nom'=>'ORDRE', 'type'=>'int', 'cle'=>'', 'incr'=>'', 'val'=>'', 'lib'=>'ordre', 'obli'=>'1', 'filtre'=>'', 'ordre'=>'1');
		
		$table 						= new gestion_table_simple() ;
		
		$table->conn				= $GLOBALS['conn_dico'];
		
		$table->table				= 'DICO_OLAP_DIMENSION';
		$table->champs 				= $champs;
		$table->nom_champ_combo		= 'LIBELLE_DIMENSION';
		$table->frame				= $GLOBALS['SISED_PATH_INC'] . 'olap_tools/frame_gestion_olap_dim.php';
		$table->taille_ecran		= '500';
		$table->taille_combo		= '150';
		$table->btn_quit			= true;
		$table->titre_ecran			= 'GesOlapDim';
		//$table->code_libelle_trad	=$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
		
		//$table->tab_champs_trad[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']] = array('table' => $GLOBALS['PARAM']['TYPE_ANNEE'], 'libelle'=>'LIBELLE_TRAD');
		
		$table->run();
		
?>
<table align="center" border="0" width="500">
    
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('import'); ?></td>
      <td width="60%"><input style="width:100%;" onClick="OpenPopupImportDim(<?php echo $_GET['id_olap'];?>);" type="button" 
	  <?php echo ' value="'.recherche_libelle_page('BtnImpDim').'"'; ?>></td>
    </tr>
</table>

