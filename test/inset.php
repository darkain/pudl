<?php

$db->string()->rows('table', ['column' => pudl::inSet('1,2,3')]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN ('1,2,3'))");




$db->string()->rows('table', ['column' => pudl::notInSet('1,2,3')]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN ('1,2,3'))");




$db->string()->rows('table', ['column' => pudl::inSet([1,2,3])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (1, 2, 3))");




$db->string()->rows('table', ['column' => pudl::notInSet([1,2,3])]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN (1, 2, 3))");




$db->string()->rows('table', ['column' => pudl::inSet([ [1], [2], [3] ])]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN (1, 2, 3))");




$db->string()->rows('table', ['column' => pudl::notInSet([ [1], [2], [3] ])]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN (1, 2, 3))");




$set = $db->select([pudl::hex('VALUE')], false);
$db->string()->rows('table', ['column' => pudl::inSet($set)]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN ('56414C5545'))");




$set = $db->select([pudl::hex('VALUE')], false);
$db->string()->rows('table', ['column' => pudl::notInSet($set)]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN ('56414C5545'))");




$set = $db->selectRows([pudl::hex('VALUE')], false);
$db->string()->rows('table', ['column' => pudl::inSet($set)]);
pudlTest("SELECT * FROM `table` WHERE (`column` IN ('56414C5545'))");




$set = $db->selectRows([pudl::hex('VALUE')], false);
$db->string()->rows('table', ['column' => pudl::notInSet($set)]);
pudlTest("SELECT * FROM `table` WHERE (`column` NOT IN ('56414C5545'))");
