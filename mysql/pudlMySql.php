<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMyShared.php'));
require_once(is_owner(__DIR__.'/pudlMySqlResult.php'));



class		pudlMySql
	extends	pudlMyShared {




	////////////////////////////////////////////////////////////////////////////
	// OPENS A CONNECTION TO A MYSQL SERVER
	// http://php.net/manual/en/function.mysql-connect.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('mysql');

		ini_set('mysql.connect_timeout', $auth['timeout']);


		//USE EXISTING CONNECTION IF AVAILABLE
		if (is_resource($auth['server'])) {
			$this->connection = $auth['server'];

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		} else if ($auth['persistent']) {
			$this->connection = @mysql_pconnect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		} else {
			$this->connection = @mysql_connect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);
		}

		//ATTEMPT TO SELECT THE DATABASE AND SET UTF8 CHARACTER SET
		if ($this->connection) {
			if (@mysql_select_db($auth['database'], $this->connection)) {
				$ok = @mysql_set_charset('utf8mb4', $this->connection);
			}
		}

		if (!empty($ok)) {
			$this->strict()->timeout($auth);
			return;
		}

		//CANNOT CONNECT - ERROR OUT
		throw new pudlConnectionException($this,
			'Unable to connect to MySQL server ' .
			'"' . $auth['server'] . '"' .
			' with the username ' .
			'"' . $auth['username'] . '"' .
			"\nError " . $this->errno() .
			': ' . $this->error()
		);


		// STORE WHICH SERVER WE'RE CONNECTED TO
		$this->connected = $auth['server'];
	}



	////////////////////////////////////////////////////////////////////////////
	// CLOSES THE DATABASE CONNECTION
	// http://php.net/manual/en/function.mysql-close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@mysql_close($this->connection);
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPES SPECIAL CHARACTERS IN A STRING FOR USE IN A SQL STATEMENT
	// http://php.net/manual/en/function.mysql-real-escape-string.php
	////////////////////////////////////////////////////////////////////////////
	public function escape($value) {
		if (!$this->connection) return @mysql_real_escape_string($value);
		return @mysql_real_escape_string($value, $this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE AND RETURNS A PUDLRESULT
	// http://php.net/manual/en/function.mysql-query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlMySqlResult($this);
		$result = @mysql_query($query, $this->connection);
		return new pudlMySqlResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE BYPASSING PUDL CALLS
	// http://php.net/manual/en/function.mysql-query.php
	////////////////////////////////////////////////////////////////////////////
	protected function _query($query) {
		if (!$this->connection) return false;
		return mysql_query($query, $this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE AUTO GENERATED ID USED IN THE LATEST QUERY
	// http://php.net/manual/en/function.mysql-insert-id.php
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		return @mysql_insert_id($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// GETS THE NUMBER OF AFFECTED ROWS IN A PREVIOUS MYSQL OPERATION
	// http://php.net/manual/en/function.mysql-affected-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return 0;
		return @mysql_affected_rows($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE VERSION NUMBER OF THE CONNECTED MYSQL/MARIADB SERVER
	// http://php.net/manual/en/function.mysql-get-server-info.php
	////////////////////////////////////////////////////////////////////////////
	public function version() {
		if (!$this->connection) return NULL;
		return @mysql_get_server_info($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/function.mysql-errno.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		return @mysql_errno($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/function.mysql-error.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		return @mysql_error($this->connection);
	}

}
