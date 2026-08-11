<script type="text/Javascript" language="javascript">
/** V�rifier la r�ouverture d'une page qui ne doit �tre ouverte qu'une seule fois
 * 	On utilise ici une variable window.localStorage['urlSansDouble']
 */
 
/** url complete de ma page mais sans lien interne � la page */
var pageOuverte = window.location.href;
var p = pageOuverte.indexOf("?");

if (p>-1) { pageOuverte = pageOuverte.slice(0,p);}
//alert (window.localStorage.getItem('nbPagesOuvertes'));
/** v�rification d'une �ventuelle double ouverture */
//function verifierSiMaPageEstOuverteDeuxFois() {
	var urlSansDouble = (!((window.localStorage.getItem('urlSansDouble')=="")||(window.localStorage.getItem('urlSansDouble')==null))) ? window.localStorage.getItem('urlSansDouble') : "";
	//alert (urlSansDouble);
	if (urlSansDouble == "") {
		window.localStorage.setItem('urlSansDouble',pageOuverte);
		window.localStorage.setItem('nbPagesOuvertes',1);
	}
	else {
		//window.localStorage.setItem('nbPagesOuvertes',parseInt(window.localStorage.getItem('nbPagesOuvertes'))+1);
		//alert("ATTENTION !!!"+"\n\n"+"La page "+pageOuverte+" est deja ouverte dans votre navigateur !"+"\n"+"Veuillez refermee d'abord celle deja ouverte"); 
		//return;
		window.close();
	}
//}
 
/** S'ex�cute d�s que la page est compl�tement affich�e */
//window.onload = function() {
//	verifierSiMaPageEstOuverteDeuxFois();
//}
 
/** S'execute � la fermeture de la page */
window.onbeforeunload = function() {
	//window.localStorage.setItem('nbPagesOuvertes',parseInt(window.localStorage.getItem('nbPagesOuvertes'))-1);
	window.localStorage.setItem('nbPagesOuvertes',0);
	//if (parseInt(window.localStorage.getItem('nbPagesOuvertes')) <1 ) {
		window.localStorage.setItem('urlSansDouble',"");
	//}
}
</script>
<?php ////Recuperation des varibles globales dans $_GET
$gets = '';
$i=0;
if(count($_GET)>0) {
	foreach($_GET as $cle => $val) {
		if(isset($_GET[$cle])) {
			$gets .= $cle . '=' . $val;
			if($i<(count($_GET)-1)) {
				$gets .= '&';
			}
		}
		$i++;
	}
}

//On positionne la filtre choisie en session
if(isset($_GET['filtre']) && $_GET['filtre']<>'') $_SESSION['filtre']=$_GET['filtre'];
$GLOBALS['lancer_theme_manager'] 		= true;
$GLOBALS['lancer_theme_manager_classe'] = true;
$GLOBALS['theme_data_MAJ_ok'] 			= true;

require_once 'common.php';
if(isset($_SESSION['reg_parents'])) unset($_SESSION['reg_parents']);
require_once $GLOBALS['SISED_PATH_CLS'] . 'arbre/arbre.class.php';
lit_libelles_page(__FILE__);
function load_theme($id_theme,$code_etab="",$type_ent_stat=""){
	print '<script type="text/Javascript">';
    if($type_ent_stat<>"")
		print '    document.location.href=\'?theme='.$id_theme.'&code_etab='.$code_etab.'&type_ent_stat='.$type_ent_stat.'\';';
	else
		print '    document.location.href=\'?theme='.$id_theme.'\';';
    print '</script>';
}

function hide_ID($id){
	print '<script type="text/Javascript">';
    print '    document.getElementById(\''.$id.'\').style.visibility = \'hidden\';';
    print '</script>';
}

if (isset($_GET['code_admin'])) {

    $requete    = 'SELECT '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].','.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].' 
                   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT'].' 
                   WHERE '.$GLOBALS['PARAM']['CODE_ADMINISTRATIF'].' =\''. $_GET['code_admin'].'\';';
    $db 	= $GLOBALS['conn'];
    $result_etab = $db->GetRow($requete);

    if ($result_etab){

        $_GET['code_etab'] = $result_etab[$GLOBALS['PARAM']['CODE_ETABLISSEMENT']];
        $_GET['code_systeme'] = $result_etab[$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT']];

    } else { // Le code administratif n'a pas �t� trouv� dans la BD
        print $_GET['code_admin'].': '.recherche_libelle_page('CodAdmInex').'<BR>';
    }
}

    // Affichage des infos sur l'�tablissement en cours
    if(	isset($_GET['type_ent_stat'])) {
        $_SESSION['type_ent_stat'] = $_GET['type_ent_stat'];
    }
	if(isset($_GET['code_etab'])) {
        $_SESSION['code_etab'] = $_GET['code_etab'];
		$conn                     = $GLOBALS['conn'];
		//Recherche le code_regroupement � partir du code_etab : Modif Hebie
		/*$requete    = 'SELECT '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].' 
					   FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'
					   WHERE '.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].' ='. $_SESSION['code_etab'];
		//et mise en session
		$code_regroup = $conn->GetOne($requete);*/
		$requete ='SELECT '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
				FROM '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].', '.$GLOBALS['PARAM']['REGROUPEMENT'].', '.$GLOBALS['PARAM']['HIERARCHIE'].','.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' 
				WHERE '.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_REGROUPEMENT'].'
				AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'
				AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'='.$GLOBALS['PARAM']['REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT'].'
				AND  '.$GLOBALS['PARAM']['ETABLISSEMENT_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE_ETABLISSEMENT'].'='.$_SESSION['code_etab'].'
				AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'='.$_SESSION['chaine'].'
				AND  '.$GLOBALS['PARAM']['HIERARCHIE'].'.'.$GLOBALS['PARAM']['NIVEAU_CHAINE'].'=1
				AND  '.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].'.'.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'].'='.$_SESSION['secteur'];
		//et mise en session
		$code_regroup = $conn->GetOne($requete);
		if(!isset($_SESSION['code_regroupement']) || $_SESSION['code_regroupement']<>$code_regroup){
			$_SESSION['code_regroupement']=$code_regroup;
			//Recherche du nombre de niveaux de la chaine
			$requete ='SELECT COUNT(*)
						FROM '.$GLOBALS['PARAM']['HIERARCHIE'].'
						WHERE '.$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_CHAINE_REGROUPEMENT'].' = '.$_SESSION['chaine'];
			$niveau = $conn->GetOne($requete)-1;
			//La classe arbre permet de recomposer la hi�rarchie des regroupements
			$arbre = new arbre($_SESSION['chaine']);
			$hierarchie = $arbre->getparentsid($niveau,$_SESSION['code_regroupement'],$_SESSION['chaine']);
		}else{
			$arbre = new arbre($_SESSION['chaine']);
			$hierarchie = $arbre->getparentsid(substr($_SESSION['nom_regroupement'],1),$_SESSION['code_regroupement'],$_SESSION['chaine']);
		}
        $_SESSION['hierarchie_regroup'] = '';
        foreach($hierarchie as $i=>$h) {
            $_SESSION['hierarchie_regroup'] .= $h[$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['REGROUPEMENT']];
            if($i<(count($hierarchie)-1)) {
                $_SESSION['hierarchie_regroup'] .= '<b> / </b>';
            }
        }
        $_SESSION['hierarchie_regroup'] .= '<br>';
		$_SESSION['infos_etab'] = get_infos_etab();
    }
	$style_infos_etab = '';
	if(isset($GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB']) && isset($GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']]) && $GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']] <> ''){
		$style_infos_etab = ' style="'.$GLOBALS['PARAM']['SECTEUR_STYLE_INFOS_ETAB'][$_SESSION['secteur']].'"';
	}
?>
<?php if($_GET['val'] == 'new_etab'){
    if(isset($_SESSION['infos_etab'])) unset($_SESSION['infos_etab']);
    if(isset($_SESSION['code_etab'])) unset($_SESSION['code_etab']);
    if(isset($_SESSION['hierarchie_regroup'])) unset($_SESSION['hierarchie_regroup']);
	if(isset($_SESSION['tmis'])) unset($_SESSION['tmis']);
	if(isset($_SESSION['liste_etab'])) unset($_SESSION['liste_etab']);
	if(isset($_SESSION['liste_etablissements'])) unset($_SESSION['liste_etablissements']);
  	if(isset($_GET['type_ent_stat']) && $_GET['type_ent_stat'] == 1) unset($_SESSION['filtre']);
    //die($_SESSION['infos_etab'])
	
	//verification de la periodicite du theme courant
	if(isset($_GET['theme']) && $_GET['theme']<>''){
		$_SESSION['theme']=$_GET['theme'];
	}
	$requete= "SELECT CHAMP_PERE FROM DICO_ZONE WHERE ID_THEME=".$theme_manager->id;
	$result=$GLOBALS['conn_dico']->GetAll($requete);
	$theme_periodique=false;
	if(is_array($result)){
		foreach($result as $rs){
			if($GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE']==$rs['CHAMP_PERE']){
				$theme_periodique=true;
				break;
			}
		}
	}
	$disabled='';
	//if($_SESSION['groupe']<>1)
	//	$disabled=' disabled="disabled"';
    ?>
<script type="text/Javascript">
		function AlertSupEtab(code_etab){
				if(confirm("<?php echo recherche_libelle_page('AlertSupEt'); ?>")){ 
						document.location.href="saisie_donnees.php?val=choix_etablissement&sup_etab="+code_etab;
				}
		}
		function Set_Element(elem, form, val){
			eval('document.'+form+'.'+elem+'.value = '+val+' ;');
		}
		function changer_action(){
			//alert(document.forms['form1'].action);
		}
		function toggle_filtre(filtre){
			document.location.href="?<?php echo $gets; ?>&filtre="+filtre;
		}
		function ouvrir_pop_imp_excel(){
			var	popup	=	window.open('saisie_donnees.php?val=PopupImportExcel&val_code_etab=new_etab','PopupImportExcel', 'toolbar=no,location=no,directories=no,menubar=no,scrollbars=yes,status=no,resizable=1,width=500, height=175, left=250, top=50')
			popup.document.close();
			popup.focus();
		}
</script>

    <div id="rappel" <?php echo $style_infos_etab;?>>
    <p <?php echo $style_infos_etab;?>> <?php echo recherche_libelle_page('curr_year');?> : <b><?php echo get_libelle_from_array($_SESSION['tab_annees'], $_SESSION['annee'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_ANNEE']);?></b> 
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php if($GLOBALS['PARAM']['FILTRE'] && $theme_periodique) echo recherche_libelle_page('curr_period').' : '.create_combo($_SESSION['tab_filtres'],$GLOBALS['PARAM']['LIBELLE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_FILTRE'], $_SESSION['filtre'], 'toggle_filtre(this.value)', $disabled); ?>
	<br>
    <?php echo recherche_libelle_page('curr_sect');?> : 
    <b><?php echo get_libelle_from_array($_SESSION['tab_secteur'], $_SESSION['secteur'],$GLOBALS['PARAM']['CODE'].'_'.$GLOBALS['PARAM']['TYPE_SYSTEME_ENSEIGNEMENT'], 'LIBELLE');?></b>
    </p>
    </div>
<?php }else{ ?>
<!-- Rappel des infos etablissement -->
    <div id="rappel" <?php echo $style_infos_etab;?>>
    <a href="questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_SESSION['type_ent_stat'])) echo "&type_ent_stat=".$_SESSION['type_ent_stat'] ; ?>" style=" text-decoration:none ; " ><p <?php echo $style_infos_etab;?>><?php print '<b>' . $_SESSION['hierarchie_regroup'] . '</b>'; ?></p></a>
    <a href="questionnaire.php?theme=<?php echo $_GET['theme']; if(isset($_SESSION['type_ent_stat'])) echo "&type_ent_stat=".$_SESSION['type_ent_stat'] ; ?>" style=" text-decoration:none ; " ><p <?php echo $style_infos_etab;?>><?php print $_SESSION['infos_etab']; ?></p></a>
    </div>
<!-- Fin rappel des infos etablissement -->
<?php }

if($GLOBALS['PARAM']['BTN_IMPORT_EXCEL']){
	$button_import	=  '<BR/>
						<DIV align=center>
						<TABLE border="0">
							<TR>
								<TD width="100%" align="center"><input style="width:98%; text-align:center;" type="button" name="btn_import_excel" value="&nbsp;&nbsp;'.recherche_libelle_page('import_excel').'&nbsp;&nbsp;" onclick="javascript:ouvrir_pop_imp_excel();"/></TD>
							</TR>
						</TABLE>
						</DIV>';
	echo $button_import;
}

$requete  = "SELECT ACTION_THEME 
	 FROM DICO_THEME 
	 WHERE ID =".$theme_manager->id.";";
//echo $requete;
$result_etab = $GLOBALS['conn_dico']->GetRow($requete);
$nom_theme = $result_etab['ACTION_THEME'];

$curfile = $GLOBALS['SISED_PATH_INS'] . $nom_theme;

if(file_exists($curfile) and $nom_theme != '') {

    require_once $curfile;

} else {

    print $curfile.' Inexistant!<BR>';

}

?>
<!-- Questionnaire suivant --> 
<?php if( $_GET['val'] <> 'new_etab'){ ?>
		<div id="suite">
		<?php $theme_precedant					= $theme_manager->recherche_theme_prec();
				$theme_suivant 						= $theme_manager->recherche_theme_suiv();
				
				//echo '<br>New_Etab='.$New_Etab;
				if(isset($New_Etab)){
						$plus_Get ='&code_etab='.$New_Etab;
						//Afin de reg�n�rer les infos decriptives de l'�tab cr��
						new_infos_etab();
						print '<script type="text/Javascript">';
						if(isset($_GET['type_ent_stat']))
							print "    document.location.href='questionnaire.php?theme=".$theme_manager->id.$_SESSION['secteur']."&code_etab=".$_SESSION['code_etab']."&type_ent_stat=".$_GET['type_ent_stat']."';";
						else
							print "    document.location.href='questionnaire.php?theme=".$theme_manager->id.$_SESSION['secteur']."&code_etab=".$_SESSION['code_etab']."';";
						print '</script>';
				}
				
						if($theme_precedant) {
						$lib_long_theme_precedant = $theme_manager->get_lib_long_theme($theme_precedant,$_SESSION['langue']);
						if(isset($_SESSION['type_ent_stat']))
							echo '<a href="?theme=' . $theme_precedant.'&code_etab='.$_SESSION['code_etab'].'&type_ent_stat='.$_SESSION['type_ent_stat'].'" class="precedent" title="'.$lib_long_theme_precedant.'">' . recherche_libelle_page('ThemPrec') . '</a>' . "\n";
						else
							echo '<a href="?theme=' . $theme_precedant.'" class="precedent" title="'.$lib_long_theme_precedant.'">' . recherche_libelle_page('ThemPrec') . '</a>' . "\n";
					
						/////////////////// pour le ctrl du post lors du clic sur suivant ou precedent
						//echo '<a href="#" onClick="POST_switch_theme('.$theme_precedant.')" class="precedent" title="'.$lib_long_theme_precedant.'">' . recherche_libelle_page('ThemPrec') . '</a>' . "\n";
						
						//echo '<INPUT TYPE="button" onClick="POST_switch_theme('.$theme_precedant.')" class="precedent" VALUE="'.recherche_libelle_page('ThemPrec').'">' . "\n";
					if( (isset($_POST['save_and_prev']) && ($_POST['save_and_prev']==1)) &&($GLOBALS['theme_data_MAJ_ok']	== true) ){
						if(isset($_GET['type_ent_stat']))
							load_theme($theme_precedant,$_GET['code_etab'],$_GET['type_ent_stat']);
						else
							load_theme($theme_precedant);
					}
				}else{
					hide_ID('btn_save_and_prev');
				}
		
				if($theme_suivant) {
						$lib_long_theme_suivant 	= $theme_manager->get_lib_long_theme($theme_suivant,$_SESSION['langue']);
						if(isset($New_Etab)){
							if(isset($_SESSION['type_ent_stat']))
								echo '<a href="?theme=' . $theme_suivant . $plus_Get .'&type_ent_stat='.$_SESSION['type_ent_stat']. '" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
							else
								echo '<a href="?theme=' . $theme_suivant . $plus_Get . '" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
						}else{
							if(isset($_SESSION['type_ent_stat']))
								echo '<a href="?theme=' . $theme_suivant.'&code_etab='.$_SESSION['code_etab'].'&type_ent_stat='.$_SESSION['type_ent_stat'].'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
							else
								echo '<a href="?theme=' . $theme_suivant.'" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
								/////////////////// pour le ctrl du post lors du clic sur suivant ou precedent
								//echo '<a href="#" onClick="POST_switch_theme('.$theme_suivant.')" class="suivant" title="'.$lib_long_theme_suivant.'">' . recherche_libelle_page('ThemSuiv') . '</a>' . "\n";
								//echo '<INPUT TYPE="button" onClick="POST_switch_theme('.$theme_suivant.')" class="precedent" VALUE="'.recherche_libelle_page('ThemSuiv').'">' . "\n";
						}
					if( (isset($_POST['save_and_next']) && ($_POST['save_and_next']==1)) &&($GLOBALS['theme_data_MAJ_ok']	== true) ){
						if(isset($_GET['type_ent_stat']))
							load_theme($theme_suivant,$_GET['code_etab'],$_GET['type_ent_stat']);
						else
							load_theme($theme_suivant);
					}
				}else{
					hide_ID('btn_save_and_next');
				}
			print '<script type="text/Javascript">'."\n";
			print '    Set_Element(\'save_and_prev\', \'form1\', 0);'."\n";
			print '    Set_Element(\'save_and_next\', \'form1\', 0);'."\n";
			print '</script>'."\n";
		?>
		</div>
	<?php }
		hide_ID('btn_save_and_prev');
		$theme_suivant 	= $theme_manager->recherche_theme_suiv();
		if(!$theme_suivant){
			hide_ID('btn_save_and_next');
		}
	?>
</DIV>

<div id="photo-light"></div>