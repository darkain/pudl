<?php


$db->string()->tables();
pudlTest('SHOW TABLES');




// COMPARE COLUMN TO COLUMN
$db->string()->tables('name=value');
pudlTest('SHOW TABLES WHERE (`name`=`value`)');




// COMPARE COLUMN TO COLUMN
$db->string()->tables(['name=value']);
pudlTest('SHOW TABLES WHERE (`name`=`value`)');




// COMPARE COLUMN TO VALUE
$db->string()->tables(['name' => 'value']);
pudlTest("SHOW TABLES WHERE (`name`='value')");
