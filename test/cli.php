<?php

error_reporting(E_ALL);
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");


chdir(__DIR__);



//TEST FOR PHP EXTENSIONS
if (!extension_loaded('date')) throw new exception('Missing "date" PHP extension');
if (!extension_loaded('json')) throw new exception('Missing "json" PHP extension');
if (!extension_loaded('session'))	echo "Missing optional \"session\" PHP extension\n";
if (!extension_loaded('redis'))		echo "Missing optional \"redis\" PHP extension\n";

$found = false;
foreach (['PDO', 'mysql', 'mysqli', 'sqlite3', 'pgsql', 'mssql', 'odbc'] as $item) {
	if (extension_loaded($item)) {
		$found = true;
	}
}
if (!$found) throw new exception('No supported database PHP extensions found');


//REQUIRE ALL VERSIONS EVEN THOUGH WE ONLY USE SQL
//THIS ENSURES THEY CAN AT LEAST BE PARSED BY PHP/HHVM!

require_once('../pudl.php');
require_once('../file/pudlExportExcel.php');
require_once('../file/pudlImportCsv.php');
require_once('../file/pudlImportExcel.php');
require_once('../mssql/pudlMsSql.php');
require_once('../mysql/pudlGalera.php');
require_once('../mysql/pudlMySql.php');
require_once('../mysql/pudlMySqli.php');
require_once('../null/pudlNull.php');
require_once('../null/pudlArrayResult.php');
require_once('../null/pudlFakeResult.php');
require_once('../pdo/pudlPdo.php');
require_once('../pgsql/pudlPgSql.php');
require_once('../sql/pudlOdbc.php');
require_once('../sql/pudlShell.php');
require_once('../sql/pudlWeb.php');
require_once('../sqlite/pudlSqlite.php');


//TEST TO ENSURE EACH CLASS CAN INSTANTIATE PROPERLY
new pudlMsSql(	[], false);
new pudlGalera(	['server'=>['localhost']], false);
new pudlMySql(	[], false);
new pudlMySqli(	[], false);
new pudlNull(	[], false);
new pudlPdo(	['server'=>'localhost'], false);
new pudlOdbc(	[], false);
new pudlShell(	[], false);
new pudlWeb(	[], false);


$db = new pudlNull(['identifier' => '`']);

require('all.php');

echo "ALL GOOD!!\n";
