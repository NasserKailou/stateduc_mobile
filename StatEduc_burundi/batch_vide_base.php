<?php require_once 'common.php';
		$conn = $GLOBALS['conn'];
		echo'<pre>';
		print_r($conn);
		//exit();
		foreach ($conn->MetaTables('TABLES') as $nom_table){            
				$sql=	'DELETE FROM '.$nom_table;
				if ($conn->Execute($sql) === false) echo '<br><font color=red> error deleting from '.$nom_table.' </font><br>'.$sql;
				else echo '<br><font color=blue> deleting from  '.$nom_table.': OK ...</font>';
		}
?>
