<?php

//INSERT statement - using associative array
$db->string()->insert('table', ['column'=>'value']);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value')");




//INSERT statement - using associative array, duplicate key update
$db->string()->insert('table', ['column'=>'value'], true);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `column`='value'");




//INSERT statement - using associative array, custom duplicate key update
$db->string()->insert('table', ['column'=>'value'], 'x=x+1');
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE x=x+1");




//INSERT statement - using associative array, custom duplicate key update using UPDATE syntax
$db->string()->insert('table', ['column'=>'value'], ['y'=>2]);
pudlTest("INSERT INTO `table` (`column`) VALUES ('value') ON DUPLICATE KEY UPDATE `y`=2");
