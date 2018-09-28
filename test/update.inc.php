<?php

//UPDATE statement - using associative array and clause
$db->string()->update('table', ['column'=>'value'], 'id=1');
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id`=1)");




//UPDATE statement - using associative array and ID
$db->string()->updateId('table', ['column'=>'value'], 'id', 'value');
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>1], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=1 WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>1.1], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=1.1 WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>NULL], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=NULL WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>NAN], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=NULL WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>INF], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=NULL WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateId('table', ['column'=>-INF], 'id', 'value');
pudlTest("UPDATE `table` SET `column`=NULL WHERE (`id`='value')");




//UPDATE statement
$db->string()->updateField('table', 'column1', 'value', ['column2'=>3]);
pudlTest("UPDATE `table` SET `column1`='value' WHERE (`column2`=3)");




//UPDATE statement
$db->string()->updateFieldId('table', 'column1', 'value', 'id', 5);
pudlTest("UPDATE `table` SET `column1`='value' WHERE (`id`=5)");




//UPDATE statement - incrementing an INTEGER value
$db->string()->update('table', ['column'=>pudlFunction::increment()], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+1 WHERE (`id`=1)");




//UPDATE statement - incrementing an INTEGER value
$db->string()->update('table', ['column'=>pudl::_increment(1)], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+1 WHERE (`id`=1)");




//UPDATE statement - incrementing an FLOAT value
$db->string()->update('table', ['column'=>pudl::_increment(2.3)], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+2.3 WHERE (`id`=1)");




//UPDATE statement - incrementing a STRING value
$db->string()->update('table', ['column'=>pudlFunction::increment('5')], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+'5' WHERE (`id`=1)");




//UPDATE statement - incrementing a STRING value
$db->string()->update('table', ['column'=>pudl::_increment('5')], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+'5' WHERE (`id`=1)");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', '1,7,7,9');
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN ('1', '7', '7', '9'))");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', '  1  ,  7  , 7,  9');
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN ('1', '7', '7', '9'))");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', [1,7,7,9]);
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN (1, 7, 7, 9))");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', ['1','7','7','9']);
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN ('1', '7', '7', '9'))");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', ['  1  ','  7  ','7','9']);
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN ('  1  ', '  7  ', '7', '9'))");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', [INF, -INF, NAN, NULL]);
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN (NULL, NULL, NULL, NULL))");




$db->string()->update('table', 'column=value', 'id=1');
pudlTest("UPDATE `table` SET column=value WHERE (`id`=1)");




$db->string()->update('table', ['column=value'], 'id=1');
pudlTest("UPDATE `table` SET column=value WHERE (`id`=1)");




//UPDATE statement - add a value to a SET column
$db->string()->update('table', ['column'=>pudl::appendSet('item')], 'id=1');
pudlTest("UPDATE `table` SET `column`=CONCAT_WS(',', `column`, 'item') WHERE (`id`=1)");




//UPDATE statement - remove a value from a SET column
$db->string()->update('table', ['column'=>pudl::removeSet('item')], 'id=1');
pudlTest("UPDATE `table` SET `column`=TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `column`, ','), ',item,', ',')) WHERE (`id`=1)");




//UPDATE statement with counted rows from subquery
$db->string()->updateCount('parent', 'column1', [
	'column2' => 'value',
], 'child');
pudlTest("UPDATE `parent` SET `column1`=(SELECT COUNT(*) FROM `child` WHERE (`column2`='value') LIMIT 1) WHERE (`column2`='value')");




$db->string()->update('table', [
	'column' => ['param' => [
		'one',
		'two',
		'three'
	]]
], true);

pudlTest("UPDATE `table` SET `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.param',JSON_COMPACT('[\\\"one\\\",\\\"two\\\",\\\"three\\\"]')) WHERE (1)");




$db->string()->update('table', [
	'column' => []
], true);

pudlTest("UPDATE `table` SET `column`=NULL WHERE (1)");
