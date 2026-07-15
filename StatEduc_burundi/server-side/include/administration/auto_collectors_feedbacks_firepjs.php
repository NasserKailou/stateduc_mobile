<?php  
$app_name = "phpJobScheduler";
$phpJobScheduler_version = "3.9";
// -------------------------------
include_once($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/functions.php");

echo '<strong><a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto">Scheduled tasks</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto&add=1">Add a NEW schedule</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_firepjs">Fire scheduled tasks</a> -
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_error">View error-logs</a></strong><br><br>';

include_once($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/phpjobscheduler.php");
// return image - used for html page img tag
if ( isset($_GET['return_image']) )
{
 header("Content-Type: image/gif");
 header("Content-Length: 49");
 echo pack('H*', '47494638396101000100910000000000ffffffffffff00000021f90405140002002c00000000010001000002025401003b');
}
?>