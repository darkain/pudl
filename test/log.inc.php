<?php

$logresult = '';

$db->on('log', function($command, $db, $result) {
	global $logresult;

	if ($logresult === 'exception-log') {
		$db->log();
	} else if ($logresult === 'exception-query') {
		$db('SELECT 1');
	} else {
		pudlTest($db, $logresult);
	}
});




$logresult = 'SELECT * FROM `table` WHERE `column`=1 LIMIT 1';
$db->string()->log()->rowId('table', 'column', 1);




try {
	$logresult = 'exception-log';
	$db->string()->log()->rowId('table', 'column', 1);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Cannot change logging status while in log callback');
}




try {
	$logresult = 'exception-query';
	$db->string()->log()->rowId('table', 'column', 1);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Cannot run PUDL queries from within logging functions');
}
