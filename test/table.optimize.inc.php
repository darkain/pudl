<?php



//CREATE TABLE
$db->string()->optimize('table');
pudlTest($db, 'OPTIMIZE TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table']);
pudlTest($db, 'OPTIMIZE TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table1', 'table2']);
pudlTest($db, 'OPTIMIZE TABLE `table1`, `table2`');



//CREATE TABLE
$db->string()->optimize('table', true);
pudlTest($db, 'OPTIMIZE NO_WRITE_TO_BINLOG TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table'], true);
pudlTest($db, 'OPTIMIZE NO_WRITE_TO_BINLOG TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table1', 'table2'], true);
pudlTest($db, 'OPTIMIZE NO_WRITE_TO_BINLOG TABLE `table1`, `table2`');



//CREATE TABLE
$db->string()->optimize('table', false);
pudlTest($db, 'OPTIMIZE TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table'], false);
pudlTest($db, 'OPTIMIZE TABLE `table`');



//CREATE TABLE
$db->string()->optimize(['table1', 'table2'], false);
pudlTest($db, 'OPTIMIZE TABLE `table1`, `table2`');
