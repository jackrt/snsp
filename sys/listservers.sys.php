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
		File: 		listservers.sys.php				
		Version: 	1.0
		Created:	26/08/2015 (JRT)
		Modified:	27/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to build an array of sites and their components.
		==================================================================
	*/

	// Set up the arrays.
	$sites = array();
	$lan_servers = array();
	
	// Get all the sites from config and stuff them into an array.
	foreach ($siteconfig as $i) {
		$sites[] = array_search($i, $siteconfig);
	}	
	
	// If a site has been specified and it is listed, stuff specified site into a variable.
	if (isset($_GET['site']) && in_array($_GET['site'], $sites)) {
		$current_site = $_GET['site'];
	}
	// If not specified, use first site as default.
	else {
		$current_site = $sites[0];
	}
	
	// Loop through wired servers for specified site and detail the server plus it's
	// SQL table and stuff into an array.
	foreach ($siteconfig[$current_site]['wired'] as $i) {
		$l_getserver = array_search($i, $siteconfig[$current_site]['wired']);
		$l_gettable = $i;
		$lan_servers[$l_getserver] = $l_gettable;
	}
	// Loop through wireless SSIDs for specified site and detail the SSID plus it's
	// SQL table and stuff into an array.
	foreach ($siteconfig[$current_site]['wireless'] as $i) {
		$l_getssid = array_search($i, $siteconfig[$current_site]['wireless']);
		$l_getwlantable = $i;
		$wlan_ssids[$l_getssid] = $l_getwlantable;
	}
?>