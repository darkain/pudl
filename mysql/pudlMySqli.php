<?php


require_once('pudlMySqliResult.php');
require_once('pudlMySqlHelper.php');



class pudlMySqli extends pudl {
	use pudlMySqlHelper;


	public function __construct($data, $autoconnect=true) {
		//SET INITIAL VALUES
		$this->identifier = '`';

		parent::__construct($data, $autoconnect);
	}



	function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlMySqli($data, $autoconnect);
	}




	public function connect() {
		$auth = $this->auth();

		$this->mysqli = mysqli_init();
		$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		$ok = @$this->mysqli->real_connect(
			'p:'.$auth['server'],
			$auth['username'],
			$auth['password'],
			$auth['database']
		);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		if (empty($ok)) {
			$ok = @$this->mysqli->real_connect(
				$auth['server'],
				$auth['username'],
				$auth['password'],
				$auth['database']
			);
		}

		//ATTEMPT TO SET UTF8 CHARACTER SET
		if ($ok) $ok = @$this->mysqli->set_charset('utf8');


		//CANNOT CONNECT - ERROR OUT
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "' . $auth['server'];
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
			if (self::$die) die($error);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->mysqli) return;
		@$this->mysqli->close();
		$this->mysqli = NULL;
	}



	public function escape($value) {
		if (!$this->mysqli) return false;
		return @$this->mysqli->real_escape_string($value);
	}



	protected function process($query) {
		if (!$this->mysqli) return new pudlMySqliResult(false, $this);
		$result = @$this->mysqli->query($query);
		return new pudlMySqliResult($result, $this);
	}



	public function insertId() {
		if (!$this->mysqli) return 0;
		return $this->mysqli->insert_id;
	}



	public function updated() {
		if (!$this->mysqli) return 0;
		return $this->mysqli->affected_rows;
	}



	public function errno() {
		if (!$this->mysqli) return @mysqli_errno(NULL);
		return $this->mysqli->errno;
	}



	public function error() {
		if (!$this->mysqli) return @mysqli_error(NULL);
		return $this->mysqli->error;
	}



	public function connectErrno() {
		if (!$this->mysqli) return @mysqli_connect_errno();
		return $this->mysqli->connect_errno;
	}



	public function connectError() {
		if (!$this->mysqli) return @mysqli_connect_error();
		return $this->mysqli->connect_error;
	}



	protected $mysqli = NULL;
}
