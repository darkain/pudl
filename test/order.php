<?php

$db->string()->row('table', false, 'column');
pudlTest('SELECT * FROM `table` ORDER BY column LIMIT 1');




$db->string()->row('table', false, 'column DESC');
pudlTest('SELECT * FROM `table` ORDER BY column DESC LIMIT 1');




$db->string()->row('table', false, ['column']);
pudlTest('SELECT * FROM `table` ORDER BY column LIMIT 1');




$db->string()->row('table', false, ['column DESC']);
pudlTest('SELECT * FROM `table` ORDER BY column DESC LIMIT 1');




$db->string()->row('table', false, ['column1', 'column2']);
pudlTest('SELECT * FROM `table` ORDER BY column1, column2 LIMIT 1');




$db->string()->row('table', false, ['column1 DESC', 'column2']);
pudlTest('SELECT * FROM `table` ORDER BY column1 DESC, column2 LIMIT 1');




$db->string()->row('table', false, ['a'=>'column1', 'b'=>'column2']);
pudlTest("SELECT * FROM `table` ORDER BY `a`='column1', `b`='column2' LIMIT 1");




//TODO: SUPPORT THIS SYNTAX
// $db->string()->row('table', false, [['a'=>'column1'], ['b'=>'column2']]);
// pudlTest("SELECT * FROM `table` ORDER BY `a`='column1', `b`='column2' LIMIT 1");




$db->string()->row('table', false, pudl::rand());
pudlTest('SELECT * FROM `table` ORDER BY RAND() LIMIT 1');




$db->string()->row('table', false, [pudl::rand()]);
pudlTest('SELECT * FROM `table` ORDER BY RAND() LIMIT 1');
