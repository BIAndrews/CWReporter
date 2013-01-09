<?php
# CentOS/RH installation instructions:
# yum install memcached php-pecl-memcache

if (class_exists("Memcache")) {

    $memcache = new Memcache;
    $memcache->connect($_memcachedHost, $_memcachedPort);

    # Gets key / value pair into memcache ... called by mysql_query_cache()
    function getCache($key) {
        global $memcache;
	$rval = $memcache->get($key);
	if ($rval) {
		_debug("Returning cached query $key = $rval");
	} else {
		_debug("No cached query for $key");
	}
	return $rval;
    }

    # Puts key / value pair into memcache ... called by mysql_query_cache()
    function setCache($key,$object,$timeout = 5) {
        global $memcache;
	_debug("Caching query key $key");
        return ($memcache) ? $memcache->set($key,$object,MEMCACHE_COMPRESSED,$timeout) : false;
    }

    # Caching version of mysql_query()
    function mssql_query_cache($sql,$linkIdentifier = false) {

       global $_cacheTimeout; 

	$cache = getCache(md5("mssql_query" . $sql));
       if ($cache === FALSE) {
            $cache = false;
            $r = ($linkIdentifier !== false) ? mssql_query($sql,$linkIdentifier) : mssql_query($sql);
            if (is_resource($r) && (($rows = mssql_num_rows($r)) !== 0)) {
                for ($i=0;$i<$rows;$i++) {
                    $fields = mssql_num_fields($r);
                    $row = mssql_fetch_array($r);
                    for ($j=0;$j<$fields;$j++) {
                        if ($i === 0) {
                            $columns[$j] = mssql_field_name($r,$j);
                        }
                        $cache[$i][$columns[$j]] = $row[$j];
                    }
                }
                if (!setCache(md5("mssql_query" . $sql),$cache,$_cacheTimeout)) {
			_debug("Failed to save query cache key $cache");
                } else {
			_debug("Successfully saved query cache key $cache for $_cacheTimeout seconds");
		}
            }
        }
        return $cache;
    }

//if (method_exists("Memcache")) {
} else {

	// no memcache
	_debug("memcached not enabled.");

	function mssql_query_cache($sql,$linkIdentifier = false) {
                $r = ($linkIdentifier !== false) ? mssql_query($sql,$linkIdentifier) : mssql_query($sql);
		if ($r) {
			//$rows = mssql_fetch_assoc($r);
			
            		if ($rows = mssql_num_rows($r)) {
                		for ($i=0;$i<$rows;$i++) {
                    			$fields = mssql_num_fields($r);
                    			$row = mssql_fetch_array($r);
                    			for ($j=0;$j<$fields;$j++) {
                        			if ($i === 0) {
                            				$columns[$j] = mssql_field_name($r,$j);
                        			}
                        			$rval[$i][$columns[$j]] = $row[$j];
                    			}
                		}
            		}

			mssql_free_result($r); // free the memory like a good boy
		} else {
			$rval = FALSE;
			_debug("ERROR with query: $sql");
			_debug("ERROR SQL message: ".mssql_get_last_message());
		}
		return $rval;
	}

}


?>
