<?php

require_once('../pudlSqlite.php');

$db = new pudlSqlite('test.db');

require('all.php');

echo "ALL GOOD!!\n";
