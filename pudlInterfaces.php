<?php


function pudl_array($item) {
	return is_array($item) || ($item instanceof ArrayAccess);
}



function pudl_require_extension($extension) {
	if (extension_loaded($extension)) return;
	throw new pudlException('Required PHP extension is missing: ' . $extension);
}



class pudlException extends Exception {
	public function __construct($message=null, $code=0, $previous=null) {
		parent::__construct($message, $code, $previous);

		global $db;
		if (!empty($db)) $db->decache()->destring();
	}
}



interface pudlHelper {}



interface pudlId {
	public function pudlId();
}



interface pudlValue {
	public function pudlValue($db, $quote=true);
}




////////////////////////////////////////////////////////////////////////////////
// FIX FOR PUDL OBJECT RECURSIVE ARRAYS
// SOURCE: http://php.net/manual/en/function.array-diff-assoc.php#111675
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('array_diff_assoc_recursive')) {
	function array_diff_assoc_recursive($array1, $array2) {
		$difference = [];
		foreach($array1 as $key => $value) {
			if (pudl_array($value)) {
				if(!array_key_exists($key, $array2)  ||  !pudl_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$new_diff = array_diff_assoc_recursive(
						(array)$value,
						(array)$array2[$key]
					);
					if(!empty($new_diff)) $difference[$key] = $new_diff;
				}
			} else if(!array_key_exists($key, $array2)  ||  $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}
		return $difference;
	}
}
