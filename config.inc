<?php
$_MSSQLServer = 	"ets-sql08-01";	// MS SQL server hostname or IP
$_MSSQLLogin = 		"sa";		// MS SQL login, usually sa
$_MSSQLPass = 		"password";	// MS SQL password
$_MSSQLDB = 		"database";	// MS SQL connectwise database name

$_cacheTimeout = 	"60"; 		// queries are cached for XX seconds 
$_memcachedHost = 	"localhost";
$_memcachedPort = 	"11211";
$scew =			215;

ini_set('display_errors',1);
ini_set('display_startup_errors',1);
error_reporting(E_ALL ^ E_NOTICE);
$_debugLog = __DIR__."/var/debug.log";

// include all libraries __DIR__/lib/*.php
foreach (glob(__DIR__."/lib/*.php") as $file) {
	include("$file");
}


?>
