<?php

/******************************************************************************\
*   IMPORTANT NOTE: The ->string() part of these queries means that they       *
*   will *NOT* be executed, but instead ONLY return an object containing       *
*   the SQL query statement generated. Removing ->string() from each line      *
*   will allow execution of the generated statement. This is simply added      *
*   here to compare the generated statements to their expected results, to     *
*   ensure that all queries are generated by PUDL correctly.                   *
*                                                                              *
*   To execute this test from the command line, simply run the following:      *
*                                                                              *
*   php test/cli.php                                                           *
*                                                                              *
*   For HHVM users, simply run the following instead:                          *
*                                                                              *
*   hhvm test/cli.php                                                          *
\******************************************************************************/




// COUNTER FOR TOTAL NUMBER OF UNIT TESTS COMPLETED
$__pudl_test_total__ = 0;


// RESET PREFIX INFORMATION IN CASE WE'RE CALLING THIS FROM ALTAFORM
$db->updateAuth(['prefix'=>[]]);



function pudlTest(pudl $pudl, $expected) {
	global $__pudl_test_total__;
	$__pudl_test_total__++;
	if (empty($pudl)) return;
	if (is_string($expected)	&&	$expected === $pudl->query()) return;
	if (is_bool($expected)		&&	$expected) return;
	$trace = debug_backtrace()[0];
	echo "\n\n";
	echo "ERROR: FAILED!!\n\n";
	echo "PHP:\t" . PHP_VERSION . "\n";
	echo "FILE:\t$trace[file]\n";
	echo "LINE:\t$trace[line]\n\n";
	echo "EXPECTED:\n";
	echo (is_bool($expected) ? '[TRUE]' : $expected) . "\n\n";
	echo "QUERY:\n";
	echo (is_bool($expected) ? '[FALSE]' : $pudl->query()) . "\n\n";
	exit(1);
}


function pudlError($exception, $expected) {
	global $__pudl_test_total__;
	$__pudl_test_total__++;
	if (is_array($expected)) {
		foreach ($expected as $item) {
			if ($exception->getMessage() === $item) {
				return;
			}
		}
	} else if ($exception->getMessage() === $expected) {
		return;
	}
	$trace = debug_backtrace()[0];
	echo "\n\n";
	echo "ERROR: FAILED!!\n\n";
	echo "PHP:\t" . PHP_VERSION . "\n";
	echo "FILE:\t$trace[file]\n";
	echo "LINE:\t$trace[line]\n\n";
	echo "EXPECTED:\n";
	echo json_encode($expected) . "\n\n";
	echo "ERROR:\n";
	echo $exception->getMessage() . "\n\n";
	echo "\n\n";
	exit(1);
}


function pudlUnit($result, $expected=true) {
	global $__pudl_test_total__;
	$__pudl_test_total__++;
	if ($result === $expected) return;
	$trace = debug_backtrace()[0];
	echo "\n\n";
	echo "ERROR: FAILED!!\n\n";
	echo "PHP:\t" . PHP_VERSION . "\n";
	echo "FILE:\t$trace[file]\n";
	echo "LINE:\t$trace[line]\n\n";
	echo "EXPECTED:\n";
	var_dump($expected);
	echo "\n\n";
	echo "RESULT:\n";
	var_dump($result);
	exit(1);
}


// PHP 5.x COMPATIBILITY
if (!defined('PHP_INT_MIN'))		define('PHP_INT_MIN',		~PHP_INT_MAX);
if (!defined('PHP_FLOAT_MIN'))		define('PHP_FLOAT_MIN',		2.2250738585072E-308);
if (!defined('PHP_FLOAT_MAX'))		define('PHP_FLOAT_MAX',		1.7976931348623E+308);
if (!defined('PHP_FLOAT_EPSILON'))	define('PHP_FLOAT_EPSILON',	2.2204460492503E-16);



// ENSURE WE HAVE PUDL NULL, SINCE SOME TESTS USE THIS INSTEAD OF DEFAULT $DB OBJECT
require_once(__DIR__.'/../null/pudlNull.php');



// PREP THE DIRECTORY
$parent	= dirname(dirname(__DIR__));
$dir	= substr(__DIR__, strlen($parent)-strlen(__DIR__)+1);
$list	= scandir(__DIR__);
shuffle($list);



// RUN ALL UNIT TESTS
foreach ($list as $item) {
	if (strtolower(substr($item, -8)) !== '.inc.php') continue;
	echo "\033[97m" . "Testing:\t";
	echo "\033[36m" . $dir . '/';
	echo "\033[96m" . $item . "\033[0m\n";
	require_once(__DIR__ . '/' . $item);
}



// OPTIONAL UNIT TESTS ONLY FOR TRAVIS CI
if (!empty($found)) {
	require_once(__DIR__ . '/timeout.php');
}
