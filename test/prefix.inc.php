<?php



$prefix1 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> 'replace_',
]);




$prefix1->string()->rows('table');
pudlTest($prefix1, "SELECT * FROM `table`");




$prefix1->string()->rows('pudl_table');
pudlTest($prefix1, "SELECT * FROM `replace_table`");




$prefix1->string()->rows('replace_table');
pudlTest($prefix1, "SELECT * FROM `replace_table`");




$prefix1->string()->rows('test_table');
pudlTest($prefix1, "SELECT * FROM `test_table`");




$prefix1->string()->rows('search_table');
pudlTest($prefix1, "SELECT * FROM `search_table`");




$prefix1->string()->rows('table', false, ['column']);
pudlTest($prefix1, "SELECT * FROM `table` ORDER BY `column`");




$prefix1->string()->rows('table', false, ['table.column']);
pudlTest($prefix1, "SELECT * FROM `table` ORDER BY `table`.`column`");




////////////////////////////////////////////////////////////////////////////////




$prefix2 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> ['replace_'],
]);




$prefix2->string()->rows('table');
pudlTest($prefix2, "SELECT * FROM `replace_table`");




$prefix2->string()->rows('pudl_table');
pudlTest($prefix2, "SELECT * FROM `replace_pudl_table`");




$prefix2->string()->rows('replace_table');
pudlTest($prefix2, "SELECT * FROM `replace_table`");




$prefix2->string()->rows('test_table');
pudlTest($prefix2, "SELECT * FROM `replace_test_table`");




$prefix2->string()->rows('search_table');
pudlTest($prefix2, "SELECT * FROM `replace_search_table`");




$prefix2->string()->rows('table', false, ['column']);
pudlTest($prefix2, "SELECT * FROM `replace_table` ORDER BY `column`");




$prefix2->string()->rows('table', false, ['table.column']);
pudlTest($prefix2, "SELECT * FROM `replace_table` ORDER BY `table`.`column`");




////////////////////////////////////////////////////////////////////////////////




$prefix3 = new pudlNull([
	'identifier'	=> '`',
	'prefix'		=> ['search_' => 'replace_'],
]);




$prefix3->string()->rows('table');
pudlTest($prefix3, "SELECT * FROM `table`");




$prefix3->string()->rows('pudl_table');
pudlTest($prefix3, "SELECT * FROM `pudl_table`");




$prefix3->string()->rows('replace_table');
pudlTest($prefix3, "SELECT * FROM `replace_table`");




$prefix3->string()->rows('test_table');
pudlTest($prefix3, "SELECT * FROM `test_table`");




$prefix3->string()->rows('search_table');
pudlTest($prefix3, "SELECT * FROM `replace_table`");




$prefix3->string()->rows('table', false, ['column']);
pudlTest($prefix3, "SELECT * FROM `table` ORDER BY `column`");




$prefix3->string()->rows('table', false, ['table.column']);
pudlTest($prefix3, "SELECT * FROM `table` ORDER BY `table`.`column`");




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
pudlTest($prefix4, "SELECT * FROM `default_table`");




$prefix4->string()->rows('pudl_table');
pudlTest($prefix4, "SELECT * FROM `prefix_table`");




$prefix4->string()->rows('replace_table');
pudlTest($prefix4, "SELECT * FROM `default_replace_table`");




$prefix4->string()->rows('test_table');
pudlTest($prefix4, "SELECT * FROM `default_test_table`");




$prefix4->string()->rows('search_table');
pudlTest($prefix4, "SELECT * FROM `replace_table`");




$prefix4->string()->rows('table', false, ['column']);
pudlTest($prefix4, "SELECT * FROM `default_table` ORDER BY `column`");




$prefix4->string()->rows('table', false, ['table.column']);
pudlTest($prefix4, "SELECT * FROM `default_table` ORDER BY `table`.`column`");
