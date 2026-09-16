<br>
<table align="center">
    <tr> 
        <td><?php echo recherche_libelle_page('code_syst'); ?></td>
        <td><INPUT type="text" size="2" name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('lib_syst'); ?></td>
        <td><INPUT type="text" size="30" name="<?php echo $GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('lib_trad'); ?></td>
        <td><INPUT type="text" size="30" name="LIBELLE_TRAD" value="<?php echo $val['LIBELLE_TRAD']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('ord_syst'); ?></td>
        <td><INPUT type="text" size="2" name="<?php echo $GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']]; ?>"></td>
    </tr>
	<!--    <tr> 
        <td><?php //echo recherche_libelle_page('tr_age'); ?></td>
        <td><INPUT type="text" size="2" name="<?php //echo $GLOBALS['PARAM']['CODE_TYPE_TRANCHE_AGE']; ?>" value="<?php //echo $val[$GLOBALS['PARAM']['CODE_TYPE_TRANCHE_AGE']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php //echo recherche_libelle_page('age_ret'); ?></td>
        <td><INPUT type="text" size="2" name="<?php //cho $GLOBALS['PARAM']['AGE_RETRAITE']; ?>" value="<?php //echo $val[$GLOBALS['PARAM']['AGE_RETRAITE']]; ?>"></td>
    </tr>-->
</table>
<br>
