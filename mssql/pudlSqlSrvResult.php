<?php


class pudlSqlSrvResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
		$this->free();
	}


	public function free() {
		if (!$this->result) return false;
		$this->result = false;
		return @sqlsrv_free_stmt($this->result);
	}


	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		$this->seek($row);
		$data = @sqlsrv_fetch_array($this->result, SQLSRV_FETCH_NUMERIC);
		return isset($data[$column]) ? $data[$column] : false;
	}


	public function count() {
		if (!$this->result) return 0;
		$rows = @sqlsrv_num_rows($this->result);
		return (!empty($rows)) ? $rows : 0;
	}


	public function fields() {
		if (!$this->result) return 0;
		$fields = @sqlsrv_num_fields($this->result);
		return (!empty($rows)) ? $rows : 0;
	}


	public function getField($column) {
		if (!$this->result) return false;
		$field = @sqlsrv_get_field($this->result, $column);
		return (!empty($field)) ? $field : false;
	}


	public function seek($row) {
		if (!$this->result) return;
		@sqlsrv_fetch($this->result, SQLSRV_SCROLL_ABSOLUTE, $row);
	}


	public function row() {
		if (!$this->result) return false;

		$this->data = @sqlsrv_fetch_array($this->result, SQLSRV_FETCH_ASSOC);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}

		return $this->data;
	}

}
