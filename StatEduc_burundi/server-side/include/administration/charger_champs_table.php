<?php if(isset($_GET["type_theme"]) && (trim($_GET["type_theme"]) == 0))
	{
		if(isset($_GET["type_chp"]))
		{
			$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
							FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES IN (1,2,3)";
			$type_donnees_chp	= $GLOBALS['conn_dico']->GetAll($requete);
			echo "<select name='TYPE_ZONE_BASE' id='TYPE_ZONE_BASE'>";
			echo '<option value=\'\'></option>';			
			foreach ($type_donnees_chp as $i => $type_donnees){
				echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
				
				$type_donnee=array();
				if(trim($type_donnees['TYPE_DONNEES'])=='int') { $type_donnee=array('-6','4','5','7','8','I','N','bigint','bit','decimal','float','float4','float8','int','int2','int4','int8','numeric','smallint','tinyint','real'); }
				elseif(trim($type_donnees['TYPE_DONNEES'])=='text') $type_donnee=array('12','C','char','nchar','ntext','nvarchar','text','varchar');
				elseif(trim($type_donnees['TYPE_DONNEES'])=='date') $type_donnee=array('11','D','T','datetime','smalldatetime');
				if (trim($type_donnees['TYPE_DONNEES']) == trim($_GET['type_chp']) && trim($_GET['champ']) <> '' ){
						echo " selected";
				}
				elseif (in_array(trim($_GET['id_chp']),$type_donnee)){
						echo " selected";
				}
				echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
			}		
			echo "</select>";
		}
		elseif(isset($_GET["chp_fils"]))
		{
			echo "<select name='CHAMP_FILS' id='CHAMP_FILS'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);				
				//echo '<option value=\'\'></option>';
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['chp_fils'] == $champ){
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
		elseif(isset($_GET["chp_sql"]))
		{
			echo "<textarea name='SQL_REQ' style='width:370px' cols='68' rows='8'>";
            if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'))))
                    $lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
                        $_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
                        $_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
                        "\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
                    
            else
                    $lien_systeme  =   ''; 
            //echo '<br>lien systeme ='.$lien_systeme ;
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				if(trim($_GET["chp_sql"])<>'' &&  strtoupper(trim($_GET["val_table"]))==strtoupper(trim($_GET["table"])))
				{
					$val_sql = urldecode($_GET["chp_sql"]);
				}
				else
				{
					$alias = '';
					if(isset($_GET["chp_pere"]) && $_GET["chp_pere"] <> '')
					{
						if(strtoupper($_GET["chp_pere"]) != strtoupper($GLOBALS['PARAM']['CODE']."_".$_GET['table']))
						{
							$alias = " AS ".$_GET["chp_pere"];
						}
					}
					if($lien_systeme!='')
						$prefix=$_GET['table'].'.';
					else
						$prefix='';
					$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].$alias. "\n".
								"FROM ". $_GET['table']. $lien_systeme."\n".
								"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
				}			
				echo $val_sql;
			}
			echo "</textarea>";	
		}
	}
	elseif(isset($_GET["type_theme"]) && (trim($_GET["type_theme"]) == 1))
	{	
		if(isset($_GET["mes1"]))
		{
			echo "<select name='MESURE1' id='MESURE1'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
				echo '<option value=\'\'></option>';
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['mes1'] == $champ){
						echo " selected";
					}
					echo ">".$champ."</option>\n";	
				}
			}else{
				echo '<option value=\'\'></option>';
			}
			echo "</select>";
		}
		elseif(isset($_GET["mes2"]))
		{
			echo "<select name='MESURE2' id='MESURE2'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				$list_champs = $GLOBALS['conn']->MetaColumnNames($_GET["table"]);
				echo '<option value=\'\'></option>';
				foreach ($list_champs as $i => $champ){
					echo "<option value='".$champ."'";
					if ($_GET['mes2'] == $champ){
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
			echo "<select name='CHAMP' id='CHAMP'>";
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
			echo "<select name='DIM_LIBELLE' id='DIM_LIBELLE'>";
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
			echo "<textarea name='DIM_SQL' cols='50' rows='10'>";
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
                if (in_array(strtoupper($_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']),array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'))))
                        $lien_systeme  =  ','.$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME']."\n".'WHERE '.
                            $_GET['table'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].' = '.
                            $_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE']."_".$_GET['table'].
                            "\n AND ".$_GET["table"].'_'.$GLOBALS['PARAM']['SYSTEME'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = $'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'];
                        
                else
                    $lien_systeme  =   ''; 
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
					$val_sql = "SELECT ".$prefix.$GLOBALS['PARAM']['CODE']."_".$_GET['table']. "\n".
								"FROM ". $_GET['table'].$lien_systeme . "\n".
								"ORDER BY ".$prefix.$GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
				}			
				echo $val_sql;
			}
			echo "</textarea>";	
		}
	}
	elseif(isset($_GET["plage_codes"]) && (trim($_GET["plage_codes"]) == 1))
	{
		echo "<select name='NOM_CHAMP' id='NOM_CHAMP'>";
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
?>
 

