<?php


//NOTE: THIS IS ONLY A PARTIAL IMPLEMENTATION AND WILL BE FINISHED LATER


class pudlArrayResult extends pudlResult {

	public function __construct(pudl $db, $array) {
		parent::__construct($db);

		if ($array instanceof pudlObject) {
			$array = $array->raw();
		} else if (!is_array($array)) {
			$array = (array) $array;
		}

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
		return is_array($array) ? count($array) : false;
	}


	public function listFields() {
		if (!is_array($this->array)) return false;
		$keys = array_keys(current($this->array));
		if (!is_array($keys)) return false;

		$out = [];
		foreach ($keys as $item) {
			$out[] = (object)['name' => $item];
		}

		return $out;
	}


	public function getField($column) {
		$fields = $this->listFields();
		if (!is_array($fields)) return false;
		if (!array_key_exists($column, $fields)) return false;
		return $fields[$column];
	}


	public function seek($row) {
		if (!is_array($this->array)) return;
		if ($row < 0  ||  $row >= count($this->array)) return;
		$this->pos = $row;
	}



	public function row() {
		if (!is_array($this->array)) return false;

		return ($this->pos++ === 0)
			? current($this->array)
			: next($this->array);
	}



	public function error()		{ return 0; }

	public function errormsg()	{ return ''; }


	/** @var array|false */		private $array	= false;
	/** @var int */				private $pos	= 0;
}
