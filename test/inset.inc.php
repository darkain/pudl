<?php

$db->string()->rows('table', ['column' => pudl::inSet('1,2,3')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN ('1,2,3')");




$db->string()->rows('table', ['column' => pudl::notInSet('1,2,3')]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN ('1,2,3')");




$db->string()->rows('table', ['column' => pudl::inSet([1,2,3])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (1, 2, 3)");




$db->string()->rows('table', ['column' => pudl::notInSet([1,2,3])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN (1, 2, 3)");




$db->string()->rows('table', ['column' => pudl::inSet([ [1], [2], [3] ])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN (1, 2, 3)");




$db->string()->rows('table', ['column' => pudl::notInSet([ [1], [2], [3] ])]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN (1, 2, 3)");




if (($db instanceof pudlNull)) {
	$set = new pudlShellResult($db, json_encode(
		['header'=>['column'], 'data'=>[[strtoupper(bin2hex('VALUE'))]]]
	));
} else {
	$set = $db->select([pudl::hex('VALUE')], false);
}
$db->string()->rows('table', ['column' => pudl::inSet($set)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN ('56414C5545')");




if (($db instanceof pudlNull)) {
	$set = new pudlShellResult($db, json_encode(
		['header'=>['column'], 'data'=>[[strtoupper(bin2hex('VALUE'))]]]
	));
} else {
	$set = $db->select([pudl::hex('VALUE')], false);
}
$db->string()->rows('table', ['column' => pudl::notInSet($set)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN ('56414C5545')");




if (($db instanceof pudlNull)) {
	$set = [['column' => strtoupper(bin2hex('VALUE'))]];
} else {
	$set = $db->selectRows([pudl::hex('VALUE')], false);
}
$db->string()->rows('table', ['column' => pudl::inSet($set)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` IN ('56414C5545')");




if (($db instanceof pudlNull)) {
	$set = [['column' => strtoupper(bin2hex('VALUE'))]];
} else {
	$set = $db->selectRows([pudl::hex('VALUE')], false);
}
$db->string()->rows('table', ['column' => pudl::notInSet($set)]);
pudlTest($db, "SELECT * FROM `table` WHERE `column` NOT IN ('56414C5545')");
