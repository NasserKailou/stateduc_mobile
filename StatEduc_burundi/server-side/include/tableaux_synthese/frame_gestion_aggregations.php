<br>
<table align="center" width="400">
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('id_agg'); ?></td>
        <td width="60%"><INPUT style="width : 90%" readonly="1" type="text" size="3" name="ID_AGGREGATION" value="<?php echo $val['ID_AGGREGATION']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('lib_agg'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="50" name="LIBELLE_AGGREGATION" value="<?php echo $val['LIBELLE_AGGREGATION']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('max_hier'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="30" name="HIERARCHY_LEVEL_MAX" value="<?php echo $val['HIERARCHY_LEVEL_MAX']; ?>"></td>
    </tr>
    <tr> 
        <td width="40%"><?php echo recherche_libelle_page('hier'); ?></td>
        <td width="60%"><INPUT style="width : 90%" type="text" size="30" name="HIERARCHY_LEVEL" value="<?php echo $val['HIERARCHY_LEVEL']; ?>"></td>
    </tr>
</table>
<br>
