<?php
$app_name = "phpJobScheduler";
$phpJobScheduler_version = "3.9";   
// -------------------------------
include_once($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/functions.php");  
//update_db(); // check database is up-to-date, if not add required tables
//include($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/header.html");

if (isset($_POST["sch_task"])) {
  updateTaskSchedule_db($_POST["sch_task"], $_POST["sch_task_period"]);
}

echo '<script src="'.$GLOBALS['SISED_URL'].'server-side/lib/phpjobscheduler/pjsfiles/functions.js" type="text/javascript"></script>';

echo '<strong><a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto">Scheduled tasks</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto&add=1">Add a NEW schedule</a> - 
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_firepjs">Fire scheduled tasks</a> -
<a href="'.$GLOBALS['SISED_URL'].'administration.php?val=feedbacks_auto_error">View error-logs</a></strong><br><br>';

if (isset($_GET['add'])) include($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/add-modify.html");
else include($GLOBALS['SISED_PATH_LIB']."phpjobscheduler/pjsfiles/main.html");
?>