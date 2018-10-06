<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlMyShared.php'));
require_once(is_owner(__DIR__.'/pudlMySqlResult.php'));



class pudlMySql extends pudlMyShared {



	public static function instance($data, $autoconnect=true) {
		return new pudlMySql($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('mysql');

		ini_set('mysql.connect_timeout', $auth['timeout']);

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		$this->connection = @mysql_pconnect(
			$auth['server'],
			$auth['username'],
			$auth['password']
		);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		if (empty($this->connection)) {
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
		$error  = "<br />\n";
		$error .= 'Unable to connect to database server "' . $auth['server'];
		$error .= '" with the username: "' . $auth['username'];
		$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
		throw new pudlException($this, $error, PUDL_X_CONNECTION);
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@mysql_close($this->connection);
		$this->connection = NULL;
	}



	public function escape($value) {
		if (!$this->connection) return @mysql_real_escape_string($value);
		return @mysql_real_escape_string($value, $this->connection);
	}



	protected function process($query) {
		if (!$this->connection) return new pudlMySqlResult($this);
		$result = @mysql_query($query, $this->connection);
		return new pudlMySqlResult($this, $result);
	}



	protected function _query($query) {
		if (!$this->connection) return false;
		return mysql_query($query, $this->connection);
	}



	public function insertId() {
		if (!$this->connection) return 0;
		return @mysql_insert_id($this->connection);
	}



	public function updated() {
		if (!$this->connection) return 0;
		return @mysql_affected_rows($this->connection);
	}



	public function errno() {
		return @mysql_errno($this->connection);
	}



	public function error() {
		return @mysql_error($this->connection);
	}
}
