<html>
<head>
<link href="style/table-style.css" rel="stylesheet" type="text/css" />
<title>ETS - SLA by Clients - Open Tickets</title>
<script type="text/javascript" src="js/jquery-latest.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script> 
<script type="text/javascript" id="js">
	$(document).ready(function() {
	$("table").tablesorter({
		sortForce: [[2,0]]
	});
}); </script>

</head>
<body>

<?php
require("config.inc");

echo _topNavMenu();

print "Client Switch: "._showClientSelectionDropList($_REQUEST['Company_RecID']);
print "Date Range: All Open Tickets<br>\n";

if ($_REQUEST['Company_RecID']) {
	// pull all the tickets for a member with SLA details

	$_REQUEST['Range'] = "30day";	// hardcoded for testing.

	$SLADetails = _getClientSLASnapshot($_REQUEST['Company_RecID']);
	print "<!-- \n";
	print_r($SLADetails);
	print "--> \n";

} else {
	die("\n<br>ERROR: No Company_RecID var passed.");
}


if (is_array($SLADetails)) {
print "<table cellspacing=\"0\" class=\"tablesorter\">\n";
print "<caption>Pulling all tickets from the CW database with SLA details. ".date("r",mktime())."</caption>\n";
print "<thead><tr><th class=\"nobg\">Ticket ID</th><th>SLA %</th><th>Hrs Left</th><th>Company</th><th>Ticket Summary</th> <th>Resources</th> <th>Status</th> </tr></thead><tbody>\n";

foreach ($SLADetails as $k => $a) {
	if ($c & 1) {
	//if ($k%2===0){ 
		$col1 = "spec";
		$allTDs = "";
	} else {
		$col1 = "specalt";
		$allTDs = "alt";
	}

	$SLAAvg += $a[sla_pct];
	$DaysOpenAvg += $a[days_open];

	$c++;

	print "<tr bgcolor=\"$a[bgColor]\"><th class=\"$col1\"><a href=\"TicketDetails.php?TID=$a[sr_service_recid]\">$a[sr_service_recid]</a> </td>
	<td nowrap class=\"$allTDs\">".round($a[sla_pct],2)." %</td><td class=\"$allTDs\">$a[hours_to_violation]</td><td class=\"$allTDs\">$a[Company_Name]</td>
	<td class=\"$allTDs\">$a[Summary]</td>
	<td class=\"$allTDs\">".trim($a[ResourceList],",")."</td>
	<td class=\"$allTDs\">$a[SR_Status]</td>
	</tr>\n";
}
print "<tr><td>-</td> <td>Average: ".round($SLAAvg/$c,2)."%</td> <td>Days Open Avg: ".round($DaysOpenAvg/2)."</td> <td>-</td> <td>-</td> <td>-</td> <td>-</td> </tr>\n";
print "</tbody></table>\n";

} else {

	// no data found
	print "No data returned.<br>\n";
}

?>


</body>
</html>
