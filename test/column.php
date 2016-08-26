<?php

//SELECT statement, choosing which columns to return
$db->string()->select('column', 'table');
pudlTest('SELECT column FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column'], 'table');
pudlTest('SELECT column FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', 'column2'], 'table');
pudlTest('SELECT column1, column2 FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', pudl::unhex('01fa')], 'table');
pudlTest("SELECT column1, UNHEX('01fa') FROM `table`");




//SELECT statement, choosing which columns to return
$db->string()->select(['x'=>'column1', 'y'=>'column2'], 'table');
pudlTest('SELECT `column1` AS `x`, `column2` AS `y` FROM `table`');
