<?php




////////////////////////////////////////////////////////////////////////////////
// COMPATIBILITY SHIM FOR LEGACY AND MODERN PHP
////////////////////////////////////////////////////////////////////////////////
trait pudlData_shim {




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN COUNTABLE INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function count() : int {
		return $this->_count();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN SEEKABLEITERATOR INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function seek($position) : void {
		$this->_seek($position);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHPS BUILT IN ITERATOR INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function current() : mixed {
		return $this->_current();
	}


	public function key() : mixed {
		return $this->_key();
	}


	public function next() : void {
		$this->_next();
	}


	public function rewind() : void {
		$this->_rewind();
	}


	public function valid() : bool {
		return $this->_valid();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULLED FROM PHP'S BUILT IN JSONSERIALIZABLE INTERFACE
	////////////////////////////////////////////////////////////////////////////
	public function jsonSerialize() : mixed {
		return $this->_jsonSerialize();
	}


}
