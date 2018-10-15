<?php


$db->string()->delete('table', 'x=1');
pudlTest($db, 'DELETE FROM `table` WHERE (`x`=1)');




$db->string()->delete('table', ['y=2']);
pudlTest($db, 'DELETE FROM `table` WHERE (`y`=2)');




$db->string()->delete('table', ['z'=>[1,2,3]]);
pudlTest($db, 'DELETE FROM `table` WHERE (`z` IN (1, 2, 3))');




$db->string()->delete('table', 'x=y', 2, 3);
pudlTest($db, 'DELETE FROM `table` WHERE (`x`=`y`) LIMIT 2 OFFSET 3');




$db->string()->delete('table', 'x=y', 5);
pudlTest($db, 'DELETE FROM `table` WHERE (`x`=`y`) LIMIT 5');




$db->string()->deleteId('table', 'column', 'value');
pudlTest($db, "DELETE FROM `table` WHERE (`column`='value')");




$db->string()->deleteRow('table', 'a=0');
pudlTest($db, 'DELETE FROM `table` WHERE (`a`=0) LIMIT 1');
