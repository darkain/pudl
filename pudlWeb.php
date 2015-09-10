<?php


require_once('pudlShell.php');
require_once('pudlShellResult.php');


class pudlWeb extends pudlShell {
	public function __construct($path, $prefix=false) {
		parent::__construct($path, $prefix);
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
		return $this->_process($result[0]);
	}
}
