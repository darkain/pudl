<?php


require_once('pudl.php');
require_once('pudlMySqliResult.php');


class pudlMySqli extends pudl {
	public function __construct($username, $password, $database, $server='localhost') {
		parent::__construct();


		$this->mysqli = new mysqli("p:$server", $username, $password, $database);
		if ($this->mysqli->connect_error) {
			$this->mysqli = new mysqli($server, $username, $password, $database);
		}

		if ($this->mysqli->connect_error) {
			$error  = "<br />\r\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\r\nError " . $this->mysqli->connect_errno . ': ' . $this->mysqli->connect_error; 
			die($error);
		}
	}



	public static function instance($data) {
		$username = empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password = empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database = empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server   = empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		return new pudlMySqli($username, $password, $database, $server);
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


	private $mysqli;
}
