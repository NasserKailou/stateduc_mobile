<?php /*****************************************************************************************************************************
    *                           Auteur : Mlle Yacine FALL
    *                           Autres appuis : Bassirou TOURE 
    *                           Contrôle de qualité : Alassane OUEDRAOGO
    *                           Version : 1.0
    *                           Date de modification : 15/07/2005                            
    ******************************************************************************************************************************/
    /*****************************************************************************************************************************
    * Inclusion des bibliothèques nécessaires pour l'accès à la source de données et à la génération des fichiers au format pdf
    ******************************************************************************************************************************/
    /*              Fin des inclusion bibliothèques nécessaires
    ******************************************************************************************************************************/
	/**
	 * Classe report_user 
	 * Classe permet d'exporter un état crystal report au format pdf.
	 * et de l'afficher
    */
	class report_user{
		
        /**
         * Attribut : db
         * <pre>
         *  variable de connexion base de données
         * </pre>
         * @var string
         * @access private
         */
		private $db; 
        /**
         * Attribut : ref
         * <pre>
         *  Contient la référence de l'état
         * </pre>
         * @var string
         * @access private
         */
		private $ref; 
        /**
         * Attribut : crapp
         * <pre>
         *  permet de manipuler une application crystalreporrt à travers un objet COM. 
         * </pre>
         * @var COM
         * @access private
         */
    private $crapp ;
        /**
         * Attribut : creport
         * <pre>
         *  instancie un état crystalReport. 
         * </pre>
         * @var variant
         * @access private
         */
    private $creport;
        
    /**
     * Attribut : fileexport
     * <pre>
     *  Chemin complet du fichier pdf générer par l'exportation. 
     * </pre>
     * @var string
     * @access private
     */
    private $fileexport;
    
	/**
	 * Attribut : conn_temp
	 * <pre>
	 * Variable de connexion à la base des tables temporaires
	 * </pre>
 	 * @var string
	 * @access public
	 */	
	 private $conn_temp;

    /**
     * METHODE : __construct($reference)
     * <pre>
     * Constructeur de la classe :
     * </pre>
     * @access public
     * @param numeric $reference :la référence de l'état
     */ 
	 
	 	 
    function __construct($rpt_file, $tab_temp, $conn_temp){
        
		$this->conn_temp = $conn_temp;
        
        $this->rpt_file = $rpt_file;
		$this->tab_temp = $tab_temp;
		
        $this->crapp = new COM("CrystalDesignRunTime.Application");  
        //sleep(25);
		$this->creport = $this->crapp->OpenReport($GLOBALS['SISED_PATH_INC'].'/annuaire/rpt/'.$this->rpt_file);
             
        $this->fileexport = $GLOBALS['SISED_PATH_INC'].'/annuaire/pdf/'.$this->tab_temp.'.pdf';
        $this->refresh_data();
    }
    /* Méthode : refresh_data
    *<pre>
    *permet de raffraichir la source de données liée au report
    </pre>
    */
	function refresh_data(){
			//connexion
		if($this->conn_temp){
			
			$database 	= $this->conn_temp->database ;
			$host 		= $this->conn_temp->host ;
			$user 		= $this->conn_temp->user ;
			$password 	= $this->conn_temp->password ;
			
			switch($this->conn_temp->databaseType){
				case 'access' :{
					$dbc = new COM("ADODB.Connection") or die("Impossible de démarrer ADO");
					$dbc->Provider = "Microsoft.Jet.Oledb.4.0";
					$chemin = explode(';',$host);
					//die(str_replace('Dbq=','',$chemin[1]));
					$dbc->ConnectionString = str_replace('Dbq=','',$chemin[1]);
					$dbc->Open(); //OK   
					break;
				}
				case 'mysqli' :{
					$strConnexion = "DRIVER=MySQL ODBC 3.51 Driver;SERVER=".$host.";HOST=%;UID=".$user.";PWD=".$password.";DATABASE=".$database.";";
					$dbc = new COM("ADODB.Connection") or die("Impossible de démarrer ADO");
					$dbc->Open($strConnexion);                
					break;
				}            
				case 'mssql' :{
					$dbc = new COM("ADODB.Connection") or die("Impossible de démarrer ADO");
					$dbc->Open("Provider=SQLOLEDB; Data Source=".$host.";
					Initial Catalog=".$database."; User ID=".$user."; Password=".$password."");                
					break;
				}        
			}
			//Chargement de la requete
			$cm= $dbc->Execute('SELECT * FROM ' . $this->tab_temp);
			$this->creport->Database->SetDataSource($cm,3);
			$tables = $this->creport->Database->Tables;
	
			$tables = $this->creport->Database->Tables;
			for ($i=1 ; $i<=$tables->Count ;$i++){
				//$this->creport->Database->Tables($i)->Location =realpath('../db/ANNUAIRE V2.4.3.mdb');
				$this->creport->Database->Tables($i)->Name ="ado";
			}
		}
	 }
    /*METHODE :  exprot_rpt_pdf( )
    *<pre>
    *export crystalreport  vers pdf
    *</pre>
    */
    function exprot_rpt_pdf(){
        $this->creport->ExportOptions->DestinationType = 1;
        $this->creport->ExportOptions->FormatType = '&H1F';
        $this->creport->ExportOptions->DiskFileName=$this->fileexport;
        $this->creport->Export(false);
   }
    /*METHODE :  preview_report( reference)
    *<pre>
    *DEBUT
    * Donne un aperçu de l'état créé au format pdf
    *FIN
    *</pre>
    */
    function preview_report(){
        header('Location: ' . $GLOBALS['SISED_AURL'] . 'server-side/include/annuaire/pdf/' . $this->tab_temp . '.pdf');
		/*
		header('Content-type: application/pdf');
		header('Content-Disposition: attachment; filename="'.$this->tab_temp.'.pdf"');
		readfile($GLOBALS['SISED_AURL'] . 'server-side/include/annuaire/pdf/' . $this->tab_temp . '.pdf');*/

	}
    }
?>

