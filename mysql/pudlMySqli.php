<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMyShared.php'));
require_once(is_owner(__DIR__.'/pudlMySqliResult.php'));



class		pudlMySqli
	extends	pudlMyShared {




	////////////////////////////////////////////////////////////////////////////
	// OPENS A CONNECTION TO A MYSQL SERVER
	// http://php.net/manual/en/function.mysqli-connect.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('mysqli');

		$this->connection = mysqli_init();
		$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT,	$auth['timeout']);
		$this->connection->options(MYSQLI_OPT_READ_TIMEOUT,		$auth['timeout']);

		//ATTEMPT TO CREATE A CONNECTION
		$ok = @$this->connection->real_connect(
			(empty($auth['persistent']) ? '' : 'p:') . $auth['server'],
			$auth['username'],
			$auth['password'],
			$auth['database']
		);

		//VERIFY WE CONNECTED OKAY!
		if ($ok) $ok = ($this->connectErrno() === 0);

		//ATTEMPT TO SET UTF8 CHARACTER SET
		if ($ok) $ok = @$this->connection->set_charset('utf8mb4');

		//CONNECTION IS GOOD!
		if (!empty($ok)) {
			$this->connection->options(
				MYSQLI_OPT_READ_TIMEOUT,
				ini_get('mysqlnd.net_read_timeout')
			);

			$this->strict()->timeout($auth);

			return true;
		}


		//CANNOT CONNECT - ERROR OUT
		throw new pudlConnectionException(
			$this,
			pudl::jsonEncode([
				'message'	=> 'Database connection error',
				'code'		=> $this->connectErrno(),
				'error'		=> $this->connectError(),
				'server'	=> $auth['server'],
				'user'		=> $auth['username'],
			],
			$this->connectErrno())
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// RECONNECT TO THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function reconnect() {
		static $depth = 0;

		if (++$depth > PUDL_RECURSION) {
			$depth = 0;
			throw new pudlRecursionException($this,
				'Recursion limit reached in ' . __METHOD__
			);
		}

		$this->disconnect(false);
		$connect = $this->connect();

		$this->trigger('reconnect', $connect, $depth);

		//TODO: move this to a reconnect callback method
		if ($connect) {
			if ($this->inTransaction()) {
				$result = $this->retryTransaction();
			} else {
				$result = $this->process($this->query());
			}
		}

		$depth--;

		return $connect;
	}




	////////////////////////////////////////////////////////////////////////////
	// CLOSES THE DATABASE CONNECTION
	// http://php.net/manual/en/mysqli.close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@$this->connection->close();
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPES SPECIAL CHARACTERS IN A STRING FOR USE IN AN SQL STATEMENT
	// http://php.net/manual/en/mysqli.real-escape-string.php
	////////////////////////////////////////////////////////////////////////////
	public function escape($value) {
		if (!$this->connection) return false;
		return @$this->connection->real_escape_string($value);
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE AND RETURNS A PUDLRESULT
	// http://php.net/manual/en/mysqli.query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		$result = $this->_query($query);

		switch ($this->errno()) {
			case 2006: // "MYSQL SERVER HAS GONE AWAY"
			case 2013: // "LOST CONNECTION TO MYSQL SERVER DURING QUERY"
			case 2062: // "READ TIMEOUT IS REACHED"
				if ($result) $result->free();
				$result = $this->reconnect();
			break;
		}

		return new pudlMySqliResult($this,
			$result instanceof mysqli_result
				? $result
				: NULL
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE BYPASSING PUDL CALLS
	// http://php.net/manual/en/mysqli.query.php
	////////////////////////////////////////////////////////////////////////////
	protected function _query($query) {
		if (!$this->connection) return false;
		return @$this->connection->query($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE AUTO GENERATED ID USED IN THE LATEST QUERY
	// http://php.net/manual/en/mysqli.insert-id.php
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->insert_id;
	}




	////////////////////////////////////////////////////////////////////////////
	// GETS THE NUMBER OF AFFECTED ROWS IN A PREVIOUS MYSQL OPERATION
	// http://php.net/manual/en/mysqli.affected-rows.php
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return 0;
		return $this->connection->affected_rows;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETRIEVES INFORMATION ABOUT THE MOST RECENTLY EXECUTED QUERY
	// http://php.net/manual/en/mysqli.info.php
	////////////////////////////////////////////////////////////////////////////
	public function info() {
		if (!$this->connection) return [];

		$info	= explode('  ', $this->connection->info);
		$array	= [];

		foreach ($info as $item) {
			$parts = explode(': ', $item);
			if (count($parts) < 2) continue;
			$parts[0] = str_replace(' ', '_', strtolower($parts[0]));
			$array[$parts[0]] = (int)$parts[1];
		}

		return $array;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE VERSION NUMBER OF THE CONNECTED MYSQL/MARIADB SERVER
	// http://php.net/manual/en/mysqli.get-server-info.php
	////////////////////////////////////////////////////////////////////////////
	public function version() {
		if (!$this->connection) return NULL;
		return $this->connection->server_info;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/mysqli.errno.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!$this->connection) return @mysqli_connect_errno();
		return $this->connection->errno;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/mysqli.error.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return @mysqli_connect_error();
		return $this->connection->error;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FROM LAST CONNECT CALL
	// http://php.net/manual/en/mysqli.connect-errno.php
	////////////////////////////////////////////////////////////////////////////
	public function connectErrno() {
		if (!$this->connection) return @mysqli_connect_errno();
		return $this->connection->connect_errno;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST CONNECT ERROR
	// http://php.net/manual/en/mysqli.connect-error.php
	////////////////////////////////////////////////////////////////////////////
	public function connectError() {
		if (!$this->connection) return @mysqli_connect_error();
		return $this->connection->connect_error;
	}

}
