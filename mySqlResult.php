<?php

class mySqlResult {
	public function __construct($result) {
		$this->result = $result;
		$this->fields = false;
	}



	public function __destruct() {
		$this->free();
	}



	public function free() {
		$return = false;
		if ($this->result) $return = @mysql_free_result($this->result);
		$this->result = false;
		return $return;
	}



	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		return @mysql_result($this->result, $row, $column);
	}



	public function count() {
		$rows = false;
		if ($this->result) $rows = @mysql_num_rows($this->result);
		return ($rows !== false) ? $rows : 0;
	}
	
	

	public function hasRows() {
		return ($this->count() > 0);
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



	public function listFields() {
		if (!$this->result) return false;

		if ($this->fields === false) {
			$this->fields = array();
			$total = $this->fields($this->result);
			for ($i=0; $i<$total; $i++) {
				$this->fields[] = $this->getField($i);
			} 
		}

		return $this->fields;
	}



	public function row($type='ARRAY') {
		if (!$this->result) return false;
		$data = false;
		switch ($type) {
			case 'ARRAY':	$data = @mysql_fetch_array($this->result, MYSQL_ASSOC);		break;
			case 'NUMBER':	$data = @mysql_fetch_array($this->result, MYSQL_NUM);		break;
			case 'ALL':		$data = @mysql_fetch_array($this->result, MYSQL_BOTH);		break;
			default:		$data = @mysql_fetch_array($this->result);
		}
		return $data;
	}
	


	public function rows($type='ARRAY') {
		if (!$this->result) return false;
		$return = array();
		while ($data = $this->row($type)) { $return[] = $data; }
		return $return;
	}



	public function get() {
		$data = $this->row('NUMBER');
		if (!$data) return false;

		$fields = $this->fields();
		for ($i=0; $i<count($fields); $i++) {
			$name = $fields[$i]->name;
			if (!isset($data[$name])  ||  is_null($data[$name])) {
				$data[$name] = &$data[$i];
			}
		} 

		return $data;
	}
	
	

	private $fields;
	private $result;
}
