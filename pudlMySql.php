<?php


require_once('pudl.php');
require_once('pudlMySqlResult.php');


class pudlMySql extends pudl {
	public function __construct($username, $password, $database, $server='localhost', $prefix=false) {
		parent::__construct();

		$this->limit	= true;
		$this->escstart	= '`';
		$this->escend	= '`';
		$this->prefix	= $prefix;

		$this->mysql	= @mysql_pconnect($server, $username, $password);

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



	public static function instance($data) {
		$username	= empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password	= empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database	= empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server		= empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];
		return new pudlMySql($username, $password, $database, $server, $prefix);
	}


	public function safe($value) {
		if (is_int($value)  ||  is_float($value)) return $value;
		return @mysql_real_escape_string($value, $this->mysql);
	}


	protected function process($query) {
		$result = @mysql_query($query, $this->mysql);
		return new pudlMySqlResult($result, $query);
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


	private $mysql;
}
