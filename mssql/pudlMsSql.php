<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMsShared.php'));
require_once(is_owner(__DIR__.'/pudlMsSqlResult.php'));


class		pudlMsSql
	extends	pudlMsShared {



	public static function instance($data, $autoconnect=true) {
		return new pudlMsSql($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('mssql');

		$this->connection = @mssql_pconnect(
			$auth['server'],
			$auth['username'],
			$auth['password']
		);

		if (!$this->connection) {
			$this->connection = @mssql_connect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);
		}

		if (!$this->connection) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server: "' . $auth['server'];
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			throw new pudlConnectionException($this, $error);
		}

		if (!@mssql_select_db($auth['database'], $this->connection)) {
			$error  = "<br />\n";
			$error .= 'Unable to select database : "' . $auth['database'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			throw new pudlConnectionException($this, $error);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if ($this->connection) @mssql_close($this->connection);
		$this->connection = NULL;
	}



	protected function process($query) {
		if (!$this->connection) return new pudlMsSqlResult($this);
		$result = @mssql_query($query, $this->connection);
		return new pudlMsSqlResult($this, $result);
	}



	public function insertId() {
		if (!$this->connection) return false;
		$result = @mssql_query('SELECT @@IDENTITY', $this->connection);
		if ($result === false) return false;
		$return = @mssql_result($result, 0, 0);
		@mssql_free_result($result);
		return $return;
	}



	public function updated() {
		if (!$this->connection) return false;
		$result = @mssql_query('SELECT @@ROWCOUNT', $this->connection);
		if ($result === false) return false;
		$return = @mssql_result($result, 0, 0);
		@mssql_free_result($result);
		return $return;
	}



	public function errno() {
		$error = $this->error();
		return (int) !empty($error);
	}



	public function error() {
		return @mssql_get_last_message();
	}

}
