<?php



$db->string()->analyze('table');
pudlTest($db, 'ANALYZE TABLE `table`');



$db->string()->analyze(['table']);
pudlTest($db, 'ANALYZE TABLE `table`');



$db->string()->analyze(['table1', 'table2']);
pudlTest($db, 'ANALYZE TABLE `table1`, `table2`');



$db->string()->analyze('table', true);
pudlTest($db, 'ANALYZE NO_WRITE_TO_BINLOG TABLE `table`');



$db->string()->analyze(['table'], true);
pudlTest($db, 'ANALYZE NO_WRITE_TO_BINLOG TABLE `table`');



$db->string()->analyze(['table1', 'table2'], true);
pudlTest($db, 'ANALYZE NO_WRITE_TO_BINLOG TABLE `table1`, `table2`');



$db->string()->analyze('table', false);
pudlTest($db, 'ANALYZE TABLE `table`');



$db->string()->analyze(['table'], false);
pudlTest($db, 'ANALYZE TABLE `table`');



$db->string()->analyze(['table1', 'table2'], false);
pudlTest($db, 'ANALYZE TABLE `table1`, `table2`');
