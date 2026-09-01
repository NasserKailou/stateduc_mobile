<br>
<table align="center">
    <tr> 
        <td><?php echo recherche_libelle_page('code_ann'); ?></td>
        <td><INPUT type="text" maxlength="3" size="2" name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('lib_ann'); ?></td>
        <td><INPUT type="text" size="10" name="<?php echo $GLOBALS['PARAM']['LIBELLE']."_".$GLOBALS['PARAM']['TYPE_ANNEE'] ; ?>" value="<?php echo $val[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('lib_trad'); ?></td>
        <td><INPUT type="text" size="10" name="LIBELLE_TRAD" value="<?php echo $val['LIBELLE_TRAD']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('ord_ann'); ?></td>
        <td><INPUT type="text" size="10" name="<?php echo $GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]; ?>"></td>
    </tr>
</table>
<br>
