<?php

//NOTE: 'table' IS PASSED THROUGH UNMODIFIED
//		NO PREFIXING, NO ESCAPING AT ALL!
$db->string()->lock('table READ');
pudlTest('LOCK TABLES table READ');




//NOTE: SAFETY IS ONLY APPLIED USING ARRAY SYNTAX
$db->string()->lock(['table']);
pudlTest('LOCK TABLES `table` WRITE');




$db->string()->lock(['table1', 'table2', 'table3']);
pudlTest('LOCK TABLES `table1` WRITE, `table2` WRITE, `table3` WRITE');




$db->string()->lock(['read' => 'table']);
pudlTest('LOCK TABLES `table` READ');




$db->string()->lock(['write' => 'table']);
pudlTest('LOCK TABLES `table` WRITE');




$db->string()->lock(['read' => ['table1', 'table2', 'table3']]);
pudlTest('LOCK TABLES `table1` READ, `table2` READ, `table3` READ');




$db->string()->lock(['write' => ['table1', 'table2', 'table3']]);
pudlTest('LOCK TABLES `table1` WRITE, `table2` WRITE, `table3` WRITE');




$db->string()->lock([
	'read'	=> ['r-table1', 'r-table2', 'r-table3'],
	'write'	=> ['w-table4', 'w-table5', 'w-table6'],
]);
pudlTest('LOCK TABLES `r-table1` READ, `r-table2` READ, `r-table3` READ, `w-table4` WRITE, `w-table5` WRITE, `w-table6` WRITE');




$db->string()->lock([
	'read'	=> ['r-table1', 'r-table2', 'r-table3'],
	'write'	=> ['w-table4', 'w-table5', 'w-table6'],
	'x-table7',
	'x-table8',
	'x-table9',
]);
pudlTest('LOCK TABLES `r-table1` READ, `r-table2` READ, `r-table3` READ, `w-table4` WRITE, `w-table5` WRITE, `w-table6` WRITE, `x-table7` WRITE, `x-table8` WRITE, `x-table9` WRITE');
