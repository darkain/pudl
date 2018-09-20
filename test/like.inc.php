<?php


$db->string()->rows('table', ['column' => pudl::like('test')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%test%')");



$db->string()->rows('table', ['column' => pudl::likeRaw('test')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE 'test')");



$db->string()->rows('table', ['column' => pudl::likeLeft('test')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%test')");



$db->string()->rows('table', ['column' => pudl::likeRight('test')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE 'test%')");



$db->string()->rows('table', ['column' => pudl::like('%te%st%')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%\%te\%st\%%')");



$db->string()->rows('table', ['column' => pudl::likeRaw('%te%st%')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%te%st%')");



$db->string()->rows('table', ['column' => pudl::likeLeft('%te%st%')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%\%te\%st\%')");



$db->string()->rows('table', ['column' => pudl::likeRight('%te%st%')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '\%te\%st\%%')");



$db->string()->rows('table', ['column' => pudl::like('_te_st_')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%\_te\_st\_%')");



$db->string()->rows('table', ['column' => pudl::likeRaw('_te_st_')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '_te_st_')");



$db->string()->rows('table', ['column' => pudl::likeLeft('_te_st_')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '%\_te\_st\_')");



$db->string()->rows('table', ['column' => pudl::likeRight('_te_st_')]);
pudlTest("SELECT * FROM `table` WHERE (`column` LIKE '\_te\_st\_%')");
