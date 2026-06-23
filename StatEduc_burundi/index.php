<?php $ne_pas_verifier_session = true;

require_once 'common.php';

lit_libelles_page(__FILE__);

if (isset($_SESSION['valide']) && $_SESSION['valide'] && !(isset($_GET['val']) && $_GET['val']=='param_conn')){ // Si nom utilisateur et mot de passe sont corrects
    // Accès à l'accueil
    header('Location: '.$GLOBALS['SISED_AURL'].'accueil.php');
    exit();
}elseif(isset($_GET['val']) && $_GET['val']=='param_conn'){ // Si on se trouve ici et que le bouton "envoyer login" a été clické 
    // c'est que la connexion est impossible
    $message                = recherche_libelle_page('ContactAdmin');
}
if ($_POST){ // Si on se trouve ici et que le bouton "envoyer login" a été clické 
    // c'est que e login n'est pas bon
    $message                = recherche_libelle_page('VerifLog');
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

<BODY onLoad="resolution();javascript:document.form1.login.focus();" bgcolor="#FFFFFF">

<div align="center">
  <table width="100%" border="0" cellspacing="0" cellpadding="5">
    <tr>
      
    <td width="95" valign="top"><img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>unesco.gif" width="95" height="88"></td>
    <td align="center" class="titre"><img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>logo.gif" width="370" height="109"></td>
      
    <td align="center" width="90" class="pays">
			<?php if(trim($_SESSION['ARMOIRIES_PAYS'])<>''){?>
			<img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>armoiries/<?php print trim($_SESSION['ARMOIRIES_PAYS']); ?>.gif" width="76" height="88"><br>
        <?php print $_SESSION['NOM_PAYS']; ?> 
			<?php }else{ ?>
			<img src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>unesco.gif" width="95" height="88">	
			<?php } ?>
		</td>
    </tr>
  </table>
  
<table width="900" border="0" cellspacing="0" cellpadding="0" align="center" background="<?php echo $GLOBALS['SISED_URL_IMG'] ?>login_fond.jpg" height="430" class="table-fond">
  <tr align="center" valign="top"> 
    <td> 
      <p class="titre"><?php print recherche_libelle_page('RCS'); ?></p> 
      <form method="post" action="index.php" name="form1">
        <table align="center" cellpadding="3" cellspacing="0" border="0" class="table-login" bgcolor="#FFFFFF" width="300">

<?php if(!empty($message)) {

echo '          <tr align="center">' . "\n";

echo '              <td colspan="2" height="30"><b> <font color=red>' . $message . '</font></b></td>' . "\n";

echo '          </tr>' . "\n";

}

$login = '';

?>

          <tr align="center"> 
            <td colspan="2" height="30"><b><?php print recherche_libelle_page('OuvSess'); ?></b></td>
          </tr>
          <tr> 
            <td align="right"><?php print recherche_libelle_page('NomUser'); ?></td>
            <td> 
              <input type="text" name="login" class="login" value="<?php echo $login; ?>">
            </td>
          </tr>
          <tr> 
            <td align="right"><?php print recherche_libelle_page('MotPass'); ?></td>
            <td> 
              <input type="password" name="password" class="login">
            </td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
            <td> 
              <input name="Submit" id="searchButton" type="image" src="<?php echo $GLOBALS['SISED_URL_IMG'] ?>envoyer.gif" width="21" height="22" border="0"  value="Envoyer" class="envoyer">
            </td>
          </tr>
        </table>
      </form>
    </td>
  </tr>
</table>
</div>
<?php require_once $GLOBALS['SISED_PATH_INC'] . 'footer.php'; ?>
