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




$db->string()->distinct(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20				//offset
);
pudlTest('SELECT DISTINCT `column` FROM `table` WHERE (`a`=`b`) ORDER BY sorted LIMIT 10 OFFSET 20');
