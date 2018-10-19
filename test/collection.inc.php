<?php



$db->string()->collection('table');
pudlTest($db, 'SELECT * FROM `table`');



$db->string()->collection('table', 'key', 'value');
pudlTest($db, 'SELECT `key`, `value` FROM `table`');



//A BASIC ORM USED FOR SEVERAL UNIT TESTS
class test_orm extends pudlOrm {}



$db->string();
test_orm::select($db);
pudlTest($db, 'SELECT * FROM `pudl` LIMIT 1');



$db->string();
test_orm::select($db, ['clause'=>['id'=>1]]);
pudlTest($db, 'SELECT * FROM `pudl` WHERE (`id`=1) LIMIT 1');



$db->string();
test_orm::collect($db);
pudlTest($db, 'SELECT * FROM `pudl`');



$db->string();
test_orm::collect($db, ['clause'=>['x'=>1], 'limit'=>10]);
pudlTest($db, 'SELECT * FROM `pudl` WHERE (`x`=1) LIMIT 10');



$db->string();
test_orm::collection($db, [1,2,3,4,5]);
pudlTest($db, 'SELECT * FROM `pudl` WHERE (`id` IN (1, 2, 3, 4, 5))');



$db->string();
test_orm::collection($db, [1,2,3,4,5], ['limit'=>[1,2]]);
pudlTest($db, 'SELECT * FROM `pudl` WHERE (`id` IN (1, 2, 3, 4, 5)) LIMIT 1 OFFSET 2');
