<?php

//SELECT statement
$db->string()->select('*', 'table');
pudlTest($db, 'SELECT * FROM `table`');




//SELECT statement, joining two tables
$db->string()->select('*', ['table1', 'table2']);
pudlTest($db, 'SELECT * FROM `table1`, `table2`');




//SELECT statement, joining two tables, both with aliases
$db->string()->select('*', ['a'=>'table1', 'b'=>'table2']);
pudlTest($db, 'SELECT * FROM `table1` AS `a`, `table2` AS `b`');




//RENAME TABLE
$db->string()->rename('table1 TO table2');
pudlTest($db, 'RENAME TABLE table1 TO table2');




//RENAME TABLE
$db->string()->rename('table1', 'table2');
pudlTest($db, 'RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['table1' => 'table2']);
pudlTest($db, 'RENAME TABLE `table1` TO `table2`');




//RENAME TABLE
$db->string()->rename(['database.table1' => 'database.table2']);
pudlTest($db, 'RENAME TABLE `database`.`table1` TO `database`.`table2`');




//RENAME TABLE - NOTE: swapTable() DOES THIS AUTOMATICALLY
$db->string()->rename([
	'table1'	=> 'tmp',
	'table2'	=> 'table1',
	'tmp'		=> 'table1',
]);
pudlTest($db, 'RENAME TABLE `table1` TO `tmp`, `table2` TO `table1`, `tmp` TO `table1`');




//CREATE TABLE
$db->string()->create('table', 'column int');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int)');




//CREATE TABLE
$db->string()->create('table', ['column int']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int)');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int)');




//CREATE TABLE
$db->string()->create('table', ['column1'=>'int', 'column2'=>'char', 'column3'=>'float']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column1` int, `column2` char, `column3` float)');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE=InnoDB', 'CHARSET=ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE'=>'InnoDB', 'CHARSET=ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE=InnoDB', 'CHARSET'=>'ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE'=>'InnoDB', 'CHARSET'=>'ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` int) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', [
	'column1'		=> [
		'type'		=> 'int',
		'collate'	=> 'utf8mb4_unicode_ci',
		'null'		=> false,
		'comment'	=> 'test column',
	],
	'column2'		=> [
		'type'		=> 'char(256)',
		'null'		=> true,
		'comment'	=> 'another column',
	],
	'column3'		=> [
		'type'		=> 'varchar(256)',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column1` int COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'test column', `column2` char(256) NULL COMMENT 'another column', `column3` varchar(256))");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'auto',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int PRIMARY KEY AUTOINCREMENT)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'primary',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int PRIMARY KEY)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'unique',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int UNIQUE)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'char(16)',
		'charset'	=> 'ascii',
		'default'	=> 'test',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` char(16) CHARACTER SET ascii DEFAULT 'test')");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'default'	=> 2,
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int DEFAULT 2)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'default'	=> NULL,
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int DEFAULT NULL)");



/*
//CREATE TABLE
//THIS IS BROKEN
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'comment'	=> " 'string' ",
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` int COMMENT ' ''string'' ')");
*/
