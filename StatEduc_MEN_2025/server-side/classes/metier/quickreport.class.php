<?php
	/*
    * Inclusion des bibliothèques nécessaires pour l'accès à la source de données et à la génération des fichiers au format pdf
	*/
    define('FPDF_FONTPATH', $GLOBALS['SISED_PATH_LIB'] .'font/');
    include_once $GLOBALS['SISED_PATH_LIB'].'pdftable.inc.php';
    /**
     * Recupération des libellés de page selon la langue de l'application
     */
    lit_libelles_page('/liste_user_report.php');
	// Fin des inclusion bibliothèques nécessaires
    /**
     * Classe quickreport permet de créer un état au format html
     * Les données sont récupérées d'une base de données à laide
     * d'une requête que l'utilisateur a défini lors de la configuration
     * de l'état soit de manière
     * brute ou en définissant les tables, les champs et les jointures
	 * @package default
     * @access  public
     * @author Alassane, Touré, Yacine
     * @version 1.4
    */

class quickreport{

	 /**
	 * Attribut : id_report
	 * Référence de l'état
	 * @var numeric description
	 * @access private
	 */
    public $id_report;
	 /**
	 * Attribut : id_systeme
	 * On se positionne sur un secteur
	 * @var numeric
	 * @access private
	 */
    public $id_systeme;
    /**
     * Attribut : pdf
     * Instance  de la classe pdftable qui permet de générer
     * les états au format pdf
     * @var pdftable
     * @access private
     */
    public $pdf;
    /**	Attribut : langue
	 * Recupération de la langue de l'application
	 * @var string
	 * @access private
	*/
    public $langue;
     /**
     * Attribut : data
     * Tableau permettant de stocker les données recupérées à
     * partir de la requête
	 * @var array
	 * @access private
	*/
    public $data = array();
     /**
     * Attribut : param
     * Paramètres d'affichage du report(format, largeur des
     * colonnes, titre etc.)
	 * @var array
	 * @access private
	*/
    public $param = array();
    /**
     * Attribut : filtres
     * Critères à inserer dans la requete
	 * @var string
	 * @access private
	*/
    public $filtres = array();
    /**
     * Attribut : dimension
     * dimensions du report
	 * @var array
	 * @access private
	*/
    public $dimension = array();
    /**
     * Attribut : mesures
     * Mesures du report
	 * @var array
	 * @access private
	*/
    public $mesure = array();
    /**
     * Attribut : aggregation_level
     * Niveau d'agrégation du report
	 * @var array
	 * @access private
	*/
    public $aggregation_level = array();
      /**
     * Attribut : conn
     * Base de données
	 * @var adodb.connection
	 * @access private
	*/
    public $conn ;
    /**
     * Attribut : sql
     * Requête donnée par l'utilisateur ou construite à partir des paramètres
	 * @var string
	 * @access private
	*/
    public $sql;
    /**
     * Attribut : htmlTable
     * Chaine html à passer à la classe pdftable
	 * @var string
	 * @access private
	*/
    public $htmlTable  ;
    /**
     * Attribut : zones
     * Zones géographiques concernées par l'état
	 * @var string
	 * @access private
	*/
    public $zones;
    /**
     * Attribut : nomfic
     * Nom du fichier pdf généré par la classe pdftable
	 * @var string
	 * @access private
	*/
    public $nomfic;
    /**
     * Attribut : no_data
     * Booléen qui permet de tester si la requête renvoie des
     * données
	 * @var boolean
	 * @access private
	*/
    public $no_data;
    /**
     * Attribut : indic_all_zone
     * Nombre de sous-zones appartenant au niveau
     * géographique sélectionné
	 * @var numéric
	 * @access private
	*/
    public $indic_all_zone;
    /**
      * Attribut : with_etabliss
      * Variable qui permet de tester le niveau etablissement
	  * @var boolean
	  * @access private
	*/
    public $with_etabliss;
    /**
     * Attribut : with_etabliss
     * Cette variable permet d'exclure l'inclusion des
     * établissements
	 * @var boolean
	 * @access private
	*/
     public $with_school;
	 public $id_type_report;
    /**
	 * Constructeur de la classe :
     * Initialisation des paramètres de l'état système  d'enseignement,
     * les niveaux géographiques concernés, l'id du report, le titre ,
     * s'il faut afficher au nom les critères sur l'état,le format de
     * sortie du fichier(excel ou pdf), si les établissements sont
	 * affichés ou pas
	 * @access public
	 * @param array $param tableau contenant la connexion, la langue courante, le
	 * @param  string $nomfic nom du fichier pdf créé
	 */
    function __construct($param,$nomfic){
			$this->conn				= $GLOBALS['conn'];
            $this->langue    		= $_SESSION['langue'];
            $this->id_systeme       = $param["ID_SYSTEME"];
            $this->id_report        = $param["ID_REPORT"];
            $this->nomfic = $nomfic;
            $this->filtres = $param["CRITERES_NOM"];
            
			$zones = $param["CRITERES_REG"];
			
			$this->get_dico();
             
			if (strlen(trim($param["TITRE_USER_RPT"]))>0){
                $this->param["TITRE_ETAT"]=trim($param["TITRE_USER_RPT"]);
            }
            $this->param["AFFICH_CRITERES"] = $param["SHOW_CRITERES_RPT"];
            $this->with_school = $param["WITH_SCHOOL"];
			$this->param["WITH_SCHOOL"] 	= $param["WITH_SCHOOL"];
            $this->param["TYPE_EXPORT_RPT"] = $param["TYPE_EXPORT_RPT"];

            if ($this->no_data == false){
				$this->zones =$this->str_level_zone($zones);
                $this->aggregation_level['HIERARCHY_LEVEL'] =count($this->zones);
				$this->get_data();
				if ($this->param['ORIENTATION_MESURE']== 1)
                    $this->htmlTable =$this->format_html_1();
                else
                    $this->htmlTable =$this->format_html_2();
                $this->create_report();
            }else{
				// Affichage uniquement des critères et en lieu et place du message
                $this->zones =$this->str_level_zone($zones);
                $this->create_report();
            }
    }
	/**
	* Renvoie la chaine html construite contenant les données
    * (utilisée pour l'affichage de l'état au format html)
	* @access public
	*/
    function get_html(){
        return $this->htmlTable;
    }
	/**
	* Renvoie  la chaine html contenant la zone géographique, les filtres
	* définis sur l'état(utilisée pour l'affichage de l'état au format html)
	* @access public
	*/
    function get_entetehtml(){
        return $this->entete_etat();
    }
 	/**
	* Renvoie le titre de l'état
	* @access public
	*/
	function get_titre(){
        return $this->param["TITRE_ETAT"];
    }

 	/**
	* Renvoie la liste des divisions administratives concernées
	* DEBUT
    *   Pour chaque zone
    *   Recupération du nom de la zone
    *   Définiton du niveau géographique dans la chaine de regroupement
	*   Affichage ou non des écoles
	* FIN
	* @access private
	* @param array zones tableau contenant les types de niveau géographique choisis et les codes de ces niveaux
	*/
    private function str_level_zone($zones){
        if (isset($zones)){
            $tab_zones = array();
            $nb_level = count($zones);
            for($i = 0 ; $i<$nb_level;$i++){
                if(trim($zones[$i]["code_reg"]) !='All' && trim($zones[$i]["type_reg"]) !='etab'){
                    $str_requete = 'SELECT '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
                                    FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].'
                                    WHERE ((('.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].')='.(int)$zones[$i]["code_reg"].')
                                    AND (('.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].')='.$zones[$i]["type_reg"].'))';
					try {
						$rszone    = $this->conn->GetRow($str_requete);
                        if(!is_array($rszone)){
                                throw new Exception('ERR_SQL');
                        }else{
                            $tab_zones[$i]['CODE'] = $zones[$i]["code_reg"];
                            $tab_zones[$i]['LIBELLE'] = $rszone[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                            //echo $zones[$i]["code_reg"].'<br>';
                            if(in_array($zones[$i]["code_reg"], explode(',',$_SESSION['tab_rpt']['zone_pere']))){
                                $this->indic_all_zone = count($tab_zones);
                            }
                        }
                    }catch(Exception $e){
                                $erreur = new erreur_manager($e,$str_requete);
                    }
                }elseif(trim($zones[$i]["type_reg"]) !='etab'){
                    $tab_zones[] =trim($zones[$i]["code_reg"]);
                }elseif(trim($zones[$i]["type_reg"]) =='etab'){
					$requete       = 'SELECT '.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS NOM_ETAB
									 FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
									 WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$zones[$i]["code_reg"].'
									 AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$this->id_systeme;
					$nom_etab_choisi	= $this->conn->GetOne($requete);
					
					$tab_zones[$i]['CODE'] = $zones[$i]["code_reg"];
            		$tab_zones[$i]['LIBELLE'] = $nom_etab_choisi;
				}
            }
            $this->aggregation_level["HIERARCHY_LEVEL"] = $nb_level ;
        }else{
            $this->aggregation_level["HIERARCHY_LEVEL"] = $this->aggregation_level["HIERARCHY_LEVEL_MAX"] ;
        }
		if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
            $this->with_etabliss = false;
        }else{
            //$this->with_etabliss =true;
            // Modif Alassane
             if ($this->with_school==false){
                 $this->with_etabliss = false;
            }else{
                $this->with_etabliss =  true;
                $this->aggregation_level["HIERARCHY_LEVEL"] = $this->aggregation_level["HIERARCHY_LEVEL"] +1;
            }
            // Fin Modif Alassane
        }
        if(trim($_SESSION['tab_rpt']['zone_pere'])=="pays"){
            $this->indic_all_zone=0;
        }
        return $tab_zones ;
    }
	/**
	* Renvoie les paramètres d'une mesure et teste si le champ est effectivement une mesure
	* DEBUT
	*  Parcours de la liste des mesures
	*   Si nom_champ=Libelle_mesure alors
	*   retourne  tableau avec attribut de la mesure
	* FIN
	* @access private
	* @param array $dico_mes tableau des mesures liées à l'état
	* @param string $nom_champ nom de la mesure à tester
	*/
   private function est_une_mesure_rpt($dico_mes, $nom_champ){
        $result=array("EXIST"=>'false',"TYPE"=>'int');
        if (is_array($dico_mes)){
            foreach ($dico_mes as $mes){
                if (strtoupper($mes["LIBELLE_MESURE"])==strtoupper($nom_champ)){
                    $result["EXIST"]=true;
                    $result["TYPE"]=$mes["TYPE_MESURE"];
                    return $result;
                }
            }
        }

    }
	/**
	* Retourne les 30 ou 31 premiers caractères de $nom_champ en fonction du type de SGBD pour extraire la valeur du champ $nom_champ dans un recordset
	* <pre>
    * DEBUT
	*   Si type de SBGD= sqlsever alors
	*       on prend 30 caractères
	*   Sinon
	*       on prend 31 caractères
    * FIN
    * @access private
	* @param string $nom_champ nom du champ à tester
	*/
	function get_champ_extract($nom_champ){
		$champ_extract = $nom_champ;
        if (strlen($nom_champ)>30) {
            if ($GLOBALS['conn']->databaseType == 'mssqlnative' || $GLOBALS['conn']->databaseType == 'mssql') {
                    //$taille_max_extract=30;
					$taille_max_extract=strlen($nom_champ);
            }else{
                    $taille_max_extract=31;
            }
            $champ_extract = substr(trim($nom_champ) ,0,$taille_max_extract);
        }
        return(strtoupper($champ_extract));
	}

    /**
	* Récuperation des données à partir de la base
	* DEBUT
	* 	Recupération des noms de colonnes
	*   Construction de la requête :
	* 	Insertion des champs de l'atlas
	*   Insertion des dimensions
	* 	Insertions des mesures
	* 	Définition de la clause from (requête définie pour l'état)
	* 	Ajout de la clause where (filtres sur l'atlas)
	*   Ajout  de la clause GROUP BY (champs de l'atlas et des dimensions)
	* 	Ajout  de la clause ORDER BY (champs de l'atlas et des dimensions)
	* @access private
	*/
    private function get_data(){
		/*$sql='SELECT `ATLAS_COLLINE`.`PROVINCE` AS `PROVINCE`,`ATLAS_COLLINE`.`COMMUNE` AS `COMMUNE`,
		`ATLAS_COLLINE`.`COLLINE` AS `COLLINE`,`ATLAS_COLLINE`.`NOM_ETAB` AS `NOM_ETAB`,`TYPE_NIVEAU`.`LIBELLE_TYPE_NIVEAU` AS 
		`LIBELLE_TYPE_NIVEAU`,`TYPE_AGE`.`LIBELLE_TYPE_AGE` AS `LIBELLE_TYPE_AGE`,`TYPE_NIVEAU`.`ORDRE_TYPE_NIVEAU` AS ORDRE_TYPE_NIVEAU,`TYPE_AGE`.`ORDRE_TYPE_AGE` AS ORDRE_TYPE_AGE,
		Sum(`ELEVES_AGE_NIVEAU_SEXE`.`FILLES_AGE_NIVEAU`) AS F,
		Sum(`ELEVES_AGE_NIVEAU_SEXE`.`TOTAL_AGE_NIVEAU`) AS T FROM `ATLAS_COLLINE`,`ELEVES_AGE_NIVEAU_SEXE`,`TYPE_NIVEAU`,
		`TYPE_AGE` WHERE ATLAS_COLLINE.CODE_TYPE_SECTEUR_ENS = 2 AND ELEVES_AGE_NIVEAU_SEXE.CODE_TYPE_ANNEE = 13 AND `ATLAS_COLLINE`.`CODE_ETABLISSEMENT` = `ELEVES_AGE_NIVEAU_SEXE`.`CODE_ETABLISSEMENT` And `TYPE_NIVEAU`.`CODE_TYPE_NIVEAU` = `ELEVES_AGE_NIVEAU_SEXE`.`CODE_TYPE_NIVEAU` And `TYPE_AGE`.`CODE_TYPE_AGE` = `ELEVES_AGE_NIVEAU_SEXE`.`CODE_TYPE_AGE` GROUP BY ATLAS_COLLINE.CODE_TYPE_SECTEUR_ENS , ELEVES_AGE_NIVEAU_SEXE.CODE_TYPE_ANNEE,`ATLAS_COLLINE`.`PROVINCE`,`ATLAS_COLLINE`.`COMMUNE`,
		`ATLAS_COLLINE`.`COLLINE`,`ATLAS_COLLINE`.`NOM_ETAB`,`TYPE_NIVEAU`.`LIBELLE_TYPE_NIVEAU`,`TYPE_AGE`.`LIBELLE_TYPE_AGE`,`TYPE_NIVEAU`.`ORDRE_TYPE_NIVEAU`,`TYPE_AGE`.`ORDRE_TYPE_AGE`';
    	
		$data = $this->conn->GetRow($sql);*/
		$data = $this->conn->GetRow($this->sql);
		//print_r($data); exit;
		if (is_array($data)) {
            $keys = array_keys($data);
        }else{
             $keys=array();
        }
        $nb_champs = count($keys);
        $str_requete = '';
        // Condition pour la gestion du niveau établissement
        if ($this->with_etabliss == false ){
            for($i = 0; $i < $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($str_requete == ''){
                    $str_requete = 'SELECT REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }
        }else{
            // Ajout gestion au niveau établissement au cas selection des wereda
            for($i = 0; $i <= $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($str_requete == ''){
                    $str_requete = 'SELECT REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }// Fin Ajout Alassane
        }
        $une_dimension =$this->is_dimension_ligne();
        if(isset($une_dimension)){
             $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
        }else{
			 $une_dimension =$this->is_dimension_line_data();
			 if(isset($une_dimension)){
				if($une_dimension['DIM_LIBELLE_ENTETE']<>'')
					$str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE_ENTETE'].'';
				else
					$str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
			}
		}
        $une_dimension =$this->is_dimension_col();
        if(isset($une_dimension)){
             $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
        }
        $une_dimension =$this->is_dimension_imbriquee();
        if(isset($une_dimension)){
             $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
        }
        /*for ($i = $this->aggregation_level["HIERARCHY_LEVEL_MAX"]+1;$i<$this->aggregation_level["HIERARCHY_LEVEL_MAX"]+1+count($this->dimension); $i++){
                    $str_requete .= ',REQ.'.$keys[$i];
        }*/

        /* stocke le nombre de mesures de type text*/
		if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
			$borne =$this->aggregation_level["HIERARCHY_LEVEL_MAX"]+1+count($this->dimension);
		}else{
			$borne =$this->aggregation_level["HIERARCHY_LEVEL_MAX"]+count($this->dimension);
		}
        $nb_mesure_text =   0;
        for($i = $borne ; $i<$nb_champs ;$i++){
                $est_une_mesure = $this->est_une_mesure_rpt($this->mesure,$keys[$i]);
                if($est_une_mesure["EXIST"]==true){
                    switch (trim($est_une_mesure["TYPE"])){
                        case 'int': {
                            $str_requete .= ',sum(REQ.'.$keys[$i].') AS '.$keys[$i].'';
                            break;
                        }
                        case 'double':{
                            $str_requete .= ',sum(REQ.'.$keys[$i].') AS '.$keys[$i].'';
                            break;
                        }
                        case 'text':{
                            if ($this->with_etabliss==true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                                $str_requete .= ',REQ.'.$keys[$i].' AS '.$keys[$i].'';
                                $nb_mesure_text ++;
                            }
                            break;
                        }
                    }

                }
        }

        $str_requete .= ' FROM ('.$this->sql.') AS REQ ';
		if(isset($this->zones)){
            if(isset($this->indic_all_zone)){
                if (!(count($this->zones) == 1 && trim($this->zones[0]['LIBELLE']) == 'All')){
                    if ($this->indic_all_zone > 0 )
                    $str_requete .= ' WHERE ';
                }
            }else{
                if (!(count($this->zones) == 1 && trim($this->zones[0]['LIBELLE']) == 'All')){
                    $str_requete .= ' WHERE ';
                }
            }
			if(count($_SESSION['tab_rpt']['all_zones'])== 0 && !(trim($_SESSION['tab_rpt']['zone_pere'])=="pays")){
                for($i=0; $i < count($this->zones); $i++){
                    if ($i==0){
                        $str_requete .= '(REQ.'.$keys[$i].' =  \''. $this->zones[$i]['LIBELLE'].'\')';
                    }else{
                        $str_requete .= ' AND ('.$keys[$i].'=\''.$this->zones[$i]['LIBELLE'].'\')';
                    }
                }
            }elseif($this->indic_all_zone== 1 && !(trim($_SESSION['tab_rpt']['zone_pere'])=="pays")){
            	$str_requete .= '(REQ.'.$keys[0].' =  \''. $this->zones[0]['LIBELLE'].'\')';
            }else{
                if ($this->indic_all_zone > 0){
                    for($i=0; $i < count($this->zones)-1; $i++){
                       if(trim($this->zones[$i]) != 'All'){
                           if ($i != $this->indic_all_zone){
                                if ($i==0){
                                    $str_requete .= '(REQ.'.$keys[$i].' =  \''.$this->zones[$i]['LIBELLE'].'\')';
                                }else{
                                    $str_requete .= ' AND ('.$keys[$i].'=\''.$this->zones[$i]['LIBELLE'].'\')';
                                }
                            }
                        }
                    }
                }
            }
        }
        $str_requete .=' GROUP BY ';

        // Condition pour la gestion du niveau établissement
         if ($this->with_etabliss == false){
            for($i = 0; $i < $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($i == 0){
                    $str_requete .= 'REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }
        }else{
            // Ajout gestion au niveau établissement au cas selection des wereda
            for($i = 0; $i <= $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($i == 0){
                    $str_requete .= 'REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }
        }
        $une_dimension =$this->is_dimension_ligne();
        if(isset($une_dimension)){
            $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
            if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }else{
			 $une_dimension =$this->is_dimension_line_data();
			 if(isset($une_dimension)){
			 	if($une_dimension['DIM_LIBELLE_ENTETE']<>'')
					$str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE_ENTETE'].'';
				else
					$str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
			}
		}
        $une_dimension =$this->is_dimension_col();
        if(isset($une_dimension)){
            $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
            if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }
        $une_dimension =$this->is_dimension_imbriquee();
        if(isset($une_dimension)){
             $str_requete .= ',REQ.'.$une_dimension['DIM_LIBELLE'];
             if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }
        for($i = $this->$borne ; $i<$nb_champs ;$i++){
            $est_une_mesure = $this->est_une_mesure_rpt($this->mesure,$keys[$i]);
            if($est_une_mesure["EXIST"]==true){
                if($est_une_mesure["TYPE"] == 'text' && ($this->with_etabliss==true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])){
                    $str_requete .= ',REQ.['.$keys[$i].']';
                }
            }
        }
        $str_requete .= ' ORDER BY';
        if ($this->with_etabliss == false){
            for($i = 0; $i < $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($i == 0){
                    $str_requete .= ' REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }
        }else{
            // Ajout gestion au niveau établissement au cas selection des wereda
            for($i = 0; $i <= $this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                if ($i == 0){
                    $str_requete .= ' REQ.'.$keys[$i].'';
                }else{
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }// Fin Ajout Alassane
        }
        $une_dimension =$this->is_dimension_ligne();
        if(isset($une_dimension)){
            if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }
        $une_dimension =$this->is_dimension_col();
        if(isset($une_dimension)){
             if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }
        $une_dimension =$this->is_dimension_imbriquee();
        if(isset($une_dimension)){
             if (in_array($GLOBALS['PARAM']['ORDRE'].'_'.trim($une_dimension['TABLE_REF']),$keys))
                $str_requete .= ',REQ.'.$GLOBALS['PARAM']['ORDRE'].'_'.$une_dimension['TABLE_REF'];
        }
        for($i = $this->$borne ; $i<$nb_champs ;$i++){
            $est_une_mesure = $this->est_une_mesure_rpt($this->mesure,$keys[$i]);
            if($est_une_mesure["EXIST"]==true){
                if($est_une_mesure["TYPE"] == 'text' && ($this->with_etabliss==true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])){
                    $str_requete .= ',REQ.'.$keys[$i].'';
                }
            }
        }
        //echo( $str_requete);
        try {
                $this->data    = $this->conn->GetAll($str_requete );
                if(!is_array($this->data)){
                     throw new Exception('ERR_SQL');
                }
        }
        catch(Exception $e){
        	$erreur = new erreur_manager($e,$this->conn->ErrorMsg().'<br>'.$str_requete);
        }
    }

	/**
	* Construction de la chaine where (achèvement de la requete SQL)
	* DEBUT
	* Pour chaque filtre défini
	* 	ajout dans la clause where  de la requête associée à l'état
	* FIN
	* @access private
	* @param string $str_sql requête sql lié à l'état
	*/
    private function get_str_critere($str_sql){
    	$str_where ='';
        $pos = strpos($str_sql,'WHERE');
        foreach($this->filtres as $un_filtre){
            // Filtre des critères ALL
            if(($un_filtre["VAL_CRITERE_FILTRE_REPORT"])!=0){ // Ajout Alassane
                if ($str_where == ''){
                    $str_where .='WHERE '.$un_filtre["NOM_TABLE_DONNEES"].'.'.$un_filtre["CHAMP_TABLE_DONNEES"].' '.$un_filtre["OP_CRITERE_FILTRE_REPORT"].' '.$un_filtre["VAL_CRITERE_FILTRE_REPORT"];
                }else{
                    $str_where .= ' '.$un_filtre["OPERATEUR_REQUETE"].' '.$un_filtre["NOM_TABLE_DONNEES"].'.'.$un_filtre["CHAMP_TABLE_DONNEES"].' '.$un_filtre["OP_CRITERE_FILTRE_REPORT"].' '.$un_filtre["VAL_CRITERE_FILTRE_REPORT"];
                }
            }// Ajout Alassane
            // Fin filtre des critères ALL
        }
        if ($pos === false){
            $str_modifie =  str_replace('$str_where',$str_where,$str_sql);
        }else{
            if ( trim($str_where) == ''){
                $str_modifie =  $str_sql;
            }else{
                 $str_modifie =  str_replace('WHERE',$str_where.' AND ',$str_sql);
            }
        }
        $str_where ='';
        foreach($this->filtres as $un_filtre){
            // Filtre des critères ALL
            if(($un_filtre["VAL_CRITERE_FILTRE_REPORT"])!=0){ // Ajout Alassane
                if ($str_where == ''){
                    $str_where .='GROUP BY '.$un_filtre["NOM_TABLE_DONNEES"].'.'.$un_filtre["CHAMP_TABLE_DONNEES"].'';
                }else{
                    $str_where .= ' , '.$un_filtre["NOM_TABLE_DONNEES"].'.'.$un_filtre["CHAMP_TABLE_DONNEES"].'';
                }
            } // Fin Ajout Alassane
             // Fin filtre des critères ALL
        }
        if (trim($str_where) != '')
            $str_modifie =  str_replace('GROUP BY ',$str_where.',',$str_modifie );
        //echo $str_modifie;;
        return $str_modifie;
    }
    /**
	* Permet de lire la configuration associée au thème et au système d'enseignement depuis DICO
    * pour associer les attributs de la classe à
	* leurs valeurs respectives
	* Recupération de la requête associée à l'état(requête stockée dans la base
	* DICO ou construite à partir des paramètres(Tables, champs, critères,
	* jointures))
	* Complèter la requête SQL en ajoutant les critères
	* Recupération les paramètres d'affichage
	* Dimensions
	* Mesures
	* Niveau d'agrégation
	* @access private
	*/
    private function get_dico(){
    	//sql
        $requete    = 'SELECT DICO_REPORT.SQL_REPORT, DICO_REPORT.ID_TYPE_REPORT FROM  DICO_REPORT WHERE DICO_REPORT.ID = '.$this->id_report;
        // Traitement Erreur Cas : GetAll / GetRow
        try {
                $rsSql    = $GLOBALS['conn_dico']->GetRow($requete);
                if(!is_array($rsSql)){
                        throw new Exception('ERR_SQL');
                }else{
                    //
                    //$requete = new create_query($this->id_report);
                    //$this->sql = $requete->creer_query();
					//echo "<pre>";
					//print_r($this->sql);die;
                    // $req = 'UPDATE DICO_REPORT SET SQL_REPORT =\''.$this->sql.'\' WHERE ID  = '.$this->id_report.'';
                    //if($GLOBALS['conn_dico']->Execute($req) === false) echo '<br><font color=red> error updating Report SQL </font><br>'.$req;
                    //if (trim($this->sql)==''){
                        $this->sql = $this->get_str_critere($rsSql["SQL_REPORT"]);
						$this->id_type_report = $rsSql["ID_TYPE_REPORT"];
                    //}
                    try {
                            $data = array();
							$data =$this->conn->GetAll($this->sql);
                            if(!is_array($data )){
                                    throw new Exception('ERR_SQL');
                            }else{
                                if (count($data)>0)
                                    $this->no_data = false;
                                else
                                    $this->no_data =true;
                            }
                    }catch(Exception $err){
                        $erreur = new erreur_manager($err,$requete);
                    }
                }
        }
        catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
        //Paramètres d'affichage
        $requete        = 'SELECT DICO_REPORT_SYSTEME.* FROM  DICO_REPORT_SYSTEME WHERE DICO_REPORT_SYSTEME.ID = '.$this->id_report.' AND ID_SYSTEME = '.$this->id_systeme;
		// Traitement Erreur Cas : GetAll / GetRow
        try {
                $this->param    = $GLOBALS['conn_dico']->GetRow($requete);
                if(!is_array($this->param)){
                        throw new Exception('ERR_SQL');
                }
        }
        catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
        $align = $this->param["ALIGNEMENT_MESURE"] ;
        switch($align ){
            case 1: $this->param["ALIGNEMENT_MESURE"] = 'left';
                    break;
            case 2: $this->param["ALIGNEMENT_MESURE"] = 'center';
                    break;
            case 3: $this->param["ALIGNEMENT_MESURE"] = 'right';
                    break;
            default :$this->param["ALIGNEMENT_MESURE"] = 'center';
                    break;
        }
        $this->param["TITRE_ETAT"] = $this->recherche_libelle($this->id_report,$this->langue,'DICO_REPORT');
        if (!isset($this->param["HAUTEUR_LIGNE"])){
            $this->param["HAUTEUR_LIGNE"] = 10;
        }
        //Dimensions
        $requete        = 'SELECT DICO_DIMENSION.*,DICO_DIMENSION_REPORT.DIM_LIBELLE_ENTETE, DICO_DIMENSION_REPORT.TYPE_DIMENSION FROM  DICO_DIMENSION_REPORT, DICO_DIMENSION WHERE DICO_DIMENSION_REPORT.ID = '.$this->id_report.' AND DICO_DIMENSION.ID_DIMENSION = DICO_DIMENSION_REPORT.ID_DIMENSION ';
				// Traitement Erreur Cas : GetAll / GetRow
        try {
                $this->dimension    = $GLOBALS['conn_dico']->GetAll($requete);
                if(!is_array($this->dimension )){
                        throw new Exception('ERR_SQL');
                }
        }
		catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
		//Niveau d'aggregation
        $requete        = 'SELECT DICO_AGGREGATION_LEVEL.* FROM  DICO_AGGREGATION_LEVEL, DICO_AGGREGATION_REPORT WHERE DICO_AGGREGATION_REPORT.ID = '.$this->id_report.' AND DICO_AGGREGATION_LEVEL.ID_AGGREGATION = DICO_AGGREGATION_REPORT.ID_AGGREGATION ';
				// Traitement Erreur Cas : GetAll / GetRow
        try {
                $this->aggregation_level    = $GLOBALS['conn_dico']->GetRow($requete);
                if(!is_array($this->aggregation_level )){
                        throw new Exception('ERR_SQL');
                }
        }
        catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
		//Dimensions provenant d'une table de données
        $dim_line = $this->is_dimension_ligne();
		if(!isset($dim_line) || !count($dim_line)){
			//Exclusion NOM_CHAMP dimension
			$list_chps_dim = '(';
			$cpt_chps = 0;
			foreach($this->dimension as $dms){
				if($cpt_chps==0) $list_chps_dim .= "'".$dms['DIM_LIBELLE']."'";
				else $list_chps_dim .= ",'".$dms['DIM_LIBELLE']."'";
				$cpt_chps++;
			}
			$dim_atlas = $data[0];
			if (is_array($dim_atlas)){
                $dim_atlas = array_keys($dim_atlas);
            }else{
                $dim_atlas =array();
            }
			for ($i= 0; $i <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"]; $i++){
				if($cpt_chps==0) $list_chps_dim .= "'".$dim_atlas[$i]."'";
				else $list_chps_dim .= ",'".$dim_atlas[$i]."'";
				$cpt_chps++;
			}
			$list_chps_dim .= ')';
			if($list_chps_dim<>'()')
				$not_in_list_chps_dim = ' AND DICO_RPT_CHAMP.NOM_CHAMP NOT IN '.$list_chps_dim;
			else
				$not_in_list_chps_dim='';
			//Fin Exclusion NOM_CHAMP dimension
			
			//Exclusion ALIAS dimension
			$list_chps_dim = '(';
			$cpt_chps = 0;
			for ($i= 0; $i <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"]; $i++){
				if($cpt_chps==0) $list_chps_dim .= "'".$dim_atlas[$i]."'";
				else $list_chps_dim .= ",'".$dim_atlas[$i]."'";
				$cpt_chps++;
			}
			$list_chps_dim .= ')';
			if($list_chps_dim<>'()')
				$not_in_list_alias_dim = ' AND DICO_RPT_CHAMP.ALIAS NOT IN '.$list_chps_dim;
			else
				$not_in_list_alias_dim='';
			
			$req_dim_line        = 'SELECT DICO_RPT_CHAMP.NOM_CHAMP AS DIM_LIBELLE, DICO_RPT_CHAMP.ALIAS AS DIM_LIBELLE_ENTETE, 1 AS TYPE_DIMENSION FROM  DICO_RPT_CHAMP WHERE DICO_RPT_CHAMP.ID = '.$this->id_report.' AND DICO_RPT_CHAMP.ID_TYPE_CHAMP = 2 '.$not_in_list_chps_dim.$not_in_list_alias_dim;
			try {
				$this->dimension_line_data    = $GLOBALS['conn_dico']->GetAll($req_dim_line);
				if(!is_array($this->dimension_line_data )){
						throw new Exception('ERR_SQL');
				}
			}
			catch(Exception $e){
				$erreur = new erreur_manager($e,$req_dim_line);
			}
		}
        //Mesures : A revoir Hebie
		$requete        = 'SELECT DICO_RPT_CHAMP.ID_CHAMP AS ID_MESURE,DICO_RPT_CHAMP.ID_TYPE_CHAMP AS TYPE_MESURE,DICO_RPT_CHAMP.ALIAS AS LIBELLE_MESURE ,
        DICO_RPT_CHAMP.ENTETE_CHAMP AS LIBELLE_MESURE_ENTETE,
        DICO_RPT_CHAMP.FORMAT AS TYPE_MESURE,
        DICO_RPT_CHAMP.EXPRESSION AS EXPRESSION_FORMULE,
        DICO_RPT_CHAMP.ENTETE_CHAMP AS ENTETE_MESURE FROM  DICO_RPT_CHAMP WHERE DICO_RPT_CHAMP.ID = '.$this->id_report.' AND DICO_RPT_CHAMP.ID_TYPE_CHAMP = 1';
        //echo $requete;
				// Traitement Erreur Cas : GetAll / GetRow
        try {
                $this->mesure    = $GLOBALS['conn_dico']->GetAll($requete);
                if(!is_array($this->mesure)){
                    $requete = 'SELECT DICO_MESURE.*, DICO_MESURE_REPORT.ENTETE_MESURE FROM  DICO_MESURE, DICO_MESURE_REPORT WHERE DICO_MESURE_REPORT.ID = '.$this->id_report.' AND DICO_MESURE.ID_MESURE = DICO_MESURE_REPORT.ID_MESURE ORDER BY DICO_MESURE_REPORT.ORDRE';
                    try{
                        $this->mesure    = $GLOBALS['conn_dico']->GetAll($requete);
                        if (!is_array($this->mesure))
                            throw new Exception('ERR_SQL');
                    }catch(Exception $e){
                        $erreur = new erreur_manager($e,$requete);
                    }
                }
        }
        catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
    }
	/**
	* Construction de l'entete de l'état
	* Debut
	* Définition d'un tableau html
	* insertion des zones géographiques
	* insertion des filtres
	* @access private
	*/
    function entete_etat(){
        if($this->param['orientation']=='L'){
            $htmlentete='<table border=1 '.$this->attrib_table('TABLE',80).'>';
        }else{
            $htmlentete='<table border=1 '.$this->attrib_table('TABLE',100).'>';
        }
        if (isset($this->zones)){
            if (count($this->filtres )==0)
                $htmlentete .= '<tr><td align="center" bgcolor="#78A0C8" '.$this->attrib_table('height',5).' colspan= 1>';
            else
                $htmlentete .= '<tr><td align="center" bgcolor="#78A0C8" '.$this->attrib_table('height',5).' colspan= 2>';
            $cpt =0;
            $all = false;
            foreach($this->zones as $une_zone){
                if ($all == false){
                    if ($cpt == 0)
                        $htmlentete .= '';
                    else
                        $htmlentete .= '--';
                    if ($une_zone['CODE']==$_SESSION['tab_rpt']['zone_pere'] or trim($_SESSION['tab_rpt']['zone_pere']) == 'pays'){
                        $htmlentete .= 'All';
                        $all =true;
                    }
                    if (trim($_SESSION['tab_rpt']['zone_pere']) == 'pays'){
                        $htmlentete .= ' '.$_SESSION['NOM_PAYS'];
                    }else{
                        $htmlentete .= ' '.$une_zone['LIBELLE'];
                    }
                    $cpt +=1;
                }
            }
            $htmlentete .= '</td></tr>';
        }
       	//if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6)){
			foreach($this->filtres as $un_filtre){
				$htmlentete .='<tr>';
	
				// Gestion des All pour les critères avec All
				if (trim($un_filtre["VAL_CRITERE_FILTRE_REPORT"])!=0){ // AJout Alassane
					$str_requete = 'SELECT '.$un_filtre["TABLE_REF"].'.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"].' FROM '.$un_filtre["TABLE_REF"].
					' WHERE '.$un_filtre["TABLE_REF"].'.'.$GLOBALS['PARAM']['CODE'].'_'.$un_filtre["TABLE_REF"].' = '.$un_filtre["VAL_CRITERE_FILTRE_REPORT"];
					try {
							$rsSql    = $this->conn->GetRow($str_requete);
							if(!is_array($rsSql)){
									throw new Exception('ERR_SQL');
							}else{
								$htmlentete .='<td>'.$un_filtre["LIBELLE"].'</td>';
								switch (trim($un_filtre["OP_CRITERE_FILTRE_REPORT"])){
									case '<>':{
										$htmlentete .='<td>'.str_replace('<','&lt;',recherche_libelle_page('diff')).'  '.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
									case '<':{
										$htmlentete .='<td>'.str_replace('<','&lt;',recherche_libelle_page('inf')).'  '.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
									case '>':{
										$htmlentete .='<td>'.str_replace('<','&lt;',recherche_libelle_page('sup')).'  '.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
									case '<=':{
										$htmlentete .='<td>'.str_replace('<','&lt;',recherche_libelle_page('inf_eg')).'  '.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
									case '>=':{
										$htmlentete .='<td>'.str_replace('<','&lt;',recherche_libelle_page('sup_eg')).'  '.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
									case '=':{
										$htmlentete .='<td>'.$rsSql[$this->get_champ_extract($GLOBALS['PARAM']['LIBELLE'].'_'.$un_filtre["TABLE_REF"])].'</td>';
										break;
									}
								}
								//print_r($rsSql);
							}
					}catch(Exception $e){
						$erreur = new erreur_manager($e,$str_requete);
					}
				}else{
					// cas des All pour les critères avec les all ajout Alassane
					$htmlentete .='<td>'.$un_filtre["LIBELLE"].'</td>';
					$htmlentete .='<td>'.recherche_libelle_page('all').'</td>';
				}
	
				$htmlentete .='</tr>';
			}
		//}
        $htmlentete .= '</table>';
        return $htmlentete ;
    }
    /**
	* construction de la chaine html(données) à passer à la classe
	* pdftable et orientation mesure = 1
	* DEBUT
	* Recupération des dimensions associées à la requête
	* Recupération des mesures(en-têtes)
	* Structures de l'atlas
	* Définition d'un tableau html
	* Insertion des en-têtes de colonnes
	* Insetion des données ligne par ligne avec calcul des totaux si nécessaire
	* Pour chaque ligne
	* 	ajouter la ou les zones géographiques
	* 	ajouter la dimension ligne s'il existe
	*   ajouter les données
	* FIN
	* @access private
	*/
    private function format_html_1(){
        $data = $this->data;
        if (count($data)> 0){
            if (is_array($this->dimension)){
                $dim_ligne   = $this->is_dimension_ligne();
				if(!isset($dim_ligne) || !count($dim_ligne)){
					$dim_ligne =$this->is_dimension_line_data();
					if($dim_ligne['DIM_LIBELLE_ENTETE']<>'')
						$dim_ligne['DIM_LIBELLE'] = $dim_ligne['DIM_LIBELLE_ENTETE'];
				}
                $dim_col     = $this->is_dimension_col();
                $dim_imbriq  = $this->is_dimension_imbriquee();
            }
            // Fin de la récuperation des types de dimensions définis pour le report

            /*Récupération des occurrences des modalités de la dimension colonne  ainsi que
            les modalités de la dimension imbriquée*/
            if (isset($dim_col)){
                $dim = $this->extract_occ_dim($data, $dim_col);
                ksort($dim );
                $nb_dimension = count($dim);
                if (isset($dim_imbriq)){
                    $dim_imbr = $this->extract_occ_dim($data, $dim_imbriq);
                    ksort($dim_imbr );
                    $nb_dimension_imbr = count($dim_imbr);
                }
            }
            /*Fin de la récupération des occurrences des modalités de la dimension colonne ainsi de la
            dimension imbriquée si celle-ci est définie*/

           /* Récupération du nombre de mesures définies pour le report*/
            $nb_mesure = count($this->mesure);

            /* initialisation de la taille ou la largeur du report en fonction de la mise en page choisie*/
            if($this->param['orientation']=='L'){
                $htmltable='<table border=1 '.$this->attrib_table('TABLE',80).'>';
            }else{
                $htmltable='<table border=1 '.$this->attrib_table('TABLE',100).'>';
            }

            $ligne = '<tr>';
            /* Récupération de la structure de l'Atlas dans un tableau*/

            $atlas = $data[0];
            if (is_array($atlas)){
                $atlas = array_keys($atlas);
            }else{
                $atlas =array();
            }
            $nb_tot_aff = $this->affich_tot_aggre();
            if (count($this->zones) == $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
            }else{
                if (!isset($_SESSION['tab_rpt']['zone_pere']) or trim($_SESSION['tab_rpt']['zone_pere'])=='pays' ){
                    $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
                }else{
                    $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-2;
                }
            }
            if (isset($dim_ligne)){
                $level_tot += 2;
            }
            /*Condition pour la gestion du niveau établissement  */
            if($this->with_etabliss == false){
                $niveau_hierarchie =$this->aggregation_level["HIERARCHY_LEVEL"]-1;
                $colspan = 1;
            }else{
                $niveau_hierarchie =$this->aggregation_level["HIERARCHY_LEVEL"] ;
                $colspan = 2;
            }
            /* Insertion des entetes des colonnes de la structure de l'Atlas et des autres colonnes
                qui doivent apparaitre au niveau du report en fonction de la structure de la requete*/
			if (count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6)){//Ajout Hebie pour school report IMIS
				for ($i= 0; $i <= $niveau_hierarchie; $i++){
					if ($nb_mesure >1 && isset($dim_col)){
						if (isset($dim_imbriq)){
							$ligne .= '<td rowspan =3 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
						}else{
							$ligne .= '<td rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
						}
					}else{
						$ligne .= '<td rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
					}
				}
			}
            /* Fin du formattage des entetes des colonnes*/
            /* totaux par regroupement*/
            if (isset($dim_ligne)){
                if ($nb_mesure >1 && isset($dim_col))
                    if (isset($dim_imbriq)){
                        $ligne .='<td  rowspan =3 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTETE"].'</td>';
                    }else{
                        $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTETE"].'</td>';
                    }
                else
                    $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTETE"].'</td>';
            }
            if (isset($dim_col)){
                $cpt = 0;
                foreach ($dim as $une_dim){
                    if (isset($dim_imbriq)){
                        $ligne .='<td colspan ='.$nb_dimension_imbr*$nb_mesure .' '.$this->attrib_table('width',($this->param["LARGEUR_COLONNE_ZONE"]*$nb_mesure*$nb_dimension_imbr)).'  align ="center">'.$une_dim.'</td>';
                    }else{
                        $ligne .='<td colspan ='.$nb_mesure.' '.$this->attrib_table('width',($this->param["LARGEUR_COLONNE_ZONE"]*$nb_mesure)) .'  align ="center">'.$une_dim.'</td>';
                    }
                    $cpt +=1;
                }
            }else{
                $nb_dimension = 1;
            }
            if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                if (isset($dim_col)){
                    if (isset($dim_imbriq)){
                            $ligne .='<td colspan ='.$nb_dimension_imbr*$nb_mesure .' '.$this->attrib_table('width',($this->param["LARGEUR_COLONNE_ZONE"]*$nb_mesure*$nb_dimension_imbr)).'  align ="center">TOTAL</td>';
                        }else{
                            $ligne .='<td colspan ='.$nb_mesure.' '.$this->attrib_table('width',($this->param["LARGEUR_COLONNE_ZONE"]*$nb_mesure)) .'  align ="center">TOTAL</td>';
                     }
                }
                if (isset($dim_col)){
                    if($nb_mesure >1 or isset($dim_imbriq)){
                        if ($nb_mesure >1 && isset($dim_col)){
                            if (isset($dim_imbriq)){
                                $ligne .='<td  rowspan =3 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">TOTAL</td>';
                            }else{
                                $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">TOTAL</td>';
                            }
                        }else{
                            $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">TOTAL</td>';
                        }
                    }
                }
            }
            //Affichage dimension imbriquée
            if(isset($dim_imbriq)){
                $ligne .= '</tr><tr>';
                if (isset($dim_ligne)){
                    // Condition pour la gestion du niveau établissement
                    $ligne .= '<td colspan ='.(count($this->zones) + $colspan).'></td>';
                }elseif($nb_mesure == 1){
                    $ligne .= '<td colspan ='.(count($this->zones) + $colspan -1).'></td>';
                }
                foreach($dim as $une_dim){
                    foreach($dim_imbr as $une_dim_imbr){
                        $ligne .='<td colspan ='.$nb_mesure.' '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]*$nb_mesure ).'  align ="center">'.$une_dim_imbr.'</td>';
                    }
                }
                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                    foreach($dim_imbr as $une_dim_imbr){
                        $ligne .='<td colspan ='.$nb_mesure.' '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]*$nb_mesure ).'  align ="center">'.$une_dim_imbr.'</td>';
                    }
                }
            }else{
                $nb_dimension_imbr=1;
            }
            //Affichage label mesure
			if ($nb_mesure > 1){
                if (isset($dim)){
                    $ligne .='</tr><tr>';
                        foreach($dim as $une_dim){
                        if (isset($dim_imbr)){
                            foreach ($dim_imbr as $une_dim_imbr){
                                foreach($this->mesure as $une_mesure){
                                    if ($this->with_etabliss == false && trim($une_mesure['TYPE_MESURE'])=='text')
                                        $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .' align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                                    else//if($this->with_etabliss == true)
                                        $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .' align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                                }
                            }
                        }else{
							foreach($this->mesure as $une_mesure){
                                if ($this->with_etabliss == false && trim($une_mesure['TYPE_MESURE'])<>'text')
                                    $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                                else//if($this->with_etabliss == true)
                                    $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                            }
                        }
                    }
                }else{
                    //$ligne .='</tr><tr>';
                    foreach($this->mesure as $une_mesure){
                        if ($this->with_etabliss == false && trim($une_mesure['TYPE_MESURE'])<>'text'){
                            $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                        }elseif($this->with_etabliss == true){
                            $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                        }
                    }
                }
            }elseif(!isset($dim)){
				$ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .'  align="center">'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
            }
            $nb_col_mes = $nb_mesure * $nb_dimension * $nb_dimension_imbr;
                //Ajout 31 mai
            if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                /*if ($dim_imbr){
                    foreach($dim_imbr as $une_dim_imbr){
                        $ligne .='<td colspan ='.$nb_mesure.' '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]*$nb_mesure ).'  align ="center">'.$une_dim_imbr.'</td>';
                    }
                }*/
                if ($nb_mesure >1 && isset($dim_col)){
                    if (isset($dim)){
                        if (isset($dim_imbr)){
                            foreach ($dim_imbr as $une_dim_imbr){
                                foreach($this->mesure as $une_mesure){
                                    $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .' align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                                }
                            }
                        }else{
                            foreach($this->mesure as $une_mesure){
                                $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                            }
                        }
                    }elseif(isset($dim_col) == false){
                        $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]) .' align="center">TOTAL</td>';
                    }else{
                        //$ligne .='</tr><tr>';
                        foreach($this->mesure as $une_mesure){
                            $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align="center">'.$une_mesure["ENTETE_MESURE"].'</td>';
                        }
                    }
                }
                if(!isset($dim_col)){
                    $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align="center">TOTAL</td>';
                }

            }
            $ligne .= '</tr>';
            $htmltable .= $ligne;
            //echo '<table>'.$ligne;'</table>';

            /*Récupération des données pour le formattage du document html*/
            $cpt = 0;
            unset($tab_tot_vertical);
            $nb_tot_vertical = 0;
            if (is_array($data )) {
				foreach($data as $ligne_data){
                    $cle = array_keys($ligne_data);
					if (isset($temp_zone)==false){
						$ligne = '<tr>';
                        for ($i= 0; $i<$nb_col_mes; $i++){
                            $mes[] = $this->param["VAL_DONNEES_MANQUANTES"];
                        }
                    }else{
						$cpt = 0;
                        $egal_zone = false;
                        for ($i=0; $i<=$niveau_hierarchie; $i++){
                            if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                $cpt += 1;
                            }
                        }
                        if ($temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]]==$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])]){
                            $cpt +=1;
                        }
                        //Ajout 31 MAi
                        if($this->with_etabliss == true ){
                            if ($temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]+1]==$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])])
                            $cpt +=1;
                        }
                        if($this->with_etabliss == false)
                            $cpt_new_line = $this->aggregation_level["HIERARCHY_LEVEL"] + 1;
                        else
                            $cpt_new_line = $this->aggregation_level["HIERARCHY_LEVEL"] + 2;
                            
							if ($cpt == $cpt_new_line){
                                $egal_zone = true;
                            }else{
								if (is_array($mes)){
									//Modif hebie pr gestion proportion par colonne IMIS
                                    unset($sum_lig);
                                    for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig[$cptTotHor] = 0;
                                    $cptTotHor = 0;
                                    foreach($mes as $une_mes){
                                        if (trim($une_mes) <> '****'){
                                        $une_mes =$this->str_to_number($une_mes);
                                        if (isset($dim_col)){
                                            if(isset($dim_imbr)){
                                                if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
                                                    if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                                    $cptTotHor +=1;
                                                }else{
                                                    if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                                    $cptTotHor =0;
                                                }
                                            }else{
                                                if ($cptTotHor< ($nb_mesure-1)){
                                                    if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                                    $cptTotHor +=1;
                                                }else{
                                                    if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                                    $cptTotHor =0;
                                                }
                                            }
                                        }else{
                                            if (is_numeric($une_mes)){
                                                $sum_lig[$cptTotHor] +=$une_mes;
                                            }
                                        }
                                        }
                                    }
									
									foreach($mes as $une_mes){
                                        if (trim($une_mes) <> '****'){
											if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round($une_mes*100/$sum_lig[0],0).'%</td>';
											else
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$this->str_to_number($une_mes).'</td>';
                                        }
                                    }
                                    //Fin Modif hebie
                                    for($i = $niveau_hierarchie ;$i>=0;$i--){
                                        $mes_tot[$i][] = $mes;
                                    }
                                }
                                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] ==1 ){
                                    $tot_hor =0;
                                    $ind_mesure_type = 0;
									if(is_array($sum_lig ))
                                    foreach($sum_lig as $une_sum){
                                        if (isset($dim_col)){
                                            switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
                                                case 'int' : {
													if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
                                                    	$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,0, ',','')*100/$une_sum,0).'%</td>';
                                                    else
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,0, ',','').'</td>';
                                                    
													break;
                                                }
                                                case 'double' : {
                                                    if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
                                                    	$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,2, ',','')*100/$une_sum,0).'%</td>';
                                                    else
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,2, ',','').'</td>';
													break;
                                                }
                                            }
                                            $mes[] = $une_sum;
                                        }
                                        ///controle nombre de mesure atteint et mise à zero si necessaire
                                        if ($ind_mesure_type < ($nb_mesure -1))
                                            $ind_mesure_type +=1;
                                        else
                                            $ind_mesure_type =0;
                                        $tot_hor += $this->str_to_number($une_sum);
                                    }
                                    if($nb_mesure >1 or isset($dim_imbriq)){
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($tot_hor,0, ',','').'</td>';
                                        $mes[] = $tot_hor;
                                    }
                                }
                                if ($this->aggregation_level["HIERARCHY_LEVEL"] == 1 && trim($_SESSION['tab_rpt']['zone_pere'])=='pays'){
                                    $tab_tot_vertical[$nb_tot_vertical] = $mes;
                                    $nb_tot_vertical +=1;
                                }
                                $ligne     .= '</tr>';
                                $htmltable .= $ligne;
                                if($this->with_etabliss == false){
                                }
                                //totaux par regroupemnt
                                for($i=$this->born_sup_tot();$i>=$this->born_inf_tot();$i--){
                                    if ($ligne_data[$this->get_champ_extract($cle[$i])] <> $temp_zone[$i]){
                                        if($this->with_etabliss == false){
                                            if (isset($dim_ligne)){
                                                $ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
                                            }else{
                                                $ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]).'>Total '.$temp_zone[$i].'</td>';
                                            }
                                        }else{
                                            if (isset($dim_ligne)){
												$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+2).'>Total '.$temp_zone[$i].'</td>';
                                            }else{
												$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
                                            }
                                        }
                                        $sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
                                        
										//Modif Hebie IMIS
										unset($sum_lig_regroup);
                                        for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig_regroup[$cptTotHor] = 0;
                                        $cptTotHor = 0;
                                        if(is_array($sum))
                                        foreach($sum as $une_ligne){
											$une_ligne =$this->str_to_number($une_ligne);
											if (isset($dim_col)){
												if(isset($dim_imbr)){
													if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
														if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
														$cptTotHor +=1;
													}else{
														if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
														$cptTotHor =0;
													}
												}else{
													if ($cptTotHor< ($nb_mesure-1)){
														if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
														$cptTotHor +=1;
													}else{
														if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
														$cptTotHor =0;
													}
												}
											}else{
												if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
											}
										}
										//Fin Modif Hebie IMIS
										unset($sum_lig);
                                        for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig[$cptTotHor] = 0;
                                        $cptTotHor = 0;
                                        $ind_mesure_type =0;
                                        if(is_array($sum))
                                        foreach($sum as $une_ligne){
											switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
                                                case 'int':{
                                                    if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round(number_format($une_ligne,0,',','')*100/$sum_lig_regroup[0],0).'%</td>';
													else
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.number_format($une_ligne,0,',','').'</td>';
                                                    break;
                                                }
                                                case 'double':{
                                                    if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round(number_format($une_ligne,2,',','')*100/$sum_lig_regroup[0],0).'%</td>';
													else
														$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.number_format($une_ligne,2,',','').'</td>';
                                                    break;
                                                }
                                                case 'text':{
                                                    if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])
                                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >-</td>';
                                                    break;
                                                }
                                            }
                                            ///controle nombre de mesure atteint et mise à zero si necessaire
                                            if ($ind_mesure_type < ($nb_mesure -1))
                                                $ind_mesure_type +=1;
                                            else
                                                $ind_mesure_type =0;
											$une_ligne =$this->str_to_number($une_ligne);
                                            if (isset($dim_col)){
                                                if(isset($dim_imbr)){
                                                    if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
                                                        if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
                                                        $cptTotHor +=1;
                                                    }else{
                                                        if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
                                                        $cptTotHor =0;
                                                    }
                                                }else{
                                                    if ($cptTotHor< ($nb_mesure-1)){
                                                        if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
                                                        $cptTotHor +=1;
                                                    }else{
                                                        if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
                                                        $cptTotHor =0;
                                                    }
                                                }
                                            }else{
                                                if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
                                            }
                                        }

                                        if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] ==1 ){
                                            $tot_hor =0;
                                            $ind_mesure_type = 0;
                                            foreach($sum_lig as $une_sum){
                                                if(isset($dim_col)){
                                                    switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
                                                        case 'int' : {
                                                            if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
																$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,0, ',','')*100/$sum_lig[0],0).'%</td>';
															else
																$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,0, ',','').'</td>';
                                                            break;
                                                        }
                                                        case 'double' : {
                                                            if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
																$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,2, ',','')*100/$sum_lig[0],0).'%</td>';
															else
																$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,2, ',','').'</td>';
                                                            break;
                                                        }
                                                    }
                                                    $sum[] = $une_sum;
                                                }
                                                ///controle nombre de mesure atteint et mise à zero si necessaire
                                                if ($ind_mesure_type < ($nb_mesure -1))
                                                    $ind_mesure_type +=1;
                                                else
                                                    $ind_mesure_type =0;
                                                $tot_hor += $this->str_to_number($une_sum);
                                            }
                                            if($nb_mesure >1 or isset($dim_imbriq)){
                                                $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($tot_hor,0, '.','').'</td>';
                                                $sum[] = $tot_hor;
                                            }
                                        }
                                        if ($this->aggregation_level["HIERARCHY_LEVEL"] > 1 && trim($_SESSION['tab_rpt']['zone_pere'])=='pays' && $i == 0){
                                            $tab_tot_vertical[$nb_tot_vertical] = $sum;
                                            $nb_tot_vertical +=1;
                                        }
                                        $ligne .= '</tr>';
                                        $htmltable .= $ligne;
                                        unset($mes_tot[$i]);
                                    }
                                }
                                unset($mes);
                                for ($i= 0; $i<$nb_col_mes; $i++){
                                    $mes[] = $this->param["VAL_DONNEES_MANQUANTES"];
                                }
                                $ligne      = '<tr>';
                            }
                    }
                    if ($egal_zone == false){
                        for ($i=0; $i<=$niveau_hierarchie; $i++){
                            if ($cpt == 0 ){
                                $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
									$ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                            }else{
                                if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))
										$ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'></td>';
                                }else{
                                    $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))
										$ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                                }
                            }
                            $cpt +=1;
                        }
                        if (isset($dim_ligne)){
                            if ($this->with_etabliss == false){
                                $temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]]=$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])];
                            }else{
                                $temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]+1]=$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])];
                            }
                            $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])].'</td>';
                        }
                    }
                    $cpt =0;
                    foreach($this->mesure as $key_mes=>$une_mesure){
                        if (isset($dim_col)){
                            if(isset($dim_imbriq)){
                                foreach($dim as $key=>$une_dim){
                                    foreach($dim_imbr as $key_imbr=>$une_dim_imbr){
                                        $ind_courant = ($key*$nb_dimension_imbr*$nb_mesure) +($key_imbr*$nb_mesure) + $key_mes;
                                        if ($ligne_data[$this->get_champ_extract($dim_col["DIM_LIBELLE"])] == $une_dim && $ligne_data[$this->get_champ_extract($dim_imbriq["DIM_LIBELLE"])] == $une_dim_imbr){
                                            switch ($une_mesure["TYPE_MESURE"]){
                                                case 'int' :{
                                                    $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                                    break;
                                                }
                                                case 'double' :{
                                                    $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                                    break;
                                                }
                                                case 'text':{
                                                     if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])
                                                        $mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
                                                    break;
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                foreach($dim as $key=>$une_dim){
                                    $ind_courant = ($key*$nb_mesure) + $key_mes;
                                    if ($ligne_data[$this->get_champ_extract($dim_col["DIM_LIBELLE"])] == $une_dim){
                                        switch ($une_mesure["TYPE_MESURE"]){
                                            case 'int' :{
                                                $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                                break;
                                            }
                                            case 'double' :{
                                                $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                                break;
                                            }
                                            case 'text':{
                                                if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])
                                                    $mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
                                                else
                                                    $mes[$ind_courant] = '****';
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            foreach($this->mesure as $ind_courant=>$une_mesure){
                                switch (trim($une_mesure["TYPE_MESURE"])){
                                    case 'int' :{
                                        $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                        break;
                                    }
                                    case 'double' :{
                                        $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                        break;
                                    }
                                    case 'text':{
                                        if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                                            $mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
                                            }else{
                                                $mes[$ind_courant] = '****';
                                            }
                                        break;
                                    }
                                }
                            }
                        }
                        $cpt +=1;
                    }
                }
            }
                //Derniere ligne
                if (is_array($mes)){
					//Modif HEBIE IMIS
					unset($sum_lig);
                    for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig[$cptTotHor] = 0;
                    $cptTotHor = 0;
                    foreach($mes as $une_mes){
                        if (trim($une_mes)<>'****'){
                            $une_mes =$this->str_to_number($une_mes);
                            if (isset($dim_col)){
                                if(isset($dim_imbr)){
                                    if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
                                        if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                        $cptTotHor +=1;
                                    }else{
                                        if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                        $cptTotHor =0;
                                    }
                                }else{
                                    if ($cptTotHor< ($nb_mesure-1)){
                                        if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                        $cptTotHor +=1;
                                    }else{
                                        if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                                        $cptTotHor =0;
                                    }
                                }
                            }else{
                                if (is_numeric($une_mes)) $sum_lig[$cptTotHor] +=$une_mes;
                            }
                        }
                    }
                    foreach($mes as $une_mes){
                        if (trim($une_mes)<>'****'){
                        	if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")    
								$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round($une_mes*100/$sum_lig[0],0).'%</td>';
							else
								$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$this->str_to_number($une_mes).'</td>';
						}
                    }
                    // Condition pour la gestion du niveau établissement
                    //if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                        for($i = $niveau_hierarchie;$i>=0;$i--){
                            $mes_tot[$i][] = $mes;
                        }
                }
                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] ==1 ){
                    $tot_hor =0;
                    $ind_mesure_type = 0;
                    foreach($sum_lig as $une_sum){
                        if (isset($dim_col)){
                            switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
                                case 'int':{
                                    if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
										$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,0,',','')*100/$une_sum,0).'%</td>';
                                    else
										$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,0,',','').'</td>';
									break;
                                }
                                case 'double':{
									if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
                                    	$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,2,',','')*100/$une_sum,0).'%</td>';
									else
										$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,2,',','').'</td>';
                                    break;
                                }
                                case 'text':{
                                    $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >-</td>';
                                    break;
                                }
                            }
                            $mes[] = $une_sum;
                        }
                        ///controle nombre de mesure atteint et mise à zero si necessaire
                        if ($ind_mesure_type < ($nb_mesure -1))
                            $ind_mesure_type +=1;
                        else
                            $ind_mesure_type =0;
                        $tot_hor += $this->str_to_number($une_sum);
                    }
                    if($nb_mesure >1 or isset($dim_imbriq)){
                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.$tot_hor.'</td>';
                        $mes[] = $tot_hor;
                    }
                }
                if ($this->aggregation_level["HIERARCHY_LEVEL"]  == 1 && trim($_SESSION['tab_rpt']['zone_pere'])=='pays'){
                    $tab_tot_vertical[$nb_tot_vertical] = $mes;
                    $nb_tot_vertical +=1;
                }
                $htmltable .= $ligne;
                //Totaux par regroupement
                if ($this->aggregation_level["HIERARCHY_LEVEL"] == 1){
                    $level_tot = $level_tot-1;
                }
                
				if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6)){
                    for($i=$this->born_sup_tot();$i>=$this->born_inf_tot();$i--){
						if($this->with_etabliss == false){
							if (isset($dim_ligne)){
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
							}else{
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]).'>Total '.$temp_zone[$i].'</td>';
							}
						}else{
							if (isset($dim_ligne)){
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+2).'>Total '.$temp_zone[$i].'</td>';
							}else{
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
							}
						}
						$sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
						
						if (is_array($sum )){ // Ajout Alassane pour tester si c'est un tableau
							//Modif Hebie IMIS
							unset($sum_lig_regroup);
							for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig_regroup[$cptTotHor] = 0;
							$cptTotHor = 0;
							foreach($sum as $une_ligne){
								$une_mes =$this->str_to_number($une_mes);
								if (isset($dim_col)){
									if(isset($dim_imbr)){
										if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
											if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
											$cptTotHor +=1;
										}else{
											if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
											$cptTotHor =0;
										}
									}else{
										if ($cptTotHor< ($nb_mesure-1)){
											if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
											$cptTotHor +=1;
										}else{
											if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
											$cptTotHor =0;
										}
									}
								}else{
									if (is_numeric($une_ligne)) $sum_lig_regroup[$cptTotHor] +=$une_ligne;
								}
							}
							//Fin modif Hebie
							
							unset($sum_lig);
							for($cptTotHor =0; $cptTotHor<($nb_dimension_imbr*$nb_mesure);$cptTotHor++) $sum_lig[$cptTotHor] = 0;
							$cptTotHor = 0;
							$ind_mesure_type =0;
							foreach($sum as $une_ligne){
								switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
									case 'int':{
										if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round(number_format($une_ligne,0,',','')*100/$sum_lig_regroup[0],0).'%</td>';
										else
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.number_format($une_ligne,0,',','').'</td>';
										break;
									}
									case 'double':{
										if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.round(number_format($une_ligne,2,',','')*100/$sum_lig_regroup[0],0).'%</td>';
										else
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.number_format($une_ligne,2,',','').'</td>';
										break;
									}
									case 'text':{
										if ($this->with_etabliss==true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >-</td>';
										break;
									}
								}
								///controle nombre de mesure atteint et mise à zero si necessaire
								if ($ind_mesure_type < ($nb_mesure -1))
									$ind_mesure_type +=1;
								else
									$ind_mesure_type =0;
								//$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$une_ligne.'</td>';
								$une_mes =$this->str_to_number($une_mes);
								if (isset($dim_col)){
									if(isset($dim_imbr)){
										if ($cptTotHor < ($nb_dimension_imbr*$nb_mesure-1)){
											if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
											$cptTotHor +=1;
										}else{
											if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
											$cptTotHor =0;
										}
									}else{
										if ($cptTotHor< ($nb_mesure-1)){
											if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
											$cptTotHor +=1;
										}else{
											if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
											$cptTotHor =0;
										}
									}
								}else{
									if (is_numeric($une_ligne)) $sum_lig[$cptTotHor] +=$une_ligne;
								}
							}
						}
	
						if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] ==1 ){
							//$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.$sum_lig.'</td>';
							$tot_hor =0;
							$ind_mesure_type =0;
							foreach($sum_lig as $une_sum){
								if (isset($dim_col)){
									switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
										case 'int' : {
											if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,0, ',','')*100/$une_sum,0).'%</td>';
											else
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,0, ',','').'</td>';
											break;
										}
										case 'double' : {
											if($nb_mesure==1 && $this->mesure[0]['EXPRESSION_FORMULE']=="%")
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.round(number_format($une_sum,2, ',','')*100/$une_sum,0).'%</td>';
											else
												$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_sum,2, ',','').'</td>';
											break;
										}
										case 'text' : {
											$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">-</td>';
											break;
										}
									}
									$sum[] = $une_sum;
								}
								///controle nombre de mesure atteint et mise à zero si necessaire
								if ($ind_mesure_type < ($nb_mesure -1))
									$ind_mesure_type +=1;
								else
									$ind_mesure_type =0;
								$tot_hor += $this->str_to_number($une_sum);
	
							}
							if($nb_mesure >1 or isset($dim_imbriq)){
								$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($tot_hor,0,',','').'</td>';
								$sum[] = $tot_hor;
							}
						}
						$ligne .= '</tr>';
						if ($this->aggregation_level["HIERARCHY_LEVEL"] <> 1 && trim($_SESSION['tab_rpt']['zone_pere'])=='pays' && $i == 0){
							$tab_tot_vertical[$nb_tot_vertical] = $sum;
							$nb_tot_vertical +=1;
						}
						$htmltable .= $ligne;
					}
                }
                //Affichage du total général
                if (trim($_SESSION['tab_rpt']['zone_pere'])=='pays'){
                    if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1 ){
                        if($this->with_etabliss == false){
                            $colspan =$this->aggregation_level["HIERARCHY_LEVEL"];
                        }else{
                            $colspan =$this->aggregation_level["HIERARCHY_LEVEL"]+1;
                        }
                        if (isset($dim_ligne)){
                            $ligne = '<tr><td colspan ='.($colspan +1).'>Total </td>';
                        }else{
                            $ligne = '<tr><td colspan ='.$colspan.'>Total </td>';
                        }
                        $sum = $this->sum_colonne_array($tab_tot_vertical,$nb_tot_vertical,count($tab_tot_vertical[0]));
                        $ind_mesure_type = 0;
                        if (is_array($sum)){
                            foreach($sum as $une_ligne){
                                switch(trim($this->mesure[$ind_mesure_type]['TYPE_MESURE'])){
                                    case 'int' : {
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_ligne,0, ',','').'</td>';
                                        break;
                                    }
                                    case 'double' : {
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">'.number_format($une_ligne,2, ',','').'</td>';
                                        break;
                                    }
                                    case 'text' : {
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle">-</td>';
                                        break;
                                    }
                                }
                                ///controle nombre de mesure atteint et mise à zero si necessaire
                                if ($ind_mesure_type < ($nb_mesure -1))
                                    $ind_mesure_type +=1;
                                else
                                    $ind_mesure_type =0;
                            }
                        }
                        $htmltable .= $ligne.'</tr>';
                    }
                }
        /**/

        $htmltable .= '</table>';
        }
    return $htmltable;
    }
    /**
	* construction de la chaine html(données) à passer à la classe pdftable
	* et orientation mesure = 2
	* DEBUT
	* Recupération des dimensions associées à la requête
	* Recupération des mesures(en-têtes)
	* Structures de l'atlas
	* Définition d'un tableau html Insertion des en-têtes de colonnes
	* Insetion des données ligne par ligne avec calcul des totaux si nécessaire
	* Pour chaque ligne
	* ajouter la ou les zones géographiques
	* ajouter la dimension ligne s'il existe
	* ajouter les données
	* FIN
	* @access private
	*/
    private function format_html_2(){
        $data = $this->data;
        if (count($data)> 0){
            /*recupere les occurences de la dimension
            Récuperation des types de dimensions définis pour le report*/
            if (is_array($this->dimension)){
                $dim_ligne   = $this->is_dimension_ligne();
				if(!isset($dim_ligne) || !count($dim_ligne)){
					$dim_ligne =$this->is_dimension_line_data();
					if($dim_ligne['DIM_LIBELLE_ENTETE']<>'')
						$dim_ligne['DIM_LIBELLE'] = $dim_ligne['DIM_LIBELLE_ENTETE'];
				}
                $dim_col     = $this->is_dimension_col();
                $dim_imbriq  = $this->is_dimension_imbriquee();
            }
            // Fin de la récuperation des types de dimensions définis pour le report

            /*Récupération des occurrences des modalités de la dimension colonne  ainsi que
            les modalités de la dimension imbriquée*/
            if (isset($dim_col)){
                $dim = $this->extract_occ_dim($data, $dim_col);
                ksort($dim );
                $nb_dimension = count($dim);
                if (isset($dim_imbriq)){
                    $dim_imbr = $this->extract_occ_dim($data, $dim_imbriq);
                    ksort($dim_imbr );
                    $nb_dimension_imbr = count($dim_imbr);
                }
            }
            /*Fin de la récupération des occurrences des modalités de la dimension colonne ainsi de la
            dimension imbriquée si celle-ci est définie*/

           /* Récupération du nombre de mesures définies pour le report*/
            $nb_mesure = count($this->mesure);

            /* initialisation de la taille ou la largeur du report en fonction de la mise en page choisie*/
            if($this->param['orientation']=='L'){
                $htmltable='<table border=1 '.$this->attrib_table('TABLE',80).'>';
            }else{
                $htmltable='<table border=1 '.$this->attrib_table('TABLE',100).'>';
            }

            $ligne = '<tr>';
            /* Récupération de la structure de l'Atlas dans un tableau*/
            $atlas = $data[0];
            if (is_array($atlas)){
                $atlas = array_keys($atlas);
            }else{
                $atlas =array();
            }
            $nb_tot_aff = $this->affich_tot_aggre();
            if (count($this->zones) == $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
            }else{
                if (!isset($_SESSION['tab_rpt']['zone_pere']) or trim($_SESSION['tab_rpt']['zone_pere'])=='pays' ){
                    $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
                }else{
                    $level_tot = $this->aggregation_level["HIERARCHY_LEVEL"]-2;
                }
            }
            if (isset($dim_ligne)){
                $level_tot += 1;
            }
             // Condition pour la gestion du niveau établissement
			if (count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6)){
	            if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
	                for ($i= 0; $i <$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
	                    if ($nb_mesure >1 && isset($dim_col)){
	                        if (isset($dim_imbriq)){
	                            $ligne .= '<td rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'>'.$atlas[$i].'</td>';
	                        }else{
	                            $ligne .= '<td rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'>'.$atlas[$i].'</td>';
	                        }
	                    }else{
	                        $ligne .= '<td rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'>'.$atlas[$i].'</td>';
	                    }
	                }
	            }else{
	                // Ajout gestion au niveau établissement au cas selection des wereda
	                for ($i= 0; $i <=$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
	                    if ($nb_mesure >1 && isset($dim_col)){
	                        if (isset($dim_imbriq)){
	                            $ligne .= '<td rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
	                        }else{
	                            $ligne .= '<td rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
	                        }
	                    }else{
	                        $ligne .= '<td rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$atlas[$i].'</td>';
	                    }
	                }
	            }
			}
            if (isset($dim_ligne)){
                if ($nb_mesure >1 && isset($dim_col))
                    if (isset($dim_imbriq)){
                        $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTETE"].'</td>';
                    }else{
                        $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTETE"].'</td>';
                    }
                else
                    $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">'.$dim_ligne["DIM_LIBELLE_ENTTE"].'</td>';
            }
            if ($nb_mesure >1 && isset($dim_col)){
                if (isset($dim_imbriq)){
                    $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center"></td>';
                }else{
                    $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center"></td>';
                }
            }else{
                $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center"></td>';
            }
            if (isset($dim_col)){
                $dim = $this->extract_occ_dim($data, $dim_col);
                ksort($dim );
                $nb_dimension = count($dim);
                if (isset($dim_imbriq)){
                    $dim_imbr = $this->extract_occ_dim($data, $dim_imbriq);
                    ksort($dim_imbr );
                    $nb_dimension_imbr = count($dim_imbr);
                }
                $cpt = 0;
                if (isset($dim_ligne)){
                    $ligne .= '<td colspan ='.(count($this->zones)+2).'></td>';
                }else{
                    //echo '2';
                    //$ligne .= '<td colspan ='.(count($this->zones)+1).'></td>';
                }
                foreach ($dim as $une_dim){
                    if (isset($dim_imbriq)){
                        $ligne .='<td colspan ='.$nb_dimension_imbr.' '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]*$nb_dimension_imbr) .'"  align ="center">'.$une_dim.'</td>';
                    }else{
                        $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align ="center">'.$une_dim.'</td>';
                    }
                    $cpt +=1;
                }
            }else{
                $nb_dimension = 1;
            }
            //echo  '<table>'.$ligne.'</tr></table>'; exit;
            if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                if ($nb_mesure >1 && isset($dim_col))
                    if (isset($dim_imbriq)){
                        $ligne .='<td  rowspan =2 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">Total</td>';
                    }else{
                        $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">Total</td>';
                    }
                else
                    $ligne .='<td  rowspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).' align="center">Total</td>';
            }
            $ligne     .= '</tr>';
            if(isset($dim_imbriq)){
                $ligne .= '</tr><tr>';
                foreach($dim as $une_dim){
                    foreach($dim_imbr as $une_dim_imbr){
                        $ligne .='<td colspan =1 '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).'  align ="center">'.$une_dim_imbr.'</td>';
                    }
                }
            }else{
                $nb_dimension_imbr=1;
            }
            $ligne .='</tr>';
            $htmltable .=$ligne;
            $nb_col_mes = $nb_mesure * $nb_dimension * $nb_dimension_imbr;
            $cpt = 0;
            // Parcours des donnees
            foreach($data as $ligne_data){
                $cle = array_keys($ligne_data);
                // Pas de changement de ligne
                if (isset($temp_zone)==false){
                    $ligne = '<tr>';
                    for ($i= 0; $i<$nb_col_mes; $i++){
                        $mes[] = $this->param["VAL_DONNEES_MANQUANTES"];
                    }
                }else{
                    // POINT DE REPRISE: Nouvelle ligne
                    $cpt = 0;
                    $egal_zone = false;
                    //...
                    // Condition pour la gestion du niveau établissement  (type_regroupement different du dernier regroupement)
                    if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                        // Verifie si on doit ajouter une autre ligne dans le tableau
                        for ($i=0; $i<$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                            if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                $cpt += 1;
                            }
                        }
                        if ($temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]]==$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])]){
                            $cpt +=1;
                        }
                        // fin verification ajout new line
                        if ($cpt == $this->aggregation_level["HIERARCHY_LEVEL"]+1){
                            $egal_zone = true;
                        }else{
                            if (is_array($mes)){
                                // Libelle de la mesure
                                $ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
                                if (isset($dim_ligne)){
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
										$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +1;
									else
										$nb_td_vide = 1;
                                }else{
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
										$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"];
									else
										$nb_td_vide = 0;
                                }
                                unset($ligne_une_mesure);
                                $sum_une_mesure[0] = 0;
                                for($i = 1; $i < $nb_mesure; $i++){
                                    $sum_une_mesure[$i] = 0;
                                    $ligne_une_mesure[$i] = '<tr>';
                                    for($j =0 ; $j < $nb_td_vide; $j++){
                                        $ligne_une_mesure[$i] .='<td></td>';
                                    }
                                    $ligne_une_mesure[$i] .='<td>'.$this->mesure[$i]["ENTETE_MESURE"].'</td>';
                                }
                                for($i = 0; $i < count($mes);){
                                    $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i].'</td>';
                                    if (is_numeric($mes[$i])){
                                        $sum_une_mesure[0] += $mes[$i];
                                    }
                                    for($j = 1; $j < $nb_mesure; $j++){
                                        if (is_numeric($mes[$i+$j])){
                                            $sum_une_mesure[$j] += $mes[$i+$j];
                                        }
                                        $ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i+$j].'</td>';
                                    }
                                     $i +=$nb_mesure;
                                }
                                for($i =  $level_tot ;$i>=$nb_tot_aff;$i--){
                                    $mes_tot[$i][] = $mes;
                                }
                            }
                            if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                $ligne     .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0].'</td></tr>';
                            }else{
                                $ligne     .= '</tr>';
                            }
                            $htmltable .= $ligne;
                            for($i = 1; $i < $nb_mesure; $i++){
                                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                    $htmltable .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$i].'</td></tr>' ;
                                }else{
                                    $htmltable .=$ligne_une_mesure[$i].'</tr>' ;
                                }

                            }
                            // Total vertical
                            if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
                                     $ligne_total = '<tr>';
                                    for($j =0 ; $j < $nb_td_vide; $j++){
                                        $ligne_total .='<td></td>';
                                    }
                                    $ligne_total .= '<td>Total</td>';
                                for ($i=0; $i < count($mes);$i+=$nb_mesure){
                                    $sum_vertical[$i]=0;
                                    for ($j=0; $j < $nb_mesure;$j++){
                                        $sum_vertical[$i] +=$mes[$i+$j];
                                    }
                                    $ligne_total .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_vertical[$i].'</td>';
                                }
                                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                    $ligne_total .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.array_sum($sum_vertical).'</td></tr>' ;
                                }
                                $htmltable.= $ligne_total;
                           }
                           unset($sum_une_mesure);
                            // Ajout des totaux par division administrative
                            for($i =  $level_tot  ;$i>= $nb_tot_aff;$i--){
                                if ($ligne_data[$this->get_champ_extract($cle[$i])] <> $temp_zone[$i]){
                                    if (isset($dim_ligne)){
                                        $ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
                                    }else{
                                        $ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]).'>Total '.$temp_zone[$i].'</td>';
                                    }
                                    $ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
                                    $sum_une_mesure[0] = 0;
                                    $sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
                                    if (isset($dim_ligne)){
                                        if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
											$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +1;
										else
											$nb_td_vide = 1;
                                    }else{
                                        if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
											$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"];
										else
											$nb_td_vide = 0;
                                    }
                                    for($k = 1; $k < $nb_mesure; $k++){
                                        $sum_une_mesure[$k] = 0;
                                        $ligne_une_mesure[$k] = '<tr>';
                                        for($j =0 ; $j < $nb_td_vide; $j++){
                                            $ligne_une_mesure[$k] .='<td></td>';
                                        }
                                        $ligne_une_mesure[$k] .='<td>'.$this->mesure[$k]["ENTETE_MESURE"].'</td>';
                                    }
                                    for($k = 0; $k < count($sum);){
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k].'</td>';
                                        if (is_numeric($mes[$k])){
                                            $sum_une_mesure[0] += $sum[$k];
                                        }
                                        for($j = 1; $j < $nb_mesure; $j++){
                                            if (is_numeric($mes[$k+$j])){
                                                $sum_une_mesure[$j] += $sum[$k+$j];
                                            }
                                            $ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k+$j].'</td>';
                                        }
                                        $k +=$nb_mesure;
                                    }
                                    if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                        $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0].'</td></tr>';
                                    }else{
                                        $ligne .= '</tr>';
                                    }
                                    $htmltable .= $ligne;
                                    for($k = 1; $k < $nb_mesure; $k++){
                                        if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                            $htmltable .=$ligne_une_mesure[$k].'<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'" '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$k].'</td></tr>' ;
                                        }else{
                                            $htmltable .=$ligne_une_mesure[$k].'</tr>' ;
                                        }
                                    }
                                    unset($mes_tot[$i]);
                                }
                            }
                            unset($mes);
                            for ($i= 0; $i<$nb_col_mes; $i++){
                                $mes[] = $this->param["VAL_DONNEES_MANQUANTES"];
                            }
                            $ligne      = '<tr>';
                        }
                    }else{
                        // Ajout gestion au niveau établissement au cas selection des wereda
                        for ($i=0; $i<=$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                            if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                $cpt += 1;
                            }
                        }
                        if ($temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]+1]==$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])]){
                            $cpt +=1;
                        }
                        if ($cpt == $this->aggregation_level["HIERARCHY_LEVEL"]+2){
                            $egal_zone = true;
                        }else{
                            if (is_array($mes)){
                                $ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
                                if (isset($dim_ligne)){
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
										$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +2;
									else
										$nb_td_vide = 2;
                                }else{
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
										$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"]+1;
									else
										$nb_td_vide = 1;
                                }
                                unset($ligne_une_mesure);
                                $sum_une_mesure[0] = 0;
                                for($i = 1; $i < $nb_mesure; $i++){
                                    $sum_une_mesure[$i] = 0;
                                    $ligne_une_mesure[$i] = '<tr>';
                                    for($j =0 ; $j < $nb_td_vide; $j++){
                                        $ligne_une_mesure[$i] .='<td></td>';
                                    }
                                    $ligne_une_mesure[$i] .='<td>'.$this->mesure[$i]["ENTETE_MESURE"].'</td>';
                                }
                                for($i = 0; $i < count($mes);){
                                    $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i].'</td>';
                                    if (is_numeric($mes[$i])){
                                        $sum_une_mesure[0] += $mes[$i];
                                    }
                                    for($j = 1; $j < $nb_mesure; $j++){
                                        if (is_numeric($mes[$i+$j])){
                                            $sum_une_mesure[$j] += $mes[$i+$j];
                                        }
                                        $ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i+$j].'</td>';
                                    }
                                     $i +=$nb_mesure;
                                }
                                for($i = $this->aggregation_level["HIERARCHY_LEVEL"];$i>=0;$i--){
                                    $mes_tot[$i][] = $mes;
                                }
                            }
                            if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                $ligne     .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0].'</td></tr>';
                            }else{
                                $ligne     .= '</tr>';
                            }
                            $htmltable .= $ligne;
                            for($i = 1; $i < $nb_mesure; $i++){
                                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                    $htmltable .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$i].'</td></tr>' ;
                                }else{
                                    $htmltable .=$ligne_une_mesure[$i].'</tr>' ;
                                }

                            }
                            // Total vertical
                            if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
                                     $ligne_total = '<tr>';
                                    for($j =0 ; $j < $nb_td_vide; $j++){
                                        $ligne_total .='<td></td>';
                                    }
                                    $ligne_total .= '<td>Total</td>';
                                for ($i=0; $i < count($mes);$i+=$nb_mesure){
                                    $sum_vertical[$i]=0;
                                    for ($j=0; $j < $nb_mesure;$j++){
                                        $sum_vertical[$i] +=$mes[$i+$j];
                                    }
                                    $ligne_total .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_vertical[$i].'</td>';
                                }
                                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                    $ligne_total .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.array_sum($sum_vertical).'</td></tr>' ;
                                }
                                $htmltable.= $ligne_total;
                           }
                            unset($sum_une_mesure);

                            if (isset($dim_ligne)){
                                $nb_level_tot= $this->aggregation_level["HIERARCHY_LEVEL"];
                            }else{
                                $nb_level_tot= $this->aggregation_level["HIERARCHY_LEVEL"]-1;
                            }
                            for($i = $nb_level_tot ;$i>= 0;$i--){
                                if ($ligne_data[$this->get_champ_extract($cle[$i])] <> $temp_zone[$i]){
                                    if (isset($dim_ligne)){
                                        $ligne = '<tr><td colspan ="'.($this->aggregation_level["HIERARCHY_LEVEL"]+2).'">Total '.$temp_zone[$i].'</td>';
                                    }else{
                                        $ligne = '<tr><td colspan ="'.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'">Total '.$temp_zone[$i].'</td>';
                                    }
                                    $ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
                                    $sum_une_mesure[0] = 0;
                                    $sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
                                    if (isset($dim_ligne)){
                                        if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
											$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +2;
										else
											$nb_td_vide = 2;
                                    }else{
                                        if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
											$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"]+1;
										else
											$nb_td_vide = 1;
                                    }
                                    for($k = 1; $k < $nb_mesure; $k++){
                                        $sum_une_mesure[$k] = 0;
                                        $ligne_une_mesure[$k] = '<tr>';
                                        for($j =0 ; $j < $nb_td_vide; $j++){
                                            $ligne_une_mesure[$k] .='<td></td>';
                                        }
                                        $ligne_une_mesure[$k] .='<td>'.$this->mesure[$k]["ENTETE_MESURE"].'</td>';
                                    }
                                    for($k = 0; $k < count($sum);){
                                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k].'</td>';
                                        if (is_numeric($mes[$k])){
                                            $sum_une_mesure[0] += $sum[$k];
                                        }
                                        for($j = 1; $j < $nb_mesure; $j++){
                                            if (is_numeric($mes[$k+$j])){
                                                $sum_une_mesure[$j] += $sum[$k+$j];
                                            }
                                            $ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k+$j].'</td>';
                                        }
                                        $k +=$nb_mesure;
                                    }
                                    //ligne total vertical
                                    if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
                                        $ligne_total = '<tr>';
                                    for($j =0 ; $j < $nb_td_vide; $j++){
                                    $ligne_total .='<td></td>';
                                    }
                                    $ligne_total .= '<td>Total</td>';
                                    }
                                    if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                        $ligne .= '<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0].'</td></tr>';
                                    }else{
                                        $ligne .= '</tr>';
                                    }
                                    $htmltable .= $ligne;
                                    for($k = 1; $k < $nb_mesure; $k++){
                                        if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                                            $htmltable .=$ligne_une_mesure[$k].'<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$k].'</td></tr>' ;
                                        }else{
                                            $htmltable .=$ligne_une_mesure[$k].'</tr>' ;
                                        }
                                    }
                                    unset($mes_tot[$i]);
                                }
                            }
                            unset($mes);
                            for ($i= 0; $i<$nb_col_mes; $i++){
                                $mes[] = $this->param["VAL_DONNEES_MANQUANTES"];
                            }
                            $ligne      = '<tr>';
                        }
                    }
                    //..
                } // ajout des regroupements géographiques
                if ($egal_zone == false){
                     // Condition pour la gestion du niveau établissement
                    if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                        for ($i=0; $i<$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                            if ($cpt == 0 ){
                                $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
									$ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                            }else{
                                if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))
										$ligne .= '<td '.$this->param["LARGEUR_COLONNE_ZONE"].'></td>';
                                }else{
                                    $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                    if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))
										$ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                                }
                            }
                            $cpt +=1;
                        }
                        if (isset($dim_ligne)){
                            $temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]]=$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])];
                            $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])].'</td>';
                        }
                    }else{
                         // Ajout gestion au niveau établissement au cas selection des wereda
                        for ($i=0; $i<=$this->aggregation_level["HIERARCHY_LEVEL"]; $i++){
                            if ($cpt == 0 ){
                                $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                $ligne .= '<td width='.$this->param["LARGEUR_COLONNE_ZONE"].'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                            }else{
                                if ($ligne_data[$this->get_champ_extract($cle[$i])] == $temp_zone[$i]){
                                    $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'></td>';
                                }else{
                                    $temp_zone[$i] = $ligne_data[$this->get_champ_extract($cle[$i])];
                                    $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($cle[$i])].'</td>';
                                }
                            }
                            $cpt +=1;
                        }
                        if (isset($dim_ligne)){
                            $temp_zone[$this->aggregation_level["HIERARCHY_LEVEL"]+1]=$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])];
                            $ligne .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE_ZONE"]).'>'.$ligne_data[$this->get_champ_extract($dim_ligne["DIM_LIBELLE"])].'</td>';
                        }
                    }
                }
                $cpt =0;
                // Recupeartion des donnees des mesures
                foreach($this->mesure as $key_mes=>$une_mesure){
                    if (isset($dim_col)){
                        if(isset($dim_imbriq)){
                            foreach($dim as $key=>$une_dim){
                                foreach($dim_imbr as $key_imbr=>$une_dim_imbr){
                                    $ind_courant = ($key*$nb_dimension_imbr*$nb_mesure) +($key_imbr*$nb_mesure) + $key_mes;
                                    if ($ligne_data[$this->get_champ_extract($dim_col["DIM_LIBELLE"])] == $une_dim && $ligne_data[$this->get_champ_extract($dim_imbriq["DIM_LIBELLE"])] == $une_dim_imbr){
                                        switch ($une_mesure["TYPE_MESURE"]){
                                            case 'int' :{
                                                $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                                break;
                                            }
                                            case 'double' :{
                                                $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                                break;
                                            }
                                            case 'text':{
                                                if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"])
                                                    $mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
												else
                                                    $mes[$ind_courant] = '****';
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }else{
                            foreach($dim as $key=>$une_dim){
                                $ind_courant = ($key*$nb_mesure) + $key_mes;
                                if ($ligne_data[$this->get_champ_extract($dim_col["DIM_LIBELLE"])] == $une_dim){
                                    switch ($une_mesure["TYPE_MESURE"]){
                                        case 'int' :{
                                            $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                            break;
                                        }
                                        case 'double' :{
                                            $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                            break;
                                        }
                                        case 'text':{
                                            if ($this->with_etabliss == true || count($this->zones) > $this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                                            	$mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
                                            }else{
                                            	$mes[$ind_courant] = '****';
                                        	}
											break;
                                        }
                                    }
                                }
                            }
                        }
                    }else{
                        foreach($this->mesure as $ind_courant=>$une_mesure){
                            switch ($une_mesure["TYPE_MESURE"]){
                                case 'int' :{
                                    $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],0,',','');
                                    break;
                                }
                                case 'double' :{
                                    $mes[$ind_courant] = number_format($ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])],2,',','');
                                    break;
                                }
                                case 'text':{
                                    $mes[$ind_courant] = $ligne_data[$this->get_champ_extract($une_mesure["LIBELLE_MESURE"])];
                                    break;
                                }
                            }
                        }
                    }
                    $cpt +=1;
                }
            }
                //Derniere ligne
                if (is_array($mes)){
                    $ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
                    $sum_une_mesure[0]=0;
                    if (isset($dim_ligne)){
                         // Condition pour la gestion du niveau établissement
                        if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                            if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +1;
							else
								$nb_td_vide = 1;
                        }else{
                            // Ajout gestion au niveau établissement au cas selection des wereda
                            if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +2;
							else
								$nb_td_vide = 2;
                        }
                    }else{
                         // Condition pour la gestion du niveau établissement
                        if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                            if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"];
							else
								$nb_td_vide = 0;
                        }else{
                            // Ajout gestion au niveau établissement au cas selection des wereda
                            if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6))//Ajout Hebie pour school report IMIS
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"]+1;
							else
								$nb_td_vide = 1;
                        }
                    }
                    // Derniere ligne donnees
                    for($i = 1; $i < $nb_mesure; $i++){
                        $sum_une_mesure[$i] =0;
                        $ligne_une_mesure[$i] = '<tr>';
                        for($j =0 ; $j < $nb_td_vide; $j++){
                            $ligne_une_mesure[$i] .='<td></td>';
                        }
                        $ligne_une_mesure[$i] .='<td>'.$this->mesure[$i]["ENTETE_MESURE"].'</td>';
                    }
                    for($i = 0; $i < count($mes);){
                        if (is_numeric($mes[$i])){
                            $sum_une_mesure[0] += $mes[$i];
                        }
                        $ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i].'</td>';
                        for($j = 1; $j < $nb_mesure; $j++){
                            if (is_numeric($mes[$i+$j])){
                                $sum_une_mesure[$j] += $mes[$i+$j];
                            }
                            $ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mes[$i+$j].'</td>';
                        }
                         $i +=$nb_mesure;
                    }

                     // Condition pour la gestion du niveau établissement
                    if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
                        for($i = $this->aggregation_level["HIERARCHY_LEVEL"]-1;$i>=0;$i--){
                            $mes_tot[$i][] = $mes;
                        }
                    }else{
                        // Ajout gestion au niveau établissement au cas selection des wereda
                        for($i = $this->aggregation_level["HIERARCHY_LEVEL"];$i>=0;$i--){
                            $mes_tot[$i][] = $mes;
                        }

                    }
                }
                // Total horizontal
                if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                    $ligne     .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0].'</td></tr>';
                }else{
                    $ligne     .= '</tr>';
                }
                $htmltable .= $ligne;
                for($i = 1; $i < $nb_mesure; $i++){
                    if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                        $htmltable .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$i].'</td></tr>' ;
                    }else{
                        $htmltable .=$ligne_une_mesure[$i].'</tr>' ;
                    }
                }
                // Total vertical
                if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
                         $ligne_total = '<tr>';
                        for($j =0 ; $j < $nb_td_vide; $j++){
                            $ligne_total .='<td></td>';
                        }
                        $ligne_total .= '<td>Total</td>';
                    for ($i=0; $i < count($mes);$i+=$nb_mesure){
                        $sum_vertical[$i]=0;
                        for ($j=0; $j < $nb_mesure;$j++){
                            $sum_vertical[$i] +=$mes[$i+$j];
                        }
                        $ligne_total .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_vertical[$i].'</td>';
                    }
                    if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
                        $ligne_total .=$ligne_une_mesure[$i].'<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.array_sum($sum_vertical).'</td></tr>' ;
                    }
                    $htmltable.= $ligne_total;
               }

				if(count($this->zones) <= $this->aggregation_level["HIERARCHY_LEVEL_MAX"] || ($_SESSION['type_rpt']<>4 && $_SESSION['type_rpt']<>5 && $_SESSION['type_rpt']<>6)){//Ajout Hebie pour school report IMIS
					unset($sum_une_mesure);
					//Totaux par division administrative
					// Condition pour la gestion du niveau établissement
					if($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
						for($i =  $level_tot ;$i>=$nb_tot_aff;$i--){
							if (isset($dim_ligne)){
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'>Total '.$temp_zone[$i].'</td>';
							}else{
								$ligne = '<tr><td colspan ='.($this->aggregation_level["HIERARCHY_LEVEL"]).'>Total '.$temp_zone[$i].'</td>';
							}
							$sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
							$ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
							$sum_une_mesure[0] = 0;
							if (isset($dim_ligne)){
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +1;
							}else{
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"];
							}
							for($k = 1; $k < $nb_mesure; $k++){
								$sum_une_mesure[$k] = 0;
								$ligne_une_mesure[$k] = '<tr>';
								for($j =0 ; $j < $nb_td_vide; $j++){
									$ligne_une_mesure[$k] .='<td></td>';
								}
								$ligne_une_mesure[$k] .='<td>'.$this->mesure[$k]["ENTETE_MESURE"].'</td>';
							}
							if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
								 $ligne_total = '<tr>';
								for($j =0 ; $j < $nb_td_vide; $j++){
									$ligne_total .='<td></td>';
								}
								$ligne_total .= '<td>Total</td>';
							}
							if (isset($dim_imbriq)){
								$nb_col = ($nb_dimension*$nb_dimension_imbr);
							}else{
								$nb_col =$nb_dimension;
							}
							unset($mesure_gen);
							for($k=0; $k <$nb_col; $k++){
								$mesure_gen[$i] = 0;
							}
							$cpt = 0;
							for($k = 0; $k < count($sum);){
								if (is_numeric($sum[$k])){
									$mesure_gen[$cpt] +=$sum[$k];
									$sum_une_mesure[0] += $sum[$k];
								}else{
								}
								$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k].'</td>';
								for($j = 1; $j < $nb_mesure; $j++){
									if (is_numeric($sum[$k+$j])){
										$mesure_gen[$cpt] +=$sum[$k+$j];
										$sum_une_mesure[$j] += $sum[$k+$j];
									}
									$ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k+$j].'</td>';
								}
								$k +=$nb_mesure;
								$cpt +=1;
							}
							if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
								$ligne .= '<td>'.$sum_une_mesure[0] .'</td></tr>';
							}else{
								$ligne .= '</tr>';
							}
							$htmltable .= $ligne;
							for($k = 1; $k < $nb_mesure; $k++){
								if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
									$htmltable .=$ligne_une_mesure[$k].'<td>'.$sum_une_mesure[$k] .'</td></tr>' ;
								}else{
									$htmltable .=$ligne_une_mesure[$k].'</tr>' ;
								}
							}
							//if ($i == 0){
								$tot_gen = 0;
								for($k=0; $k < $nb_col; $k++){
									$tot_gen += $mesure_gen[$k];
									$ligne_total .='<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mesure_gen[$k].'</td>';
								}
								if ($this->param["AFFICHER_TOTAL_VERTICAL"]==1 && $this->param["AFFICHER_TOTAL_HORIZONTAL"]==1){
									$ligne_total .='<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$tot_gen.'</td>';
								}
								if ($this->param["AFFICHER_TOTAL_VERTICAL"]==1){
									$htmltable .= $ligne_total.'</tr>';
								}
							//}
						}
					}else{
						// Ajout gestion au niveau établissement au cas selection des wereda
						for($i =  $level_tot ;$i>=$nb_tot_aff-$this->zone_pere();$i--){
							if (isset($dim_ligne)){
								$ligne = '<tr><td colspan ="'.($this->aggregation_level["HIERARCHY_LEVEL"]+2).'">Total '.$temp_zone[$i].'</td>';
							}else{
								$ligne = '<tr><td colspan ="'.($this->aggregation_level["HIERARCHY_LEVEL"]+1).'">Total '.$temp_zone[$i].'</td>';
							}
							$sum =$this->sum_colonne_array($mes_tot[$i],count($mes_tot[$i]),$nb_col_mes);
							$ligne .= '<td>'.$this->mesure[0]["ENTETE_MESURE"].'</td>';
							$sum_une_mesure[0] = 0;
							if (isset($dim_ligne)){
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"] +2;
							}else{
								$nb_td_vide = $this->aggregation_level["HIERARCHY_LEVEL"]+1;
							}
							for($k = 1; $k < $nb_mesure; $k++){
								$sum_une_mesure[$k] = 0;
								$ligne_une_mesure[$k] = '<tr>';
								for($j =0 ; $j < $nb_td_vide; $j++){
									$ligne_une_mesure[$k] .='<td></td>';
								}
								$ligne_une_mesure[$k] .='<td>'.$this->mesure[$k]["ENTETE_MESURE"].'</td>';
							}
							if ($this->param["AFFICHER_TOTAL_VERTICAL"] == 1){
								 $ligne_total = '<tr>';
								for($j =0 ; $j < $nb_td_vide; $j++){
									$ligne_total .='<td></td>';
								}
								$ligne_total .= '<td>Total</td>';
							}
							if (isset($dim_imbriq)){
								$nb_col = ($nb_dimension*$nb_dimension_imbr);
							}else{
								$nb_col =$nb_dimension;
							}
							unset($mesure_gen);
							for($k=0; $k <$nb_col; $k++){
								$mesure_gen[$i] = 0;
							}
							$cpt = 0;
							for($k = 0; $k < count($sum);){
								if (is_numeric($sum[$k])){
									$mesure_gen[$cpt] +=$sum[$k];
									$sum_une_mesure[0] += $sum[$k];
								}else{
								}
								$ligne .='<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k].'</td>';
								for($j = 1; $j < $nb_mesure; $j++){
									if (is_numeric($sum[$k+$j])){
										$mesure_gen[$cpt] +=$sum[$k+$j];
										$sum_une_mesure[$j] += $sum[$k+$j];
									}
									$ligne_une_mesure[$j] .= '<td '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum[$k+$j].'</td>';
								}
								$k +=$nb_mesure;
								$cpt +=1;
							}
							if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
								$ligne .= '<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[0] .'</td></tr>';
							}else{
								$ligne .= '</tr>';
							}
							$htmltable .= $ligne;
							for($k = 1; $k < $nb_mesure; $k++){
								if ($this->param["AFFICHER_TOTAL_HORIZONTAL"] == 1){
									$htmltable .=$ligne_une_mesure[$k].'<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$sum_une_mesure[$k] .'</td></tr>' ;
								}else{
									$htmltable .=$ligne_une_mesure[$k].'</tr>' ;
								}
							}
							//if ($i == 0){
								$tot_gen = 0;
								for($k=0; $k < $nb_col; $k++){
									$tot_gen += $mesure_gen[$k];
									$ligne_total .='<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$mesure_gen[$k].'</td>';
								}
								if ($this->param["AFFICHER_TOTAL_VERTICAL"]==1 && $this->param["AFFICHER_TOTAL_HORIZONTAL"]==1){
									$ligne_total .='<td  '.$this->attrib_table('width',$this->param["LARGEUR_COLONNE"]).' align ="'.$this->param["ALIGNEMENT_MESURE"].'"  '.$this->attrib_table('height',$this->param["HAUTEUR_LIGNE"]).' valign = "middle" >'.$tot_gen.'</td>';
								}
								if ($this->param["AFFICHER_TOTAL_VERTICAL"]==1){
									$htmltable .= $ligne_total.'</tr>';
								}
							//}
						}
	
					}
				}
                //..
        /**/
        $htmltable .= '</table>';
        }
        return $htmltable;
    }
    /**
	* Instanciation de la classe pdftable et ajout des différentes chaines
	* html (tableaux) construites plus haut(format_html_1 ou format_html_2 ou
	* entete_etat)
	* DEBUT
	* instanciation de PDFTable
	* Ajout entete
	* Ajout tableau donnees
	*   FIN
	* @access private
	*/
    function create_report(){
        //instanciation de la classe pdftable
        $this->pdf = new PDFTable();
        $this->pdf->Open();

       //entete de page
        $this->pdf->entete_page =$this->param['ENTETE_PAGE'];
        $this->pdf->pied_page =$this->param['PIED_PAGE'];
        $this->pdf->AddPage($this->param['PAGE_ORIENTATION']);

        //titre de l'état
        $this->pdf->SetX(20);
        $this->pdf->SetFont('times','',10);
        $this->pdf->MultiCell(100,5,$this->param['TITRE_ETAT']);

        //entete etat
        if ($this->param["AFFICH_CRITERES"]==1){
        $this->pdf->Ln(1);
        $this->pdf->SetX(20);
        $this->pdf->SetFont('times','',9);
        $this->pdf->htmltable(array("html"=>$this->entete_etat(),"multipage"=>1));
        }
        //donnees
        $this->pdf->Ln(5);
        $this->pdf->SetX(20);
        $this->pdf->SetFont('times','',9);
        if($this->no_data == false){
            // Contrôle de la présence des données
            $this->pdf->htmltable(array("html"=>$this->htmlTable,"multipage"=>2));
        }
    }
    /**
	* Cette méthode permet de recupérer les occurences des dimensions dans
	* la base de données conformément aux données renvoyées par la requête
	* associée à l'état
	* DEBUT
	* Vérification si la table de nomenclature associée
	* à la dimension est liée au système d'enseignement
	* Recupération du contenu de la table de nomenclature
	* Comparaison du recordset renvoyé et du recordset renvoyé par la requête
	* associée à la base de données
	* FIN
	* @param array data Tableau de données renvoyées par la requête associée à
	* l'état
	* @param array dim Tableau regroupant les différents paramètres d'une dimension
	*/
    function extract_occ_dim($data,$une_dim){
        $rsdim = $this->conn->GetAll(str_replace('$id_systeme',$this->id_systeme,$une_dim["DIM_SQL"]));
        $rsdim = $this->conn->GetAll(str_replace('$'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'],$this->id_systeme,$une_dim["DIM_SQL"]));
        $dim_list = array();
		$lib_dim = $this->get_champ_extract($une_dim["DIM_LIBELLE"]);
        if (is_array($data)){
            foreach($data as $ligne_data){
                if (in_array(trim($ligne_data[$lib_dim]),$dim_list) == false){
                    $dim_list[] = trim($ligne_data[$lib_dim]);
                }
            }
        }

        $dim_list_ord = array();
        foreach($rsdim  as $une_rsdim){
            if (in_array(trim($une_rsdim[$lib_dim]),$dim_list) == true){
                $dim_list_ord[] = $une_rsdim[$lib_dim];
            }
        }
        return $dim_list_ord ;
    }
    /**
	* Donne un aperçu de l'état créé au format pdf
	* @access public
	*/
    public function preview_report(){
        //if ($this->no_data == false){
        $this->pdf->Output($this->nomfic.'.pdf','F');
        //}
    }
    /**
    * Activation de la connexion
	*/
    function __wakeup(){
        $this->conn	= $GLOBALS['conn'];
    }
    /**
	* Récupération de la traduction des occurences de la table de
	* nomenclature
	* DEBUT
	* Requete pour recuperer un libellé dans DICO_TRADUCTION
	* Return Libelle
	* FIN
	* l'occurence de nomenclature
	* @param numeric code identifiant de l'occurence de nomenclature
	* @param string langue langue courante de l'application identifiant de
	* @param string table Nom de la table associée à une dimension dans la base de
	* données

	*/
    function recherche_libelle($code,$langue,$table){
        // permet de récupérer le libellé dans la table de traduction
        // en fonction de la langue et de la table  aussi
        $requete 	= "SELECT LIBELLE
                                FROM DICO_TRADUCTION
                                WHERE CODE_NOMENCLATURE=".$code." And CODE_LANGUE='".$langue."'
                                AND NOM_TABLE='".$table."'";
        // Traitement Erreur Cas : GetAll / GetRow
        try {
                $all_res	= $this->conn->GetAll($requete);
                if(!is_array($all_res)){
                        throw new Exception('ERR_SQL');
                }
            return($all_res[0]['LIBELLE']);
        }
        catch(Exception $e){
                $erreur = new erreur_manager($e,$requete);
        }
        // Fin Traitement Erreur Cas : GetAll / GetRow
    }

    /**
	* Calcule la somme d'un tableau par colonne
	* DEBUT
	* POUR i = 0 à nombre de colonnes
	* POUR j = 0 à nombre de lignes
	* Si tableau[j][i] est un nombre
	* 	val	[i]=tableau[j][i]
	* Sinon
	* 	transforme	 tableau[j][i] en numéric si possible
	* FIN SI
	* FIN POUR
	* $sum_def = 0
	* POUR j = 0 à nombre de lignes
	* 	$sum_def	 = val [j]
	* FIN POUR
    * @access private
	* @param array tableau tableau de données
	* @param numeric nb_row Nombre de lignes à sommer
	* @param numeric nb_col Nombre de colonnes du tableau
	*/
    private function sum_colonne_array($tableau,$nb_row, $nb_col){
        for ($i = 0; $i<$nb_col ; $i++){
            $sum[$i][0] = 0;
            $sum[$i][1] = 0;
            for ($j = 0; $j< $nb_row ; $j++){
                if (trim($tableau[$j][$i]) == '-')
                    $val = 0;
                else
                    $val =$tableau[$j][$i];
                //conversion d'une chaine de caractères en nombre
                $add = str_replace(',','.',$val);
                $add = $this->str_to_number($add);
				if (is_numeric($add)){
                    $sum[$i][0] += $add;
                    // comptabilise le nombre de valeurs nulles
                    if ((int)$add == 0) {
                        $sum[$i][1] +=1;
                    }
                }
            }
        }
        //Teste toutes les colonnes pour determiner lesquelles correspondent aux ratios
        $ind_mesure_type = 0;
        unset ($temp_sum);
        for($i=0; $i< count($sum); $i++){
		   //Modif Hebie IMIS
		   if($this->id_type_report == 1){
			   if (trim($this->mesure[$ind_mesure_type]['EXPRESSION_FORMULE']) =='%'){
					// permet d'enlever le nombre de valeurs nulles
					$diviseur =$nb_row - $sum[$i][1];
					if ($diviseur <> 0)
						$sum[$i][0] = $sum[$i][0]/$diviseur;
				}
			}
			//Fin Modif Hebie
            ///controle nombre de mesure atteint et mise à zero si necessaire
            if ($ind_mesure_type <= (count($this->mesure)-1))
                $ind_mesure_type +=1;
            else
                $ind_mesure_type =0;
            //$temp_sum[] = $une_ligne;
        }
        for ($i = 0; $i<$nb_col ; $i++){
            if ($sum[$i][0] == 0) $sum[$i][0] = $this->param["VAL_DONNEES_MANQUANTES"];
        }

        unset ($sum_def);
        if (is_array($sum) ){
            foreach($sum as $une_sum){
                $sum_def[] = $une_sum[0];
            }
        }
        return $sum_def ;
    }
    /**
	* Vérifie s'il existe une dimension ligne associée à l'état
	* DEBUT
	* Parcours de la liste des mesures
	* Si TYPE_DIMENSION=1  alors
	* retourne tableau avec attribut de la mesure
	* FIN
	* @access private
	*/
    function existe_dimension_ligne(){
        $bool = false;
        foreach($this->dimension as $une_dimension){
            if ($une_dimension["TYPE_DIMENSION"]==1 && $bool ==false){
                $bool = true;
            }
        }
        return $bool;
    }
    /**
	* Permet de définir des attributs du tableau html
	* DEBUT
	* chaine_attritbut = nom_attribut+'='+valeur_attribut
	* FIN
	* @access private
	* @param stirng attrib  height,width, align
	* @param numeric val valeur de l'attribut
	*/
    private function attrib_table($attrib, $val){
            switch($param["TYPE_EXPORT_RPT"]){
                case 'pdf' : {
                    $chaineattrib = $attrib.'='.$val;
                    break;
                }
                default :  {
                    $chaineattrib = '';
                    break;
                }
            }
            //echo '<br>'.$chaineattrib.'<br>';
            return $chaineattrib;

    }
    /**
	* Gestion des totaux (limite infér des zones)
	* Si dans le tableau $_SESSION['tab_rpt']['all_zones'] ne contient pas
	* d'éléments alors pas de total
	*  Sinon  Si la zone père est égal à 'pays' alors une seule ligne totale
	* (total pays)
	* Sinon
	* nombre de totaux à calculer = nombre de types de regroupement($this->indic_all_zone)
	* @access private
	*/
     private function affich_tot_aggre(){
        if (count($_SESSION['tab_rpt']['all_zones']) == 0){
            $nb_tot_aff = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
        }else{
            if (trim($_SESSION['tab_rpt']['zone_pere'])=="pays" or trim($_SESSION['tab_rpt']["tab_regs"][0]["code_reg"])==trim($_SESSION['tab_rpt']['zone_pere'])){
                $nb_tot_aff=0;
            }else{
                $nb_tot_aff = $this->indic_all_zone;
            }
        }
        return $nb_tot_aff;
    }
    /**
    *
    * Teste  si la zone sélectionner comporte des sous-divisions ou pas
    * Si la zone père est définie et le type de la zone père >1 alors
    * 	return 1
    * sinon
    * 	return 0
	* @access private
	*/
    private function zone_pere(){
        if (isset($_SESSION['tab_rpt']['zone_pere']) and $_SESSION['tab_rpt']['type_zone_pere']>1){
                 $ret =  1;
            }else{
                $ret =  0;
            }
        return $ret;
    }
    /**
	* Retourne la dimension ligne s'il en existe
	* DEBUT
	* Faire jusqu'à fin liste des dimensions
	* {
	* 	Si	 type_dimension = 1 alors
	* 		return dimension
	* }
	*  FIN
	* @access private
	*/
    private function is_dimension_ligne(){
        foreach($this->dimension as $dimension){
            if ($dimension['TYPE_DIMENSION'] == 1){
                return $dimension;
            }
        }
    }
	/**
	* Retourne la dimension ligne en provenance d'une table de données s'il en existe
	* DEBUT
	* Faire jusqu'à fin liste des dimensions données
	* {
	* 	Si	 type_dimension = 1 alors
	* 		return dimension
	* }
	*  FIN
	* @access private
	*/
    private function is_dimension_line_data(){
        //echo "<pre>";
		//print_r($this->dimension_line_data);
		foreach($this->dimension_line_data as $dimension){
            if ($dimension['TYPE_DIMENSION'] == 1){
                return $dimension;
            }
        }
    }
    /**
	*
	* Retourne la dimension colonne s'il en existe
	* DEBUT
	* Faire jusqu'à fin liste des dimensions
	* {
	* 	Si		 type_dimension = 2 alors
	* 		return	 dimension
	* }
	* FIN
	* @access private
	*/
    private function is_dimension_col(){
        foreach($this->dimension as $dimension){
            if ($dimension['TYPE_DIMENSION'] == 2){
                return $dimension;
            }
        }
    }
    /**
	* Retourne la dimension imbriquée s'il en existe
	* DEBUT
	* Faire jusqu'à fin liste des dimensions
	* {
	* 	Si	 type_dimension = 3 alors
	* 		return	 dimension
	* }
	* FIN
	* @access private
	*/
    private function is_dimension_imbriquee(){
        foreach($this->dimension as $dimension){
            if ($dimension['TYPE_DIMENSION'] == 3){
                return $dimension;
            }
        }
    }
    /**
	*
	* Retourne la borne supérieure de la boucle qui permet de calculer les
	* totaux par division administratives
	* la borne supérieure est définie selon le niveau d'agrégation  est
	* différent du niveau max alors
	* @access private
	*/
    private function born_sup_tot(){
        $dim_ligne = $this->is_dimension_ligne();
        if ($this->aggregation_level["HIERARCHY_LEVEL"]!=$this->aggregation_level["HIERARCHY_LEVEL_MAX"]){
            if ($this->aggregation_level["HIERARCHY_LEVEL"] == 1){
                $borne = $this->aggregation_level["HIERARCHY_LEVEL"] - 2;
                if ( isset($dim_ligne)){
                    $borne = $borne + 1;
                }
            }else{
                if (count($_SESSION['tab_rpt']['all_zones']) == 0){
                    $borne = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
                    if (!isset($dim_ligne)){
                    $borne = $borne - 1;
                }
                }else{
                    $borne = $this->aggregation_level["HIERARCHY_LEVEL"]-1;
                    if (isset($dim_ligne)){
                        $borne = $borne + 1;
                    }
                }
            }
        }else{
            if ($this->aggregation_level["HIERARCHY_LEVEL"] == 1){
                $borne = $this->aggregation_level["HIERARCHY_LEVEL"] - 1;
                if ( isset($dim_ligne)){
                    $borne = $borne + 1;
                }
            }else{
                if (count($_SESSION['tab_rpt']['all_zones']) == 0){
                    $borne = $this->aggregation_level["HIERARCHY_LEVEL"]-0;
                    if (!isset($dim_ligne)){
                    $borne = $borne - 1;
                }
                }else{
                    $borne = $this->aggregation_level["HIERARCHY_LEVEL"]-0;
                    if (!isset($dim_ligne)){
                        $borne = $borne - 1;
                    }
                }
            }
        }
        if($this->with_etabliss == false && $this->with_school == false){
            $borne = $borne - 1;
        }
        return $borne;
    }
    /**
	*
	* Retourne la borne inférieure de la boucle qui permet de calculer les
	* totaux par division administratives
	* Si le niveau hierarchique est le dernier niveau de la chaine alors
	* borne_inf = 0
	* sinon
	* si ne tableau de zones est vide alors borne = nombre de type de
	* regroupement-1
	* sinon
	* 	borne = type_zone_pere-1
	* @access private
	*/
    private function born_inf_tot(){
        if ($this->aggregation_level["HIERARCHY_LEVEL"] == 1){
            $borne = 0;
        }else{
            if (count($_SESSION['tab_rpt']['all_zones']) == 0){
                $borne = $_SESSION['tab_rpt']['type_reg']-1;
            }else{
                $borne =$_SESSION['tab_rpt']['type_zone_pere']-1;
                if($borne < 0){
                    $borne = 0;
                }
            }
        }
        return $borne;
    }
    /**
	* Transforme une chaine de caractères en nombre
	* @access private
	* @param  sting str chaine de caractères à formater
	*/
    function str_to_number($str){
        if($this->langue=='eng'){//Todo:ameliorer/dynamiser
			$tab_num = explode(' ',$str);
			$number = '';
			foreach($tab_num as $un_tab_num){
				$number .= $un_tab_num;
			}
			$number = str_replace(',','.',$number);
			return $number;
		}else{
			return $str;
		}
    }
}
