<?php

//TIMESTAMP for December 13th, 2015 @ 9:46 AM (UTC)
$db->string()->row('table', ['column'=>pudlFunction::timestamp(1450000000)]);
pudlTest("SELECT * FROM `table` WHERE (`column`=CONVERT_TZ(FROM_UNIXTIME(1450000000), @@session.time_zone, 'UTC')) LIMIT 1");




//Verify TIMESTAMP conversion is working properly! Check pudlFunction for notes if this fails
if (is_a($db, 'pudlGalera')) {
	$row = $db->selectRow(['time'=>pudlFunction::timestamp()], false);
	pudlTest( strtotime(reset($row)) === $db->time() );
}
