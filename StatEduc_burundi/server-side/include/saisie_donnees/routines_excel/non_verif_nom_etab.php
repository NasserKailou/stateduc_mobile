	print "<script type='text/Javascript'>\n";
	print "document.location.href='saisie_donnees.php?val=PopupImportExcel&excel_file=".urlencode($_POST['chemin_fichier'])."&row_excel_code_etab=".$row_excel_code_etab."&col_excel_code_etab=".$col_excel_code_etab."';\n";
	print "</script>\n";

