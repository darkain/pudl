<?php


require_once(is_owner(__DIR__.'/pudlOdbcResult.php'));


class pudlOdbc extends pudl {

	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlOdbc($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		$this->connection = @odbc_connect(
			$auth['database'],
			$auth['username'],
			$auth['password']
		);

		if ($this->connection === false) {
			throw new pudlException(
				$this,
				'ERROR CONNECTING TO ODBC: ' . $this->errno() . ' - ' . $this->error(),
				PUDL_X_CONNECTION
			);
		}
	}



	protected function process($query) {
		if (!$this->connection) return new pudlOdbcResult($this);
		$result = @odbc_exec($this->connection, $query);
		$this->numrows = ($result !== false) ? @odbc_num_rows($result) : 0;
		return new pudlOdbcResult($this, $result);
	}



	public function insertId() {
		if (!$this->connection) return 0;
		$result = @odbc_exec($this->connection, 'SELECT @@IDENTITY');
		if ($result === false) return false;
		@odbc_fetch_row($result, 0);
		$return = @odbc_result($result, 0);
		@odbc_free_result($result);
		return $return;
	}



	public function updated() {
		return $this->numrows;
	}



	public function errno() {
		if (!$this->connection) return (int) odbc_error();
		return (int) odbc_error($this->connection);
	}



	public function error() {
		if (!$this->connection) return odbc_errormsg();
		return odbc_errormsg($this->connection);
	}



	private $numrows	= 0;
}
