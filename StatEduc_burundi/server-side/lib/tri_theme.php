<?php //---------------------------------------------------------------------//

	function permuter(&$val1, &$val2) {
		//permuter le contenu de 2 variables
		$temp = $val1;
		$val1 = $val2;
		$val2= $temp;
	}

//---------------------------------------------------------------------//    
    
	 function tri_pere(&$dico) {
    	/**
         * Trie les elements pere d'un tableau (dico) selon l'ordre de
         * précedence
         * 
         * @param array $dico
         * @return array $dico
         */   
        $cpt = 0;
		while  ( $cpt < count($dico) )
    	{    		
    		// trier les peres
    		if ( !$dico[$cpt][PERE] )// element ayant pas de pere
    		{    		
    			$cpt2 = 0;
    			while ( ($cpt2 < count($dico)) )
    			{	
    				$entrer = false; 
	    			if ( $dico[$cpt2][PRECEDENT]=== $dico[$cpt][ID] )
	    			{
	    				$entrer = true;
	    				break;
	    			}
    				$cpt2++;	
    			}    			
    			if ( ($cpt > $cpt2) && $entrer )
    			{	//permuter les valeurs
    				$this->permuter($dico[$cpt], $dico[$cpt2]);

    				$cpt = 0; // on recommence au début du dico    				
    			}
    			else $cpt++;
    		}
    		else $cpt++;    		
    	}
    	
    	// remonte les père en haut du tableau
    	$cpt=0;
    	while  ( $cpt < count($dico) )
    	{
    	$cpt2 = 0;
			while ( ($cpt2 < count($dico)) )
			{	
				//remonter les pere en haut du tableau
				if ( $dico[$cpt2][PERE] ) //si c'est un fils
					if ($cpt > $cpt2) // si il en bas 
						permuter($dico[$cpt], $dico[$cpt2]);
				$cpt2++;
			}
			$cpt++;
    	}
    	return $dico;
  	}


//---------------------------------------------------------------------//
  	
  	function tri_fils(&$dico) {
    	/**
         * Trie les elements fils d'un tableau (dico) selon l'ordre de
         * précedence
         * 
         * @param array $dico
         * @return array $dico
         */
    	$cpt = 0;
    	while  ( $cpt < count($dico) )
    	{
    		if ( $dico[$cpt][PERE] )
    		{ // element ayant un pere
    			$cpt2 = 0;    			
    			while ( ($cpt2 < count($dico)) )
    			{ 	  
    				$entrer = false;  	
    					
		    			if ( ( $dico[$cpt][ID] === $dico[$cpt2][PRECEDENT] ) || ( $dico[$cpt][ID] === $dico[$cpt2][PERE] ))
		    			{
		    				$entrer = true;
		    				break;
		    			}
	    				$cpt2++;	
    			}    			
    			if ( ($cpt > $cpt2) && $entrer )
    			{     				   				
    				permuter($dico[$cpt], $dico[$cpt2]);

    				$cpt = 0; // on recommence au début du dico    				
    			}
    			else $cpt++;
    		}
    		else $cpt++;  
    	}// fin while
    	return $dico;
  	}
  	

//---------------------------------------------------------------------// 	
 
    /**
	 * Trie les elements d'un tableau (dico) selon l'ordre de précedence
	 * le passage du parametre a lieu par référence
	 *
	 * @access public
	 * @author Olivier DASINI <olivier@anaska.com>
	 * @version 1.0
	 * @param array $dico dictionnaire de données
	 * @return array $dico dictionnaire de données trié
	 */ 
    function tri_tab(&$dico) {
    	/**
         * Trie les elements d'un tableau (dico) selon l'ordre de précedence
         * 
         * @param array $dico
         * @return array $dico
         */   
      
        //$this->affiche_dico ($dico); echo '<br>';        
      
		tri_pere($dico);
		tri_fils($dico);  
        
    	//$this->affiche_dico ($dico);
    	return $dico;
    }
    
    
    
    
//---------------------------------------------------------------------//    
    function charger_theme($langue){
    /**
	 * Charge les informations du dico relative aux themes des questionnaires
	 * (table: DICO_THEME)
	 * Ces infos sont triées avec tri_tab()
	 * 
	 */ 
        // Lecture des infos du theme dans la table DICO_THEME du dico

        $requete = "SELECT D_T.ID, D_T.PERE, D_T.PRECEDENT, D_T.TAILLE_MENU, D_TRAD.LIBELLE
                    FROM DICO_THEME_SYSTEME AS D_T, DICO_TRADUCTION AS D_TRAD
                    WHERE D_T.ID = D_TRAD.CODE_NOMENCLATURE
                    And D_TRAD.NOM_TABLE='DICO_THEME_LIB_MENU'
                    And D_T.ID_SYSTEME=".$_SESSION['secteur']."
                    And D_TRAD.CODE_LANGUE='".$langue."';";
        //print $requete;
        // récupération du résultat dans un tableau
        $db 	        = $GLOBALS['conn_dico'];
        $t_result_theme	= $GLOBALS['conn_dico']->GetAll($requete);
/*        print '<BR><BR>';
        foreach ($t_result_theme as $k => $v){
            echo  '<B>'.$k ; 
            print ': </B>';
            print_r($v);
            print '</B><BR>';
        }*/

        // tri de la table selon les precedences
        $t_result_theme = tri_tab($t_result_theme);
/*        print '<BR><BR>';
        foreach ($t_result_theme as $k => $v){
            echo  '<B>'.$k ; 
            print ': </B>';
            print_r($v);
            print '</B><BR>';
        }*/
        
        //$dico_theme = $t_result_theme;
        
        return($t_result_theme);
    }
?>
