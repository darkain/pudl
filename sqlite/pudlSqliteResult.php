<?php


class		pudlSqliteResult
	extends	pudlResult {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl, $result=NULL) {
		parent::__construct($pudl, $result);

		$this->row = 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FREE RESOURCES
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		parent::__destruct();
		$this->free();
	}




	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	// http://php.net/manual/en/sqlite3result.finalize.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		if (is_object($this->result)) {
			$this->result->finalize();
			$this->result = NULL;
			return true;
		}
		return false;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/sqlite3result.fetcharray.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!is_object($this->result)) return false;

		if ($row > $this->row) {
			$this->row = 0;
			$this->result->reset();
		}

		for ($i=$this->row; $i<=$row; $i++) {
			$data = $this->row();
		}

		if (pudl_array($data)) {
			$data = array_values($data);
			if (array_key_exists($column, $data)) {
				return $data[$column];
			}
		}

		return false;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	////////////////////////////////////////////////////////////////////////////
	public function _count() {
		return 0;
		//TODO: IMPLEMENT THIS (but it'll be hacky, since Sqlite doesn't support it!)
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/sqlite3result.numcolumns.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		$fields = false;
		if (is_object($this->result)) $fields = $this->result->numColumns();
		return ($fields !== false) ? $fields : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	// http://php.net/manual/en/sqlite3result.columnname.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		$field = false;
		if (is_object($this->result)) $field = $this->result->columnName($column);
		return ($field !== false) ? $field : false;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	public function _seek($row) {
		//TODO: IMPLEMENT THIS!
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/sqlite3result.fetcharray.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!is_object($this->result)) return false;

		$this->data = $this->result->fetchArray(SQLITE3_ASSOC);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}


}
