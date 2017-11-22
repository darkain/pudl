<?php


function pudl_array($item) {
	//	if ($item instanceof pudlCollection) return false;
	//TODO:	this "if" statement is used in another variation of this function.
	//		research to see if it should be included here, too!
	return is_array($item) || ($item instanceof ArrayAccess);
}



class pudlException extends Exception {}



interface pudlHelper {}



interface pudlId {
	public function pudlId();
}



interface pudlValue {
	public function pudlValue($db, $quote=true);
}




////////////////////////////////////////////////////////////////////////////////
//Used by both pudlResult and pudlObject
//http://php.net/manual/en/class.countable.php
//http://php.net/manual/en/class.seekableiterator.php
////////////////////////////////////////////////////////////////////////////////
interface	pudlData
	extends
			Countable,
			SeekableIterator {

	//Countable
	public function count();

	//SeekableIterator
	public function seek($position);

	//Iterator
	public function current();
	public function key();
	public function next();
	public function rewind();
	public function valid();

	//pudlData
	public function fields();
	public function getField($column);
	public function listFields();
	public function row($type=PUDL_ARRAY);
	public function free();

	//JSON
	public function json();
}




////////////////////////////////////////////////////////////////////////////////
// FIX FOR PUDL OBJECT RECURSIVE ARRAYS
// SOURCE: http://php.net/manual/en/function.array-diff-assoc.php#111675
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('array_diff_assoc_recursive')) {
	function array_diff_assoc_recursive($array1, $array2) {
		$difference = [];
		foreach($array1 as $key => $value) {
			if(pudl_array($value)) {
				if(!isset($array2[$key])  ||  !pudl_array($array2[$key])) {
					$difference[$key] = $value;
				} else {
					$new_diff = array_diff_assoc_recursive($value, $array2[$key]);
					if(!empty($new_diff)) $difference[$key] = $new_diff;
				}
			} else if(!array_key_exists($key,$array2)  ||  $array2[$key] !== $value) {
				$difference[$key] = $value;
			}
		}
		return $difference;
	}
}
