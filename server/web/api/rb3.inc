<?php

function dbConnect() {

	//$env = "prod";
	$env = "test";
	
	if ($env == "prod")
	{
		$server="your production server hostname";
		$username="your production server username";
		$password="your production server password";
		$database="your production server database";
	}
	else
	{
		$server="your test server hostname";
		$username="your test server username";
		$password="your test server password";
		$database="your test server database";
	}
	
	mysql_connect($server,$username,$password);
	@mysql_select_db($database) or die( "Unable to select database");
}

function dbClose() {
 	mysql_close();
}

?>