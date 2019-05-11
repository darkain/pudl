<?php

//SELECT statement
$db->string()->select('*', 'table');
pudlTest($db, 'SELECT * FROM `table`');




//SELECT statement, joining two tables
$db->string()->select('*', ['table1', 'table2']);
pudlTest($db, 'SELECT * FROM `table1`, `table2`');




//SELECT statement, joining two tables, both with aliases
$db->string()->select('*', ['a'=>'table1', 'b'=>'table2']);
pudlTest($db, 'SELECT * FROM `table1` AS `a`, `table2` AS `b`');




//RENAME TABLE
$db->string()->rename('table1 TO table2');
pudlTest($db, 'RENAME TABLE table1 TO table2');




//RENAME TABLE
$db->string()->rename('table1', 'table2');
pudlTest($db, 'RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['table1' => 'table2']);
pudlTest($db, 'RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['database.table1' => 'database.table2']);
pudlTest($db, 'RENAME TABLE `database`.`table1` TO `database`.`table2`');




//RENAME TABLE - NOTE: swapTable() DOES THIS AUTOMATICALLY
$db->string()->rename([
	'table1'	=> 'tmp',
	'table2'	=> 'table1',
	'tmp'		=> 'table1',
]);
pudlTest($db, 'RENAME TABLE `table1` TO `tmp`, `table2` TO `table1`, `tmp` TO `table1`');
