<?php if(isset($_GET['nom_table'])){
		$_SESSION['table'] = $_GET['nom_table'];
	}

	$requete 	=	' SELECT * 
						FROM DICO_AGGREGATED_TABLE
						WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE_2 =\''.$_SESSION['table'].'\'';
	$tab  = $GLOBALS['conn_dico']->GetAll($requete);


?>
    <br/>
        <table>
			<INPUT type="hidden" name="ID_SYSTEME" id="ID_SYSTEME" value="<?php echo $val['ID_SYSTEME']; ?>"/>
			<INPUT type="hidden" name="ID_TABLE" id="ID_TABLE" value="<?php echo $val['ID_TABLE']; ?>"/>
			<tr> 
        		<td width="100%" align="center" nowrap="nowrap" colspan="2"><?php echo recherche_libelle_page('choix_table').' <b>'.$tab[0]['NOM_TABLE_2']; ?></b></td>
   			</tr>
			<tr><td >&nbsp;</td></tr>
			<tr>
				<td><?php echo recherche_libelle_page('NomChamp'); ?></td>
				<td>
				<select name="NOM_CHAMP" ID="NOM_CHAMP" style="width:250px" onchange="changer_val_champ()">
				<?php $req 	=	' SELECT NOM_TABLE 
									FROM DICO_AGGREGATED_TABLE
									WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE_2 = \''.$_GET['nom_table'].'\'';
					$table  = $GLOBALS['conn_dico']->GetOne($req);
					$requete 	=	' SELECT * 
										FROM DICO_AGGREGATED_FIELD
										WHERE ID_SYSTEME = '.$_SESSION['secteur'].' AND NOM_TABLE = \''.$table.'\'';
					$champs_aggreg  = $GLOBALS['conn_dico']->GetAll($requete);
					foreach($champs_aggreg as $champ){
						if($champ['FIXES_VALS']<>''){
							echo "<option value='".$champ['NOM_CHAMP']."'";
							if ($champ['NOM_CHAMP'] == $val['NOM_CHAMP']){
									echo " selected";
							}
							echo ">".$champ['NOM_CHAMP_2']."</option>\n";
						}
					}
				?>
				</select>
				</td>
			</tr>
            <tr> 
                <td nowrap><?php echo recherche_libelle_page('ValChamp');?></td>
                <td><INPUT type="text" size="4" name="VAL_CHAMP" value="<?php echo $val['VAL_CHAMP']; ?>"/></td>
            </tr>
		</table>
		<br/>
