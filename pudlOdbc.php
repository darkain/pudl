<?php


require_once('pudl.php');
require_once('pudlOdbcResult.php');


class pudlOdbc extends pudl {
	public function __construct($username, $password, $database, $server='localhost') {
		parent::__construct();

		$this->escstart = '"';
		$this->escend = '"';
		$this->numrows = 0;
		$this->top = true;
		
		$this->odbc = false;
		$this->odbc = @odbc_connect($database, $username, $password);
		if ($this->odbc === false) {
			die('ERROR CONNECTING TO ODBC: ' . odbc_error() . ' - ' . odbc_errormsg());
		}
		//TODO: error out if connection fails
	}


	protected function process($query) {
		$result = false;
		$result = @odbc_exec($this->odbc, $query);
		$this->numrows = ($result !== false) ? @odbc_num_rows($result) : 0;
		return new pudlOdbcResult($result, $query);
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
		return $this->numrows;
	}


	public function errno() {
		return (int) odbc_error($this->odbc);
	}


	public function error() {
		return odbc_errormsg($this->odbc);
	}


	private $odbc;
	private $numrows;
}
