<?php

//INSERT statement - all default values
$db->string()->insert('table', false);
pudlTest($db, "INSERT INTO `table` () VALUES ()");




//INSERT statement - all default values
$db->string()->insert('table', []);
pudlTest($db, "INSERT INTO `table` () VALUES ()");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>'']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('')");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>'0']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('0')");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>'value']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value')");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>' value ']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (' value ')");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>' `value` ']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (' `value` ')");




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>' va"lue ']);
if ($db instanceof pudlSqlite) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES (' va\"lue ')");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES (' va\\\"lue ')");
}




//INSERT statement - using associative array - string
$db->string()->insert('table', ['column'=>" va'lue "]);
if ($db instanceof pudlSqlite) {
	pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\' va\'\'lue \')');
} else {
	pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\' va\\\'lue \')');
}




//INSERT statement - using associative array - null
$db->string()->insert('table', ['column'=>NULL]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - boolean
$db->string()->insert('table', ['column'=>false]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (FALSE)");




//INSERT statement - using associative array - boolean
$db->string()->insert('table', ['column'=>true]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (TRUE)");




//INSERT statement - using associative array - integer
$db->string()->insert('table', ['column'=>2]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (2)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>3.4]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (3.4)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>-5.6]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (-5.6)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>NAN]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>INF]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float (null)
$db->string()->insert('table', ['column'=>-INF]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>1e56]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (1.0E+56)");




//INSERT statement - using associative array - float
$db->string()->insert('table', ['column'=>-1e78]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (-1.0E+78)");




//INSERT statement - using associative array - integer constant
$db->string()->insert('table', ['column'=>PHP_INT_MAX]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES ('.PHP_INT_MAX.')');




//INSERT statement - using associative array - integer constant
$db->string()->insert('table', ['column'=>PHP_INT_MIN]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES ('.PHP_INT_MIN.')');




//INSERT statement - using associative array - float constant
$db->string()->insert('table', ['column'=>PHP_FLOAT_EPSILON]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (2.2204460492503E-16)");




//INSERT statement - using associative array - float constant
$db->string()->insert('table', ['column'=>PHP_FLOAT_MIN]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES ('.PHP_FLOAT_MIN.')');




//INSERT statement - using associative array - float constant
$db->string()->insert('table', ['column'=>PHP_FLOAT_MAX]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES ('.PHP_FLOAT_MAX.')');




//INSERT statement - using associative array - array (empty)
$db->string()->insert('table', ['column'=>[]]);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES (NULL)");




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['item']]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\'[\"item\"]\')');




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['dynamic'=>'item']]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\'{\"dynamic\":\"item\"}\')');




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['item1','item2']]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\'[\"item1\",\"item2\"]\')');




//INSERT statement - using associative array - array
$db->string()->insert('table', ['column'=>['dynamic1'=>'item1', 'dynamic2'=>'item2']]);
pudlTest($db, 'INSERT INTO `table` (`column`) VALUES (\'{\"dynamic1\":\"item1\",\"dynamic2\":\"item2\"}\')');




//INSERT statement - using associative array, duplicate key update
$db->string()->insert('table', ['column'=>'value'], true);
if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value'");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') UPDATE `column`='value'");
}




//INSERT statement - using associative array, custom duplicate key update
$db->string()->insert('table', ['column'=>'value'], 'x=x+1');
if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE x=x+1");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') UPDATE x=x+1");
}




//INSERT statement - using associative array, custom duplicate key update using UPDATE syntax
$db->string()->insert('table', ['column'=>'value'], ['y'=>2]);
if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `y`=2");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') UPDATE `y`=2");
}




//INSERT statement - with ON DUPLICATE KEY returning row ID
$db->string()->insertUpdate('table', [
	'column1' => 1,
	'column2' => 2,
], 'column1');

if ($db instanceof pudlMyShared) {
	pudlTest($db, 'INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `column1`=LAST_INSERT_ID(`column1`)');
} else {
	pudlTest($db, "INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) UPDATE `column1`=LAST_INSERT_ID(`column1`)");
}



//INSERT statement - with ON DUPLICATE KEY returning row ID, using custom UPDATE syntax
$db->string()->insertUpdate(
	'table',
	['column1' => 1, 'column2' => 2],
	'column1',
	['column3' => 3]
);

if ($db instanceof pudlMyShared) {
	pudlTest($db, 'INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `column3`=3, `column1`=LAST_INSERT_ID(`column1`)');
} else {
	pudlTest($db, "INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) UPDATE `column3`=3, `column1`=LAST_INSERT_ID(`column1`)");
}




$db->string()->replace('table', ['column'=>'value']);
pudlTest($db, "REPLACE INTO `table` (`column`) VALUES ('value')");




$db->string()->insert('table', [
	'column1' => 1,
	'column2' => 2,
], 'column1', false);

if ($db instanceof pudlMyShared) {
	pudlTest($db, 'INSERT INTO `table` VALUES (1, 2) ON DUPLICATE KEY UPDATE `column1`=LAST_INSERT_ID(`column1`)');
} else {
	pudlTest($db, "INSERT INTO `table` VALUES (1, 2) UPDATE `column1`=LAST_INSERT_ID(`column1`)");
}




$db->string()->insert('table', [
	'column1' => 1,
	'column2' => 2,
], 'column1', true);

if ($db instanceof pudlMyShared) {
	pudlTest($db, 'INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `column1`=LAST_INSERT_ID(`column1`)');
} else {
	pudlTest($db, "INSERT INTO `table` (`column1`, `column2`) VALUES (1, 2) UPDATE `column1`=LAST_INSERT_ID(`column1`)");
}




$db->string()->insertValues('table', [
	'column1'=>'value1',
	'column2'=>'value2',
]);
pudlTest($db, "INSERT INTO `table` VALUES ('value1', 'value2')");




$testdata = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
$db->string()->insert('table', pudl::extract($testdata, ['b','d']));
pudlTest($db, 'INSERT INTO `table` (`b`, `d`) VALUES (2, 4)');




$testdata = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
$db->string()->insert('table', pudl::extract($testdata, 'a','c'));
pudlTest($db, 'INSERT INTO `table` (`a`, `c`) VALUES (1, 3)');




$testdata = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
$db->string()->insert('table', pudl::extract($testdata, ['b'=>1,'c'=>1]));
pudlTest($db, 'INSERT INTO `table` (`b`, `c`) VALUES (2, 3)');




//INSERT statement - removing table prefix
$db->string()->insert('table', ['prefix.column'=>'test']);
pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('test')");




//UPSERT statement
$db->string()->upsert('table', ['column'=>'value']);

if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value'");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') UPDATE `column`='value'");
}



//UPSERT statement
$db->string()->upsert('table', ['column'=>'value'], 'id');

if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value', `id`=LAST_INSERT_ID(`id`)");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('value') UPDATE `column`='value', `id`=LAST_INSERT_ID(`id`)");
}




$db->string()->upsert('table', ['column'=>['param'=>[1,2,3]]]);

if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('{\\\"param\\\":[1,2,3]}') ON DUPLICATE KEY UPDATE `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.param',JSON_COMPACT('[1,2,3]'))");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('{\\\"param\\\":[1,2,3]}') UPDATE `column`=JSON_SET(IFNULL(NULLIF(TRIM(`column`), ''), '{}'),'$.param',JSON_COMPACT('[1,2,3]'))");
}




$db->string()->upsert('table', ['column'=>[1,2,3]]);

if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('[1,2,3]') ON DUPLICATE KEY UPDATE `column`='[1,2,3]'");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('[1,2,3]') UPDATE `column`='[1,2,3]'");
}




$db->string()->upsert('table', ['column'=>['a','b','c']]);

if ($db instanceof pudlMyShared) {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('[\\\"a\\\",\\\"b\\\",\\\"c\\\"]') ON DUPLICATE KEY UPDATE `column`='[\\\"a\\\",\\\"b\\\",\\\"c\\\"]'");
} else {
	pudlTest($db, "INSERT INTO `table` (`column`) VALUES ('[\\\"a\\\",\\\"b\\\",\\\"c\\\"]') UPDATE `column`='[\\\"a\\\",\\\"b\\\",\\\"c\\\"]'");
}




$db->string()->insertInto('table1')->select('*', 'table2');
pudlTest($db, 'INSERT INTO `table1` (SELECT * FROM `table2`)');




$db->string()->insertInto('table1')->rows('table2');
pudlTest($db, 'INSERT INTO `table1` (SELECT * FROM `table2`)');




$db->string()->insertInto('table1')->row('table2');
pudlTest($db, 'INSERT INTO `table1` (SELECT * FROM `table2` LIMIT 1)');




$db->string()->replaceInto('table1')->select('*', 'table2');
pudlTest($db, 'REPLACE INTO `table1` (SELECT * FROM `table2`)');




$db->string()->replaceInto('table1')->rows('table2');
pudlTest($db, 'REPLACE INTO `table1` (SELECT * FROM `table2`)');




$db->string()->replaceInto('table1')->row('table2');
pudlTest($db, 'REPLACE INTO `table1` (SELECT * FROM `table2` LIMIT 1)');




$db->string()->exsert('table', ['a'=>1, 'b'=>2]);
if ($db instanceof pudlMyShared) {
	pudlTest($db, 'INSERT INTO `table` (`a`, `b`) VALUES (1, 2) ON DUPLICATE KEY UPDATE `a`=`a`');
} else {
	pudlTest($db, 'INSERT INTO `table` (`a`, `b`) VALUES (1, 2) UPDATE `a`=`a`');
}
