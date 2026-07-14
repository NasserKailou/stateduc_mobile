<?php set_time_limit(0);
	
    
	
	$req = 'SELECT DISTINCT TABLE_MERE	FROM DICO_ZONE	ORDER BY TABLE_MERE';
		   
	$list_tabms	= $GLOBALS['conn_dico']->GetAll($req); 
	$tab_cnt = array();
	if( is_array($list_tabms) && count($list_tabms) ){
		foreach($list_tabms as $i_tabm => $tabm){
			$reqcnt = 'SELECT COUNT(*) FROM '.$tabm['TABLE_MERE'];
			$tab_cnt[] = array($tabm['TABLE_MERE'], $GLOBALS['conn']->GetOne($reqcnt));
		}
	}
	
	print'
			<br><br><br><br>
			<table border="1">';
	
	foreach($tab_cnt as $i => $row){ 
		print'
		<tr>
			<td>'.$row[0].'</td>
			<td align="right">'.$row[1].'</td>
		</tr>';
	}
			
	print'	</table>';
	
?>
