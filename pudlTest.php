<?php

/*****************************************************************************\
	This is the testing framework for PUDL. This testing framework is
	designed to be launched exclusively form within an Altaform based
	application. The instructions on how to set this up will be provided
	at a later time. This file, however, still gives some clear examples
	as to the types of SQL statements that can be generated through the
	PUDL library.

	IMPORTANT NOTE: The ->string() part of these queries means that they
	will *NOT* be executed, but instead ONLY return an object containing
	the SQL query statement generated. Removing ->string() from each line
	will allow execution of the generated statement. This is simply added
	here to compare the generated statements to their expected results, to
	ensure that all queries are generated by PUDL correctly.
\*****************************************************************************/

if (!isset($af)  ||  !is_object($af)  ||  !($af instanceof altaform)) {
	die('PLEASE RUN THIS FROM WITHIN ALTAFORM!');
}



//RAW SQL using ->query('STATEMENT')
$sql = $db->string()->query('SELECT * FROM table');
assert422($sql == 'SELECT * FROM table', 'Line: ' . __LINE__ . ' ');




//RAW SQL using $db('STATEMENT')
$db->string();
$sql = $db('SELECT * FROM table');
assert422($sql == 'SELECT * FROM table', 'Line: ' . __LINE__ . ' ');




//SELECT statement
$sql = $db->string()->select('*', 'table');
assert422($sql == 'SELECT * FROM `table`', 'Line: ' . __LINE__ . ' ');




//SELECT statement, joining two tables
$sql = $db->string()->select('*', ['table1', 'table2']);
assert422($sql == 'SELECT * FROM `table1`, `table2`', 'Line: ' . __LINE__ . ' ');




//SELECT statement, joining two tables, both with aliases
$sql = $db->string()->select('*', ['a'=>'table1', 'b'=>'table2']);
assert422($sql == 'SELECT * FROM `table1` a, `table2` b', 'Line: ' . __LINE__ . ' ');




//SELECT statement, choosing which columns to return
$sql = $db->string()->select(['column1', 'column2'], 'table');
assert422($sql == 'SELECT column1, column2 FROM `table`', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause
$sql = $db->string()->select('*', 'table', 'column=value');
assert422($sql == 'SELECT * FROM `table` WHERE (column=value)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with STRING value
$sql = $db->string()->select('*', 'table', ['column'=>'value']);
assert422($sql == "SELECT * FROM `table` WHERE (`column`='value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with NULL value
$sql = $db->string()->select('*', 'table', ['column'=>NULL]);
assert422($sql == 'SELECT * FROM `table` WHERE (`column` IS NULL)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with INTEGER value
$sql = $db->string()->select('*', 'table', ['column'=>5]);
assert422($sql == 'SELECT * FROM `table` WHERE (`column`=5)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with FLOAT value
$sql = $db->string()->select('*', 'table', ['column'=>2.3]);
assert422($sql == 'SELECT * FROM `table` WHERE (`column`=2.3)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with FLOAT value including exponent
$sql = $db->string()->select('*', 'table', ['column'=>1.2e23]);
assert422($sql == 'SELECT * FROM `table` WHERE (`column`=1.2E+23)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with BOOLEAN value including exponent
$sql = $db->string()->select('*', 'table', ['column'=>true]);
assert422($sql == 'SELECT * FROM `table` WHERE (`column`=TRUE)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with table definition
$sql = $db->string()->select('*', 'table', ['table.column'=>'value']);
assert422($sql == "SELECT * FROM `table` WHERE (`table`.`column`='value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a single clause with table definition (spaced)
$sql = $db->string()->select('*', 'table', ['table . column'=>'value']);
assert422($sql == "SELECT * FROM `table` WHERE (`table`.`column`='value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a LIKE clause (left and right search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::like('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` LIKE '%value%')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a LIKE clause (left search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::likeLeft('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` LIKE '%value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a LIKE clause (right search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::likeRight('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` LIKE 'value%')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a NOT LIKE clause (left and right search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::notLike('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` NOT LIKE '%value%')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a NOT LIKE clause (left search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::notLikeLeft('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` NOT LIKE '%value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with a NOT LIKE clause (right search)
$sql = $db->string()->select('*', 'table', ['column'=>pudl::notLikeRight('value')]);
assert422($sql == "SELECT * FROM `table` WHERE (`column` NOT LIKE 'value%')", 'Line: ' . __LINE__ . ' ');




//SELECT statement with an AND clause
$sql = $db->string()->select('*', 'table', [
	'column1=value',
	'column2=other',
]);
assert422($sql == 'SELECT * FROM `table` WHERE (column1=value AND column2=other)', 'Line: ' . __LINE__ . ' ');




//SELECT statement with an OR clause (nested arrays)
$sql = $db->string()->select('*', 'table', [[
	'column1=value', 'column2=other'
]]);
assert422($sql == 'SELECT * FROM `table` WHERE ((column1=value OR column2=other))', 'Line: ' . __LINE__ . ' ');




//SELECT statement with an AND and OR clause (nested arrays)
$sql = $db->string()->select('*', 'table', [
	'column1=value',
	['column2=again', 'column3=other']
]);
assert422($sql == 'SELECT * FROM `table` WHERE (column1=value AND (column2=again OR column3=other))', 'Line: ' . __LINE__ . ' ');




//SELECT statement with complex AND and OR clause (nested arrays)
$sql = $db->string()->select('*', 'table', [
	[
		['x=1', 'y=2'],
		['x=2', 'y=1'],
	],
	'z=3'
]);
assert422($sql == 'SELECT * FROM `table` WHERE (((x=1 AND y=2) OR (x=2 AND y=1)) AND z=3)', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single row
//Returns associative array instead of a pudlResult object
$sql = $db->string()->row('table');
assert422($sql == 'SELECT * FROM `table` LIMIT 1', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single row using a clause
//Returns associative array instead of a pudlResult object
$sql = $db->string()->row('table', 'column=value');
assert422($sql == 'SELECT * FROM `table` WHERE (column=value) LIMIT 1', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get multiple rows
//Returns array of associative array instead of a pudlResult object
$sql = $db->string()->rows('table');
assert422($sql == 'SELECT * FROM `table`', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get multiple rows using a clause
//Returns array of associative array instead of a pudlResult object
$sql = $db->string()->rows('table', 'column=value');
assert422($sql == 'SELECT * FROM `table` WHERE (column=value)', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single row based on column STRING value
//Returns associative array instead of a pudlResult object
$sql = $db->string()->rowId('table', 'column', 'value');
assert422($sql == "SELECT * FROM `table` WHERE (`column`='value') LIMIT 1", 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single row based on column FUNCTION value
//Returns associative array instead of a pudlResult object
$sql = $db->string()->rowId('table', 'column', pudl::unhex('0123DEADBEEF0123'));
assert422($sql == "SELECT * FROM `table` WHERE (`column`=UNHEX('0123DEADBEEF0123')) LIMIT 1", 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get multiple rows based on column value
//Returns array of associative array instead of a pudlResult object
$sql = $db->string()->rowsId('table', 'column', 'value');
assert422($sql == "SELECT * FROM `table` WHERE (`column`='value')", 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single cell value
//Returns string of the cell's value (false if not found)
$sql = $db->string()->cell('table', 'column');
assert422($sql == 'SELECT column FROM `table` LIMIT 1', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single cell value using a clause
//Returns string of the cell's value (false if not found)
$sql = $db->string()->cell('table', 'column', 'id=value');
assert422($sql == 'SELECT column FROM `table` WHERE (id=value) LIMIT 1', 'Line: ' . __LINE__ . ' ');




//SELECT statement shortcut to get a single cell value by another column's value
//Returns string of the cell's value (false if not found)
$sql = $db->string()->cellId('table', 'column', 'id', 'value');
assert422($sql == "SELECT column FROM `table` WHERE (`id`='value') LIMIT 1", 'Line: ' . __LINE__ . ' ');




///////////////////////////////////////////////////////////////////////////////




//INSERT statement - using associative array
$sql = $db->string()->insert('table', ['column'=>'value']);
assert422($sql == "INSERT INTO `table` (`column`) VALUES ('value')", 'Line: ' . __LINE__ . ' ');




//INSERT statement - using associative array, duplicate key update
$sql = $db->string()->insert('table', ['column'=>'value'], true);
assert422($sql == "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value'", 'Line: ' . __LINE__ . ' ');




//INSERT statement - using associative array, custom duplicate key update
$sql = $db->string()->insert('table', ['column'=>'value'], 'x=x+1');
assert422($sql == "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE x=x+1", 'Line: ' . __LINE__ . ' ');




//INSERT statement - using associative array, custom duplicate key update using UPDATE syntax
$sql = $db->string()->insert('table', ['column'=>'value'], ['y'=>2]);
assert422($sql == "INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `y`=2", 'Line: ' . __LINE__ . ' ');




//UPDATE statement - using associative array and clause
$sql = $db->string()->update('table', ['column'=>'value'], 'id=1');
assert422($sql == "UPDATE `table` SET `column`='value' WHERE (id=1)", 'Line: ' . __LINE__ . ' ');




//UPDATE statement - using associative array and ID
$sql = $db->string()->updateId('table', ['column'=>'value'], 'id', 'value');
assert422($sql == "UPDATE `table` SET `column`='value' WHERE (`id`='value')", 'Line: ' . __LINE__ . ' ');




//UPDATE statement - incrementing an INTEGER value
$sql = $db->string()->update('table', ['column'=>pudlFunction::increment()], 'id=1');
assert422($sql == "UPDATE `table` SET `column`=`column`+1 WHERE (id=1)", 'Line: ' . __LINE__ . ' ');




//UPDATE statement - incrementing an INTEGER value
$sql = $db->string()->update('table', ['column'=>pudlFunction::increment('5')], 'id=1');
assert422($sql == "UPDATE `table` SET `column`=`column`+'5' WHERE (id=1)", 'Line: ' . __LINE__ . ' ');




//UPDATE statement - incrementing an INTEGER value
$sql = $db->string()->updateIn('table', ['column'=>'value'], 'id', [1,7,7,9]);
assert422($sql == "UPDATE `table` SET `column`='value' WHERE (`id` IN (1,7,7,9))", 'Line: ' . __LINE__ . ' ');
