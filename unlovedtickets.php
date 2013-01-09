<html>
<head>
<link href="style/table-style.css" rel="stylesheet" type="text/css" />
<title>ETS - Oldest Un-updated Tickets</title>
    <!--Load the AJAX API-->
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
</head>
<body>

<?php
require("config.inc");

echo _topNavMenu();

$q = _getAllMembers();
foreach ($q as $k => $row) {
	//print_r($row);

	if ($_DEBUG) print "Getting info for... ".$row['Member_ID'];

	$OldestUpdate = _getOldestTicketUpdated($row['Member_ID']);

	$newDatetime = 	preg_replace('/:[0-9][0-9][0-9]/','', $OldestUpdate['Last_Update']); // strip fractional seconds
	$lu = 		strtotime($newDatetime);

	if ((!$lu) OR ($row['Member_ID'] == "GGary")){
		if ($_DEBUG) print "<Br>\n";
		continue;	// if there has never been a ticket, skip this member
	}

	if ($row['Member_ID'] == "GGary") continue;
	$co = 		$OldestUpdate['Company_Name'];
	$ticket = 	$OldestUpdate['SR_Service_RecID'];

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
	   $totalDays = round($w * 7 + $d);
	}

	$bgColor = "white";
	if (($w == "1") && ($d <= "2")) $bgColor = "green";
	if ($w > "1") $bgColor = "red";

	if ($_DEBUG) print " Oldest ticket: $totalDays ($lu) days old<br>\n";

	$now = microtime();	// unique microtime time stamp to avoid dupes

	$members[$totalDays.".".$now] = array(
		'OldestUpdate' => $luHumanReadable,
		'TotalDays' => $totalDays,
		'OldestUpdateUNIX' => $lu,
		'Company' => $co,
		'bgColor' => $bgColor,
		'Ticket' => $ticket,
		'TicketTitle' => $OldestUpdate['Summary'],
		'User' => $row['Member_ID']
	);
	
}
krsort($members, SORT_NUMERIC);

// debugging
//print_r($members);




print "
    <script type=\"text/javascript\">
      // Load the Visualization API and the piechart package.
      google.load('visualization', '1.0', {'packages':['corechart']});

      // Set a callback to run when the Google Visualization API is loaded.
      google.setOnLoadCallback(drawChart);

      function drawChart() {

        // Create the data table.
        var data = new google.visualization.DataTable();
        data.addColumn('string', 'User Name');
        data.addColumn('number', 'Days un-Updated');
        data.addRows([
";

foreach ($members as $k => $a) {
	$c++;
	print "\t['$a[User]', $a[TotalDays]]";
	if ($c != count($members)) print ",";
	print "\n";
}

	print "        ]);

        // Set chart options
        var options = {'title':'Oldest un-Updated Ticket in Queue',
                       'width':1000,
                       'height':350};

        // Instantiate and draw our chart, passing in some options.
        var chart = new google.visualization.BarChart(document.getElementById('chart_div'));
        chart.draw(data, options);
      }
    </script>

<div id=\"chart_div\"></div>
";






print "<table cellspacing=\"0\">\n";
print "<caption>Pulling all users from the CW database and then pulling the oldest un-updated ticket. ".date("r",mktime())."</caption>\n";
print "<tr><th class=\"nobg\">User</th><th>Oldest Ticket Update</th><th>Ticket/ID</th><th>Company</th></tr>\n";

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

	print "<tr bgcolor=\"$a[bgColor]\"><th class=\"$col1\">$a[User] </th><td class=\"$allTDs\">$a[OldestUpdate]</td><td class=\"$allTDs\">$a[TicketTitle]/<a href=\"TicketDetails.php?TID=$a[Ticket]\">$a[Ticket]</a></td><td class=\"$allTDs\">$a[Company]</td></tr>\n";
}
print "</table>\n";

?>


</body>
</html>
