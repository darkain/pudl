<?php




////////////////////////////////////////////////////////////////////////////////
// Used by both pudlResult and pudlObject
// http://php.net/manual/en/class.countable.php
// http://php.net/manual/en/class.seekableiterator.php
// http://php.net/manual/en/class.jsonserializable.php
////////////////////////////////////////////////////////////////////////////////
interface	pudlData
	extends	Countable,
			SeekableIterator,
			JsonSerializable {

	//Pulled from PHPs built in Countable interface
	public function count();

	//Pulled from PHPs built in SeekableIterator interface
	public function seek($position);

	//Pulled from PHPs built in Iterator interface
	public function current();
	public function key();
	public function next();
	public function rewind();
	public function valid();

	//Methods added for pudlData
	public function fields();
	public function getField($column);
	public function listFields();
	public function row();
	public function free();

	//Pulled from PHP's built in JsonSerializable interface
	public function jsonSerialize();

	//Method to get JSON text string from this object
	public function json();
}
