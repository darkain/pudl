<?php

//RAW unmodified SQL by invoking $db('SQL STATEMENT')
$db->string()('SELECT * FROM table');
pudlTest($db, 'SELECT * FROM table');




//RAW unmodified SQL by invoking $db('SQL STATEMENT')
$db->string();
$db('SELECT * FROM table');
pudlTest($db, 'SELECT * FROM table');
