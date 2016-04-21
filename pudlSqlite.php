<?php


require_once('pudl.php');
require_once('pudlSqliteResult.php');


class pudlSqlite extends pudl {
	public function __construct($data=[], $autoconnect=true) {

		if (!is_array($data)) $data = [$data];
		if (empty($data['database'])) {
			$data['database'] = empty($data[0]) ? 'sqlite.db' : $data[0];
		}

		if (!empty($data['identifier'])) {
			$this->identifier = $data['identifier'];
		}

		parent::__construct($data, $autoconnect);
	}



	function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data=[], $autoconnect=true) {
		return new pudlSqlite($data, $autoconnect);
	}


	public function connect() {
		$auth = $this->auth();

		//Create Sqlite3 object instance
		$this->sqlite = new SQLite3($auth['database']);

		//Cannot connect - Error out
		if (empty($this->sqlite)) {
			die('Unable to open Sqlite database file: ' . $auth['database']);
		}

		//Set a busy timeout for Sqlite to 5 seconds
		$this->sqlite->busyTimeout(5000);
	}


	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->sqlite) return;
		@$this->sqlite->close();
		$this->sqlite = false;
	}



	public function escape($str) {
		if (!$this->sqlite) return false;
		return @$this->sqlite->escapeString($str);
	}


	protected function process($query) {
		if (!$this->sqlite) return false;
		$result = $this->sqlite->query($query);
		return new pudlSqliteResult($result, $this);
	}


	public function insertId() {
		if (!$this->sqlite) return 0;
		return $this->sqlite->lastInsertRowID();
	}


	public function updated() {
		if (!$this->sqlite) return 0;
		return $this->sqlite->changes();
	}


	public function errno() {
		if (!$this->sqlite) return 0;
		return $this->sqlite->lastErrorCode();
	}


	public function error() {
		if (!$this->sqlite) return '';
		return $this->sqlite->lastErrorMsg();
	}



	private $sqlite = false;
}
