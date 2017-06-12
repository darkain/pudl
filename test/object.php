<?php

$x=[
	'a' => 1,
	'b' => 2.2,
	'c' => NULL,
	'd' => 'test',
	'e' => true,
];

$test_object = new pudlObject($x);



pudlUnit(
	$test_object->json(),
	'{"a":1,"b":2.2,"c":null,"d":"test","e":true}'
);



pudlUnit(
	$test_object->partition(-1),
	[]
);



pudlUnit(
	$test_object->partition(0),
	[]
);



pudlUnit(
	$test_object->partition(1),
	[['a'=>1, 'b'=>2.2, 'c'=>NULL, 'd'=>'test', 'e'=>true]]
);



pudlUnit(
	$test_object->partition(2),
	[['a'=>1, 'b'=>2.2, 'c'=>NULL], ['d'=>'test', 'e'=>true]]
);



pudlUnit(
	$test_object->partition(3),
	[['a'=>1, 'b'=>2.2], ['c'=>NULL, 'd'=>'test'], ['e'=>true]]
);



pudlUnit(
	$test_object->partition(4),
	[['a'=>1, 'b'=>2.2], ['c'=>NULL], ['d'=>'test'], ['e'=>true]]
);



pudlUnit(
	$test_object->partition(5),
	[['a'=>1], ['b'=>2.2], ['c'=>NULL], ['d'=>'test'], ['e'=>true]]
);



pudlUnit(
	$test_object->partition(6),
	[['a'=>1], ['b'=>2.2], ['c'=>NULL], ['d'=>'test'], ['e'=>true], []]
);



pudlUnit(
	$test_object->in(1),
	true
);



pudlUnit(
	$test_object->in('1'),
	true
);



pudlUnit(
	$test_object->in(1, true),
	true
);



pudlUnit(
	$test_object->in('1', true),
	false
);




$text_data_1	= 'a,b,c,d';
$test_object_1	= new pudlObject($text_data_1, ',');
pudlUnit(
	$test_object_1->raw(),
	['a', 'b', 'c', 'd']
);




$text_data_1	= '"a","b","c","d,e"';
$test_object_1	= new pudlObject($text_data_1, PUDL_CSV);
pudlUnit(
	$test_object_1->raw(),
	['a', 'b', 'c', 'd,e']
);
