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
		$this->prefix	= $prefix;

		//Ensure we're dealing with an array, and verify they're online
		if (!is_array($servers)) $servers = array($servers);
		$servers = $this->onlineServers($servers);
		shuffle($servers);

		//Set connection timeout to 10 second if we're in a clsuter
		$this->mysqli = mysqli_init();
		if (count($servers)>1) $this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, 10);


		foreach ($servers as &$server) {
			//Attempt to create a persistant connection
			$ok = @$this->mysqli->real_connect("p:$server", $username, $password, $database);

			//Attempt to create a non-persistant connection
			if (empty($ok)) {
				$ok = @$this->mysqli->real_connect($server, $username, $password, $database);
			}

			//Attempt to set UTF-8 character set
			if ($ok  &&  $this->mysqli->set_charset('utf8')) {
				$this->server = $server;
				break;
			} else { $ok = false; }

			//Okay, maybe we're not
			$this->offlineServer($server);
		} unset($server);


		//Cannot connect - Error out
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "';
			$error .= implode(', ', $servers);
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\nError " . $this->mysqli->connect_errno . ': ' . $this->mysqli->connect_error; 
			die($error);
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

		$db = new pudlMySqli($username, $password, $database, $server, $prefix);

		if (!empty($data['pudl_redis'])) {
			if (is_object($data['pudl_redis'])) {
				$db->redis = $data['pudl_redis'];
			} else {
				$db->redis = new Redis();
				$db->redis->connect($data['pudl_redis'], -1, 1);
				$db->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
			}
		}

		return $db;
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

		//If we deadlock, then retry!
		//1205 = deadlock wait timeout : 1213 = deadlocked
		if ($this->errno() == 1205  ||  $this->errno() == 1213) {
			if ($this->inTransaction()) {
				usleep(30000);
				$result = $this->retryTransaction();

				//It is possible to deadlock with a single query
				//This condition is simple: just retry the query!
			} else {
				usleep(15000);
				$result = @$this->mysqli->query($query);

				//If we deadlock again, try once more but wait longer
				if ($this->errno() == 1205  ||  $this->errno() == 1213) {
					usleep(30000);
					$result = @$this->mysqli->query($query);
				}
			}
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


	private $mysqli;
}
