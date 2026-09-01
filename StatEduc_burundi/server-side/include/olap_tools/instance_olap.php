<script language="javascript" type="text/javascript">
function OpenPopupOpenDoc(doc_name) {
	var	popup	=	window.open('olap.php?val=pop_olap_menu&doc_name='+doc_name,'popDOC', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=700, height=460, left=200, top=150');            
	popup.document.close();
	popup.focus();
}
function OpenPopupOLAP2(id_OLAP,type_sgbd,nom_base,nom_cube,nom_server) {
	var	popup	=	window.open('olap.php?val=pop_olap_menu&id_OLAP='+id_OLAP+'&type_sgbd='+type_sgbd+'&nom_base='+nom_base+'&nom_cube='+nom_cube+'&nom_server='+nom_server,'popOLAP', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=700, height=460, left=200, top=150');            
	popup.document.close();
	popup.focus();
}
</script>
<?php if(!isset($_GET['id_OLAP']) && isset($_GET['type_sgbd']) && $_GET['type_sgbd']<>''){
        $nom_fichier = 'olap.php?val=pop_olap_gen_menu&val_gen='.$_GET['val_gen'];
        echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo 'OpenPopupOLAP2(0,\''.$_GET['type_sgbd'].'\',\''.$_GET['nom_base'].'\',\''.$_GET['nom_cube'].'\',\''.$_GET['server_olap'].'\')'."\n";
		echo '</script>'."\n";
    }elseif(isset($_GET['type_sgbd']) && $_GET['type_sgbd']<>''){
		require_once $GLOBALS['SISED_PATH_CLS'].'metier/olap.class.php';
		if (isset($_GET['type_sgbd']) && $_GET['type_sgbd']<>'' ){
			if ($_GET['type_sgbd']=='OlapFile'){
				if (isset($_GET['id_OLAP']) && $_GET['id_OLAP']<>''){
					// Affichage d'un cube local
					if(isset($_GET['nom_cube']) && $_GET['nom_cube']<>'')
						$pivot = new olap($_GET['id_OLAP'],'',$_GET['nom_cube'],'OlapFile',''); 
					else
						$pivot = new olap($_GET['id_OLAP'],'','','OlapFile','');
					echo $pivot->html;   
				 }
			}
			else{
				// Affichage d'un cube sur un serveur Olap
				if(isset($_GET['nom_base']) && isset($_GET['nom_cube'])){
					$pivot = new olap($_GET['id_OLAP'],$_GET['nom_base'],$_GET['nom_cube'],'OlapServer',$_GET['nom_server']);
				}else{
					$pivot = new olap($_GET['id_OLAP'],'','','OlapServer',$_GET['nom_server']);
				}
				echo $pivot->html;   
			}
		}  
		if (isset($_SESSION['liste_cubes'])){
			//unset($_SESSION['liste_cubes']);
		}
		if (isset($_SESSION['liste_local_cubes'])){
			//unset($_SESSION['liste_local_cubes']);
		}
	}elseif(isset($_GET['nom_cube']) && $_GET['nom_cube']<>''){
        echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo "OpenPopupOpenDoc('".$_GET['nom_cube']."')\n;";
		echo '</script>'."\n";
	}elseif(isset($_GET['doc_name']) && $_GET['doc_name']<>''){
        echo '<script language="JavaScript" type="text/javascript">'."\n";
		echo 'document.location.href=\''.$_GET['doc_name'].'\''."\n";
		echo '</script>'."\n";
	}
   
?>
