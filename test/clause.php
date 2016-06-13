<?php

//SELECT statement with a simple clause
$db->string()->select('*', 'table', true);
pudlTest('SELECT * FROM `table` WHERE (1)');




//SELECT statement with a simple clause
//NOTE: 'false' is internally ignored for a clause
$db->string()->select('*', 'table', false);
pudlTest('SELECT * FROM `table`');




//SELECT statement with a simple clause
//NOTE: pass 'true' in an array to have it unmodified
$db->string()->select('*', 'table', [true]);
pudlTest('SELECT * FROM `table` WHERE (TRUE)');




//SELECT statement with a simple clause
//NOTE: pass 'false' in an array to have it unmodified
$db->string()->select('*', 'table', [false]);
pudlTest('SELECT * FROM `table` WHERE (FALSE)');




//SELECT statement with a single clause
$db->string()->select('*', 'table', 'column=value');
pudlTest('SELECT * FROM `table` WHERE (column=value)');




//SELECT statement with a single clause with STRING value
$db->string()->select('*', 'table', ['column'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (`column`='value')");




//SELECT statement with a single clause with NULL value
$db->string()->select('*', 'table', ['column'=>NULL]);
pudlTest('SELECT * FROM `table` WHERE (`column` IS NULL)');




//SELECT statement with a single clause with INTEGER value
$db->string()->select('*', 'table', ['column'=>5]);
pudlTest('SELECT * FROM `table` WHERE (`column`=5)');




//SELECT statement with a single clause with FLOAT value
$db->string()->select('*', 'table', ['column'=>2.3]);
pudlTest('SELECT * FROM `table` WHERE (`column`=2.3)');




//SELECT statement with a single clause with FLOAT value including exponent
$db->string()->select('*', 'table', ['column'=>1.2e23]);
pudlTest('SELECT * FROM `table` WHERE (`column`=1.2E+23)');




//SELECT statement with a single clause with NaN (Not a Number) value
$db->string()->select('*', 'table', ['column'=>NAN]);
pudlTest('SELECT * FROM `table` WHERE (`column` IS NULL)');




//SELECT statement with a single clause with Infinite value
$db->string()->select('*', 'table', ['column'=>INF]);
pudlTest('SELECT * FROM `table` WHERE (`column` IS NULL)');




//SELECT statement with a single clause with Negative Infinite value
$db->string()->select('*', 'table', ['column'=>-INF]);
pudlTest('SELECT * FROM `table` WHERE (`column` IS NULL)');




//SELECT statement with a single clause with BOOLEAN value
$db->string()->select('*', 'table', ['column'=>true]);
pudlTest('SELECT * FROM `table` WHERE (`column`=TRUE)');




//SELECT statement with a single clause with ARRAY value
$db->string()->select('*', 'table', ['column'=>[1,2,3]]);
pudlTest('SELECT * FROM `table` WHERE (`column` IN (1, 2, 3))');




//SELECT statement with a single clause with complex ARRAY value
$db->string()->select('*', 'table', ['column'=>[1.2e23,'2',3, NULL, [5]]]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (1.2E+23, '2', 3, NULL, 5))");




//SELECT statement with a single clause with table definition
$db->string()->select('*', 'table', ['table.column'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (`table`.`column`='value')");




//SELECT statement with a single clause with table definition (spaced)
$db->string()->select('*', 'table', ['table . column'=>'value']);
pudlTest("SELECT * FROM `table` WHERE (`table`.`column`='value')");




//SELECT statement with a LIKE clause (left and right search)
$db->string()->select('*', 'table', ['column'=>pudl::like('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%value%')");




//SELECT statement with a LIKE clause (left search)
$db->string()->select('*', 'table', ['column'=>pudl::likeLeft('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%value')");




//SELECT statement with a LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::likeRight('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE 'value%')");




//SELECT statement with a NOT LIKE clause (left and right search)
$db->string()->select('*', 'table', ['column'=>pudl::notLike('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT LIKE '%value%')");




//SELECT statement with a NOT LIKE clause (left search)
$db->string()->select('*', 'table', ['column'=>pudl::notLikeLeft('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT LIKE '%value')");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::notLikeRight('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT LIKE 'value%')");




//SELECT statement with a LIKE clause, column instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',`column2`,'%'))");




//SELECT statement with a LIKE clause, column instead of string (left)
$db->string()->select('*', 'table', ['column1'=>pudl::likeLeft( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',`column2`,''))");




//SELECT statement with a LIKE clause, column instead of string (right)
$db->string()->select('*', 'table', ['column1'=>pudl::likeRight( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('',`column2`,'%'))");




//SELECT statement with a LIKE clause, function instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::hex('value') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',HEX('value'),'%'))");




//SELECT statement with a LIKE clause, function chain instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like()->hex('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',HEX('value'),'%'))");




//SELECT statement with a LIKE clause, raw SQL instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like( pudl::raw("X'65'") )]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',X'65','%'))");




//SELECT statement with a LIKE clause, raw SQL chain instead of string
$db->string()->select('*', 'table', ['column1'=>pudl::like()->raw("X'65'")]);
pudlTest("SELECT * FROM `table` WHERE (`column1` LIKE CONCAT('%',X'65','%'))");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::eq(NULL)]);
pudlTest("SELECT * FROM `table` WHERE (`column` IS NULL)");




//SELECT statement where column isnt equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::eq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=5)");




//SELECT statement where column isnt equal to float
$db->string()->select('*', 'table', ['column'=>pudl::eq(5.0E+90)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=5.0E+90)");




//SELECT statement where column in an array of integers
$db->string()->select('*', 'table', ['column'=>pudl::eq([5,7,9])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (5, 7, 9))");




//SELECT statement where column in an array of floats
$db->string()->select('*', 'table', ['column'=>pudl::eq([5.0E+90,7.0E+80,5.0E+70])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (5.0E+90, 7.0E+80, 5.0E+70))");




//SELECT statement where column in an array of string
$db->string()->select('*', 'table', ['column'=>pudl::eq(['5','7','9'])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN ('5', '7', '9'))");




//SELECT statement where column in a mixed array
$db->string()->select('*', 'table', ['column'=>pudl::eq([5.0E+90,7,'9'])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (5.0E+90, 7, '9'))");




//SELECT statement where column isnt equal to string
$db->string()->select('*', 'table', ['column'=>pudl::eq('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column`='value')");




//SELECT statement where column isnt equal to column
$db->string()->select('*', 'table', ['column1'=>pudl::eq( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1`=`column2`)");




//SELECT statement where column isnt equal to column chain
$db->string()->select('*', 'table', ['column1'=>pudl::eq()->column('column2')]);
pudlTest("SELECT * FROM `table` WHERE (`column1`=`column2`)");




//SELECT statement where column isnt equal to NULL
$db->string()->select('*', 'table', ['column'=>pudl::neq(NULL)]);
pudlTest("SELECT * FROM `table` WHERE (`column` IS NOT NULL)");




//SELECT statement where column isnt equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::neq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`!=5)");




//SELECT statement where column isnt equal to float
$db->string()->select('*', 'table', ['column'=>pudl::neq(5.0E+90)]);
pudlTest("SELECT * FROM `table` WHERE (`column`!=5.0E+90)");




//SELECT statement where column in an array
$db->string()->select('*', 'table', ['column'=>pudl::neq([5,7,9])]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN (5, 7, 9))");




//SELECT statement where column isnt equal to string
$db->string()->select('*', 'table', ['column'=>pudl::neq('value')]);
pudlTest("SELECT * FROM `table` WHERE (`column`!='value')");




//SELECT statement where column isnt equal to column
$db->string()->select('*', 'table', ['column1'=>pudl::neq( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (`column1`!=`column2`)");




//SELECT statement where column isnt equal to column chain
$db->string()->select('*', 'table', ['column1'=>pudl::neq()->column('column2')]);
pudlTest("SELECT * FROM `table` WHERE (`column1`!=`column2`)");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq(5)]);
pudlTest("SELECT * FROM `table` WHERE (ABS(`column`-5)<0.0000000001)");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq(5.7)]);
pudlTest("SELECT * FROM `table` WHERE (ABS(`column`-5.7)<0.0000000001)");




//SELECT statement where column is "equal" (roughly) to floating point value
$db->string()->select('*', 'table', ['column'=>pudl::fleq('5')]);
pudlTest("SELECT * FROM `table` WHERE (ABS(`column`-'5')<0.0000000001)");




//SELECT statement where column is "equal" (roughly) to floating point value with lower precision
$db->string()->select('*', 'table', ['column'=>pudl::fleq(8, 3)]);
pudlTest("SELECT * FROM `table` WHERE (ABS(`column`-8)<0.001)");




//SELECT statement where column is "equal" (roughly) to floating point column
$db->string()->select('*', 'table', ['column1'=>pudl::fleq( pudl::column('column2') )]);
pudlTest("SELECT * FROM `table` WHERE (ABS(`column1`-`column2`)<0.0000000001)");




//SELECT statement where column is less than integer
$db->string()->select('*', 'table', ['column'=>pudl::lt(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`<5)");




//SELECT statement where column is greater than integer
$db->string()->select('*', 'table', ['column'=>pudl::gt(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`>5)");




//SELECT statement where column is less than or equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::lteq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`<=5)");




//SELECT statement where column is greater than or equal to integer
$db->string()->select('*', 'table', ['column'=>pudl::gteq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`>=5)");




//Custom equals
$db->string()->select('*', 'table', pudl::eq(5,10));
pudlTest("SELECT * FROM `table` WHERE (5=10)");




//Custom equals
$db->string()->select('*', 'table', [pudl::eq(5,10)]);
pudlTest("SELECT * FROM `table` WHERE (5=10)");




//Custom equals
$db->string()->select('*', 'table', pudl::lteq(5,10));
pudlTest("SELECT * FROM `table` WHERE (5<=10)");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(5,10));
pudlTest("SELECT * FROM `table` WHERE (5>=10)");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq('text',10));
pudlTest("SELECT * FROM `table` WHERE ('text'>=10)");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(pudl::column('column'),10));
pudlTest("SELECT * FROM `table` WHERE (`column`>=10)");




//Custom equals
$db->string()->select('*', 'table', pudl::gteq(
	pudl::column('column1'),
	pudl::column('column2')
));
pudlTest("SELECT * FROM `table` WHERE (`column1`>=`column2`)");




//Custom equals
$db->string()->select('*', 'table', [pudl::gteq(
	pudl::column('column1'),
	pudl::column('column2')
)]);
pudlTest("SELECT * FROM `table` WHERE (`column1`>=`column2`)");




//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', ['column'=>pudl::between(5,10)]);
pudlTest("SELECT * FROM `table` WHERE (`column` BETWEEN 5 AND 10)");




$haxdebug = true;
//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', [pudl::between(pudl::column('column'), 5,10)]);
pudlTest("SELECT * FROM `table` WHERE (`column` BETWEEN 5 AND 10)");




//SELECT statement where column is between two integer values
$db->string()->select('*', 'table', [pudl::between(pudl::dynamic('column.field:i'), 5,10)]);
pudlTest("SELECT * FROM `table` WHERE (COLUMN_GET(`column`, 'field' AS INTEGER) BETWEEN 5 AND 10)");




//SELECT statement with an AND clause
$db->string()->select('*', 'table', [
	'column1=value',
	'column2=other',
]);
pudlTest('SELECT * FROM `table` WHERE (column1=value AND column2=other)');




//SELECT statement with an OR clause (nested arrays)
$db->string()->select('*', 'table', [[
	'column1=value', 'column2=other'
]]);
pudlTest('SELECT * FROM `table` WHERE ((column1=value OR column2=other))');




//SELECT statement with an AND and OR clause (nested arrays)
$db->string()->select('*', 'table', [
	'column1=value',
	['column2=again', 'column3=other']
]);
pudlTest('SELECT * FROM `table` WHERE (column1=value AND (column2=again OR column3=other))');




//SELECT statement with complex AND and OR clause (nested arrays)
$db->string()->select('*', 'table', [
	[
		['x=1', 'y=2'],
		['x=2', 'y=1'],
	],
	'z=3'
]);
pudlTest('SELECT * FROM `table` WHERE (((x=1 AND y=2) OR (x=2 AND y=1)) AND z=3)');
