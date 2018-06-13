<?php



$clone = new pudlClone($db);


pudlUnit(
	pudlCLone::like(['test'])
	instanceof pudlLike
);


pudlUnit(
	$clone->like(['test'])
	instanceof pudlLike
);


pudlUnit(
	$clone->time(),
	$db->time()
);


pudlUnit(
	$clone->microtime(),
	$db->microtime()
);
