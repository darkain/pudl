<?php

//UPDATE statement - incrementing an INTEGER value
$db->string()->increment('table', 'item', ['column'=>'value']);
pudlTest("UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value');
pudlTest("UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");
