<?php

class pudlCacheResult extends pudlResult {

	public function __construct($data, $db) {
		parent::__construct(count($data), $db);
		$this->rows	= $data;
		$this->cur	= 0;
	}



	public function free() {
		$this->rows = [];
		$this->result = false;
	}


	public function cell($row=0, $column=0) {
		if (!isset($this->rows[$row])) return false;

		$row = &$this->rows[$row];
		if (count($row) < $column) return false;

		//Thanks to PHP's requirement of reset() needing
		//the array to be passed by reference, we have
		//to assign said array to a temporary variable
		$slice = array_slice($row, $column, 1);
		return reset($slice);
	}


	public function count() { return count($this->rows()); }


	public function fields() {
		if (empty($this->rows)) return false;
		return array_keys($this->rows[0]);
	}


	public function getField($column) {
		$fields = $this->fields();
		if (empty($fields[$column])) return false;
		return $fields[$column];
	}


	public function seek($row) { $this->cur = $row; }


	public function row($type=PUDL_ARRAY) {
		if (isset($this->rows[$this->cur])) {
			return $this->rows[$this->cur++];
		}
		return false;
	}



	public function rows($type=PUDL_ARRAY) {
		return $this->rows;
	}



	protected $rows;
	protected $cur;
}
