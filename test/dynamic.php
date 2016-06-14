<?php


$db->string()->select(pudl::dynamic_binary('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS BINARY) FROM `table`");


$db->string()->select(pudl::dynamic_char('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS CHAR) FROM `table`");


$db->string()->select(pudl::dynamic_date('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS DATE) FROM `table`");


$db->string()->select(pudl::dynamic_datetime('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS DATETIME) FROM `table`");


$db->string()->select(pudl::dynamic_decimal('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS DECIMAL) FROM `table`");


$db->string()->select(pudl::dynamic_double('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS DOUBLE) FROM `table`");


$db->string()->select(pudl::dynamic_integer('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS INTEGER) FROM `table`");


$db->string()->select(pudl::dynamic_signed('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS SIGNED) FROM `table`");


$db->string()->select(pudl::dynamic_time('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS TIME) FROM `table`");


$db->string()->select(pudl::dynamic_unsigned('table.column', 'dynamic'), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS UNSIGNED) FROM `table`");




$db->string()->select(pudl::dynamic_binary('table.column', 'dynamic', 10), 'table');
pudlTest("SELECT COLUMN_GET(`table`.`column`, 'dynamic' AS BINARY(10)) FROM `table`");




$db->string()->select(
	pudl::dynamic_integer(
		pudl::dynamic_binary('column', 'name'),
		'dynamic'
	),
	'table'
);
pudlTest("SELECT COLUMN_GET(COLUMN_GET(`column`, 'name' AS BINARY), 'dynamic' AS INTEGER) FROM `table`");




$db->string()->select(
	pudl::dynamic('parent.child:i'),
	'table'
);
pudlTest("SELECT COLUMN_GET(`parent`, 'child' AS INTEGER) FROM `table`");




$db->string()->select(
	pudl::dynamic('parent.child:f'),
	'table'
);
pudlTest("SELECT COLUMN_GET(`parent`, 'child' AS DOUBLE) FROM `table`");




$db->string()->select(
	pudl::dynamic('parent.child.subchild:c'),
	'table'
);
pudlTest("SELECT COLUMN_GET(COLUMN_GET(`parent`, 'child' AS BINARY), 'subchild' AS CHAR) FROM `table`");




//SELECT statement with a single clause with dynamic column definition
$db->string()->select('*', 'table', ['column#dynamic:i'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(`column`, 'dynamic' AS INTEGER)='value')");




//SELECT statement with a single clause with sub-dynamic column definition
$db->string()->select('*', 'table', ['column#dynamic.property:s'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(COLUMN_GET(`column`, 'dynamic' AS BINARY), 'property' AS CHAR)='value')");




//SELECT statement with a single clause with sub-sub-dynamic column definition
$db->string()->select('*', 'table', ['column#dynamic.property.sub:s'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(COLUMN_GET(COLUMN_GET(`column`, 'dynamic' AS BINARY), 'property' AS BINARY), 'sub' AS CHAR)='value')");




//SELECT statement with a single clause with table and dynamic column definition
$db->string()->select('*', 'table', ['table.column#dynamic:c'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(`table`.`column`, 'dynamic' AS CHAR)='value')");




//SELECT statement with a single clause with table and sub-dynamic column definition
$db->string()->select('*', 'table', ['table.column#dynamic.property:f'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(COLUMN_GET(`table`.`column`, 'dynamic' AS BINARY), 'property' AS DOUBLE)='value')");