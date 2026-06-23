<script language="JavaScript" type="text/javascript">
	function recharger(id_dim) {
		location.href   = 'synthese.php?val=OpenPopupRptDim&id_rpt=<?php echo $_GET['id_rpt']?>&id_dim='+id_dim;
	}
</script>

<?php $requete                = ' SELECT DICO_REPORT.LIBELLE_REPORT
								FROM DICO_REPORT
								WHERE DICO_REPORT.ID ='.$_GET['id_rpt'];
	//print $requete;
	$nom_rpt = $GLOBALS['conn_dico']->GetOne($requete);

	$requete                = '	SELECT * FROM DICO_DIMENSION ORDER BY TABLE_REF';
	//print $requete;
	$all_dims 			= $GLOBALS['conn_dico']->GetAll($requete);

	if( $this->action == 'Open' ){
		$selected_dim	=	$val['ID_DIMENSION'];
	}elseif(isset($_GET['id_dim']) and (trim($_GET['id_dim']) <> '')){
		$selected_dim	= $_GET['id_dim'] ;
	}else{
		$selected_dim	= $val['ID_DIMENSION'];
	}
	
	$requete                = '	SELECT DICO_DIMENSION.*
								FROM DICO_DIMENSION
								WHERE DICO_DIMENSION.ID_DIMENSION =' . $selected_dim . ' ;';
	//print $requete;
	$tab_dim_choisi			= $GLOBALS['conn_dico']->GetAll($requete);

	if(isset($_GET['id_dim']) and (trim($_GET['id_dim']) <> '') and ($this->action <> 'Open') ){
		$val['DIM_LIBELLE_ENTETE']	= $tab_dim_choisi[0]['DIM_LIBELLE_ENTTE'] ;
	}
	
	$all_type_dim = array(  
							array ('CODE' => '1', 'LIBELLE' => recherche_libelle_page('dim_ligne') ),
							array ('CODE' => '2', 'LIBELLE' => recherche_libelle_page('dim_col') ),
                            array ('CODE' => '3', 'LIBELLE' => recherche_libelle_page('dim_imbr') )
						 );
	$selected_dim_lib = "";					 
?>
<table align="center" border="1">
    <tr> 
        <td style="min-width:60px"><?php echo recherche_libelle_page('id_rpt'); ?><br/><INPUT readonly="1" type="text" size="3" name="ID" value="<?php echo $val['ID']; ?>"></td>
        <td style="min-width:185px"><?php echo recherche_libelle_page('id_dim'); ?><br/> 
			<INPUT type="hidden" name="ID_DIMENSION" value="<?php echo $selected_dim; ?>">
			<INPUT readonly="1" type="text" size="30" value="<?php 
                 foreach ($all_dims as $i => $dim) {
					if ($dim['ID_DIMENSION'] == $selected_dim){
						$selected_dim_lib = $dim['TABLE_REF'];
						echo $selected_dim_lib;
					}
				}
				?>">
		</td>
        <td style="min-width:110px"><?php echo recherche_libelle_page('type_dim'); ?><br/>
			<select style="width : 100px;" name="TYPE_DIMENSION">
                <option value=''></option>
                <?php foreach ($all_type_dim as $i => $type_dim){
					echo "<option value='".$type_dim['CODE']."'";
					if ($type_dim['CODE'] == $val['TYPE_DIMENSION']){
						echo " selected";
					}
					echo ">".$type_dim['LIBELLE']."</option>";
				}
				?>
            </select></td>
        <td style="min-width:185px"><?php echo recherche_libelle_page('ent_dim'); ?><br/><INPUT type="text" size="30" name="DIM_LIBELLE_ENTETE" value="<?php echo ($val['DIM_LIBELLE_ENTETE']=='')?$selected_dim_lib:$val['DIM_LIBELLE_ENTETE']; ?>"></td>
    </tr>
</table>
