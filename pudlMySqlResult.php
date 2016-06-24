<?php


require_once('pudlResult.php');


class pudlMySqlResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
		$this->free();
	}


	public function free() {
		$return = false;
		if ($this->result) $return = @mysql_free_result($this->result);
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		$return = false;
		if ($this->result) $return = @mysql_result($this->result, $row, $column);
		return $return;
	}


	public function count() {
		$rows = false;
		if ($this->result) $rows = @mysql_num_rows($this->result);
		return ($rows !== false) ? $rows : 0;
	}


	public function fields() {
		$fields = false;
		if ($this->result) $fields = @mysql_num_fields($this->result);
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if ($this->result) $field = @mysql_fetch_field($this->result, $column);
		return ($field !== false) ? $field : 0;
	}


	public function seek($row) {
		if ($this->result) return @mysql_data_seek($this->result, $row);
		return false;
	}


	public function row($type=PUDL_ARRAY) {
		if (!$this->result) return false;
		$data = false;
		switch ($type) {
			case PUDL_INDEX:	//fall through
			case PUDL_ARRAY:	$data = @mysql_fetch_array($this->result, MYSQL_ASSOC);		break;
			case PUDL_NUMBER:	$data = @mysql_fetch_array($this->result, MYSQL_NUM);		break;
			case PUDL_BOTH:		$data = @mysql_fetch_array($this->result, MYSQL_BOTH);		break;
			default:			$data = @mysql_fetch_array($this->result);
		}
		return $data;
	}

}
