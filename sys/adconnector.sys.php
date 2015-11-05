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
		File: 		adconnector.sys.php				
		Version: 	1.0
		Created:	12/10/2015 (JRT)
		Modified:	05/11/2015 (JRT)
		------------------------------------------------------------------
		Description/Details:
		Used to provide AD connectivity functions and pull attributes.
		==================================================================
	*/

	// Contains modified version of Sam J Levy's LDAP connection scripts. 
	// (samjlevy.com)

	$ldap_host = $ldapconfig['domain'];
	$ldap_user = $ldapconfig['username'];
	$ldap_pwd = $ldapconfig['password'];
	$ldap_basedn = $ldapconfig['basedn'];
	
	function ad_start() {
		global $adconn, $ldap_host, $ldap_user, $ldap_pwd;
		if(isset($adconn)) die('Error, LDAP connection already established');
	 
		// Connect to AD
		$adconn = ldap_connect($ldap_host) or die('Error connecting to LDAP');
		ldap_set_option($adconn, LDAP_OPT_PROTOCOL_VERSION, 3);
		@ldap_bind($adconn, $ldap_user, $ldap_pwd) or die('Error binding to LDAP: '.ldap_error($adconn));
		return true;
	}

	function ad_end() {
		global $adconn;
		if(!isset($adconn)) die('Error, no LDAP connection established');
	 
		// Close existing LDAP connection
		ldap_unbind($adconn);
	}

	function ad_findattributes($user,$keep=false) {
		// Find attributes of AD user based on their username.
		
		global $adconn, $ldap_basedn;
		if (!isset($adconn)) die('Error, no LDAP connection established');
		if(empty($user)) die('Error, no LDAP user specified');
		
		// Query user attributes
		$results = ldap_search($adconn, $ldap_basedn, '(cn=' . $user . ')', $keep) or die('Error searching LDAP: '.ldap_error($adconn));
		$attributes = ldap_get_entries($adconn, $results);
		
		// Return attributes list
		return $attributes[0];
	}


	function ad_attributes($user,$keep=false) {
		global $adconn;
		if(!isset($adconn)) die('Error, no LDAP connection established');
		if(empty($user)) die('Error, no LDAP user specified');
	 
		// Query user attributes
		$results = ldap_search($adconn,$user,'sn=*',$keep) or die('Error searching LDAP: '.ldap_error($adconn));
		$attributes = ldap_get_entries($adconn, $results);
	 
		// Return attributes list
		return $attributes[0];
	}

	function ad_members($group) {
		global $adconn;
		if(!isset($adconn)) die('Error, no LDAP connection established');
		if(empty($group)) die('Error, no LDAP group specified');
	 
		// Query group members
		$results = ldap_search($adconn,$group,'cn=*',array('member')) or die('Error searching LDAP: '.ldap_error($adconn));
		$members = ldap_get_entries($adconn, $results);
	 
		if(!isset($members[0]['member'])) return false;
	 
		// Remove 'count' element from array
		array_shift($members[0]['member']);
	 
		// Return member list
		return $members[0]['member'];
	}
	
	// Added this to enable returning Primary Groups along with the groups the user is a member of.
	// LDAP/AD creators (in their infinite wisdom) didn't dump them in the MemberOf property aswell,
	// so we need to take some extra steps. (JRT 05/11/15)
	function ad_usergroups($user) {
		global $adconn, $ldap_basedn;
		if(!isset($adconn)) die('Error, no LDAP connection established');
		if(empty($user)) die('Error, no LDAP user specified');
		
		// Search AD
		$results = ldap_search($adconn, $ldap_basedn, "(samaccountname=$user)", array("memberof","primarygroupid"));
		$entries = ldap_get_entries($adconn, $results);
		
		// No information found, bad user
		if($entries['count'] == 0) return false;
		
		// Get groups and primary group token
		if (isset($entries[0]['memberof'])) {			
			$output = $entries[0]['memberof'];
		}
		$token = $entries[0]['primarygroupid'][0];
		
		// Remove extraneous first entry
		if (isset($output)) {
			array_shift($output);
		}
		
		// We need to look up the primary group, get list of all groups
		$results2 = ldap_search($adconn ,$ldap_basedn, "(objectcategory=group)", array("distinguishedname","primarygrouptoken"));
		$entries2 = ldap_get_entries($adconn, $results2);
		
		// Remove extraneous first entry
		array_shift($entries2);
		
		// Loop through and find group with a matching primary group token
		foreach($entries2 as $e) {
			if($e['primarygrouptoken'][0] == $token) {
				// Primary group found, add it to output array
				$output[] = $e['distinguishedname'][0];
				// Break loop
				break;
			}
		}
		// Count the results and stuff this number into the array.
		$output['count'] = count($output);
	 
		return $output;
	}

?>
