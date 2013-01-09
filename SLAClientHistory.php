<html>
<head>
<link href="style/table-style.css" rel="stylesheet" type="text/css" />
<title>ETS - SLA Clients - Historical</title>
<script type="text/javascript" src="js/jquery-latest.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.js"></script> 
<script type="text/javascript" src="js/jquery.tablesorter.pager.js"></script> 
<script type="text/javascript" id="js">
	$(document).ready(function() {
	$("table").tablesorter({
		sortForce: [[0,0]]
	});
}); </script>

</head>
<body>

<?php
require("config.inc");

echo _topNavMenu();

print "Client: "._showClientSelectionDropList($_REQUEST['Company_RecID'],"Name");
print "Date Range: All<br>\n";

if ($_REQUEST['Company_RecID']) {
	// pull all the tickets for a member with SLA details

	$_REQUEST['Range'] = "30day";	// hardcoded for testing.

	$SLADetails = _getClientSLAHistory($_REQUEST['Company_RecID']);
	print "<!-- \n";
	print_r($SLADetails);
	print "--> \n";

} else {
	die("\n<br>ERROR: No Company_Name var passed.");
}


if (is_array($SLADetails)) {
print "<table cellspacing=\"0\" class=\"tablesorter\">\n";
print "<caption>Pulling complete SLA historical data from the CW database. ".date("r",mktime())."</caption>\n";
print "<thead><tr>

<!-- <th class=\"nobg\">".$SLADetails[0][company_name]."</th> -->
<th class=\"nobg\">Year/Month</th>
<th>Board</th>
<th>Hours: Resp/Plan/Res Goals</th>
<th>%: Resp/Plan/Res Actual</th>
<th>Tickets Created/Responded</th>
<th>Met Resp SLA/%</th>
<th><u>Avg Hours to:</u><br> Respond/ResPlan/Resolve</th>
<th><u>Tickets Response:</u><br>Total/Met SLA Count/PCT%</th>
<th><u>Tickets Resolved:</u><br>Total/Met SLA Count/PCT%</th>

 </tr></thead><tbody>\n";

foreach ($SLADetails as $k => $a) {
	if ($c & 1) {
		$col1 = "spec";
		$allTDs = "";
	} else {
		$col1 = "specalt";
		$allTDs = "alt";
	}

	$c++;

	print "<tr>
<th class=\"$col1\">$a[year_nbr]/$a[month_nbr]</a> </td>
<th class=\"$allTDs\">$a[board_name]/$a[agr_name]</a> </td>
<th class=\"$allTDs\">$a[responded_hours]/$a[resplan_hours]/$a[resolution_hours]</a> </td>
<th class=\"$allTDs\">$a[responded_pct]%/$a[resplan_pct]%/$a[resolution_pct]%</a> </td>
<th class=\"$allTDs\">$a[tickets_created]/$a[tickets_responded]</a> </td>
<th class=\"$allTDs\">$a[met_responded_sla]/$a[responded_pct_actual]%</a> </td>
<th class=\"$allTDs\">$a[AvgHrsToResponded]/$a[AvgHrsToResplan]/$a[AvgHrsToResolved]</a> </td>
<th class=\"$allTDs\">$a[tickets_resplan]/$a[met_resplan_sla]/$a[resplan_pct_actual]%</a> </td>
<th class=\"$allTDs\">$a[tickets_resolved]/$a[met_resolution_sla]/$a[resolved_pct_actual]%</a> </td>

	</tr>\n";
}
//print "<tr><td>-</td> <td>Average: ".round($SLAAvg/$c,2)."%</td> <td>Days Open Avg: $DaysOpenAvg</td> <td>-</td> <td>-</td> <td>-</td> <td>-</td> </tr>\n";


print "</tbody></table>\n";

} else {

	// no data found
	print "No data returned.<br>\n";
}

?>


</body>
</html>
