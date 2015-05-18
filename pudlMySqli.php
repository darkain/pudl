<?php


require_once('pudl.php');
require_once('pudlMySqliResult.php');


class pudlMySqli extends pudl {
	public function __construct($username, $password, $database, $servers='localhost', $prefix=false) {
		parent::__construct();

		//Set initial values
		$this->limit	= true;
		$this->escstart	= '`';
		$this->escend	= '`';
		$this->username	= $username;
		$this->password	= $password;
		$this->database	= $database;
		$this->prefix	= $prefix;

		//Ensure we're dealing with an array, and verify they're online
		$this->pool = $servers;
		if (!is_array($this->pool)) $this->pool = array($this->pool);
		$this->pool = $this->onlineServers($this->pool);
		shuffle($this->pool);

		$this->connect($username, $password, $database, $this->pool);
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

		if (!empty($data['pudl_redis'])) {
			if (is_object($data['pudl_redis'])) {
				$db->redis = $data['pudl_redis'];
			} else if (class_exists('Redis')) {
				$db->redis = new Redis();
				if (@$db->redis->connect($data['pudl_redis'], -1, 1)) {
					$db->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
				} else {
					$db->redis = false;
				}
			}
		}

		return $db;
	}



	public function connect() {
		foreach ($this->pool as $server) {
			$this->mysqli = mysqli_init();

			//Set connection timeout to 10 second if we're in a clsuter
			if (count($this->pool)>1) $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);

			//Attempt to create a persistant connection
			$ok = @$this->mysqli->real_connect("p:$server", $this->username, $this->password, $this->database);

			//Attempt to create a non-persistant connection
			if (empty($ok)) {
				$ok = @$this->mysqli->real_connect($server, $this->username, $this->password, $this->database);
			}

			//Attempt to set UTF-8 character set
			if ($ok  &&  $this->mysqli->set_charset('utf8')) {
				$this->server = $server;
				break;
			} else { $ok = false; }

			//Okay, maybe we're not
			$this->offlineServer($server);
		}


		//Cannot connect - Error out
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "';
			$error .= implode(', ', $this->pool);
			$error .= '" with the username: "' . $this->username;
			$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
			if (self::$die) die($error);
		}
	}



	public function reconnect() {
		if (empty($this->pool)) return;

		array_shift($this->pool);

		if (empty($this->pool)) {
			if (self::$die) die('No more servers available in server pool');
			return;
		}

		$this->connect();
	}



	public function disconnect() {
		parent::disconnect();
		if (!$this->mysqli) return;
		@$this->mysqli->close();
		$this->mysqli = false;
	}



	public function safe($str) {
		return @$this->mysqli->real_escape_string($str);
	}



	protected function process($query) {
		$result = @$this->mysqli->query($query);

		switch ($this->errno()) {
			case 0: break; //NO ERRORS!

			//An error occurred with this node, so let's connect to a different node in the cluster
			case 1047: //Unknown command
			case 1053: //Server shutdown in progress
			case 2006: //MySQL server has gone away
			case 2062: //Read timeout is reached
				$this->reconnect();
				if ($this->inTransaction()) {
					$result = $this->retryTransaction();
				} else {
					$result = @$this->mysqli->query($query);
				}
			break;

			//A deadlocking condition occurred, simple, let's retry!
			case 1205: //Lock wait timeout exceeded; try restarting transaction
			case 1213: //Deadlock found when trying to get lock; try restarting transaction
				if ($this->inTransaction()) {
					usleep(50000);
					$result = $this->retryTransaction();

				//It is possible to deadlock with a single query
				//This condition is simple: just retry the query!
				} else {
					usleep(25000);
					$result = @$this->mysqli->query($query);

					//If we deadlock again, try once more but wait longer
					if ($this->errno() == 1205  ||  $this->errno() == 1213) {
						usleep(50000);
						$result = @$this->mysqli->query($query);
					}
				}
			break;
		}

		return new pudlMySqliResult($result, $query);
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


	public static function aesKey($key) {
		$aes = str_repeat(chr(0), 16);
		$len = strlen($key);
		for ($i=0; $i<$len; $i++) {
			$aes[$i%16] = $aes[$i%16] ^ $key[$i];
		}
		return $aes;
	}


	public static function aesDecrypt($data, $key) {
		return rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				self::aesKey($key),
				pack('H*', $data),
				MCRYPT_MODE_ECB,
				''
			),
			"\0"
		);
	}



	public static function dieOnError($die) {
		self::$die = $die;
	}



	protected function _cache() {
		if (is_array($this->union))	return '';
		if (!$this->cache)			return '';
		if (!$this->redis)			return 'SQL_CACHE ';
		return 'SQL_NO_CACHE ';
	}



	private $mysqli;
	private $pool;
	private $username;
	private $password;
	private $database;
	private static $die=true;
}
