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
		File: 		searchresults.sys.php				
		Version: 	1.0
		Created:	13/10/2015 (JRT)
		Modified:	19/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to display Search Results from a specified query.
		==================================================================
	*/
	
	// Debugging... 1 for on, 0 for off.
	$debug = 0;
	
	// Require these again as we're going to be loaded into a refreshed div.
	require 'snsp.config.php';
	require 'functions.sys.php';
	require 'listservers.sys.php';
	require 'dbconnector.sys.php';
	
	
	// Get the site we are supposed to be viewing.
	if (isset($_GET['site']) && in_array($_GET['site'], $sites)) {
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	else {
		// No site defined, default to first site specified.
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	
	// Set up the first bit of the LAN query.	
	$l_lan_query_pfx = 		"SELECT id, username, nasportid, ldapuserdescription, sathtagid, macoui, callingstationid, nasipaddress,
							nasidentifier, tunnelprivategroup, egressvlanid, reply, debugrejectinformation, authdate
							FROM (SELECT * FROM";
							
	// Set up the first bit of the WLAN query.						
	$l_wlan_query_pfx = 	"SELECT id, username, ldapuserdescription, sathtagid, eaptype, macoui, certexpirydate,
							callingstationid, nasipaddress, nasidentifier, calledstationid, reply, ssid, 
							debugrejectinformation, authdate FROM (SELECT * FROM";
								
	// Determine what we're searching for and where we're searching.
	// We want to be able to search for not just MAC in the future, but for now
	// that's what we're rolling with.
	
	if (isset($_GET['search'])) {
		// Stuff the search term into a variable.
		$l_searchterm = $_GET['search'];
		// Determine whether we've been passed a MAC address by looking at the formatting.
		if (macvalid($l_searchterm)) {
			// Stuff the MAC into a variable and format it.
			$l_device = $_GET['search'];
			$l_device_mac = macformat($l_device);
			// Set up the last bit of the SQL query.
			$l_query_sfx =	"USE INDEX(macindex) WHERE callingstationid LIKE lower (\"". $l_device_mac . "\")) AS mytable 
							WHERE mytable.authdate BETWEEN NOW() - INTERVAL 7 DAY AND NOW()
							ORDER BY id DESC LIMIT 10";
		}
		else {
			$l_device = $_GET['search'];
			// Obviously not a MAC then! Let's look at Tag ID, Description and Username instead. 
			$l_query_sfx =	"USE INDEX(macindex) WHERE sathtagid LIKE lower (\"%". $l_device . "%\") 
							OR ldapuserdescription LIKE lower (\"%". $l_device . "%\")
							OR username LIKE lower (\"%". $l_device . "%\")) AS mytable 
							WHERE mytable.authdate BETWEEN NOW() - INTERVAL 7 DAY AND NOW()
							ORDER BY id DESC LIMIT 10";
		}
	}
	
	/*
	if (isset($_GET['desc'])) {
		// Stuff the description into a variable.
		$l_desc = $_GET['desc'];
	}
	if (isset($_GET['switch'])) {
		// Stuff the switch into a variable.
		$l_switch = $_GET['switch'];
	}
	*/
	
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
		// Add both the query and the SSID into an array (we'll need it later).
		$sql_query_wlan[] = array("sqlquery" => "$l_wlan_query_pfx $specified_ssid $l_query_sfx", "ssid" => $l_ssidname);
	}
	
	// Replace some jargon and annoying characters as they come out the database.
	//$l_repl_eschr = array(array('=28', '=29', '=3Cnone=3E', '=26', '=3F', '=27'), array('(', ')', '', '&', '?', "'"));
	$l_repl_eschr = array(
						array('=28', '=29', '=3Cnone=3E', '=26', '=3F', '=27', '=2C', '=5C', '=8', '=2'), 
						array('(', ')', '', '&', '?', "'", ',', '\\', '\\', ')')
					);
	$l_repl_desc = array(array('Access-Accept', 'Access-Reject'), array('Accepted', 'Rejected'));
	$l_repl_escmac = array(array(':', '-', '.'), array('', '', ''));
	
	if ($debug == 1) { echoarray($sql_query_lan); }; // Debug details.
	if ($debug == 1) { echoarray($sql_query_wlan); }; // Debug details.
	
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
											'decinfo' => $row['debugrejectinformation'],
											'radbox' => $sql_query_lan[$i]['radbox']
											);
			}
		}
		$i++;	
	}
	
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
	
	if ($debug == 1) { echo '<p>LAN QUERY RESULTS</p>'; echoarray($sql_lan_results); }; // Debug details.
	if ($debug == 1) { echo '<p>WLAN QUERY RESULTS</p>'; echoarray($sql_wlan_results); }; // Debug details.
	
	// Count results from both arrays and suppress 0 results errors with a @... a bit naughty
	// but easier than going through a series of checks!
	@$l_results_total = count($sql_lan_results) + count($sql_wlan_results);
	
	// Determine whether it's a MAC we're searching for (display different message to user).
	if (isset($l_device_mac)) {
		echo " | Found ". $l_results_total ." result(s) for MAC address \"". $l_device ."\"";
	}
	else {
		echo " | Found ". $l_results_total ." result(s) for \"". $l_device ."\"";
	}
		
	// Start building the results table.
	if (isset($sql_lan_results)) {	
		echo "		<h4>LAN Results</h4>";
		
		echo "		<small><table class=\"table\">
						<thead>
							<tr>
								<th width=\"140\">Date</th>
								<th width=\"80\">Decision</th>
								<th width=\"100\">MAC Address</th>
								<th width=\"360\">AD Description</th>
								<th width=\"120\">Switch</th>
								<th width=\"50\">Port</th>
								<th width=\"100\">RADIUS Box</th>
								<th>Decision Info</th>
							</tr>
						</thead>
						<tbody>
			";
			
		// Get how many records are in the array and start the displaying process.
		$i = 0;
		$count = sizeof($sql_lan_results);
		while ($i < $count) {
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
								<td>". $sql_lan_results[$i]['date']. "</td>
								<td>". $sql_lan_results[$i]['decision']. "</td>
								<td><a data-toggle=\"popover\" data-popover=\"true\" data-html=\"true\" tabindex=\"0\" data-trigger=\"focus\"
										title=\"<b>" . $sql_lan_results[$i]['addesc'] . "</b>\" data-content=\"
										<b>Tag ID:</b> " . $sql_lan_results[$i]['tagid'] . "<br />
										<b>MAC Vendor:</b> " . $sql_lan_results[$i]['macoui'] . "</br />
										<b>Untagged VLAN</b>: " . $sql_lan_results[$i]['utgvlan'] . "<br />
										<b>Tagged VLAN</b>: " . $sql_lan_results[$i]['tgvlan'] . "<br />
										<b>Switch IP</b>: " . $sql_lan_results[$i]['switchip'] . "<br />
										<b>RADIUS Box</b>: " . $sql_lan_results[$i]['radbox'] . "<br />
										\">". $sql_lan_results[$i]['mac']. "</a></td>
								<td><a href=\"?site=". $current_site . "&details=". $sql_lan_results[$i]['mac']. "\">" . $sql_lan_results[$i]['addesc'] ."</a></td>
								<td>". $sql_lan_results[$i]['switch']. "</td>
								<td>". $sql_lan_results[$i]['port']. "</td>
								<td>". $sql_lan_results[$i]['radbox']. "</td>
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
		echo "		<h4>WLAN Results</h4>";
		
		echo "		<small><table class=\"table\">
						<thead>
							<tr>
								<th width=\"140\">Date</th>
								<th width=\"80\">Decision</th>
								<th width=\"100\">MAC Address</th>
								<th width=\"360\">AD Description</th>
								<th width=\"120\">Username</th>
								<th width=\"80\">SSID</th>
								<th width=\"100\">NAS ID</th>
								<th>Decision Info</th>
							</tr>
						</thead>
						<tbody>
			";
			
			// Get how many records are in the array and start the displaying process.
			$i = 0;
			$count = sizeof($sql_wlan_results);
			while ($i < $count) {
				// Change the row colour via CSS depending on whether the decision was to 
				// accept or reject.
				if ($sql_wlan_results[$i]['decision'] == "Rejected") {
					echo "<tr class=\"danger\">";
				}
				else {
					echo "<tr class=\"success\">";
				}
				// Stuff array record into table.
				echo "
									<td>". $sql_wlan_results[$i]['date']. "</td>
									<td>". $sql_wlan_results[$i]['decision']. "</td>
									<td><a data-toggle=\"popover\" data-popover=\"true\" data-html=\"true\" tabindex=\"0\" data-trigger=\"focus\"
										title=\"<b>" . $sql_wlan_results[$i]['addesc'] . "</b>\" data-content=\"
										<b>Tag ID:</b> " . $sql_wlan_results[$i]['tagid'] . "<br />
										<b>MAC Address:</b> " . $sql_wlan_results[$i]['mac'] . "<br />
										<b>MAC Vendor:</b> " . $sql_wlan_results[$i]['macoui'] . "<br />
										<b>NASID:</b> " . $sql_wlan_results[$i]['nasidentifier'] . "<br />
										\">". $sql_wlan_results[$i]['username']. "</a></td>
									<td><a href=\"?site=". $current_site . "&details=". $sql_wlan_results[$i]['mac']. "\">" . $sql_wlan_results[$i]['addesc'] ."</a></td>
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
<script>
	$(document).ready(function(){
		$('[data-toggle="popover"]').popover();   
	});
</script>