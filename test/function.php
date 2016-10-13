<?php

//TIMESTAMP for December 13th, 2015 @ 9:46 AM (UTC)
$db->string()->row('table', ['column'=>pudlFunction::timestamp(1450000000)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=CONVERT_TZ(FROM_UNIXTIME(1450000000), @@SESSION.time_zone, 'UTC')) LIMIT 1");



//Verify TIMESTAMP conversion is working properly! Check pudlFunction for notes if this fails
/*if (is_a($db, 'pudlGalera')) {
	$row = $db->selectRow(['time'=>pudlFunction::timestamp()], false);
	pudlTest( strtotime(reset($row)) === $db->time() );
}*/




//TIMESTAMP for right now
$db->string()->select($db->date());
pudlTest("SELECT FROM_UNIXTIME(" . $db->time() . ")");




//TIMESTAMP for December 13th, 2015 @ 9:46 AM (UTC)
$db->string()->select($db->date(1450000000));
pudlTest("SELECT FROM_UNIXTIME(1450000000)");




$db->string()->row('table', ['column'=>pudl::column('other')]);
pudlTest("SELECT * FROM `table` WHERE (`column`=`other`) LIMIT 1");




$db->string()->row('table', [pudl::column('column', 'value')]);
pudlTest("SELECT * FROM `table` WHERE (`column`='value') LIMIT 1");




$db->string()->row('table', [pudl::column('column1', pudl::column('column2'))]);
pudlTest("SELECT * FROM `table` WHERE (`column1`=`column2`) LIMIT 1");




$db->string()->row('table', [pudl::column('column1', pudl::column('column2')->not())]);
pudlTest("SELECT * FROM `table` WHERE (`column1`!=`column2`) LIMIT 1");




$db->string()->row('table', [pudl::column('column', [1,2,3])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (1, 2, 3)) LIMIT 1");




$db->string()->row('table', [pudl::column('column', false)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=FALSE) LIMIT 1");




$db->string()->row('table', [pudl::column('column', true)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=TRUE) LIMIT 1");




$db->string()->row('table', [
	pudl::column(
		pudlFunction::replace(pudl::column('column'), 'old', 'new'),
		'value'
	)
]);
pudlTest("SELECT * FROM `table` WHERE (REPLACE(`column`, 'old', 'new')='value') LIMIT 1");




$db->string()->row('table', [
	pudl::column(
		pudl::_replace(pudl::column('column'), 'old', 'new'),
		pudl::like('value')
	)
]);
pudlTest("SELECT * FROM `table` WHERE (REPLACE(`column`, 'old', 'new') LIKE '%value%') LIMIT 1");




$db->string()->row('table', [pudl::find('column', ['a', 'b', 'c'])]);
pudlTest("SELECT * FROM `table` WHERE ((FIND_IN_SET('a', `column`) OR FIND_IN_SET('b', `column`) OR FIND_IN_SET('c', `column`))) LIMIT 1");




$db->string()->row('table', [pudl::find('column', 'a,b,c')]);
pudlTest("SELECT * FROM `table` WHERE ((FIND_IN_SET('a', `column`) OR FIND_IN_SET('b', `column`) OR FIND_IN_SET('c', `column`))) LIMIT 1");




$db->string()->row('table', ['column' => pudl::regexp('expression')]);
pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP 'expression') LIMIT 1");




$db->string()->row('table', [
	'column' => pudl::regexp(
		'part1',
		'part2',
		'part3'
	)
]);
pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP 'part1part2part3') LIMIT 1");




$db->string()->row('table', [
	'column' => pudl::regexp(
		'[[:<:]]',
		'value',
		'[[:>:]]'
	)
]);
if ($db instanceof pudlMySqli  ||  $db instanceof pudlNull) {
	pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP '\\\\[\\\\[\\\\:\\\\<\\\\:\\\\]\\\\]value\\\\[\\\\[\\\\:\\\\>\\\\:\\\\]\\\\]') LIMIT 1");
} else {
	pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP '\\[\\[\\:\\<\\:\\]\\]value\\[\\[\\:\\>\\:\\]\\]') LIMIT 1");
}




$db->string()->row('table', [
	'column' => pudl::regexp(
		pudl::raw('[[:<:]]'),
		'value',
		pudl::raw('[[:>:]]')
	)
]);
pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP '[[:<:]]value[[:>:]]') LIMIT 1");




$db->string()->row('table', [
	'column' => pudl::regexp([
		pudl::raw('[[:<:]]'),
		'value',
		pudl::raw('[[:>:]]')
	])
]);
pudlTest("SELECT * FROM `table` WHERE (`column` REGEXP '[[:<:]]value[[:>:]]') LIMIT 1");




$db->string()->row('table', pudl::reglike('column', '12345', '\\d'));
if ($db instanceof pudlMySqli  ||  $db instanceof pudlNull) {
	pudlTest("SELECT * FROM `table` WHERE (REGEXP_REPLACE(`column`, '\\\\d', '') LIKE '%12345%') LIMIT 1");
} else {
	pudlTest("SELECT * FROM `table` WHERE (REGEXP_REPLACE(`column`, '\\d', '') LIKE '%12345%') LIMIT 1");
}




$db->string()->row('table', [pudl::reglike('column', '12345', '\\d')]);
if ($db instanceof pudlMySqli  ||  $db instanceof pudlNull) {
	pudlTest("SELECT * FROM `table` WHERE (REGEXP_REPLACE(`column`, '\\\\d', '') LIKE '%12345%') LIMIT 1");
} else {
	pudlTest("SELECT * FROM `table` WHERE (REGEXP_REPLACE(`column`, '\\d', '') LIKE '%12345%') LIMIT 1");
}




$db->string()->select(pudl::text('value'));
pudlTest("SELECT 'value'");




$db->string()->select(['column' => pudl::text('value')]);
pudlTest("SELECT 'value' AS `column`");




$db->string()->select(pudl::now());
pudlTest("SELECT NOW()");




$db->string()->select([pudl::curdate(), pudl::curtime()]);
pudlTest("SELECT CURDATE(), CURTIME()");




$db->redis(true);
if ($db instanceof pudlMySqli) {
	$db->cache(1)->select(pudl::now());
	pudlTest("SELECT SQL_CACHE NOW()");
}




$db->string()->select(pudl::json('column'));
pudlTest("SELECT COLUMN_JSON(`column`)");




$db->string()->row('table', [
	'z' => 0,
	pudl::_and([
		pudl::_and([
			'a' => 1,
			'b' => 2,
		]),
		pudl::_and([
			'c=3',
			'd=4',
		]),
	]),
]);
pudlTest("SELECT * FROM `table` WHERE (`z`=0 AND ((`a`=1 AND `b`=2) AND (c=3 AND d=4))) LIMIT 1");




$db->string()->row('table', [
	'z' => 0,
	pudl::_or([
		pudl::_or([
			'a' => 1,
			'b' => 2,
		]),
		pudl::_or([
			'c=3',
			'd=4',
		]),
	]),
]);
pudlTest("SELECT * FROM `table` WHERE (`z`=0 AND ((`a`=1 OR `b`=2) OR (c=3 OR d=4))) LIMIT 1");




$db->string()->row('table', pudl::_or([
	[
		'a' => 1,
		'b' => 2,
	],
	[
		'c=3',
		'd=4',
	],
]));
pudlTest("SELECT * FROM `table` WHERE ((`a`=1 AND `b`=2) OR (c=3 AND d=4)) LIMIT 1");




$db->string()->row('table', pudl::_or(pudl::_and([
	'a' => 1,
	'b' => 2,
])));
pudlTest("SELECT * FROM `table` WHERE (`a`=1 AND `b`=2) LIMIT 1");
