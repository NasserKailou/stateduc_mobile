<?php

	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}

   	echo "Start";
	$file_handle = fopen("stateduc_men_fer_db.sql", "r");
	$fh = fopen("stateduc_men_fer_db_upper.sql", 'a');
	while (!feof($file_handle)) {
	   $line = fgets($file_handle);
	   $line = (startsWith($line, "INSERT INTO"))?strtoupper($line):$line;
	   $line = (startsWith($line, "CREATE TABLE"))?strtoupper($line):$line;
	   $line = (startsWith($line, "ALTER TABLE"))?strtoupper($line):$line;
	   $line = (startsWith($line, "  ADD CONSTRAINT"))?strtoupper($line):$line;
	   @fwrite($fh, $line);   
	   echo ".";
	}
	@fclose($fh);	
   	echo "End";
	fclose($file_handle);

?>