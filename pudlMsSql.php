<?php


require_once('pudl.php');
require_once('pudlMsSqlResult.php');


class pudlMsSql extends pudl {
	public function __construct($username, $password, $database, $server='localhost', $prefix=false) {
		parent::__construct();

		$this->escstart	= '[';
		$this->escend	= ']';
		$this->top		= true;
		$this->prefix	= $prefix;

		$this->mssql = false;
		$this->mssql = @mssql_pconnect($server, $username, $password);

		if (!$this->mssql) {
			$this->mssql = @mssql_connect($server, $username, $password);
		}

		if (!$this->mssql) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			die($error);
		}

		if (!@mssql_select_db($database, $this->mssql)) {
			$error  = "<br />\n";
			$error .= 'Unable to select database : "' . $database;
			$error .= "\"<br />\nError " . $this->errno() . ': ' . $this->error();
			die($error);
		}
	}



	public static function instance($data) {
		$username	= empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password	= empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database	= empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server		= empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];
		return new pudlMsSql($username, $password, $database, $server, $prefix);
	}



	protected function process($query) {
		$result = @mssql_query($query, $this->mssql);
		return new pudlMsSqlResult($result, $this);
	}


	public function insertId() {
		$result = @mssql_query('SELECT @@identity', $this->mssql);
		if ($result === false) return false;
		$return = mssql_result($result, 0, 0);
		mssql_free_result($result);
		return $return;
	}


	public function updated() {
		$result = @mssql_query('SELECT @@rowcount', $this->mssql);
		if ($result === false) return false;
		$return = mssql_result($result, 0, 0);
		mssql_free_result($result);
		return $return;
	}


	public function errno() {
		return (int) !empty($this->error());
	}


	public function error() {
		return @mssql_get_last_message();
	}


	private $mssql;
}
