<?php if(isset($_GET["chp_fils"]))
	{
		echo "<select name='CHAMP_TABLE_DONNEES' id='CHAMP_TABLE_DONNEES' style='width: 280px'>";
		if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
			$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);				
			//echo '<option value=\'\'></option>';
			foreach ($list_champs as $i => $champ){
				echo "<option value='".$champ."'";
				if ($_GET['chp_fils'] == $champ){
					echo " selected";
				}
				echo ">".$champ."</option>\n";
			}
		}else{
			echo '<option value=\'\'></option>';
		}
		echo "</select>";
	}
	elseif(isset($_GET["chp_dim"]))
		{
			echo "<select name='CHAMP' id='CHAMP' style='width: 330px'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['chp_dim'] == $champ){
						echo " selected";
					}
					elseif($champ==$GLOBALS['PARAM']['CODE'].'_'.$_GET["table"])
					{
						echo " selected";
					}
					echo ">".$champ."</option>\n";	
				
				}
			}else{
				echo '<option value=\'\'></option>';
			}
			echo "</select>";
		}
		elseif(isset($_GET["lib_dim"]))
		{
			echo "<select name='DIM_LIBELLE' id='DIM_LIBELLE' style='width: 330px'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['lib_dim'] == $champ){
						echo " selected";
					}
					elseif($champ==$GLOBALS['PARAM']['LIBELLE'].'_'.$_GET["table"])
					{
						echo " selected";
					}
					echo ">".$champ."</option>\n";						
				}
			}else{
				echo '<option value=\'\'></option>';
			}
			echo "</select>";
		}
		elseif(isset($_GET["chp_sql_dim"]))
		{
			echo "<textarea name='DIM_SQL' style='width: 330px' rows='6'>";
			if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'))))
                    $lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
                        $_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
                        $_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
                        "\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
                    
            else
                    $lien_systeme  =   ''; 
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				if(trim($_GET["chp_sql_dim"])<>'' &&  strtoupper(trim($_GET["val_dim_table"]))==strtoupper(trim($_GET["table"])))
				{
					$val_sql = urldecode($_GET["chp_sql_dim"]);
				}
				else
				{
					if($lien_systeme!='')
						$prefix=$_GET['table'].'.';
					else
						$prefix='';
					$val_sql = "SELECT ".$prefix."*". "\n".
								"FROM ". $_GET['table'].$lien_systeme. "\n".
								"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
				}			
				echo $val_sql;
			}
			echo "</textarea>";	
		}
		
		elseif(isset($_GET["chp_ref"]))
		{
			echo "<select name='CHAMP_REF' id='CHAMP_REF' style='width: 330px'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['chp_ref'] == $champ){
						echo " selected";
					}
					elseif($champ==$GLOBALS['PARAM']['CODE'].'_'.$_GET["table"])
					{
						echo " selected";
					}
					echo ">".$champ."</option>\n";	
				
				}
			}else{
				echo '<option value=\'\'></option>';
			}
			echo "</select>";
		}
		elseif(isset($_GET["chp_sql_crit"]))
		{
			echo "<textarea name='SQL_CRITERE' cols='40' rows='4'>";
			if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'))))
                    $lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
                        $_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
                        $_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
                        "\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
                    
            else
                    $lien_systeme  =   ''; 
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				if(trim($_GET["chp_sql_crit"])<>'' &&  strtoupper(trim($_GET["val_dim_table"]))==strtoupper(trim($_GET["table"])))
				{
					$val_sql = urldecode($_GET["chp_sql_crit"]);
				}
				else
				{
					if($lien_systeme!='')
						$prefix=$_GET['table'].'.';
					else
						$prefix='';
					$val_sql = "SELECT ".$prefix."*". "\n".
								"FROM ". $_GET['table'].$lien_systeme. "\n".
								"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
				}			
				echo $val_sql;
			}
			echo "</textarea>";	
		}
		
	
?>
 

