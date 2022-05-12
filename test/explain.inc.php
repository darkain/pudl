<?php


$db->string()->explain('SELECT 1');
pudlTest($db, 'EXPLAIN (SELECT 1)');



$db->string()->explain('SELECT 1', 'TRADITIONAL');
pudlTest($db, "EXPLAIN FORMAT='TRADITIONAL' (SELECT 1)");




$db->string()->explain('SELECT 1', 'json');
pudlTest($db, "EXPLAIN FORMAT='JSON' (SELECT 1)");



$db->string()->explain('SELECT 1', 'Tree');
pudlTest($db, "EXPLAIN FORMAT='TREE' (SELECT 1)");
