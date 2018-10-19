<?php


class pudlMySqliResult extends pudlResult {


	public function __construct(pudl $pudl, mysqli_result $result=NULL) {
		parent::__construct($pudl, $result);
	}


	public function __destruct() {
		parent::__destruct();
		$this->free();
	}


	public function free() {
		$return = false;
		if ($this->result instanceof mysqli_result) {
			$return = @$this->result->free();
		}
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		if (!($this->result instanceof mysqli_result)) return false;
		$this->seek($row);

		$data = $this->row();
		if (!pudl_array($data)) return false;

		if (is_int($column)) $data = array_values($data);

		return (array_key_exists($column, $data))
			? $data[$column]
			: false;
	}


	public function count() {
		$rows = false;
		if ($this->result instanceof mysqli_result) {
			$rows = $this->result->num_rows;
		}
		return ($rows !== false) ? $rows : 0;
	}


	public function fields() {
		$fields = false;
		if ($this->result instanceof mysqli_result) {
			$fields = $this->result->field_count;
		}
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if ($this->result instanceof mysqli_result) {
			@$this->result->field_seek($column);
			$field = @$this->result->fetch_field();
		}
		return ($field !== false) ? $field : 0;
	}


	public function seek($row) {
		if ($this->result instanceof mysqli_result) {
			@$this->result->data_seek($row);
		}
	}


	public function row() {
		if (!($this->result instanceof mysqli_result)) return false;

		$this->data = @$this->result->fetch_assoc();

		if ($this->data === NULL) return $this->data = false;

		if ($this->first) {
			$this->first = false;
			foreach ($this->data as $key => $val) {
				switch (0) {
					case substr_compare($key, '_json', -5, 5, true):
						$this->json[$key] = $key;
					break;

					case substr_compare($key, 'JSON', 0, 4, true):
						$new = substr($key, 5, -1);
						$pos = strrpos($new, '.');
						if ($pos !== false) $new = substr($new, $pos+1);
						$new = trim($new, " \t\n\r\0\x0B`");
						$this->json[$key] = $new;
					break;
				}
			}
		}

		foreach ($this->json as $key => $new) {
			$this->data[$new] = pudl::jsonDecode($this->data[$key]);
			if ($this->data[$new] === NULL) $this->data[$new] = [];
		} unset($new);

		$this->row = ($this->row === false) ? 0 : $this->row+1;
		return $this->data;
	}




	private $first	= true;
	private $json	= [];

}
