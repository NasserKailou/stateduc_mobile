<?php $requete      = ' SELECT DICO_OLAP.THEME_NAME
								FROM DICO_OLAP
								WHERE DICO_OLAP.ID_OLAP ='.$_GET['id_olap'];	
	$theme_name = $GLOBALS['conn_dico']->GetOne($requete);
    
    $req_tabm               = ' SELECT DICO_OLAP_TABLE_MERE.ID_OLAP_TABLE_MERE, 
								DICO_OLAP_TABLE_MERE.NOM_TABLE_MERE, DICO_OLAP_TABLE_MERE.NOM_ALIAS
								FROM DICO_OLAP_TABLE_MERE
								WHERE DICO_OLAP_TABLE_MERE.ID_OLAP = '.$_GET['id_olap'].' 
								AND (DICO_OLAP_TABLE_MERE.IS_FICTIF Is Null Or DICO_OLAP_TABLE_MERE.IS_FICTIF=0) ;';
    $res_tabm  =  $GLOBALS['conn_dico']->GetAll($req_tabm);
    
	$code_js = ''."\n\t";
	
	$code_js .= 'tab_tabm = new Array() ;'."\n\t";
	$code_js .= 'tab_chp  = new Array() ;'."\n\t";
	
	if(is_array($res_tabm)){
		foreach($res_tabm as $i => $tabm){
			
			$id_tabm  = $tabm['ID_OLAP_TABLE_MERE'];
			$nom_tabm = $tabm['NOM_TABLE_MERE'].' ('.$tabm['NOM_ALIAS'].')';

			$code_js .= 'tab_tabm['.$i.'] = new Array() ;'."\n\t\t";
			$code_js .= 'tab_tabm['.$i.'][0] = '.$id_tabm.' ;'."\n\t\t";
			$code_js .= 'tab_tabm['.$i.'][1] = \''.$nom_tabm.'\' ;'."\n\t\t";
            $code_js .= 'tab_tabm['.$i.'][2] = \''.$tabm['NOM_ALIAS'].'\' ;'."\n\t\t";
            $code_js .= 'tab_tabm['.$i.'][3] = \''.$tabm['NOM_TABLE_MERE'].'\' ;'."\n\t\t";
			
			
			$requete                = ' SELECT DICO_OLAP_CHAMP.ID_CHAMP, DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE, DICO_OLAP_CHAMP.NOM_CHAMP, DICO_OLAP_CHAMP.ALIAS
										FROM DICO_OLAP_CHAMP
										WHERE DICO_OLAP_CHAMP.ID_OLAP = '.$_GET['id_olap'].' AND DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE='.$id_tabm.';';
			//print $requete .'<br><br>';
			$res_champs_tabm 				= $GLOBALS['conn_dico']->GetAll($requete);
			
			if(is_array($res_champs_tabm)){
				
				$code_js .= 'tab_chp['.$id_tabm.'] = new Array() ;'."\n\t\t\t";
				
				foreach($res_champs_tabm as $j => $chp){
					
					$id_chp  = $chp['ID_CHAMP'];
					$nom_chp = $chp['NOM_CHAMP'].' ('.$chp['ALIAS'].')';
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'] = new Array() ;'."\n\t\t\t\t";
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'][0] = '.$id_chp.' ;'."\n\t\t\t\t";
					$code_js .= 'tab_chp['.$id_tabm.']['.$j.'][1] = \''.$nom_chp.'\' ;'."\n\t\t\t\t";
                    $code_js .= 'tab_chp['.$id_tabm.']['.$j.'][2] = \''.$chp['NOM_CHAMP'].'\' ;'."\n\t\t\t\t";
				}
			}
		}
	}
    
    if( isset($val['ID_TABLE']) && (trim($val['ID_TABLE']) <> '')){
        $requete                = ' SELECT DICO_OLAP_CHAMP.ID_CHAMP, DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE, DICO_OLAP_CHAMP.NOM_CHAMP, DICO_OLAP_CHAMP.ALIAS
                                    FROM DICO_OLAP_CHAMP
                                    WHERE DICO_OLAP_CHAMP.ID_OLAP = '.$_GET['id_olap'].' AND DICO_OLAP_CHAMP.ID_OLAP_TABLE_MERE='.$val['ID_TABLE'].';';
        
        $all_curr_champs 				= $GLOBALS['conn_dico']->GetAll($requete);
    }
	
?>
<script language="JavaScript" type="text/javascript">

	<?php print $code_js;?>
	
	function set_tabm_things(tabm){       
		
	 eval ('document.Formulaire.ID_CHAMP.options.length=0');
     if(tab_chp[tabm].length > 0){
			sel = 0 ;
			for( i=0; i < tab_chp[tabm].length; i++ ){
				var ch_eval = 'document.Formulaire.ID_CHAMP.options['+i+']=new Option(tab_chp['+tabm+']['+i+'][1],tab_chp['+tabm+']['+i+'][0])';
				eval(ch_eval);
			}
            eval('document.Formulaire.ID_CHAMP.selectedIndex='+sel);
            eval('document.Formulaire.NOM_CHAMP.value="'+tab_chp[tabm][sel][2]+'"');
            //alert(eval('tab_chp['+tabm+']['+sel+'][1]'));
            for( i=0; i < tab_tabm.length; i++ ){
                if(tab_tabm[i][0] == tabm) {
                    //alert('here='+tab_tabm[i][2]);
                    eval('document.Formulaire.ALIAS_TABLE.value="'+tab_tabm[i][2]+'"');
                    eval('document.Formulaire.NOM_TABLE.value="'+tab_tabm[i][3]+'"');
                    break;
                } ;
            }

		}
	}
    
	function set_nom_champ(tabm, idchp){
        for( i=0; i < tab_chp[tabm].length; i++ ){
            if(tab_chp[tabm][i][0] == idchp) {
                //alert(tab_chp[tabm][i][1]);
                eval('document.Formulaire.NOM_CHAMP.value="'+tab_chp[tabm][i][2]+'"');
                break;
            } 
        }/**/
	}
	
	/**/
</script>


<table align="center" border="1" width="400">
    <caption style="text-align:center;"><B><?php echo 'Cube : '.$theme_name;?></B></caption>
    <INPUT type="hidden" name="ID_OLAP" value="<?php echo $val['ID_OLAP']; ?>">  
	<tr> 
        <td width="30%"><?php echo recherche_libelle_page('id_crit'); ?></td>
      <td width="70%"><INPUT style="width : 100%;" readonly="1" type="text" size="3" name="ID_CRITERE" value="<?php echo $val['ID_CRITERE']; ?>"></td>
    </tr>
	<tr> 
        <td width="30%"><?php echo recherche_libelle_page('nom_table'); ?></td>
    <td width="70%">	  
	  <select style="width : 100%;" name="ID_TABLE" onchange="set_tabm_things(this.value)">
                <?php $nom_tabm="";
                foreach ($res_tabm as $i => $tabm ){
					echo "<option value='".$tabm['ID_OLAP_TABLE_MERE']."'";
					if ( trim($tabm['ID_OLAP_TABLE_MERE']) == trim($val['ID_TABLE'])){
						echo " selected";
                        $nom_tabm = $tabm['NOM_TABLE_MERE'];						
					}
					echo ">".$tabm['NOM_TABLE_MERE']." (".$tabm['NOM_ALIAS'].")"."</option>";
				}				
				?>
      </select>
    <input type="hidden" name="NOM_TABLE" value="<?php echo  $nom_tabm;?>"/>    </tr>	
	
	<tr> 
        <td width="30%"><?php echo recherche_libelle_page('alias_table'); ?></td>
      <td width="70%"><INPUT style="width : 100%;" type="text" size="20" id="ALIAS_TABLE" name="ALIAS_TABLE" value="<?php echo $val['ALIAS_TABLE']; ?>" readonly="1"></td>
    </tr>
    <tr> 
        <td width="30%"><?php echo recherche_libelle_page('nom_chp'); ?></td>
      <td width="70%">
	  
	  <select style="width : 100%;" name="ID_CHAMP" onchange="set_nom_champ(ID_TABLE.value, this.value)">
                <?php $nom_champ="";
	            if(is_array($all_curr_champs)){
                    foreach ($all_curr_champs as $i => $champ ){
                        echo "<option value='".$champ['ID_CHAMP']."'";
                        if ( trim($champ['ID_CHAMP']) == trim($val['ID_CHAMP'])){
                            echo " selected";
                            $nom_champ=$champ['NOM_CHAMP'];
                        }
                        echo ">".$champ['NOM_CHAMP']." (".$champ['ALIAS'].")"."</option>";
                    }
                }
				?>
      </select>
    <input type="hidden" name="NOM_CHAMP" value="<?php echo  $nom_champ;?>"/>    </tr>
    
    <tr> 
        <td width="30%"><?php echo recherche_libelle_page('opera'); ?></td>
      <td width="70%">	  
	  <select style="width : 100%;" name="OPERATEUR">
                <?php $req_oper  =   "SELECT VAL_OPERATEUR FROM DICO_OPERATEUR";
				$res_oper =  $GLOBALS['conn_dico']->GetAll($req_oper);
				
				foreach ($res_oper as $i => $oper ){
					echo "<option value='".$oper['VAL_OPERATEUR']."'";
					if ( trim($oper['VAL_OPERATEUR']) == trim($val['OPERATEUR'])){
						echo " selected";
					}
					echo ">".$oper['VAL_OPERATEUR']."</option>";
				}
				?>
 	 </select>  
  	</tr>
    	 
	<tr> 
        <td width="30%"><?php echo recherche_libelle_page('val'); ?></td>
      <td width="70%">
	  <INPUT style="width : 89%;" type="text" size="20" name="VALEUR" value="<?php echo $val['VALEUR']; ?>">
      <input name="button" type="button" style="width:10%;" onclick="OpenPopupCritValue(<?php echo '\''.$_GET['id_olap'].'\''; ?>,NOM_TABLE.value,NOM_CHAMP.value)" value="..." />
      </td>
    </tr>
    
    <tr> 
        <td width="30%"><?php echo recherche_libelle_page('type_chp'); ?></td>
      <td width="70%">	  
	   <select style="width : 100%;" name="TYPE_CHAMP">
                <?php $tab_type_champ = array('int','text','date','other');
				foreach ($tab_type_champ as $i => $type_champ ){
					echo "<option value='".$type_champ."'";
					if ( trim($type_champ) == trim($val['TYPE_CHAMP'])){
						echo " selected";
					}
					echo ">".$type_champ."</option>";
				}
				?>
    </select>	</tr>  
</table>
<?php if($this->action == 'New' or $this->total_enr == 0)
    echo '
        <script language="javascript" type="text/javascript">
            set_tabm_things(document.Formulaire.ID_TABLE.value);
            set_nom_champ(document.Formulaire.ID_TABLE.value, document.Formulaire.ID_CHAMP.value)        
        </script>';
?>

<br>
