<?php


$db->string()->tables();
pudlTest($db, 'SHOW TABLES');




// COMPARE COLUMN TO COLUMN
$db->string()->tables('name=value');
pudlTest($db, 'SHOW TABLES WHERE `name`=`value`');




// COMPARE COLUMN TO COLUMN
$db->string()->tables(['name=value']);
pudlTest($db, 'SHOW TABLES WHERE `name`=`value`');




// COMPARE COLUMN TO VALUE
$db->string()->tables(['name' => 'value']);
pudlTest($db, "SHOW TABLES WHERE `name`='value'");
