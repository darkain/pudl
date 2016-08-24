<?php

$db->string()->row('table', false, 'column');
pudlTest('SELECT * FROM `table` ORDER BY column LIMIT 1');




$db->string()->row('table', false, 'column DESC');
pudlTest('SELECT * FROM `table` ORDER BY column DESC LIMIT 1');




//IF $order IS FALSE, IGNORE IT
$db->string()->row('table', false, false);
pudlTest('SELECT * FROM `table` LIMIT 1');




//IF $order IS AN EMPTY ARRAY, IGNORE IT
$db->string()->row('table', false, []);
pudlTest('SELECT * FROM `table` LIMIT 1');




//IF $order IS INTEGER 0, IGNORE IT
$db->string()->row('table', false, 0);
pudlTest('SELECT * FROM `table` LIMIT 1');




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




$db->string()->row('table', false, ['a'=>NULL, 'b'=>pudl::neq(NULL)]);
pudlTest("SELECT * FROM `table` ORDER BY `a` IS NULL, `b` IS NOT NULL LIMIT 1");




$db->string()->row('table', false, [['column1'], ['column2']]);
pudlTest("SELECT * FROM `table` ORDER BY column1, column2 LIMIT 1");




$db->string()->row('table', false, [['a'=>'column1'], ['b'=>'column2']]);
pudlTest("SELECT * FROM `table` ORDER BY `a`='column1', `b`='column2' LIMIT 1");




$db->string()->row('table', false, pudl::rand());
pudlTest('SELECT * FROM `table` ORDER BY RAND() LIMIT 1');




$db->string()->row('table', false, [pudl::rand()]);
pudlTest('SELECT * FROM `table` ORDER BY RAND() LIMIT 1');
