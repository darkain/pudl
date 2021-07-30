<?php


// TEST CALLABLE FUNCTION
$function = function() {
	return 'test text';
};

$db->string()->rows('table', $function);
pudlTest($db, "SELECT * FROM `table` WHERE 'test text'");


$db->string()->rows('table', ['column' => $function]);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='test text'");




// ENSURE THAT A STRING REPRESENTING A PHP FUNCTION
// IS NOT "CALLABLE"
$db->string()->rows('table', ['column' => 'time()']);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='time()'");

$db->string()->rows('table', ['column' => '$function']);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='\$function'");

$db->string()->rows('table', ['column' => '$function()']);
pudlTest($db, "SELECT * FROM `table` WHERE `column`='\$function()'");




