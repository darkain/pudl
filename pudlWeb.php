<?php


require_once('pudl.php');
require_once('pudlShellResult.php');


class pudlWeb extends pudl {
	public function __construct($path, $prefix=false) {
		parent::__construct();

		$this->escstart	= '"';
		$this->escend	= '"';
		$this->top		= true;
		$this->error	= false;
		$this->path		= $path;
		$this->prefix	= $prefix;
	}


	public static function instance($data) {
		$path	= empty($data['pudl_path'])		? '' : $data['pudl_path'];
		$prefix	= empty($data['pudl_prefix'])	? false : $data['pudl_prefix'];
		return new pudlWeb($path, $prefix);
	}

	

	protected function process($query) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->path);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'q=' . rawurlencode($query));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec($ch);
		curl_close($ch);

		$item = new pudlShellResult($result, $query);
		$this->error = $item->error();
		return $item;
	}


	public function safe($str) {
		//TODO: IMPLEMENT THIS BIG TIEMZ!
		return $str;
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
