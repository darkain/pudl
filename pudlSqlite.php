<?php


require_once('pudl.php');
require_once('pudlSqliteResult.php');


class pudlSqlite extends pudl {
	public function __construct($filename, $prefix=false) {
		parent::__construct();

		$this->sqlite = new SQLite3($filename);

		//Cannot connect - Error out
		if (empty($this->sqlite)) {
			die('Unable to open Sqlite database file: ' . $filename);
		}
	}



	public static function instance($data) {
		$database = 'sqlite.db';
		if (is_string($data)) {
			$database = $data;
		} else if (is_array($data)) {
			if (!empty($data['pudl_database'])) $database = $data['pudl_database'];
			else if (!empty($data[0])) $database = $data[0];
		}
		return new pudlSqlite($database);
	}


	public function safe($str) {
		return @$this->sqlite->escapeString($str);
	}


	protected function process($query) {
		$result = $this->sqlite->query($query);
		return new pudlSqliteResult($result, $query);
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
