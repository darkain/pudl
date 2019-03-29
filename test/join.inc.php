<?php

$db->string()->select('*', ['x' => ['table1',
	['join' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['right' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` RIGHT JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['inner' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` INNER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['outer' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` OUTER JOIN `table2` AS `y`');



$db->string()->select('*', ['x' => ['table1',
	['natural' => ['y'=>'table2']],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` NATURAL JOIN `table2` AS `y`');



//NOTE: 'hack' will LEFT JOIN a group of tables with ZERO SQL SAFETY
$db->string()->select('*', ['xx' => ['table1',
	['hack' => 'table2 AS yy JOIN table3 AS zz'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `xx` LEFT JOIN (table2 AS yy JOIN table3 AS zz)');




$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'using'=>'column'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` USING (`column`)');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'on'=>'x.column=y.column'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON `x`.`column`=`y`.`column`');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=y.column'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON `x`.`column`=`y`.`column`');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=0'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON `x`.`column`=0');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=1'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON `x`.`column`=1');



$db->string()->select('*', ['x' => ['table1',
	['left' => ['y'=>'table2'], 'clause'=>'x.column=-1'],
]]);
pudlTest($db, 'SELECT * FROM `table1` AS `x` LEFT JOIN `table2` AS `y` ON `x`.`column`=-1');



////////////////////////////////////////////////////////////////////////////////



$db->string()->select('*', ['tbl1', '~tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '<tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` LEFT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '>tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` RIGHT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` NATURAL JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=<tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` NATURAL LEFT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '=>tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` NATURAL RIGHT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '<>tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` OUTER JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '><tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` INNER JOIN `tbl2`');



$db->string()->select('*', ['tbl1', '+tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` CROSS JOIN `tbl2`');



$db->string()->select('*', ['a'=>'tbl1', 'b'=>'=tbl2', 'c'=>'=tbl3']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `a` NATURAL JOIN `tbl2` AS `b` NATURAL JOIN `tbl3` AS `c`');



$db->string()->select('*', ['tbl1', '~tbl2(col)']);
pudlTest($db, 'SELECT * FROM `tbl1` JOIN `tbl2` USING (`col`)');



$db->string()->select('*', ['tbl1', '>tbl2( col ) ']);
pudlTest($db, 'SELECT * FROM `tbl1` RIGHT JOIN `tbl2` USING (`col`)');



$db->string()->select('*', ['tbl1', 'x'=>' < tbl2 ( col )  ']);
pudlTest($db, 'SELECT * FROM `tbl1` LEFT JOIN `tbl2` AS `x` USING (`col`)');



$db->string()->select('*', ['tbl1', 'y'=>'+ tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` CROSS JOIN `tbl2` AS `y`');



$db->string()->select('*', ['tbl1', 'z'=>' <tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` LEFT JOIN `tbl2` AS `z`');



$db->string()->select('*', ['tbl1', ' > tbl2']);
pudlTest($db, 'SELECT * FROM `tbl1` RIGHT JOIN `tbl2`');



$db->string()->select('*', ['tbl1', " ~ \t tbl2"]);
pudlTest($db, 'SELECT * FROM `tbl1` JOIN `tbl2`');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(t1.col1=t2.col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`t1`.`col1`=`t2`.`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1!=col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`!=`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1<col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`<`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1>col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`>`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1<>col2)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`<>`col2`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=5)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=5)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=5.5)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=5.5)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=-5.5)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=-5.5)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=null)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=NULL)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1!=null)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NOT NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1!=NULL)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NOT NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1<>null)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NOT NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1<>NULL)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1` IS NOT NULL)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=inf)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=`inf`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=-inf)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=`-inf`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(col1=nan)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`col1`=`nan`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(5=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (5=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(5.5=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (5.5=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(-5.5=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (-5.5=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(null=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`null`=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(NULL=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`NULL`=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(inf=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`inf`=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(-inf=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`-inf`=`col1`)');



$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(nan=col1)']);
pudlTest($db, 'SELECT * FROM `tbl1` AS `t1` JOIN `tbl2` AS `t2` ON (`nan`=`col1`)');



try {
	$db->string()->select('*', ['t1'=>'tbl1', 't2'=>'~tbl2(test<null)']);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid NULL comparison: test<null');
}
