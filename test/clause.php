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




//SELECT statement with a single clause with BOOLEAN value including exponent
$db->string()->select('*', 'table', ['column'=>true]);
pudlTest('SELECT * FROM `table` WHERE (`column`=TRUE)');




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




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::neq(NULL)]);
pudlTest("SELECT * FROM `table` WHERE (`column` IS NOT NULL)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::neq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`!=5)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::lt(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`<5)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::gt(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`>5)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::lteq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`<=5)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::gteq(5)]);
pudlTest("SELECT * FROM `table` WHERE (`column`>=5)");




//SELECT statement with a NOT LIKE clause (right search)
$db->string()->select('*', 'table', ['column'=>pudl::between(5,10)]);
pudlTest("SELECT * FROM `table` WHERE (`column` BETWEEN 5 AND 10)");




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
