<?php


trait pudlRequire {


	////////////////////////////////////////////////////////////////////////////
	// VALIDATE THAT THE GIVEN OBJECT HAS THE REQUIRED PROPERTY
	////////////////////////////////////////////////////////////////////////////
	protected function _requireProperty($object, $property) {
		if (!is_object($object)) $this->_invalidType($object, 'object');
		if (property_exists($object, $property)) return;
		throw new pudlException(
			$this,
			'Undefined property: ' . get_class($object) . '::' . $property
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// VALIDATE THAT THE GIVEN ARRAY HAS THE REQUIRED KEY INDEX
	////////////////////////////////////////////////////////////////////////////
	protected function _requireKey($array, $key) {
		if (!is_array($array)) $this->_invalidType($array, 'array');
		if (array_key_exists($key, $array)) return;
		throw new pudlException($this, 'Undefined key: ' . $key);
	}




	////////////////////////////////////////////////////////////////////////////
	// VALIDATE THAT THE VALUE IS [TRUE] (WITH IMPLICIT DATA TYPE CONVERSION)
	////////////////////////////////////////////////////////////////////////////
	protected function _requireTrue($value, $error) {
		if ($value) return;
		throw new pudlValueException($this, $error);
	}




	////////////////////////////////////////////////////////////////////////////
	// THROW AN EXCEPTION FOR AN INVALID DATA TYPE
	////////////////////////////////////////////////////////////////////////////
	protected function _invalidType($item, $thing=false) {
		switch (true) {
			case ($thing !== false)  &&  is_object($item):
				$error = 'Invalid object type for ' . $thing . ': ' . get_class($item);
			break;

			case ($thing !== false):
				$error = 'Invalid data type for ' . $thing . ': ' . gettype($item);
			break;

			case is_object($item):
				$error = 'Invalid object type: ' . get_class($item);
			break;

			default:
				$error = 'Invalid data type: ' . gettype($item);
			break;
		}

		throw new pudlTypeException($this, $error);
	}


}
