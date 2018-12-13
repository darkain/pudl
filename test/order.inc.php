<?php

$db->string()->row('table', false, 'column');
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` LIMIT 1');




//$db->string()->row('table', false, 'column DESC');
//pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` DESC LIMIT 1');




//IF $order IS FALSE, IGNORE IT
$db->string()->row('table', false, false);
pudlTest($db, 'SELECT * FROM `table` LIMIT 1');




//IF $order IS AN EMPTY ARRAY, IGNORE IT
$db->string()->row('table', false, []);
pudlTest($db, 'SELECT * FROM `table` LIMIT 1');




//IF $order IS INTEGER 0, IGNORE IT
$db->string()->row('table', false, 0);
pudlTest($db, 'SELECT * FROM `table` LIMIT 1');




$db->string()->row('table', false, ['column']);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` LIMIT 1');




$db->string()->row('table', false, ['column'=>pudl::desc()]);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` DESC LIMIT 1');




$db->string()->row('table', false, pudl::desc('column'));
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` DESC LIMIT 1');




$db->string()->row('table', false, [pudl::desc('column')]);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column` DESC LIMIT 1');




$db->string()->row('table', false, ['column1', 'column2']);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column1`, `column2` LIMIT 1');




$db->string()->row('table', false, ['column1'=>pudl::asc(), 'column2']);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column1` ASC, `column2` LIMIT 1');




$db->string()->row('table', false, ['column1'=>pudl::desc(), 'column2']);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column1` DESC, `column2` LIMIT 1');




$db->string()->row('table', false, ['column1'=>pudl::asc(), 'column2'=>pudl::desc()]);
pudlTest($db, 'SELECT * FROM `table` ORDER BY `column1` ASC, `column2` DESC LIMIT 1');




$db->string()->row('table', false, ['a'=>'column1', 'b'=>'column2']);
pudlTest($db, "SELECT * FROM `table` ORDER BY `a`='column1', `b`='column2' LIMIT 1");




$db->string()->row('table', false, ['a'=>NULL, 'b'=>pudl::neq(NULL)]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `a` IS NULL, `b` IS NOT NULL LIMIT 1");




$db->string()->row('table', false, [['column1'], ['column2']]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `column1`, `column2` LIMIT 1");




$db->string()->row('table', false, [['a'=>'value1'], ['b'=>'value2']]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `a`='value1', `b`='value2' LIMIT 1");




$db->string()->row('table', false, [['a=column1'], ['b=column2']]);
pudlTest($db, "SELECT * FROM `table` ORDER BY `a`=`column1`, `b`=`column2` LIMIT 1");




$db->string()->row('table', false, [['a'=>'value1', 'b'=>'value2']]);
pudlTest($db, "SELECT * FROM `table` ORDER BY (`a`='value1' OR `b`='value2') LIMIT 1");




$db->string()->row('table', false, [['a=column1', 'b=column2']]);
pudlTest($db, "SELECT * FROM `table` ORDER BY (`a`=`column1` OR `b`=`column2`) LIMIT 1");




$db->string()->row('table', false, pudl::rand());
pudlTest($db, 'SELECT * FROM `table` ORDER BY RAND() LIMIT 1');




$db->string()->row('table', false, [pudl::rand()]);
pudlTest($db, 'SELECT * FROM `table` ORDER BY RAND() LIMIT 1');
