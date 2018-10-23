<?php


$db->unionStart();
$db->rows('table1');
$db->rows('table2');
$db->string()->unionEnd();
pudlTest($db, '(SELECT * FROM `table1`) UNION (SELECT * FROM `table2`)');




$db->unionStart();
$db->rows('table1');
$db->rows('table2');
$db->string()->unionDistinct();
pudlTest($db, '(SELECT * FROM `table1`) UNION DISTINCT (SELECT * FROM `table2`)');




$db->unionStart();
$db->rows('table1');
$db->rows('table2');
$db->string()->unionAll();
pudlTest($db, '(SELECT * FROM `table1`) UNION ALL (SELECT * FROM `table2`)');



/*
NEED A BETTER WAY TO TEST DYNAMIC ALIAS NAME GENERATION
$db->unionStart();
$db->rows('table1');
$db->rows('table2');
$db->string()->unionGroup('column');
pudlTest($db, '(SELECT * FROM `table1`) UNION (SELECT * FROM `table2`)');
*/



try {
	$db->unionEnd();
	pudlTest($db, 'pudlException');
} catch (pudlMethodException $error) {
	pudlError($error, 'Invalid call to pudlUnion::_union');
}
