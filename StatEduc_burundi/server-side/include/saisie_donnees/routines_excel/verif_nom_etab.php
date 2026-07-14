			$req_etab = "SELECT ".$GLOBALS['PARAM']['NOM_ETABLISSEMENT']." FROM ".$GLOBALS['PARAM']['ETABLISSEMENT']." WHERE ".$GLOBALS['PARAM']['CODE_ETABLISSEMENT']."=".$_SESSION['code_etab'];
			$inst_name = $GLOBALS['conn']->GetOne($req_etab);
			if(trim($inst_name) <> "" && strtoupper($data->sheets[$sheet]['cells'][$row_excel_nom_etab][$col_excel_nom_etab]) <> strtoupper($inst_name) && (!isset($_SESSION['xls_files']) || count($_SESSION['xls_files'])==1)){
				print "<script type='text/Javascript'>\n";
				print "if(confirm(\"".recherche_libelle_page('Err_Diff_Etab_Nom')." ".$GLOBALS['PARAM']['ETABLISSEMENT']." ".recherche_libelle_page('Err_Diff_Etab_Nom_Suite')."\")){\n";
				print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				print "}\n";
				print "</script>\n";
			}else{
				print "<script type='text/Javascript'>\n";
				if(isset($_POST['new_code_etab']) && $_POST['new_code_etab'] <> "")
					print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&new_code_etab=".$_POST['new_code_etab']."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				else
					print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
				print "</script>\n";
			}
