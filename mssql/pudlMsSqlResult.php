<?php


class pudlMsSqlResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
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


	public function seek($row) {
		if ($this->result) @mssql_data_seek($this->result, $row);
	}


	public function row() {
		if (!$this->result) return false;

		$this->data = @mssql_fetch_assoc($this->result);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}

}
