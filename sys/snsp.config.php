<?php
	// USER CONFIG
	// RADIUS site information is stuck here.
	// Default site is listed first.
	
	// LDAP CONFIGURATION =====================================================================
		
	// Configure Active Directory Domain Name, the Base DN of it's lookup and the username and
	// password it will use to perform it's lookup.
	
	$ldapconfig = array (
				"domain" => "SATH.nhs.uk",
				"basedn" => "ou=SATHNetwork,dc=SATH,dc=nhs,dc=uk",
				"username" => "LDAPQuery",
				"password" => "ld4Pqu3ry!"
			);

	// SITE CONFIGURATION =====================================================================		
			
	// Configure the sites to show, including their SQL connection details, RADIUS servers and
	// their respective tables.
			
	$siteconfig = array ( 
				"rsh" => array (
								"desc" => "Royal Shrewsbury Hospital",
								"sql" => array (
												"servername" => "rsh-radius-sql",
												"username" => "rad_readuser",
												"password" => "r4d1US87",
												"dbname" => "radius"
								),
								"wired" => array (
												"rsh-radius-lan0" => "postauth_lan0", 
												"rsh-radius-lan1" => "postauth_lan1",
												"rsh-radius-lan2" => "postauth_lan2",
												"rsh-radius-lan3" => "postauth_lan3"
								),
								"wireless" => array (
												"RSH_WiFi" => "postauth_RSH_WiFi",
												"SaTH_Guest" => "postauth_SaTH_Guest",
												"SBP_WiFi" => "postauth_SBP_WiFi"
								),
							),
				"prh" => array (
								"desc" => "Princess Royal Hospital",
								"sql" => array (
												"servername" => "prh-radius-sql",
												"username" => "rad_readuser",
												"password" => "r4d1US87",
												"dbname" => "radius"
								),
								"wired" => array (
												"prh-radius-lan0" => "postauth_lan0", 
												"prh-radius-lan1" => "postauth_lan1"
								),
								"wireless" => array (
												"PRH_WiFi" => "postauth_PRH_WiFi"
								),
							), 
	);
?>