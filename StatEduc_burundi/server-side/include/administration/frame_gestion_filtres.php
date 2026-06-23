<br>
<table align="center">
    <tr> 
        <td><?php echo recherche_libelle_page(code_period); ?></td>
        <td><INPUT type="text" maxlength="3" size="2" name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page(lib_period); ?></td>
        <td><INPUT type="text" size="10" name="<?php echo $GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'] ; ?>" value="<?php echo $val[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page(lib_trad_period); ?></td>
        <td><INPUT type="text" size="10" name="LIBELLE_TRAD" value="<?php echo $val['LIBELLE_TRAD']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page(ord_period); ?></td>
        <td><INPUT type="text" size="10" name="<?php echo $GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']]; ?>"></td>
    </tr>
</table>
<br>
