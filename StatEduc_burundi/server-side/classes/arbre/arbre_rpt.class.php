<?php

class arbre {

    public $chaine;
    public $init_depht;
    public $result;
    
    // Ajout Alassane
    public $type_access;

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

            $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', T_R.'.$GLOBALS['PARAM']['LIBELLE'] .'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
                            .' FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C, '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS T_R'
                            .' WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'  AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$code_chaine.'
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

            foreach($chaine_fille as $cf) {

                $requete     = 'SELECT DISTINCT T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', T_C.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].', T_R.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']
                                .' FROM '.$GLOBALS['PARAM']['HIERARCHIE'].' AS T_C, '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS T_R '
                                .' WHERE T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'=T_R.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AND T_C.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.(int)$cf[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']].'
                                ORDER BY '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' DESC;';

                //$temp = $this->conn->CacheGetAll($requete);
                $temp = $this->conn->GetAll($requete);

                for($i=0;$i<count($temp);$i++) {

                    $temp[$i][$GLOBALS['PARAM']['NIVEAU_CHAINE']] = $i;

                }

                $result = array_merge($result, $temp);

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
	
	public function get_libelle_type_reg($code_type_reg){
		
		$requete     = 'SELECT '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AS LIBELLE_TYPE_REG
						FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$code_type_reg.';';

                $temp = $this->conn->GetAll($requete);	
				return ($temp[0]['LIBELLE_TYPE_REG']);
	}

    public function create_entete($init_depht=0, $final_depht=false, $combo_br=false) {

        $result = array();
                // bass
				$tab_lib_reg_cache 			= array();
				$GLOBALS['tab_reg_combo'] 	= array();
				// fin bass

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
        
		$regroupement_filtres = array() ;
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
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
				}else{
					$requete  = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].
								' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$curcodetyperegroup.
								' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')'.
								' ORDER BY '.$GLOBALS['CHAMP_ORDRE'].';';
				}
				//echo $requete;
                $regroupement = $this->conn->GetAll($requete);

                if(isset($_GET['code_regroup_' . $i]) && trim($_GET['code_regroup_' . $i]) <> '') {
					 
					 $code_regroups[$i] = $_GET['code_regroup_' . $i];
					 $_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_GET['code_regroup_' . $i];
				
				}elseif(isset($_POST['combo_regroup_' . $i]) && trim($_POST['combo_regroup_' . $i]) <> '') {
					 
					 $code_regroups[$i] = $_POST['combo_regroup_' . $i];
					 $_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_POST['combo_regroup_' . $i];

				}elseif(isset($_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i];
				
				} elseif(isset($_SESSION['reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['reg_parents'][$curcodetyperegroup][$i];
				
				}
				/*elseif(isset($_SESSION['fixe_regs'][$curcodetyperegroup]) && $_SESSION['fixe_regs'][$curcodetyperegroup]<>''){

                    $code_regroups[$i] = $_SESSION['fixe_regs'][$curcodetyperegroup];//$regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                
                }*/
				else{

                    $code_regroups[$i] = $regroupement[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];

                } 

                $j = 0;
				if($GLOBALS['type_reg']<>'etab'){
					$br = '<br/>';
					$width = 'width:100%;';
				}else{
					$br='<br/>';
					$width = 'width:50%;';
				}
				$combo_regroups[$i] = '<table width="100%"><tr><td nowrap="nowrap" width="45%"> &nbsp;'.$this->get_libelle_type_reg($curcodetyperegroup).' : '.$br;
                $combo_regroups[$i] .= '<select name="combo_regroup_'.$i.'" onchange="toggle_combo_'.$i.'();" style=\''.$width.'\'>' . "\n";
                $deja_sel = false;
				//on boucle sur le résultat
               foreach($regroupement as $i_regrp => $regrp) {	

                    $sel = '';

					if(in_array($regrp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],explode(',',$code_regroups[$i])) && !$deja_sel) {
                        $sel = ' selected';
                        // bass
                        $tab_lib_reg_cache[$i] = '<INPUT TYPE="hidden" NAME="LIBELLE_REG_'.$i.'" VALUE="' . $regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '">';
                        $GLOBALS['tab_reg_combo'][$i] = array('code' => $code_regroups[$i], 'lib' =>$regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
					    // fin bass
						$GLOBALS['code_reg_final_select'] = $regrp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
						$deja_sel = true;	
                    }

                    $combo_regroups[$i] .= '<option value="' . $regrp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";

                    $j++;

                }
				if(!$deja_sel) $GLOBALS['code_reg_final_select'] = $regroupement[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
                // Modif Alassane
                $combo_regroups[$i] .= '</select>' . "\n";
				if($GLOBALS['type_reg'] <> 'etab'){
					$combo_regroups[$i] .= ' </td>           
							<td align="right" width="55%"> <br />'.
								'All &nbsp;' . $GLOBALS['last_lib_type_reg'] .' (s)
										&nbsp;in &nbsp; <span style="color:#0000FF">' . $_SESSION['NOM_PAYS'] . '</span>
										<INPUT type="checkbox" id="all_zones_'.$i.'" name="all_zones_'.$i.'" value="1"'; 
				
					if($_POST['all_zones_'.$i] == '1') $combo_regroups[$i] .= ' CHECKED' ;
					if(in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])) $combo_regroups[$i] .= ' DISABLED'; 
					$combo_regroups[$i] .=	' onClick="ctrl_all_check('.$i.', '.($final_depht+1).', \'pays\' );"> ';
					$combo_regroups[$i] .=' </td>';
				}
				$combo_regroups[$i] .=' </tr></table> <br />' . "\n";
                // Fin Modif Alassane
                //$combo_regroups[$i] .= '</select>. "\n";

            } else {

                //on trouve le type courant de regoupement
                $curcodetyperegroup = $this->chaine[$i][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                //puis on fabrique la requête à partir du code regoupement et du type de regoupement
                if(!in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])){
					$requete =  'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroups[($i-1)].''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								' ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].';';
				}else{
					$requete =  'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroups[($i-1)].''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')'.
								' ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].';';
				}
				//echo $requete;
                //on exécute la requête
                $regroupement = $this->conn->GetAll($requete);

                if(isset($_GET['code_regroup_' . $i]) && trim($_GET['code_regroup_' . $i]) <> '') {
					 
					 $code_regroups[$i] = $_GET['code_regroup_' . $i];
					 $_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_GET['code_regroup_' . $i];
				
				}elseif(isset($_POST['combo_regroup_' . $i]) && trim($_POST['combo_regroup_' . $i]) <> '') {
					 
					 $code_regroups[$i] = $_POST['combo_regroup_' . $i];
					 $_SESSION['reg_parents'][$curcodetyperegroup][$i] = $_POST['combo_regroup_' . $i];
				
				}elseif(isset($_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['fixe_reg_parents'][$curcodetyperegroup][$i];

				}elseif(isset($_SESSION['reg_parents'][$curcodetyperegroup][$i]) && $_SESSION['reg_parents'][$curcodetyperegroup][$i]<>''){
					
					$code_regroups[$i] = $_SESSION['reg_parents'][$curcodetyperegroup][$i];

				}
				/*elseif(isset($_SESSION['fixe_regs'][$curcodetyperegroup]) && $_SESSION['fixe_regs'][$curcodetyperegroup]<>''){

                    $code_regroups[$i] = $_SESSION['fixe_regs'][$curcodetyperegroup];//$regroupement->fields[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];

                }*/
				else{

                    $code_regroups[$i] = $regroupement[0][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
					
                } 
				
				if($GLOBALS['type_reg']<>'etab'){
					$br = '<br/>';
					$width = 'width:100%;';
				}else{
					$br='<br/>';
					$width = 'width:50%;';
				}
				$j = 0;
				$combo_regroups[$i] = '<table width="100%"><tr><td nowrap="nowrap" width="45%"> &nbsp;'.$this->get_libelle_type_reg($curcodetyperegroup).' : '.$br;
                $combo_regroups[$i] .= '<select name="combo_regroup_'.$i.'" onchange="toggle_combo_'.$i.'();" style=\''.$width.'\'>' . "\n";
				$deja_sel = false;
				//on boucle sur le résultat
				foreach($regroupement as $i_regrp => $regrp) {						
					$sel = '';

					if(in_array($regrp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']],explode(',',$code_regroups[$i])) && !$deja_sel) {	
						$sel = ' selected';
						// bass
						$tab_lib_reg_cache[$i] = '<INPUT TYPE="hidden" NAME="LIBELLE_REG_'.$i.'" VALUE="' . $regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '">';
                        $GLOBALS['tab_reg_combo'][$i] = array('code' => $code_regroups[$i], 'lib' =>$regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']]);
						// fin bass
						$deja_sel = true;
					}

					$combo_regroups[$i] .= '<option value="' . $regrp[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '"'.$sel.'>' . $regrp[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</option>' . "\n";

					$j++;
				}
			
                // Modif Alassane
				 
                $combo_regroups[$i] .= '</select>' . "\n";
				if($GLOBALS['type_reg']<>'etab'){
					$combo_regroups[$i] .= ' </td>           
							<td align="right" width="55%"> <br />'.
								'All &nbsp;' . $GLOBALS['last_lib_type_reg'] .' (s)
								&nbsp;in &nbsp; <span style="color:#0000FF">' . $GLOBALS['tab_reg_combo'][$i - 1]['lib'] . '</span>
								<INPUT type="checkbox" id="all_zones_'.$i.'" name="all_zones_'.$i.'" value="1"'; 
								
					if($_POST['all_zones_'.$i] == '1') $combo_regroups[$i] .= ' CHECKED' ; 
					if(in_array($curcodetyperegroup,$_SESSION['fixe_type_regs'])) $combo_regroups[$i] .= ' DISABLED'; 
					$combo_regroups[$i] .=	' onClick="ctrl_all_check('.$i.', '.($final_depht+1).', \''.$GLOBALS['tab_reg_combo'][$i - 1]['code'].'\' );"> ';
					$combo_regroups[$i] .=' </td>';
				}
				$combo_regroups[$i] .='</tr></table> <br />' . "\n";
			    // Fin Modif Alassane
                //$combo_regroups[$i] .= '</select>' . "\n";

            }

        }

        $result['code_regroup'] = $code_regroups[$final_depht];
        $GLOBALS['last_reg'] 	= $result['code_regroup'] ;
		
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

                $vars .= 'code_regroup_' . $j . '=\'+combo_'.$j.'_val';

                if($j<$i) {

                    $vars .= '+\'&';

                }
            }

            $curpage = $_SERVER['PHP_SELF'];

            $gets = '';
			$i_count_gets = -1 ;
            foreach($_GET as $i_get=>$g) {

                $i_count_gets++ ;
				$catch = false;

                if(!preg_match('`^code_regroup_`', $i_get)) {

                    $catch = true;

                    $gets .= $i_get . '=' . $g;

                }

                if($catch && $i_count_gets<(count($_GET))) {

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

       // $result['html'] .= '<div align="center">' . "\n";
        //$result['html'] .= '<form name="combo_regroups">' . "\n";
        // bass
        foreach($tab_lib_reg_cache as $lib_reg_cache) {
                $result['html'] .= $lib_reg_cache . "\n";
        }
        // fin bass
       // $result['html'] .= '<br>'."\n";
        foreach($combo_regroups as $combo_regroup) {

            $result['html'] .= $combo_regroup . ''."\n";

            if($combo_br) {

               // $result['html'] .= '<br>'."\n";

            }

        }
        // bass


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
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.'
									 ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].' ; ';
				}else{
					$requete = 'SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L '.
								   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
								   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
								   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' IN ('.$_SESSION['fixe_regs'][$curcodetyperegroup].')
									 ORDER BY FILS.'.$GLOBALS['CHAMP_ORDRE'].' ; ';
				}

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
            // bass
            if($GLOBALS['popup'] <> 1){
                $result .= $depht_space . '<li>' . '<img src="'.$GLOBALS['SISED_URL_IMG'].'arbre/root.gif" onclick="expand(this)" />' . trim($_SESSION['NOM_PAYS']) . '</li>' . "\n";
            }
            // fin bass
            
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
                $result .= $depht_space . '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";

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
                    $result .= '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";
                    $result .= $depht_space . '<ol>' . "\n";

                //si il n'y a pas d'enfants
                } else {

                    //on met le libellé de ce regroupement
                    $result .= '<a href="'.$curlink.'"'.$curtarget.'>' . $r[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']] . '</a>' . "\n";

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
                    
            //echo $requete ;
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
								' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].
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
        foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {

            if($this->haschilds($ncs, $code_regroupement)) {
    
                $haschilds = true;
                break;
    
            }

        }
//die('child='.$haschilds);
        if(!$haschilds) {

            $result = array(0=>array($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']=>$code_regroupement));

        } else {

            if(isset($this->chaine[$depht])) {

                $curcodetyperegroup = $this->chaine[$depht][$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];

                $requete = ' SELECT FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', FILS.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L , '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'.
						   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
						   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
						   ' AND PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'='.(int)$curcodetyperegroup.
						   ' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].
						   ' AND  H.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$id_chaine.';';

                //$result = $this->conn->CacheGetAll($requete);
                $result = $this->conn->GetAll($requete);
				//echo $requete ;
                foreach($result as $r) {
    
                    foreach($this->chaine[$depht]['NIVEAU_CHAINE_SUIVANT'] as $i=>$ncs) {
        
                        $result = array_merge($this->getchildsid($ncs, $r[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']], $id_chaine, true), $result); 
    
          }    }    }   }
        
        return $result;

    }

    public function getparentsid($depht, $code_regroupement, $id_chaine, $recur=false) {

        $result = array();

        if(isset($this->chaine[$depht])) {

            $curcodetyperegroup = $this->chaine[$depht]['CODE_TYPE_REGROUPEMENT'];

            $requete = 'SELECT PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].', PERE.'.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS PERE, '.$GLOBALS['PARAM']['REGROUPEMENT'].' AS FILS, '.$GLOBALS['PARAM']['LIAISONS'].' AS L , '.$GLOBALS['PARAM']['HIERARCHIE'].' AS H'.
                                   ' WHERE  PERE.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=L.'.$GLOBALS['PARAM']['REG_CODE_REGROUPEMENT'].
                                   ' AND L.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'=FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' AND FILS.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'= '.(int)$code_regroupement.''.
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

    /**
     * Fonctions d'administration de l'arbre
     *
     */
    
    public function get_typeregs() {
    
        $requete = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].
                   ' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'<255 ORDER BY '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].';';
        //return $this->conn->CacheGetAll($requete);
        return $this->conn->GetAll($requete);
    
    }

    public function add_typereg($libelle, $ordre) {
    
        $newcodetypereg = 0;
    
        foreach($this->get_typeregs() as $t) {
    
            if($t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] > $newcodetypereg && $t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']] < 255) {
    
                $newcodetypereg = $t[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']];
    
            }
    
        }
    
        $requete = 'INSERT INTO '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' ('.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].', '.$GLOBALS['PARAM']['ORDRE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].') VALUES ('.(int)$newcodetypereg.', '.$this->conn->qstr($libelle).', '.(int)$ordre.');';
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

            $requete = 'DELETE FROM '.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'] .' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$id.';';
            return $this->conn->Execute($requete);

        }
    
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

            $j = count($hierarchie);

            foreach($hierarchie as $h) {

                $requete = 'UPDATE '.$GLOBALS['PARAM']['HIERARCHIE'].' SET '.$GLOBALS['PARAM']['NIVEAU_CHAINE'].' = '.(int)$j.' WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.(int)$_SESSION['chaine'].' AND '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.(int)$h[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT']].';';
                $this->conn->Execute($requete);

                $j--;

            }

            $result = true;

        }

        $this->chaine = $this->build_chaine($_SESSION['chaine']);

        return $result;

    }
		function get_chaine_reg_loc($reg_loc){
				
				$requete ='SELECT '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' FROM '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].', '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].
										' WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' AND  '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$reg_loc.
										' AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1'.
										' AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
		
				$result = $this->conn->Getrow($requete);
				return($result[$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT']]);
				
		}

}

?>