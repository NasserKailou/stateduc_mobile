<script language="JavaScript" type="text/javascript">
	function recharger(id_agg) {
		location.href   = 'synthese.php?val=OpenPopupRptAgg&id_rpt=<?php echo $_GET['id_rpt']?>&id_agg='+id_agg;
	}
</script>

<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'];
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);

	$requete                = '	SELECT * FROM DICO_AGGREGATION_LEVEL ORDER BY ID_AGGREGATION';
	//print $requete;
	$all_aggs 			= $GLOBALS['conn_dico']->GetAll($requete);

	if( $this->action == 'Open' ){
		$selected_agg	=	$val['ID_AGGREGATION'];
	}elseif(isset($_GET['id_agg']) and (trim($_GET['id_agg']) <> '')){
		$selected_agg	= $_GET['id_agg'] ;
	}else{
		$selected_agg	= $val['ID_AGGREGATION'];
	}
	
	$requete                = '	SELECT DICO_AGGREGATION_LEVEL.*
								FROM DICO_AGGREGATION_LEVEL
								WHERE DICO_AGGREGATION_LEVEL.ID_AGGREGATION =' . $selected_agg . ' ;';
	//print $requete;
	$tab_agg_choisi			= $GLOBALS['conn_dico']->GetAll($requete);

	if(isset($_GET['id_agg']) and (trim($_GET['id_agg']) <> '') and ($this->action <> 'Open') ){
		$val['LIBELLE_DICO_AGGREGATION']	= $tab_agg_choisi[0]['LIBELLE_AGGREGATION'] ;
	}
?>
<table align="center" border="1" width="400">
    <tr> 
        <td colspan="2"   align="center"><?php echo recherche_libelle_page('nom_rpt'); ?> 
            : <b><?php echo $nom_rpt; ?></b></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_rpt'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_agg'); ?></td>
        <td width="60%"> <select style="width : 100%;" name="ID_AGGREGATION">
                <option value=''></option>
                <?php foreach ($all_aggs as $i => $agg){
					echo "<option value='".$agg['ID_AGGREGATION']."'";
					if ($agg['ID_AGGREGATION'] == $selected_agg){
						echo " selected";
					}
					echo ">".$agg['LIBELLE_AGGREGATION']."</option>";
				}
				?>
            </select></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_agg'); ?></td>
        <td width="60%"><INPUT style="width : 95%;" type="text" size="20" name="LIBELLE_DICO_AGGREGATION" value="<?php echo $val['LIBELLE_DICO_AGGREGATION']; ?>"></td>
    </tr>
</table>
<br>
