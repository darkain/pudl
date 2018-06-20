<?php

try {
	pudl::instance([
		'type'		=>	'mysqli',
		'server'	=>	'example.com:80',
	]);
} catch (Exception $error) {
	pudlError($error, [
		"<br />\nUnable to connect to database server \"example.com:80\" with the username: \"\"<br />\nError 2006: MySQL server has gone away",

		'mysqli::real_connect(): MySQL server has gone away',
		
		"mysql_connect(): Lost connection to MySQL server at 'waiting for initial communication packet', system error: 110"
	]);
}
