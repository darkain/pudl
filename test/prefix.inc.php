<?php


require_once(__DIR__.'/../null/pudlNull.php');




////////////////////////////////////////////////////////////////////////////////




$prefix1 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> 'replace_',
]);




$prefix1->string()->rows('table');
pudlTest("SELECT * FROM `table`", $prefix1);




$prefix1->string()->rows('pudl_table');
pudlTest("SELECT * FROM `replace_table`", $prefix1);




$prefix1->string()->rows('replace_table');
pudlTest("SELECT * FROM `replace_table`", $prefix1);




$prefix1->string()->rows('test_table');
pudlTest("SELECT * FROM `test_table`", $prefix1);




$prefix1->string()->rows('search_table');
pudlTest("SELECT * FROM `search_table`", $prefix1);




$prefix1->string()->rows('table', false, ['column']);
pudlTest("SELECT * FROM `table` ORDER BY `column`", $prefix1);




$prefix1->string()->rows('table', false, ['table.column']);
pudlTest("SELECT * FROM `table` ORDER BY `table`.`column`", $prefix1);




////////////////////////////////////////////////////////////////////////////////




$prefix2 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> ['replace_'],
]);




$prefix2->string()->rows('table');
pudlTest("SELECT * FROM `replace_table`", $prefix2);




$prefix2->string()->rows('pudl_table');
pudlTest("SELECT * FROM `replace_pudl_table`", $prefix2);




$prefix2->string()->rows('replace_table');
pudlTest("SELECT * FROM `replace_table`", $prefix2);




$prefix2->string()->rows('test_table');
pudlTest("SELECT * FROM `replace_test_table`", $prefix2);




$prefix2->string()->rows('search_table');
pudlTest("SELECT * FROM `replace_search_table`", $prefix2);




$prefix2->string()->rows('table', false, ['column']);
pudlTest("SELECT * FROM `replace_table` ORDER BY `column`", $prefix2);




$prefix2->string()->rows('table', false, ['table.column']);
pudlTest("SELECT * FROM `replace_table` ORDER BY `table`.`column`", $prefix2);




////////////////////////////////////////////////////////////////////////////////




$prefix3 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> ['search_' => 'replace_'],
]);




$prefix3->string()->rows('table');
pudlTest("SELECT * FROM `table`", $prefix3);




$prefix3->string()->rows('pudl_table');
pudlTest("SELECT * FROM `pudl_table`", $prefix3);




$prefix3->string()->rows('replace_table');
pudlTest("SELECT * FROM `replace_table`", $prefix3);




$prefix3->string()->rows('test_table');
pudlTest("SELECT * FROM `test_table`", $prefix3);




$prefix3->string()->rows('search_table');
pudlTest("SELECT * FROM `replace_table`", $prefix3);




$prefix3->string()->rows('table', false, ['column']);
pudlTest("SELECT * FROM `table` ORDER BY `column`", $prefix3);




$prefix3->string()->rows('table', false, ['table.column']);
pudlTest("SELECT * FROM `table` ORDER BY `table`.`column`", $prefix3);




////////////////////////////////////////////////////////////////////////////////




$prefix4 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> [
						'default_',
		'pudl_'		=>	'prefix_',
		'search_'	=>	'replace_',
	],
]);




$prefix4->string()->rows('table');
pudlTest("SELECT * FROM `default_table`", $prefix4);




$prefix4->string()->rows('pudl_table');
pudlTest("SELECT * FROM `prefix_table`", $prefix4);




$prefix4->string()->rows('replace_table');
pudlTest("SELECT * FROM `default_replace_table`", $prefix4);




$prefix4->string()->rows('test_table');
pudlTest("SELECT * FROM `default_test_table`", $prefix4);




$prefix4->string()->rows('search_table');
pudlTest("SELECT * FROM `replace_table`", $prefix4);




$prefix4->string()->rows('table', false, ['column']);
pudlTest("SELECT * FROM `default_table` ORDER BY `column`", $prefix4);




$prefix4->string()->rows('table', false, ['table.column']);
pudlTest("SELECT * FROM `default_table` ORDER BY `table`.`column`", $prefix4);
