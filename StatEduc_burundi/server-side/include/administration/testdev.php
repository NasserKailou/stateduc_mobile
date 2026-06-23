<?php session_start();
print basename($_SERVER['PHP_SELF']);
print '<BR><BR><BR><BR>';
?>
<link href="../css/formulaire_senegal.css" rel="stylesheet" type="text/css">
<html>
      <form method="post" action="../accueil.php">
                <INPUT type="submit" name="Submit" value="Retour">
      </FORM></TD>
</html>

<?php /*********************/
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
    				$this->permuter($dico[$cpt], $dico[$cpt2]);

    				$cpt = 0; // on recommence au début du dico    				
    			}
    			else $cpt++;
    		}
    		else $cpt++;  
    	}// fin while
    	return $dico;
  	}
        
    print_r($_SESSION);
    print_r($_SESSION['dico']);
    print '<B>';
    tri_fils($_SESSION['dico']);
    print_r($_SESSION['dico']);
?>
