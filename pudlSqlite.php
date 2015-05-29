<?php


require_once('pudl.php');
require_once('pudlSqliteResult.php');


class pudlSqlite extends pudl {
	public function __construct($filename, $prefix=false) {
		parent::__construct();

		//Set initial values
		$this->limit	= true;
		$this->escstart	= '`';
		$this->escend	= '`';
		$this->prefix	= $prefix;

		//Create Sqlite3 object instance
		$this->sqlite = new SQLite3($filename);

		//Cannot connect - Error out
		if (empty($this->sqlite)) {
			die('Unable to open Sqlite database file: ' . $filename);
		}

		//Set a busy timeout for Sqlite to 5 seconds
		$this->sqlite->busyTimeout(5000);
	}



	function __destruct() {
		parent::__destruct();
		$this->disconnect();
	}



	public static function instance($data) {
		$prefix = empty($data['pudl_prefix']) ? false : $data['pudl_prefix'];

		$database = 'sqlite.db';
		if (is_string($data)) {
			$database = $data;
		} else if (is_array($data)) {
			if (!empty($data['pudl_database'])) $database = $data['pudl_database'];
			else if (!empty($data[0])) $database = $data[0];
		}

		return new pudlSqlite($database, $prefix);
	}



	public function disconnect() {
		parent::disconnect();
		if (!$this->sqlite) return;
		@$this->sqlite->close();
		$this->sqlite = false;
	}



	public function safe($str) {
		return @$this->sqlite->escapeString($str);
	}


	protected function process($query) {
		$result = $this->sqlite->query($query);
		return new pudlSqliteResult($result, $this);
	}


	public function insertId() {
		return $this->sqlite->lastInsertRowID();
	}


	public function updated() {
		return $this->sqlite->changes();
	}


	public function errno() {
		return $this->sqlite->lastErrorCode();
	}


	public function error() {
		return $this->sqlite->lastErrorMsg();
	}



	private $sqlite;
}
