<?php



$db->string()->orderGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest($db, 'SELECT *, COUNT(*) FROM (SELECT `column` FROM `table` WHERE `a`=`b` ORDER BY `sorted`) `x_pudl_alias_1` GROUP BY `grouped` ORDER BY `sorted` LIMIT 10 OFFSET 20');




$db->string()->orderGroupEx(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'inside',		//inner_group
	'outside',		//outer_group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest($db, 'SELECT *, COUNT(*) FROM (SELECT `column` FROM `table` WHERE `a`=`b` GROUP BY `inside` ORDER BY `sorted`) `x_pudl_alias_2` GROUP BY `outside` ORDER BY `sorted` LIMIT 10 OFFSET 20');




$db->string()->distinctGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest($db, 'SELECT DISTINCT * FROM (SELECT `column` FROM `table` WHERE `a`=`b` ORDER BY `sorted`) `x_pudl_alias_3` GROUP BY `grouped` ORDER BY `sorted` LIMIT 10 OFFSET 20');




$prefix = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> ['default_'],
]);
$prefix->string()->selex([
	'table' => ['tbl' => 'table'],
	'group' => ['column'],
	'order' => ['tbl.vendor_id'	=> 'value'],
]);
pudlTest($prefix, "SELECT *, COUNT(*) FROM (SELECT * FROM `default_table` AS `tbl` ORDER BY `tbl`.`vendor_id`='value') `x_pudl_alias_4` GROUP BY `column` ORDER BY `vendor_id`='value'");



