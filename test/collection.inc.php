<?php



$db->string()->collection('table');
pudlTest('SELECT * FROM `table`');



$db->string()->collection('table', 'key', 'value');
pudlTest('SELECT `key`, `value` FROM `table`');



//A BASIC ORM USED FOR SEVERAL UNIT TESTS
class test_orm extends pudlOrm {
	const classname = __CLASS__;
}



$db->string();
test_orm::select();
pudlTest('SELECT * FROM `pudl` LIMIT 1');



$db->string();
test_orm::select(['clause'=>['id'=>1]]);
pudlTest('SELECT * FROM `pudl` WHERE (`id`=1) LIMIT 1');



$db->string();
test_orm::collect();
pudlTest('SELECT * FROM `pudl`');



$db->string();
test_orm::collect(['clause'=>['x'=>1], 'limit'=>10]);
pudlTest('SELECT * FROM `pudl` WHERE (`x`=1) LIMIT 10');



$db->string();
test_orm::collection([1,2,3,4,5]);
pudlTest('SELECT * FROM `pudl` WHERE (`id` IN (1, 2, 3, 4, 5))');



$db->string();
test_orm::collection([1,2,3,4,5], ['limit'=>[1,2]]);
pudlTest('SELECT * FROM `pudl` WHERE (`id` IN (1, 2, 3, 4, 5)) LIMIT 1 OFFSET 2');
