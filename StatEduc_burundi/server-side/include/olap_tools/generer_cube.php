<script language="JavaScript" type="text/javascript">
    function OpenPopupGenerateAll(nom_fic) {
        var	popup	=	window.open(nom_fic,'popGenAll', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=350, height=50, left=200, top=150')
        popup.document.close();
        popup.focus();
    }
    function Reload() {
       <?php if(isset($_GET['appel']) && $_GET['appel']==1)
            echo "var n = 1;";
        else
            echo "var n = 0;";
        ?>        
       if (n==0)
       {
            location.href="olap.php?val=pop_olap_gen&id_olap=<?php echo $_GET['id_olap']?>&appel=1";
       }
    }
</script>

<?php if($_GET['val'] == 'olap_gen_all'){
        $nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen='.$_GET['val_gen'];
        echo '<script language="JavaScript" type="text/javascript">';
        echo 'OpenPopupGenerateAll(\''.$nom_fichier.'\')';
        echo '</script>';
    }else{
        require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.createcube.class.php'; 
        $tab_cubes=array();
        $tab_cubes_faits = array();
        if(isset($_GET['id_olap']) && $_GET['id_olap']<>'') 
        {
            $tab_cubes[]=$_GET['id_olap'];
        }        
        else
        {
            $req = "SELECT ID_OLAP,TABLE_FAITS FROM DICO_OLAP";
            $res = $GLOBALS['conn_dico']->GetAll($req);
            foreach($res as $rs)
            {
                $tab_cubes_faits[$rs['ID_OLAP']] = $rs['TABLE_FAITS'];
				$tab_cubes[] = $rs['ID_OLAP'];
            }
			$_SESSION['tab_cubes_faits'] = $tab_cubes_faits;
			
            $show_progress = true ;
        }
        if($show_progress ==true ) { 
            debut_popup_progress();
        }
        
        $cube = new olap_create_cube($tab_cubes);
       	if($_GET['val'] == 'pop_olap_gen'){
			// Suppression batch des tables de faits
			$cube->delete_table_faits();			
			// Suppression batch des cubes
			$cube->delete_cubes();			
			 // Création batch des tables temporaires
			$cube->creer_table_temp();			
			// Création batch des tables de faits    
			$cube->creer_table_faits();	
			// Suppression batch des tables temporaires
			$cube->delete_table_temp();
		}
		elseif($_GET['val'] == 'pop_olap_gen_menu'){
			if($_GET['val_gen'] == 'fact_tab'){
				// Suppression batch des tables de faits
				$cube->delete_table_faits();
				// Création batch des tables temporaires
				$cube->creer_table_temp();					
				// Création batch des tables de faits    
				$cube->creer_table_faits();	
				// Suppression batch des tables temporaires
				$cube->delete_table_temp();
			}
			elseif($_GET['val_gen'] == 'loc_cube'){
				// Suppression batch des cubes
				$cube->delete_cubes();
			}elseif($_GET['val_gen'] == 'del_fact_tab'){
				// Suppression batch des tables des faits   
				$cube->delete_table_faits();   
			}elseif($_GET['val_gen'] == 'compact_db'){
				// Compactage de la base de donnees   
				require_once $GLOBALS['SISED_PATH_INC'] . 'outils_integres/compacter_BD.php';
			}
		} 
		if((!isset($_GET['val_gen'])) || (isset($_GET['val_gen']) && $_GET['val_gen'] == 'loc_cube')){       
			foreach($cube->html as $html)
			{            
				echo $html;
			}   
			
			echo "\n";  
			echo '<SCRIPT LANGUAGE="VBSCRIPT">';
			for($i=0; $i<count($cube->liste_cubes); $i++)
			{    
				if($cube->html[$cube->liste_cubes[$i]])
				{
					echo "\n";   
					echo 'Create_cube'.$cube->liste_cubes[$i].'()';
				}
			}
			if(count($cube->liste_cubes)==1){
				echo '</SCRIPT>'."\n";
            }else{
                echo "\n";
                echo "Set WshShell = CreateObject(\"WScript.Shell\")\n";
                echo "WshShell.SendKeys( \"%{F4}\" )\n";
                echo "\n";
                echo '</SCRIPT>'."\n";
            }
		}
        	
		if($_GET['val_gen'] == 'fact_tab'){
			if($show_progress ==true){
				/*echo '
				<script language="JavaScript" type="text/javascript">
					parent.document.location.href="olap.php?val=gen_batch&val_gen=loc_cube'.'"; 
				</script>
					';*/
				fin_popup_progress();            
			}
		}
		/*elseif($_GET['val_gen'] == 'loc_cube'){
			if($show_progress ==true){
				echo '
				<script language="JavaScript" type="text/javascript">
					parent.document.location.href="olap.php?val=gen_batch&val_gen=del_fact_tab'.'"; 
				</script>
					';
				fin_popup_progress();            
			}
		}*/elseif($_GET['val_gen'] == 'del_fact_tab'){
			if($show_progress ==true){
				/*echo '
				<script language="JavaScript" type="text/javascript">
					parent.document.location.href="olap.php?val=gen_batch&val_gen=compact_db'.'"; 
				</script>
					';*/
				fin_popup_progress();            
			}
		}
        
        //Si c'est un seul un cube à générer, afficher le pivot table à la fin   
        if(count($cube->liste_cubes)==1){
            require_once $GLOBALS['SISED_PATH_CLS'] . 'metier/olap.class.php'; 
            $source_cube = "OlapFile";
            $nom_cube=$cube->liste_file_cubes[0];   
            if(isset($_GET['id_olap']) && $_GET['id_olap']<>'') {          
                $pivot = new olap($_GET['id_OLAP'],'',$nom_cube,'OlapFile',''); 
                echo $pivot->html;             
            }
        } 
    }
if( $_GET['val'] == 'pop_olap_gen'){
    echo '
    <script language="JavaScript" type="text/javascript">
        var timer = setInterval("Reload()", 2000);
    </script>
        ';
}
?>
