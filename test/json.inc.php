<?php



$db->string()->select('column', 'table', 'json$param');
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param'))");



$db->string()->select('column', 'table', ['json$param']);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param'))");



$db->string()->select('column', 'table', ['json$param'=>1]);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param')=1)");



$db->string()->select('column', 'table', 'json$.param');
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param'))");



$db->string()->select('column', 'table', ['json$.param']);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param'))");




$db->string()->select('column', 'table', 'alias.json$param');
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`alias`.`json`,'$.param'))");



$db->string()->select('column', 'table', ['alias.json$param']);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`alias`.`json`,'$.param'))");



$db->string()->select('column', 'table', ['alias.json$param'=>1]);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`alias`.`json`,'$.param')=1)");



$db->string()->select('column', 'table', 'alias.json$.param');
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`alias`.`json`,'$.param'))");



$db->string()->select('column', 'table', ['alias.json$.param']);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`alias`.`json`,'$.param'))");




$db->string()->select('column', 'table', ['json$.param'=>1]);
pudlTest($db, "SELECT `column` FROM `table` WHERE (JSON_VALUE(`json`,'$.param')=1)");



$db->string()->select(pudl::json('column'));
pudlTest($db, "SELECT `column` AS `JSON(column)`");




$db->string()->select(pudl::json('table.column'));
pudlTest($db, "SELECT `table`.`column` AS `JSON(table.column)`");




$db->string()->select([pudl::json('column')]);
pudlTest($db, "SELECT `column` AS `JSON(column)`");




$db->string()->select([pudl::json('table.column')]);
pudlTest($db, "SELECT `table`.`column` AS `JSON(table.column)`");




$db->string()->update('table', [
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '$.parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '[1]', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$[1]', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '{key}', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '\${key}', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonReplace('column_1', 'param_1', 'value_x'),
	pudl::jsonReplace('column_2', 'param_2', 'value_y'),
], true);

pudlTest($db, "UPDATE `table` SET `column_1`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column_1`), ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_REPLACE(IFNULL(NULLIF(TRIM(`column_2`), ''), '{}'), '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonSet('column_1', 'param_1', 'value_x'),
	pudl::jsonSet('column_2', 'param_2', 'value_y'),
], true);

pudlTest($db, "UPDATE `table` SET `column_1`=JSON_SET(IFNULL(NULLIF(TRIM(`column_1`), ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_SET(IFNULL(NULLIF(TRIM(`column_2`), ''), '{}'), '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_INSERT(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_INSERT(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_INSERT(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonInsert('column_1', 'param_1', 'value_x'),
	pudl::jsonInsert('column_2', 'param_2', 'value_y'),
], true);

pudlTest($db, "UPDATE `table` SET `column_1`=JSON_INSERT(IFNULL(NULLIF(TRIM(`column_1`), ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_INSERT(IFNULL(NULLIF(TRIM(`column_2`), ''), '{}'), '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonRemove('column', 'parameter'),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_REMOVE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonRemove('column', 'parameter'),
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_REMOVE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonRemove('column', 'parameter'),
	'y' => 2,
], true);

pudlTest($db, "UPDATE `table` SET `x`=1, `column`=JSON_REMOVE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonRemove('column_1', 'param_1'),
	pudl::jsonRemove('column_2', 'param_2'),
], true);

pudlTest($db, "UPDATE `table` SET `column_1`=JSON_REMOVE(IFNULL(NULLIF(TRIM(`column_1`), ''), '{}'), '$.param_1'), `column_2`=JSON_REMOVE(IFNULL(NULLIF(TRIM(`column_2`), ''), '{}'), '$.param_2') WHERE (1)");






$db->string()->jsonUpdate('table', 'column', ['path' => 'new value'], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.path', 'new value') WHERE (1)");






$db->string()->jsonUpdateId('table', 'column', ['path' => 'new value'], 'id', 1);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.path', 'new value') WHERE (`id`=1)");







$db->string()->update('table', [
	pudl::jsonSet('column', ['parameter' => 'value']),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value') WHERE (1)");







$db->string()->update('table', [
	pudl::jsonSet('column', ['parameter' => 'value', 'key' => 1]),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value', '$.key', 1) WHERE (1)");







$db->string()->update('table', [
	pudl::jsonSet('column', 'parameter', 'value', 'key', 1),
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter', 'value', '$.key', 1) WHERE (1)");





$db->string()->update('table', [
	'column' => [
		'key1' => 'value1',
		'key2' => 'value2',
	],
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.key1','value1','$.key2','value2') WHERE (1)");





$db->string()->update('table', [
	'column' => [
		'key1' => ['value1', 'value2'],
	],
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.key1',JSON_COMPACT('[\\\"value1\\\",\\\"value2\\\"]')) WHERE (1)");





$db->string()->update('table', [
	'column' => [
		'key1' => ['sub1'=>['value1', 'value2']],
	],
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.key1',JSON_COMPACT('{\\\"sub1\\\":[\\\"value1\\\",\\\"value2\\\"]}')) WHERE (1)");





$db->string()->row('table', [
	pudl::jsonCompare('column', 'parameter', 'value'),
]);

pudlTest($db, "SELECT * FROM `table` WHERE (JSON_VALUE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter')='value') LIMIT 1");





$db->string()->row('table',
	pudl::jsonCompare('column', 'parameter', 'value')
);

pudlTest($db, "SELECT * FROM `table` WHERE (JSON_VALUE(IFNULL(NULLIF(TRIM(`column`), ''), '{}'), '$.parameter')='value') LIMIT 1");





$db->string()->update('table', [
	'column' => [
		'=key1.' => 'value',
	],
], true);

pudlTest($db, "UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.=key1\\\\.','value') WHERE (1)");
