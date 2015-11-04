<?php


require_once('pudlResult.php');


class pudlOdbcResult extends pudlResult {

	public function __construct($result, $db) {
		parent::__construct($result, $db);
	}


	public function __destruct() {
		parent::__destruct();
		$this->free();
	}



	public function free() {
		$return = false;
		if ($this->result) $return = @odbc_free_result($this->result);
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		@odbc_fetch_row($this->result, $this->row);
		return @odbc_result($this->result, $column);
	}


	public function count() {
		$rows = false;
		if ($this->result) $rows = @odbc_num_rows($this->result);
		return ($rows !== false  &&  $rows > 0) ? $rows : 0;
	}


	public function fields() {
		$fields = false;
		if ($this->result) $fields = @odbc_num_fields($this->result);
		return ($fields !== false  &&  $fields > 0) ? $fields : 0;
	}


	public function getField($column) {
		//TODO: implement this!!
		return array();
	}


	public function seek($row) {
		if (!$this->result) return false;
		$this->row = $row;
		return true;
	}


	public function row($type=PUDL_ARRAY) {
		if (!$this->result) return false;

		if ($this->row) {
			$fetch = @odbc_fetch_row($this->result, $this->row);
			$this->row = false;
		} else {
			$fetch = @odbc_fetch_row($this->result);
		}
		if ($fetch === false) return false;

		$fields = $this->fields();
		$data = array();

		//TODO: make trim() OPTIONAL!!! :-O
		if ($type === PUDL_ARRAY) {
			for ($i=1; $i<=$fields; $i++) {
				$data[odbc_field_name($this->result, $i)] = trim(@odbc_result($this->result, $i));
			}
		} else if ($type === PUDL_NUMBER) {
			for ($i=1; $i<=$fields; $i++) {
				$data[$i] = trim(@odbc_result($this->result, $i));
			}
		} else {
			for ($i=1; $i<=$fields; $i++) {
				$data[$i] = odbc_result($this->result, $i);
				$data[odbc_field_name($this->result, $i)] = trim(@odbc_result($this->result, $i));
			}
		}

		return $data;
	}



	private $row = false;
}
