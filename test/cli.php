<?php

//ALL WARNINGS AS EXCEPTIONS
error_reporting(E_ALL);
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
	throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
}
set_error_handler("exception_error_handler");



//PHP REQUIRES DEFAULT TIMEZONE TO BE SET NOW
date_default_timezone_set('UTC');



//SET FLOATING POINT SERIALIZATION PRECISION TO A KNOWN VALUE
ini_set('precision', 14);
ini_set('serialize_precision', 14);



//CHANGE TO THIS DIRECTORY FOR CONSISTENCY
chdir(__DIR__);



//TEST FOR PHP EXTENSIONS
if (!extension_loaded('date')) throw new Exception('Missing "date" PHP extension');
if (!extension_loaded('json')) throw new Exception('Missing "json" PHP extension');
if (!extension_loaded('session'))	echo "Missing optional \"session\" PHP extension\n";
if (!extension_loaded('redis'))		echo "Missing optional \"redis\" PHP extension\n";

$found = false;
foreach (['PDO', 'mysql', 'mysqli', 'sqlite3', 'pgsql', 'mssql', 'odbc'] as $item) {
	if (extension_loaded($item)) {
		$found = true;
	}
}
if (!$found) throw new Exception('No supported database PHP extensions found');


//REQUIRE ALL VERSIONS EVEN THOUGH WE ONLY USE "NULL"
//THIS ENSURES THEY CAN AT LEAST BE PARSED BY PHP/HHVM!

require_once(__DIR__.'/../pudl.php');
require_once(__DIR__.'/../clone/pudlClone.php');
require_once(__DIR__.'/../file/pudlExportExcel.php');
require_once(__DIR__.'/../file/pudlImportCsv.php');
require_once(__DIR__.'/../file/pudlImportExcel.php');
require_once(__DIR__.'/../mssql/pudlMsSql.php');
require_once(__DIR__.'/../mysql/pudlGalera.php');
require_once(__DIR__.'/../mysql/pudlMySql.php');
require_once(__DIR__.'/../mysql/pudlMySqli.php');
require_once(__DIR__.'/../null/pudlNull.php');
require_once(__DIR__.'/../null/pudlArrayResult.php');
require_once(__DIR__.'/../null/pudlFakeResult.php');
require_once(__DIR__.'/../pdo/pudlPdo.php');
require_once(__DIR__.'/../pgsql/pudlPgSql.php');
require_once(__DIR__.'/../sql/pudlOdbc.php');
require_once(__DIR__.'/../sql/pudlShell.php');
require_once(__DIR__.'/../sql/pudlWeb.php');
require_once(__DIR__.'/../sqlite/pudlSqlite.php');
require_once(__DIR__.'/../pudlSession.php');


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



require(__DIR__.'/all.php');



echo "PHP:\t" . PHP_VERSION . "\n";
echo "Tests:\t" . $__pudl_test_total__ . "\n";
echo "ALL GOOD!!\n";
