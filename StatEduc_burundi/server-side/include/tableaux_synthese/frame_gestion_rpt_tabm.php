<?php //echo '<pre>';print_r($_GET);
	$requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['ID'];
	//print $requete;
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);

	$all_type_req = array(  
							array ('CODE' => '0', 'LIBELLE' => recherche_libelle_page('donnees') ),
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('admin') )
						 );
?>

<table align="center" border="1" width="100%">
	<caption style="text-align:center;"><B><?php echo recherche_libelle_page('objet').' : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID" value="<?php echo $val['ID']; ?>">  
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('id_tabm'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID_RPT_TABLE_MERE" value="<?php echo $val['ID_RPT_TABLE_MERE']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('nom_tabm'); ?></td>
      <td width="88%">
	  	  <select name="NOM_TABLE_MERE" id="NOM_TABLE_MERE">
		  <?php 
			$TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
			foreach($TabBD as $tab)
			{
				$deb_fact_tab = 'FACT_TABLE_';
				if(!preg_match("/^($deb_fact_tab)/",strtoupper($tab)) && !preg_match("/^(DICO_)/",strtoupper($tab)) && !preg_match("/^(ADMIN_DROITS/)",strtoupper($tab)) && !preg_match("/^(ADMIN_GROUPES)/",strtoupper($tab)) && !preg_match("/^(ADMIN_USERS)/",strtoupper($tab)) && !preg_match("/^(PARAM_DEFAUT)/",strtoupper($tab)) && !preg_match("/^(SYSTEME)/",strtoupper($tab)))
				{
					$tables[] = $tab;
				}
			}
			if($GLOBALS['conn']->databaseType<>'access'){
				$ReqBD	=	$GLOBALS['conn']->MetaTables("VIEWS");
				foreach($ReqBD as $tab)
				{
					$tables[] = $tab;
				}
			}
			$TabBD = $tables;
			sort($TabBD);
			echo "<option value=''></option>";
			foreach ($TabBD as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($val['NOM_TABLE_MERE']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}
		?>
		</select >

	  <!--<INPUT style="width : 100%;" type="text" size="3" name="NOM_TABLE_MERE" value="<?php echo $val['NOM_TABLE_MERE']; ?>"></td>-->
    </tr>	
	
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('alias'); ?></td>
      <td width="88%"><INPUT style="width : 95%;" type="text" size="20" name="NOM_ALIAS" value="<?php echo $val['NOM_ALIAS']; ?>"></td>
    </tr> 
</table>

<?php if( $this->action == 'Del')
	{
		
		$req='DELETE FROM DICO_RPT_CHAMP WHERE ID_RPT_TABLE_MERE = '.$_POST['ID_RPT_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_RPT_JOINTURES WHERE ID_RPT_TABLE_MERE_1 = '.$_POST['ID_RPT_TABLE_MERE'].' OR ID_RPT_TABLE_MERE_2 = '.$_POST['ID_OLAP_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_RPT_CRITERE WHERE ID_TABLE = '.$_POST['ID_RPT_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);	
		
	}
?>
