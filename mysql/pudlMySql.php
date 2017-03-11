<?php


require_once('pudlMySqlResult.php');
require_once('pudlMySqlHelper.php');



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

		ini_set('mysql.connect_timeout', 10);

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		$this->mysql = @mysql_pconnect(
			$auth['server'],
			$auth['username'],
			$auth['password']
		);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		if (empty($this->mysql)) {
			$this->mysql = @mysql_connect(
				$auth['server'],
				$auth['username'],
				$auth['password']
			);
		}

		//ATTEMPT TO SELECT THE DATABASE AND SET UTF8 CHARACTER SET
		if ($this->mysql) {
			if (@mysql_select_db($auth['database'], $this->mysql)) {
				$ok = @mysql_set_charset('utf8', $this->mysql);
			}
		}

		//CANNOT CONNECT - ERROR OUT
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "' . $auth['server'];
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			if (self::$die) throw new pudlException($error);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->mysql) return;
		@mysql_close($this->mysql);
		$this->mysql = NULL;
	}



	public function escape($value) {
		if (!$this->mysql) return @mysql_real_escape_string($value);
		return @mysql_real_escape_string($value, $this->mysql);
	}



	protected function process($query) {
		if (!$this->mysql) return new pudlMySqlResult(false, $this);
		$result = @mysql_query($query, $this->mysql);
		return new pudlMySqlResult($result, $this);
	}



	public function insertId() {
		if (!$this->mysql) return 0;
		return @mysql_insert_id($this->mysql);
	}



	public function updated() {
		if (!$this->mysql) return 0;
		return @mysql_affected_rows($this->mysql);
	}



	public function errno() {
		return @mysql_errno($this->mysql);
	}



	public function error() {
		return @mysql_error($this->mysql);
	}



	private $mysql = NULL;
}
