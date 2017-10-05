<?php


class pudlMySqliResult extends pudlResult {

	public function __destruct() {
		parent::__destruct();
		$this->free();
	}


	public function free() {
		$return = false;
		if (is_object($this->result)) $return = @$this->result->free();
		$this->result = false;
		return $return;
	}


	public function cell($row=0, $column=0) {
		if (!is_object($this->result)) return false;
		$this->seek($row);
		$data = $this->row(PUDL_NUMBER);

		return (is_array($data)  &&  array_key_exists($column, $data))
			? $data[$column] : false;
	}


	public function count() {
		$rows = false;
		if (is_object($this->result)) $rows = $this->result->num_rows;
		return ($rows !== false) ? $rows : 0;
	}


	public function fields() {
		$fields = false;
		if (is_object($this->result)) $fields = $this->result->field_count;
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if (is_object($this->result)) {
			@$this->result->field_seek($column);
			$field = @$this->result->fetch_field();
		}
		return ($field !== false) ? $field : 0;
	}


	public function seek($row) {
		if (!is_object($this->result)) return false;
		return @$this->result->data_seek($row);
	}


	public function row($type=PUDL_ARRAY) {
		if (!is_object($this->result)) return false;

		$this->data = false;
		switch ($type) {
			case PUDL_INDEX:	//fall through
			case PUDL_ARRAY:	$this->data = @$this->result->fetch_array(MYSQLI_ASSOC);	break;
			case PUDL_NUMBER:	$this->data = @$this->result->fetch_array(MYSQLI_NUM);		break;
			case PUDL_BOTH:		$this->data = @$this->result->fetch_array(MYSQLI_BOTH);		break;
			default:			$this->data = @$this->result->fetch_array();
		}
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

					case substr_compare($key, 'COLUMN_JSON', 0, 11, true):
						$new = substr($key, 12, -1);
						$pos = strrpos($new, '.');
						if ($pos !== false) $new = substr($new, $pos+1);
						$new = trim($new, " \t\n\r\0\x0B`");
						$this->json[$key] = $new;
					break;

					case substr_compare($key, 'JSON_QUERY', 0, 10, true):
					case substr_compare($key, 'JSON_VALUE', 0, 10, true):
					case substr_compare($key, 'JSON_EXTRACT', 0, 12, true):
						preg_match("/(?:(?:\"(?:\\\\\"|[^\"])+\")|(?:'(?:\\\'|[^'])+'))\s*\)\s*$/is", $key, $m);
						if (empty($m[0])) break;
						$this->json[$key] = trim($m[0], " \t\n\r\0\x0B`'\"()");
					break;
				}
			}
		}

		foreach ($this->json as $key => $new) {
			$this->data[$new] = pudl::jsonDecode($this->data[$key], true);
			if ($this->data[$new] === NULL) $this->data[$new] = [];
		} unset($new);

		$this->row = ($this->row === false) ? 0 : $this->row+1;
		return $this->data;
	}


	private $first	= true;
	private $json	= [];

}
