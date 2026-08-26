<?php
	$req_crit_sys = "SELECT ACTIVER_CRITERE FROM DICO_REGLE_SUIVI_SYSTEME WHERE ID_REGLE_SUIVI = ".$val['ID_REGLE_SUIVI']." AND ID_SYSTEME =".$_SESSION['secteur'];
	$crit_sys = $GLOBALS['conn_dico']->getAll($req_crit_sys);
?>
<br>
<table>

	<tr>
		<td><?php echo recherche_libelle_page('idregsuivi'); ?></td>
		<td><INPUT type="text" size="10" name="ID_REGLE_SUIVI" value="<?php echo $val['ID_REGLE_SUIVI']; ?>" readonly="1"></td>
	</tr>
	
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('ordregsuivi'); ?></td>
		<td><INPUT type="text" size="40" name="ORDRE_REGLE_SUIVI" value="<?php echo $val['ORDRE_REGLE_SUIVI']; ?>"></td>
	</tr>
	
	<tr> 
        <td width="40%"><?php echo recherche_libelle_page('activer').' : <b>'.get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE').'</b>';?></td>
        <td width="60%"><INPUT name="ACTIVER_CRITERE" type="checkbox" value="1" <?php if($crit_sys[0]['ACTIVER_CRITERE']==1) echo' checked';?>></td>
    </tr>
    
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('libregsuivi'); ?></td>
		<td><INPUT type="text" size="40" name="LIBELLE_TRAD" value="<?php echo $val['LIBELLE_TRAD']; ?>"></td>
	</tr>
	
	<tr>
		<td valign="top"><?php echo recherche_libelle_page('sqlregsuivi'); ?></td>
		<td><textarea name="SQL_REGLE_SUIVI" cols="40" rows="8"><?php echo $val['SQL_REGLE_SUIVI']; ?></textarea></td>
	</tr>
	
</table>
<br>
