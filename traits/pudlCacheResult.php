<?php


class pudlCacheResult extends pudlResult {


	public function __construct(pudl $db, $data, $key) {
		parent::__construct($db, count($data));
		$this->rows	= $data;
		$this->key	= $key;
		$this->row	= 0;
	}



	public function purge() {
		$this->db->purge($this->key);
	}



	public function free() {
		$this->rows = [];
		$this->result = false;
	}


	public function cell($row=0, $column=0) {
		$this->seek($row);
		if (!isset($this->rows[$this->row])) return ;

		$this->data = &$this->rows[$row];
		if (count($this->data) < $column) return false;

		//Thanks to PHP's requirement of reset() needing
		//the array to be passed by reference, we have
		//to assign said array to a temporary variable
		$slice = array_slice($this->data, $column, 1);
		return reset($slice);
	}


	public function count() { return count($this->rows); }


	public function fields() {
		if (empty($this->rows)) return false;
		return array_keys($this->rows[0]);
	}


	public function getField($column) {
		$fields = $this->fields();
		if (empty($fields[$column])) return false;
		return $fields[$column];
	}


	public function seek($row) {
		$this->row = (int) $row;
	}


	public function row() {
		if (isset($this->rows[$this->row])) {
			return $this->data = $this->rows[$this->row++];
		}
		return $this->data = false;
	}



	public function rows() {
		return $this->rows;
	}



	protected $rows;
	protected $key;
}
