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
