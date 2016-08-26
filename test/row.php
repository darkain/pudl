<?php

//SELECT statement shortcut to get a single row
//Returns associative array instead of a pudlResult object
$db->string()->row('table');
pudlTest('SELECT * FROM `table` LIMIT 1');




//SELECT statement shortcut to get a single row using a clause
//Returns associative array instead of a pudlResult object
$db->string()->row('table', 'column=value');
pudlTest('SELECT * FROM `table` WHERE (column=value) LIMIT 1');




//SELECT statement shortcut to get multiple rows
//Returns array of associative array instead of a pudlResult object
$db->string()->rows('table');
pudlTest('SELECT * FROM `table`');




//SELECT statement shortcut to get multiple rows using a clause
//Returns array of associative array instead of a pudlResult object
$db->string()->rows('table', 'column=value');
pudlTest('SELECT * FROM `table` WHERE (column=value)');




//SELECT statement shortcut to get a single row based on column STRING value
//Returns associative array instead of a pudlResult object
$db->string()->rowId('table', 'column', 'value');
pudlTest("SELECT * FROM `table` WHERE (`column`='value') LIMIT 1");




//SELECT statement shortcut to get a single row based on column FUNCTION value
//Returns associative array instead of a pudlResult object
$db->string()->rowId('table', 'column', pudl::unhex('0123DEADBEEF0123'));
pudlTest("SELECT * FROM `table` WHERE (`column`=UNHEX('0123DEADBEEF0123')) LIMIT 1");




//SELECT statement shortcut to get a single row based on LIKE comparison
//Returns associative array instead of a pudlResult object
$db->string()->rowId('table', 'column', pudl::like('search'));
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%search%') LIMIT 1");




//SELECT statement shortcut to get a single row based on a passed in associative array
//Returns associative array instead of a pudlResult object
$array = ['column' => 5];
$db->string()->rowId('table', 'column', $array);
pudlTest("SELECT * FROM `table` WHERE (`column`=5) LIMIT 1");




//SELECT statement shortcut to get a single row based on a passed in associative array
//Returns associative array instead of a pudlResult object
$object = new stdClass;
$object->column = 'value';
$db->string()->rowId('table', 'column', $object);
pudlTest("SELECT * FROM `table` WHERE (`column`='value') LIMIT 1");




//SELECT statement shortcut to get multiple rows based on column value
//Returns array of associative array instead of a pudlResult object
$db->string()->rowsId('table', 'column', 'value');
pudlTest("SELECT * FROM `table` WHERE (`column`='value')");
