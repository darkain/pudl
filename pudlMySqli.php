<?php


require_once('pudl.php');
require_once('pudlMySqliResult.php');


class pudlMySqli extends pudl {
	public function __construct($username, $password, $database, $server='localhost', $prefix=false) {
		parent::__construct();

		$this->limit	= true;
		$this->escstart	= '`';
		$this->escend	= '`';
		$this->prefix	= $prefix;

		$this->mysqli = false;
		$this->mysqli = @new mysqli("p:$server", $username, $password, $database);

		if (empty($this->mysqli)  ||  $this->mysqli->connect_error) {
			$this->mysqli = @new mysqli($server, $username, $password, $database);
		}

		if (empty($this->mysqli)  ||  $this->mysqli->connect_error) {
			$error  = "<br />\r\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\r\nError " . $this->mysqli->connect_errno . ': ' . $this->mysqli->connect_error; 
			die($error);
		}

		if (!$this->mysqli->set_charset('utf8')) {
			die('Error loading character set utf8: ' . $mysqli->error);
		}
	}



	public static function instance($data) {
		$username	= empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password	= empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database	= empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server		= empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];
		return new pudlMySqli($username, $password, $database, $server, $prefix);
	}


	public function safe($str) {
		$return = false;
		$return = @$this->mysqli->real_escape_string($str);
		return $return;
	}


	protected function process($query) {
		$result = false;
		$result = @$this->mysqli->query($query);
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
