<?php class erreur_manager {
	
		/**
		* Attribut : $e
		* <pre>
		* réfère à l'Exception
		* </pre>
		* @var 
		* @access public
		*/   
		public $e;
			
		/**
		* Attribut : $sql
		* <pre>
		* texte sql contenant des erreurs
		* </pre>
		* @var 
		* @access public
		*/   
		public $sql = '';
		
		
		/**
		* METHODE : __construct($e, $sql='')
		* <pre>
		* Constructeur
		* </pre>
		* @access public
		* 
		*/
		public function __construct($e, $sql=''){
			lit_libelles_page('/gestion_err_msg');
			$this->e 	= $e;
			$this->sql 	= $sql;
			$this->display_exception();
			$this->destroy_objet();
			$GLOBALS['erreur_manager'] = true ;
		}
		
		
		/**
		* METHODE : get_print_sql
		* <pre>
		* manage l'affichage de l'entête du type d'erreur  (cas d'erreur SQL)
		* </pre>
		* @access public
		* 
		*/
		public function get_print_sql(){
			$print_sql = '';
			if( $this->e->getMessage() == 'ERR_SQL' ){
				if( trim($this->sql) <> '' ){
					$print_sql = '<br>'.'<span style="color : black;">'.$this->sql.'</span>';
				}
			}
			return $print_sql ;
		}
		
		
		/**
		* METHODE : display_exception()
		* <pre>
		* Affichage des détails de l'erreur 
		* </pre>
		* @access public
		* 
		*/
		public function display_exception(){
			print'
			<br><br>
				<TABLE class="table-questionnaire" border="1" width="80%">
					<TR><TD colspan=2><span style="color : red;">'.recherche_libelle_page('Tit_Err').'</span></TD></TR>
					<TR>
						<TD><b>'.recherche_libelle_page('Fichier').'</b></TD>
						<TD><span style="color : blue;">'.$this->e->getFile().'</span></TD>
					</TR>	
					<TR>
						<TD><b>'.recherche_libelle_page('Ligne').'</b></TD>
						<TD><span style="color : blue;">'.$this->e->getLine().'</span></TD>
					</TR>
					<TR>
						<TD><b>'.recherche_libelle_page('Txt_err').'</b></TD>
						<TD>
							<span style="color : blue;">'.recherche_libelle_page($this->e->getMessage()).'</span>'.$this->get_print_sql().'
						</TD>
					</TR>
				</TABLE>
				<br>
			';
		}
		
		
		/**
		* METHODE : destroy_objet()
		* <pre>
		* Destruction de l'objet
		* </pre>
		* @access public
		* 
		*/
		public function destroy_objet(){
			//unset($this);
		}
	}
?>
