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
