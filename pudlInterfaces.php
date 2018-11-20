<?php




////////////////////////////////////////////////////////////////////////////////
// CHECK IF THE GIVEN ITEM IS EITHER AN ARRAY
// OR AN OBJECT IMPLEMENTING ARRAYACCESS
////////////////////////////////////////////////////////////////////////////////
function pudl_array($item) {
	return is_array($item) || ($item instanceof ArrayAccess);
}




////////////////////////////////////////////////////////////////////////////////
// VERIFY THE REQUIRED PHP EXTENSION IS INSTALLED ON THE LOCAL SERVER
////////////////////////////////////////////////////////////////////////////////
function pudl_require_extension($extension) {
	if (extension_loaded($extension)) return;
	throw new pudlExtensionException(
		NULL,
		'Required PHP extension is missing: ' . $extension
	);
}




////////////////////////////////////////////////////////////////////////////////
// HELPER INTERFACE TO SHOW PUDL THE GIVEN CLASS BELONGS TO THIS LIBRARY
// THIS SHOULD *NOT* BE USED OUTSIDE OF THE PUDL LIBRARY
////////////////////////////////////////////////////////////////////////////////
interface pudlHelper {}




////////////////////////////////////////////////////////////////////////////////
// ALLOW A CLASS TO RETURN AN ID RECOGNIZED BY PUDL
////////////////////////////////////////////////////////////////////////////////
interface pudlId {
	public function pudlId();
}




////////////////////////////////////////////////////////////////////////////////
// ALLOW A CLASS TO RETURN A VALUE RECOGNIZED BY PUDL
////////////////////////////////////////////////////////////////////////////////
interface pudlValue {
	public function pudlValue(pudl $pudl, $quote=true);
}




////////////////////////////////////////////////////////////////////////////////
// FIX FOR PUDL OBJECT RECURSIVE ARRAYS
// SOURCE: http://php.net/manual/en/function.array-diff-assoc.php#111675
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('array_diff_assoc_recursive')) {
	function array_diff_assoc_recursive($array1, $array2) {
		$array1		= (array) $array1;
		$array2		= (array) $array2;
		$difference	= [];
		foreach($array1 as $key => $value) {
			if (pudl_array($value)) {
				if(!array_key_exists($key, $array2)  ||  !pudl_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$new_diff = array_diff_assoc_recursive(
						(array) $value,
						(array) $array2[$key]
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
