<script language="JavaScript" type="text/javascript">
	function recharger(id_mes) {
		location.href   = 'synthese.php?val=OpenPopupRptMes&id_rpt=<?php echo $_GET['id_rpt']?>&id_mes='+id_mes;
	}
</script>

<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'];
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);

	$requete                = '	SELECT * FROM DICO_MESURE ORDER BY LIBELLE_MESURE';
	//print $requete;
	$all_mesures 			= $GLOBALS['conn_dico']->GetAll($requete);

	if( $this->action == 'Open' ){
		$selected_mesure	=	$val['ID_MESURE'];
	}elseif(isset($_GET['id_mes']) and (trim($_GET['id_mes']) <> '')){
		$selected_mesure	= $_GET['id_mes'] ;
	}else{
		$selected_mesure	= $val['ID_MESURE'];
	}
	
	$requete                = '	SELECT DICO_MESURE.*
								FROM DICO_MESURE
								WHERE DICO_MESURE.ID_MESURE =' . $selected_mesure . ' ;';
	//print $requete;
	$tab_mes_choisi			= $GLOBALS['conn_dico']->GetAll($requete);

	if(isset($_GET['id_mes']) and (trim($_GET['id_mes']) <> '') and ($this->action <> 'Open') ){
		$val['LIBELLE_DICO_MESURE']	= $tab_mes_choisi[0]['LIBELLE_MESURE'] ;
		$val['ENTETE_MESURE']		= $tab_mes_choisi[0]['LIBELLE_MESURE_ENTETE'] ;
	}

?>
<table align="center" border="1" width="400">
    <tr> 
        <td style="min-width:60px"><?php echo recherche_libelle_page('id_rpt'); ?><br/><INPUT readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
        <td style="min-width:160px"><?php echo recherche_libelle_page('id_mes'); ?><br/> 
			<INPUT type="hidden" name="ID_MESURE" value="<?php echo $selected_mesure; ?>">
			<INPUT readonly="1" type="text" size="20" value="<?php 
                 foreach ($all_mesures as $i => $mes) {
					if ($mes['ID_MESURE'] == $selected_mesure){
						$selected_mes_lib = $mes['LIBELLE_MESURE'];
						echo $selected_mes_lib;
					}
				}
				?>">
		</td>
        <td style="min-width:150px"><?php echo recherche_libelle_page('ordre'); ?><br/><INPUT type="text" size="20" name="ORDRE" value="<?php echo $val['ORDRE']; ?>"></td>
        <td style="min-width:150px"><?php echo recherche_libelle_page('lib_mes'); ?><br/><INPUT type="text" size="20" name="LIBELLE_DICO_MESURE" value="<?php echo $val['LIBELLE_DICO_MESURE']; ?>"></td>
        <td style="min-width:150px"><?php echo recherche_libelle_page('ent_mes'); ?><br/><INPUT type="text" size="20" name="ENTETE_MESURE" value="<?php echo $val['ENTETE_MESURE']; ?>"></td>
    </tr>
</table>
