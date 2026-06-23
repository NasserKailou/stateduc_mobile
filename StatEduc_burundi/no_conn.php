<?php 
require_once 'config_app.php';

require_once $GLOBALS['SISED_PATH_LIB'] . 'fonctions.inc.php';
lit_libelles_page('/index.php');
manage_magic_quotes();

//Variable ADODB

$ADODB_FETCH_MODE   = ADODB_FETCH_ASSOC;
$ADODB_CACHE_DIR    = $SISED_PATH . 'server-side/adodbcache/';
require_once $GLOBALS['SISED_PATH_CLS'] . 'connexion.class.php';
$connexion = new connexion();

if ($_POST['form_db_code'] == '1'){ 
		$code_md5	= '21232f297a57a5a743894a0e4a801fc3';
		$db_code = $_POST['db_code'];
		if(md5($db_code) == $code_md5){
				$GLOBALS['dbs_manage'] = 1;
		}else{
				$message = 'Code Invalide / Invalid Code';
				unset($GLOBALS['dbs_manage']);
		}
}
if ( ($_GET['dbs_manage'] == 1) or ($_POST['form_dbs_manage'] == '1') or (trim($_GET['connexion']) <> '') ){
		$GLOBALS['dbs_manage'] = 1;
}

if ( $GLOBALS['dbs_manage'] == 1 ){
	
		$connexion->set_dbs_manage();
		///// ICI TRAITEMENT ET CONNEXION EN FONCTION DES TERMES SOUMIS
		if( isset($_GET['connexion']) && trim($_GET['connexion']) <> '' ) {
        $cnx_id = $_GET['connexion'];
    }
		elseif( isset($_POST['sources']) && trim($_POST['sources']) <> '' ) {
				$cnx_id = $_POST['sources'];
		}

		if(!isset($cnx_id) ){
				$cnx_id = $connexion->sources[0]['id'];
		}
		$cnx_key = $connexion->get_cnx_key_of($cnx_id);
	  //$connexion->set_default($cnx_id);
		
		$id                 = $connexion->sources[$cnx_key]['id'];
		$nom                = $connexion->sources[$cnx_key]['nom'];
		$type               = $connexion->sources[$cnx_key]['type'];
		$utilisateur        = $connexion->sources[$cnx_key]['utilisateur'];
		$mdp                = $connexion->sources[$cnx_key]['mdp'];
		$base               = $connexion->sources[$cnx_key]['base'];
		$serveur            = $connexion->sources[$cnx_key]['serveur'];
		
		if ( ($_POST['form_dbs_manage'] == '1') ){		
				if ($connexion->sources[$cnx_key]['type'] == 'access'){
						$base               = '';
						$serveur            = $_POST['base'];
				} else {
						//$serveur    = $connexion->sources[$cnx_key]['serveur'];
						$serveur    = $_POST['serveur'];
						$base       = $_POST['base'];
				}
		}
		
		$serveur = str_replace("\\",'/',$serveur);
		
		if ( ($_POST['form_dbs_manage'] == '1') ){
				if( $test_connect = $connexion->test_connect( $type, $serveur, $utilisateur, $mdp, $base) ){
						$connexion->modifier_source($id, $nom, $type, $serveur, $utilisateur, $mdp, $base);
						$connexion->set_default($id);
						$connexion->init($id);
						$cnx_key = 0;
						$GLOBALS['change_cnx'] = true;
						if($connexion->ok == true){
								//session_destroy();
								header('Location: '.$GLOBALS['SISED_AURL'].'index.php');
								//die('connexion réussie');
								exit();
						}
				}
		}		
}


        
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.02 Transitional//FR">
<HTML>
<TITLE>StatEduc2 - <?php echo recherche_libelle_page('TypeAppli'); ?></TITLE>
<META http-equiv="Content-Type" content="text/html; charset=iso-8859-15">
<link rel="stylesheet" href="<?php echo $GLOBALS['SISED_URL_CSS']; ?>login.css">

<SCRIPT language=javascript>

function centre(page,largeur,hauteur,options)
{
var top=(screen.height-hauteur)/2;
var left=(screen.width-largeur)/2;
window.open(page,"","top="+top+",left="+left+",width="+largeur+",height="+hauteur+","+options);
}

	function getCookieVal(offset) {
		var endstr=document.cookie.indexOf (";", offset);
		if (endstr==-1)
				endstr=document.cookie.length;
		return unescape(document.cookie.substring(offset, endstr));
	}
	
	function GetCookie (name) {
		var arg=name+"=";
		var alen=arg.length;
		var clen=document.cookie.length;
		var i=0;
		while (i<clen) {
			var j=i+alen;
			if (document.cookie.substring(i, j)==arg)
							return getCookieVal (j);
					i=document.cookie.indexOf(" ",i)+1;
							if (i==0) break;}
		return null;
	}
	
    function OuvrirPopup(page,nom,option) {
       w=window.open(page,nom,option);
    }
	
	function resolution () {
		la_resolution=GetCookie("resolution");
		if (la_resolution == null) // le cookie n'existe pas
		{// si la resolution est trop basse
			if( (screen.width==640)&&(screen.height==480) || (screen.width==800)&&(screen.height==600) )
				OuvrirPopup('resolution.htm', 'resolution', 'resizable=yes, directories=no, location=no, width=340, height=200, top=50, left=50, menubar=no, status=no, scrollbars=auto, menubar=no, fullscreen=no');
		}
	}
 </SCRIPT>

<BODY onLoad="resolution();" bgcolor="#FFFFFF">

<div align="center">
  <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
      
    <td width="95" valign="top"><img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>unesco.gif" width="95" height="88"></td>
      
    <td align="center" class="titre"><img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>logo.gif" width="370" height="109"></td>
      
    <td align="center" width="90" class="pays">

			<img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>unesco.gif" width="95" height="88">	
		</td>
    </tr>
  </table>
  
<table width="900" border="0" cellspacing="0" cellpadding="0" align="center" background="<?php echo $GLOBALS['SISED_URL_IMG'] ?>login_fond.jpg" height="430" class="table-fond">
  <tr align="center" valign="top"> 
    <td>
		<br>
      
			<?php if($GLOBALS['dbs_manage'] == 1){ ?>
			<?php if($connexion->ok == true){ ?>
				<div class="titre"><span style="color=red">CONNEXION A LA BASE REUSSIE... <br> CONNECTION TO DATABASE SUCCESS ...</span></div> 
			 <?php }else{
			?>
				<div class="titre"><span style="color=red">ECHEC DE CONNEXION A LA BASE ... <br> CONNECTION TO DATABASE FAILED ...</span></div> 
			 <?php }
			?>
			
			<script type="text/Javascript">
				function toggle_sources() {
				
						var value_langue = document.form1.sources.options[document.form1.sources.selectedIndex].value;
						location.href= '?connexion='+value_langue;
				
				}
				</script>
		
		<form method="post" action="no_conn.php" name="form1">

			<table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login" bgcolor="#FFFFFF" >
					<tr>
					<td>
            <table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login" bgcolor="#FFFFFF" >
                <TR align="center">
									<TD>
										TYPE SGBD :
									</TD>
                    <TD>
<select name="sources" onChange="toggle_sources();">
<?php foreach($connexion->sources as $i=>$s) {
    $selected = '';
    if($cnx_id == $s['id']) {
        $selected = ' selected';
    }
    echo '<option value="'.$s['id'].'"'.$selected.'>'.$s['nom'].'</option>' . "\n";
}
?>
</select>
                    </TD>
                </TR>
            </TABLE>


	<br>
			<INPUT type="hidden" value="1" name="form_dbs_manage">

            <table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login" bgcolor="#FFFFFF" >
 							<?php if ($connexion->sources[$cnx_key]['type'] == 'access'){
							?>
											<tr>
													<TD colspan="2">
															SELECT DB ACCESS : 
													</TD>
											</tr>               
											<tr>
													<TD colspan="2">
															<div style="position :absolute ;">
															<input type="file" name="select_db" style="filter :alpha(opacity=0)" size="50"
															onchange="document.form1.base.value=document.form1.select_db.value">
															</div>
															<input size="50" type="text" name="base" <?php echo 'value="'.$serveur.'" ';?>>
															<INPUT type="button" value="...">
													</TD> 
											</tr>
									<?php }else{
									?>
											<tr>
													<TD align="right">
															SERVER : 
													</TD>
													<TD>
															<input type="text" name="serveur" <?php echo 'value="'.$serveur.'" ';?>>
													</TD> 
											</tr>               
											<tr>
													              <TD align="right"> DB : </TD>
													<TD>
															<input type="text" name="base" <?php echo 'value="'.$base.'" ';?>>
													</TD> 
											</tr>
									<?php }
									?>
               
            <tr align="center">
                <td colspan=2 align="right">
                    <input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer">
                </td>
            </tr>
         </table>
				 </td>
					</tr>
					</table></form>

			<?php }else{ ?>
			<div class="titre"><span style="color=red">ECHEC DE CONNEXION A LA BASE ... <br> CONNECTION TO DATABASE FAILED ...</span></div> 
			<br>
      <form method="post" action="no_conn.php" name="form1">
			<INPUT type="hidden" value="1" name="form_db_code">
                    <table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login" bgcolor="#FFFFFF" >
                        <?php if($message){ ?>
												<tr align="center"> 
													<td colspan="3" height="30"><b><?php print $message; ?></b></td>
												</tr>
												 <?php }?>

												<tr> 
                            <td align="right" nowrap>DB MANAGER CODE :</td>
                            <td> <INPUT type="password" name="db_code" class="login" value="<?php echo $db_code; ?>"></td>
														                            <td> <input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer"> 
                            </td>

                        </tr>
                    </table>
      </form>
			<script language="JavaScript" type="text/javascript">
					document.form1.db_code.focus();
			</script>

			<?php } ?>
    </td>
  </tr>
</table>
</div>
<div id="<?php echo $photo; ?>"></div>

<!-- Affichage du pied de page --> 
<div id="centre"><a target="_blank" href='http://www.uis.unesco.org'>http://www.uis.unesco.org</a></div>
<!-- FIN Affichage du pied de page -->

</BODY>

</HTML>

<?php Alert_chg_cnx();
	exit();
?>

