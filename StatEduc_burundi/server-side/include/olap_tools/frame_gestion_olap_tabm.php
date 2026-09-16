<?php $requete                = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];
	//print $requete;
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);

	$all_type_req = array(  
							array ('CODE' => '0', 'LIBELLE' => recherche_libelle_page('donnees') ),
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('admin') )
						 );
?>

<table align="center" border="1" width="400">
	<caption style="text-align:center;"><B><?php echo 'Cube : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">  
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('id_tabm'); ?></td>
      <td width="88%"><INPUT style="width : 100%;" readonly="1" type="text" size="3" name="ID_OLAP_TABLE_MERE" value="<?php echo $val['ID_OLAP_TABLE_MERE']; ?>"></td>
    </tr>
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('nom_tabm'); ?></td>
      <td width="88%">
	  
	  <select name="NOM_TABLE_MERE" id="NOM_TABLE_MERE" style='width:280px'>
		<?php $TabBD	=	array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
			$tables = array();
				foreach($TabBD as $tab)
				{
					$deb_fact_tab = 'FACT_TABLE_';
					if(!eregi("^($deb_fact_tab)",strtoupper($tab)) && !eregi("^(DICO_)",strtoupper($tab)) && !eregi("^(ADMIN_DROITS)",strtoupper($tab)) && !eregi("^(ADMIN_GROUPES)",strtoupper($tab)) && !eregi("^(ADMIN_USERS)",strtoupper($tab)) && !eregi("^(PARAM_DEFAUT)",strtoupper($tab)) && !eregi("^(SYSTEME)",strtoupper($tab)))
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
	  </td>
    </tr>	
	
	<tr> 
        <td width="12%"><?php echo recherche_libelle_page('alias'); ?></td>
      <td width="88%"><INPUT style="width : 100%;" type="text" size="20" name="NOM_ALIAS" value="<?php echo $val['NOM_ALIAS']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('is_fictif'); ?> </td>      
        <td width="60%"><INPUT name="IS_FICTIF" type="checkbox" value="1" <?php if($val['IS_FICTIF']=='1') echo' checked';?>> </td>
    </tr>
    
	
    
</table>
<br>
<?php if( $this->action == 'Del')
	{
		
		$req='DELETE FROM DICO_OLAP_CHAMP WHERE ID_OLAP_TABLE_MERE = '.$_POST['ID_OLAP_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_JOINTURES WHERE ID_OLAP_TABLE_MERE_1 = '.$_POST['ID_OLAP_TABLE_MERE'].' OR ID_OLAP_TABLE_MERE_2 = '.$_POST['ID_OLAP_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
		
		$req='DELETE FROM DICO_OLAP_CRITERE WHERE ID_TABLE = '.$_POST['ID_OLAP_TABLE_MERE'].' ;';
		$GLOBALS['conn_dico']->Execute($req);	
		
	}
?>
