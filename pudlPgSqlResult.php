<?php


require_once('pudlResult.php');


class pudlPgSqlResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
		$this->free();
	}



	public function free() {
		$return = false;
		if ($this->result) $return = @pg_free_result($this->result);
		$this->result = false;
		return $return;
	}



	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		$data = @pg_fetch_row($this->result, $this->row);

		return (is_array($data)  &&  array_key_exists($column, $data))
			? $data[$column] : false;
	}



	public function count() {
		$rows = false;
		if ($this->result) $rows = @pg_num_rows($this->result);
		return ($rows !== false  &&  $rows > 0) ? $rows : 0;
	}



	public function fields() {
		$fields = false;
		if ($this->result) $fields = @pg_num_fields($this->result);
		return ($fields !== false  &&  $fields > 0) ? $fields : 0;
	}



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



	public function seek($row) {
		if (!$this->result) return false;
		return @pg_result_seek($row);
	}



	public function row($type=PUDL_ARRAY) {
		if (!$this->result) return false;

		$data = false;
		switch ($type) {
			case PUDL_ARRAY:	$data = pg_fetch_array($this->result, NULL, PGSQL_ASSOC);	break;
			case PUDL_NUMBER:	$data = pg_fetch_array($this->result, NULL, PGSQL_NUM);		break;
			case PUDL_BOTH:		$data = pg_fetch_array($this->result, NULL, PGSQL_BOTH);	break;
			default:			$data = pg_fetch_array($this->result);
		}
		if (!is_array($data)) return false;

		return $data;
	}



	public function error() {
		//if (!$this->result) return $this->db->error();
		return pg_result_error($this->result);
	}

}
