<?php


require_once('pudlResult.php');


class pudlMySqliResult extends pudlResult {
	public function __construct($result, $query) {
		parent::__construct($result, $query);
	}
	
	
	public function __destruct() {
		$this->free();
	}


	public function free() {
		$return = false;
		if (is_array($this->result)) $return = @$this->result->free();
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		$return = false;
		if (is_array($this->result)) {
			@$this->result->data_seek($row);
			$data = $this->row('NUMBER');
			if (isset($data[$column])) $return = $data[$column];
		}
		return $return;
	}


	public function count() {
		$rows = false;
		if (is_array($this->result)) $rows = $this->result->num_rows;
		return ($rows !== false) ? $rows : 0;
	}

	
	public function fields() {
		$fields = false;
		if (is_array($this->result)) $fields = $this->result->field_count;
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if (is_array($this->result)) {
			@$this->result->field_seek($column);
			$field = @$this->result->fetch_field();
		}
		return ($field !== false) ? $field : 0;
	}

	
	public function row($type='ARRAY') {
		if (!$this->result) return false;
		$data = false;
		switch ($type) {
			case 'ARRAY':	$data = @$this->result->fetch_array(MYSQLI_ASSOC);	break;
			case 'NUMBER':	$data = @$this->result->fetch_array(MYSQLI_NUM);	break;
			case 'ALL':		$data = @$this->result->fetch_array(MYSQLI_BOTH);	break;
			default:		$data = @$this->result->fetch_array();
		}
		return is_array($data) ? $data : false;
	}
	
}
