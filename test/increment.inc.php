<?php

//UPDATE statement - incrementing an INTEGER value
$db->string()->increment('table', 'item', ['column'=>'value']);
pudlTest($db, "UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value of 10
$db->string()->increment('table', 'item', ['column'=>'value'], 10);
pudlTest($db, "UPDATE `table` SET `item`=`item`+10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value of negative 10
$db->string()->increment('table', 'item', ['column'=>'value'], -10);
pudlTest($db, "UPDATE `table` SET `item`=`item`-10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value');
pudlTest($db, "UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value', 10);
pudlTest($db, "UPDATE `table` SET `item`=`item`+10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value', -10);
pudlTest($db, "UPDATE `table` SET `item`=`item`-10 WHERE (`column`='value')");




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', NULL);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: NULL');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', INF);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: double');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', -INF);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: double');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', true);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: boolean');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', false);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: boolean');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', []);
	pudlTest($db, 'pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid data type for increment: array');
}
