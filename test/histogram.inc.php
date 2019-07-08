<?php



$db->string()->histogramUpdate('table', 'column');
pudlTest($db, 'ANALYZE TABLE `table` UPDATE HISTOGRAM ON `column`');



$db->string()->histogramUpdate('table', ['column']);
pudlTest($db, 'ANALYZE TABLE `table` UPDATE HISTOGRAM ON `column`');



$db->string()->histogramUpdate('table', ['column1', 'column2']);
pudlTest($db, 'ANALYZE TABLE `table` UPDATE HISTOGRAM ON `column1`, `column2`');



$db->string()->histogramUpdate('table', ['column1', 'column2'], 5);
pudlTest($db, 'ANALYZE TABLE `table` UPDATE HISTOGRAM ON `column1`, `column2` WITH 5 BUCKETS');



$db->string()->histogramUpdate('table', ['column1', 'column2'], 5, true);
pudlTest($db, 'ANALYZE NO_WRITE_TO_BINLOG TABLE `table` UPDATE HISTOGRAM ON `column1`, `column2` WITH 5 BUCKETS');



$db->string()->histogramDrop('table', 'column');
pudlTest($db, 'ANALYZE TABLE `table` DROP HISTOGRAM ON `column`');



$db->string()->histogramDrop('table', ['column']);
pudlTest($db, 'ANALYZE TABLE `table` DROP HISTOGRAM ON `column`');



$db->string()->histogramDrop('table', ['column1', 'column2']);
pudlTest($db, 'ANALYZE TABLE `table` DROP HISTOGRAM ON `column1`, `column2`');



$db->string()->histogramDrop('table', ['column1', 'column2'], true);
pudlTest($db, 'ANALYZE NO_WRITE_TO_BINLOG TABLE `table` DROP HISTOGRAM ON `column1`, `column2`');
