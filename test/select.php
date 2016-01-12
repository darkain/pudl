<?php


//TEST ALL PARAMS ON
$db->string()->select(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT column FROM `table` WHERE (a=b) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->having(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'x=y',			//having
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT column FROM `table` WHERE (a=b) HAVING (x=y) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->group(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT column FROM `table` WHERE (a=b) GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->groupHaving(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'x=y',			//having
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT column FROM `table` WHERE (a=b) GROUP BY grouped HAVING (x=y) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->orderGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT *, COUNT(*) FROM (SELECT column FROM `table` WHERE (a=b) ORDER BY sorted) groupbyorderby GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->orderGroupEx(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'inside',		//inner_group
	'outside',		//outer_group
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT *, COUNT(*) FROM (SELECT column FROM `table` WHERE (a=b) GROUP BY inside ORDER BY sorted) groupbyorderby GROUP BY outside ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->selectJoin(
	'column',		//col
	'table1',		//table
	'table2',		//join_table
	'x=y',			//join_clause
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT column FROM `table1` LEFT JOIN (`table2`) ON (x=y) WHERE (a=b) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->distinct(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT DISTINCT column FROM `table` WHERE (a=b) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->distinctGroup(
	'column',		//col
	'table',		//table
	'a=b',			//clause
	'grouped',		//group
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT DISTINCT * FROM (SELECT column FROM `table` WHERE (a=b) ORDER BY sorted) groupbyorderby GROUP BY grouped ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');




$db->string()->distinctJoin(
	'column',		//col
	'table1',		//table
	'table2',		//join_table
	'x=y',			//join_clause
	'a=b',			//clause
	'sorted',		//order
	10,				//limit
	20,				//offset
	true			//lock
);
pudlTest('SELECT DISTINCT column FROM `table1` LEFT JOIN (`table2`) ON (x=y) WHERE (a=b) ORDER BY sorted LIMIT 10 OFFSET 20 FOR UPDATE');
