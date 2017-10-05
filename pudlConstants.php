<?php

//Default Behavior
define('PUDL_DEFAULT',		0x00);

//Array Types
define('PUDL_ARRAY',		0x01);
define('PUDL_NUMBER',		0x02);
define('PUDL_BOTH',			0x03);
define('PUDL_INDEX',		0x04);

//Escapes
define('PUDL_START',		0x01);
define('PUDL_END',			0x02);
//define('PUDL_BOTH',		0x03);

//PUDL Object
define('PUDL_CSV',			0x10);


/* NOTE: HERE ARE THE NUMERICAL STATE VALUES FOR A GALERA CLUSTER LOCAL STATE
0 - No cluster state information available
1 - Joining (requesting/receiving State Transfer) - node is joining the cluster
2 - Desynced - node is the donor to another node joining the cluster
3 - Joined - node has joined the cluster
4 - Synced - node is synced with the cluster
5 - Donor - node receives sync request from another node in the cluster
6 - Resyncing - node completes sync request from another node in the cluster */
define('GALERA_NONE',		0x00);
define('GALERA_JOINING',	0x01);
define('GALERA_DESYNCED',	0x02);
define('GALERA_JOINED',		0x03);
define('GALERA_SYNCED',		0x04);
define('GALERA_DONOR',		0x05);
define('GALERA_RESYNCING',	0x06);


//PudlException Codes
define('PUDL_X_CONNECTION',	0x01);