<?php
	// No user servicable parts in here (only geeky ones).
	// Used to display the Advanced Search Modal. 
	// -----------------------------------------------------
	// JRT - 25.08.15	
?>

	<div id="advSearchModal" class="modal fade" role="dialog">
		<div class="modal-dialog modal-lg">
			<form class="form-horizontal" method="post">
			<div class="modal-content">
				<div class="alert alert-info" role="alert"><b>Bong!</b> This part is still in development,
				it doesn't work at the moment.</div>
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Advanced Search</h4>
				</div>
				<div class="modal-body">
					<fieldset>
						<!-- Site options -->
						<div class="form-group" id="grp_site">
							<label class="col-md-2 control-label" for="grp_site">Site</label>
							<div class="col-md-8">
								<?php
								foreach ($sites as $i) {
									$l_sitecode = $i;
									$l_sitedesc = $siteconfig[$i]['desc'];

									echo '	<label class="checkbox-inline" for="grp_site-' . $l_sitecode . '">
											<input name="grp_site" id="grp_site-0" value="rsh" type="checkbox">
											' . $l_sitedesc . '
											</label>
										';
								}
								?>
							</div>
						</div>
						<!-- Network options -->
						<div class="form-group" id="grp_network">
							<label class="col-md-2 control-label" for="grp_net">Network</label>
							<div class="col-md-8"> 
								<label class="radio-inline" for="grp_net-lan">
								<input name="grp_net" id="grp_net-lan" value="lan" checked="checked" type="radio">
								Wired
								</label> 
								<label class="radio-inline" for="grp_net-wlan">
								<input name="grp_net" id="grp_net-wlan" value="wlan" type="radio">
								Wireless
								</label>
							</div>
						</div>
						<!-- Search for options -->
						<div class="form-group" id="grp_searchfor">
							<label class="col-md-2 control-label" for="grp_searchfor">Search in</label>
							<div class="col-md-8"> 
								<label class="radio-inline" for="grp_searchin-0">
								<input name="grp_searchfor" id="grp_searchfor-0" value="mac" checked="checked" type="radio">
								MAC Address
								</label> 
								<label class="radio-inline" for="grp_searchin-1">
								<input name="grp_searchfor" id="grp_searchfor-1" value="desc" type="radio">
								Description
								</label>
								<label class="radio-inline" for="grp_searchin-2">
								<input name="grp_searchfor" id="grp_searchfor-2" value="switch" type="radio">
								Switch
								</label>									
							</div>
						</div>
						<!-- Search terms -->
						<div class="form-group" id="searchinput">
							<label class="col-md-2 control-label" for="searchinput">Search Input</label>
							<div class="col-md-6">
								<input id="searchinput" name="searchinput" placeholder="Search..." class="form-control input-md" required="" type="search">
							</div>
						</div>
					</fieldset>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" id="search" name="search" >Search</button>
					<button type="button" class="btn btn-default" id="search" data-dismiss="modal">Close</button>
				</div>
			</div>
			</form>
		</div>
	</div>
</div>