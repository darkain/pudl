<?php

//SELECT statement, choosing which columns to return
$db->string()->select('column', [
	't1' => $db->in()->select('column', 'table'),
]);
pudlTest($db, 'SELECT `column` FROM (SELECT `column` FROM `table`) AS `t1`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', 'column2'], [
	't1' => $db->in()->select('column1', 'table1'),
	't2' => $db->in()->select('column2', 'table2'),
]);
pudlTest($db, 'SELECT `column1`, `column2` FROM (SELECT `column1` FROM `table1`) AS `t1`, (SELECT `column2` FROM `table2`) AS `t2`');




//SELECT statement, choosing which columns to return
$db->string()->select('column', [
	't1' => ['parent',
		[
			'left' => ['t2' => $db->in()->select('column', 'table')],
			'using' => 'column'
		]
	]
]);
pudlTest($db, 'SELECT `column` FROM `parent` AS `t1` LEFT JOIN (SELECT `column` FROM `table`) AS `t2` USING (`column`)');




//SELECT statement, choosing which columns to return
$db->string()->select('column', 'table', [
	'column' => $db->in()->select('column', 'table'),
]);
pudlTest($db, 'SELECT `column` FROM `table` WHERE `column` IN (SELECT `column` FROM `table`)');
