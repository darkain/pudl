<?php

//SELECT statement with a simple clause
$db->string()->select('*', 'table', true);
pudlTest($db, 'SELECT * FROM `table` WHERE 1');




//SELECT statement with a simple clause
//NOTE: 'false' is internally ignored for a clause
$db->string()->select('*', 'table', false);
pudlTest($db, 'SELECT * FROM `table`');




//SELECT statement with a simple clause
//NOTE: pass 'true' in an array to have it unmodified
$db->string()->select('*', 'table', [true]);
pudlTest($db, 'SELECT * FROM `table` WHERE TRUE');




//SELECT statement with a simple clause
//NOTE: pass 'false' in an array to have it unmodified
$db->string()->select('*', 'table', [false]);
pudlTest($db, 'SELECT * FROM `table` WHERE FALSE');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column1=column2');
pudlTest($db, 'SELECT * FROM `table` WHERE `column1`=`column2`');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column1=0');
pudlTest($db, 'SELECT * FROM `table` WHERE `column1`=0');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column1=1');
pudlTest($db, 'SELECT * FROM `table` WHERE `column1`=1');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column1=5.5');
pudlTest($db, 'SELECT * FROM `table` WHERE `column1`=5.5');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column1=-5.5');
pudlTest($db, 'SELECT * FROM `table` WHERE `column1`=-5.5');




//SELECT statement with a single clause with STRING value
$db->string()->select('*', 'table', ['column'=>'value']);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='value'");




//SELECT statement with a single clause with BINARY STRING value
$db->string()->select('*', 'table', ['column'=>"\r\n\0\x"]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`=0x0d0a005c78");




//SELECT statement with a single clause with NULL value
$db->string()->select('*', 'table', ['column'=>NULL]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column` IS NULL');




//SELECT statement with a single clause with INTEGER value
$db->string()->select('*', 'table', ['column'=>5]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column`=5');




//SELECT statement with a single clause with FLOAT value
$db->string()->select('*', 'table', ['column'=>2.3]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column`=2.3');




//SELECT statement with a single clause with FLOAT value including exponent
$db->string()->select('*', 'table', ['column'=>1.2e23]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column`=1.2E+23');




//SELECT statement with a single clause with NaN (Not a Number) value
$db->string()->select('*', 'table', ['column'=>NAN]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column` IS NULL');




//SELECT statement with a single clause with Infinite value
$db->string()->select('*', 'table', ['column'=>INF]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column` IS NULL');




//SELECT statement with a single clause with Negative Infinite value
$db->string()->select('*', 'table', ['column'=>-INF]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column` IS NULL');




//SELECT statement with a single clause with BOOLEAN value
$db->string()->select('*', 'table', ['column'=>true]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column`=TRUE');




//SELECT statement with a single clause with ARRAY value
$db->string()->select('*', 'table', ['column'=>[1,2,3]]);
pudlTest($db, 'SELECT * FROM `table` WHERE `column` IN (1, 2, 3)');




//SELECT statement with a single clause with complex ARRAY value
$db->string()->select('*', 'table', ['column'=>[1.2e23,'2',3, NULL, [5]]]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (1.2E+23, '2', 3, NULL, 5)");




//SELECT statement with a single clause with table definition
$db->string()->select('*', 'table', ['table.column'=>'value']);
pudlTest($db, "SELECT * FROM `table` WHERE `table`.`column`='value'");




//SELECT statement with a single clause with table definition (spaced)
$db->string()->select('*', 'table', ['table . column'=>'value']);
pudlTest($db, "SELECT * FROM `table` WHERE `table`.`column`='value'");




//SELECT statement with a LIKE clause (left and right search)
$db->string()->select('*', 'table', ['column'=>pudl::like('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` LIKE '%value%'");




//SELECT statement with a LIKE clause (left search)
$db->string()->select('*', 'table', ['column'=>pudl::likeLeft('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` LIKE '%value'");




//SELECT statement with a LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::likeRight('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` LIKE 'value%'");




//SELECT statement with a NOT LIKE clause (left and right search)
$db->string()->select('*', 'table', ['column'=>pudl::notLike('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT LIKE '%value%'");




//SELECT statement with a NOT LIKE clause (left search)
$db->string()->select('*', 'table', ['column'=>pudl::notLikeLeft('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT LIKE '%value'");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::notLikeRight('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT LIKE 'value%'");




//SELECT statement with a LIKE clause, column instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',`column2`,'%')");




//SELECT statement with a LIKE clause, column instead of string (left)
$db->string()->select('*', 'table', ['column1'=>pudl::likeLeft( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',`column2`,'')");




//SELECT statement with a LIKE clause, column instead of string (right)
$db->string()->select('*', 'table', ['column1'=>pudl::likeRight( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('',`column2`,'%')");




//SELECT statement with a LIKE clause, function instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::hex('value') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',HEX('value'),'%')");




//SELECT statement with a LIKE clause, function chain instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like()->hex('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',HEX('value'),'%')");




//SELECT statement with a LIKE clause, raw SQL instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::raw("X'65'") )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',X'65','%')");




//SELECT statement with a LIKE clause, raw SQL chain instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like()->raw("X'65'")]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1` LIKE CONCAT('%',X'65','%')");




//SELECT statement with a LIKE clause (left and right search)
$db->string()->select('*', 'table', [pudl::like(pudl::column('column'), 'value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` LIKE '%value%'");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::eq(NULL)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IS NULL");




//SELECT statement where column isnt equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::eq(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`=5");




//SELECT statement where column isnt equal to float
$db->string()->select('*', 'table', ['column'=>pudl::eq(5.0E+90)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`=5.0E+90");




//SELECT statement where column in an array of integers
$db->string()->select('*', 'table', ['column'=>pudl::eq([5,7,9])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (5, 7, 9)");




//SELECT statement where column in an array of floats
$db->string()->select('*', 'table', ['column'=>pudl::eq([5.0E+90,7.0E+80,5.0E+70])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (5.0E+90, 7.0E+80, 5.0E+70)");




//SELECT statement where column in an array of string
$db->string()->select('*', 'table', ['column'=>pudl::eq(['5','7','9'])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN ('5', '7', '9')");




//SELECT statement where column in a mixed array
$db->string()->select('*', 'table', ['column'=>pudl::eq([5.0E+90,7,'9'])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (5.0E+90, 7, '9')");




//SELECT statement where column isnt equal to string
$db->string()->select('*', 'table', ['column'=>pudl::eq('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='value'");




//SELECT statement where column isnt equal to column
$db->string()->select('*', 'table', ['column1'=>pudl::eq( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1`=`column2`");




//SELECT statement where column isnt equal to column chain
$db->string()->select('*', 'table', ['column1'=>pudl::eq()->column('column2')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1`=`column2`");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::neq(NULL)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IS NOT NULL");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::neq([NULL])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN (NULL)");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::neq([])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN (NULL)");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::eq([])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (NULL)");




//SELECT statement where column isnt equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::neq(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`!=5");




//SELECT statement where column isnt equal to float
$db->string()->select('*', 'table', ['column'=>pudl::neq(5.0E+90)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`!=5.0E+90");




//SELECT statement where column in an array
$db->string()->select('*', 'table', ['column'=>pudl::neq([5,7,9])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN (5, 7, 9)");




//SELECT statement where column isnt equal to string
$db->string()->select('*', 'table', ['column'=>pudl::neq('value')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`!='value'");




//SELECT statement where column isnt equal to column
$db->string()->select('*', 'table', ['column1'=>pudl::neq( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1`!=`column2`");




//SELECT statement where column isnt equal to column chain
$db->string()->select('*', 'table', ['column1'=>pudl::neq()->column('column2')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1`!=`column2`");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE ABS(`column`-5)<0.0000000001");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq(5.7)]);
pudlTest($db, "SELECT * FROM `table` WHERE ABS(`column`-5.7)<0.0000000001");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq('5')]);
pudlTest($db, "SELECT * FROM `table` WHERE ABS(`column`-'5')<0.0000000001");




//SELECT statement where column is "equal" (roughly) to floating point value with lower precision
$db->string()->select('*', 'table', ['column'=>pudl::fleq(8, 3)]);
pudlTest($db, "SELECT * FROM `table` WHERE ABS(`column`-8)<0.001");




//SELECT statement where column is "equal" (roughly) to floating point column
$db->string()->select('*', 'table', ['column1'=>pudl::fleq( pudl::column('column2') )]);
pudlTest($db, "SELECT * FROM `table` WHERE ABS(`column1`-`column2`)<0.0000000001");




//SELECT statement where column is less than integer
$db->string()->select('*', 'table', ['column'=>pudl::lt(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`<5");




//SELECT statement where column is greater than integer
$db->string()->select('*', 'table', ['column'=>pudl::gt(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`>5");




//SELECT statement where column is less than or equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::lteq(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`<=5");




//SELECT statement where column is greater than or equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::gteq(5)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`>=5");




//Custom equals
$db->string()->select('*', 'table', pudl::eq(5,10));
pudlTest($db, "SELECT * FROM `table` WHERE 5=10");




//Custom equals
$db->string()->select('*', 'table', [pudl::eq(5,10)]);
pudlTest($db, "SELECT * FROM `table` WHERE 5=10");




//Custom equals
$db->string()->select('*', 'table', pudl::lteq(5,10));
pudlTest($db, "SELECT * FROM `table` WHERE 5<=10");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(5,10));
pudlTest($db, "SELECT * FROM `table` WHERE 5>=10");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq('text',10));
pudlTest($db, "SELECT * FROM `table` WHERE 'text'>=10");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(pudl::column('column'),10));
pudlTest($db, "SELECT * FROM `table` WHERE `column`>=10");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(
	pudl::column('column1'),
	pudl::column('column2')
));
pudlTest($db, "SELECT * FROM `table` WHERE `column1`>=`column2`");




//Custom equals
$db->string()->select('*', 'table', [pudl::gteq(
	pudl::column('column1'),
	pudl::column('column2')
)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column1`>=`column2`");




//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', ['column'=>pudl::between(5,10)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` BETWEEN 5 AND 10");




//SELECT statement where column is NOT between two integer values
$db->string()->select('*', 'table', ['column'=>pudl::notBetween(5,10)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT BETWEEN 5 AND 10");




//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', pudl::between(pudl::column('column'), 5,10));
pudlTest($db, "SELECT * FROM `table` WHERE `column` BETWEEN 5 AND 10");




//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', [pudl::between(pudl::column('column'), 5,10)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` BETWEEN 5 AND 10");




//SELECT statement with an AND clause
$db->string()->select('*', 'table', [
	'',
	'',
	'column1=column3',
	'',
	'column2=column4',
	'',
]);
pudlTest($db, 'SELECT * FROM `table` WHERE (`column1`=`column3` AND `column2`=`column4`)');




//SELECT statement with an AND clause
$db->string()->select('*', 'table', [
	'column1=column3',
	'column2=column4',
]);
pudlTest($db, 'SELECT * FROM `table` WHERE (`column1`=`column3` AND `column2`=`column4`)');




//SELECT statement with an OR clause (nested arrays)
$db->string()->select('*', 'table', [[
	'column1=column3', 'column2=column4'
]]);
pudlTest($db, 'SELECT * FROM `table` WHERE (`column1`=`column3` OR `column2`=`column4`)');




//SELECT statement with an AND and OR clause (nested arrays)
$db->string()->select('*', 'table', [
	'column1=column4',
	['column2=column5', 'column3=column6']
]);
pudlTest($db, 'SELECT * FROM `table` WHERE (`column1`=`column4` AND (`column2`=`column5` OR `column3`=`column6`))');




//SELECT statement with complex AND and OR clause (nested arrays)
$db->string()->select('*', 'table', [
	[
		['x'=>1, 'y'=>2],
		['x'=>2, 'y'=>1],
	],
	'z'=>3
]);
pudlTest($db, 'SELECT * FROM `table` WHERE (((`x`=1 AND `y`=2) OR (`x`=2 AND `y`=1)) AND `z`=3)');
