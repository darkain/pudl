<?php

$db->string()->globals();
pudlTest('SHOW GLOBAL STATUS');


$db->string()->variables();
pudlTest('SHOW VARIABLES');


$db->string()->status();
pudlTest('SHOW STATUS');





$db->string()->globals('test%');
pudlTest('SHOW GLOBAL STATUS LIKE "test%"');


$db->string()->variables('test%');
pudlTest('SHOW VARIABLES LIKE "test%"');


$db->string()->status('test%');
pudlTest('SHOW STATUS LIKE "test%"');





$db->string()->globals('test%', 1);
pudlTest('SHOW GLOBAL STATUS LIKE "test%" LIMIT 1');


$db->string()->variables('test%', 1);
pudlTest('SHOW VARIABLES LIKE "test%" LIMIT 1');


$db->string()->status('test%', 1);
pudlTest('SHOW STATUS LIKE "test%" LIMIT 1');





$db->string()->globals('test%', 1, 2);
pudlTest('SHOW GLOBAL STATUS LIKE "test%" LIMIT 2,1');


$db->string()->variables('test%', 1, 2);
pudlTest('SHOW VARIABLES LIKE "test%" LIMIT 2,1');


$db->string()->status('test%', 1, 2);
pudlTest('SHOW STATUS LIKE "test%" LIMIT 2,1');
