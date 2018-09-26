<?php


//TEST ALL PARAMS ON
$db->string()->select(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT `column` FROM `table` WHERE (`a`=`b`) ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->having(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'x=y',			//having
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT `column` FROM `table` WHERE (`a`=`b`) HAVING (`x`=`y`) ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->group(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT `column` FROM `table` WHERE (`a`=`b`) GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->groupHaving(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'x=y',			//having
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT `column` FROM `table` WHERE (`a`=`b`) GROUP BY grouped HAVING (`x`=`y`) ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->orderGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT *, COUNT(*) FROM (SELECT `column` FROM `table` WHERE (`a`=`b`) ORDER BY sorted) `x_pudl_alias_1` GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20');




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
pudlTest('SELECT *, COUNT(*) FROM (SELECT `column` FROM `table` WHERE (`a`=`b`) GROUP BY inside ORDER BY sorted) `x_pudl_alias_2` GROUP BY outside ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->distinct(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT DISTINCT `column` FROM `table` WHERE (`a`=`b`) ORDER BY sorted LIMIT 10 OFFSET 20');




$db->string()->distinctGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT DISTINCT * FROM (SELECT `column` FROM `table` WHERE (`a`=`b`) ORDER BY sorted) `x_pudl_alias_3` GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20');
