<?php



$db->string()->truncate('table');
pudlTest("TRUNCATE TABLE `table`");




$db->string()->truncate('table', NULL);
pudlTest("TRUNCATE TABLE `table`");




$db->string()->truncate('table', true);
pudlTest("TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('table', false);
pudlTest("TRUNCATE TABLE `table` NOWAIT");




$db->string()->truncate('table', 1);
pudlTest("TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('table', 10);
pudlTest("TRUNCATE TABLE `table` WAIT 10");




$db->string()->truncate('table', 2.3);
pudlTest("TRUNCATE TABLE `table` WAIT 2");




$db->string()->truncate('table', []);
pudlTest("TRUNCATE TABLE `table` WAIT 0");




$db->string()->truncate('table', ['test']);
pudlTest("TRUNCATE TABLE `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->truncate('table', [10]);
pudlTest("TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('database.table');
pudlTest('TRUNCATE TABLE `database`.`table`');
