<?php


$db	->string()
	->cte('tablex',
		$db->in()->select(
			'id',
			'table1'
		)
	)->select('*', [
		'tablex',
		'=table2',
	]);

pudlTest($db, 'WITH `tablex` AS (SELECT `id` FROM `table1`) SELECT * FROM `tablex` NATURAL JOIN `table2`');




$db->string();
$db([
	'cte'	=> ['tbx' => $db->in()->select('column', 'table')],
	'table'	=> ['tbx', '=table2'],
]);

pudlTest($db, 'WITH `tbx` AS (SELECT `column` FROM `table`) SELECT * FROM `tbx` NATURAL JOIN `table2`');
