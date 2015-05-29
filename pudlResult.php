<?php

abstract class pudlResult {

	public function __construct($result, $db) {
		$this->result	= $result;
		$this->db		= $db;
		$this->query	= $db->query();
		$this->fields	= false;
	}



	abstract public function free();

	abstract public function cell($row=0, $column=0);

	abstract public function count();

	abstract public function fields();

	abstract public function getField($column);

	abstract public function seek($row);


	public function hasRows() {
		return ($this->count() > 0);
	}


	public function listFields() {
		if (!$this->result) return false;

		if ($this->fields === false) {
			$this->fields = array();
			$total = $this->fields($this->result);
			for ($i=0; $i<$total; $i++) {
				$this->fields[] = $this->getField($i);
			}
		}

		return $this->fields;
	}



	abstract public function row($type=PUDL_ARRAY);



	public function rows($type=PUDL_ARRAY) {
		if (!$this->result) return false;
		$return = array();
		while ($data = $this->row($type)) { $return[] = $data; }
		return $return;
	}



	public function get() {
		$data = $this->row(PUDL_NUMBER);
		if (!$data) return false;

		$fields = $this->fields();
		for ($i=0; $i<count($fields); $i++) {
			$name = $fields[$i]->name;
			if (!isset($data[$name])  ||  is_null($data[$name])) {
				$data[$name] = &$data[$i];
			}
		}

		return $data;
	}



	public function query() {
		return $this->query;
	}



	public function result() {
		return $this->result;
	}



	public function error() {
		return $this->result === false;
	}


	protected $db;
	protected $fields;
	protected $result;
	protected $query;
}
