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
		File: 		details.sys.php				
		Version: 	1.0
		Created:	14/09/2015 (JRT)
		Modified:	05/11/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to display detailed information of a particular client.
		==================================================================
	*/
	
	// Debugging... 1 for on, 0 for off.
	$debug = 0;
	
	// Require these again as we're going to be loaded into a div.
	require 'snsp.config.php';
	require 'functions.sys.php';
	require 'listservers.sys.php';
	require 'adconnector.sys.php';
	require 'dbconnector.sys.php';
	
	
	// Get the device ID passed from GET.
	$l_device = $_GET['details'];
	$l_device_mac = macformat($l_device);
	
	
	if ($debug == 1) { echo '<p>QUERY BUILD START '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	
	// Set up the first bit of the LAN query.	
	$l_lan_query_pfx = 		"SELECT id, username, nasportid, ldapuserdescription, sathtagid, macoui, callingstationid, nasipaddress,
							nasidentifier, tunnelprivategroup, egressvlanid, reply, debugrejectinformation, authdate
							FROM (SELECT * FROM";
							
	// Set up the first bit of the WLAN query.						
	$l_wlan_query_pfx = 	"SELECT id, username, ldapuserdescription, sathtagid, eaptype, macoui, certexpirydate,
							callingstationid, nasipaddress, nasidentifier, calledstationid, reply, ssid, 
							debugrejectinformation, authdate FROM (SELECT * FROM";

	// ..and the last bit.						
	$l_query_sfx =			"USE INDEX(macindex) WHERE callingstationid='$l_device_mac') AS mytable 
							WHERE mytable.authdate BETWEEN NOW() - INTERVAL 7 DAY AND NOW()
							ORDER BY id DESC LIMIT 10";
	
	// Set up the query arrays...
	$sql_query_lan = array();
	$sql_query_wlan = array();
	
	// ...and the queries for LAN...
	foreach ($lan_servers as $specified_server) {
		$l_servername = array_search($specified_server, $lan_servers);
		// Add both the query and the RADIUS box into an array (we'll need it later).
		$sql_query_lan[] = array("sqlquery" => "$l_lan_query_pfx $specified_server $l_query_sfx", "radbox" => $l_servername);
	}
	// ...and WLAN.
	foreach ($wlan_ssids as $specified_ssid) {
		$l_ssidname = array_search($specified_ssid, $wlan_ssids);
		// Add both the query and the actual server into an array (we'll need it later).
		$sql_query_wlan[] = array("sqlquery" => "$l_wlan_query_pfx $specified_ssid $l_query_sfx", "ssid" => $l_ssidname);
	}
	
	if ($debug == 1) { echoarray($sql_query_lan); }; // Debug details.
	if ($debug == 1) { echoarray($sql_query_wlan); }; // Debug details.
	
	if ($debug == 1) { echo '<p>QUERY BUILD END '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	
	// Replace some jargon and annoying characters as they come out the database.
	$l_repl_eschr = array(array('=28', '=29', '=26', '=3F', '=27', '=3C', '=3E'), array('(', ')', '&', '?', "'", '', ''));
	$l_repl_desc = array(array('Access-Accept', 'Access-Reject'), array('Accepted', 'Rejected'));
	$l_repl_escmac = array(array(':', '-', '.'), array('', '', ''));
	
	//$sql_results = array();    ** not needed **
		
	if ($debug == 1) { echo '<p>LAN QUERY START '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	
	// Set up the LAN loop to return results and stuff into array.
	$i = 0;
	foreach ($sql_query_lan as $cur_query) {
		$result = $conn->query($sql_query_lan[$i]['sqlquery']);
		if ($result->num_rows > 0) {
			while ($row = $result->fetch_assoc()) {
				$sql_lan_results[] = array(	'id' => $row['id'],
											'decision' => str_replace($l_repl_desc[0], $l_repl_desc[1], $row['reply']),
											'mac' => $row['username'],
											'macoui' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['macoui']),
											'callingstationid' => $row['callingstationid'],
											'addesc' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['ldapuserdescription']),
											'tagid' => $row['sathtagid'],
											'date' => $row["authdate"],
											'switch' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['nasidentifier']),
											'switchip' => $row['nasipaddress'],
											'port' => $row['nasportid'],
											'utgvlan' => $row['tunnelprivategroup'],
											'tgvlan' => $row['egressvlanid'],
											'decinfo' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['debugrejectinformation']),
											'radbox' => $sql_query_lan[$i]['radbox']
											);
			}
		}
		$i++;	
	}
	if ($debug == 1) { echo '<p>LAN QUERY END '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	if ($debug == 1) { echo '<p>WLAN QUERY START '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	
	// Set up the WLAN loop to return results and stuff into array.
	$i = 0;
	foreach ($sql_query_wlan as $cur_query) {
		$result = $conn->query($sql_query_wlan[$i]['sqlquery']);
		if ($result->num_rows > 0) {
			// Stuff info into array...
			while ($row = $result->fetch_assoc()) {
				$sql_wlan_results[] = array('id' => $row['id'],
											'decision' => str_replace($l_repl_desc[0], $l_repl_desc[1], $row['reply']),
											'username' => $row['username'],
											'addesc' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['ldapuserdescription']),
											'tagid' => $row['sathtagid'],
											'date' => $row["authdate"],
											'mac' => str_replace($l_repl_escmac[0], $l_repl_escmac[1], $row['callingstationid']),
											'macoui' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row["macoui"]),
											'callingstationid' => $row['callingstationid'],
											'eaptype' => $row['eaptype'],
											'certexpirydate' => $row['certexpirydate'],
											'ssid' => $row['ssid'],
											'nasidentifier' => $row['nasidentifier'],
											'decinfo' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['debugrejectinformation']),
											);
			}
		}
		$i++;
	}
	
	if ($debug == 1) { echo '<p>WLAN QUERY END '; echo date('g:i:s'); echo '</p>'; }; // Debug details.
	if ($debug == 1) { echo '<p>LAN QUERY RESULTS</p>'; echoarray($sql_lan_results); }; // Debug details.
	if ($debug == 1) { echo '<p>WLAN QUERY RESULTS</p>'; echoarray($sql_wlan_results); }; // Debug details.
	
	// Sort the LAN array by newest date first.
	$sort_lan_array = array();
	if (isset($sql_lan_results)) {
		foreach ($sql_lan_results as $record) {
			$sort_lan_array[] = $record['date'];
		}
		array_multisort($sort_lan_array, SORT_DESC, $sql_lan_results);
	}
	
	// Sort the WLAN array by newest date first.
	$sort_wlan_array = array();
	if (isset($sql_wlan_results)) {
		foreach ($sql_wlan_results as $record) {
			$sort_wlan_array[] = $record['date'];
		}
		array_multisort($sort_wlan_array, SORT_DESC, $sql_wlan_results);
	}
	
	// A bit of trickery to return certain values from the WLAN array if the
	// required values aren't in the LAN array.
	if (isset($sql_lan_results)) {
		$mac_vendor = $sql_lan_results[0]['macoui'];
	}
	elseif (!isset($mac_vendor) && (isset($sql_lan_results))) {
		$mac_vendor = $sql_wlan_results[0]['macoui'];
	}
	
	// Get the Tag ID from the LAN results.  If it's not there, get it from WLAN instead.
	// If it's still not there, set it to none and be done.
	if (isset($sql_lan_results[0]['tagid'])) {
		$tagid = $sql_lan_results[0]['tagid'];
	}
	elseif ((!isset($sql_lan_results[0]['tagid'])) and (isset($sql_wlan_results[0]['tagid']))) {
		$tagid = $sql_wlan_results[0]['tagid'];
	}
	if (!isset($tagid) or ($tagid == "")) {
		$tagid = "none";
	}

	// Open the AD connection up.
	ad_start();
	
	// Determine which properties we want to return from the AD query and then set it off finding.
	$keep = array('cn', 'displayname', 'department', 'physicaldeliveryofficename', 'memberof', 'description', 'useraccountcontrol');
	$ldap_results = ad_findattributes($l_device, $keep);
	$ldap_result_usergroups = ad_usergroups($l_device);
	
	if ($debug == 1) { echoarray($ldap_results); }; // Debug details.
	
	// Close the AD connection
	ad_end();
	
	// Remove DN tags and replace with something a bit more friendly.
	$l_rgx_ou = array(array('/(CN=)\w+,|(OU=)|(,DC=[^;]*)/', '/,/'), array(' ', ' <b><</b>'));
	$l_rgx_memberof = array(array('/(CN=)|(,OU=[^;]*)/'), array(''));
	$ou = preg_replace($l_rgx_ou[0], $l_rgx_ou[1], $ldap_results['dn']);
	
	// Changed this slightly so it gets the PrimaryGroupID from the user aswell as it's groups.  For
	// some reason the Primary Group is stored differently in AD - *shrugs* (JRT 05/11/15).
	// Check to see if user is part of any groups.
	/*
	if (isset($ldap_results['memberof']['count'])) {
		$i = 0;
		$memberof = null;
		// Loop through the groups and append to string.
		while ($i < $ldap_results['memberof']['count']) {
			$memberof .= preg_replace($l_rgx_memberof[0], $l_rgx_memberof[1], $ldap_results['memberof'][$i]);	
			// Add a new line in between the groups to make it look fancy.
			if ($i < $ldap_results['memberof']['count'] - 1 ) {
				$memberof .= '<br />';
			}
			$i++;
		}
	}
	else {
		// Not part of any groups, return none.
		$memberof = 'none';
	}
	*/

	if (isset($ldap_result_usergroups['count']) and ($ldap_result_usergroups['count'] > 0)) {
		$i = 0;
		$memberof = null;
		// Loop through the groups and append to string.
		while ($i < $ldap_result_usergroups['count']) {
			$memberof .= preg_replace($l_rgx_memberof[0], $l_rgx_memberof[1], $ldap_result_usergroups[$i]);	
			// Add a new line in between the groups to make it look fancy.
			if ($i < $ldap_result_usergroups['count'] - 1 ) {
				$memberof .= '<br />';
			}
			$i++;
		}
	}
	else {
		// Not part of any groups, return none.
		$memberof = 'none';
	}	
	
	/* User account control - this bit looks at a value for the user and determines whether the
	   account is locked/disabled or bad.
		66048 - Enabled - Account OK
		66050 - Disabled - Account OK
		66080 - Enabled - Account Bad (not required)
		66082 - Disabled - Account Bad (not required) 
	*/
	
	$uac = intval($ldap_results['useraccountcontrol'][0]);
	
	if ($uac == 66048) {
		$uac_result = '<p class="text-success"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> Enabled (Account OK)</p>';
	}
	elseif ($uac == 66050) {
		$uac_result = '<p class="text-warning"><span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true"></span> Disabled (Account OK)</p>';
	}
	elseif ($uac == 66080) {
		$uac_result = '<p class="text-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Enabled (Bad Account)</p>';
	}
	elseif ($uac == 66082) {
		$uac_result = '<p class="text-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Disabled, (Bad Account)</p>';
	}
	else {
		$uac_result = '<p class="text-danger"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span> Account Unrecognised</p>';
	}	
	
	// If the LDAP query doesn't return some values, create them and make the value blank.
	if (!isset($ldap_results['department'][0])) {
		$ldap_results['department'][0] = "none";
	}
	if (!isset($ldap_results['physicaldeliveryofficename'][0])) {
		$ldap_results['physicaldeliveryofficename'][0] = "none";
	}
	
	
	// And now time to draw the tables.
	
	echo "		<h4>Details for device</h4>";
	
	echo "		<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>MAC Address:</b></div>
					<div class=\"col-md-3\">". $ldap_results['cn'][0] ."</div>
					<div class=\"col-md-1\"></div>
					<div class=\"col-md-2\" align=\"right\"><b>MAC Vendor:</b></div>
					<div class=\"col-md-3\">". $mac_vendor ."</div>					
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>AD Description:</b></div>
					<div class=\"col-md-6\">". $ldap_results['description'][0] ."</div>	
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>Site:</b></div>
					<div class=\"col-md-3\">". $ldap_results['physicaldeliveryofficename'][0] ."</div>
					<div class=\"col-md-1\"></div>
					<div class=\"col-md-2\" align=\"right\"><b>Department:</b></div>
					<div class=\"col-md-3\">". $ldap_results['department'][0] ."</div>
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>SaTH Tag ID:</b></div>
					<div class=\"col-md-6\">". $tagid ."</div>
				</div>				
				<br />
		";
	
	/* echo "		<h4>Device last seen</h4>";
	
	echo "		<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>Date:</b></div>
					<div class=\"col-md-3\">2015-00-00 12:00:00</div>
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>Switch:</b></div>
					<div class=\"col-md-3\">Cab-00-5406-1 (192.168.0.0)</div>
					<div class=\"col-md-1\"></div>
					<div class=\"col-md-2\" align=\"right\"><b>Port:</b></div>
					<div class=\"col-md-1\">A1</div>
					<div class=\"col-md-1\" align=\"right\"><b>NAS Port:</b></div>
					<div class=\"col-md-1\">1</div>
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>Decision:</b></div>
					<div class=\"col-md-3\">Rejected (User Account/UAC Wrong)</div>			
				</div>
				<br />
		";
	*/
	
	echo "		<h4>AD Information</h4>";
		

	
	echo "		<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>OU:</b></div>
					<div class=\"col-md-8\">". $ou ."</div>
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>MBA Groups:</b></div>
					<div class=\"col-md-4\">". $memberof ."</div>
					<div class=\"col-md-1\"></div>
				</div>
				<div class=\"row\">
					<div class=\"col-md-2\" align=\"right\"><b>Account State:</b></div>
					<div class=\"col-md-3\">". $uac_result ."</div>
				</div>
				<br />
		";
	
	if (isset($sql_lan_results)) {	
		echo "		<h4>Last 10 LAN Authentications</h4>";
		
		echo "		<small><table class=\"table\">
						<thead>
							<tr>
								<th>Decision</th>
								<th>Date</th>
								<th>Switch</th>
								<th>RADIUS Box</th>
								<th>Port</th>
								<th>Decision Info</th>
							</tr>
						</thead>
						<tbody>
			";
			
		// Get how many records are in the array and start the displaying process.
		$i=0;
		$count = sizeof($sql_lan_results);
		while (($i < $count) and ($i < 10)) {
			// Change the row colour via CSS depending on whether the decision was to 
			// accept or reject.
			if ( $sql_lan_results[$i]['decision'] == "Rejected" ) {
				echo "<tr class=\"danger\">";
			}
			else {
				echo "<tr class=\"success\">";
			}
			// Stuff array record into table.
			echo "
								<td>". $sql_lan_results[$i]['decision']. "</td>
								<td>". $sql_lan_results[$i]['date']. "</td>
								<td>". $sql_lan_results[$i]['switch']. "</td>
								<td>". $sql_lan_results[$i]['radbox']. "</td>
								<td>". $sql_lan_results[$i]['port']. "</td>
								<td>". $sql_lan_results[$i]['decinfo']. "</td>
							</tr>
				";			
		$i++;
		}
		echo "			</tbody>
					</table></small><br />
			";	
	}
		
		
	
	if (isset($sql_wlan_results)) {			
		echo "		<h4>Last 10 WLAN Authentications</h4>";
		
		echo "		<small><table class=\"table\">
						<thead>
							<tr>
								<th>Decision</th>
								<th>Date</th>
								<th>Username</th>
								<th>SSID</th>
								<th>NAS ID</th>
								<th>Decision Info</th>
							</tr>
						</thead>
						<tbody>
			";
			
			// Get how many records are in the array and start the displaying process.
			$i = 0;
			$count = sizeof($sql_wlan_results);
			while (($i < $count) and ($i < 10)) {
				// Change the row colour via CSS depending on whether the decision was to 
				// accept or reject.
				if ( $sql_wlan_results[$i]['decision'] == "Rejected" ) {
					echo "<tr class=\"danger\">";
				}
				else {
					echo "<tr class=\"success\">";
				}
				// Stuff array record into table.
				echo "
									<td>". $sql_wlan_results[$i]['decision']. "</td>
									<td>". $sql_wlan_results[$i]['date']. "</td>
									<td>". $sql_wlan_results[$i]['username']. "</td>
									<td>". $sql_wlan_results[$i]['ssid']. "</td>
									<td>". $sql_wlan_results[$i]['nasidentifier']. "</td>
									<td>". $sql_wlan_results[$i]['decinfo']. "</td>
								</tr>
					";			
			$i++;
			}
			echo "			</tbody>
						</table></small>
				";
	}				
			
?>
