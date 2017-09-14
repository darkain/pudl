<?php



$db->string()->select(pudl::json('column'));
pudlTest("SELECT `column` AS `JSON(column)`");




$db->string()->select([pudl::json('column')]);
pudlTest("SELECT `column` AS `JSON(column)`");




$db->string()->update('table', [
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_REPLACE(`column`, '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(`column`, '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonReplace('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_REPLACE(`column`, '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonReplace('column_1', 'param_1', 'value_x'),
	pudl::jsonReplace('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_REPLACE(`column_1`, '$.param_1', 'value_x'), `column_2`=JSON_REPLACE(`column_2`, '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_SET(`column`, '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_SET(`column`, '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonSet('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_SET(`column`, '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonSet('column_1', 'param_1', 'value_x'),
	pudl::jsonSet('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_SET(`column_1`, '$.param_1', 'value_x'), `column_2`=JSON_SET(`column_2`, '$.param_2', 'value_y') WHERE (1)");





$db->string()->update('table', [
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `column`=JSON_INSERT(`column`, '$.parameter', 'value') WHERE (1)");





$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_INSERT(`column`, '$.parameter', 'value') WHERE (1)");






$db->string()->update('table', [
	'x' => 1,
	pudl::jsonInsert('column', 'parameter', 'value'),
	'y' => 2,
], true);

pudlTest("UPDATE `table` SET `x`=1, `column`=JSON_INSERT(`column`, '$.parameter', 'value'), `y`=2 WHERE (1)");






$db->string()->update('table', [
	pudl::jsonInsert('column_1', 'param_1', 'value_x'),
	pudl::jsonInsert('column_2', 'param_2', 'value_y'),
], true);

pudlTest("UPDATE `table` SET `column_1`=JSON_INSERT(`column_1`, '$.param_1', 'value_x'), `column_2`=JSON_INSERT(`column_2`, '$.param_2', 'value_y') WHERE (1)");
