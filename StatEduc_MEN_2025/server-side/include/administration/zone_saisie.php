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
	
	function load_type_champ(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr3.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr3.readyState == 4 && xhr3.status == 200){
				lesel = xhr3.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('TYPE_ZONE_BASE').innerHTML = lesel;
				unpdateFormElt();
			}
		}
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('CHAMP_FILS').innerHTML = leselect;
				unpdateFormElt();
			}
		}
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				letexte = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('SQL_REQ').innerHTML = letexte;
				unpdateFormElt();
			}
		}
		sel3 = document.getElementById('CHAMP_PERE');
		champ = sel3.options[sel3.selectedIndex].value;
		id_chp = sel3.options[sel3.selectedIndex].id;
		sel = document.getElementById('TABLE_FILLE');
		table = sel.options[sel.selectedIndex].value;
		chp_pere = document.getElementById('CHAMP_PERE').value;
		url3="administration.php?val=charger_chps&champ="+champ+"&id_chp="+id_chp+"&type_chp=<?php echo $this->TabValZone['TYPE_ZONE_BASE']; ?>&type_theme=0";
		url="administration.php?val=charger_chps&table="+table+"<?php echo '&chp_fils='.$this->TabValZone['CHAMP_FILS']; ?>&type_theme=0";
		url2="administration.php?val=charger_chps&val_table=<?php echo $this->TabValZone['TABLE_FILLE']; ?>&table="+table+"<?php echo '&chp_sql='.urlencode($this->TabValZone['SQL_REQ']); ?>&chp_pere="+chp_pere+"&type_theme=0";
		// Ici on va voir comment faire du get
		xhr3.open("GET",url3,true);	
		xhr.open("GET",url,true);
		xhr2.open("GET",url2,true);		
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr3.send(null);
		xhr.send(null);
		xhr2.send(null);
		unpdateFormElt();
	}
	
	function load_champs(){
		getXhr();
		// On défini ce qu'on va faire quand on aura la réponse
		xhr.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr.readyState == 4 && xhr.status == 200){
				leselect = xhr.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('CHAMP_FILS').innerHTML = leselect; alert(leselect);
				unpdateFormElt();
			}
		}
		xhr2.onreadystatechange = function(){
			// On ne fait quelque chose que si on a tout reçu et que le serveur est ok
			if(xhr2.readyState == 4 && xhr2.status == 200){
				letexte = xhr2.responseText;
				// On se sert de innerHTML pour rajouter les options a la liste
				document.getElementById('SQL_REQ').innerHTML = letexte;
				unpdateFormElt();
			}
		}
		sel = document.getElementById('TABLE_FILLE');
		table = sel.options[sel.selectedIndex].value;
		chp_pere = document.getElementById('CHAMP_PERE').value;
		url="administration.php?val=charger_chps&table="+table+"<?php echo '&chp_fils='.$this->TabValZone['CHAMP_FILS']; ?>&type_theme=0";
		url2="administration.php?val=charger_chps&val_table=<?php echo $this->TabValZone['TABLE_FILLE']; ?>&table="+table+"<?php echo '&chp_sql='.urlencode($this->TabValZone['SQL_REQ']); ?>&chp_pere="+chp_pere+"&type_theme=0";
		// Ici on va voir comment faire du get
		xhr.open("GET",url,true);
		xhr2.open("GET",url2,true);		
		// ici pas d'arguments pour la methode send() car arguments deja mis dans l'url
		xhr.send(null);
		xhr2.send(null);
		unpdateFormElt();
	}
	
</script>
<br>
<span class="classe_table"> 
<table>
<tr>
	<td colspan="4">
		<div> 
    		<table width="100%">
				<tr> 
					<td><?php echo recherche_libelle_page(LibChp); ?></td>
					<td nowrap><INPUT type="text" size="60" name="LIBELLE" value="<?php echo $this->TabValZone['LIBELLE']; ?>"><b>(<?php echo $this->langue; ?>)</b></td>
				</tr>
				<tr> 
					<td><?php echo recherche_libelle_page(ChPere); ?></td>
					<td>
						<select name="CHAMP_PERE" id="CHAMP_PERE" onchange="load_type_champ()">";
						<?php $this->get_col_table_db_2($this->TabValZone['TABLE_MERE']);
							echo "<option value=''></option>";
							foreach ($this->ColTabBD as $col){
								echo "<option value='".$col->name."' id='".$col->type."'";
								if ($this->TabValZone['CHAMP_PERE'] == $col->name){
									echo " selected";
								}
								echo ">".$col->name."</option>";
							}	
						?>
						</select >	
					</td>
				</tr>
				<tr> 
					<td><?php echo recherche_libelle_page(typeChbd); ?></td>
					<td>
						<div id='TYPE_ZONE_BASE' style='display:inline'>
						<select name="TYPE_ZONE_BASE">
							<option value=''></option>																		
						</select>
						</div>
					</td>
				</tr>
				<tr> 
					<td><?php echo recherche_libelle_page(TabMere); ?></td>														
            		<td><INPUT type="text" size="50" name="TABLE_MERE" value="<?php echo $this->TabValZone['TABLE_MERE']; ?>" readonly /></td>
				</tr>
			</table>
		</div>
	</td>
</tr>
<tr>
	<td colspan="2">
		<div> 
			<table>
				<tr> 
					<td nowrap>
					<?php echo recherche_libelle_page(OrdMet); 
						if($this->btn_add == true) $readonly=" readonly='1'";
						else $readonly="";
					?>
					</td>
					<td><INPUT type="text" size="2" name="ORDRE" value="<?php echo $this->TabValZone['ORDRE'].'"'.$readonly; ?>></td>
				</tr>
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(OrdTri); ?></td>
					<td><INPUT type="text" size="2" name="ORDRE_TRI" value="<?php echo $this->TabValZone['ORDRE_TRI']; ?>"></td>
				</tr>
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(NomGrp); ?></td>
					<td><INPUT type="text" size="5" name="NOM_GROUPE" value="<?php echo $this->TabValZone['NOM_GROUPE']; ?>"></td>
				</tr>
			</table>
		</div>			
	</td>									
	<td colspan="2" align="right">                
		<div> 
    		<table align="right">	
			<?php if( ($this->id_type_theme == 2) or ($this->id_type_theme == 3) or ($this->id_type_theme == 4) or ($this->id_type_theme == 5) or ($this->id_type_theme == 6) ){?>
				<tr> 
					<td valign="top" nowrap><?php echo recherche_libelle_page(ZoneRef); ?></td>
					<td>
						<select name="ID_ZONE_REF">
							<option value=''> </option>
							<?php $iTab 	= $this->iTab;
							//$iZone 	= $this->iZone;
							foreach ($this->TabGestZones['zones'][$iTab] as $iZone => $zone){
									echo "<option value='".$zone['ID_ZONE']."'";
									if ( trim($zone['ID_ZONE']) == trim($this->TabValZone['ID_ZONE_REF']) ){
											echo " selected";
									}
									echo ">".$zone['ID_ZONE'].'  ('.$zone['CHAMP_PERE'].')'."</option>";
							}
							?>									
						</select>
					</td>
				</tr>
				<?php } if($this->btn_add == false) {
								$iTab 		= $this->iTab;
								$iZone 		= $this->iZone; 
								$nb_zone 	= count($this->TabGestZones['zones'][$this->iTab]) ;
				?>
				<?php if($this->nb_TabM > 1){ ?>
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(ZoneJoin); ?></td>
					<td><input type='button' name='' value='...' onClick="OpenPopupZoneJoin(<?php echo $this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'];?>);"/></td>
				</tr>
				<?php } ?>
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(Regle); ?></td>
					<td><input type='button' name='' value='...'
								onClick=" OpenPopupZoneRegle(<?php echo $this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE'];?>);"></td>
				</tr>
				<?php } ?>			
			</table>
		</div>			
	</td>
</tr>
<tr>
	<td colspan="4">
		<div> 
    		<table width="100%">				
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(TabFille); ?></td>
					<td>
						<select name="TABLE_FILLE" id="TABLE_FILLE" onchange="load_champs()">
						<?php $this->get_tables_nomenc_db();
							echo "<option value=''></option>";
							foreach ($this->TabBD as $tab){
								echo "<option value='".$tab."'";
								if (strtoupper($this->TabValZone['TABLE_FILLE']) == strtoupper($tab)){
									echo " selected";
								}
								echo ">".$tab."</option>";
							}
						?>
						</select >
					</td>
				</tr>
				<tr>
					<td height="30" nowrap><?php echo recherche_libelle_page(ChFils); ?></td>
					<td>
						<div id='CHAMP_FILS' style='display:inline'>														
							<select name="CHAMP_FILS">
								<option value=''></option>
							</select>
						</div>														
					</td>
				</tr>
				<tr> 
					<td valign="top" nowrap><?php echo recherche_libelle_page(ReqSql); ?></td>
					<td>
						<div id='SQL_REQ' style='display:inline'>
						  <textarea name="SQL_REQ" cols="68" rows="8"></textarea>
						</div>														
					</td>
				</tr>
			</table>
		</div>			
	</td>
</tr>											
<tr>
	<td colspan="4">
		<?php if($this->btn_add == false){ ?>							
		<div> 
			<table>
				<tr> 
					<td nowrap><?php echo recherche_libelle_page(PropSect); ?></td>
					<td><input type='button' name='' value='...'
							onClick=" OpenPopupZoneSysteme(<?php echo $this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE']; ?>);"></td>
				</tr>
			</table>
		</div>
		<?php } ?>			
	</td>
</tr>
<tr>
	<td colspan="4" align="right">
		<div> 
    		<table>
				<tr> 
					<td nowrap align="center">
					<?php if($this->btn_add == true){ ?>
					<input type='button' name='Input' <?php echo 'value="'.recherche_libelle_page(Ajouter).'"'; ?>	
							<?php echo "onClick=\"javascript:Action(".$this->iTab.",".count($this->TabGestZones['zones'][$this->iTab]).",'Zone','AddZone');\""; ?> />
					<?php }else{?>
					&nbsp;<input type='button' name='' <?php echo 'value="'.recherche_libelle_page(Modifier).'"' ?>	 
							<?php echo "onClick=\"javascript:Action(".$this->iTab.",".$this->iZone.",'Zone','UpdZone');\""; ?>>
					&nbsp;<input type='button' name='' <?php echo 'value="'.recherche_libelle_page(Supprimer).'"' ?> 
						<?php echo "onClick=\"javascript:AvertirSupp(".$this->iTab.",".$this->iZone.",'Zone','".$this->TabValZone['ID_ZONE']."');\""; ?>>
					&nbsp;<INPUT type='button' name='' 
						<?php echo"value='".recherche_libelle_page(AjZone2)."'";?>
						<?php echo"onclick=\"javascript:Action(".$iTab.",".($this->iZone + 1).",'Zone','NewZone');\"";?>>
								<?php if( $this->iZone > 0 ){?>
											&nbsp;<INPUT type='button' name='' <?php echo"value=' < '";?>
											<?php echo"onclick=\"javascript:Action(".$iTab.",".($iZone - 1 ).",'Zone','OpenZone');\"";?>>
							<?php }?>

							<?php if( $this->iZone < ( count($this->TabGestZones['zones'][$iTab]) - 1 ) ){?>
											&nbsp;<INPUT type='button' name='' <?php echo"value=' > '";?>
											<?php echo"onclick=\"javascript:Action(".$iTab.",".($iZone + 1).",'Zone','OpenZone');\"";?>>
							<?php }?>
					<?php }// fin else ?>
					<INPUT type="hidden" id="RetourTabM" name="RetourTabM">
					&nbsp;<input type='button' name='Retour' <?php echo 'value="'.recherche_libelle_page(Retour).'"' ?>   onClick="document.getElementById( 'RetourTabM' ).value = <?php echo $this->iTab;?>; document.location.href = 'administration.php?val=gestzone&id_theme_choisi=<?php echo $this->id_theme.'&ret=1'.'&itab='.$iTab.'&izone='.$iZone.'&nb_tabm='.$this->nb_TabM.'#'.$this->TabGestZones['zones'][$iTab][$iZone]['ID_ZONE']."';" ; ?>">
					</td>
				</tr>
		</table>
	  </div>
			
	</td>
</tr>
</table>
</span>
	<div><br /><br /></div>
<script type='text/javascript'>
	load_type_champ();
</script>
