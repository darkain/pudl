<?php

//UPDATE statement - using associative array and clause
$db->string()->update('table', ['column'=>'value'], 'id=1');
pudlTest("UPDATE `table` SET `column`='value' WHERE (id=1)");




//UPDATE statement - using associative array and ID
$db->string()->updateId('table', ['column'=>'value'], 'id', 'value');
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->update('table', ['column'=>pudlFunction::increment()], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+1 WHERE (id=1)");




//UPDATE statement - incrementing an INTEGER value
$db->string()->update('table', ['column'=>pudlFunction::increment('5')], 'id=1');
pudlTest("UPDATE `table` SET `column`=`column`+'5' WHERE (id=1)");




//UPDATE statement - incrementing an INTEGER value
$db->string()->updateIn('table', ['column'=>'value'], 'id', [1,7,7,9]);
pudlTest("UPDATE `table` SET `column`='value' WHERE (`id` IN (1,7,7,9))");




$db->string()->update('table', 'column=value', 'id=1');
pudlTest("UPDATE `table` SET column=value WHERE (id=1)");




$db->string()->update('table', ['column=value'], 'id=1');
pudlTest("UPDATE `table` SET column=value WHERE (id=1)");
