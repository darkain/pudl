<?php


require_once('pudlResult.php');


class pudlOdbcResult extends pudlResult {

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
		if ($this->fieldCount !== false) return $this->fieldCount;
		if ($this->result) {
			$this->fieldCount = @odbc_num_fields($this->result);
			if ($this->fieldCount < 0) $this->fieldCount = false;
		}
		return ($fields !== false) ? $fields : 0;
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

		$fetch					= $this->row
								? @odbc_fetch_row($this->result, $this->row)
								: @odbc_fetch_row($this->result);

		$this->row				= false;
		$fields					= $this->fields();
		$data					= [];

		if ($fetch === false) return false;

		for ($i=1; $i<=$fields; $i++) {
			$item				= @odbc_result($this->result, $i);

			if ($type & PUDL_ARRAY) {
				$name			= @odbc_field_name($this->result, $i);
				$data[$name]	= $item;
			}

			if ($type & PUDL_NUMBER) {
				$data[$i]		= $item;
			}
		}

		return $data;
	}


	private $row				= false;
	private $fieldCount			= false;

}
