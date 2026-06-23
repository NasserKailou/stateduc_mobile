<?php $all_op		= array(  
						array ('OPERATEUR_JOINTURE' => 'Equi'),
						array ('OPERATEUR_JOINTURE' => 'Left'),
						array ('OPERATEUR_JOINTURE' => 'Right')
					 );

	$requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id'];	
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);
    
	$requete                = ' SELECT DICO_RPT_TABLE_MERE.ID_RPT_TABLE_MERE, 
								DICO_RPT_TABLE_MERE.NOM_TABLE_MERE, DICO_RPT_TABLE_MERE.NOM_ALIAS
								FROM DICO_RPT_TABLE_MERE
								WHERE DICO_RPT_TABLE_MERE.ID = '.$_GET['id'].' ;';
	//print $requete;
	$all_tabm 				= $GLOBALS['conn_dico']->GetAll($requete);
	
	$code_js .= ''."\n\t";
	
	$code_js .= 'tab_tabm = new Array() ;'."\n\t";
	$code_js .= 'tab_chp  = new Array() ;'."\n\t";
	
	if(is_array($all_tabm)){
		foreach($all_tabm as $i => $tabm){
			
			$id_tabm  = $tabm['ID_RPT_TABLE_MERE'];
			$nom_tabm = $tabm['NOM_TABLE_MERE'].' ('.$tabm['NOM_ALIAS'].') ';

			$code_js .= 'tab_tabm['.$i.'] = new Array() ;'."\n\t\t";
			$code_js .= 'tab_tabm['.$i.'][0] = '.$id_tabm.' ;'."\n\t\t";
			$code_js .= 'tab_tabm['.$i.'][1] = \''.$nom_tabm.'\' ;'."\n\t\t";
			
			$requete                = ' SELECT DICO_RPT_CHAMP.ID_CHAMP, DICO_RPT_CHAMP.ID_RPT_TABLE_MERE, DICO_RPT_CHAMP.NOM_CHAMP, DICO_RPT_CHAMP.ALIAS
										FROM DICO_RPT_CHAMP
										WHERE DICO_RPT_CHAMP.ID = '.$_GET['id'].' AND DICO_RPT_CHAMP.ID_RPT_TABLE_MERE='.$id_tabm.';';
			//print $requete;
			$all_chp 				= $GLOBALS['conn_dico']->GetAll($requete);
			
			if(is_array($all_chp)){
				
				$code_js .= 'tab_chp['.$id_tabm.'] = new Array() ;'."\n\t\t\t";
				
				foreach($all_chp as $j => $chp){
					
					$id_chp  = $chp['ID_CHAMP'];
					$nom_chp = $chp['NOM_CHAMP'].' ('.$chp['ALIAS'].')';
					
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'] = new Array() ;'."\n\t\t\t\t";
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'] [0] = '.$id_chp.' ;'."\n\t\t\t\t";
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'] [1] = \''.$nom_chp.'\' ;'."\n\t\t\t\t";
				}
			}
		}
	}
	

?>
<script language="JavaScript" type="text/javascript">
	function recharger(id_dim) {
		location.href   = 'synthese.php?val=OpenPopupRptDim&id_rpt=<?php echo $_GET['id']?>&id_dim='+id_dim;
	}
	
	<?php print $code_js;?>
	
	function set_options_tabm (combo, except, opt_sel){
		
		if(tab_tabm.length > 0){
	 		
			eval ('document.Formulaire.'+combo+'.options.length=0');			
			i_opt = -1;
			sel = 0 ;
			for( i=0; i < tab_tabm.length; i++ ){
				
				if(tab_tabm[i][0] !=except ){
					i_opt++;
					if(tab_tabm[i][0] == opt_sel) {sel = i_opt} ;
					//alert(tab_tabm[i][0] +' - '+ opt_sel +' - '+ sel)
					var ch_eval = 'document.Formulaire.'+combo+'.options['+i_opt+']=new Option(tab_tabm['+i+'][1],tab_tabm['+i+'][0])';
					eval(ch_eval);
				}
			}/**/
			eval('document.Formulaire.'+combo+'.selectedIndex='+sel);
		}
	}
	function set_options_chp (combo, tabm, opt_sel){
	
		eval ('document.Formulaire.'+combo+'.options.length=0');
		if(tab_chp[tabm].length > 0){
			sel = 0 ;
			for( i=0; i < tab_chp[tabm].length; i++ ){
				if(tab_chp[tabm][i][0] == opt_sel){sel = i}
				var ch_eval = 'document.Formulaire.'+combo+'.options['+i+']=new Option(tab_chp['+tabm+']['+i+'][1],tab_chp['+tabm+']['+i+'][0])';
				eval(ch_eval);
			}
			eval('document.Formulaire.'+combo+'.selectedIndex='+sel);
		}
	}
	
	/**/
</script>
<table align="center" border="1" width="500"> 
	<caption style="text-align:center;"><B><?php echo 'Report : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID" value="<?php echo $val['ID']; ?>">
	<tr> 
        <td colspan="2" nowrap="nowrap" align="center"><?php echo recherche_libelle_page('nom_jnt'); ?>  
		  <input style="width : 50%;" type="text" name="NOM_JOINTURE" value="<?php echo $val['NOM_JOINTURE']; ?>" /></td>
  </tr>	<tr> 
      <td width="60%"><?php echo recherche_libelle_page('tab_jnt'); ?></td>
	  <td width="40%"><?php echo recherche_libelle_page('chp_jnt'); ?></td>
    </tr>   
<tr> 
        <td width="60%"><select style="width : 100%;" name="ID_RPT_TABLE_MERE_1" onchange="set_options_tabm('ID_RPT_TABLE_MERE_2', this.value, ID_RPT_TABLE_MERE_2.value);set_options_chp('ID_CHAMP_1',this.value,'')">
        </select></td>
        <td width="40%"><select style="width : 100%;" name="ID_CHAMP_1">
          <option value=''></option>
        </select></td>
  </tr>
	<tr> 
        <td width="60%" align="right"><?php echo recherche_libelle_page('exp_join'); ?></td>
        <td width="40%"><select style="width : 100%;" name="OPERATEUR_JOINTURE">
              <?php foreach ($all_op as $i => $op){
					echo "<option value='".$op['OPERATEUR_JOINTURE']."'";
					if (trim($op['OPERATEUR_JOINTURE']) == trim($val['OPERATEUR_JOINTURE'])){
						echo " selected";
					}
					echo ">".$op['OPERATEUR_JOINTURE']."</option>";
				}
				?>
            </select></td>
	</tr>	
	<tr> 
        <td width="60%"><select style="width : 100%;" name="ID_RPT_TABLE_MERE_2"  onchange="set_options_tabm('ID_RPT_TABLE_MERE_1', this.value, ID_RPT_TABLE_MERE_1.value);set_options_chp('ID_CHAMP_2',this.value,'')">
         
        </select></td>
        <td width="40%"><select style="width : 100%;" name="ID_CHAMP_2">
          <option value=''></option>
        </select></td>
    </tr>
</table>
<br>
<?php if($this->action == 'New' or $this->total_enr == 0)
echo '
	<script language="javascript" type="text/javascript">
		set_options_tabm (\'ID_RPT_TABLE_MERE_1\', document.Formulaire.ID_RPT_TABLE_MERE_2.value, \'\');
		set_options_tabm (\'ID_RPT_TABLE_MERE_2\', document.Formulaire.ID_RPT_TABLE_MERE_1.value, \'\' );
		set_options_chp (\'ID_CHAMP_1\',document.Formulaire.ID_RPT_TABLE_MERE_1.value, \'\');
		set_options_chp (\'ID_CHAMP_2\',document.Formulaire.ID_RPT_TABLE_MERE_2.value, \'\');
		
		set_options_tabm (\'ID_RPT_TABLE_MERE_1\', document.Formulaire.ID_RPT_TABLE_MERE_2.value, \'\');
		set_options_tabm (\'ID_RPT_TABLE_MERE_1\', document.Formulaire.ID_RPT_TABLE_MERE_2.value, \'\');
	</script>';
else 
echo '
	<script language="javascript" type="text/javascript">
		set_options_tabm (\'ID_RPT_TABLE_MERE_1\', \''.$val['ID_RPT_TABLE_MERE_2'].'\', \''.$val['ID_RPT_TABLE_MERE_1'].'\');
		set_options_tabm (\'ID_RPT_TABLE_MERE_2\', \''.$val['ID_RPT_TABLE_MERE_1'].'\', \''.$val['ID_RPT_TABLE_MERE_2'].'\');
		set_options_chp (\'ID_CHAMP_1\', \''.$val['ID_RPT_TABLE_MERE_1'].'\', \''.$val['ID_CHAMP_1'].'\');
		set_options_chp (\'ID_CHAMP_2\', \''.$val['ID_RPT_TABLE_MERE_2'].'\', \''.$val['ID_CHAMP_2'].'\');
	</script>';
?>
