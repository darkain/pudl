<?php


require_once('pudl.php');
require_once('pudlOdbcResult.php');


class pudlOdbc extends pudl {
	public function __construct($username, $password, $database, $server='localhost', $prefix=false) {
		parent::__construct();

		$this->escstart	= '"';
		$this->escend	= '"';
		$this->numrows	= 0;
		$this->top		= true;
		$this->prefix	= $prefix;

		$this->odbc = false;
		$this->odbc = @odbc_connect($database, $username, $password);
		if ($this->odbc === false) {
			die('ERROR CONNECTING TO ODBC: ' . odbc_error() . ' - ' . odbc_errormsg());
		}
	}


	public static function instance($data) {
		$username	= empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password	= empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database	= empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server		= empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];
		return new pudlOdbc($username, $password, $database, $server, $prefix);
	}



	protected function process($query) {
		$result = false;
		$result = @odbc_exec($this->odbc, $query);
		$this->numrows = ($result !== false) ? @odbc_num_rows($result) : 0;
		return new pudlOdbcResult($result, $this);
	}



	public function insertId() {
		$result = @odbc_exec($this->odbc, 'SELECT @@IDENTITY');
		if ($result === false) return false;
		@odbc_fetch_row($result, 0);
		$return = @odbc_result($this->result, 0);
		@odbc_free_result($this->result);
		return $return;
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
