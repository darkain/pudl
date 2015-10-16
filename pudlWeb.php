<?php


require_once('pudlShell.php');
require_once('pudlShellResult.php');


class pudlWeb extends pudlShell {
	public function __construct($path, $prefix=false) {
		parent::__construct($path, $prefix);

		$this->path = $path;
	}


	public static function instance($data) {
		$path	= empty($data['pudl_path'])		? '' : $data['pudl_path'];
		$prefix	= empty($data['pudl_prefix'])	? false : $data['pudl_prefix'];
		$db = new pudlWeb($path, $prefix);
		if (!empty($data['pudl_redis'])) $db->redis($data['pudl_redis']);
		return $db;
	}



	protected function process($query) {
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $this->path);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'q='.rawurlencode($query));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$result = curl_exec($ch);

		$this->curl_error = curl_error($ch);
		$this->curl_errno = curl_errno($ch);

		curl_close($ch);

		return $this->_process($result);
	}


	public function errno() {
		if ($this->curl_errno) return $this->curl_errno;
		return parent::errno();
	}


	public function error() {
		if ($this->curl_error) return $this->curl_error;
		return parent::error();
	}


	private $curl_error = '';
	private $curl_errno = 0;
}
