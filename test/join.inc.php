<?php

$db->string()->select('*', ['x' => ['table1',
	['join' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['right' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` RIGHT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['inner' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` INNER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['outer' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` OUTER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['natural' => ['y'=>'table2']],
]]);
pudlTest('SELECT * FROM `table1` AS `x` NATURAL JOIN `table2` AS `y`');



//NOTE: 'hack' will LEFT JOIN a group of tables with ZERO SQL SAFETY
$db->string()->select('*', ['xx' => ['table1',
	['hack' => 'table2 AS yy JOIN table3 AS zz'],
]]);
pudlTest('SELECT * FROM `table1` AS `xx` LEFT JOIN (table2 AS yy JOIN table3 AS zz)');




$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'using'=>'column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` USING (`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'on'=>'x.column=y.column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=`y`.`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=y.column'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=`y`.`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=0'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=0)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=1'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=1)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=-1'],
]]);
pudlTest('SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON (`x`.`column`=-1)');



////////////////////////////////////////////////////////////////////////////////



$db->string()->select('*', ['tbl1', '<tbl2']);
pudlTest('SELECT * FROM `tbl1` LEFT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '>tbl2']);
pudlTest('SELECT * FROM `tbl1` RIGHT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=tbl2']);
pudlTest('SELECT * FROM `tbl1` NATURAL JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=<tbl2']);
pudlTest('SELECT * FROM `tbl1` NATURAL LEFT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=>tbl2']);
pudlTest('SELECT * FROM `tbl1` NATURAL RIGHT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '<>tbl2']);
pudlTest('SELECT * FROM `tbl1` OUTER JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '><tbl2']);
pudlTest('SELECT * FROM `tbl1` INNER JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '+tbl2']);
pudlTest('SELECT * FROM `tbl1` CROSS JOIN `tbl2`');



$db->string()->select('*', ['a'=>'tbl1', 'b'=>'=tbl2', 'c'=>'=tbl3']);
pudlTest('SELECT * FROM `tbl1` AS `a` NATURAL JOIN `tbl2` AS `b` NATURAL JOIN `tbl3` AS `c`');
