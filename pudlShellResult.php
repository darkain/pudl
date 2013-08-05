<?php


require_once('pudlResult.php');


class pudlShellResult extends pudlResult {

	public function __construct($result, $query) {
		parent::__construct($result, $query);

		$this->row		= 0;
		$this->error	= false;
		$this->json		= json_decode($result, true);
		
		if ($this->json === NULL) {
			$this->result = false;
			$this->error  = json_last_error();
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


	public function row($type='ARRAY', $trim=true) {
		if (!$this->result) return false;
		if (!isset($this->json['data'][$this->row])) return false;

		$data = $this->json['data'][$this->row];

		if ($type === 'ARRAY') {
			$return = array();
			foreach ($data as $key => &$val) {
				$return[$this->json['header'][$key]] = $val;
			} unset($val);

		} else if ($type === 'NUMBER') {
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


	public function error() {
		if ($this->json === NULL) return $this->error;
		return $this->json['error'][0];
	}


	public function errormsg() {
		if ($this->json === NULL) return $this->error;
		return $this->json['error'][1];
	}


	private $json;
	private $error;
	private $row;
}
