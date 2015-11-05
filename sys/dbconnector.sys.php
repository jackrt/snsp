<?php
	/* 
		==================================================================
		SaTH Network Security Portal v1
		J.Turner 2015
		------------------------------------------------------------------
		***** Warning *****
		This page contains no user-servicable parts (only geeky ones).
		Do not fiddle without confirmation first!
		------------------------------------------------------------------
		File: 		dbconnector.sys.php				
		Version: 	1.0
		Created:	25/08/2015 (JRT)
		Modified:	27/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to connect to specific MySQL database based on site.
		==================================================================
	*/
	
	// Connect database based on current site.
	$servername = $siteconfig[$current_site]['sql']['servername'];
	$username = $siteconfig[$current_site]['sql']['username'];
	$password = $siteconfig[$current_site]['sql']['password'];
	$dbname = $siteconfig[$current_site]['sql']['dbname'];
	
	$conn = new mysqli($servername, $username, $password, $dbname);
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
?>