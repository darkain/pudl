<?php

$db->string()->globals();
pudlTest($db, 'SHOW GLOBAL STATUS');


$db->string()->variables();
pudlTest($db, 'SHOW VARIABLES');


$db->string()->status();
pudlTest($db, 'SHOW STATUS');





$db->string()->globals('test%');
pudlTest($db, "SHOW GLOBAL STATUS LIKE 'test%'");


$db->string()->variables('test%');
pudlTest($db, "SHOW VARIABLES LIKE 'test%'");


$db->string()->status('test%');
pudlTest($db, "SHOW STATUS LIKE 'test%'");





$db->string()->globals('test%', 1);
pudlTest($db, "SHOW GLOBAL STATUS LIKE 'test%' LIMIT 1");


$db->string()->variables('test%', 1);
pudlTest($db, "SHOW VARIABLES LIKE 'test%' LIMIT 1");


$db->string()->status('test%', 1);
pudlTest($db, "SHOW STATUS LIKE 'test%' LIMIT 1");





$db->string()->globals('test%', 1, 2);
pudlTest($db, "SHOW GLOBAL STATUS LIKE 'test%' LIMIT 1 OFFSET 2");


$db->string()->variables('test%', 1, 2);
pudlTest($db, "SHOW VARIABLES LIKE 'test%' LIMIT 1 OFFSET 2");


$db->string()->status('test%', 1, 2);
pudlTest($db, "SHOW STATUS LIKE 'test%' LIMIT 1 OFFSET 2");




$db->string()->set('test', 1);
pudlTest($db, "SET @@SESSION.test=1");

$db->string()->set('test', 2.3);
pudlTest($db, "SET @@SESSION.test=2.3");

$db->string()->set('test', INF);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', -INF);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', NAN);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', 'string');
pudlTest($db, "SET @@SESSION.test='string'");

$db->string()->set('test', true);
pudlTest($db, "SET @@SESSION.test=TRUE");

$db->string()->set('test', false);
pudlTest($db, "SET @@SESSION.test=FALSE");

$db->string()->set('test', NULL);
pudlTest($db, "SET @@SESSION.test=NULL");




$db->string()->set('test', 1, true);
pudlTest($db, "SET @@GLOBAL.test=1");

$db->string()->set('test', 2.3, true);
pudlTest($db, "SET @@GLOBAL.test=2.3");

$db->string()->set('test', INF, true);
pudlTest($db, "SET @@GLOBAL.test=NULL");

$db->string()->set('test', -INF, true);
pudlTest($db, "SET @@GLOBAL.test=NULL");

$db->string()->set('test', NAN, true);
pudlTest($db, "SET @@GLOBAL.test=NULL");

$db->string()->set('test', 'string', true);
pudlTest($db, "SET @@GLOBAL.test='string'");

$db->string()->set('test', true, true);
pudlTest($db, "SET @@GLOBAL.test=TRUE");

$db->string()->set('test', false, true);
pudlTest($db, "SET @@GLOBAL.test=FALSE");

$db->string()->set('test', NULL, true);
pudlTest($db, "SET @@GLOBAL.test=NULL");




$db->string()->set('test', 1, false);
pudlTest($db, "SET @@SESSION.test=1");

$db->string()->set('test', 2.3, false);
pudlTest($db, "SET @@SESSION.test=2.3");

$db->string()->set('test', INF, false);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', -INF, false);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', NAN, false);
pudlTest($db, "SET @@SESSION.test=NULL");

$db->string()->set('test', 'string', false);
pudlTest($db, "SET @@SESSION.test='string'");

$db->string()->set('test', true, false);
pudlTest($db, "SET @@SESSION.test=TRUE");

$db->string()->set('test', false, false);
pudlTest($db, "SET @@SESSION.test=FALSE");

$db->string()->set('test', NULL, false);
pudlTest($db, "SET @@SESSION.test=NULL");




$db->string()->set('test', 1, 'thing');
pudlTest($db, "SET @@thing.test=1");

$db->string()->set('test', 2.3, 'thing');
pudlTest($db, "SET @@thing.test=2.3");

$db->string()->set('test', INF, 'thing');
pudlTest($db, "SET @@thing.test=NULL");

$db->string()->set('test', -INF, 'thing');
pudlTest($db, "SET @@thing.test=NULL");

$db->string()->set('test', NAN, 'thing');
pudlTest($db, "SET @@thing.test=NULL");

$db->string()->set('test', 'string', 'thing');
pudlTest($db, "SET @@thing.test='string'");

$db->string()->set('test', true, 'thing');
pudlTest($db, "SET @@thing.test=TRUE");

$db->string()->set('test', false, 'thing');
pudlTest($db, "SET @@thing.test=FALSE");

$db->string()->set('test', NULL, 'thing');
pudlTest($db, "SET @@thing.test=NULL");