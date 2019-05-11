<?php



//CREATE TABLE
$db->string()->create('table', 'column int');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int)');




//CREATE TABLE
$db->string()->create('table', ['column int']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int)');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT)');




//CREATE TABLE
$db->string()->create('table', ['column1'=>'int', 'column2'=>'char', 'column3'=>'float']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column1` INT, `column2` CHAR, `column3` FLOAT)');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT, PRIMARY KEY (column))');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)', 'ENGINE=InnoDB');
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', 'column int', 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column int'], 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (column int, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], 'PRIMARY KEY (column)', ['ENGINE'=>'InnoDB']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT, PRIMARY KEY (column)) ENGINE=InnoDB');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE=InnoDB', 'CHARSET=ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE'=>'InnoDB', 'CHARSET=ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE=InnoDB', 'CHARSET'=>'ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT) ENGINE=InnoDB CHARSET=ascii');




//CREATE TABLE
$db->string()->create('table', ['column'=>'int'], false, ['ENGINE'=>'InnoDB', 'CHARSET'=>'ascii']);
pudlTest($db, 'CREATE TABLE IF NOT EXISTS `table` (`column` INT) ENGINE=InnoDB CHARSET=ascii');




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
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column1` INT COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'test column', `column2` CHAR(256) NULL COMMENT 'another column', `column3` VARCHAR(256))");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'auto',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT PRIMARY KEY AUTOINCREMENT)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> ['bigint(10)', 'key' => 'auto'],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` BIGINT(10) PRIMARY KEY AUTOINCREMENT))");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'primary',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT PRIMARY KEY)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'key'		=> 'unique',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT UNIQUE)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'char(16)',
		'charset'	=> 'ascii',
		'default'	=> 'test',
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` CHAR(16) CHARACTER SET ascii DEFAULT 'test')");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'default'	=> 2,
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT DEFAULT 2)");




//CREATE TABLE
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'default'	=> NULL,
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT DEFAULT NULL)");



/*
//CREATE TABLE
//THIS IS BROKEN
$db->string()->create('table', [
	'column'		=> [
		'type'		=> 'int',
		'comment'	=> " 'string' ",
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` INT COMMENT ' ''string'' ')");
*/





//CREATE TABLE - SET
$db->string()->create('table', [
	'column'		=> [
		'type'		=> pudl::set('1', '2', '3'),
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` SET('1','2','3'))");




//CREATE TABLE - SET
$db->string()->create('table', [
	'column'		=> [
		'type'		=> pudl::set(['1', '2', '3']),
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` SET('1','2','3'))");





//CREATE TABLE - ENUM
$db->string()->create('table', [
	'column'		=> [
		'type'		=> pudl::enum('1', '2', '3'),
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` ENUM('1','2','3'))");




//CREATE TABLE - ENUM
$db->string()->create('table', [
	'column'		=> [
		'type'		=> pudl::enum(['1', '2', '3']),
	],
]);
pudlTest($db, "CREATE TABLE IF NOT EXISTS `table` (`column` ENUM('1','2','3'))");
