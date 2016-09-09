<?php

//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>'value']);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value')");




//INSERT statement - using associative array - null
$db->string()->insert('table', ['column'=>NULL]);
pudlTest("INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - boolean
$db->string()->insert('table', ['column'=>false]);
pudlTest("INSERT INTO `table` (`column`) VALUES (FALSE)");




//INSERT statement - using associative array - boolean
$db->string()->insert('table', ['column'=>true]);
pudlTest("INSERT INTO `table` (`column`) VALUES (TRUE)");




//INSERT statement - using associative array - integer
$db->string()->insert('table', ['column'=>2]);
pudlTest("INSERT INTO `table` (`column`) VALUES (2)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>3.4]);
pudlTest("INSERT INTO `table` (`column`) VALUES (3.4)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>-5.6]);
pudlTest("INSERT INTO `table` (`column`) VALUES (-5.6)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>NAN]);
pudlTest("INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>INF]);
pudlTest("INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>-INF]);
pudlTest("INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>1e56]);
pudlTest("INSERT INTO `table` (`column`) VALUES (1.0E+56)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>-1e78]);
pudlTest("INSERT INTO `table` (`column`) VALUES (-1.0E+78)");




//INSERT statement - using associative array - integer
$db->string()->insert('table', ['column'=>PHP_INT_MAX]);
pudlTest("INSERT INTO `table` (`column`) VALUES (9223372036854775807)");




//INSERT statement - using associative array - integer
$db->string()->insert('table', ['column'=>PHP_INT_MIN]);
pudlTest("INSERT INTO `table` (`column`) VALUES (-9223372036854775808)");




//INSERT statement - using associative array - array (empty)
$db->string()->insert('table', ['column'=>[]]);
pudlTest("INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['item']]);
pudlTest("INSERT INTO `table` (`column`) VALUES (COLUMN_CREATE(0,'item'))");




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['dynamic'=>'item']]);
pudlTest("INSERT INTO `table` (`column`) VALUES (COLUMN_CREATE('dynamic','item'))");




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['item1','item2']]);
pudlTest("INSERT INTO `table` (`column`) VALUES (COLUMN_CREATE(0,'item1', 1,'item2'))");




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['dynamic1'=>'item1', 'dynamic2'=>'item2']]);
pudlTest("INSERT INTO `table` (`column`) VALUES (COLUMN_CREATE('dynamic1','item1', 'dynamic2','item2'))");




//INSERT statement - using associative array, duplicate key update
$db->string()->insert('table', ['column'=>'value'], true);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value'");




//INSERT statement - using associative array, custom duplicate key update
$db->string()->insert('table', ['column'=>'value'], 'x=x+1');
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE x=x+1");




//INSERT statement - using associative array, custom duplicate key update using UPDATE syntax
$db->string()->insert('table', ['column'=>'value'], ['y'=>2]);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `y`=2");




//INSERT statement - with ON DUPLICATE KEY returning row ID
$db->string()->insertUpdate('table', [
	'column1' => 1,
	'column2' => 2,
], 'column1');

pudlTest('INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `column1`=LAST_INSERT_ID(`column1`)');



//INSERT statement - with ON DUPLICATE KEY returning row ID, using custom UPDATE syntax
$db->string()->insertUpdate(
	'table',
	['column1' => 1, 'column2' => 2],
	'column1',
	['column3' => 3]
);

pudlTest('INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `column3`=3, `column1`=LAST_INSERT_ID(`column1`)');




$db->string()->insertIgnore('table', ['column'=>'value']);
pudlTest("INSERT IGNORE INTO `table` (`column`) VALUES ('value')");




$db->string()->replace('table', ['column'=>'value']);
pudlTest("REPLACE INTO `table` (`column`) VALUES ('value')");




$db->string()->insert('table', [
	'column1' => 1,
	'column2' => 2,
], 'column1', false);
pudlTest('INSERT INTO `table` VALUES (1, 2) ON DUPLICATE KEY UPDATE column1');




$db->string()->insertValues('table', [
	'column1'=>'value1',
	'column2'=>'value2',
]);
pudlTest("INSERT INTO `table` VALUES ('value1', 'value2')");




$testdata = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
$db->string()->insert('table', pudl::extract($testdata, ['b','d']));
pudlTest('INSERT INTO `table` (`b`, `d`) VALUES (2, 4)');




$testdata = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
$db->string()->insert('table', pudl::extract($testdata, 'a','c'));
pudlTest('INSERT INTO `table` (`a`, `c`) VALUES (1, 3)');
