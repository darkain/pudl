<?php

chdir(__DIR__);

//REQUIRE ALL VERSIONS EVEN THOUGH WE ONLY USE SQL
//THIS ENSURES THEY CAN AT LEAST BE PARSED BY PHP/HHVM!

require_once('../pudl.php');
require_once('../mysql/pudlGalera.php');
require_once('../mysql/pudlMySql.php');
require_once('../mysql/pudlMySqli.php');
require_once('../mssql/pudlMsSql.php');
require_once('../pgsql/pudlPgSql.php');
require_once('../sqlite/pudlSqlite.php');
require_once('../sql/pudlOdbc.php');
require_once('../sql/pudlShell.php');
require_once('../sql/pudlWeb.php');



$db = new pudlSqlite([
	'database'		=> 'test.db',
	'identifier'	=> '`',
]);

require('all.php');

echo "ALL GOOD!!\n";
