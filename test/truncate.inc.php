<?php



$db->string()->truncate('table');
pudlTest($db, "TRUNCATE TABLE `table`");




$db->string()->truncate('table', NULL);
pudlTest($db, "TRUNCATE TABLE `table`");




$db->string()->truncate('table', true);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('table', false);
pudlTest($db, "TRUNCATE TABLE `table` NOWAIT");




$db->string()->truncate('table', 1);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('table', 10);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 10");




$db->string()->truncate('table', 2.3);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 2");




$db->string()->truncate('table', []);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 0");




$db->string()->truncate('table', ['test']);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 1");




//NOTE: WE DON'T INSPECT THE ARRAY, A NON-EMPTY ARRAY CONVERTED TO INT IS (1)
$db->string()->truncate('table', [10]);
pudlTest($db, "TRUNCATE TABLE `table` WAIT 1");




$db->string()->truncate('database.table');
pudlTest($db, 'TRUNCATE TABLE `database`.`table`');
