<?php


require_once(is_owner(__DIR__.'/pudlMySqlResult.php'));
require_once(is_owner(__DIR__.'/pudlMySqlHelper.php'));



class pudlMySql extends pudl {
	use pudlMySqlHelper;


	public function __construct($data, $autoconnect=true) {
		//SET INITIAL VALUES
		$this->identifier = '`';

		parent::__construct($data, $autoconnect);
	}



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlMySql($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

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
				$ok = @mysql_set_charset('utf8', $this->connection);
			}
		}

		//CANNOT CONNECT - ERROR OUT
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "' . $auth['server'];
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			if (self::$die) throw new pudlException($error, PUDL_X_CONNECTION);
		} else {
			$this->strict()->timeout($auth);
		}
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



	public function _query($query) {
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
