<?php


class		pudlOdbcResult
	extends	pudlResult {




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FREE RESOURCES
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		parent::__destruct();
		$this->free();
		$this->row = 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	// http://php.net/manual/en/function.odbc-free-result.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		$return = false;
		if ($this->result) $return = @odbc_free_result($this->result);
		$this->result = false;
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/function.odbc-fetch-row.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		@odbc_fetch_row($this->result, $this->row);
		return @odbc_result($this->result, $column);
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	// http://php.net/manual/en/function.odbc-num-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function _count() {
		$rows = false;
		if ($this->result) $rows = @odbc_num_rows($this->result);
		return ($rows !== false  &&  $rows > 0) ? $rows : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/function.odbc-num-fields.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		if ($this->fieldCount !== false) return $this->fieldCount;
		if ($this->result) {
			$this->fieldCount = @odbc_num_fields($this->result);
			if ($this->fieldCount < 0) $this->fieldCount = false;
		}
		return ($this->fieldCount !== false) ? $this->fieldCount : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		//TODO: implement this!!
		return array();
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	public function _seek($row) {
		if ($this->result) $this->row = (int) $row;
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/function.odbc-fetch-row.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!$this->result) return false;

		$fetch						= @odbc_fetch_row($this->result, $this->row);
		$fields						= $this->fields();
		$this->data					= false;

		if ($fetch === false) return false;

		$this->row++;
		$this->data					= [];

		for ($i=1; $i<=$fields; $i++) {
			$name				= @odbc_field_name(	$this->result, $i);
			$this->data[$name]	= @odbc_result(		$this->result, $i);
		}

		return $this->data;
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var int|false */	private $fieldCount = false;

}
