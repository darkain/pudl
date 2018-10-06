<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlPgSqlResult.php'));



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
			" host='"				. $auth['server']	. "'" .
			" dbname='"				. $auth['database']	. "'" .
			" user='"				. $auth['username']	. "'" .
			" password='"			. $auth['password']	. "'" .
			" connect_timeout='"	. $auth['timeout']	. "'" .
			" options='--client_encoding=UTF8'"
		);

		if ($this->connection === false) {
			$error = error_get_last();
			throw new pudlException(
				$this,
				'ERROR CONNECTING TO POSTGRESQL: ' . $error['message'],
				PUDL_X_CONNECTION
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
		return ($this->connection)
			? pg_escape_identifier($this->connection, $identifier)
			: pg_escape_identifier($identifier);
	}



	public function escape($value) {
		return ($this->connection)
			? pg_escape_string($this->connection, $value)
			: pg_escape_string($value);
	}



	protected function process($query) {
		if (!$this->connection) return new pudlPgSqlResult($this);
		$result = @pg_query($this->connection, $query);
		return new pudlPgSqlResult($this, $result);
	}



	public function insertId() {
		if (!$this->connection) return false;
		$result = @pg_query($this->connection, 'SELECT lastval()');
		if ($result === false) return false;
		$return = @pg_fetch_array($result);
		pg_free_result($result);
		return is_array($return) ? reset($return) : false;
	}



	public function updated() {
		if (!$this->connection) return 0;
		return @pg_affected_rows($this->connection);
	}



	public function errno() {
		$error = $this->error();
		return empty($error) ? 0 : 1;
	}



	public function error() {
		if (!$this->connection) return @pg_last_error();
		return pg_last_error($this->connection);
	}

}
