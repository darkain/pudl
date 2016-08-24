<?php

//RAW SQL using ->query('STATEMENT')
$db->string()->query('SELECT * FROM table');
pudlTest('SELECT * FROM table');




//RAW SQL using $db('STATEMENT')
$db->string();
$db('SELECT * FROM table');
pudlTest('SELECT * FROM table');
