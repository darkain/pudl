<?php

//Default Behavior
define('PUDL_DEFAULT',	0);

//Array Types
define('PUDL_ARRAY',	1);
define('PUDL_NUMBER',	2);
define('PUDL_BOTH',		3);
define('PUDL_INDEX',	4);

//Escapes
define('PUDL_START',	1);
define('PUDL_END',		2);
//define('PUDL_BOTH',	3);


/* NOTE: HERE ARE THE NUMERICAL STATE VALUES FOR A GALERA CLUSTER LOCAL STATE
0 - No cluster state information available
1 - Joining (requesting/receiving State Transfer) - node is joining the cluster
2 - Desynced - node is the donor to another node joining the cluster
3 - Joined - node has joined the cluster
4 - Synced - node is synced with the cluster
5 - Donor - node receives sync request from another node in the cluster
6 - Resyncing - node completes sync request from another node in the cluster */
define('GALERA_NONE',		0);
define('GALERA_JOINING',	1);
define('GALERA_DESYNCED',	2);
define('GALERA_JOINED',		3);
define('GALERA_SYNCED',		4);
define('GALERA_DONOR',		5);
define('GALERA_RESYNCING',	6);
