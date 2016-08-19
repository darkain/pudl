<?php


require_once('pudlPgSqlResult.php');


class pudlPgSql extends pudl {

	function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlPgSql($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		$this->pgsql = @pg_connect(
			' host='		. $auth['server'] .
			' dbname='		. $auth['database'] .
			' user='		. $auth['username'] .
			' password='	. $auth['password']
		);

		if ($this->pgsql === false) {
			die('ERROR CONNECTING TO POSTGRESQL: ' . $this->error());
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->pgsql) return;
		pg_close($this->pgsql);
		$this->pgsql = false;
	}



	public function identifier($identifier) {
		if (!$this->pgsql) return false;
		return pg_escape_identifier($this->pgsql, $identifier);
	}



	public function escape($value) {
		if (!$this->pgsql) return false;
		return pg_escape_string($this->pgsql, $value);
	}



	protected function process($query) {
		if (!$this->pgsql) return new pudlPgSqlResult(false, $this);
		$result = @pg_query($this->pgsql, $query);
		$this->numrows = ($result !== false) ? @pg_num_rows($result) : 0;
		return new pudlPgSqlResult($result, $this);
	}



	public function insertId() {
		if (!$this->pgsql) return 0;
		$result = @pg_query($this->pgsql, 'SELECT lastval()');
		if ($result === false) return false;
		$return = @pg_fetch_array($result, NULL, PGSQL_NUM);
		pg_free_result($result);
		return $return[0];
	}



	public function updated() {
		if (!$this->pgsql) return 0;
		return @pg_affected_rows($this->pgsql);
	}



	public function errno() {
		return $this->error() === '' ? 0 : 1;
	}



	public function error() {
		if (!$this->pgsql) return pg_last_error();
		return pg_last_error($this->pgsql);
	}



	private $pgsql = false;

}
