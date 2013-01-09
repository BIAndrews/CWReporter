<?php

/*******************************************************************************
*
* get the client IP even if it's behind a nice proxy
*
**/

if ( isset($_SERVER["REMOTE_ADDR"]) )    {
    $_thisClientIP = $_SERVER["REMOTE_ADDR"];
} else if ( isset($_SERVER["HTTP_X_FORWARDED_FOR"]) )    {
    $_thisClientIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
} else if ( isset($_SERVER["HTTP_CLIENT_IP"]) )    {
    $_thisClientIP = $_SERVER["HTTP_CLIENT_IP"];
} 

if (file_exists($_debugLog)) {

	if (!is_writable($_debugLog)) {
		die("FATAL ERROR: debug log at $_debugLog is not writable.");
	}

} else {

	// file doesn't exist, make it
	touch($_debugLog);
}

/*******************************************************************************
*	_debug - Logs a debug message with a timestamp and the client IP.
*
*	int _debug ( string $txt )
*
*	txt	string to log in the debug log about the event
**/

function _debug($txt) {

	global $_debugLog, $_thisClientIP;

	$tstamp = date("m/d/y g:i:sa",mktime());
	$str = "$tstamp\t$_thisClientIP\t$txt\n";
	$rval = file_put_contents($_debugLog, $str, FILE_APPEND);

	return $rval;
}

?>
