<?php


require_once('pudl.php');
require_once('pudlMySqliResult.php');
require_once('pudlMySqlHelper.php');



class pudlMySqli extends pudl {
	use pudlMySqlHelper;


	public function __construct($username, $password, $database, $server='localhost', $prefix=false) {
		parent::__construct();

		//SET INITIAL VALUES
		$this->limit	= true;
		$this->escstart	= '`';
		$this->escend	= '`';
		$this->prefix	= $prefix;

		//STORE IN SECURED AREA HIDDEN FROM VAR_DUMP/VAR_EXPORT
		$this->auth([
			'username'	=> $username,
			'password'	=> $password,
			'database'	=> $database,
		]);

		//CONNECT TO THE SERVER
		$this->server = $server;
		if ($server) $this->connect();
	}



	function __destruct() {
		parent::__destruct();
		$this->disconnect();
	}



	public static function instance($data) {
		$username	= empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password	= empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database	= empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server		= empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];

		$db = new pudlMySqli($username, $password, $database, $server, $prefix);
		if (!empty($data['pudl_redis'])) $db->redis($data['pudl_redis']);
		return $db;
	}




	public function connect() {
		$auth = $this->auth();

		$this->mysqli = mysqli_init();
		$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		$ok = @$this->mysqli->real_connect(
			'p:'.$this->server,
			$auth['username'],
			$auth['password'],
			$auth['database']
		);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		if (empty($ok)) {
			$ok = @$this->mysqli->real_connect(
				$this->server,
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
			$error .= 'Unable to connect to database server "';
			$error .= $this->server;
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
			if (self::$die) die($error);
		}
	}



	public function disconnect() {
		parent::disconnect();
		if (!$this->mysqli) return;
		@$this->mysqli->close();
		$this->mysqli = false;
	}



	public function escape($value) {
		return @$this->mysqli->real_escape_string($value);
	}



	protected function process($query) {
		$result = @$this->mysqli->query($query);
		return new pudlMySqliResult($result, $this);
	}



	public function insertId() {
		return $this->mysqli->insert_id;
	}



	public function updated() {
		return $this->mysqli->affected_rows;
	}



	public function errno() {
		return $this->mysqli->errno;
	}



	public function error() {
		return $this->mysqli->error;
	}



	public function connectErrno() {
		return $this->mysqli->connect_errno;
	}



	public function connectError() {
		return $this->mysqli->connect_error;
	}



	protected $mysqli = NULL;
}
