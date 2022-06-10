<?php




////////////////////////////////////////////////////////////////////////////////
// COMPATIBILITY SHIM FOR LEGACY AND MODERN PHP
////////////////////////////////////////////////////////////////////////////////
trait pudlData_shim {




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN COUNTABLE INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function count() {
		return $this->_count();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN SEEKABLEITERATOR INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function seek($position) {
		$this->_seek($position);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN ITERATOR INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		return $this->_current();
	}


	public function key() {
		return $this->_key();
	}


	public function next() {
		$this->_next();
	}


	public function rewind() {
		$this->_rewind();
	}


	public function valid() {
		return $this->_valid();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHP'S BUILT IN JSONSERIALIZABLE INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function jsonSerialize() {
		return $this->_jsonSerialize();
	}


}
