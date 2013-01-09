<?php

// bad data traps

if (!$_MSSQLServer) die("MSSQLServer setting missing.");
if (!$_MSSQLLogin) die("MSSQLLogin setting missing.");
if (!$_MSSQLPass) die("MSSQLPass setting missing.");
if (!$_MSSQLDB) die("MSSQLDB setting missing.");

// connection to MS SQL Server

$link = mssql_connect($_MSSQLServer, $_MSSQLLogin, $_MSSQLPass);

// just checking to make sure the connection is valid

if (!$link || !mssql_select_db($_MSSQLDB, $link)) {
    die('Unable to connect or select database!');
}

?>
