<?php



$db->string()->drop('table', false);
pudlTest("DROP TABLE IF EXISTS `table`");




$db->string()->drop('table', false, NULL);
pudlTest("DROP TABLE IF EXISTS `table`");




$db->string()->drop('table', false, true);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', false, false);
pudlTest("DROP TABLE IF EXISTS `table` NOWAIT");




$db->string()->drop('table', false, 1);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', false, 10);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 10");




$db->string()->drop('table', false, 2.3);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 2");




$db->string()->drop('table', false, []);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 0");




$db->string()->drop('table', false, ['test']);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->drop('table', false, [10]);
pudlTest("DROP TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table`");




$db->string()->drop('table', true, NULL);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table`");




$db->string()->drop('table', true, true);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true, false);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` NOWAIT");




$db->string()->drop('table', true, 1);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('table', true, 10);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 10");




$db->string()->drop('table', true, 2.3);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 2");




$db->string()->drop('table', true, []);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 0");




$db->string()->drop('table', true, ['test']);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->drop('table', true, [10]);
pudlTest("DROP TEMPORARY TABLE IF EXISTS `table` WAIT 1");




$db->string()->drop('database.table', false);
pudlTest('DROP TABLE IF EXISTS `database`.`table`');




$db->string()->drop(['table']);
pudlTest('DROP TEMPORARY TABLE IF EXISTS `table`');




$db->string()->drop(['table'], false);
pudlTest('DROP TABLE IF EXISTS `table`');




$db->string()->drop(['database.table'], false);
pudlTest('DROP TABLE IF EXISTS `database`.`table`');




$db->string()->drop(['database.table1', 'database.table2'], false);
pudlTest('DROP TABLE IF EXISTS `database`.`table1`, `database`.`table2`');




$db->string()->drop(['table1', 'table2', 'table3']);
pudlTest('DROP TEMPORARY TABLE IF EXISTS `table1`, `table2`, `table3`');




$db->string()->drop(['table1', 'table2', 'table3'], false);
pudlTest('DROP TABLE IF EXISTS `table1`, `table2`, `table3`');
