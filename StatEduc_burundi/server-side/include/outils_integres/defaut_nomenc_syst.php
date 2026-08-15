<?php lit_libelles_page('/defaut_nomenc.php');
	$list_tables 		= array_map('strtoupper',$GLOBALS['conn']->MetaTables('TABLES'));
	$list_tables_nomenc = array();
	$tabl_exclues = array($GLOBALS['PARAM']['TYPE_ANNEE'], $GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_REGROUPEMENT'], $GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']);
	foreach($list_tables as $nom_table){            
		if ( (preg_match('/^'.$GLOBALS['PARAM']['TYPE'].'_.*$/', strtoupper($nom_table)) && !preg_match('/_'.$GLOBALS['PARAM']['SYSTEME'].'$/', strtoupper($nom_table))) ){               
			if( !in_array(strtoupper($nom_table), array_map('strtoupper',$tabl_exclues)) ){    
				$list_tables_nomenc[]    =   $nom_table ;
			}
		}
	}
	sort( $list_tables_nomenc );
?>
<br />
 1 : Tables Nomenclatures 
 <br />
<table width="50%"><tr><td align="left">
<?php //<span style="text-align: left;	margin-left: 1px;	left: 200px;">
	if(recherche_libelle_page('indeterm')<>'') $indeterm = recherche_libelle_page('indeterm'); else $indeterm = recherche_libelle_page('indetermin');
	foreach($list_tables_nomenc as $i => $table_nomenc){
		///////////////////
		$ok   = true ;
		$mess = '';
		echo '<br>'.$table_nomenc;
		
		$sql    =   'INSERT INTO '.$table_nomenc.
					' ('.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.','.$GLOBALS['PARAM']['LIBELLE'].'_'.$table_nomenc.','.$GLOBALS['PARAM']['ORDRE'].'_'.$table_nomenc.')'.
					'VALUES (255,'.$GLOBALS['conn']->qstr($indeterm).',255)'; 
		if (($rs = $GLOBALS['conn']->Execute($sql))===false){$ok = false; /*echo'<br>'.$sql.'<br>';*/} 
		($ok==true)?($mess = '<span style="color: #0000FF;"> : OK ! ... </span>'):($mess = '<span style="color: #FF0000;"> : PB ! ... </span>');
		echo $mess;
		///////////////////
	}
//</span>
?> 
</td></tr></table>

<br />
 2 : Tables Nomenclatures System
<br />

<table width="50%"><tr><td align="left">
<?php //<span style="text-align: left;	margin-left: 1px;	left: 200px;">
	
	foreach($list_tables_nomenc as $i => $table_nomenc){
		///////////////////
		$table_nomenc_syst = $table_nomenc . '_' . $GLOBALS['PARAM']['SYSTEME'] ;
		//if( exist_table( $table_nomenc_syst ) ){
		if(in_array( $table_nomenc_syst, $list_tables)){
			$ok   = true ;
			$mess = '';
			echo '<br>' . $table_nomenc_syst; 
			//die();
			$requete 	= 'SELECT DISTINCT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' FROM ' . $table_nomenc_syst ;
			//$all_used_sectors	= $GLOBALS['conn']->GetAll($requete);
			$all_used_sectors	= $_SESSION['tab_secteur'];
			
			if( is_array($all_used_sectors) && count($all_used_sectors) ){
				$ok   = false ;
				foreach( $all_used_sectors  as $i => $syst ){
					$id_syst = $syst[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];
					$sql    =   ' INSERT INTO '.$table_nomenc_syst.'  ('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.')
								VALUES ('.$id_syst.' ,255) ';
					if (($rs = $GLOBALS['conn']->Execute($sql)) <> false){$ok = true;}  
				}
			}else { $ok = false; }	
			/*
			$sql    =   ' INSERT INTO '.$table_nomenc_syst.'  ('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.')
							SELECT DISTINCT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].', 255 AS '.$GLOBALS['PARAM']['CODE'].'_'.$table_nomenc.'
							FROM         '.$table_nomenc_syst.''; 
			
			if (($rs = $GLOBALS['conn']->Execute($sql))===false){$ok = false; } */
			
			($ok==true)?($mess = '<span style="color: #0000FF;"> : OK ! ... </span>'):($mess = '<span style="color: #FF0000;"> : PB ! ... </span>');
			echo $mess;
		}
		///////////////////
	}
//</span>
?> 
</td></tr></table>

