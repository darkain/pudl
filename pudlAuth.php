<?php


trait pudlAuth {


	//RETRIEVE STORED HIDDEN DATA
	protected function auth() { return $this->_auth(); }


	//STORE CREDENTIALS IN SECURED AREA HIDDEN FROM VAR_DUMP/VAR_EXPORT
	private function _auth($data=false) {
		static $auth = [];

		if (!$this->instance) {
			$this->instance = ++self::$instances;
		}

		if ($data === NULL) {
			unset($auth[$this->instance]);
			return NULL;
		}

		if ($data !== false) {
			return $auth[$this->instance] = $data;
		}

		return empty($auth[$this->instance]) ? [] : $auth[$this->instance];
	}


	private			$instance	= 0;
	private static	$instances	= 0;

}