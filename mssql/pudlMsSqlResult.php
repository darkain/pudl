<?php


class		pudlMsSqlResult
	extends	pudlResult {




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FREE RESOURCES
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		parent::__destruct();
		$this->free();
	}




	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	// http://php.net/manual/en/function.mssql-free-result.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		$return = false;
		if ($this->result) $return = @mssql_free_result($this->result);
		$this->result = false;
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/function.mssql-result.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		$return = false;
		if ($this->result) $return = @mssql_result($this->result, $row, $column);
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	// http://php.net/manual/en/function.mssql-num-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function _count() {
		$rows = false;
		if ($this->result) $rows = @mssql_num_rows($this->result);
		return ($rows !== false) ? $rows : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/function.mssql-num-fields.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		$fields = false;
		if ($this->result) $fields = @mssql_num_fields($this->result);
		return ($fields !== false) ? $fields : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	// http://php.net/manual/en/function.mssql-fetch-field.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		$field = false;
		if ($this->result) $field = @mssql_fetch_field($this->result, $column);
		return ($field !== false) ? $field : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	// http://php.net/manual/en/function.mssql-data-seek.php
	////////////////////////////////////////////////////////////////////////////
	public function _seek($row) {
		if ($this->result) @mssql_data_seek($this->result, $row);
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/function.mssql-fetch-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!$this->result) return false;

		$this->data = @mssql_fetch_assoc($this->result);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}

}
