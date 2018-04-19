<?php


//NOTE: THIS IS ONLY A PARTIAL IMPLEMENTATION AND WILL BE FINISHED LATER


class pudlArrayResult extends pudlResult {

	public function __construct(pudl $db, $array) {
		parent::__construct($db);

		$this->array = $array;
	}



	public function free() {
		$this->array	= false;
		$this->pos		= 0;
	}


	public function cell($row=0, $column=0) { return false; }


	public function count() {
		return is_array($this->array) ? count($this->array) : 0;
	}


	public function fields() {
		if (!is_array($this->array)) return false;
		$array = current($this->array);
		if (!is_array($array)) return false;
		return count($array);
	}


	public function listFields() {
		if (!is_array($this->array)) return false;
		$keys = array_keys(current($this->array));
		if ($keys === false) return false;

		$out = [];
		foreach ($keys as $item) {
			$out[] = (object)['name' => $item];
		}

		return $out;
	}


	public function getField($column) {
		$fields = $this->fields();
		if ($fields === false) return false;
		if (!isset($fields[$column])) return false;
		return $fields[$column];
	}


	public function seek($row) {}



	public function row($type=PUDL_ARRAY) {
		if (!is_array($this->array)) return false;

		if ($this->pos++ === 0) {
			$data = current($this->array);
		} else {
			$data = next($this->array);
		}

		if ($data === false) return false;

		switch ($type) {
			case PUDL_INDEX:	break;
			case PUDL_ARRAY:	break;
			case PUDL_NUMBER:	return array_values($data);
			case PUDL_BOTH:		return $data + array_values($data);
		}

		return $data;
	}



	public function error()		{ return 0; }

	public function errormsg()	{ return ''; }


	private $array	= false;
	private $pos	= 0;
}
