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
		File: 		wlanlog.sys.php				
		Version: 	1.0
		Created:	25/08/2015 (JRT)
		Modified:	27/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Shows a list of devices authenticated on the WLAN network.
		==================================================================
	*/
	
	// Require these again as we're going to be loaded into a refreshed div.
	require 'snsp.config.php';
	require 'functions.sys.php';
	require 'listservers.sys.php';
	
	// Useful for debugging.
	//print_r(array_values($sites));
	//print_r($wlan_ssids);
	
	echo "<b>Wireless Logs</b> | ";
	
	// Get the site we are supposed to be viewing.
	if (isset($_GET['site']) && in_array($_GET['site'], $sites)) {
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	else {
		// No site defined, default to first site specified.
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	
	if (isset($_GET['ssid']) && in_array($_GET['ssid'], $wlan_ssids)) {
		echo " | <b>SSID:</b> " . $_GET['ssid'];
	}
	
	// Set up the first bit of the query (this should never change).
	$l_query_pfx = "SELECT username, ldapuserdescription, sathtagid, eaptype, macoui, certexpirydate, callingstationid, nasipaddress,
					nasidentifier, calledstationid, reply, ssid, debugrejectinformation, authdate FROM ";
	
	// Have we been passed an SSID to browse?  Check it's in the array...
	if (isset($_GET['ssid']) && array_key_exists($_GET['ssid'], $wlan_ssids)) {
		// Set that we've been passed an SSID name but not a query.
		$has_specified_ssid = True;
		$has_specified_search = False;
		$specified_ssid = $_GET['ssid'];
		$specified_table = $wlan_ssids[$specified_ssid];
		$l_query_sfx = " ORDER BY id DESC LIMIT 200";
		$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_table $l_query_sfx", "ssid" => $specified_ssid);
		echo " | <b>SSID:</b> " . $specified_ssid;
	}
	// Have we been passed a term to look for?
	elseif (isset($_GET['query'])) {
		// Set that we've not been passed a server name but a query instead.
		$has_specified_ssid = False;
		$has_specified_search = True;
		$cur_query = $_GET['query'];
		$l_query_sfx = " WHERE username LIKE lower(\"%" . $cur_query . "%\")
						AND authdate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 5";
		// We need to set the array up before we push some stuff onto it (it'll whinge otherwise)
		$sql_query = array();
		// Build the query for each of the servers.
		foreach ($wlan_ssids as $specified_ssid) {
			$l_ssidname = array_search($specified_ssid, $wlan_ssids);
			// Add both the query and the actual server into an array (we'll need it later).
			$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_table $l_query_sfx", "ssid" => $specified_ssid);
		}
		echo " | <b>MAC Query:</b> " . $cur_query;
	}
	// None of the above, show the page normally then.
	else {
		// Set that we've not received a server name or a query (bah, humbug).
		$has_specified_ssid = False;
		$has_specified_search = False;
		$l_query_sfx = " ORDER BY id DESC LIMIT 50";
		// We need to set the array up before we push some stuff onto it (it'll whinge otherwise)
		$sql_query = array();
		// Build the query for each of the servers.
		//$count = sizeof($lan_servers);
		foreach ($wlan_ssids as $specified_ssid) {
			$l_ssidname = array_search($specified_ssid, $wlan_ssids);
			// Add both the query and the actual server into an array (we'll need it later).
			$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_ssid $l_query_sfx", "ssid" => $l_ssidname);
		}
	}
	// Set up the array we're going to stuff table data into.
	$wlan_dataresults = array();
	// Replace some jargon and annoying characters as they come out the database.
	$l_repl_eschr = array(
							array('=28', '=29', '=3Cnone=3E', '=26', '=3F', '=27', '=2C', '=5C', '=8', '=2'), 
							array('(', ')', '', '&', '?', "'", ',', '\\', '\\', ')')
						);
	$l_repl_desc = array(array('Access-Accept', 'Access-Reject'), array('Accepted', 'Rejected'));
	$l_repl_escmac = array(array(':', '-'), array('', ''));

	// Fire up the database engines (brmmm)...
	require 'dbconnector.sys.php';
	
	// Loop through the tables...
	$i=0; // <-------------------- needed anymore??
	foreach ($sql_query as $cur_query) {
		//print_r ($cur_query);
		$result = $conn->query($sql_query[$i]['sqlquery']);	
		//echo $sql_query[$i]['sqlquery'];
		if($result->num_rows > 0) {		
			// Stuff info into array...
			while ($row = $result->fetch_assoc()) {
				$wlan_dataresults[] = array('decision' => str_replace($l_repl_desc[0], $l_repl_desc[1], $row['reply']),
											'username' => $row['username'],
											'addesc' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['ldapuserdescription']),
											'tagid' => $row['sathtagid'],
											'date' => $row["authdate"],
											'mac' => str_replace($l_repl_escmac[0], $l_repl_escmac[1], $row['callingstationid']),
											'macoui' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row["macoui"]),
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
	
	// Sort the array by newest date first.
	$sort_array = array();
	foreach ($wlan_dataresults as $record) {
		$sort_array[] = $record['date'];
	}
	array_multisort($sort_array, SORT_DESC, $wlan_dataresults);
		
	/* Normally disabled... useful for debugging.  */
	//print_r ($p_match_addesc);
	//print_r ($lan_dataresults);
	//print_r($sql_query);
		
	// Start building the table to display.
		echo "<br />
				<small><table class=\"table\">
					<thead>
						<tr>
							<th>Decision</th>
							<th>Username</th>
							<th>AD Description</th>
							<th>Date</th>
							<th>SSID</th>
							<th>Decision Info</th>
						</tr>
					</thead>
					<tbody>
			";
		// Get how many records are in the array and start the displaying process.
		$i=0;
		$count = sizeof($wlan_dataresults);
		while ( $i < $count ) {
			// Change the row colour via CSS depending on whether the decision was to 
			// accept or reject.
			if ( $wlan_dataresults[$i]['decision'] == "Rejected" ) {
				echo "<tr class=\"danger\">";
			}
			else {
				echo "<tr class=\"success\">";
			}
			// Stuff array record into table.
			echo "
								<td>". $wlan_dataresults[$i]['decision']. "</td>
								<td><a data-toggle=\"popover\" data-popover=\"true\" data-html=\"true\" tabindex=\"0\"
										title=\"<b>" . $wlan_dataresults[$i]['addesc'] . "</b>\" data-content=\"
										<b>Tag ID:</b> " . $wlan_dataresults[$i]['tagid'] . "<br />
										<b>MAC Address:</b> " . $wlan_dataresults[$i]['mac'] . "<br />
										<b>MAC Vendor:</b> " . $wlan_dataresults[$i]['macoui'] . "<br />
										<b>NASID:</b> " . $wlan_dataresults[$i]['nasidentifier'] . "<br />
										\">". $wlan_dataresults[$i]['username']. "</a></td>
								<td><a href=\"?site=". $current_site . "&details=". $wlan_dataresults[$i]['mac']. "\">" . $wlan_dataresults[$i]['addesc'] ."</a></td>
								<td>". $wlan_dataresults[$i]['date']. "</td>
								<td>". $wlan_dataresults[$i]['ssid']. "</td>
								<td>". $wlan_dataresults[$i]['decinfo']. "</td>
							</tr>
				";			
		$i++;
		}
		echo "			</tbody>
					</table></small>
			";
	
	// Close the SQL connection.
	$conn->close();
			
?>
<script>
	$(document).ready(function(){
		$('[data-toggle="popover"]').popover();   
	});
</script>