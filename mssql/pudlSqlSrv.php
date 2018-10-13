<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMsShared.php'));
require_once(is_owner(__DIR__.'/pudlSqlSrvResult.php'));


class		pudlSqlSrv
	extends	pudlMsShared {



	public static function instance($data, $autoconnect=true) {
		return new pudlSqlSrv($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('sqlsrv');

		$this->connection = @sqlsrv_connect(
			$auth['server'],
			[
				'Database'	=> $auth['database'],
				'UID'		=> $auth['username'],
				'PWD'		=> $auth['password'],
			]
		);

		if (!$this->connection) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server: "' . $auth['server'];
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			throw new pudlConnectionException($this, $error);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if ($this->connection) @sqlsrv_close($this->connection);
		$this->connection = NULL;
	}



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



	public function insertId() {
		if (!$this->connection) return false;
		$result = @sqlsrv_query($this->connection, 'SELECT @@IDENTITY');
		if ($result === false) return false;
		$return = @sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
		@sqlsrv_free_stmt($result);
		return reset($return);
	}



	public function updated() {
		if (!$this->connection) return false;
		$result = @sqlsrv_query($this->connection, 'SELECT @@ROWCOUNT');
		if ($result === false) return false;
		$return = @sqlsrv_fetch_array($result, SQLSRV_FETCH_NUMERIC);
		@sqlsrv_free_stmt($result);
		return reset($return);
	}



	public function errno() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (empty($errors)) return 0;
		$error = end($errors);
		return !empty($error['code']) ? $error['code'] : 0;
	}



	public function error() {
		$errors = sqlsrv_errors(SQLSRV_ERR_ERRORS);
		if (empty($errors)) return false;
		$error = end($errors);
		return !empty($error['message']) ? $error['message'] : false;
	}


}
