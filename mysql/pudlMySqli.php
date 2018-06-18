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



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlMySqli($data, $autoconnect);
	}




	public function connect() {
		$auth = $this->auth();

		$this->connection = mysqli_init();
		$this->connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);
		$this->connection->options(MYSQLI_OPT_READ_TIMEOUT, 10);

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

		//CANNOT CONNECT, BUT BAILING OUT OF SCRIPT IS DISABLED
		if (!self::$die) return false;

		//CANNOT CONNECT - ERROR OUT
		$error  = "<br />\n";
		$error .= 'Unable to connect to database server "' . $auth['server'];
		$error .= '" with the username: "' . $auth['username'];
		$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
		throw new pudlException($error, PUDL_X_CONNECTION);
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@$this->connection->close();
		$this->connection = NULL;
	}



	public function escape($value) {
		if (!$this->connection) return false;
		return @$this->connection->real_escape_string($value);
	}



	protected function process($query) {
		if (!$this->connection) return new pudlMySqliResult($this);

		$result = @$this->connection->query($query);

		return new pudlMySqliResult($this,
			$result instanceof mysqli_result ?
			$result : NULL
		);
	}



	public function _query($query) {
		if (!$this->connection) return false;
		return $this->connection->query($query);
	}



	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->insert_id;
	}



	public function updated() {
		if (!$this->connection) return 0;
		return $this->connection->affected_rows;
	}



	public function errno() {
		if (!$this->connection) return @mysqli_errno(NULL);
		return $this->connection->errno;
	}



	public function error() {
		if (!$this->connection) return @mysqli_error(NULL);
		return $this->connection->error;
	}



	public function connectErrno() {
		if (!$this->connection) return @mysqli_connect_errno();
		return $this->connection->connect_errno;
	}



	public function connectError() {
		if (!$this->connection) return @mysqli_connect_error();
		return $this->connection->connect_error;
	}
}
