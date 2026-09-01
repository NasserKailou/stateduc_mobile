<?php lit_libelles_page('/defaut_nomenc.php');
	$list_tables 		= array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
	$list_tables_nomenc = array();
	$opt_tabl_exclues = array();
	if(isset($GLOBALS['PARAM']['TYPE_RATTACHEMENT']) && $GLOBALS['PARAM']['TYPE_RATTACHEMENT']<>'')
		$opt_tabl_exclues[] = $GLOBALS['PARAM']['TYPE_RATTACHEMENT'];
	if(isset($GLOBALS['PARAM']['TYPE_FILTRE']) && $GLOBALS['PARAM']['TYPE_FILTRE']<>'')
		$opt_tabl_exclues[] = $GLOBALS['PARAM']['TYPE_FILTRE'];
	if(isset($GLOBALS['PARAM']['TYPE_PERIODICITE']) && $GLOBALS['PARAM']['TYPE_PERIODICITE']<>'')
		$opt_tabl_exclues[] = $GLOBALS['PARAM']['TYPE_PERIODICITE'];
	if(isset($GLOBALS['PARAM']['TYPE_PERIODE']) && $GLOBALS['PARAM']['TYPE_PERIODE']<>'')
		$opt_tabl_exclues[] = $GLOBALS['PARAM']['TYPE_PERIODE'];
	$tabl_exclues=array($GLOBALS['PARAM']['TYPE_ANNEE'], $GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']);
	$tabl_exclues=array_merge($tabl_exclues,$opt_tabl_exclues);
	foreach($list_tables as $nom_table){            
		if ( (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/',strtoupper($nom_table)) && !preg_match('/_'.$GLOBALS['PARAM']['SYSTEME'].'$/',strtoupper($nom_table))) ){   
			if( !in_array(strtoupper($nom_table), array_map('strtoupper',$tabl_exclues)) ){            
				$list_tables_nomenc[]    =   $nom_table ;
			}
		}
	}
	sort( $list_tables_nomenc );
?>

<table width="50%" align="center"><tr><td align="left">
<?php //<span style="text-align: left;	margin-left: 1px;	left: 200px;">
	if(recherche_libelle_page('indeterm')<>'') $indeterm = recherche_libelle_page('indeterm'); else $indeterm = recherche_libelle_page('indetermin');
	foreach($list_tables_nomenc as $i => $table_nomenc){
		///////////////////
		$ok   = true ;
		$ok_upd   = true ;
		$mess = '';
		echo '<br>'.$table_nomenc;
		
		$sql    =   'INSERT INTO '.$table_nomenc.
					' ('.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.','.$GLOBALS['PARAM']['LIBELLE'].'_'.$table_nomenc.','.$GLOBALS['PARAM']['ORDRE'].'_'.$table_nomenc.')'.
					'VALUES (255,'.$GLOBALS['conn']->qstr($indeterm).',255)'; 
		if (($rs = $GLOBALS['conn']->Execute($sql))===false){
			$ok = false; 
			//echo'<br>'.$sql.'<br>';
			$sql_upd	=   'UPDATE '.$table_nomenc.
							' SET '.$GLOBALS['PARAM']['LIBELLE'].'_'.$table_nomenc.' = '.$GLOBALS['conn']->qstr($indeterm).
							' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.' = 255';
			if(($rs_upd = $GLOBALS['conn']->Execute($sql_upd))===false){
				$ok_upd   = false ;
				//echo'<br>'.$sql_upd.'<br>';
				$mess = '<span style="color: #FF0000;"> : INSERT PB ! UPDATE PB ! ... </span>';
			}else{
				$mess = '<span style="color: #FF0000;"> : INSERT PB ! ... </span>'.'<span style="color: #0000FF;"> UPDATE OK ! ... </span>';
			}
		}else{
			$mess = '<span style="color: #0000FF;"> : INSERT OK ! ... </span>';
		}
		//($ok==true)?($mess = '<span style="color: #0000FF;"> : OK ! ... </span>'):($mess = '<span style="color: #FF0000;"> : PB ! ... </span>');
		echo $mess;
		///////////////////
	}
//</span>
?> 
</td></tr></table>

