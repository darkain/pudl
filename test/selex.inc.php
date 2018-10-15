<?php

$db->string();
$db([
	'column'	=> ['one', 'two'],
	'table'		=> ['t1'=>'table1', 't2'=>'table2'],
	'clause'	=> ['t1.key=t2.id'],
	'order'		=> ['t2.sort'],
]);
pudlTest($db, 'SELECT `one`, `two` FROM `table1` AS `t1`, `table2` AS `t2` WHERE (`t1`.`key`=`t2`.`id`) ORDER BY `t2`.`sort`');




$db->string();
$db(['column' => pudl::unix_timestamp()]);
pudlTest($db, 'SELECT UNIX_TIMESTAMP()');




$db->string();
$db(['column' => [pudl::unix_timestamp()]]);
pudlTest($db, 'SELECT UNIX_TIMESTAMP()');




$db->string();
$db(['column' => ['time'=>pudl::unix_timestamp()]]);
pudlTest($db, 'SELECT UNIX_TIMESTAMP() AS `time`');




$db->string();
$db(['table' => 'table1']);
pudlTest($db, 'SELECT * FROM `table1`');




$db->string();
$db(['table' => ['table1']]);
pudlTest($db, 'SELECT * FROM `table1`');




$db->string();
$db(['table' => ['t1'=>'table1']]);
pudlTest($db, 'SELECT * FROM `table1` AS `t1`');




$db->string();
$db([
	'table'		=> 'table',
	'clause'	=> 'column1=column2'
]);
pudlTest($db, "SELECT * FROM `table` WHERE (`column1`=`column2`)");




$db->string();
$db([
	'table'		=> 'table',
	'clause'	=> ['column'=>'value']
]);
pudlTest($db, "SELECT * FROM `table` WHERE (`column`='value')");




$db->string();
$db([
	'table'		=> 'table',
	'group'		=> 'column'
]);
pudlTest($db, "SELECT * FROM `table` GROUP BY column");




$db->string();
$db([
	'table'		=> 'table',
	'group'		=> ['column']
]);
pudlTest($db, "SELECT * FROM `table` GROUP BY `column`");




$db->string();
$db([
	'table'		=> 'table',
	'group'		=> ['column1', 'column2']
]);
pudlTest($db, "SELECT * FROM `table` GROUP BY `column1`, `column2`");




$db->string();
$db([
	'table'		=> 'table',
	'order'		=> 'column'
]);
pudlTest($db, "SELECT * FROM `table` ORDER BY column");




$db->string();
$db([
	'table'		=> 'table',
	'order'		=> ['column']
]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `column`");




$db->string();
$db([
	'table'		=> 'table',
	'order'		=> ['column1', 'column2']
]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `column1`, `column2`");




$db->string();
$db([
	'table'		=> 'table',
	'limit'		=> 5
]);
pudlTest($db, "SELECT * FROM `table` LIMIT 5");




$db->string();
$db([
	'table'		=> 'table',
	'offset'	=> 10
]);
pudlTest($db, "SELECT * FROM `table` LIMIT 18446744073709551615 OFFSET 10");




$db->string();
$db([
	'table'		=> 'table',
	'limit'		=> 5,
	'offset'	=> 10
]);
pudlTest($db, "SELECT * FROM `table` LIMIT 5 OFFSET 10");




$db->string();
$db([
	'table'		=> 'table',
	'limit'		=> [5, 10],
]);
pudlTest($db, "SELECT * FROM `table` LIMIT 5 OFFSET 10");




$db->string();
$db([
	'table'		=> 'table',
	'having'	=> 'column1=column2',
]);
pudlTest($db, "SELECT * FROM `table` HAVING (`column1`=`column2`)");




$db->string();
$db([
	'table'		=> 'table',
	'having'	=> ['column1=column2'],
]);
pudlTest($db, "SELECT * FROM `table` HAVING (`column1`=`column2`)");




$db->string();
$db([
	'table'		=> 'table',
	'having'	=> ['column' => 'value'],
]);
pudlTest($db, "SELECT * FROM `table` HAVING (`column`='value')");




$db->string();
$db([
	'explain'	=> true,
	'table'		=> 'table',
]);
pudlTest($db, "EXPLAIN SELECT * FROM `table`");




$db->string();
$db([
	'distinct'	=> true,
	'table'		=> 'table',
]);
pudlTest($db, "SELECT DISTINCT * FROM `table`");
