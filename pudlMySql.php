<?php


require_once('pudl.php');
require_once('pudlMySqlResult.php');
require_once('pudlMySqlHelper.php');



class pudlMySql extends pudl {
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
			'pudl_username'	=> $username,
			'pudl_password'	=> $password,
			'pudl_database'	=> $database,
		]);

		//CONNECT TO THE SERVER
		$this->server = $server;
		$this->connect();


		if (!$this->mysql) {
			$this->mysql = @mysql_connect($server, $username, $password);
		}

		if (!$this->mysql) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\nError " . mysql_errno() . ': ' . mysql_error();
			die($error);
		}

		if (!@mysql_select_db($database, $this->mysql)) {
			$error  = "<br />\n";
			$error .= 'Unable to select database : "' . $database;
			$error .= "\"<br />\nError " . mysql_errno() . ': ' . mysql_error();
			die($error);
		}

		if (!@mysql_set_charset('utf8', $this->mysql)) {
			die('Error loading character set utf8: ' . mysql_error());
		}
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

		$db = new pudlMySql($username, $password, $database, $server, $prefix);
		if (!empty($data['pudl_redis'])) $db->redis($data['pudl_redis']);
		return $db;
	}



	public function connect() {
		$auth = $this->auth();

		ini_set('mysql.connect_timeout', 10);

		//ATTEMPT TO CREATE A PERSISTANT CONNECTION
		$this->mysql = @mysql_pconnect(
			$this->server,
			$auth['pudl_username'],
			$auth['pudl_password']
		);

		//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
		if (empty($this->mysql)) {
			$this->mysql = @mysql_connect(
				$this->server,
				$auth['pudl_username'],
				$auth['pudl_password']
			);
		}

		//ATTEMPT TO SELECT THE DATABASE AND SET UTF8 CHARACTER SET
		if ($this->mysql) {
			if (@mysql_select_db($auth['pudl_database'], $this->mysql)) {
				$ok = @mysql_set_charset('utf8', $this->mysql);
			}
		}

		//CANNOT CONNECT - ERROR OUT
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "';
			$error .= $this->server;
			$error .= '" with the username: "' . $auth['pudl_username'];
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			if (self::$die) die($error);
		}
	}



	public function disconnect() {
		parent::disconnect();
		if (!$this->mysql) return;
		@mysql_close($this->mysql);
		$this->mysql = false;
	}



	public function escape($value) {
		return @mysql_real_escape_string($value, $this->mysql);
	}



	protected function process($query) {
		$result = @mysql_query($query, $this->mysql);
		return new pudlMySqlResult($result, $this);
	}



	public function insertId() {
		return @mysql_insert_id($this->mysql);
	}



	public function updated() {
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
