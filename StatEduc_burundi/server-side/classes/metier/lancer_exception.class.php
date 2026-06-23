<?php class lancer_exception {
	/**
	* METHODE : __construct($code_mess)
	* <pre>
	* instanciation et lancement des détails de l'Erreur
	* </pre>
	* @access public
	* 
	*/
	public function __construct($code_mess){
		try {
			throw new Exception($code_mess); // on force le lancement de l'exception
		}catch (Exception $e) {
			print'
				<TABLE class="table-questionnaire" border="1">
					<CAPTION><B>'.recherche_libelle_page(Err_excp).'</B></CAPTION>
					<TR>
						<TD>'.recherche_libelle_page(Fichier).' </TD>
						<TD><span style="color : blue;">'.$e->getFile().'</span></TD>
					</TR>	
					<TR>
						<TD>'.recherche_libelle_page(Ligne).'</TD>
						<TD><span style="color : blue;">'.$e->getTrace().'</span></TD>
					</TR>
					<TR>
						<TD>'.recherche_libelle_page(Txt_err).'</TD>
						<TD><span style="color : blue;">'.$e->getMessage().'</span></TD>
					</TR>
				</TABLE>
			';
		}
	}
}
?>
