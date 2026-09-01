<?php
	$tableMere = $_GET['tm'];
	$nomZone = $_GET['zn'];
	$idTheme = $_GET['th'];
	$OkAction = 1;
	$ColTabBD = array();
	
	$_SESSION['GestZones']['type_int'] = array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real');
	$_SESSION['GestZones']['type_text'] = array('12','C','char','nchar','ntext','nvarchar','text','varchar');
	$_SESSION['GestZones']['type_date'] = array('11','D','T','datetime','smalldatetime');
		
	if ( trim($tableMere == '') ) {
		$OkAction = 0;
		//return 0;
		//echo 'passe 2';
	}
	
	if ($OkAction == 1) { // s'il n'y a  aucun PB dans les données postées
		$ColTabBD	=	$GLOBALS['conn']->MetaColumns($tableMere);
		$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
											FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES < 10";
		$_SESSION['GestZones']['type_donnees'] = $GLOBALS['conn_dico']->GetAll($requete);
		
		$requete = "SELECT TYPE_OBJET, CODE_TYPE_OBJET, LIBELLE_TYPE_OBJET   
			FROM   DICO_TYPE_OBJET
			WHERE CODE_LANGUE = '".$_SESSION['langue']."'";
		
		$_SESSION['GestZones']['type_objet'] = $GLOBALS['conn_dico']->GetAll($requete);
	}
	
	function Aff_Type_Donnees($curr_typ) {
		$currType = "";	
		if(in_array($curr_typ, $_SESSION['GestZones']['type_int'])) { $currType = "int"; }
		elseif(in_array($curr_typ, $_SESSION['GestZones']['type_text'])) { $currType = "text"; }
		elseif(in_array($curr_typ, $_SESSION['GestZones']['type_date'])) { $currType = "date"; }
		
		echo "<select name='TYPE_ZONE_BASE'>";
		echo '<option value=\'\'></option>';	
		foreach ($_SESSION['GestZones']['type_donnees'] as $j => $type_donnees) {
			if (in_array($type_donnees['CODE_TYPE_DONNEES'], array(1,2,3))) {
				echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";							
				if (trim($type_donnees['TYPE_DONNEES']) == $currType ){
						echo " selected='selected'";
				}
				echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
			}
		}		
		echo "</select>";
	}
	
	function Aff_Type_Objets() {
		echo "<select name='TYPE_OBJET'>".
				"<option value=''></option>";
				foreach ($_SESSION['GestZones']['type_objet'] as $j => $type_objet) {
					echo "<option value='".trim($type_objet['TYPE_OBJET'])."'>";
					echo get_libelle($type_objet['CODE_TYPE_OBJET'],'DICO_TYPE_OBJET',$type_objet['LIBELLE_TYPE_OBJET'])."</option>";
				}
		 echo "</select>";
	}
	
?>
<script language="JavaScript" src="<?php echo $GLOBALS['SISED_URL_JSC']; ?>js.js"></script>
<form name="Formulaire" method="POST" action="" style="margin-top: 45px; left: 20px;">
	<div style=""> 
		<div style="text-align:right; font-weight:bold; margin-right:20px;"> Table : <?php echo $tableMere; ?></div>
		<table class="sous_liste_form" id="zone_table_<?php echo $iTab; ?>" cellspacing="0">
			<tbody>
				<tr class="zone_table_header ui-widget-header">
					<tr class="zone_table_header ui-widget-header">
						<th class=""><div>Ordre</div></th>
						<th class=""><div>Champ</div></th>
						<th class=""><div>Type</div></th>
						<th class=""><div>Libellé</div></th>
						<th class=""><div>Comportement</div></th>
					</tr>
				</tr>
				<?php 
					echo "<tr class='zone_liste liste_elt_a'>
						<td><input type='text' name='ORDRE' value='' size='2'/></td>
						<td><input class='readonly_input' type='text' name='CHAMP_PERE' value=\"".$nomZone."\"  readonly='readonly' size='40'/></td>
						<td>";
					$typeZone = "";
					if ($nomZone != "") {	
						foreach ($ColTabBD as $col) {
							if ($col->name == $nomZone) {
								$typeZone = $col->type;
								break;
							}
						}	
					}
					echo Aff_Type_Donnees($typeZone);
					echo "</td>
						<td><input type='text' name='LIBELLE_ZONE' value=\"\"  size='60'/></td>
						<td>";
					echo Aff_Type_Objets();
					echo "</td>
					</tr>";					
				?>
				<tr>
						<td colspan="6" align="right">
						<input type="button" value="ANNULER" name="btn_annuler_zone" onclick="javascript:fermer()" />&nbsp;<input type="button" value="AJOUTER" name="btn_ajouter_zone" onclick="javascript:fermer()" />
					</td>
				</tr>
			</tbody>
		</table>	
	</div>
</form>