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

		$this->mysql  = false;
		$this->mysql  = @mysql_pconnect($server, $username, $password);

		if (!$this->mysql) {
			$this->mysql = @mysql_connect($server, $username, $password);
		}

		if (!$this->mysql) {
			$error  = "<br />\r\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\r\nError " . mysql_errno() . ': ' . mysql_error(); 
			die($error);
		}

		$selected = false;
		$selected = @mysql_select_db($database, $this->mysql);
		if (!$selected) {
			$error  = "<br />\r\n";
			$error .= 'Unable to select database : "' . $database;
			$error .= "\"<br />\r\nError " . mysql_errno() . ': ' . mysql_error(); 
			die($error);
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


	public function safe($str) {
		$return = false;
		$return = @mysql_real_escape_string($str, $this->mysql);
		return $return;
	}


	protected function process($query) {
		$result = false;
		$result = @mysql_query($query, $this->mysql);
		return new pudlMySqlResult($result, $query);
	}

	
	public function insertId() {
		$return = false;
		$return = @mysql_insert_id($this->mysql);
		return $return;
	}


	public function updated() {
		$return = false;
		$return = @mysql_affected_rows($this->mysql);
		return $return;
	}

	
	public function errno() {
		$return = false;
		$return = @mysql_errno($this->mysql);
		return $return;
	}
	
	
	public function error() {
		$return = false;
		$return = @mysql_error($this->mysql);
		return $return;
	}


	private $mysql;
}
