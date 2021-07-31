<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlOdbcResult.php'));



class		pudlOdbc
	extends	pudl {




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// CONNECT TO THE ODBC SQL DATABASE
	// http://php.net/manual/en/function.odbc-connect.php
	// http://php.net/manual/en/function.odbc-pconnect.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();


		//USE EXISTING CONNECTION IF AVAILABLE
		if (is_resource($auth['server'])) {
			$this->connection = $auth['server'];


		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		} else if ($auth['persistent']) {
			$this->connection = @odbc_pconnect(
				$auth['database'],
				$auth['username'],
				$auth['password']
			);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		} else {
			$this->connection = @odbc_connect(
				$auth['database'],
				$auth['username'],
				$auth['password']
			);
		}


		//CANNOT CONNECT - ERROR OUT
		if (empty($this->connection)) {
			throw new pudlConnectionException($this,
				'Unable to connect to ODBC database ' .
				'"' . $auth['database'] . '"' .
				' with the username ' .
				'"' . $auth['username'] . '"' .
				"\nError " . $this->errno() .
				': ' . $this->error()
			);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS THE SQL QUERY
	// http://php.net/manual/en/function.odbc-exec.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlOdbcResult($this);
		$result = @odbc_exec($this->connection, $query);
		$this->numrows = ($result !== false) ? @odbc_num_rows($result) : 0;
		return new pudlOdbcResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE MOST RECENT AUTO-INCREMENT ID
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		$result = @odbc_exec($this->connection, 'SELECT @@IDENTITY');
		if ($result === false) return false;
		@odbc_fetch_row($result, 0);
		$return = @odbc_result($result, 0);
		@odbc_free_result($result);
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS AFFECTED BY THE MOST RECENT SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		return $this->numrows;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/function.odbc-error.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!$this->connection) return (int) odbc_error();
		return (int) odbc_error($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/function.odbc-errormsg.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return odbc_errormsg();
		return odbc_errormsg($this->connection);
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $numrows = 0;
}
