<?php



$db->string()->collection('table');
pudlTest('SELECT * FROM `table`');



$db->string()->collection('table', 'key', 'value');
pudlTest('SELECT key, value FROM `table`');
