<?php




require_once(is_owner(__DIR__.'/pudlData.php'));




abstract class	pudlResult
	implements	pudlData {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR. PASS IN REFERENCE TO PUDL AS $DB, AND THE $RESULT IF AVAIL
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $db, $result=false) {
		$this->result	= $result;
		$this->db		= $db;
		$this->query	= $db->query();
		$this->string	= $db->isString();
	}




	////////////////////////////////////////////////////////////////////////////
	// REQUIRED FOR INHERITANCE
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
	}




	////////////////////////////////////////////////////////////////////////////
	// SHORTCUT METHOD FOR ACCESSING CURRENT ROW DATA
	////////////////////////////////////////////////////////////////////////////
	public function __invoke() {
		return $this->row();
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE
	// http://php.net/manual/en/countable.count.php
	////////////////////////////////////////////////////////////////////////////
	abstract public function count();




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR
	// http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	abstract public function seek($row);




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR
	// http://php.net/manual/en/iterator.current.php
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		if ($this->row === false) $this();
		return $this->data;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR
	// http://php.net/manual/en/iterator.key.php
	////////////////////////////////////////////////////////////////////////////
	public function key() {
		return ($this->row === false) ? 0 : $this->row;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR
	// http://php.net/manual/en/iterator.next.php
	////////////////////////////////////////////////////////////////////////////
	public function next() {
		return $this();
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR
	// http://php.net/manual/en/iterator.rewind.php
	////////////////////////////////////////////////////////////////////////////
	public function rewind() {
		$this->seek(0);
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR
	// http://php.net/manual/en/iterator.valid.php
	////////////////////////////////////////////////////////////////////////////
	public function valid() {
		if ($this->row === false) $this();
		return pudl_array($this->data);
	}




	////////////////////////////////////////////////////////////////////////////
	//pudlData
	////////////////////////////////////////////////////////////////////////////
	abstract public function row();
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



	public function rows() {
		if (!$this->result) return false;
		$rows = [];
		while ($data = $this->row()) {
			$rows[] = $data;
		}
		return $rows;
	}



	public function complete() {
		$rows = $this->rows();
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
				if (!array_key_exists($key, $node)) $node[$key] = [];
				if (!pudl_array($node[$key])) $node[$key] = [$node[$key]];
				$node = &$node[$key];
			}

			if (!array_key_exists($key, $node)) {
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



	public function query() {
		return $this->query;
	}



	public function result() {
		return $this->result;
	}



	public function error() {
		return ($this->result === false)  ||  ($this->result === NULL);
	}




	/** @var pudl */
	protected $db;

	/** @var mixed */
	protected $result;

	/** @var string */
	protected $query;

	/** @var bool */
	protected $string;

	/** @var array|false */
	protected $fields	= false;

	/** @var int|false */
	protected $row		= false;

	/** @var array|false */
	protected $data		= false;
}
