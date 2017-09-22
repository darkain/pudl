<?php



$db->string()->select(pudl::json('column'));
pudlTest("SELECT `column` AS `JSON(column)`");







$db->string()->select(pudl::json('table.column'));
pudlTest("SELECT `table`.`column` AS `JSON(table.column)`");




$db->string()->select([pudl::json('column')]);
pudlTest("SELECT `column` AS `JSON(column)`");




$db->string()->select([pudl::json('table.column')]);
pudlTest("SELECT `table`.`column` AS `JSON(table.column)`");




$db->string()->update('table', [
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '$.parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '[1]', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '$[1]', 'value') WHERE (1)");




$db->string()->update('table', [
	pudl::jsonReplace('column', '{key}', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '\${key}', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonReplace('column_1', 'param_1', 'value_x'),
	pudl::jsonReplace('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_REPLACE(IFNULL(NULLIF(`column_1`, ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_REPLACE(IFNULL(NULLIF(`column_2`, ''), '{}'), '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_SET(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_SET(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonSet('column_1', 'param_1', 'value_x'),
	pudl::jsonSet('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_SET(IFNULL(NULLIF(`column_1`, ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_SET(IFNULL(NULLIF(`column_2`, ''), '{}'), '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_INSERT(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_INSERT(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_INSERT(IFNULL(NULLIF(`column`, ''), '{}'), '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonInsert('column_1', 'param_1', 'value_x'),
	pudl::jsonInsert('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_INSERT(IFNULL(NULLIF(`column_1`, ''), '{}'), '$.param_1', 'value_x'), `column_2`=JSON_INSERT(IFNULL(NULLIF(`column_2`, ''), '{}'), '$.param_2', 'value_y') WHERE (1)");






$db->string()->jsonUpdate('table', 'column', 'path', 'new value', true);

pudlTest("UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(`column`, ''), '{}'), '$.path', 'new value') WHERE (1)");






$db->string()->jsonUpdateId('table', 'column', 'path', 'new value', 'id', 1);

pudlTest("UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(`column`, ''), '{}'), '$.path', 'new value') WHERE (`id`=1)");
