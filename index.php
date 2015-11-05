<!DOCTYPE html>
<html lang="en">
	<head>
		<title>SaTH Network Security Portal</title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="stylesheet" href="sys/css/bootstrap.min.css">
		<link rel="stylesheet" href="sys/css/spinner.css" type="text/css">
		<script src="sys/js/jquery-2.1.4.min.js"></script>
		<script src="sys/js/bootstrap.min.js"></script>
		<script src="sys/js/snsp.js"></script>
		<script>
			function refreshTable(){
				url = ("<?php
				// Set the array up.
				$l_querybuild = array();
				
				// Which page do we need?
				if (isset($_GET['details'])) {
					echo "sys/details.sys.php?";
				}
				elseif (isset($_GET['search'])) {
					echo "sys/searchresults.sys.php?";
				}
				elseif (isset($_GET['log'])) {
					if ($_GET['log'] == "wireless") {
						echo "sys/wlanlog.sys.php?";
					}
					elseif ($_GET['log'] == "wired") {
						echo "sys/lanlog.sys.php?";
					}
				}
				elseif ((!isset($_GET['log'])) and (!isset($_GET['details']))) {	
					echo "sys/lanlog.sys.php?";
				}
				
				// Build the HTTP query.
				if (isset($_GET['search']))		{ $l_querybuild['search'] = $_GET['search']; }
				if (isset($_GET['details'])) 	{ $l_querybuild['details'] = $_GET['details']; }
				if (isset($_GET['site'])) 		{ $l_querybuild['site'] = $_GET['site']; }
				if (isset($_GET['log']))		{ $l_querybuild['log'] = $_GET['log']; }
				if (isset($_GET['server'])) 	{ $l_querybuild['server'] = $_GET['server']; }
				if (isset($_GET['mac'])) 		{ $l_querybuild['mac'] = $_GET['mac']; }
				if (isset($_GET['ssid'])) 		{ $l_querybuild['ssid'] = $_GET['ssid']; }
				
				// Build the HTTP query.
				echo http_build_query($l_querybuild);
			?>");
			return url;
			}
			<?php 
			// Stop refresh timer if we've been passed a query or asked for a details page.
			if ((isset($_GET['search'])) or (isset($_GET['details']))) { echo "stopTimer();"; } 
			?>
		</script>
	</head>
	<body>
	<?php include 'sys/navbar.sys.php' ?>
		<div class="container">
			<?php
			if ((isset($_GET['search']) or (isset($_GET['details'])))) {
				// Don't do anything.
			}
			else {
				echo "
						<div class=\"checkbox\">
							<label><input type=\"checkbox\" id=\"autoupdate\" value=\"\" checked>Auto-update every 5 seconds</label>
						</div>		
					";
			}
			?>			
			<div id="responsecontainer"><div class="spinner-loader">Loading…</div></div>
		</div>
	</body>
</html>