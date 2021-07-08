<?php


// START A TRANSACTION
$db->string()->begin();
pudlTest($db, "START TRANSACTION");

pudlUnit($db->inTransaction(), true);



// FINISH THE TRANSACTION
$db->string()->commit();
pudlTest($db, "COMMIT");

pudlUnit($db->inTransaction(), false);



// START ANOTHER TRANSACTION
$db->string()->begin(true);
pudlTest($db, "START TRANSACTION WITH CONSISTENT SNAPSHOT");

pudlUnit($db->inTransaction(), true);



// FINISH THE TRANSACTION
$db->string()->commit();
pudlTest($db, "COMMIT");

pudlUnit($db->inTransaction(), false);
