<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlPgSqlResult.php'));



class		pudlPgSql
	extends pudl {




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// REATE AN INSTANCE OF THE THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data, $autoconnect=true) {
		return new pudlPgSql($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE A CONNECTION TO THE POSTGRESQL SERVER
	// http://php.net/manual/en/function.pg-connect.php
	// http://php.net/manual/en/function.pg-pconnect.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		if ($auth['persistent']) {
			$this->connection = @pg_pconnect(
				" host='"				. $auth['server']	. "'" .
				" dbname='"				. $auth['database']	. "'" .
				" user='"				. $auth['username']	. "'" .
				" password='"			. $auth['password']	. "'" .
				" connect_timeout='"	. $auth['timeout']	. "'" .
				" options='--client_encoding=UTF8'"
			);


		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		} else {
			$this->connection = @pg_connect(
				" host='"				. $auth['server']	. "'" .
				" dbname='"				. $auth['database']	. "'" .
				" user='"				. $auth['username']	. "'" .
				" password='"			. $auth['password']	. "'" .
				" connect_timeout='"	. $auth['timeout']	. "'" .
				" options='--client_encoding=UTF8'"
			);
		}


		if (empty($this->connection)) {
			$error = error_get_last();

			throw new pudlConnectionException($this,
				'Unable to connect to PostgreSQL server ' .
				'"' . $auth['server'] . '"' .
				' with the username ' .
				'"' . $auth['username'] . '"' .
				"\nError: " . $error['message']
			);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT FROM THE POSTGRESQL SERVER
	// http://php.net/manual/en/function.pg-close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		pg_close($this->connection);
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE AN IDENTIFIER
	// http://php.net/manual/en/function.pg-escape-identifier.php
	////////////////////////////////////////////////////////////////////////////
	public function identifier($identifier) {
		return ($this->connection)
			? pg_escape_identifier($this->connection, $identifier)
			: pg_escape_identifier($identifier);
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE A VALUE
	// http://php.net/manual/en/function.pg-escape-string.php
	////////////////////////////////////////////////////////////////////////////
	public function escape($value) {
		return ($this->connection)
			? pg_escape_string($this->connection, $value)
			: pg_escape_string($value);
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS A QUERY
	// http://php.net/manual/en/function.pg-query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlPgSqlResult($this);
		$result = @pg_query($this->connection, $query);
		return new pudlPgSqlResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST AUTO INCREMENT NUMBER FROM INSERTED DATA
	// https://www.postgresql.org/docs/8.1/functions-sequence.html
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return false;
		$result = @pg_query($this->connection, 'SELECT lastval()');
		if ($result === false) return false;
		$return = @pg_fetch_array($result);
		pg_free_result($result);
		return is_array($return) ? reset($return) : false;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS UPDATED BY THE LAST QUERY
	// http://php.net/manual/en/function.pg-affected-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return 0;
		return @pg_affected_rows($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR NUMBER
	// http://php.net/manual/en/function.pg-last-error.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		$error = $this->error();
		return empty($error) ? 0 : 1;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR MESSAGE
	// http://php.net/manual/en/function.pg-last-error.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return @pg_last_error();
		return pg_last_error($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UPSERT PART OF THE QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _upsert($data) {
		if (!pudl_array($data)  ||  empty($data)) return false;

		return	' ON CONFLICT (' .
				$this->identifier(key($data)) .
				') DO UPDATE SET ' .
				$this->_update($data);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT DATA TO BLOB
	////////////////////////////////////////////////////////////////////////////
	protected function blob($value) {
		return "E'x\\\\" . bin2hex($value) . "'";
	}

}
