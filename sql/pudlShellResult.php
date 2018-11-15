<?php


class		pudlShellResult
	extends	pudlResult {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl, $result) {
		parent::__construct($pudl, $result);

		$this->row			= 0;
		$this->json			= $pudl->jsonDecode($result);

		if (!is_array($this->json)) {
			$this->result	= false;
			$this->error	= json_last_error();
			$this->ermsg	= json_last_error_msg();
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FREE RESOURCES
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		parent::__destruct();
		$this->free();
	}




	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		if (!is_array($this->json)) return false;
		$this->json = NULL;
		$this->result = false;
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!is_array($this->json)) return false;
		if (empty($this->json['data'][$row])) return false;
		if (!array_key_exists($column, $this->json['data'][$row])) return false;
		return $this->json['data'][$row][$column];
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	////////////////////////////////////////////////////////////////////////////
	public function count() {
		if (!is_array(	$this->json))			return 0;
		if (!isset(		$this->json['data']))	return 0;
		if (!is_array(	$this->json['data']))	return 0;
		return count(	$this->json['data']);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		if (!is_array(	$this->json))			return false;
		if (!isset(		$this->json['header']))	return false;
		if (!is_array(	$this->json['header']))	return false;
		return count(	$this->json['header']);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		if (!is_array($this->json)) return false;
		if (!isset($this->json['header'][$column])) return false;
		return $this->json['header'][$column];
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	public function seek($row) {
		if (!is_array($this->json)) return;
		$this->row = (int) $row;
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	////////////////////////////////////////////////////////////////////////////
	public function row($trim=true) {
		if (!is_array($this->json)) return false;
		if (!isset($this->json['data'][$this->row])) return false;

		$data = $this->json['data'][$this->row];

		$this->data = [];
		foreach ($data as $key => &$val) {
			if (!$this->json['header'][$key]) continue;
			$this->data[$this->json['header'][$key]] = $val;
		} unset($val);


		if ($trim) {
			foreach ($this->data as $key => &$val) {
				$val = trim($val);
			} unset($val);
		}

		$this->row++;
		return $this->data;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE RAW JSON DATA
	////////////////////////////////////////////////////////////////////////////
	public function json() {
		return $this->json;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE AUTO INCREMENT INSERT ID
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!is_array($this->json)) return 0;
		if (!isset($this->json['insertid'])) return 0;
		return (int) $this->json['insertid'];
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS UPDATED
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!is_array($this->json)) return 0;
		if (!isset($this->json['updated'])) return 0;
		return (int) $this->json['updated'];
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR CODE FOR THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (isset($this->json['error'][0])) {
			return $this->json['error'][0];
		} else if ($this->error) {
			return $this->error;
		}
		return $this->pudl->errno();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR MESSAGE FOR THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (isset($this->json['error'][1])) {
			return $this->json['error'][1];
		}
		return $this->pudl->error();
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var ?array */	private $json	= NULL;
	/** @var int */		private $error	= 0;
	/** @var string */	private $ermsg	= '';
}




////////////////////////////////////////////////////////////////////////////////
// COMPATIBILITY WITH OLDER PHP VERSIONS
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('json_last_error_msg')) {
	function json_last_error_msg() {
		global $__json_errors__;
		$error = json_last_error();
		if (in_array($error, $__json_errors__)) {
			throw new Exception($__json_errors__[$error]);
		}
	}
}
