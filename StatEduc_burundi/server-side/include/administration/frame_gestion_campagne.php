<script type="text/Javascript">

function  setLibelleCamp() {
   var id = jQuery("#<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']; ?>").val();
   var lib = jQuery("#<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']; ?> option[value="+id+"]").html();
   jQuery("#<?php echo $GLOBALS['PARAM']['LIBELLE_STATUT']; ?>").val(lib);
}

</script>
<br>
<table align="center">
    <tr>
	  <td><?php echo recherche_libelle_page('code_annee'); ?></td>
	  <td><select name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']; ?>">
        <option value=''></option>
        <?php 
			$requete	="SELECT * FROM ".$GLOBALS['PARAM']['TYPE_ANNEE']." ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'];
			$all_type_annee	= $GLOBALS['conn']->GetAll($requete);
			foreach ($all_type_annee as $i => $type_annee){
					echo "<option value='".trim($type_annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']])."'";
					if (trim($type_annee[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]) == trim($val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']])){
							echo " selected";
					}
					echo ">".$type_annee[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']]."</option>";
			}
		?>
      </select>
      </td>
	 </tr> 
   <tr>
	  <td><?php echo recherche_libelle_page('code_camp'); ?></td>
	  <td><select id="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']; ?>" name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']; ?>" onChange="setLibelleCamp();">
        <option value=''></option>
        <?php
			$requete	="	SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
			$all_type_survey 		= $GLOBALS['conn']->GetAll($requete);
			foreach ($all_type_survey as $i_camp => $camp){
				echo "<option value='".trim($camp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']])."'";
				if (trim($camp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]) == $val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]){
						echo " selected";
				}
				echo ">".$camp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]."</option>";
			}
		?>
      </select></td>
	</tr>
   <tr>
	  <td><?php echo recherche_libelle_page('statut_camp'); ?></td>
	  <td><select name="<?php echo $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'] ?>">
        <option value=''></option>
        <?php
			$requete	="	SELECT * FROM ".$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']." ORDER BY ".$GLOBALS['PARAM']['ORDRE']."_".$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT'];
			$all_type_survey 		= $GLOBALS['conn']->GetAll($requete);
			foreach ($all_type_survey as $i_camp => $camp){
				echo "<option value='".trim($camp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']])."'";
				if (trim($camp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']]) == $val[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']]){
						echo " selected";
				}
				echo ">".$camp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT_STATUT']]."</option>";
			}
		?>
      </select></td>
	</tr>   
	<tr> 
        <td><?php echo recherche_libelle_page('date_deb_camp'); ?></td>
        <td><INPUT type="text" size="10" name="<?php echo $GLOBALS['PARAM']['DATE_DEBUT_STATUT']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['DATE_DEBUT_STATUT']]; ?>"></td>
   </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('lib_camp'); ?></td>
        <td><INPUT type="text" size="60" id="<?php echo $GLOBALS['PARAM']['LIBELLE_STATUT']; ?>" name="<?php echo $GLOBALS['PARAM']['LIBELLE_STATUT']; ?>" value="<?php echo $val[$GLOBALS['PARAM']['LIBELLE_STATUT']]; ?>"></td>
    </tr>
</table>
<br>
