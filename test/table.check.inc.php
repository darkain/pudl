<?php



$db->string()->check('table');
pudlTest($db, 'CHECK TABLE `table`');



$db->string()->check(['table']);
pudlTest($db, 'CHECK TABLE `table`');



$db->string()->check(['table1', 'table2']);
pudlTest($db, 'CHECK TABLE `table1`, `table2`');



$db->string()->check('table', 'upgrade');
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE');



$db->string()->check('table', 'quick');
pudlTest($db, 'CHECK TABLE `table` QUICK');



$db->string()->check('table', 'fast');
pudlTest($db, 'CHECK TABLE `table` FAST');



$db->string()->check('table', 'medium');
pudlTest($db, 'CHECK TABLE `table` MEDIUM');



$db->string()->check('table', 'extended');
pudlTest($db, 'CHECK TABLE `table` EXTENDED');



$db->string()->check('table', 'changed');
pudlTest($db, 'CHECK TABLE `table` CHANGED');



$db->string()->check('table', ['upgrade']);
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE');



$db->string()->check('table', ['quick']);
pudlTest($db, 'CHECK TABLE `table` QUICK');



$db->string()->check('table', ['fast']);
pudlTest($db, 'CHECK TABLE `table` FAST');



$db->string()->check('table', ['medium']);
pudlTest($db, 'CHECK TABLE `table` MEDIUM');



$db->string()->check('table', ['extended']);
pudlTest($db, 'CHECK TABLE `table` EXTENDED');



$db->string()->check('table', ['changed']);
pudlTest($db, 'CHECK TABLE `table` CHANGED');



$db->string()->check('table', ['extended', 'upgrade']);
pudlTest($db, 'CHECK TABLE `table` FOR UPGRADE EXTENDED');
