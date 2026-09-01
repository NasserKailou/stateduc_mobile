<?php session_start();
?>
<link href="../css/formulaire_senegal.css" rel="stylesheet" type="text/css">
<html>
      <form method="post" action="<?php echo $GLOBALS['SISED_AURL'].'accueil.php'; ?>">
                <INPUT type="submit" name="Submit" value="Retour">
      </FORM></TD>
</html>
<?php foreach ($_SESSION as $k => $v)
{
    echo  '<B>'.$k ; 
    print ': </B>';
	print_r($v);
    print '</B><BR>';
}
?>
