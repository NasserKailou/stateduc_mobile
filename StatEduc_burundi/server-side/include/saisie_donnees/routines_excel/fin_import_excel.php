	//For closing importation processing statement or displaying errors report
	if(!isset($GLOBALS['theme_data_MAJ_ok']) || (isset($GLOBALS['theme_data_MAJ_ok']) && $GLOBALS['theme_data_MAJ_ok'] == true)){//For progress popup closing
		print "<script type='text/Javascript'>\n";
		print "parent.document.location.href='questionnaire.php?theme=".$_SESSION['theme']."&code_etab=".$_SESSION['code_etab_excel']."';\n";
		print "</script>\n";
		fin_popup_progress();//For progress popup closing
	}else{//display errors report if there is
		print("\n<script type='text/Javascript'>\n");
		print("\t <!--  \n");
		print("\t window.resizeTo(800,400); \n"); 
		print("\t window.moveTo(100,50); \n"); 
		print("\t var ch_eval = 'document.getElementById(\"progress\").style.visibility=\'hidden\';';\n");
		print("\t eval(ch_eval);\n");
		print("\t --> \n");
		print("\t </script> \n");
		print "<script type='text/Javascript'>\n";
		print "parent.document.location.href='questionnaire.php?theme=".$_SESSION['theme']."&code_etab=".$_SESSION['code_etab_excel']."';\n";
		print "</script>\n";
		
	}
	//End: For closing importation processing statement or displaying errors report
