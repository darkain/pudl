<?php


require_once('pudlResult.php');


class pudlShellResult extends pudlResult {

	public function __construct($result, $db) {
		parent::__construct($result, $db);

		$this->row		= 0;
		$this->error	= false;
		$this->json		= $db->jsonDecode($result);

		if ($this->json === NULL) {
			$this->result = false;
			$this->error  = json_last_error();
			$this->ermsg  = json_last_error_msg();
		}
	}


	public function __destruct() {
		$this->free();
	}



	public function free() {
		if (!$this->result) return false;
		$this->json = NULL;
		$this->result = false;
		return true;
	}


	public function cell($row=0, $column=0) {
		if (!$this->result) return false;
		if (!isset($this->json['data'][$row][$column])) return false;
		return $this->json['data'][$row][$column];
	}


	public function count() {
		if (!$this->result) return 0;
		return count($this->json['data']);
	}


	public function fields() {
		if (!$this->result) return false;
		return count($this->json['header']);
	}


	public function getField($column) {
		if (!isset($this->json['header'][$column])) return false;
		return $this->json['header'][$column];
	}


	public function seek($row) {
		$this->row = (int) $row;
	}


	public function row($type=PUDL_ARRAY, $trim=true) {
		if (!$this->result) return false;
		if (!isset($this->json['data'][$this->row])) return false;

		$data = $this->json['data'][$this->row];

		if ($type === PUDL_ARRAY) {
			$return = array();
			foreach ($data as $key => &$val) {
				$return[$this->json['header'][$key]] = $val;
			} unset($val);

		} else if ($type === PUDL_NUMBER) {
			$return = &$data;

		} else { //BOTH
			$return = $data;
			foreach ($data as $key => &$val) {
				$return[$this->json['header'][$key]] = $val;
			} unset($val);
		}

		if ($trim) {
			foreach ($return as $key => &$val) {
				$val = trim($val);
			} unset($val);
		}

		$this->row++;
		return $return;
	}


	public function json() {
		return $this->json;
	}


	public function insertId() {
		if ($this->json === NULL) return 0;
		if (!isset($this->json['insertid'])) return 0;
		return (int) $this->json['insertid'];
	}


	public function updated() {
		if ($this->json === NULL) return 0;
		if (!isset($this->json['updated'])) return 0;
		return (int) $this->json['updated'];
	}


	public function error() {
		if ($this->json === NULL) return (int) $this->error;
		return (int) $this->json['error'][0];
	}


	public function errormsg() {
		if ($this->json === NULL) return $this->error;
		return $this->json['error'][1];
	}


	private $json;
	private $error;
	private $ermsg;
	private $row;
}



if (!function_exists('json_last_error_msg')) {
	function json_last_error_msg() {
		switch (json_last_error()) {
			default: return;
			case JSON_ERROR_DEPTH: $error = 'Maximum stack depth exceeded'; break;
			case JSON_ERROR_STATE_MISMATCH: $error = 'Underflow or the modes mismatch'; break;
			case JSON_ERROR_CTRL_CHAR: $error = 'Unexpected control character found'; break;
			case JSON_ERROR_SYNTAX: $error = 'Syntax error, malformed JSON'; break;
			case JSON_ERROR_UTF8: $error = 'Malformed UTF-8 characters, possibly incorrectly encoded';break;
		}
		throw new Exception($error);
	}
}
