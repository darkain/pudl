<?php


class		pudlPgSqlResult
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
	// http://php.net/manual/en/function.pg-free-result.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		$return = false;
		if ($this->result) $return = @pg_free_result($this->result);
		$this->result = false;
		return $return;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/function.pg-fetch-row.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		$data = @pg_fetch_row($this->result, $this->row);

		return (pudl_array($data)  &&  array_key_exists($column, $data))
			? $data[$column]
			: false;
	}





	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	// http://php.net/manual/en/function.pg-num-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function _count() {
		$rows = false;
		if ($this->result) $rows = @pg_num_rows($this->result);
		return ($rows !== false  &&  $rows > 0) ? $rows : 0;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/function.pg-num-fields.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		$fields = false;
		if ($this->result) $fields = @pg_num_fields($this->result);
		return ($fields !== false  &&  $fields > 0) ? $fields : 0;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	// http://php.net/manual/en/function.pg-field-is-null.php
	// http://php.net/manual/en/function.pg-field-name.php
	// http://php.net/manual/en/function.pg-field-num.php
	// http://php.net/manual/en/function.pg-field-prtlen.php
	// http://php.net/manual/en/function.pg-field-size.php
	// http://php.net/manual/en/function.pg-field-table.php
	// http://php.net/manual/en/function.pg-field-type.php
	// http://php.net/manual/en/function.pg-field-type-oid.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		return (!$this->result) ? [] : [
			'null'		=> @pg_field_is_null(	$this->result, $column),
			'name'		=> @pg_field_name(		$this->result, $column),
			'number'	=> @pg_field_num(		$this->result, $column),
			'printed'	=> @pg_field_prtlen(	$this->result, $column),
			'size'		=> @pg_field_size(		$this->result, $column),
			'table'		=> @pg_field_table(		$this->result, $column),
			'type'		=> @pg_field_type(		$this->result, $column),
			'oid'		=> @pg_field_type_oid(	$this->result, $column),
		];
	}





	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	// http://php.net/manual/en/function.pg-result-seek.php
	////////////////////////////////////////////////////////////////////////////
	public function _seek($row) {
		if ($this->result) @pg_result_seek($this->result, $row);
	}





	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/function.pg-fetch-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!$this->result) return false;

		$this->data = pg_fetch_assoc($this->result);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR CODE FOR THIS RESULT
	// http://php.net/manual/en/function.pg-result-error.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		return (pg_result_error($this->result) !== '');
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR MESSAGE FOR THIS RESULT
	// http://php.net/manual/en/function.pg-result-error.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		return pg_result_error($this->result);
	}

}
