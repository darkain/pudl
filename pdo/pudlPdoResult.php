<?php


class pudlPdoResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
		$this->free();
	}



	public function free() {
		$this->result = false;
		return true;
	}



	public function cell($row=0, $column=0) {
		if (!is_object($this->result)) return false;

		$data = $this->result->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_ABS, $row);

		return (pudl_array($data)  &&  array_key_exists($column, $data))
			? $data[$column] : false;
	}



	public function count() {
		if (!is_object($this->result)) return 0;
		return $this->result->rowCount();
	}



	public function fields() {
		if (!is_object($this->result)) return 0;
		return $this->result->columnCount();
	}



	public function getField($column) {
		if (!is_object($this->result)) return [];
		return $this->result->getColumnMeta($column);
	}



	public function seek($row) {
		if (!is_object($this->result)) return;
		$this->result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row);
		if (!$row) $this->seekzero = true;
	}


/////////////////////////////
	public function row() {
		if (!is_object($this->result)) return false;

		$seek = $this->seekzero ? 0 : 1;
		$this->seekzero = false;

		$this->data = $this->result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_REL, $seek);

		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}
		return $this->data;
	}



	public function errno() {
		if (!is_object($this->result)) return 0;
		return $this->result->errorCode();
	}



	public function error() {
		if (!is_object($this->result)) return '';
		return $this->result->errorInfo();
	}



	private $seekzero = false;
}
