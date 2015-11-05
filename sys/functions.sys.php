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
		File: 		functions.sys.php				
		Version: 	1.0
		Created:	13/10/2015 (JRT)
		Modified:	27/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to provide custom functions for processing data.
		==================================================================
	*/
	
	function echoarray($array) {
		// Function used to show array nicely
		echo '<pre>'; print_r($array); echo'</pre>';
	}
	
	function macformat($mac, $separator = '-') {
		// Function to format non-seperated MAC address with seperators
		return join($separator, str_split($mac, 2));
	}
		
	function macvalid($mac) {
		// 01:23:45:67:89:ab
		if (preg_match('/^([a-fA-F0-9]{2}:){5}[a-fA-F0-9]{2}$/', $mac)) {
			return true;
		}
		// 01-23-45-67-89-ab
		elseif (preg_match('/^([a-fA-F0-9]{2}\-){5}[a-fA-F0-9]{2}$/', $mac)) {
			return true;
		}
		// 0123456789ab
		elseif (preg_match('/^[a-fA-F0-9]{12}$/', $mac)) {
			return true;
		}
		// 0123.4567.89ab
		elseif (preg_match('/^([a-fA-F0-9]{4}\.){2}[a-fA-F0-9]{4}$/', $mac)) {
			return true;
		}
		else {
			return false;
		}
	}
?>