<?php $afficher = 0;
?>
<script language="JavaScript1.2" type="text/javascript">
    function OpenPopupInstanceRpt(nom_fic) {
            <?php include_once $GLOBALS['SISED_PATH_CLS'].'metier/quickreport.class.php';         
                        set_time_limit(0);
                        $id_report =2;
                        $nom_fichier ='server-side/include/tableaux_synthese/pdf/'.$id_report.session_id().'.pdf';
                        $creport = new quickreport($id_report,  $_SESSION['secteur'],$nom_fichier);
                        $creport->preview_report();
                
                ?>
              <?php $afficher = true ;?>
				var	popup	=	window.open(nom_fic,'popUserRpt', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=600, height=370, left=200, top=150')
				popup.document.close();
				popup.focus();
		}
    function DeleteRpt(afficher) {
          alert(afficher);
          if (afficher = true){
            alert("Test");
          }
		}
</script>
<html>
<body onFocus="DeleteRpt(<?php echo $afficher; ?>)">
<input style="width:50%;" onClick="OpenPopupInstanceRpt('<?php echo $nom_fichier; ?>');" type="button" ></body>
</html>

