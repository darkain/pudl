<?php

//NOTE: 'table' IS PASSED THROUGH UNMODIFIED
//		NO PREFIXING, NO ESCAPING AT ALL!
$db->string()->lock('table READ');
pudlTest($db, 'LOCK TABLES table READ');




//NOTE: SAFETY IS ONLY APPLIED USING ARRAY SYNTAX
$db->string()->lock(['table']);
pudlTest($db, 'LOCK TABLES `table` WRITE');




$db->string()->lock(['table1', 'table2', 'table3']);
pudlTest($db, 'LOCK TABLES `table1` WRITE, `table2` WRITE, `table3` WRITE');




$db->string()->lock(['t1'=>'table1', 't2'=>'table2', 't3'=>'table3']);
pudlTest($db, 'LOCK TABLES `table1` AS `t1` WRITE, `table2` AS `t2` WRITE, `table3` AS `t3` WRITE');




$db->string()->lock(['read' => 'table']);
pudlTest($db, 'LOCK TABLES `table` READ');




$db->string()->lock(['write' => 'table']);
pudlTest($db, 'LOCK TABLES `table` WRITE');




$db->string()->lock(['read' => ['table1', 'table2', 'table3']]);
pudlTest($db, 'LOCK TABLES `table1` READ, `table2` READ, `table3` READ');




$db->string()->lock(['read' => ['t1'=>'table1', 't2'=>'table2']]);
pudlTest($db, 'LOCK TABLES `table1` AS `t1` READ, `table2` AS `t2` READ');




$db->string()->lock(['write' => ['table1', 'table2', 'table3']]);
pudlTest($db, 'LOCK TABLES `table1` WRITE, `table2` WRITE, `table3` WRITE');




$db->string()->lock(['write' => ['t1'=>'table1', 't2'=>'table2']]);
pudlTest($db, 'LOCK TABLES `table1` AS `t1` WRITE, `table2` AS `t2` WRITE');




$db->string()->lock([
	'read'	=> ['r-table1', 'r-table2', 'r-table3'],
	'write'	=> ['w-table4', 'w-table5', 'w-table6'],
]);
pudlTest($db, 'LOCK TABLES `r-table1` READ, `r-table2` READ, `r-table3` READ, `w-table4` WRITE, `w-table5` WRITE, `w-table6` WRITE');




$db->string()->lock([
	'read'	=> ['r-table1', 'r-table2', 'r-table3'],
	'write'	=> ['w-table4', 'w-table5', 'w-table6'],
	'x-table7',
	'x-table8',
	'x-table9',
]);
pudlTest($db, 'LOCK TABLES `r-table1` READ, `r-table2` READ, `r-table3` READ, `w-table4` WRITE, `w-table5` WRITE, `w-table6` WRITE, `x-table7` WRITE, `x-table8` WRITE, `x-table9` WRITE');




$db->string()->lock([
	'read'	=> ['r1'=>'r-table1', 'r2'=>'r-table2', 'r-table3'],
	'write'	=> ['w-table4', 'w5'=>'w-table5', 'w6'=>'w-table6'],
	'x-table7',
	'x8'=>'x-table8',
	'x-table9',
]);
pudlTest($db, 'LOCK TABLES `r-table1` AS `r1` READ, `r-table2` AS `r2` READ, `r-table3` READ, `w-table4` WRITE, `w-table5` AS `w5` WRITE, `w-table6` AS `w6` WRITE, `x-table7` WRITE, `x-table8` AS `x8` WRITE, `x-table9` WRITE');
