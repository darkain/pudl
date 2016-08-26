<?php

//SELECT statement
$db->string()->select('*', 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, joining two tables
$db->string()->select('*', ['table1', 'table2']);
pudlTest('SELECT * FROM `table1`, `table2`');




//SELECT statement, joining two tables, both with aliases
$db->string()->select('*', ['a'=>'table1', 'b'=>'table2']);
pudlTest('SELECT * FROM `table1` AS `a`, `table2` AS `b`');




//RENAME TABLE
$db->string()->rename('table1 TO table2');
pudlTest('RENAME TABLE table1 TO table2');




//RENAME TABLE
$db->string()->rename('table1', 'table2');
pudlTest('RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['table1' => 'table2']);
pudlTest('RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['database.table1' => 'database.table2']);
pudlTest('RENAME TABLE `database`.`table1` TO `database`.`table2`');




//RENAME TABLE - NOTE: swapTable() DOES THIS AUTOMATICALLY
$db->string()->rename([
	'table1'	=> 'tmp',
	'table2'	=> 'table1',
	'tmp'		=> 'table1',
]);
pudlTest('RENAME TABLE `table1` TO `tmp`, `table2` TO `table1`, `tmp` TO `table1`');




//DROP TABLE
$db->string()->drop('table');
pudlTest('DROP TEMPORARY TABLE IF EXISTS `table`');




//DROP TABLE
$db->string()->drop('table', false);
pudlTest('DROP TABLE IF EXISTS `table`');




//DROP TABLE
$db->string()->drop('database.table', false);
pudlTest('DROP TABLE IF EXISTS `database`.`table`');




//DROP TABLE
$db->string()->drop(['table']);
pudlTest('DROP TEMPORARY TABLE IF EXISTS `table`');




//DROP TABLE
$db->string()->drop(['table'], false);
pudlTest('DROP TABLE IF EXISTS `table`');




//DROP TABLE
$db->string()->drop(['database.table'], false);
pudlTest('DROP TABLE IF EXISTS `database`.`table`');




//DROP TABLE
$db->string()->drop(['table1', 'table2', 'table3']);
pudlTest('DROP TEMPORARY TABLE IF EXISTS `table1`, `table2`, `table3`');




//DROP TABLE
$db->string()->drop(['table1', 'table2', 'table3'], false);
pudlTest('DROP TABLE IF EXISTS `table1`, `table2`, `table3`');
