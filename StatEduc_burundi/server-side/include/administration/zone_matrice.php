<script type='text/javascript'>
	var xhr = null; 
	var xhr2 = null;
	var xhr3 = null;
	
	function getXhr(){
		if(window.XMLHttpRequest){ // Firefox et autres
		   xhr = new XMLHttpRequest(); 
		   xhr2 = new XMLHttpRequest();
		   xhr3 = new XMLHttpRequest();
		}
		else if(window.ActiveXObject){ // Internet Explorer 
		   try {
					xhr = new ActiveXObject("Msxml2.XMLHTTP");
					xhr2 = new ActiveXObject("Msxml2.XMLHTTP");
					xhr3 = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (e) {
					xhr = new ActiveXObject("Microsoft.XMLHTTP");
					xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
					xhr3 = new ActiveXObject("Microsoft.XMLHTTP");
				}
		}
		else { // XMLHttpRequest non supporté par le navigateur 
		   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
		   xhr = false; 
		   xhr2 = false; 
		   xhr3 = false; 
		} 
	}
		
	/**
	* Méthode qui sera appelée sur le click du bouton
	*/
	function load_champs1(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('MESURE1').innerHTML = leselect;
			}
		}
		
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				leselect2 = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('MESURE2').innerHTML = leselect2;
			}
		}
		
		sel = document.getElementById('TABLE_MERE');
		table = sel.options[sel.selectedIndex].value;
		url="administration.php?val=charger_chps&table="+table+"<?php if(trim($this->TabValZone['MESURE1'])<>'') echo '&mes1='.$this->TabValZone['MESURE1']; else echo '&mes1=\'\''; ?>&type_theme=1";
		url2="administration.php?val=charger_chps&table="+table+"<?php if(trim($this->TabValZone['MESURE2'])<>'') echo '&mes2='.$this->TabValZone['MESURE2']; else echo '&mes2=\'\''; ?>&type_theme=1";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		xhr2.open("GET",url2,true);			
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		xhr2.send(null);	
	}
</script>
<br> <br />
<br />
<table align="center">
  <tr><td>
<table border="0" align="center">
  <tr>
    <td colspan="2"><?php echo recherche_libelle_page(TabMere); ?>	
		<select name="TABLE_MERE" id="TABLE_MERE" onchange="load_champs1()" style='width:280px'>
		<?php $this->get_tables_db();
			echo "<option value=''></option>";
			foreach ($this->TabBD as $tab){
				echo "<option value='".$tab."'";
				if (strtoupper($this->TabValZone['TABLE_MERE']) == strtoupper($tab)){
					echo " selected";
				}
				echo ">".$tab."</option>";
			}	
  		?>
        </select >		
	</td>
  </tr>
  <tr>
  	<td>
		<table>
			<caption><?php echo recherche_libelle_page('mes_1');?></caption>
			<tr>
				<td><?php echo recherche_libelle_page('chp_mes'); ?></td>
				<td>
				<div id='MESURE1' style='display:inline'>
				<select name="MESURE1" style="width: 180px">
					<option value=''></option>
				</select>
				</div>
				</td>
		  	</tr>
			<tr>
				<td><?php echo recherche_libelle_page('libmes1'); ?></td>
				<td><INPUT type="text" size="30" name="LIBELLE_MESURE1" value="<?php echo $this->TabValZone['LIBELLE_MESURE1']; ?>"></td>
		  	</tr>
			<tr>
				<td><?php echo recherche_libelle_page('type_mes'); ?></td>
				<td>
				<select name="TYPE_SAISIE_MESURE1">
				<option value=''></option>
				<?php foreach ($_SESSION['GestZones']['type_donnees'] as $i => $type_donnees){
									echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
									if (trim($type_donnees['TYPE_DONNEES']) == trim($this->TabValZone['TYPE_SAISIE_MESURE1'])){
											echo " selected";
									}
									echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
							}
					?>
				</select> 				
				</td>
		  	</tr>
		</table>
	</td>
   <td>
   		<table>
			<caption><?php echo recherche_libelle_page('mes_2');?></caption>
			<tr>
				<td><?php echo recherche_libelle_page('chp_mes'); ?></td>
				<td>
				<div id='MESURE2' style='display:inline'>
				<select name="MESURE2" style="width: 180px">
					<option value=''></option>
				</select>
				</div>
				</td>
		  	</tr>
			<tr>
				<td><?php echo recherche_libelle_page('libmes2'); ?></td>
				<td><INPUT type="text" size="30" name="LIBELLE_MESURE2" value="<?php echo $this->TabValZone['LIBELLE_MESURE2']; ?>"></td>
		  	</tr>
			<tr>
				<td><?php echo recherche_libelle_page('type_mes'); ?></td>
				<td><select name="TYPE_SAISIE_MESURE2">
				<option value=''></option>
				<?php foreach ($_SESSION['GestZones']['type_donnees'] as $i => $type_donnees){
									echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
									if (trim($type_donnees['TYPE_DONNEES']) == trim($this->TabValZone['TYPE_SAISIE_MESURE2'])){
											echo " selected";
									}
									echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
							}
					?>
				</select>
				</td> 				
		  	</tr>

		</table>
   </td>
  </tr>
</table>
</td></tr>
<tr><td>
    <table align="center">
		<tr><td colspan="2" align="center"><b><?php echo recherche_libelle_page(dims); ?></b></td></tr>
		<tr> 
			<td width="50%" align="center">
				<table width="95%">
					<tr><td colspan="2" align="center"><b><?php echo recherche_libelle_page(ligne); ?></b></td></tr>
					<tr>
						<td>
							<?php echo recherche_libelle_page(choixdim); ?>
						</td>
						<td>
							<select name="ID_DIMENSION1">
									<option value=''> </option>
									<?php foreach ($this->TabGestZones['dimensions'] as $i => $dim){
											echo "<option value='".$dim['ID_DIMENSION']."'";
											if ( trim($dim['ID_DIMENSION']) == trim($this->TabValZone['ID_DIMENSION1']) ){
													echo " selected";
											}
											if(trim($dim['DIM_LIBELLE_ENTTE'])<>'')
												echo ">".$dim['DIM_LIBELLE_ENTTE']."</option>";
											else
												echo ">".$dim['TABLE_REF']."</option>";
									}
									?>
									
							</select>
						</td>
					</tr>
					<tr> 
							<td nowrap><?php echo recherche_libelle_page(nblidim); ?></td>
							<td><INPUT type="text" size="5" name="NB_LIGNES_DIMENSION1" value="<?php echo $this->TabValZone['NB_LIGNES_DIMENSION1']; ?>"></td>
					</tr>

				</table>
			</td>
			<td width="50%" align="center">
				<table width="95%">
					<tr><td colspan="2" align="center"><b><?php echo recherche_libelle_page(colonne); ?></b></td></tr>
					<tr>
						<td>
							<?php echo recherche_libelle_page(choixdim); ?>
						</td>
						<td>
							<select name="ID_DIMENSION2">
									<option value=''> </option>
									<?php foreach ($this->TabGestZones['dimensions'] as $i => $dim){
											echo "<option value='".$dim['ID_DIMENSION']."'";
											if ( trim($dim['ID_DIMENSION']) == trim($this->TabValZone['ID_DIMENSION2']) ){
													echo " selected";
											}
											if(trim($dim['DIM_LIBELLE_ENTTE'])<>'')
												echo ">".$dim['DIM_LIBELLE_ENTTE']."</option>";
											else
												echo ">".$dim['TABLE_REF']."</option>";
									}
									?>
									
							</select>
						</td>
					</tr>
					<tr> 
							<td nowrap><?php echo recherche_libelle_page(nblidim); ?></td>
							<td><INPUT type="text" size="5" name="NB_LIGNES_DIMENSION2" value="<?php echo $this->TabValZone['NB_LIGNES_DIMENSION2']; ?>"></td>
					</tr>

				</table>
			</td>
		</tr>
	</table>
</div>
</td></tr>
<tr><td>
<table align="center">
       <?php if($this->btn_add == false){ ?>

        <tr> 
            <td nowrap align="right"><?php echo recherche_libelle_page(indexes); ?></td>
            <td><input type='button' name='' value='...'
																onClick=" OpenPopupZoneIndexes(<?php echo $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];?>,document.getElementById('TABLE_MERE').value);"></td>
        </tr>
        <tr> 
            <td nowrap align="right"><?php echo recherche_libelle_page(Regle); ?></td>
            <td><input type='button' name='' value='...'
																onClick=" OpenPopupZoneRegle(<?php echo $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];?>);"></td>
        </tr>
				<tr> 
						<td nowrap align="right"><?php echo recherche_libelle_page(PropSect); ?></td>
						<td><input type='button' name='' value='...'
								onClick=" OpenPopupZoneSysteme(<?php echo $this->TabGestZones['zone_matrice'][0]['ID_ZONE'];?>);"></td>
				</tr>
        <?php } ?>
    </table>
								</div>
<br>							
<div align="right"> 
    <table>
										<tr> 
												<td nowrap align="center">
												<?php if($this->btn_add == true){ ?>
												<input type='button' name='' <?php echo 'value="'.recherche_libelle_page(Ajouter).'"'; ?>	
														<?php echo "onClick=\"javascript:Action('0','0','Zone','AddZone');\""; ?>> 
												<?php }else{?>
												&nbsp;<input type='button' name='' <?php echo 'value="'.recherche_libelle_page(Modifier).'"' ?>	 
														<?php echo "onClick=\"javascript:Action('0','0','Zone','UpdZone');\""; ?>>
												&nbsp;<input type='button' name='' <?php echo 'value="'.recherche_libelle_page(Supprimer).'"' ?> 
													<?php echo "onClick=\"javascript:AvertirSupp('0','0','Zone','".$this->TabValZone['ID_ZONE']."');\""; ?>>
                <?php }?>
                 </td>
										</tr>
		</table>
	  </div>
</div> 
</td></tr>
</table>
<div><br /><br /></div>
<script type='text/javascript'>
	load_champs1();
</script>
