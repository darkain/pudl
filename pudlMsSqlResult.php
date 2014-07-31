<?php


require_once('pudlResult.php');


class pudlMsSqlResult extends pudlResult {
	public function __construct($result, $query) {
		parent::__construct($result, $query);
	}
	
	
	public function __destruct() {
		$this->free();
	}


	public function free() {
		$return = false;
		if ($this->result) $return = @mssql_free_result($this->result);
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		$return = false;
		if ($this->result) $return = @mssql_result($this->result, $row, $column);
		return $return;
	}


	public function count() {
		$rows = false;
		if ($this->result) $rows = @mssql_num_rows($this->result);
		return ($rows !== false) ? $rows : 0;
	}

	
	public function fields() {
		$fields = false;
		if ($this->result) $fields = @mssql_num_fields($this->result);
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if ($this->result) $field = @mssql_fetch_field($this->result, $column);
		return ($field !== false) ? $field : 0;
	}

	
	public function row($type=PUDL_ARRAY) {
		if (!$this->result) return false;
		$data = false;
		switch ($type) {
			case PUDL_ARRAY:	$data = @mssql_fetch_array($this->result, MSSQL_ASSOC);		break;
			case PUDL_NUMBER:	$data = @mssql_fetch_array($this->result, MSSQL_NUM);		break;
			case PUDL_BOTH:		$data = @mssql_fetch_array($this->result, MSSQL_BOTH);		break;
			default:			$data = @mssql_fetch_array($this->result);
		}
		return $data;
	}
	
}
