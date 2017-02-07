<?php

abstract class pudlResult implements Countable, SeekableIterator {

	public function __construct($result, $db) {
		$this->result	= $result;
		$this->db		= $db;
		$this->query	= $db->query();
		$this->string	= $db->isString();
		$this->fields	= false;
	}

	public function __destruct() {}


	public function __invoke() { return $this->row(); }



	abstract public function free();

	abstract public function cell($row=0, $column=0);

	abstract public function fields();

	abstract public function getField($column);

//	Provided by: Countable
//	abstract public function count();

//	Provided by: SeekableIterator
//	abstract public function seek($row);


	public function rewind() { $this->seek(0); }

	public function current() {
		if ($this->row === false) $this();
		return $this->data;
	}

	public function key() {
		return ($this->row === false) ? 0 : $this->row;
	}

	public function next() { $this(); }

	public function valid() {
		if ($this->row === false) $this();
		return is_array($this->data);
	}


	public function isString() { return $this->string; }


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
		$rows = [];
		while ($data = $this->row($type)) {
			if ($type === PUDL_INDEX) {
				$rows[ reset($data) ] = $data;
			} else {
				$rows[] = $data;
			}
		}
		return $rows;
	}



	public function complete($type=PUDL_ARRAY) {
		$rows = $this->rows($type);
		$this->free();
		return $rows;
	}



	public function completeCell($row=0, $column=0) {
		$cell = $this->cell($row, $column);
		$this->free();
		return $cell;
	}



	public function collection() {
		$return = [];
		while ($data = $this->row()) {
			$return[reset($data)] = end($data);
		}
		$this->free();
		return $return;
	}



	public function json() {
		return pudl::jsonEncode( $this->rows() );
	}


	public function completeJson() {
		$json = $this->json();
		$this->free();
		return $json;
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
	protected $string;
	protected $row		= false;
	protected $data		= false;
}
