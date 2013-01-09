<html>
<head>
<link href="style/table-style.css" rel="stylesheet" type="text/css" />
<title>ETS - Ticket Details</title>
    <!--Load the AJAX API-->
</head>
<body>

<?php
require("config.inc");

echo _topNavMenu();

if ($_REQUEST['TID']) {
	$data = _getTicketFullDetails($_REQUEST['TID']);
	/*
	print "<pre>";
	print_r($data);
	print "</pre>";
	*/
} else {
	die("ERROR: No TID var passed.");
}


if (!is_array($data)) {

	print "ERROR: No ticket data found in database.\n";

} else {

print "<table cellspacing=\"0\">\n";
print "<caption>Pulling complete ticket details for ticket ID ".$_REQUEST['TID'].". ".date("r",mktime())."</caption>\n";
print "<tr><th class=\"nobg\">Field</th><th>Value</th></tr>\n";

foreach ($data as $k => $a) {
        if ($c & 1) {
        //if ($k%2===0){
                $col1 = "spec";
                $allTDs = "";
        } else {
                $col1 = "specalt";
                $allTDs = "alt";
        }

        $c++;

        print "<tr><th class=\"$col1\"> $k </th><td class=\"$allTDs\"> $a </td></tr>\n";
}
print "</table>\n";

}

?>
</body>
</html>
