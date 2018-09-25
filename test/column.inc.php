<?php

//SELECT statement, returning all columns
$db->string()->select(NULL, 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning all columns
//Deprecated
$db->string()->select(false, 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning all columns
$db->string()->select('', 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning all columns
$db->string()->select('*', 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning all columns
$db->string()->select(NULL, 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning literal
$db->string()->select(0, NULL);
pudlTest('SELECT 0');




//SELECT statement, returning literal
$db->string()->select(1, NULL);
pudlTest('SELECT 1');




//SELECT statement, returning literal
$db->string()->select(-1, NULL);
pudlTest('SELECT -1');




//SELECT statement, returning literal
$db->string()->select(10, NULL);
pudlTest('SELECT 10');




//SELECT statement, returning literal
$db->string()->select(-10, NULL);
pudlTest('SELECT -10');




//SELECT statement, returning literal
$db->string()->select(PHP_INT_MIN, NULL);
pudlTest('SELECT -9223372036854775808');




//SELECT statement, returning literal
$db->string()->select(PHP_INT_MAX, NULL);
pudlTest('SELECT 9223372036854775807');




//SELECT statement, returning literal
$db->string()->select(0.0, NULL);
pudlTest('SELECT 0');




//SELECT statement, returning literal
$db->string()->select(1.2, NULL);
pudlTest('SELECT 1.2');




//SELECT statement, returning literal
$db->string()->select(-1.2, NULL);
pudlTest('SELECT -1.2');




//SELECT statement, returning literal
$db->string()->select(1e50, NULL);
pudlTest('SELECT 1.0E+50');




//SELECT statement, returning literal
$db->string()->select(-1e50, NULL);
pudlTest('SELECT -1.0E+50');




//SELECT statement, returning literal
$db->string()->select(2.3e+30, NULL);
pudlTest('SELECT 2.3E+30');




//SELECT statement, returning literal
$db->string()->select(-2.3e+30, NULL);
pudlTest('SELECT -2.3E+30');




//SELECT statement, returning literal
$db->string()->select(INF, NULL);
pudlTest('SELECT NULL');




//SELECT statement, returning literal
$db->string()->select(-INF, NULL);
pudlTest('SELECT NULL');




//SELECT statement, returning literal
$db->string()->select(NAN, NULL);
pudlTest('SELECT NULL');




//SELECT statement, returning literal
$db->string()->select(PHP_FLOAT_EPSILON, NULL);
pudlTest('SELECT 2.2204460492503E-16');




//SELECT statement, returning literal
$db->string()->select(PHP_FLOAT_MIN, NULL);
pudlTest('SELECT 2.2250738585072E-308');




//SELECT statement, returning literal
$db->string()->select(PHP_FLOAT_MAX, NULL);
pudlTest('SELECT 1.7976931348623E+308');




//SELECT statement, returning literal
$db->string()->select(['*'], 'table');
pudlTest('SELECT * FROM `table`');




//SELECT statement, returning literal
$db->string()->select([NULL], 'table');
pudlTest('SELECT NULL FROM `table`');




//SELECT statement, returning literal
$db->string()->select([1], 'table');
pudlTest('SELECT 1 FROM `table`');




//SELECT statement, returning literal
$db->string()->select([2.3], 'table');
pudlTest('SELECT 2.3 FROM `table`');




//SELECT statement, returning literal
$db->string()->select([1.2e+31], 'table');
pudlTest('SELECT 1.2E+31 FROM `table`');




//SELECT statement, returning literal
$db->string()->select([NAN], 'table');
pudlTest('SELECT NULL FROM `table`');




//SELECT statement, returning literal
$db->string()->select([INF], 'table');
pudlTest('SELECT NULL FROM `table`');




//SELECT statement, returning literal
$db->string()->select([-INF], 'table');
pudlTest('SELECT NULL FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('column', 'table');
pudlTest('SELECT `column` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('table.column', 'table');
pudlTest('SELECT `table`.`column` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('table.*', 'table');
pudlTest('SELECT `table`.* FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column'], 'table');
pudlTest('SELECT `column` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['table.column'], 'table');
pudlTest('SELECT `table`.`column` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['table.*'], 'table');
pudlTest('SELECT `table`.* FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('column1 , column2', 'table');
pudlTest('SELECT `column1`, `column2` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('table.* , column2', 'table');
pudlTest('SELECT `table`.*, `column2` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('column1 , table.*', 'table');
pudlTest('SELECT `column1`, `table`.* FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select('table.column1 , table.column2', 'table');
pudlTest('SELECT `table`.`column1`, `table`.`column2` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', 'column2'], 'table');
pudlTest('SELECT `column1`, `column2` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['table.column1', 'table.column2'], 'table');
pudlTest('SELECT `table`.`column1`, `table`.`column2` FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', 'table.*'], 'table');
pudlTest('SELECT `column1`, `table`.* FROM `table`');




//SELECT statement, choosing which columns to return
$db->string()->select(['column1', pudl::unhex('01fa')], 'table');
pudlTest("SELECT `column1`, UNHEX('01fa') FROM `table`");




//SELECT statement, choosing which columns to return
$db->string()->select(['x'=>'column1', 'y'=>'column2'], 'table');
pudlTest('SELECT `column1` AS `x`, `column2` AS `y` FROM `table`');




//BRAVO CUSTOM FUNCTION
$db->string()->select('*', 'table', pudl::bravo(123, 'column1', 'column2'));
pudlTest('SELECT * FROM `table` WHERE (123 IN (`column1`, `column2`))');




//BRAVO CUSTOM FUNCTION
$db->string()->select('*', 'table', [pudl::bravo(123, 'column3', 'column4')]);
pudlTest('SELECT * FROM `table` WHERE (123 IN (`column3`, `column4`))');




//BRAVO CUSTOM FUNCTION
$db->string()->select('*', 'table', pudl::bravo(123, ['column5', 'column6']));
pudlTest('SELECT * FROM `table` WHERE (123 IN (`column5`, `column6`))');




//BRAVO CUSTOM FUNCTION
$db->string()->select('*', 'table', [pudl::bravo(123, ['column7', 'column8'])]);
pudlTest('SELECT * FROM `table` WHERE (123 IN (`column7`, `column8`))');




//BRAVO CUSTOM FUNCTION
$db->string()->select('*', 'table', [
	pudl::bravo(123, ['column7', 'column8']),
	'column' => 'value',
]);
pudlTest("SELECT * FROM `table` WHERE (123 IN (`column7`, `column8`) AND `column`='value')");
