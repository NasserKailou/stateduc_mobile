<script language="JavaScript" type="text/javascript">
	function recharger(id_syst) {
		location.href   = 'synthese.php?val=OpenPopupRptSyst&id_rpt=<?php echo $_GET['id_rpt']?>&id_syst='+id_syst;
	}
</script>
<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'];
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);
	
	
	$requete                 = 'SELECT DICO_REPORT_SYSTEME.*, DICO_REPORT_SYSTEME.ID, DICO_REPORT_SYSTEME.TITRE_ETAT_MENU
								FROM DICO_REPORT_SYSTEME
								WHERE DICO_REPORT_SYSTEME.ID <> '.$_GET['id_rpt'].' 
								AND DICO_REPORT_SYSTEME.ID_SYSTEME = '.$GLOBALS['id_syst'].' 
								AND DICO_REPORT_SYSTEME.PERE = 0 
								AND DICO_REPORT_SYSTEME.ACTIVER = 1 
								ORDER BY DICO_REPORT_SYSTEME.ORDRE_AFFICHAGE_MENU;';
	//echo " <br> $requete <br>";
	$all_rpt_sys = $GLOBALS['conn_dico']->GetAll($requete);
	
	
		
	$all_page_ori = array(  
							array ('CODE' => 'L', 'LIBELLE' => recherche_libelle_page('paysage') ),
							array ('CODE' => 'P', 'LIBELLE' => recherche_libelle_page('portrait') )
						 );
						 
	$all_ori_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('colonne') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('ligne') )
						 );
						 
	$all_align_mes = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('gauche') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('centre') ),
							array ('CODE' => '3', 'LIBELLE' => recherche_libelle_page('droite') )
						 );
						 
	$this->btn_new = false;

?>

<table align="center" border="1" width="400">
    <tr> 
        <td colspan="6"  nowrap align="center"><?php echo recherche_libelle_page('nom_rpt'); ?> 
            : <b><?php echo $nom_rpt; ?></b></td>
    </tr>
    <tr> 
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('id_rpt'); ?><br> 
            <INPUT style="width : 95%;" readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
        <td colspan="3" width="50%"><?php echo recherche_libelle_page('id_syst'); ?><br> 
            <select  style="width : 100%;" name="ID_SYSTEME" onChange="recharger(this.value);">
				 <option value=''></option>
                <?php foreach ($_SESSION['tab_secteur'] as $i => $systeme){
							echo "<option value='".$systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]."'";
							if ($systeme[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']] == $GLOBALS['id_syst']){
								echo " selected";
							}
							echo ">".$systeme['LIBELLE']."</option>";
						}
				?>
            </select></td>
    </tr>
    <tr> 
        <td colspan="2" nowrap width="33%"> <?php echo recherche_libelle_page('activer'); ?> 
            <INPUT name="ACTIVER" type="checkbox" value="1" <?php if($val['ACTIVER']=='1') echo' checked';?>> 
            <br> </td>
        <td colspan="2" nowrap width="34%"><?php echo recherche_libelle_page('tot_vert'); ?> 
            <INPUT name="AFFICHER_TOTAL_VERTICAL" type="checkbox" value="1" <?php if($val['AFFICHER_TOTAL_VERTICAL']=='1') echo' checked';?>></td>
        <td colspan="2" nowrap width="33%"><?php echo recherche_libelle_page('tot_hori'); ?> 
            <INPUT name="AFFICHER_TOTAL_HORIZONTAL" type="checkbox" value="1" <?php if($val['AFFICHER_TOTAL_HORIZONTAL']=='1') echo' checked';?>></td>
    </tr>
    <tr> 
        <td colspan="3"><?php echo recherche_libelle_page('l_col'); ?> 
            <br> <INPUT style="width : 95%;" type="text" size="30" name="LARGEUR_COLONNE" value="<?php echo $val['LARGEUR_COLONNE']; ?>"></td>
        <td colspan="3"><?php echo recherche_libelle_page('l_col_zon'); ?> 
            <br> <INPUT style="width : 95%;" type="text" size="30" name="LARGEUR_COLONNE_ZONE" value="<?php echo $val['LARGEUR_COLONNE_ZONE']; ?>">
			</td>
    </tr>
    <tr> 
        <td colspan="3"><?php echo recherche_libelle_page('page_ori'); ?><br> 
			<select style="width : 100%;" name="PAGE_ORIENTATION">
                <option value=''></option>
                <?php foreach ($all_page_ori as $i => $page_ori){
					echo "<option value='".$page_ori['CODE']."'";
					if ($page_ori['CODE'] == $val['PAGE_ORIENTATION']){
						echo " selected";
					}
					echo ">".$page_ori['LIBELLE']."</option>";
				}
				?>
            </select>
			</td>
        <td colspan="3"><?php echo recherche_libelle_page('ori_mes'); ?> 
            <br>
			<select style="width : 100%;" name="ORIENTATION_MESURE">
                <option value=''></option>
                <?php foreach ($all_ori_mes as $i => $ori_mes){
					echo "<option value='".$ori_mes['CODE']."'";
					if ($ori_mes['CODE'] == $val['ORIENTATION_MESURE']){
						echo " selected";
					}
					echo ">".$ori_mes['LIBELLE']."</option>";
				}
				?>
            </select>
			</td>
    </tr>
    <tr> 
        <td colspan="3"><?php echo recherche_libelle_page('etat_menu'); ?><br>
            <INPUT style="width : 95%;" type="text" size="30" name="TITRE_ETAT_MENU" value="<?php echo $val['TITRE_ETAT_MENU']; ?>"> </td>
        <td colspan="3"><?php echo recherche_libelle_page('entete'); ?> 
            <br> <INPUT style="width : 95%;" type="text" size="30" name="ENTETE_PAGE" value="<?php echo $val['ENTETE_PAGE']; ?>"></td>
    </tr>
    <tr> 
        <td colspan="6"><?php echo recherche_libelle_page('tit_etat'); ?> 
            <br> <INPUT style="width : 95%;" type="text" size="30" name="TITRE_ETAT" value="<?php echo $val['TITRE_ETAT']; ?>"></td>
    </tr>
    <tr> 
        <td colspan="3"><?php echo recherche_libelle_page('pied'); ?><br> 
            <INPUT style="width : 95%;" type="text" size="30" name="PIED_PAGE" value="<?php echo $val['PIED_PAGE']; ?>"></td>
        <td colspan="3"><?php echo recherche_libelle_page('miss_data'); ?><br> 
            <INPUT style="width : 95%;" type="text" size="30" name="VAL_DONNEES_MANQUANTES" value="<?php echo $val['VAL_DONNEES_MANQUANTES']; ?>"></td>
    </tr>
    <tr> 
        <td colspan="3"><?php echo recherche_libelle_page('h_ligne'); ?> 
            <br> <INPUT style="width : 95%;" type="text" size="20" name="HAUTEUR_LIGNE" value="<?php echo $val['HAUTEUR_LIGNE']; ?>"></td>
        <td colspan="3"><?php echo recherche_libelle_page('align_mes'); ?> 
            <br><select style="width : 100%;" name="ALIGNEMENT_MESURE">
                <option value=''></option>
                <?php foreach ($all_align_mes as $i => $align_mes){
					echo "<option value='".$align_mes['CODE']."'";
					if ($align_mes['CODE'] == $val['ALIGNEMENT_MESURE']){
						echo " selected";
					}
					echo ">".$align_mes['LIBELLE']."</option>";
				}
				?>
            </select></td>
    </tr>
<tr> 
        <td colspan="3"><?php echo recherche_libelle_page('pere'); ?> 
            <br> <select style="width : 100%;" name="PERE">
                <option value='0'> List Reports </option>
                <?php foreach ($all_rpt_sys as $i => $rpt_sys ){
					echo "<option value='".$rpt_sys['ID'].$GLOBALS['id_syst']."'";
					if ( trim($rpt_sys['ID'].$GLOBALS['id_syst']) == trim($val['PERE'])){
						echo " selected";
					}
					echo ">".$rpt_sys['TITRE_ETAT_MENU']."</option>";
				}
				?>
            </select></td>
        <td colspan="3"><?php echo recherche_libelle_page('ordre'); ?><br>
             <INPUT style="width : 95%;" type="text" size="30" name="ORDRE_AFFICHAGE_MENU" value="<?php echo $val['ORDRE_AFFICHAGE_MENU']; ?>"></td>
</tr></table>
<br>
