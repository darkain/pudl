<?php


require_once(is_owner(__DIR__.'/pudlSqliteResult.php'));


class pudlSqlite extends pudl {
	public function __construct($data=[], $autoconnect=true) {

		if (!pudl_array($data)) $data = [$data];
		if (empty($data['database'])) {
			$data['database'] = empty($data[0]) ? 'sqlite.db' : $data[0];
		}

		if (!empty($data['identifier'])) {
			$this->identifier = $data['identifier'];
		}

		parent::__construct($data, $autoconnect);
	}



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data=[], $autoconnect=true) {
		return new pudlSqlite($data, $autoconnect);
	}


	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('sqlite3');

		//Create Sqlite3 object instance
		$this->connection = new SQLite3($auth['database']);

		//Cannot connect - Error out
		if (empty($this->connection)) {
			throw new pudlException(
				'Unable to open Sqlite database file: ' . $auth['database'],
				PUDL_X_CONNECTION
			);
		}

		//Set a busy timeout for Sqlite to 5 seconds
		$this->connection->busyTimeout(5000);
	}


	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@$this->connection->close();
		$this->connection = NULL;
	}



	public function escape($str) {
		if (!$this->connection) return false;
		return @$this->connection->escapeString($str);
	}


	protected function process($query) {
		if (!$this->connection) return new pudlSqliteResult($this);
		$result = $this->connection->query($query);
		return new pudlSqliteResult($this, $result);
	}


	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->lastInsertRowID();
	}


	public function updated() {
		if (!$this->connection) return 0;
		return $this->connection->changes();
	}


	public function errno() {
		if (!$this->connection) return 0;
		return $this->connection->lastErrorCode();
	}


	public function error() {
		if (!$this->connection) return '';
		return $this->connection->lastErrorMsg();
	}


	public function upsert($table, $data, $idcol=false) {
		return $this->replace($table, $data);
	}


}
