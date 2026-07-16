<?php
$app_name = "phpJobScheduler";
$phpJobScheduler_version = "3.9";   
// -------------------------------
include_once($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/functions.php");

echo '<script src="'.$GLOBALS['SISED_URL'].'server-side/lib/phpjobscheduler/pjsfiles/functions.js" type="text/javascript"></script>';
  
echo '<strong><a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto">Scheduled tasks</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto&add=1">Add a NEW schedule</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_firepjs">Fire scheduled tasks</a> -
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_error">View error-logs</a></strong><br><br>';

include($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/error-logs.html");
?>