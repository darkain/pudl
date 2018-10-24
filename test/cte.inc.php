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
