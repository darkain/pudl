<?php


function pudl_array($item) {
	if (is_array($item)) return true;
	return ($item instanceof ArrayAccess);
}



class pudlException extends Exception {}



interface pudlHelper {}



interface pudlId {
	public function pudlId();
}



interface pudlValue {
	public function pudlValue($db, $quote=true);
}
