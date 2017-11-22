<?php


require_once(__DIR__.'/pudlInterfaces.php');


abstract class	pudlResult
	implements	pudlData {




	public function __construct($result, $db) {
		$this->result	= $result;
		$this->db		= $db;
		$this->query	= $db->query();
		$this->string	= $db->isString();
	}



	public function __destruct() {
	}



	public function __invoke() {
		return $this->row();
	}




	////////////////////////////////////////////////////////////////////////////
	//Countable
	////////////////////////////////////////////////////////////////////////////
	abstract public function count();




	////////////////////////////////////////////////////////////////////////////
	//SeekableIterator
	////////////////////////////////////////////////////////////////////////////
	abstract public function seek($row);




	////////////////////////////////////////////////////////////////////////////
	//Iterator
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		if ($this->row === false) $this();
		return $this->data;
	}


	public function key() {
		return ($this->row === false) ? 0 : $this->row;
	}


	public function next() {
		return $this();
	}


	public function rewind() {
		$this->seek(0);
	}


	public function valid() {
		if ($this->row === false) $this();
		return pudl_array($this->data);
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData
	////////////////////////////////////////////////////////////////////////////
	abstract public function row($type=PUDL_ARRAY);
	abstract public function fields();
	abstract public function getField($column);


	public function listFields() {
		if (!$this->result) return false;

		if ($this->fields === false) {
			$this->fields = [];
			$total = $this->fields();
			for ($i=0; $i<$total; $i++) {
				$this->fields[] = $this->getField($i);
			}
		}

		return $this->fields;
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlResult
	////////////////////////////////////////////////////////////////////////////
	abstract public function free();
	abstract public function cell($row=0, $column=0);



	public function isString() {
		return $this->string;
	}



	public function hasRows() {
		return ($this->count() > 0);
	}



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



	public function tree($separator='.') {
		$return = [];

		while ($data = $this->row()) {
			$keys = explode($separator, reset($data));
			$node = &$return;

			foreach ($keys as $count => $key) {
				if ($count === count($keys)-1) break;
				if (!isset($node[$key])) $node[$key] = [];
				if (!pudl_array($node[$key])) $node[$key] = [$node[$key]];
				$node = &$node[$key];
			}

			if (!isset($node[$key])) {
				$node[$key] = end($data);
			} else {
				$node[$key][] = end($data);
			}
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
	protected $result;
	protected $query;
	protected $string;
	protected $fields	= false;
	protected $row		= false;
	protected $data		= false;
}
