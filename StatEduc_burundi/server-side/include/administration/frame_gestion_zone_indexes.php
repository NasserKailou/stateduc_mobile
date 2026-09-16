<br>
<table>
    <tr> 
        <td><?php echo recherche_libelle_page('idzone'); ?></td>
        <td><INPUT type="text" size="5" name="ID_ZONE" value="<?php echo $val['ID_ZONE']; ?>" readonly=""></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('id_ind'); ?></td>
        <td><INPUT type="text" size="2" name="ID_INDEXE" value="<?php echo $val['ID_INDEXE']; ?>"></td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('chp_ind'); ?></td>
        <td>
		
		<select name="CHAMP_INDEXE" style='width:220px'>";
		<?php if(isset($_GET['table']) && trim($_GET['table'])<>'')
			{
				$ColTabBD	=	$GLOBALS['conn']->MetaColumnNames($_GET['table']);
				foreach ($ColTabBD as $col){
					echo "<option value='".$col."'";
					if ($val['CHAMP_INDEXE'] == $col){
						echo " selected";
					}
					echo ">".$col."</option>";
				}
			}	
		?>
		</select >	
		</td>
    </tr>
    <tr> 
        <td><?php echo recherche_libelle_page('val_def'); ?></td>
        <td><INPUT type="text" size="10" name="VALEUR_DEFAUT_INDEXE" value="<?php echo $val['VALEUR_DEFAUT_INDEXE']; ?>"></td>
    </tr>
</table>
<br>
