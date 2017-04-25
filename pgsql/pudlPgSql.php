<?php


require_once('pudlPgSqlResult.php');


class pudlPgSql extends pudl {

	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlPgSql($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		$this->connection = @pg_connect(
			' host='		. $auth['server'] .
			' dbname='		. $auth['database'] .
			' user='		. $auth['username'] .
			' password='	. $auth['password']
		);

		if ($this->connection === false) {
			throw new pudlException(
				'ERROR CONNECTING TO POSTGRESQL: ' . $this->error()
			);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		pg_close($this->connection);
		$this->connection = NULL;
	}



	public function identifier($identifier) {
		if (!$this->connection) return false;
		return pg_escape_identifier($this->connection, $identifier);
	}



	public function escape($value) {
		if (!$this->connection) return false;
		return pg_escape_string($this->connection, $value);
	}



	protected function process($query) {
		if (!$this->connection) return new pudlPgSqlResult(false, $this);
		$result = @pg_query($this->connection, $query);
		$this->numrows = ($result !== false) ? @pg_num_rows($result) : 0;
		return new pudlPgSqlResult($result, $this);
	}



	public function insertId() {
		if (!$this->connection) return 0;
		$result = @pg_query($this->connection, 'SELECT lastval()');
		if ($result === false) return false;
		$return = @pg_fetch_array($result, NULL, PGSQL_NUM);
		pg_free_result($result);
		return $return[0];
	}



	public function updated() {
		if (!$this->connection) return 0;
		return @pg_affected_rows($this->connection);
	}



	public function errno() {
		return $this->error() === '' ? 0 : 1;
	}



	public function error() {
		if (!$this->connection) return pg_last_error();
		return pg_last_error($this->connection);
	}

}
