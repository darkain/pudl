<?php


class		pudlSqlSrvResult
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
	// http://php.net/manual/en/function.sqlsrv-free-stmt.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		if (!$this->result) return false;
		$this->result = false;
		return @sqlsrv_free_stmt($this->result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/function.sqlsrv-fetch-array.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		$this->seek($row);
		$data = @sqlsrv_fetch_array($this->result, SQLSRV_FETCH_NUMERIC);
		return isset($data[$column]) ? $data[$column] : false;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	// http://php.net/manual/en/function.sqlsrv-num-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function _count() {
		if (!$this->result) return 0;
		$rows = @sqlsrv_num_rows($this->result);
		return (!empty($rows)) ? $rows : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/function.sqlsrv-num-fields.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		if (!$this->result) return 0;
		$fields = @sqlsrv_num_fields($this->result);
		return (!empty($rows)) ? $rows : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	// http://php.net/manual/en/function.sqlsrv-get-field.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		if (!$this->result) return false;
		$field = @sqlsrv_get_field($this->result, $column);
		return (!empty($field)) ? $field : false;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	// http://php.net/manual/en/function.sqlsrv-fetch.php
	////////////////////////////////////////////////////////////////////////////
	public function _seek($row) {
		if (!$this->result) return;
		@sqlsrv_fetch($this->result, SQLSRV_SCROLL_ABSOLUTE, $row);
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/function.sqlsrv-fetch-array.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!$this->result) return false;

		$this->data = @sqlsrv_fetch_array($this->result, SQLSRV_FETCH_ASSOC);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}

}
