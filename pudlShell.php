<?php


require_once('pudl.php');
require_once('pudlShellResult.php');


class pudlShell extends pudl {
	public function __construct($path, $prefix=false) {
		parent::__construct();

		$this->escstart	= '"';
		$this->escend	= '"';
		$this->top		= true;
		$this->path		= escapeshellarg($path);
		$this->prefix	= $prefix;
	}


	public static function instance($data) {
		$path	= empty($data['pudl_path'])		? '' : $data['pudl_path'];
		$prefix	= empty($data['pudl_prefix'])	? false : $data['pudl_prefix'];
		return new pudlShell($path, $prefix);
	}



	protected function process($query) {
		$result = false;
		exec('php5 ' . $this->path . ' ' . escapeshellarg($query), $result);
		return $this->_process($result[0]);
	}



	protected function _process($json) {
		$item = new pudlShellResult($json, $this);
		$this->insertId	= $item->insertId();
		$this->updated	= $item->updated();
		$this->error	= $item->error();
		return $item;
	}



	public function insertId() {
		return $this->insertId;
	}


	public function updated() {
		return $this->updated;
	}


	public function errno() {
		return $this->error;
	}


	public function error() {
		switch ($this->errno()) {
			case JSON_ERROR_NONE:			return 'No errors';
			case JSON_ERROR_DEPTH:			return 'Maximum stack depth exceeded';
			case JSON_ERROR_STATE_MISMATCH:	return 'Underflow or the modes mismatch';
			case JSON_ERROR_CTRL_CHAR:		return 'Unexpected control character found';
			case JSON_ERROR_SYNTAX:			return 'Syntax error, malformed JSON';
			case JSON_ERROR_UTF8:			return 'Malformed UTF-8 characters';
		}
		return 'Unknown error';
	}


	protected $path;
	protected $error	= false;
	protected $insertId	= 0;
	protected $updated	= 0;
}
