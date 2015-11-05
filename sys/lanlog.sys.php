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
		File: 		lanlog.sys.php				
		Version: 	1.0
		Created:	25/08/2015 (JRT)
		Modified:	27/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Shows a list of devices authenticated on the LAN network.
		==================================================================
	*/
	
	// Debugging... 1 for on, 0 for off.
	$debug = 0;

	// Require these again as we're going to be loaded into a refreshed div.
	require 'snsp.config.php';
	require 'functions.sys.php';
	require 'listservers.sys.php';
	
	// Useful for debugging.
	//if ($debug == 1) { echoarray($sites); };
	//if ($debug == 1) { echoarray($lan_servers); };
	
	
	// Get the site we are supposed to be viewing.
	if (isset($_GET['site']) && in_array($_GET['site'], $sites)) {
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	else {
		// No site defined, default to first site specified.
		echo "<b>Site:</b> " . $siteconfig[$current_site]['desc'];
	}
	
	// Set up the first bit of the query (this should never change).
	$l_query_pfx = "SELECT username, nasportid, ldapuserdescription, sathtagid, macoui, nasipaddress,
					nasidentifier, tunnelprivategroup, egressvlanid, reply, debugrejectinformation, authdate
					FROM ";	
	// Have we been passed a server to browse?  Check it's in the array...
	if (isset($_GET['server']) && array_key_exists($_GET['server'], $lan_servers)) {
		// Set that we've been passed a server name but not a query.
		//$has_specified_server = True;  <----- no longer needed ***
		//$has_specified_search = False; <----- no longer needed ***
		$specified_server = $_GET['server'];
		$specified_table = $lan_servers[$specified_server];
		$l_query_sfx = " ORDER BY id DESC LIMIT 200";
		$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_table $l_query_sfx", "radbox" => $specified_server);
		echo " | <b>Server:</b> " . $specified_server;
	}
	// Have we been passed a term to look for?
	elseif (isset($_GET['query'])) {
		// Set that we've not been passed a server name but a query instead.
		//$has_specified_server = False; <----- no longer needed ***
		//$has_specified_search = True; <----- no longer needed ***
		$cur_query = $_GET['query'];
		$l_query_sfx = " WHERE username LIKE lower(\"%" . $cur_query . "%\")
						AND authdate >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ORDER BY id DESC LIMIT 5";
		// We need to set the array up before we push some stuff onto it (it'll whinge otherwise)
		$sql_query = array();
		// Build the query for each of the servers.
		foreach ($lan_servers as $specified_server) {
			$l_servername = array_search($specified_server, $lan_servers);
			// Add both the query and the actual server into an array (we'll need it later).
			$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_server $l_query_sfx", "radbox" => $l_servername);
		}
		echo " | <b>MAC Query:</b> " . $cur_query;
	}
	// None of the above, show the page normally then.
	else {
		// Set that we've not received a server name or a query (bah, humbug).
		//$has_specified_server = False; <----- no longer needed ***
		//$has_specified_search = False; <----- no longer needed ***
		$l_query_sfx = " ORDER BY id DESC LIMIT 50";
		// We need to set the array up before we push some stuff onto it (it'll whinge otherwise)
		$sql_query = array();
		// Build the query for each of the servers.
		//$count = sizeof($lan_servers);
		foreach ($lan_servers as $specified_server) {
		$l_servername = array_search($specified_server, $lan_servers);
		// Add both the query and the actual server into an array (we'll need it later).
		$sql_query[] = array("sqlquery" => "$l_query_pfx $specified_server $l_query_sfx", "radbox" => $l_servername);
		}
	}
	// Set up the array we're going to stuff table data into.
	$lan_dataresults = array();
	// Replace some jargon and annoying characters as they come out the database.
	$l_repl_eschr = array(array('=28', '=29', '=3Cnone=3E', '=26', '=3F', '=27'), array('(', ')', '', '&', '?', "'"));
	$l_repl_desc = array(array('Access-Accept', 'Access-Reject'), array('Accepted', 'Rejected'));

	// Fire up the database engines (brmmm)...
	require 'dbconnector.sys.php';
	
	// Loop through the tables...
	$i=0;
	foreach ($sql_query as $cur_query) {
		$result = $conn->query($sql_query[$i]['sqlquery']);	
		if($result->num_rows > 0) {		
			// Stuff info into array...
			while ($row = $result->fetch_assoc()) {
				$lan_dataresults[] = array('decision' => str_replace($l_repl_desc[0], $l_repl_desc[1], $row['reply']),
											'mac' => $row['username'],
											'macoui' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['macoui']),
											'addesc' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['ldapuserdescription']),
											'tagid' => $row['sathtagid'],
											'date' => $row["authdate"],
											'switch' => str_replace($l_repl_eschr[0], $l_repl_eschr[1], $row['nasidentifier']),
											'switchip' => $row['nasipaddress'],
											'port' => $row['nasportid'],
											'utgvlan' => $row['tunnelprivategroup'],
											'tgvlan' => $row['egressvlanid'],
											'decinfo' => $row['debugrejectinformation'],
											'radbox' => $sql_query[$i]['radbox']
											);
			}
		}
		$i++;
	}
	
	// Sort the array by newest date first.
	$sort_array = array();
	foreach ($lan_dataresults as $record) {
		$sort_array[] = $record['date'];
	}
	array_multisort($sort_array, SORT_DESC, $lan_dataresults);
		
	/* Normally disabled... useful for debugging.  */
	//if ($debug == 1) { echoarray $p_match_addesc); };
	if ($debug == 1) { echoarray($lan_dataresults); };
	if ($debug == 1) { echoarray($sql_query); };
		
	// Start building the table to display.
		echo "<br />
				<small><table class=\"table\">
					<thead>
						<tr>
							<th>Decision</th>
							<th>MAC Address</th>
							<th>AD Description</th>
							<th>Date</th>
							<th>Switch</th>
							<th>Port</th>
							<th>Decision Info</th>
						</tr>
					</thead>
					<tbody>
			";
		// Get how many records are in the array and start the displaying process.
		$i=0;
		$count = sizeof($lan_dataresults);
		while ( $i < $count ) {
			// Change the row colour via CSS depending on whether the decision was to 
			// accept or reject.
			if ( $lan_dataresults[$i]['decision'] == "Rejected" ) {
				echo "<tr class=\"danger\">";
			}
			else {
				echo "<tr class=\"success\">";
			}
			// Stuff array record into table.
			echo "
								<td>". $lan_dataresults[$i]['decision']. "</td>
								<td><a data-toggle=\"popover\" data-popover=\"true\" data-html=\"true\" tabindex=\"0\" data-trigger=\"focus\"
										title=\"<b>" . $lan_dataresults[$i]['addesc'] . "</b>\" data-content=\"
										<b>Tag ID:</b> " . $lan_dataresults[$i]['tagid'] . "<br />
										<b>MAC Vendor:</b> " . $lan_dataresults[$i]['macoui'] . "</br />
										<b>Untagged VLAN</b>: " . $lan_dataresults[$i]['utgvlan'] . "<br />
										<b>Tagged VLAN</b>: " . $lan_dataresults[$i]['tgvlan'] . "<br />
										<b>Switch IP</b>: " . $lan_dataresults[$i]['switchip'] . "<br />
										<b>RADIUS Box</b>: " . $lan_dataresults[$i]['radbox'] . "<br />
										\">". $lan_dataresults[$i]['mac']. "</a></td>
								<td><a href=\"?site=". $current_site . "&details=". $lan_dataresults[$i]['mac']. "\">" . $lan_dataresults[$i]['addesc'] ."</a></td>
								<td>". $lan_dataresults[$i]['date']. "</td>
								<td>". $lan_dataresults[$i]['switch']. "</td>
								<td>". $lan_dataresults[$i]['port']. "</td>
								<td>". $lan_dataresults[$i]['decinfo']. "</td>
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