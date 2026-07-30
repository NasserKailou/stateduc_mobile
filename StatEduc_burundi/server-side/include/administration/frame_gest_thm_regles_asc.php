<br>
<table>
		<tr>
				<td><?php echo recherche_libelle_page(id_assoc); ?></td>
				<td><INPUT type="text" size="5" name="ID_ASSOC_REG_THM" value="<?php echo $val['ID_ASSOC_REG_THM']; ?>" readonly="1"></td>
		</tr>
    <tr> 
        <td>
						<?php echo recherche_libelle_page(regth);
								$requete	='SELECT DICO_REGLE_THEME.ID_REGLE_THEME
												FROM DICO_REGLE_THEME, DICO_THEME_SYSTEME, DICO_THEME 
												WHERE DICO_REGLE_THEME.ID_THEME = DICO_THEME_SYSTEME.ID
												AND DICO_THEME.ID = DICO_THEME_SYSTEME.ID 
												AND (((DICO_REGLE_THEME.ID_REGLE_THEME)<>'.$val['ID_REGLE_THEME'].') 
												AND ((DICO_THEME_SYSTEME.ID_SYSTEME)='.$_SESSION['secteur'].'))
												ORDER BY DICO_THEME.ORDRE_THEME, DICO_REGLE_THEME.ORDRE_REGLE_THEME';
								//echo $requete ;			
								$all_ID_REGLE_THEME = $GLOBALS['conn_dico']->GetAll($requete);

						?>				
				</td>
        <td>
					<INPUT type="hidden"  name="ID_REGLE_THEME" value="<?php echo $val['ID_REGLE_THEME']; ?>">
					<b><?php echo $this->recherche_libelle($val['ID_REGLE_THEME'],$this->langue,'DICO_REGLE_THEME'); ?></b>
				</td>
    </tr>
    <tr> 
        <td>
					<?php echo recherche_libelle_page(crit);
						$tab_CRITERE = array('==','<','<=','>','>=');
					?>
				</td>
        <td>
				<!--
					<select name="CRITERE">
					<option value=''></option>
							<?php /*
										foreach ($tab_CRITERE as $i => $critere){
												echo "<option value='".trim($critere)."'";
												if (trim($critere) == trim($val['CRITERE'])){
														echo " selected";
												}
												echo ">".$critere."</option>";
										}
								*/
								?>
					</select>-->
					<INPUT type="text" size="2" name="CRITERE" value="<?php echo $val['CRITERE']; ?>">
				</td>
    </tr>
    <tr> 
        <td valign="top"><?php echo recherche_libelle_page(regthasc); ?></td>
        <td>
			<select name="ID_REGLE_THEME_ASSOC">
					<option value=''></option>
					<?php foreach ($all_ID_REGLE_THEME as $i => $tab){
										echo "<option value='".trim($tab['ID_REGLE_THEME'])."'";
										if (trim($tab['ID_REGLE_THEME']) == trim($val['ID_REGLE_THEME_ASSOC'])){
												echo " selected";
										}
										echo ">".$this->recherche_libelle($tab['ID_REGLE_THEME'],$this->langue,'DICO_REGLE_THEME')."</option>";
								}
						
						?>
			</select>
		</td>
    </tr>
		<tr>
				<td valign="top"><?php echo recherche_libelle_page(mess_alert); ?></td>
				<td>
					<textarea name="MESSAGE_ALERT_THM" cols="50" rows="3"><?php echo $val['MESSAGE_ALERT_THM']; ?></textarea>
				</td>
	</tr>
	<tr>
				<td valign="top"><?php echo recherche_libelle_page(activ); ?></td>
				<td>
					<INPUT name="ACTIVER_CTRL" type="checkbox" value="1" <?php if($val['ACTIVER_CTRL']==1) echo' checked';?>>
				</td>
	</tr>
</table>
<br>
