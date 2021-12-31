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
	#[\ReturnTypeWillChange]
	public function count();



	//Pulled from PHPs built in SeekableIterator interface
	#[\ReturnTypeWillChange]
	public function seek($position);



	//Pulled from PHPs built in Iterator interface
	#[\ReturnTypeWillChange]
	public function current();

	#[\ReturnTypeWillChange]
	public function key();

	#[\ReturnTypeWillChange]
	public function next();

	#[\ReturnTypeWillChange]
	public function rewind();

	#[\ReturnTypeWillChange]
	public function valid();



	//Methods added for pudlData
	#[\ReturnTypeWillChange]
	public function fields();

	#[\ReturnTypeWillChange]
	public function getField($column);

	#[\ReturnTypeWillChange]
	public function listFields();

	#[\ReturnTypeWillChange]
	public function row();

	#[\ReturnTypeWillChange]
	public function free();


	//Pulled from PHP's built in JsonSerializable interface
	#[\ReturnTypeWillChange]
	public function jsonSerialize();



	//Method to get JSON text string from this object
	#[\ReturnTypeWillChange]
	public function json();
}
