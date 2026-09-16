<!-- Rappel des infos etablissement -->
<?php
if(!isset($_SESSION['tmis'])){
	$style_infos_etab = '';
	if(isset($GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB']) && isset($GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']]) && $GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']] <> ''){
	 	$style_infos_etab = ' style="'.$GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']].'"';
	}
	
?>
	<div id="rappel" <?php echo $style_infos_etab;?>>
    <A href="questionnaire.php?theme=<?php echo $_SESSION['theme']; if(isset($_SESSION['type_ent_stat'])) echo "&type_ent_stat=".$_SESSION['type_ent_stat']; ?>" style=" text-decoration:none ; " >
    <p <?php echo $style_infos_etab;?>><?php print '<b>' . $_SESSION['hierarchie_regroup'] . '</b>'; ?></p>
    <p <?php echo $style_infos_etab;?>><?php print $_SESSION['infos_etab'].$_SESSION['infos_data_entry']; ?></p>
    </A>
    </div>
<?php
}
?>
<!-- Fin rappel des infos etablissement -->

