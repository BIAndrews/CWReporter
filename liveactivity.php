<link href="style/table-style.css" rel="stylesheet" type="text/css" />
<?php
require("config.inc");

echo _topNavMenu();

$_QueryGetAllMembers = "SELECT [Member_ID] FROM [dbo].[Member] WHERE [Inactive_Flag] = '0'";
$q=mssql_query_cache($_QueryGetAllMembers);

// go through each user and get their last ticket update

foreach ($q as $k => $row) {
	//print_r($row);

	$bgColor = "white";
	//print "Getting info for... ".$row['Member_ID']."<br>\n";
	$lastUpdate = _QueryGetLastTicketUpdate($row['Member_ID']);

	$newDatetime = preg_replace('/:[0-9][0-9][0-9]/','', $lastUpdate['Last_Update']); // strip fractional seconds
	$lu = 		strtotime($newDatetime);
	if (!$lu) continue;	// if there has never been a ticket, skip this member
	$co = 		$lastUpdate['Company_Name'];
	$ticket = 	$lastUpdate['SR_Service_RecID'];

	$now = mktime()+$scew; //fix for screw issues on SQL server or web server
	$diff = $now - $lu;
	if( 1 > $diff ){
	   print $row['Member_ID']." - Ticket time screw ($diff)<br>\n";
	   continue;
	} else {
	   $w = round($diff / 86400 / 7);
	   $d = $diff / 86400 % 7;
	   $h = $diff / 3600 % 24;
	   $m = $diff / 60 % 60; 
	   $s = $diff % 60;
	
	   $luHumanReadable = "${w}w, {$d}d, {$h}h, {$m}m";
	}

	if (($w == "0") && ($d == "0") && ($h < "6")) $bgColor = "green";
	if (($w > "0")) $bgColor = "red";

	$members[$lu] = array(
		'LastUpdate' => $luHumanReadable,
		'LastUpdateDB' => $lu,
		'Company' => $co,
		'Type' => $lastUpdate['Type'],
		'bgColor' => $bgColor,
		'Ticket' => $ticket,
		'TicketTitle' => $lastUpdate['Summary'],
		'User' => $row['Member_ID']
	);
	
}
krsort($members, SORT_NUMERIC);

//print_r($members); 
//exit;



print "<table cellspacing=\"0\">\n";
print "<caption>Pulling all users from the CW database and then pulling the last ticket assigned to them that has been updated. ".date("r",mktime())."</caption>\n";
print "<tr><th class=\"nobg\">User</th><th>Last Ticket Update</th><th>Ticket/ID</th><th>Company</th></tr>\n";

foreach ($members as $k => $a) {
	if ($c & 1) {
	//if ($k%2===0){ 
		$col1 = "spec";
		$allTDs = "";
	} else {
		$col1 = "specalt";
		$allTDs = "alt";
	}

	$c++;

	if ($a[Type] == "Ticket") {

		$type = "[T]";
	} elseif ($a[Type] == "Activity") {

		$type = "[A]";
	} else {
		$type = "[E]";
	}

	print "<tr bgcolor=\"$a[bgColor]\"><th class=\"$col1\">$a[User] $type</th><td class=\"$allTDs\">$a[LastUpdate]</td><td class=\"$allTDs\">$a[TicketTitle]/<a href=\"TicketDetails.php?TID=$a[Ticket]\">$a[Ticket]</a></td><td class=\"$allTDs\">$a[Company]</td></tr>\n";
}
print "</table>\n";

?>
