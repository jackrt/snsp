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
		File: 		navbar.sys.php				
		Version: 	1.0
		Created:	25/08/2015 (JRT)
		Modified:	28/10/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Builds the navigation bar on all pages.
		==================================================================
	*/
?>

<nav class="navbar navbar-default">
	<div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
		<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>
		<a class="navbar-brand" href="index.php">SaTH Network Security Portal</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
	<ul class="nav navbar-nav">
	    <li class="dropdown active">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">Logs <span class="caret"></span></a>
			<ul class="dropdown-menu">
			<?php
			// Get some stuff we need to get the sites and servers.
			require 'snsp.config.php';
			require 'listservers.sys.php';
			
			// Get the sites out the config file, and list them in our nav bar.
			foreach ($sites as $i) {
				$l_sitecode = $i;
				$l_sitedesc = $siteconfig[$i]['desc'];
				echo "
						<li class=\"dropdown-header\">$l_sitedesc</li>
						<li><a href=\"?site=$l_sitecode\">View LAN Logs</a></li>
						<li><a href=\"?log=wireless&site=$l_sitecode\">View Wireless Logs</a></li>
						<li role=\"separator\" class=\"divider\"></li>
					";
			}
			// Get out servers for the current site.
			if (isset ($_GET['log']) && ($_GET['log'] == 'wireless')) {
				echo "<li class=\"dropdown-header\">Current SSIDs</li>";
				foreach ($wlan_ssids as $i) {
					$l_ssid = array_search($i, $wlan_ssids);
					echo "<li><a href=\"?site=" . $current_site . "&log=wireless&ssid=" . $l_ssid . "\">". $l_ssid ." </li>";
				}
				echo "<li><a href=\"?\">All SSIDs</a></li>";
			}
			else {
				echo "<li class=\"dropdown-header\">Current Servers</li>";
				foreach ($lan_servers as $i) {
					$l_servername = array_search($i, $lan_servers);
					echo "<li><a href=\"?site=" . $current_site . "&server=" . $l_servername . "\">". $l_servername ." </li>";
				}
				echo "<li><a href=\"?\">All Servers</a></li>";
			}
			?>
			<li role="separator" class="divider"></li>
			<li><a href="#advSearchModal" role="button" data-toggle="modal" id="advsearch">Advanced Search</a></li>
			</ul>
        </li>
		 
        <li class=""><a href="">Config <span class="sr-only"></span></a></li>
        
      </ul>
      <form class="navbar-form navbar-left" role="search" action="" method="get">
        <div class="form-group">
		  <input type="hidden" name="site" value="<?php echo $current_site; ?>" />
          <input type="text" class="form-control" name="search" id="query" placeholder="MAC/Tag/Description..." />
        </div>
        <button type="submit" class="btn btn-default">Search</button>
      </form>
		
		<img src="img/sath_logo.png" align="right" />
  </div><!-- /.container-fluid -->
</nav>
<?php require 'advsearch.sys.php'; ?>
