<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMsShared.php'));
require_once(is_owner(__DIR__.'/pudlSqlSrvResult.php'));



class		pudlSqlSrv
	extends	pudlMsShared {




	////////////////////////////////////////////////////////////////////////////
	// CREATE AN INSTANCE OF THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data, $autoconnect=true) {
		return new pudlSqlSrv($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONNECT TO THE MICROSOFT SQL SERVER
	// http://php.net/manual/en/function.sqlsrv-connect.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('sqlsrv');

		$this->connection = @sqlsrv_connect(
			$auth['server'],
			[
				'Database'			=> $auth['database'],
				'UID'				=> $auth['username'],
				'PWD'				=> $auth['password'],
				'ConnectionPooling'	=> $auth['persistent'],
				'LoginTimeout'		=> $auth['timeout'],
				'APP'				=> $this->version,
				'CharacterSet'		=> 'UTF-8',
			]
		);

		if (!$this->connection) {
			throw new pudlConnectionException($this,
				'Unable to connect to Microsoft SQL Server ' .
				'"' . $auth['server'] . '"' .
				' with the username ' .
				'"' . $auth['username'] . '"' .
				"\nError " . $this->errno() .
				': ' . $this->error()
			);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// CLOSE THE CONNECTION TO THE MICROSOFT SQL SERVER
	// http://php.net/manual/en/function.sqlsrv-close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if ($this->connection) @sqlsrv_close($this->connection);
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS THE SQL QUERY
	// http://php.net/manual/en/function.sqlsrv-query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlSqlSrvResult($this);

		$result = @sqlsrv_query(
			$this->connection,
			$query,
			[],
			['Scrollable' => SQLSRV_CURSOR_STATIC]
		);

		return new pudlSqlSrvResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE MOST RECENT AUTO-INCREMENT ID
	// https://docs.microsoft.com/en-us/sql/t-sql/functions/identity-transact-sql
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return false;
		$result = @sqlsrv_query($this->connection, 'SELECT @@IDENTITY');
		if ($result === false) return false;
		$return = @sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
		@sqlsrv_free_stmt($result);
		return reset($return);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS AFFECTED BY THE MOST RECENT SQL QUERY
	// https://docs.microsoft.com/en-us/sql/t-sql/functions/rowcount-transact-sql
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return false;
		$result = @sqlsrv_query($this->connection, 'SELECT @@ROWCOUNT');
		if ($result === false) return false;
		$return = @sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
		@sqlsrv_free_stmt($result);
		return reset($return);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE VERSION STRING FOR THE MICROSOFT SQL SERVER
	// http://php.net/manual/en/function.sqlsrv-server-info.php
	////////////////////////////////////////////////////////////////////////////
	public function version() {
		if (!$this->connection) return NULL;
		$version = sqlsrv_server_info($this->connection);
		return $version['SQLServerVersion'];
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/function.sqlsrv-errors.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (empty($errors)) return 0;
		$error = end($errors);
		return !empty($error['code']) ? $error['code'] : 0;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/function.sqlsrv-errors.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (empty($errors)) return false;
		$error = end($errors);
		return !empty($error['message']) ? $error['message'] : false;
	}


}
