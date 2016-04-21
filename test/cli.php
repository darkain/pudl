<?php

chdir(__DIR__);

//REQUIRE ALL VERSIONS EVEN THOUGH WE ONLY USE SQL
//THIS ENSURES THEY CAN AT LEAST BE PARSED BY PHP/HHVM!

require_once('../pudlGalera.php');
require_once('../pudlMsSql.php');
require_once('../pudlMySql.php');
require_once('../pudlMySqli.php');
require_once('../pudlOdbc.php');
require_once('../pudlPgSql.php');
require_once('../pudlShell.php');
require_once('../pudlSqlite.php');
require_once('../pudlWeb.php');



$db = new pudlSqlite([
	'database'		=> 'test.db',
	'identifier'	=> '`',
]);

require('all.php');

echo "ALL GOOD!!\n";
