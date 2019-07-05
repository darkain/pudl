<?php



//CREATE TABLE
$db->string()->check('table');
pudlTest($db, 'CHECK TABLE `table`');



//CREATE TABLE
$db->string()->check(['table1', 'table2']);
pudlTest($db, 'CHECK TABLE `table1`, `table2`');



//CREATE TABLE
$db->string()->check('table', 'upgrade');
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE');



//CREATE TABLE
$db->string()->check('table', 'quick');
pudlTest($db, 'CHECK TABLE `table` QUICK');



//CREATE TABLE
$db->string()->check('table', 'fast');
pudlTest($db, 'CHECK TABLE `table` FAST');



//CREATE TABLE
$db->string()->check('table', 'medium');
pudlTest($db, 'CHECK TABLE `table` MEDIUM');



//CREATE TABLE
$db->string()->check('table', 'extended');
pudlTest($db, 'CHECK TABLE `table` EXTENDED');



//CREATE TABLE
$db->string()->check('table', 'changed');
pudlTest($db, 'CHECK TABLE `table` CHANGED');



//CREATE TABLE
$db->string()->check('table', ['upgrade']);
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE');



//CREATE TABLE
$db->string()->check('table', ['quick']);
pudlTest($db, 'CHECK TABLE `table` QUICK');



//CREATE TABLE
$db->string()->check('table', ['fast']);
pudlTest($db, 'CHECK TABLE `table` FAST');



//CREATE TABLE
$db->string()->check('table', ['medium']);
pudlTest($db, 'CHECK TABLE `table` MEDIUM');



//CREATE TABLE
$db->string()->check('table', ['extended']);
pudlTest($db, 'CHECK TABLE `table` EXTENDED');



//CREATE TABLE
$db->string()->check('table', ['changed']);
pudlTest($db, 'CHECK TABLE `table` CHANGED');



//CREATE TABLE
$db->string()->check('table', ['extended', 'upgrade']);
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE EXTENDED');
