<?php


// ONLY DO THE TIMEOUT TEST IF WE HAVE MYSQLI DRIVER INSTALLER
if (!extension_loaded('mysqli')) {
	return;
}



$mtime = microtime(true);


try {
	pudl::instance([
		'type'		=>	'mysqli',
		'server'	=>	'example.com:80',
		'timeout'	=>	1,
	]);
} catch (Exception $error) {
	pudlError($error, [
		"Unable to connect to database server \"example.com:80\" with the username \"\"\nError 2006: MySQL server has gone away",

		'mysqli::real_connect(): MySQL server has gone away',

		"mysql_connect(): Lost connection to MySQL server at 'waiting for initial communication packet', system error: 110",

		'mysqli::real_connect(): Error while reading greeting packet. PID='.getmypid(),

		'MySQL server has gone away',

		'{"message":"Database connection error","code":2006,"error":"MySQL server has gone away","server":"example.com:80","user":""}',
	]);
}


$mtime = microtime(true) - $mtime;



if (version_compare(PHP_VERSION, '7.2', '>=')) {
	//TIMEOUT WORKS IN PHP 7.2 AND HIGHER
	$mtest = ($mtime >= 0.5)  &&  ($mtime <= 2.5);

} else if (defined('HHVM_VERSION')) {
	//TIMEOUT WORKS IN ALL HHVM VERSIONS
	$mtest = ($mtime >= 0.5)  &&  ($mtime <= 2.5);

} else {
	//TIMEOUT IS BROKEN ON PHP 7.1 AND LOWER
	$mtest = ($mtime >= 55.0)  &&  ($mtime <= 65.0);
}


pudlTest($db, $mtest);
