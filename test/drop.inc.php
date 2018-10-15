<?php



$db->string()->drop('table', false);
pudlTest($db, "DROP TABLE IF EXISTS `table`");




$db->string()->drop('table', false, NULL);
pudlTest($db, "DROP TABLE IF EXISTS `table`");




$db->string()->drop('table', false, true);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', false, false);
pudlTest($db, "DROP TABLE IF EXISTS `table` NOWAIT");




$db->string()->drop('table', false, 1);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', false, 10);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 10");




$db->string()->drop('table', false, 2.3);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 2");




$db->string()->drop('table', false, []);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 0");




$db->string()->drop('table', false, ['test']);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->drop('table', false, [10]);
pudlTest($db, "DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table`");




$db->string()->drop('table', true, NULL);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table`");




$db->string()->drop('table', true, true);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true, false);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` NOWAIT");




$db->string()->drop('table', true, 1);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true, 10);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 10");




$db->string()->drop('table', true, 2.3);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 2");




$db->string()->drop('table', true, []);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 0");




$db->string()->drop('table', true, ['test']);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->drop('table', true, [10]);
pudlTest($db, "DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('database.table', false);
pudlTest($db, 'DROP TABLE IF EXISTS `database`.`table`');




$db->string()->drop(['table']);
pudlTest($db, 'DROP TEMPORARY TABLE IF EXISTS `table`');




$db->string()->drop(['table'], false);
pudlTest($db, 'DROP TABLE IF EXISTS `table`');




$db->string()->drop(['database.table'], false);
pudlTest($db, 'DROP TABLE IF EXISTS `database`.`table`');




$db->string()->drop(['database.table1', 'database.table2'], false);
pudlTest($db, 'DROP TABLE IF EXISTS `database`.`table1`, `database`.`table2`');




$db->string()->drop(['table1', 'table2', 'table3']);
pudlTest($db, 'DROP TEMPORARY TABLE IF EXISTS `table1`, `table2`, `table3`');




$db->string()->drop(['table1', 'table2', 'table3'], false);
pudlTest($db, 'DROP TABLE IF EXISTS `table1`, `table2`, `table3`');
