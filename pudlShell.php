<?php


require_once('pudl.php');
require_once('pudlShellResult.php');


class pudlShell extends pudl {
	public function __construct($path, $prefix=false) {
		parent::__construct();

		$this->escstart	= '"';
		$this->escend	= '"';
		$this->top		= true;
		$this->error	= false;
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
		$item = new pudlShellResult($result[0], $query);
		$this->error = $item->error();
		return $item;
	}


	public function safe($value) {
		//TODO: IMPLEMENT THIS BIG TIEMZ!
		return $value;
	}



	public function insertId() {
		//TODO: Insert ID
		return 0;
	}


	public function updated() {
		//TODO: Number of Rows Updated
		return 0;
	}


	public function errno() {
		return (int) $this->error;
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


	private $path;
	private $error;
}
