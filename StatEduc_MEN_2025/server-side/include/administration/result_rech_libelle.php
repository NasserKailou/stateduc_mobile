<?php include_once $GLOBALS['SISED_PATH_CLS'] . 'metier/gestion_zone.class.php'; 	
	lit_libelles_page('/frame_gestion_libelle_trad.php');
	///// fonction de récupération des codes d'un libelle
	function get_codes_libelle($libelle){
			
			$requete =" SELECT   DICO_LIBELLE_PAGE.CODE_LIBELLE, DICO_LIBELLE_PAGE.NOM_PAGE
						FROM     DICO_LIBELLE_PAGE 
						WHERE 	 DICO_LIBELLE_PAGE.CODE_LANGUE ='".$_SESSION['langue']."'
						AND    	 (DICO_LIBELLE_PAGE.LIBELLE LIKE '%".addslashes(trim($libelle))."%' 
									OR DICO_LIBELLE_PAGE.LIBELLE LIKE '%".convertWordSpecialChr(trim($libelle))."%')
						ORDER BY DICO_LIBELLE_PAGE.NOM_PAGE
					  ";
			//echo $requete ;			
			$_SESSION['Codes_Libelle'] = $GLOBALS['conn_dico']->GetAll($requete);
	} 
	//FIN get_codes_libelle()
	
	get_codes_libelle($_GET['libelle']);
	$cpt=0;
	if(is_array($_SESSION['Codes_Libelle'])) $cpt=count($_SESSION['Codes_Libelle']);
			
		
?>
<script language="javascript" type="text/javascript">
function choisir(ID_CODE,NOM_PAGE,LIBELLE){
	parent.document.location.href="administration.php?val=gest_lib_trad&nom_page="+NOM_PAGE+"&id_libelle="+ID_CODE+"&libelle="+LIBELLE;
	fermer();
}
</script>
<body>
<br><br><br>
    <span> 
    <div align="center"> 
        <table>
			<tr>
				<td>
				<b><?php echo '('.$cpt.') '.recherche_libelle_page(TitreResultRech).' " '.$_GET['libelle'].' "'; ?></b>
				</td>
			</tr>
		</table>
	</div>
	</span> 
	<br>
	<div align="center"> 
    	<table border="1" width="400" bgcolor="#000000" cellspacing="1" cellpading="3">
			<?php if(is_array($_SESSION['Codes_Libelle'])){
			?>
			<tr bgcolor="#cccccc"> 
				<td align=center><?php echo"".recherche_libelle_page('IdLibelle')."";?></td>
				<td align=center><?php echo"".recherche_libelle_page('NomPage')."";?></td>
			</tr>
			<?php foreach ($_SESSION['Codes_Libelle'] as $i => $ligne){
					$ID_CODE 			= $ligne['CODE_LIBELLE'] ;
					$NOM_PAGE 			= $ligne['NOM_PAGE'] ;
			?>
			<tr bgcolor='#FFFFFF'> 
				<td><a href=# onClick="choisir(<?php echo '\''.$ID_CODE.'\',\''.$NOM_PAGE.'\',\''.$_GET['libelle'].'\'';?>)"><?php echo $ID_CODE;?></a></td>
				<td><a href=# onClick="choisir(<?php echo '\''.$ID_CODE.'\',\''.$NOM_PAGE.'\',\''.$_GET['libelle'].'\'';?>)"><?php echo $NOM_PAGE;?></a></td>
			</tr>
			<?php }
			$_SESSION['choix_libelle']=1;
			}
			?>
		</table>
		<br>
		<table>
			<tr>
				<td colspan=2 align='center' nowrap="nowrap">
					<INPUT  style="width:100px;"  type="button" <?php echo 'value="'.recherche_libelle_page('close').'"';?> onClick="javascript:fermer();">
				</td>
			</tr>
											 
		</table>
	</div>
</body>
