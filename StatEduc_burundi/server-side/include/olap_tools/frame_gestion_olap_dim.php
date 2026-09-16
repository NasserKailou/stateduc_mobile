<script language="JavaScript" type="text/javascript">
	function recharger(id_dim) {
		location.href   = 'synthese.php?val=OpenPopupRptDim&id_rpt=<?php echo $_GET['id_rpt']?>&id_dim='+id_dim;
	}
</script>

<?php $requete                = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];
	//print $requete;
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);
	
	/*
	$requete                = '	SELECT * FROM DICO_DIMENSION 
								WHERE DICO_DIMENSION.ID_DIMENSION 
								NOT IN (SELECT DICO_OLAP_DIMENSIONS.ID_DIMENSION FROM DICO_OLAP_DIMENSIONS
										WHERE DICO_OLAP_DIMENSIONS.ID_OLAP='.$_GET['id_olap'].')
								ORDER BY DICO_DIMENSION.TABLE_REF'; */
	$requete                = '	SELECT * FROM DICO_DIMENSION 
								ORDER BY DICO_DIMENSION.TABLE_REF';
	//print $requete;
	$all_dims 			= $GLOBALS['conn_dico']->GetAll($requete);

	
	$all_type_dim = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('dim_ligne') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('dim_col') ),
                            array ('CODE' => '3', 'LIBELLE' => recherche_libelle_page('dim_imbr') )
						 );
?>
<table align="center" border="1" width="450">
    <caption style="text-align:center;"><B><?php echo 'Cube : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">
<tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_dim'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" readonly="1" type="text" size="20" name="ID_DIMENSION" value="<?php echo $val['ID_DIMENSION']; ?>"></td>
    </tr>    
<tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_dim'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="LIBELLE_DIMENSION" value="<?php echo $val['LIBELLE_DIMENSION']; ?>"></td>
    </tr>
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ordre'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="ORDRE" value="<?php echo $val['ORDRE']; ?>"></td>
    </tr>	
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('ent_dim'); ?></td>
        <td width="60%"><INPUT style="width : 100%;" type="text" size="20" name="LIBELLE_ENTETE_DIM" value="<?php echo $val['LIBELLE_ENTETE_DIM']; ?>"></td>
    </tr>
    
</table>
<br>
<?php if( $this->action == 'Del')
	{		
		$req='DELETE FROM DICO_OLAP_CHAMP WHERE ID_DIMENSION = '.$_POST['ID_DIMENSION'].' ;';
		$GLOBALS['conn_dico']->Execute($req);
	}
?>

