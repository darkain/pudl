<?php

//UPDATE statement - incrementing an INTEGER value
$db->string()->increment('table', 'item', ['column'=>'value']);
pudlTest("UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value of 10
$db->string()->increment('table', 'item', ['column'=>'value'], 10);
pudlTest("UPDATE `table` SET `item`=`item`+10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value of negative 10
$db->string()->increment('table', 'item', ['column'=>'value'], -10);
pudlTest("UPDATE `table` SET `item`=`item`-10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value');
pudlTest("UPDATE `table` SET `item`=`item`+1 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value', 10);
pudlTest("UPDATE `table` SET `item`=`item`+10 WHERE (`column`='value')");




//UPDATE statement - incrementing an INTEGER value
$db->string()->incrementId('table', 'item', 'column', 'value', -10);
pudlTest("UPDATE `table` SET `item`=`item`-10 WHERE (`column`='value')");




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', NULL);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: NULL');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', INF);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: double');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', -INF);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: double');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', true);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: boolean');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', false);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: boolean');
}




//UPDATE statement - invalid increment value
try {
	$db->string()->incrementId('table', 'item', 'column', 'value', []);
	pudlTest('pudlException');
} catch (pudlException $error) {
	pudlError($error, 'Invalid value for increment: array');
}
