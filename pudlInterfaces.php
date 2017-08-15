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
}
