<?php class arbre {

    public $chaine;
    public $init_depht;
    public $result;
    
    // Ajout Alassane
    public $type_access;
		
	// Ajout Bass : pour les modifs rattachements
	public $vars_modif_atlas = array();

    public function __construct($code_chaine) {

        $this->conn = $GLOBALS['conn'];
		$this->chaine = $this->build_chaine($code_chaine);
		$this->nbtyperegroup = count($this->chaine);
		$this->init();

    }
		
		function __wakeup(){
			$this->conn	= $GLOBALS['conn'];
		}
    
		public function build_chaine($code_chaine) {
			$chaine_fille = array();
		
        if(count($chaine_fille) == 0) {

            $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', T_R.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
                            FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C,'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'  AS T_R
                            WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$code_chaine.'
                            ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';

            //$result = $this->conn->CacheGetAll($requete);
            $result = $this->conn->GetAll($requete);

            for($i=0;$i<(count($result));$i++) {

                $result[$i][$GLOBALS['PARAM']['NIVEAU_CHAINE']] = $i;

                $result[$i]['NIVEAU_CHAINE_SUIVANT'] = array();

                if(isset($result[($i+1)])) {

                    $result[$i]['NIVEAU_CHAINE_SUIVANT'] = array(($i+1));

                }

            }

            return $result;

        } else {

            $result = array();
            $temp = array();
            $chaines = array();
            if (is_array($chaine_fille )){
                foreach($chaine_fille as $cf) {
    
                    $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', T_R.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
                                    FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C, '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS T_R
                                    WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$cf[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']].'
                                    ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';
                    //$temp = $this->conn->CacheGetAll($requete);
                    $temp = $this->conn->GetAll($requete);
    
                    for($i=0;$i<count($temp);$i++) {
    
                        $temp[$i][$GLOBALS['PARAM']['NIVEAU_CHAINE']] = $i;
    
                    }
    
                    $result = array_merge($result, $temp);
    
                }
            }

            uasort($result, array('arbre', 'order_by_niveau_chaine'));

            $temp = $result;
            $result = array();
            $codes_type_reg = array();

            foreach($temp as $t) {

                if(!in_array($t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']], $codes_type_reg)) {

                    array_push($codes_type_reg, $t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]);
                    array_push($result, $t);

                }

            }

            foreach($result as $i1=>$r1) {

                $result[$i1]['NIVEAU_CHAINE_SUIVANT'] = array();

                foreach($result as $i2=>$r2) {

                    if($r2[$GLOBALS['PARAM']['NIVEAU_CHAINE']] == ($r1[$GLOBALS['PARAM']['NIVEAU_CHAINE']]+1)) {

                        array_push($result[$i1]['NIVEAU_CHAINE_SUIVANT'], $i2);

                    }

                }

            }

            return $result;

        }

    }

    public function order_by_niveau_chaine($a, $b) {

        if($a[$GLOBALS['PARAM']['NIVEAU_CHAINE']] > $b[$GLOBALS['PARAM']['NIVEAU_CHAINE']]) {

            return 1;

        }

        if($b[$GLOBALS['PARAM']['NIVEAU_CHAINE']] > $a[$GLOBALS['PARAM']['NIVEAU_CHAINE']]) {

            return -1;

        }

        return 0;

    }

    public function init($depht=0) {

        $this->init_depht = $depht;

    }

    public function create_entete($init_depht=0, $final_depht=false, $combo_br=false) {

        $result = array();

        $result['html'] = '';
        $result['coderegroup'] = 0;

        if(!$final_depht) {

            $final_depht = $this->init_depht - 1;

        }

        //on initialise $code_regroups à vide
        $code_regroups = array();
        $combo_regroups = array();
        $combo_regroups_js = array();

        //on boucle entre 0 et prev_depht 
        
        // modification apportée par Alssane 
            if($this->type_access=='config' && $final_depht>0){
                    $final_depht    =   $final_depht-1;
            }
        // fin de la modification apportée alssane        
        for($i=$init_depht; $i<=$final_depht; $i++) {

            //si $i est à 0 c'est qu'on doit trouver le premier niveau
            if($i == $init_depht) {

                //on trouve le type courant de regoupement
                $curcodetyperegroup = $this->chaine[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                //on construit la requête
                if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].
								' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].
								';';
				}else{
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].
								' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.
								' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')'.
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].
								';';
				}
                //on récupère la premiere ligne du résultat
                $regroupement = $this->conn->execute($requete);
                
				if(isset($_GET['code_regroup_' . $i])) {

                    $code_regroups[$i] = $_GET['code_regroup_' . $i];
					$_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_GET['code_regroup_' . $i];

                } elseif(isset($_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i];
				
				} elseif(isset($_SESSION['reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['reg_parents'][$curcodetyperegroup][$i]<>''){

					$code_regroups[$i] = $_SESSION['reg_parents'][$curcodetyperegroup][$i];
				
				}
				else{

                    $code_regroups[$i] = $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];

                }

                $j = 0;

                $combo_regroups[$i] = '<select name="combo_regroup_'.$i.'" id="combo_regroup_'.$i.'" onchange="toggle_combo_'.$i.'();">' . "\n";
								$this->vars_modif_atlas['options_combo'] = '';
								$this->vars_modif_atlas['link_from'] = $code_regroups[$i];
                //on boucle sur le résultat
                while(!$regroupement->EOF) {

                    $sel = '';

                    if($regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] == $code_regroups[$i]) {

                        $sel = ' selected';
                        //Ajout yacine
                        $_GET['code_regroup_' . $i] =$code_regroups[$i];
						$_SESSION['reg_parents'][$curcodetyperegroup][$i] = $code_regroups[$i];

                    }
										
                    $combo_regroups[$i] .= '<option value="' . $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regroupement->fields[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";
					$this->vars_modif_atlas['options_combo'] .= '<option value="' . $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regroupement->fields[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";
										
                    $regroupement->MoveNext();
                    $j++;

                }

                // Modif Alassane
                $combo_regroups[$i] .= '</select><br />' . "\n";
                // Fin Modif Alassane
                //$combo_regroups[$i] .= '</select>. "\n";

            } else {

                //on trouve le type courant de regoupement
                $curcodetyperegroup = $this->chaine[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                //puis on fabrique la requête à partir du code regoupement et du type de regoupement
                if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete =  'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroups[($i-1)].''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								' ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].
								';';
				}else{
					$requete =  'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroups[($i-1)].''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')'.
								' ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].
								';';
				}

                //echo '<br> sql ='.$requete;
                //on exécute la requête
                $regroupement = $this->conn->execute($requete);

				if(isset($_GET['code_regroup_' . $i])) {

                    $code_regroups[$i] = $_GET['code_regroup_' . $i];
					$_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_GET['code_regroup_' . $i];

                }elseif(isset($_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i];

				}elseif(isset($_SESSION['reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['reg_parents'][$curcodetyperegroup][$i];

				}
				else{

                    $code_regroups[$i] = $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];

                }

                $j = 0;
                $combo_regroups[$i] = '<select name="combo_regroup_'.$i.'" id="combo_regroup_'.$i.'" onchange="toggle_combo_'.$i.'();">' . "\n";
							  $this->vars_modif_atlas['options_combo']	= '';
								$this->vars_modif_atlas['link_from'] 			= $code_regroups[$i];

                //on boucle sur le résultat
                while(!$regroupement->EOF) {

                    $sel = '';

                    if($regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] == $code_regroups[$i]) {

                        $sel = ' selected';
                        //Ajout yacine
                        $_GET['code_regroup_' . $i] =$code_regroups[$i];
						$_SESSION['reg_parents'][$curcodetyperegroup][$i] = $code_regroups[$i];
                    }

                    $combo_regroups[$i] .= '<option value="' . $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regroupement->fields[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";
					$this->vars_modif_atlas['options_combo'] .= '<option value="' . $regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regroupement->fields[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";
													
                    $regroupement->MoveNext();
                    $j++;

                }
                // Modif Alassane
                $combo_regroups[$i] .= '</select><br />' . "\n";
                // Fin Modif Alassane
                //$combo_regroups[$i] .= '</select>' . "\n";

            }

        }

        $result['code_regroup'] = $code_regroups[$final_depht];
        
        $result['html'] .= "\n";

        $result['html'] .= '<script type="text/Javascript">' . "\n";

        foreach($code_regroups as $i=>$code_regroup) {

            $result['html'] .= "\n";

            $result['html'] .= 'function toggle_combo_'.$i.'() {' . "\n";

            $result['html'] .= "\n";

            $vars = '';

            for($j=0; $j<=$i; $j++) {
				$result['html'] .= "\t".'if (document.combo_regroups)  ' . "\n";
                $result['html'] .= "\t".'var combo_'.$j.' = document.combo_regroups.combo_regroup_'.$j . ';' . "\n";
				$result['html'] .= "\t".'else if (document.Formulaire) ' . "\n";
                $result['html'] .= "\t".'var combo_'.$j.' = document.Formulaire.combo_regroup_'.$j . ';' . "\n";
                $result['html'] .= "\t".'combo_'.$j.'_val = combo_'.$j.'.options[combo_'.$j.'.selectedIndex].value;' . "\n";
                //$result['html'] .='alert(combo_'.$j. '_val);' . "\n";

                $vars .= 'code_regroup_' . $j . '=\'+combo_'.$j.'_val';

                if($j<$i) {

                    $vars .= '+\'&';

                }

            }

            $curpage = $_SERVER['PHP_SELF'];

            $gets = '';
            foreach($_GET as $i=>$g) {

                $catch = false;

                if(!preg_match('`^code_regroup_`', $i)) {

                    $catch = true;

                    // Quand on change de regroupent, on doit se positionner sur ses premiers fils
                    if(trim($i)=='debut') $g = 0;
                    
                    $gets .= $i . '=' . $g;

                }

                if($catch && $i<(count($_GET)-1)) {

                    $gets .= '&';

                }

            }

            $result['html'] .= "\t".'document.location.href = \''.$curpage.'?' . $gets . $vars . ';' . "\n";

            $result['html'] .= "\n";

            $result['html'] .= '}' . "\n";

            $result['html'] .= "\n";

        }

        $result['html'] .= '</script>' . "\n";

        $result['html'] .= "\n";

        $result['html'] .= '<div align="center">' . "\n";
        $result['html'] .= '<form name="combo_regroups">' . "\n";

        foreach($combo_regroups as $combo_regroup) {

            $result['html'] .= $combo_regroup . "\n";

            if($combo_br) {

                $result['html'] .= '<br>'."\n";

            }

        }

        $result['html'] .= '</form>' . "\n";
        $result['html'] .= '</div>' . "\n";
        //echo '<br> lien ='.$result['html'].'<br>';
        return $result;

    }

    public function build($link, $target='', $depht=0, $code_regroupement=false) {

        if($depht == 0) {

            $depht = $this->init_depht;

        }
		//on initialise le résultat
        $result = '';

        //si la fonction init a été utilisée on a besoin de trouver le code regroupement courant
        if($depht == $this->init_depht && $this->init_depht != 0) {

            $entete = $this->create_entete();

            $code_regroupement = $entete['code_regroup'];
            $result .= $entete['html'];

        }

        //on initialise l'indentation
        $depht_space = '';

        //on définit l'identation à partir de la profondeur de la chaine courante
        for($i=0;$i<$this->chaine[$depht][$GLOBALS['PARAM']['NIVEAU_CHAINE']];$i++) {

            $depht_space .= "\t";

        }

        //s'il y'a un code regroupement on va chercher les regoupements de ce code
        if($code_regroupement) {

            //si le niveau atteint de cette chaine existe <= normalement devrait toujours être le cas
            if(isset($this->chaine[$depht])) {

                //on trouve le type courant de regoupement
                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                //puis on fabrique la requête à partir du code regoupement et du type de regoupement
				if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.'
								ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].' ; ';
				}else{
					$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')
									 ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].' ; ';
				}
				//echo $requete ;
                //on récupère les regoupements
                //$regroupements = $this->conn->CacheGetAll($requete);
                $regroupements = $this->conn->GetAll($requete);

            //si le niveau atteint de cette chaine d'nexiste pas
            } else {

                //on initialise les regoupements à vide
                $regroupements = array();

            }

        //il n'y a pas de code de regoupement on est donc au début de la création de l'arbre
        } else {

            //si le niveau atteint de cette chaine existe <= normalement devrait toujours être le cas
            if(isset($this->chaine[$depht])) {

                //on trouve le type courant de regoupement
                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                //puis on fabrique la requête à partir du type de regoupement
                if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
				}else{
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].') ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
				}
                //on récupère les regoupements
                //$regroupements = $this->conn->CacheGetAll($requete);
                $regroupements = $this->conn->GetAll($requete);

            //si le niveau atteint de cette chaine d'nexiste pas
            } else {

                //on initialise les regoupements à vide
                $regroupements = array();

            }

        }

        //si la profondeur est à 0 on est donc au début de la création de l'arbre
        if($depht == $this->init_depht) {

            $result .= $depht_space . '<ol class="menu">' . "\n";
            $result .= $depht_space . '<li>' . '<img src="'.$GLOBALS['SISED_URL_IMG'].'arbre/root.gif" onclick="expand(this)" />' . trim($_SESSION['NOM_PAYS']) . '</li>' . "\n";

        }

        //on boucle sur les regroupements
        foreach($regroupements as $r) {

            //s'il n'y a pas de niveau suivant on affiche simplement le lien
            if(count($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT']) == 0) {

                //on commence la liste courante
                $result .= $depht_space . '<li>' . "\n";

                //on met le libellé de ce regroupement
                $curlink = preg_replace(array('`\$code_regroup`', '`\$nom_regroup`'), array($r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], 'C'.$depht), $link);

                if($target != '') {

                    $curtarget = ' target="'.$target.'"';

                } else {

                    $curtarget = '';

                }

                //on met le libellé de ce regroupement
				if(!in_array($r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],$_SESSION['fixe_reg_parents_code']) || (isset($_SESSION['fixe_regs'][0]) && $_SESSION['fixe_regs'][0]<> ''))
					$result .= $depht_space . '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";
				else
					$result .= $depht_space . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . "\n";

                //on finit la liste courante
                $result .= $depht_space . '</li>' . "\n";

            } else {

                //on commence la liste courante
                $result .= $depht_space . '<li>' . "\n";

                $haschilds = false;

                //on boucle sur les niveaux suivants afin de savoir s'il y'a des enfants
                foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {

                    if($this->haschilds($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']])) {
    
                        $haschilds = true;
                        break;
    
                    }

                }

                $curlink = preg_replace(array('`\$code_regroup`', '`\$nom_regroup`'), array($r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], 'C'.$depht), $link);

                if($target != '') {

                    $curtarget = ' target="'.$target.'"';

                } else {

                    $curtarget = '';

                }

                //si il y a des enfants
                if($haschilds) {
                    //on met l'image pour développer l'arborescence puis le libellé de ce regoupement
					$result .= $depht_space . '<img src="'.$GLOBALS['SISED_URL_IMG'].'arbre/plus.gif" onclick="expand(this)" />';
                    if(!in_array($r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],$_SESSION['fixe_reg_parents_code']))
						$result .= '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";
                    else
						$result .= $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . "\n";
					$result .= $depht_space . '<ol>' . "\n";

                //si il n'y a pas d'enfants
                } else {

                    //on met le libellé de ce regroupement
                    if(!in_array($r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],$_SESSION['fixe_reg_parents_code']))
						$result .= '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";
					else
						$result .= $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . "\n";

                }

                //on boucle sur les niveaux suivants afin de savoir s'il y'a des enfants
                foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $ncs) {

                    if($this->haschilds($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']])) {
    
                        $result .= $this->build($link, $target, $ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
    
                    }

                }

                //si il y a des enfants
                if($haschilds) {

                    $result .= $depht_space . '</ol>' . "\n";

                }

                //on finit la liste courante
                $result .= $depht_space . '</li>' . "\n";

            }

        }

        //si la profondeur est à 0 on est donc au début de la création de l'arbre
        if($depht == $this->init_depht) {

            $result .= $depht_space . '</ol>' . "\n";

        }

        //on retourne le résultat
        return $result;

    }
		
		public function get_depht_regroup($code_regroup){
				$depht = 1;
				
				$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$code_regroup;
				$type_reg = $this->conn->GetOne($requete);
				
				if(is_array($this->chaine)){
						foreach($this->chaine as $i => $c){
								if($c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $type_reg ){
										$depht = $i;
										break;
								}
						}
				}
				return $depht;
		}
		
    public function haschilds($depht, $code_regroupement) {

        $result = false;

        if(isset($this->chaine[$depht])) {

            $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
    		if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
				$requete =  'SELECT COUNT(FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS NB FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
							' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
							' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.';';
			}else{
				$requete =  'SELECT COUNT(FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].') AS NB FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
							' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
							' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
							' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].');';
			}
            //$regroupements = $this->conn->CacheGetAll($requete);
            $regroupements = $this->conn->GetAll($requete);

            if((int)$regroupements[0]['NB'] > 0) {
    
                $result = true;
    
            }

        }

        return $result;

    }

    public function count_nodes($depht=0, $code_regroupement=false) {

        $result = 0;

        if($code_regroupement) {

            if(isset($this->chaine[$depht])) {

                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.';';
				}else{
					$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
								   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].');';
				}
                //$regroupements = $this->conn->CacheGetAll($requete);
                $regroupements = $this->conn->GetAll($requete);

            } else {

                $regroupements = array();

            }

        } else {

            if(isset($this->chaine[$depht])) {

                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
				if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
                	$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.';';
				}else{
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].');';
				}
                //$regroupements = $this->conn->CacheGetAll($requete);
                $regroupements = $this->conn->GetAll($requete);

            } else {

                $regroupements = array();

            }

        }

        foreach($regroupements as $r) {

            if(count($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT']) == 0) {

                $result++;

            } else {

                $result++;

                foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $ncs) {

                    if($this->haschilds($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']])) {
    
                        $result += $this->count_nodes($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
    
                    }

                }

            }

        }

        return $result;

    }

    public function getchildsid($depht, $code_regroupement, $id_chaine, $recur=false) {

        $result = array();

        $haschilds = false;

        //on boucle sur les niveaux suivants afin de savoir s'il y'a des enfants
        if (is_array($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'])){
            foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {
    
                if($this->haschilds($ncs, $code_regroupement)) {
        
                    $haschilds = true;
                    break;
        
                }
    
            }
        }

        if(!$haschilds) {

            $result = array(0=>array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']=>$code_regroupement));
			if(isset($_SESSION['list_code_reg_last']) && !in_array($code_regroupement,$_SESSION['list_code_reg_last']))	$_SESSION['list_code_reg_last'][] = $code_regroupement;

        } else {

            if(isset($this->chaine[$depht])) {

                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                $requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L , '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'.
                                   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
                                   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
                                   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
               					   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].
								   ' AND  H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine.';';
				//$result = $this->conn->CacheGetAll($requete);
                $result = $this->conn->GetAll($requete);
                //$tab_reg = array();
                foreach($result as $r) {
    
                    foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {
        
                        $result = array_merge($this->getchildsid($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $id_chaine, true), $result); 
    
                        //$tab_reg [] = $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] ;
                    }
        
                }

            }

        }
        //echo'<pre>';
        //print_r( $result );
        return $result;

    }

    public function getparentsid($depht, $code_regroupement, $id_chaine, $recur=false) {

        $result = array();

        if(isset($this->chaine[$depht])) {

            $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

            $requete = 'SELECT PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', PERE.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L , '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'.
                                   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
                                   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
                                   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
                                   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
               					   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].
								   ' AND  H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine.';';
            //$dbresult = $this->conn->CacheGetAll($requete);
            $dbresult = $this->conn->GetAll($requete);

            foreach($this->chaine as $ncp=>$c) {

                if(in_array($depht, $c['NIVEAU_CHAINE_SUIVANT'])) {

                    $result = array_merge($this->getparentsid($ncp, $dbresult[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $id_chaine, true), $dbresult);
                    break;

                }
    
            }

            if(!$recur) {

                $requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].
                                   ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
                                   ' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.';';

                //$dbresult = $this->conn->CacheGetAll($requete);
                $dbresult = $this->conn->GetAll($requete);

                $result = array_merge($result, $dbresult);

            }

        }

        return $result;

    }
	
	public function getregwithparentid($depht, $code_regroupement) {

        $result = array();

		$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS id, FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS nom, FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS type, PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS parentid FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
							   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
							   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].''.
							   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.';';
		
		$dbresult = $this->conn->GetAll($requete);
		
		if (count($dbresult) == 0) {
				$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS id, FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS nom, FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS type, -1 AS parentid FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS'.
						' WHERE  FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.';';
			
			$dbresult = $this->conn->GetAll($requete);
		}
		
		$result = $dbresult[0];

        return $result;

    }

    /**
     * Fonctions d'administration de l'arbre
     *
     */
    
    public function get_typeregs() {
    
        $requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'<255 ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].';';
        //return $this->conn->CacheGetAll($requete);
        return $this->conn->GetAll($requete);
    
    }
	function get_type_reg ($code_reg){
		$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS TYPE_REG 
					FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
					WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$code_reg;            
		
		return($this->conn->GetOne($requete));
	}
	
	function get_type_reg_data ($code_type_reg) {
		$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS id, '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS nom, '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS ordre FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$code_type_reg.';';            
		
		return($this->conn->GetAll($requete));
	}
	
	function get_regs_having_type ($type_reg){
		if(!in_array($type_reg,$_SESSION['fixe_type_regs'])){
			$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$type_reg.
						' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
		}else{
			$requete='  SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS CODE_REG 
						FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' 
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.$type_reg.
						' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$type_reg].')'.
						' ORDER BY '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['CHAMP_ORDRE'];
		}          
			
		return($this->conn->GetAll($requete));
	}
	
    public function add_typereg($libelle, $ordre) {
    
        $newcodetypereg = 0;
    
        foreach($this->get_typeregs() as $t) {
    
            if($t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] > $newcodetypereg && $t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] < 255) {
    
                $newcodetypereg = $t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
    
            }
    
        }
    
        $requete = 'INSERT INTO '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' ('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['ORDRE_TYPE_REGROUPEMENT'].') VALUES ('.(int)$newcodetypereg.', '.$this->conn->qstr($libelle).', '.(int)$ordre.');';
        return $this->conn->Execute($requete);
    
    }
    
    public function mod_typereg($id, $libelle, $ordre) {
    
        $requete = 'UPDATE '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' SET '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$this->conn->qstr($libelle).', '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$ordre.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$id.';';
        return $this->conn->Execute($requete);
    
    }
    
    public function del_typereg($id) {

        //vérification d'existance dans une chaine

        $requete = 'SELECT COUNT('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].') AS COUNT FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$id.';';
        //$count = $this->conn->CacheGetAll($requete);
        $count = $this->conn->GetAll($requete);

        if(isset($count[0]) && $count[0]['COUNT'] > 0) {

            return false;

        } else {

            $requete = 'DELETE FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$id.';';
            return $this->conn->Execute($requete);

        }
    
    }

    public function get_chaines_simples() {

		$requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.(int)$_SESSION['secteur'].' ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].';';	
        return $this->conn->GetAll($requete);
    
    }

    public function get_chaine_unused_typeregs() {

        $result = array();

        $typeregs = $this->get_typeregs();
        foreach($typeregs as $t) {

            $unused = true;

            foreach($this->chaine as $c) {

                if($t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] == $c[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']]) {

                    $unused = false;
                    break;

                }

            }

            if($unused) {

                array_push($result, $t);

            }

        }

        return $result;

    }

    public function save_chaine_typeregs($list) {

        $result = false;

        //règle de gestion de cohérence
        $coherence = true;

        //mise à jour des données
        if($coherence) {

            $j = count($list);
    
            foreach($list as $i=>$l) {
    
                $requete = 'UPDATE '.$GLOBALS['PARAM']['HIERARCHIE'].' SET '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' = '.(int)$j.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$i.';';
                $this->conn->Execute($requete);
    
                $j--;
    
            }

            $result = true;

        }

        $this->chaine = $this->build_chaine($_SESSION['chaine']);

        return $result;

    }

    public function add_chaine_typereg($id) {

        $result = false;

        //règle de gestion de cohérence
        $coherence = true;

        //mise à jour des données
        if($coherence) {

            $requete = 'UPDATE '.$GLOBALS['PARAM']['HIERARCHIE'].' SET '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' = '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'+1 WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].';';
            $this->conn->Execute($requete);

            $requete = 'INSERT INTO '.$GLOBALS['PARAM']['HIERARCHIE'].' ('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].') VALUES ('.(int)$_SESSION['chaine'].', '.$id.', 1);';
            $this->conn->Execute($requete);
            //echo $requete ;
            $result = true;

        }

        $this->chaine = $this->build_chaine($_SESSION['chaine']);

        return $result;

    }

    public function del_chaine_typereg($id) {

        $result = false;

        //règle de gestion de cohérence
        $coherence = true;

        //mise à jour des données
        if($coherence) {

            $requete = 'DELETE FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$id.';';
            $this->conn->Execute($requete);

            $requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].' ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';
            //$hierarchie = $this->conn->CacheGetAll($requete);
            $hierarchie = $this->conn->GetAll($requete);

			if (is_array($hierarchie)){
				$j = count($hierarchie);
				foreach($hierarchie as $h) {
					$requete = 'UPDATE '.$GLOBALS['PARAM']['HIERARCHIE'].' SET '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' = '.(int)$j.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$h[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].';';
					$this->conn->Execute($requete);
					$j--;

				}
			}

            $result = true;

        }

        $this->chaine = $this->build_chaine($_SESSION['chaine']);

        return $result;

    }

	// AJOUT POUR MOBILE
	function get_all_hierarchie_etabs( $from_list_etab,  $types_hierar ){
		$types_etabs = array();
		$pos_types_hierar = array();
		
		$fils_de_type = array();
		$est_un_fils = array();
		$return = array() ;
		
		$champ_type_hierarch = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'] ;
		
		foreach( $from_list_etab as $ord_etab => $etab ){
			$types_etabs[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] = $etab[$champ_type_hierarch] ;
		}
		
		foreach( $types_hierar as $i_type_fils => $type_fils ){
			$pos_types_hierar[$type_fils] = $i_type_fils ;
		}
		
		foreach( $from_list_etab as $ord_etab => $etab ){
			if( $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']] <> '' ){
				$type_etab_fils = $etab[$champ_type_hierarch] ;
				$type_etab_pere = $types_etabs[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']]] ;
				
				$pos_type_etab = $pos_types_hierar[$type_etab_fils] ;
				
				if( ($pos_types_hierar[$type_etab_fils] - $pos_types_hierar[$type_etab_pere]) == 1 ){
					$tabul = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' ;
					$decal = $tabul ;
					if( $pos_types_hierar[$type_etab_fils] > 1 ){
						$decal = '' ;
						for( $i = 1 ; $i <= $pos_types_hierar[$type_etab_fils] ; $i++ ){
							$decal .= $tabul ;
						}
					}
					$etab['ETAB_TABUL_HIERARCHIE'] = $decal ;
					$fils_de_type[$pos_type_etab][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT']]][] = $etab ;
					$est_un_fils[$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] = 'oui' ; 
					
				}
			}
		}
		$return['fils_de_type'] = $fils_de_type ;
		$return['est_un_fils'] 	= $est_un_fils ;
		return($return);
	}
	
	function get_hierarchie_etabs( $from_list_etab,  $types_hierar ) {
		
		$champ_type_hierarch = $GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'] ;
		$etab_traites 	= array();
		$new_list_etab	= array();
		
		$tab_hierar = get_all_hierarchie_etabs( $from_list_etab,  $types_hierar ) ;
		//echo'<pre>';
		//print_r($tab_hierar);
		
		for( $i_type_fils = 1 ; $i_type_fils < count($types_hierar) ; $i_type_fils++ ){
			foreach( $from_list_etab as $ord_etab => $etab ){
				if( !_est_ds_tableau($etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']], $etab_traites)){
					//$pos_type_etab = $pos_types_hierar[$etab[$champ_type_hierarch]] ;
					$curr_fils_etab = $tab_hierar['fils_de_type'][$i_type_fils][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] ;
					$est_un_fils 	= $tab_hierar['est_un_fils'][$etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']]] ;
					if( is_array($curr_fils_etab) && count($curr_fils_etab) ){
						//die('ICI');
						$new_list_etab[] = $etab ;
						$etab_traites[] = $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
						foreach( $curr_fils_etab as $ord_etab_fils => $etab_fils ){
							$new_list_etab[] = $etab_fils ;
							$etab_traites[] = $etab_fils[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
						}
					}else if( $est_un_fils == 'oui' ){
						//continue;
					}else{
						$new_list_etab[] = $etab ;
						$etab_traites[] = $etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']] ;
					}
				}
			}
		}
		return($new_list_etab);
	}
	
	
	function get_list_etabs($typre_reg, $code_reg, $code_sys, $id_chaine) {

		$db = $GLOBALS['conn'];
		
		// Recherche des regroupements sélectionnés par click dans l'arbre
		$list_code_reg = array();
		$list_nom_reg = array();
		
		$liste_etab = array();
			
			
		//création de la liste des coderegs enfants
		$list_code_reg = $this->getchildsid($typre_reg, $code_reg, $id_chaine);
		 // Modif Bass depuis Niger
		$crit_in = array();
		foreach($list_code_reg as $i_code_reg => $code_reg){
			if( !in_array( $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $crit_in) ){
				$crit_in[] = $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] ;
			}
		}
		
		$where = 'E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ( '.implode(', ',$crit_in).' )';
		// Fin Modif Bass depuis Niger
		$with_code_admin =  '';
		if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
			$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' AS code' ;
		}
		$with_etab_status = '';
		if( exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ){
			$with_etab_status = ', E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_STATUT_ETABLISSEMENT'].' AS status' ;
		}
		$with_etab_hierarchie = '';
		$exist_rattach_etab_annee = false;
		if ( ($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] <> '') && ($GLOBALS['PARAM']['TYPE_RATTACHEMENT'] <> '') ) {
			if ( exist_champ_in_table($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) 
				&& exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ) {
				
				$with_etab_hierarchie = ' , E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' , E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] ;
				//Modif Hebie pr filtrer certain type d'entité collectée par année
				$from_rattach_etab_annee = array();
				$where_rattach_etab_annee = array();
				if (isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && count($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) > 0){
					$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
					$res_types_hierar = $this->conn->GetAll($req_etab_hierar);
					if (is_array($res_types_hierar)) {
						foreach( $res_types_hierar as $i_res => $res ){
							if(isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && $GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] <> ''){
								$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = ' , '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]];
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = ' AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  .= ' AND '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
							} else {
								$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = '';
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = '';
							}
						}
						if (count($from_rattach_etab_annee)>0) {
							$exist_rattach_etab_annee = true;
							$cpt_types_hierar = 0;
							foreach( $res_types_hierar as $i_res => $res ){
								if($cpt_types_hierar == 0){
									$requete = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom'. $with_etab_status . $with_code_admin . $with_etab_hierarchie . '
												FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
												WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
												AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$code_sys.'
												ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
								}else{
									$requete .= ' UNION SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom' . $with_etab_status . $with_code_admin . $with_etab_hierarchie . '
												FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
												WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
												AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$code_sys.'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
												ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
								}
								$cpt_types_hierar++;
							}
						}
					}
				}
				//Fin Modif Hebie
			}
		}
		if(!$exist_rattach_etab_annee){
			$requete        = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AS idregroup, E.'.$GLOBALS['PARAM']['NOM_ETABLISSEMENT'].' AS nom' . $with_etab_status . $with_code_admin . $with_etab_hierarchie . '
								FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R   
								WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
								AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$code_sys.'
								ORDER BY E.'.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
		}
		//echo $requete;
		$liste_etab = $this->conn->GetAll($requete);
		if( trim($with_etab_hierarchie) <> '' ){
		
			$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
									FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
									ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
		
			$res_types_hierar = $this->conn->GetAll($req_etab_hierar);
			$types_hierar  = array();
			if( is_array($res_types_hierar) ){
				foreach( $res_types_hierar as $i_res => $res ){
					$types_hierar[] = $res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
				}
			}
			//echo '<pre>';print_r($types_hierar);
			
			$liste_etab = get_hierarchie_etabs( $liste_etab,  $types_hierar ) ;
				
		}
		
		
		return $liste_etab;
	}
	
	
	function get_list_etabs_ids($typre_reg, $code_reg, $code_sys) {
		

		$db = $GLOBALS['conn'];
		
		// Recherche des regroupements sélectionnés par click dans l'arbre
		$list_code_reg = array();
		$list_nom_reg = array();
		
		$liste_etab = array();
			
			
		//création de la liste des coderegs enfants
		$list_code_reg = $this->getchildsid($typre_reg, $code_reg,$_SESSION['chaine']); //
		 // Modif Bass depuis Niger
		$crit_in = array();
		foreach($list_code_reg as $i_code_reg => $code_reg){
			if( !in_array( $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $crit_in) ){
				$crit_in[] = $code_reg[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] ;
			}
		}
		
		$where = 'E_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ( '.implode(', ',$crit_in).' )';
		// Fin Modif Bass depuis Niger
		$with_code_admin =  '';
		if( exist_champ_in_table($GLOBALS['PARAM']['CODE_ADMINISTRATIF'], $GLOBALS['PARAM']['ETABLISSEMENT']) && ($GLOBALS['PARAM']['CONCAT_CODE_ADMIN']) ){
			$with_code_admin = ', E.'.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' AS code' ;
		}

		$with_etab_hierarchie = '';
		$exist_rattach_etab_annee = false;
		if ( ($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] <> '') && ($GLOBALS['PARAM']['TYPE_RATTACHEMENT'] <> '') ) {
			if ( exist_champ_in_table($GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) 
				&& exist_champ_in_table($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'], $GLOBALS['PARAM']['ETABLISSEMENT']) ) {
				
				$with_etab_hierarchie = ' , E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' , E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT_PARENT'] ;
				//Modif Hebie pr filtrer certain type d'entité collectée par année
				$from_rattach_etab_annee = array();
				$where_rattach_etab_annee = array();
				if (isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && count($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) > 0){
					$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
											ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
					$res_types_hierar = $this->conn->GetAll($req_etab_hierar);
					if (is_array($res_types_hierar)) {
						foreach( $res_types_hierar as $i_res => $res ){
							if(isset($GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE']) && $GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] <> ''){
								$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = ' , '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]];
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = ' AND E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'];
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  .= ' AND '.$GLOBALS['PARAM']['RATTACHEMENT_ETABLISSEMENT_ANNEE'][$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'].' = '.$_SESSION['annee'];
							} else {
								$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]] = '';
								$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]]  = '';
							}
						}
						if (count($from_rattach_etab_annee)>0) {
							$exist_rattach_etab_annee = true;
							$cpt_types_hierar = 0;
							foreach( $res_types_hierar as $i_res => $res ){
								if($cpt_types_hierar == 0){
									$requete = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id
												FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
												WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
												AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$code_sys.'
												ORDER BY '.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
								}else{
									$requete .= ' UNION SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id, 
												FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R '.$from_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].' 
												WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
												AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'].$where_rattach_etab_annee[$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']]].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].' = '.$res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']].'
												AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' = '.$code_sys.'
												ORDER BY '.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
								}
								$cpt_types_hierar++;
							}
						}
					}
				}
				//Fin Modif Hebie
			}
		}
		if(!$exist_rattach_etab_annee){
			$requete        = 'SELECT E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' AS id
								FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' AS E, '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].' AS E_R 
								WHERE E.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' = E_R.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'
								AND '.$where.' AND E.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$code_sys.'
								ORDER BY '.$GLOBALS['PARAM']['ORDRE_TRIE_ETABLISSEMENT'].', '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'];
		}
		//echo $requete;
		$liste_etab = $this->conn->GetAll($requete);
		if( trim($with_etab_hierarchie) <> '' ){
		
			$req_etab_hierar    = ' SELECT '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
									FROM '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'
									ORDER BY '.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'.'.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT'].'';
		
			$res_types_hierar = $this->conn->GetAll($req_etab_hierar);
			$types_hierar  = array();
			if( is_array($res_types_hierar) ){
				foreach( $res_types_hierar as $i_res => $res ){
					$types_hierar[] = $res[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_RATTACHEMENT']];
				}
			}
			//echo '<pre>';print_r($types_hierar);
			
			$liste_etab = get_hierarchie_etabs( $liste_etab,  $types_hierar ) ;
				
		}
		
		
		return $liste_etab;
	}
}

?>
