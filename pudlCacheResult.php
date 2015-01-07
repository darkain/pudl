<?php

class pudlCacheResult extends pudlResult {

	public function __construct($data, $query) {
		parent::__construct(count($data), $query);
		$this->rows	= $data;
		$this->cur	= 0;
	}



	public function free() {
		$this->rows = [];
		$this->result = false;
	}


	public function cell($row=0, $column=0) {
		if (!isset($this->rows[$row][$column])) return false;
		return $this->rows[$row][$column];
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
