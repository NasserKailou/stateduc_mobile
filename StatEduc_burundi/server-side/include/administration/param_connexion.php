<?php $cnx_box_types = $connexion->build_cnx_box_type('type');

?>

<script type="text/Javascript">
<!--

function toggle_sources() {

    frm = document.connexion;
    cmb = frm.sources;
    curconnexion = cmb.options[cmb.selectedIndex].value;
    document.location.href = 'administration.php?val=param&connexion='+curconnexion;

}

-->
</script>

<FORM name="connexion" method="post" action="accueil.php?val=param" enctype="multipart/form-encoded">
<TABLE>

<TR>
<TD>
<select name="sources" onchange="toggle_sources();">
<?php foreach($connexion->sources as $s) {

    $selected = '';

    if(isset($_GET['connexion']) && $_GET['connexion'] == $s['id']) {

        $selected = ' selected';

    }

    echo '<option value="'.$s['id'].'"'.$selected.'>'.$s['nom'].'</option>' . "\n";

}

?>
</select>
</TD>
<TD></TD>
</TR>
<!--
<TR>
<TD colspan="2">
<INPUT type="text" name="nom" value="<?php echo $cur_nom; ?>">
<input type="hidden" name="id" value="<?php echo $cur_id; ?>">
</TD>
</TR>

<TR>
<TD colspan="2"><?php echo $cnx_box_types; ?></TD>
</TR>

<?php if(!isset($cur_type) || (isset($cur_type) && $cur_type != 'access')) {
?>
<TR>
<TD colspan="2"><INPUT type="text" name="serveur" value="<?php echo $cur_serveur; ?>"></TD>
</TR>
<?php } else {
?>
<TR>
<TD colspan="2"><INPUT type="file" name="serveur"></TD>
</TR>
<?php }
?>

<TR>
<TD colspan="2"><INPUT type="text" name="utilisateur" value="<?php echo $cur_utilisateur; ?>"></TD>
</TR>

<TR>
<TD colspan="2"><INPUT type="text" name="mdp" value="<?php echo $cur_mdp; ?>"></TD>
</TR>

<?php if(!isset($cur_type) || (isset($cur_type) && $cur_type != 'access')) {
?>
<TR>
<TD colspan="2"><INPUT type="text" name="base" value="<?php echo $cur_base; ?>"></TD>
</TR>
<?php }
?>
-->
</TABLE>
</FORM>
