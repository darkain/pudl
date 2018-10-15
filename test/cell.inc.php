<?php

//SELECT statement shortcut to get a single cell value
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column');
pudlTest($db, 'SELECT `column` FROM `table` LIMIT 1');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column', 'id=col');
pudlTest($db, 'SELECT `column` FROM `table` WHERE (`id`=`col`) LIMIT 1');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column', ['id=col']);
pudlTest($db, 'SELECT `column` FROM `table` WHERE (`id`=`col`) LIMIT 1');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$db->string()->cell('table', 'column', ['id'=>'value']);
pudlTest($db, "SELECT `column` FROM `table` WHERE (`id`='value') LIMIT 1");




//SELECT statement shortcut to get a single cell value using an ID
//Returns string of the cell's value (false if not found)
$db->string()->cellId('table', 'column', 'id', 'value');
pudlTest($db, "SELECT `column` FROM `table` WHERE (`id`='value') LIMIT 1");




//SELECT statement shortcut to get a single cell value using an ID
//Returns string of the cell's value (false if not found)
$db->string()->cellId('table', 'column', 'id', pudl::unhex('abcdef1230'));
pudlTest($db, "SELECT `column` FROM `table` WHERE (`id`=UNHEX('abcdef1230')) LIMIT 1");




//SELECT statement shortcut to get a single cell value using an ID
//Returns string of the cell's value (false if not found)
$db->string()->cellId('table', 'column', 'id', hex2bin('abcdef1230'));
pudlTest($db, "SELECT `column` FROM `table` WHERE (`id`=0xabcdef1230) LIMIT 1");




$db->string()->count('table');
pudlTest($db, "SELECT COUNT(*) FROM `table` LIMIT 1");




$db->string()->count('table', 'cell=1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`=1) LIMIT 1");




$db->string()->count('table', ['cell'=>10]);
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`=10) LIMIT 1");




$db->string()->count('table', 'cell > 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`>1) LIMIT 1");




$db->string()->count('table', 'cell < 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`<1) LIMIT 1");




$db->string()->count('table', 'cell >= 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`>=1) LIMIT 1");




$db->string()->count('table', 'cell <= 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`<=1) LIMIT 1");




$db->string()->count('table', 'cell != 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`!=1) LIMIT 1");




$db->string()->count('table', 'cell <=> 1');
pudlTest($db, "SELECT COUNT(*) FROM `table` WHERE (`cell`<=>1) LIMIT 1");




$db->string()->cell('table', pudl::_count());
pudlTest($db, "SELECT COUNT(*) FROM `table` LIMIT 1");




$db->string()->cell('table', pudl::_count('table'));
pudlTest($db, "SELECT COUNT(`table`) FROM `table` LIMIT 1");




$db->string()->cell('table', pudl::_count('table.*'));
pudlTest($db, "SELECT COUNT(`table`.*) FROM `table` LIMIT 1");




$db->string()->cell('table', [pudl::_count()]);
pudlTest($db, "SELECT COUNT(*) FROM `table` LIMIT 1");




$db->string()->cell('table', [pudl::_count('table')]);
pudlTest($db, "SELECT COUNT(`table`) FROM `table` LIMIT 1");




$db->string()->cell('table', [pudl::_count('table.*')]);
pudlTest($db, "SELECT COUNT(`table`.*) FROM `table` LIMIT 1");




$db->string()->cell('table', ['total' => pudl::_count()]);
pudlTest($db, "SELECT COUNT(*) AS `total` FROM `table` LIMIT 1");




$db->string()->cell('table', ['total' => pudl::_count('table')]);
pudlTest($db, "SELECT COUNT(`table`) AS `total` FROM `table` LIMIT 1");




$db->string()->cell('table', ['total' => pudl::_count('table.*')]);
pudlTest($db, "SELECT COUNT(`table`.*) AS `total` FROM `table` LIMIT 1");
