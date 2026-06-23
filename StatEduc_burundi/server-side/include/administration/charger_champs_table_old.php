<?php if(isset($_GET["type_theme"]) && (trim($_GET["type_theme"]) == 0))
	{
		if(isset($_GET["type_chp"]))
		{
			$requete	= "	SELECT  TYPE_DONNEES, CODE_TYPE_DONNEES, LIBELLE_TYPE_DONNEES   
							FROM   DICO_TYPE_DONNEES WHERE CODE_TYPE_DONNEES < 10";
			$type_donnees_chp	= $GLOBALS['conn_dico']->GetAll($requete);
			echo "<select name='TYPE_ZONE_BASE' id='TYPE_ZONE_BASE'>";
			echo '<option value=\'\'></option>';			
			foreach ($type_donnees_chp as $i => $type_donnees){
				echo "<option value='".trim($type_donnees['TYPE_DONNEES'])."'";
				
				$type_donnee='';
				if(trim($type_donnees['TYPE_DONNEES'])=='int') $type_donnee='I';
				elseif(trim($type_donnees['TYPE_DONNEES'])=='text') $type_donnee='C';
				elseif(trim($type_donnees['TYPE_DONNEES'])=='date') $type_donnee='T';
				
				if (trim($type_donnees['TYPE_DONNEES']) == trim($_GET['type_chp']) && trim($_GET['champ']) <> '' ){
						echo " selected";
				}
				elseif ($type_donnee == trim($_GET['id_chp'])){
						echo " selected";
				}
				echo ">".get_libelle($type_donnees['CODE_TYPE_DONNEES'],'DICO_TYPE_DONNEES',$type_donnees['LIBELLE_TYPE_DONNEES'])."</option>";
			}		
			echo "</select>";
		}
		elseif(isset($_GET["chp_fils"]))
		{
			echo "<select name='CHAMP_FILS' id='CHAMP_FILS' style='width: 280px'>";
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
			if(isset($_GET["table"]) && (trim($_GET["table"]) <> '')){
				if(trim($_GET["chp_sql"])<>'' &&  trim($_GET["val_table"])==trim($_GET["table"]))
				{
					$val_sql = urldecode($_GET["chp_sql"]);
				}
				else
				{
					$alias = '';
					if(isset($_GET["chp_pere"]) && $_GET["chp_pere"] <> '')
					{
						if($_GET["chp_pere"] != $GLOBALS['PARAM']['CODE']."_".$_GET['table'])
						{
							$alias = " AS ".$_GET["chp_pere"];
						}
					}
					
					$val_sql = "SELECT ". $GLOBALS['PARAM']['CODE']."_".$_GET['table'].$alias. "\n".
								"FROM ". $_GET['table']. "\n".
								"ORDER BY ". $GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
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
			echo "<select name='MESURE1' id='MESURE1' style='width: 180px'>";
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
			echo "<select name='MESURE2' id='MESURE2' style='width: 180px'>";
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
			echo "<select name='CHAMP' id='CHAMP' style='width: 240px'>";
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
			echo "<select name='DIM_LIBELLE' id='DIM_LIBELLE' style='width: 240px'>";
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
				if(trim($_GET["chp_sql_dim"])<>'' &&  trim($_GET["val_dim_table"])==trim($_GET["table"]))
				{
					$val_sql = urldecode($_GET["chp_sql_dim"]);
				}
				else
				{
					$val_sql = "SELECT ". $GLOBALS['PARAM']['CODE']."_".$_GET['table']. "\n".
								"FROM ". $_GET['table']. "\n".
								"ORDER BY ". $GLOBALS['PARAM']['ORDRE']."_".$_GET['table'];
				}			
				echo $val_sql;
			}
			echo "</textarea>";	
		}
	}
	elseif(isset($_GET["plage_codes"]) && (trim($_GET["plage_codes"]) == 1))
	{
		echo "<select name='NOM_CHAMP' id='NOM_CHAMP' style='width: 280px'>";
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
 

