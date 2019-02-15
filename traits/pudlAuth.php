<?php


////////////////////////////////////////////////////////////////////////////////
// STORE CREDENTIALS IN SECURED AREA HIDDEN FROM VAR_DUMP/VAR_EXPORT
////////////////////////////////////////////////////////////////////////////////
trait pudlAuth {




	////////////////////////////////////////////////////////////////////////////
	// RETRIEVE STORED HIDDEN DATABASE AUTH INFORMATION
	////////////////////////////////////////////////////////////////////////////
	protected function auth() { return $this->_auth(); }




	////////////////////////////////////////////////////////////////////////////
	// STORE DATABASE AUTH INFORMATION IN HIDDEN DATA
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// UPDATE AUTH INFORMATION FOR THIS PUDL INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function updateAuth($data) {
		$auth = $this->_auth();
		if (!pudl_array($auth)) $auth = [];

		// MERGE IN NEW DATA
		foreach ($data as $key => $value)  {
			$auth[$key] = $value;
		}

		// UPDATE PREFIX INFORMATION
		if (isset($auth['prefix'])) {
			$this->prefix	= is_string($auth['prefix'])
							? ['pudl_' => $auth['prefix']]
							: $auth['prefix'];
		}

		// STORE AUTH DATA
		return $this->_auth($auth);
	}




	////////////////////////////////////////////////////////////////////////////
	// PRIVATE VARIABLES - INSTANCE INFORMATION
	////////////////////////////////////////////////////////////////////////////
	private			$instance	= 0;
	private static	$instances	= 0;

}
