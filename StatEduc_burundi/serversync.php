<?php

/**
 *  Get the phone number that sent the SMS.
 */
$error = null;

if (isset($_POST['from']))
{
    $from = $_POST['from'];
}
else
{
$error = 'The from variable was not set';
 
}
 
/**
 * Get the SMS aka the message sent.
 */
if (isset($_POST['message']))
{
    $message = $_POST['message'];
}
else
{
    $error = 'The message variable was not set';
}
 
// Set success to false as the default success status
$success = false;
 
/**
 * Get the secret key set on SMSSync side
 * for matching on the server side.
 */
if (isset($_POST['secret']))
{
    $secret = $_POST['secret'];
}
 
 
/**
 * Get the timestamp of the SMS
 */
if (isset($_POST['sent_timestamp']))
{
    $sent_timestamp = $_POST['sent_timestamp'];
}

$sent_to = "";
/**
 * Get the phone number of the device SMSSync is
 * installed on.
 */
if (isset($_POST['sent_to']))
{
    $sent_to = $_POST['sent_to'];
}

$message_id = "";
/**
 * Get the unique message id
 */
if (isset($_POST['message_id']))
{
    $message_id = $_POST['message_id'];
}
 
/**
 * Now we have retrieved the data sent over by SMSSync
 * via HTTP. Next thing to do is to do something with
 * the data. Either echo it or write it to a file or even
 * store it in a database. This is entirely up to you.
 * After, return a JSON string back to SMSSync to know
 * if the web service received the message successfully or not.
 *
 * In this demo, we are just going to save the data
 * received into a text file.
 *
 */
if ((strlen($from) > 0) AND (strlen($message)))
{
    /* The screte key set here is 123456. Make sure you enter
     * that on SMSSync.
     */
    if (($secret == '123456'))
    {
        $success = true;
    }
    else
    {
        $error = "The secret value sent from the device does not match the one on the server";
    }
    // now let's write the info sent by SMSSync
    //to a file called test.txt
    $string = "From: ".$from."\n";
    $string .= "Message: ".$message."\n";
    $string .= "Timestamp: ".$sent_timestamp."\n";
    $string .= "Messages Id:" .$message_id."\n";
    $string .= "Sent to: ".$sent_to."\n\n\n";
    $myFile = "test.txt";
    $fh = fopen($myFile, 'a') or die("can't open file");
    @fwrite($fh, $string);
    @fclose($fh);
 
    /**
    * Now send a JSON formatted string to SMSSync to
     * acknowledge that the web service received the message
    */
   // echo json_encode(array("payload"=>array(
     //   "success"=>$success, "error" => $error)));
 
}
else
{
    //echo json_encode(array("payload"=>array(
    //    "success"=>$success, "error" => $error)));
}
 
/**
 * UnComment the code below to send an instant
 * reply as SMS to the user.
 *
 * This feature requires the "Get reply from server" checked on SMSSync.
 */
 if (isset($_GET['task']))
 {
    $m = "HeHeHeHe is ok";
    $f = "+221774906209";
    $s = true;
    $reply[0] = array("to" => $f, "message" => substr($message,9,5));
    $myFile = "task.txt";
    $fh = fopen($myFile, 'a') or die("can't open file");
    @fwrite($fh, $m."\n\n");
    @fclose($fh);
 
    echo json_encode(array("payload"=>array("success"=>$s,"task"=>"send","messages"=>array_values($reply))));
 }
?>
