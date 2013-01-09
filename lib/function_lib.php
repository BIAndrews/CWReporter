<?php

/*******************************************************************************
*	_topNavMenu - Returns the top most navigation menu. Fast and dirty.
*
*	string _topNavMenu ( void )
*
*	string - Returned HTML string
**/
function _topNavMenu() {

	$r = "\n<center> <a href=\"unlovedtickets.php\">Stale Ticket Report</a> 
		| <a href=\"liveactivity.php\">Live Activity Report</a> 
		| <a href=\"SLAMemberDetails.php\">SLA by Tech Report</a> 
		| <a href=\"SLAClientSnapshot.php\">Current SLA by Client</a> 
		| <a href=\"SLAClientHistory.php\">Client SLA History</a> 
	      </center>\n";

	return $r;
}

/*******************************************************************************
*	_getAllMembers - Return an array from SQL with all the CW members.
*
*	array _getAllMembers ( void )
*
*	array - Returned array of active members from the [dbo].[Member] table
**/
function _getAllMembers() {

	$_QueryGetAllMembers = "SELECT [Member_ID] FROM [dbo].[Member] WHERE [Inactive_Flag] = '0' ORDER BY [Member_ID]";
	$q=mssql_query_cache($_QueryGetAllMembers);

	return $q;
}

/*******************************************************************************
*	_getAllClients - Return an array from SQL with all the CW companies or clients.
*
*	array _getAllClients ( void )
*
*	array - Returned unfiltered array of active companies from [dbo].[v_Companies]
**/
function _getAllClients() {

	$_q = "SELECT * FROM [dbo].[v_Companies] WHERE [Company_Status_Desc] = 'Active' ORDER BY [Company_Name]";
	$q=mssql_query_cache($_q);

	return $q;
}

/*******************************************************************************
*	_showMemberSelectionDropList - Return an HTML form drop down list of members to select from.
*
*	string _showMemberSelectionDropList (string $Member_ID)
*
*	string - Returned HTML code
*	$Member_ID - string of an existing member to mark as SELECTED in the list
**/
function _showMemberSelectionDropList($Member_ID = "") {

	$list = _getAllMembers();

	$r = "<form><select name=\"Member_ID\" onchange=\"this.form.submit()\">\n";
	if ($Member_ID == "") {
		$r .= "<option SELECTED>_______</option>\n";
	}
	foreach ($list as $k => $a) {
		$r .= "<option";
		if ($Member_ID == $a[Member_ID]) $r .= " SELECTED";
		$r .= ">$a[Member_ID]</option>\n";
	}
	$r .= "</select></form>\n";

	return $r;
}

/*******************************************************************************
*	_showClientSelectionDropList - Return string of HTML form drop down list of CW clients/Companies.
*
*	string _showClientSelectionDropList ( string $Company_RecID, bool $NameOrRecID )
*
	string - Returned HTML code
*	$Company_RecID - ID number or string name of the company to mark as SELECTED
*	$NameOrRecID - boolian "RecID" or "Name" for what you want the returned HTML var to be named.
*
**/
function _showClientSelectionDropList($Company_RecID = "", $NameOrRecID = "RecID") {

	$list = _getAllClients();

	$r = "<form><select name=\"Company_RecID\" onchange=\"this.form.submit()\">\n";
	if ($Company_RecID == "") {
		$r .= "<option SELECTED>____________</option>\n";
	}
	foreach ($list as $k => $a) {
		$r .= "<option";
		if ($Company_RecID == $a[Company_RecID]) $r .= " SELECTED";
		if ($Company_RecID == $a[Company_Name]) $r .= " SELECTED";
		if ($NameOrRecID == "RecID") {
			$r .= " value=\"$a[Company_RecID]\">$a[Company_Name]</option>\n";
		} elseif ($NameOrRecID == "Name") {
			$r .= " value=\"$a[Company_Name]\">$a[Company_Name]</option>\n";
		}
	}
	$r .= "</select></form>\n";

	return $r;
}

/*******************************************************************************
*	_getOldestTicketUpdated - Get the oldest ticket details assigned to a member.
*
*	array _getOldestTicketUpdated ( string $user )
*
*	array - returned array of all the details of the [dbo].[v_cbi_AllTicketsByTech] table for $user
*	$user - string of the user name in the database to get the oldest ticket they have matched to [Member_ID].
**/
function _getOldestTicketUpdated ($user) {

	// pull the last updated ticket
	//$_query = "SELECT TOP 1 [Summary],[Last_Update],[SR_Service_RecID],[Company_Name] FROM [dbo].[v_cbi_AllTicketsByTech] WHERE [Member_ID] = '$user' AND [Closed_Flag] != '1' ORDER BY [Last_Update]";
	$_query = "SELECT TOP 1 [Summary],[Last_Update],[SR_Service_RecID],[Company_Name] FROM [dbo].[v_cbi_AllTicketsByTech] WHERE [Member_ID] = '$user' AND [Closed_Flag] != '1' AND [SR_Status] != 'Scheduled' ORDER BY [Last_Update]";

	$q2 = mssql_query_cache($_query);
	$ticketTime = strtotime(preg_replace('/:[0-9][0-9][0-9]/','', $q2[0]['Last_Update']));

	$r = $q2[0];
	return $r; //only return the first row
}


/*******************************************************************************
*	_getTicketsSLAunResolved - Return array of unresolved tickets with SLA data
*
*	array _getTicketsSLAunResolved ( string $user )
*
*	array - Returned array of uncompleted tickets from the [dbo].[v_cbi_TicketsByTech] table by [Member_ID]
*	$user - String to match on [Member_ID]
**/
function _getTicketsSLAunResolved ($user) {

	// pull all a members tickets and return SLA % and hrs to go.
	$_query = "SELECT * FROM [dbo].[v_cbi_TicketsByTech] WHERE [Member_ID] = '$user' AND [SR_Status] NOT LIKE '%Completed' AND [SR_Status] != 'Activity'";
	$q1 = mssql_query_cache($_query);

	if (is_array($q1)) {
	foreach ($q1 as $k => $row) {
		$ticket = $row[SR_Service_RecID]; // lookup this tickets SLA info
		//print "getting SLA details for ticket $ticket...\n";
		$_slaLookupQuery = "SELECT * FROM [dbo].[v_cbi_SLA_Not_Resolved] WHERE [sr_service_recid] = '$ticket'";
		$ticketSLADetails = mssql_query_cache($_slaLookupQuery);

		// build an array to return using the ticket number as the array key
		if ((is_array($ticketSLADetails)) && (is_array($row))) {
			$r[$ticket] = array_merge($ticketSLADetails[0],$row);
		}
	}

	} else {
		// no data returned
		$r = FALSE;
	}
	//print_r($r);
	return $r;
}

/*******************************************************************************
*	_getClientSLASnapshot - Client company unresolved tickets SLA snapshot
*
*	array _getClientSLASnapshot ( int $Company_RecID, string $Range="FALSE")
*
*	array - Returned array of the data pulled from the [dbo].[v_cbi_All_Tickets] table for company $Company_RecID
*	$Company_RecID - Company record ID number, int
*	$Range - Currently unused. Will be a range of dates to filter results by
**/
function _getClientSLASnapshot ($Company_RecID, $Range="FALSE") {

	$_query = "SELECT * FROM [dbo].[v_cbi_All_Tickets] WHERE [Company_RecID] = '$Company_RecID' AND [SR_Status] != 'Activity' ";

	if (is_int($Range)) {
		$_query .= "AND [Date_Entered] >= DATEADD(d, -${Range}, getdate())";
	}

	$q1 = mssql_query_cache($_query);

	if (is_array($q1)) {
	foreach ($q1 as $k => $row) {
		$ticket = $row[SR_Service_RecID]; // lookup this tickets SLA info
		//print "getting SLA details for ticket $ticket...\n";
		$_slaLookupQuery = "SELECT * FROM [dbo].[v_cbi_SLA_Not_Resolved] WHERE [sr_service_recid] = '$ticket'";
		$ticketSLADetails = mssql_query_cache($_slaLookupQuery);

		// build an array to return using the ticket number as the array key
		if ((is_array($ticketSLADetails)) && (is_array($row))) {
			$r[$ticket] = array_merge($ticketSLADetails[0],$row);
		}
	}

	} else {
		// no data returned
		$r = FALSE;
	}
	//print_r($r);
	return $r;
}

/*******************************************************************************
*	_getClientSLAHistory - Returns an array for the SLAClientHistory.php page
*
*	array _getClientSLAHistory ( string $Company_Name, string $Range="FALSE" )
*
*	array - array pulled from [dbo].[v_cbi_SLAStats] matched by [Company_Name]
*	$Company_Name - string to match [Company_Name] with
*	$Range - Unused, placeholder for a later software feature
**/
function _getClientSLAHistory ($Company_Name, $Range="FALSE") {

	$Company_Name = html_entity_decode($Company_Name);
	$_query = "SELECT * FROM [dbo].[v_cbi_SLAStats] WHERE [Company_Name] = '$Company_Name' ";

	// this is not working and might not ever get added
	if (is_int($Range)) {
		// adjust the query to only grab a certain year or month with the [year_nbr] and [month_nbr] fields
	}
	$_query .= " ORDER BY [year_nbr] DESC, [month_nbr] DESC";
	print "\n<!-- DEBUG: $_query -->\n";

	$q1 = mssql_query_cache($_query);

	if (is_array($q1)) {
		// return all results
		$r = $q1;
	} else {
		// no data returned
		$r = FALSE;
	}
	//print_r($r);
	return $r;
}

/*******************************************************************************
*	_getTicketFullDetails - Get full ticket details of ticket $service_recid from table [dbo].[v_cbi_All_Tickets]
*
*	array _getTicketFullDetails ( int $service_recid )
*
*	array - Returned array of the details found
*	$service_recid - int of the ticket to match against [SR_Service_RecID] in [dbo].[v_cbi_All_Tickets]
**/
function _getTicketFullDetails($service_recid) {

	$_query = "SELECT * FROM [dbo].[v_cbi_All_Tickets] WHERE [SR_Service_RecID] = '$service_recid'";
	$r = mssql_query_cache($_query);

	return $r[0];
}

/*******************************************************************************
*	_QueryGetLastTicketUpdate - Get the last ticket updated by [Member_ID] from the [dbo].[v_cbi_AllTicketsByTech] table
*
*	array _QueryGetLastTicketUpdate ( int $user )
*
*	array - Returned array of all the fields in the row
*	$user - Int of a user to match against [Member_ID]
**/
function _QueryGetLastTicketUpdate ($user) {

	$_QueryGetLastTicketUpdate = "SELECT TOP 1 [Summary],[Last_Update],[SR_Service_RecID],[Company_Name] FROM [dbo].[v_cbi_All_Tickets] WHERE [Updated_By] = '$user' ORDER BY [Last_Update] DESC";

	//$_QueryGetLastTicketUpdate = "SELECT TOP 1 [Summary],[Last_Update],[SR_Service_RecID],[Company_Name] FROM [dbo].[v_cbi_AllTicketsByTech] WHERE [Member_ID] = '$user' AND [Updated_By] = '$user' ORDER BY [Last_Update] DESC";
	#$_QueryGetLastTicketUpdate = "SELECT TOP 1 * FROM [dbo].[v_cbi_AllTicketsByTech] WHERE [Member_ID] LIKE '$user' ORDER BY [Last_Update] DESC";
	$q2 = mssql_query_cache($_QueryGetLastTicketUpdate);
	$q2[0][Type] = "Ticket";
	$ticketTime = strtotime(preg_replace('/:[0-9][0-9][0-9]/','', $q2[0]['Last_Update']));

	//bug: is this really the best way and accurate data?
	//todo: test to make sure this is the correct data and best method

	$_QueryGetLastActivityUpdate = "SELECT TOP 1 [Subject] AS [Summary],[Last_Update],[SR_Service_RecID],[Company_Name] FROM [dbo].[v_cbi_AllActivitiesByTech] WHERE [Member_ID] = '$user' AND [Updated_By] = '$user' ORDER BY [Last_Update] DESC";
	$q3 = mssql_query_cache($_QueryGetLastActivityUpdate);
	$q3[0][Type] = "Activity";
	$activityTime = strtotime(preg_replace('/:[0-9][0-9][0-9]/','', $q3[0]['Last_Update']));

	if ($ticketTime > $activityTime) {
		// since the ticket is newer we will return ticket info
		$r = $q2[0];
	} else {
		// since the activity is newer we will return ticket info
		$r = $q3[0];
	}

	return $r; //only return the first row
}

?>
