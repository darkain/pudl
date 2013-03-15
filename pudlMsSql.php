<?php


require_once('pudl.php');
require_once('pudlMsSqlResult.php');


class pudlMsSql extends pudl {
	public function __construct($username, $password, $database, $server='localhost') {
		parent::__construct();

		$this->escstart = '[';
		$this->escend = ']';
		$this->top = true;

		$this->mssql  = false;
		$this->mssql  = @mssql_pconnect($server, $username, $password);

		if (!$this->mssql) {
			$this->mssql = @mssql_connect($server, $username, $password);
		}

		if (!$this->mssql) {
			$error  = "<br />\r\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\r\nError " . mssql_errno() . ': ' . mssql_error(); 
			die($error);
		}

		$selected = false;
		$selected = @mssql_select_db($database, $this->mssql);
		if (!$selected) {
			$error  = "<br />\r\n";
			$error .= 'Unable to select database : "' . $database;
			$error .= "\"<br />\r\nError " . mssql_errno() . ': ' . mssql_error(); 
			die($error);
		}
	}



	public static function instance($data) {
		$username = empty($data['pudl_username']) ? '' : $data['pudl_username'];
		$password = empty($data['pudl_password']) ? '' : $data['pudl_password'];
		$database = empty($data['pudl_database']) ? '' : $data['pudl_database'];
		$server   = empty($data['pudl_server']) ? 'localhost' : $data['pudl_server'];
		return new pudlMsSql($username, $password, $database, $server);
	}


	public function safe($str) {
		if ( is_numeric($str) ) return $str;

		$encode = array(
			'/%0[0-8bcef]/',            // url encoded 00-08, 11, 12, 14, 15
			'/%1[0-9a-f]/',             // url encoded 16-31
			'/[\x00-\x08]/',            // 00-08
			'/\x0b/',                   // 11
			'/\x0c/',                   // 12
			'/[\x0e-\x1f]/'             // 14-31
		);
		foreach ($encode as $regex) $str = preg_replace($regex, '', $str);

		return str_replace("'", "''", $str );;
	}


	protected function process($query) {
		$result = false;
		$result = @mssql_query($query, $this->mssql);
		return new pudlMsSqlResult($result, $query);
	}

	
	public function insertId() {
		$result = @mssql_query('SELECT @@identity', $this->mssql);
		if ($result === false) return false;
		$return = mssql_result($result, 0, 0);
		mssql_free_result($result);
		return $return;
	}


	public function updated() {
		$result = mssql_query('SELECT @@rowcount', $this->mssql); 
		if ($result === false) return false;
		$return = mssql_result($result, 0, 0);
		mssql_free_result($result);
		return $return;
	}

	
	public function errno() {
		return 0;	//TODO: find a solution for this!
	}
	
	
	public function error() {
		$return = false;
		$return = @mssql_get_last_message();
		return $return;
	}


	private $mssql;
}
