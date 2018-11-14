<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMsShared.php'));
require_once(is_owner(__DIR__.'/pudlMsSqlResult.php'));



class		pudlMsSql
	extends	pudlMsShared {




	////////////////////////////////////////////////////////////////////////////
	// CREATE AN INSTANCE OF THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data, $autoconnect=true) {
		return new pudlMsSql($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONNECT TO THE MICROSOFT SQL SERVER
	// http://php.net/manual/en/function.mssql-connect.php
	// http://php.net/manual/en/function.mssql-pconnect.php
	// http://php.net/manual/en/function.mssql-select-db.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('mssql');

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		if ($auth['persistent']) {
			$this->connection = @mssql_pconnect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		} else {
			$this->connection = @mssql_connect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);
		}


		if (empty($this->connection)) {
			throw new pudlConnectionException($this,
				'Unable to connect to Microsoft SQL Server ' .
				'"' . $auth['server'] . '"' .
				' with the username ' .
				'"' . $auth['username'] . '"' .
				"\nError: " . $this->error()
			);
		}

		if (!@mssql_select_db($auth['database'], $this->connection)) {
			throw new pudlConnectionException($this,
				'Unable to select database ' .
				'"' . $auth['database'] . '"' .
				"\nError: " . $this->error()
			);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT FROM THE MICROSOFT SQL SERVER
	// http://php.net/manual/en/function.mssql-close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if ($this->connection) @mssql_close($this->connection);
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS THE SQL QUERY
	// http://php.net/manual/en/function.mssql-query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlMsSqlResult($this);
		$result = @mssql_query($query, $this->connection);
		return new pudlMsSqlResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE MOST RECENT AUTO-INCREMENT ID
	// https://docs.microsoft.com/en-us/sql/t-sql/functions/identity-transact-sql
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return false;
		$result = @mssql_query('SELECT @@IDENTITY', $this->connection);
		if ($result === false) return false;
		$return = @mssql_result($result, 0, 0);
		@mssql_free_result($result);
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS AFFECTED BY THE MOST RECENT SQL QUERY
	// https://docs.microsoft.com/en-us/sql/t-sql/functions/rowcount-transact-sql
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return false;
		$result = @mssql_query('SELECT @@ROWCOUNT', $this->connection);
		if ($result === false) return false;
		$return = @mssql_result($result, 0, 0);
		@mssql_free_result($result);
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR NUMBER
	// http://php.net/manual/en/function.mssql-get-last-message.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		$error = $this->error();
		return (int) !empty($error);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR MESSAGE
	// http://php.net/manual/en/function.mssql-get-last-message.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		return @mssql_get_last_message();
	}

}
