<?php

$db->string()->globals();
pudlTest('SHOW GLOBAL STATUS');


$db->string()->variables();
pudlTest('SHOW VARIABLES');


$db->string()->status();
pudlTest('SHOW STATUS');





$db->string()->globals('test%');
pudlTest("SHOW GLOBAL STATUS LIKE 'test%'");


$db->string()->variables('test%');
pudlTest("SHOW VARIABLES LIKE 'test%'");


$db->string()->status('test%');
pudlTest("SHOW STATUS LIKE 'test%'");





$db->string()->globals('test%', 1);
pudlTest("SHOW GLOBAL STATUS LIKE 'test%' LIMIT 1");


$db->string()->variables('test%', 1);
pudlTest("SHOW VARIABLES LIKE 'test%' LIMIT 1");


$db->string()->status('test%', 1);
pudlTest("SHOW STATUS LIKE 'test%' LIMIT 1");





$db->string()->globals('test%', 1, 2);
pudlTest("SHOW GLOBAL STATUS LIKE 'test%' LIMIT 1 OFFSET 2");


$db->string()->variables('test%', 1, 2);
pudlTest("SHOW VARIABLES LIKE 'test%' LIMIT 1 OFFSET 2");


$db->string()->status('test%', 1, 2);
pudlTest("SHOW STATUS LIKE 'test%' LIMIT 1 OFFSET 2");




$db->string()->set('test', 1);
pudlTest("SET @@SESSION.test=1");

$db->string()->set('test', 2.3);
pudlTest("SET @@SESSION.test=2.3");

$db->string()->set('test', INF);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', -INF);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', NAN);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', 'string');
pudlTest("SET @@SESSION.test='string'");

$db->string()->set('test', true);
pudlTest("SET @@SESSION.test=TRUE");

$db->string()->set('test', false);
pudlTest("SET @@SESSION.test=FALSE");

$db->string()->set('test', NULL);
pudlTest("SET @@SESSION.test=NULL");




$db->string()->set('test', 1, true);
pudlTest("SET @@GLOBAL.test=1");

$db->string()->set('test', 2.3, true);
pudlTest("SET @@GLOBAL.test=2.3");

$db->string()->set('test', INF, true);
pudlTest("SET @@GLOBAL.test=NULL");

$db->string()->set('test', -INF, true);
pudlTest("SET @@GLOBAL.test=NULL");

$db->string()->set('test', NAN, true);
pudlTest("SET @@GLOBAL.test=NULL");

$db->string()->set('test', 'string', true);
pudlTest("SET @@GLOBAL.test='string'");

$db->string()->set('test', true, true);
pudlTest("SET @@GLOBAL.test=TRUE");

$db->string()->set('test', false, true);
pudlTest("SET @@GLOBAL.test=FALSE");

$db->string()->set('test', NULL, true);
pudlTest("SET @@GLOBAL.test=NULL");




$db->string()->set('test', 1, false);
pudlTest("SET @@SESSION.test=1");

$db->string()->set('test', 2.3, false);
pudlTest("SET @@SESSION.test=2.3");

$db->string()->set('test', INF, false);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', -INF, false);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', NAN, false);
pudlTest("SET @@SESSION.test=NULL");

$db->string()->set('test', 'string', false);
pudlTest("SET @@SESSION.test='string'");

$db->string()->set('test', true, false);
pudlTest("SET @@SESSION.test=TRUE");

$db->string()->set('test', false, false);
pudlTest("SET @@SESSION.test=FALSE");

$db->string()->set('test', NULL, false);
pudlTest("SET @@SESSION.test=NULL");




$db->string()->set('test', 1, 'thing');
pudlTest("SET @@thing.test=1");

$db->string()->set('test', 2.3, 'thing');
pudlTest("SET @@thing.test=2.3");

$db->string()->set('test', INF, 'thing');
pudlTest("SET @@thing.test=NULL");

$db->string()->set('test', -INF, 'thing');
pudlTest("SET @@thing.test=NULL");

$db->string()->set('test', NAN, 'thing');
pudlTest("SET @@thing.test=NULL");

$db->string()->set('test', 'string', 'thing');
pudlTest("SET @@thing.test='string'");

$db->string()->set('test', true, 'thing');
pudlTest("SET @@thing.test=TRUE");

$db->string()->set('test', false, 'thing');
pudlTest("SET @@thing.test=FALSE");

$db->string()->set('test', NULL, 'thing');
pudlTest("SET @@thing.test=NULL");