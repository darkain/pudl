<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlShellResult.php'));



class		pudlWeb
	extends	pudlShell {




	////////////////////////////////////////////////////////////////////////////
	// VERIFY WE HAVE THE PROPER PHP EXTENSION INSTALLED
	// NOTE: NO ACTIVE CONNECTIONS ARE MADE WITH THIS UNTIL A REQUEST IS MADE
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		pudl_require_extension('curl');
		parent::connect();
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE AND RETURNS A PUDLRESULT
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		$ch = curl_init();

		curl_setopt_array($ch, [
			CURLOPT_URL				=> $this->path,
			CURLOPT_POST			=> true,
			CURLOPT_POSTFIELDS		=> ['q' => $query],
			CURLOPT_RETURNTRANSFER	=> true,
		]);

		$result = curl_exec($ch);

		$this->curl_error = curl_error($ch);
		$this->curl_errno = curl_errno($ch);

		curl_close($ch);

		return $this->_process($result);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/function.curl-errno.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		return	($this->curl_errno)
				? $this->curl_errno
				: parent::errno();
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/function.curl-error.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if ($this->curl_error) return $this->curl_error;
		return parent::error();
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $curl_errno = 0;
	private $curl_error = '';

}
