<?php


require_once('pudl.php');
require_once('pudlOdbcResult.php');


class pudlOdbc extends pudl {

	function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlOdbc($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		$this->odbc = @odbc_connect(
			$auth['database'],
			$auth['username'],
			$auth['password']
		);

		if ($this->odbc === false) {
			die('ERROR CONNECTING TO ODBC: ' . $this->errno() . ' - ' . $this->error());
		}
	}



	protected function process($query) {
		if (!$this->odbc) return false;
		$result = @odbc_exec($this->odbc, $query);
		$this->numrows = ($result !== false) ? @odbc_num_rows($result) : 0;
		return new pudlOdbcResult($result, $this);
	}



	public function insertId() {
		if (!$this->odbc) return 0;
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
		if (!$this->odbc) return (int) odbc_error();
		return (int) odbc_error($this->odbc);
	}



	public function error() {
		if (!$this->odbc) return odbc_errormsg();
		return odbc_errormsg($this->odbc);
	}



	private $odbc		= false;
	private $numrows	= 0;
}
