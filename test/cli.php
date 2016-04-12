<?php

chdir(__DIR__);
require_once('../pudlSqlite.php');

$db = new pudlSqlite([
	'database'		=> 'test.db',
	'identifier'	=> '`',
]);

require('all.php');

echo "ALL GOOD!!\n";
