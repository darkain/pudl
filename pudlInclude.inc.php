<?php




////////////////////////////////////////////////////////////////////////////////
//CONSTANTS, INTERFACES, HELPER CLASSES AND FUNCTIONS
////////////////////////////////////////////////////////////////////////////////
require_once(is_owner(__DIR__.'/pudlConstants.php'));
require_once(is_owner(__DIR__.'/pudlException.php'));
require_once(is_owner(__DIR__.'/pudlInterfaces.php'));
require_once(is_owner(__DIR__.'/pudlHelpers.php'));
require_once(is_owner(__DIR__.'/pudlOrm.php'));
require_once(is_owner(__DIR__.'/pudlList.php'));
require_once(is_owner(__DIR__.'/clone/pudlClone.php'));




////////////////////////////////////////////////////////////////////////////////
//INTERNAL USAGE RESULT SETS
////////////////////////////////////////////////////////////////////////////////
require_once(is_owner(__DIR__.'/pudlResult.php'));
require_once(is_owner(__DIR__.'/traits/pudlStringResult.php'));
require_once(is_owner(__DIR__.'/traits/pudlCacheResult.php'));




////////////////////////////////////////////////////////////////////////////////
//TRAITS
////////////////////////////////////////////////////////////////////////////////
require_once(is_owner(__DIR__.'/traits/pudlCte.php'));
require_once(is_owner(__DIR__.'/traits/pudlAuth.php'));
require_once(is_owner(__DIR__.'/traits/pudlJson.php'));
require_once(is_owner(__DIR__.'/traits/pudlAlias.php'));
require_once(is_owner(__DIR__.'/traits/pudlRedis.php'));
require_once(is_owner(__DIR__.'/traits/pudlQuery.php'));
require_once(is_owner(__DIR__.'/traits/pudlUnion.php'));
require_once(is_owner(__DIR__.'/traits/pudlTable.php'));
require_once(is_owner(__DIR__.'/traits/pudlSelect.php'));
require_once(is_owner(__DIR__.'/traits/pudlInsert.php'));
require_once(is_owner(__DIR__.'/traits/pudlUpdate.php'));
require_once(is_owner(__DIR__.'/traits/pudlDelete.php'));
require_once(is_owner(__DIR__.'/traits/pudlStatic.php'));
require_once(is_owner(__DIR__.'/traits/pudlCompare.php'));
require_once(is_owner(__DIR__.'/traits/pudlCounter.php'));
require_once(is_owner(__DIR__.'/traits/pudlRequire.php'));
require_once(is_owner(__DIR__.'/traits/pudlCallback.php'));
require_once(is_owner(__DIR__.'/traits/pudlInternal.php'));
require_once(is_owner(__DIR__.'/traits/pudlTransaction.php'));
