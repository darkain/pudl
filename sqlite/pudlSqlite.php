<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlSqliteResult.php'));



class		pudlSqlite
	extends	pudl {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options=[]) {

		// PRE-PROCESS OPTIONS
		$tmp = self::_options($options);

		// IF JSON FAILED, STRING IS A FILE NAME
		if (!pudl_array($tmp)) {
			$options = [$options];
		}

		// IF DATABASE NOT SPECIFIED, AUTO DETECT IT ANOTHER WAY
		if (empty($options['database'])) {
			$options['database']	= empty($options[0])
									? 'sqlite.db'
									: $options[0];
		}

		// ALLOW CUSTOM IDENTIFIER
		if (!empty($options['identifier'])) {
			$this->identifier = $options['identifier'];
		}

		// INITIALIZE PUDL OBJECT
		parent::__construct($options);
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// OPEN THE SQLITE DATABASE FILE
	// http://php.net/manual/en/sqlite3.construct.php
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();


		// Verify we have the Sqlite3 PHP extension installed
		pudl_require_extension('sqlite3');


		// Set READ-ONLY / READ-WRITE access
		$flags	= $auth['readonly']
				? SQLITE3_OPEN_READONLY
				: SQLITE3_OPEN_READWRITE;


		// Create Sqlite3 object instance
		try {
			if ($auth['server'] instanceof SQLite3) {
				$this->connection = $auth['server'];

			} else {
				$this->connection = new SQLite3(
					$auth['database'],
					SQLITE3_OPEN_CREATE | $flags,
					$auth['key']
				);
			}

			// Enable exceptions instead of errors/warnings
			// https://www.php.net/manual/en/sqlite3.enableexceptions.php
			$this->connection->enableExceptions(true);

		// Convert PHP exception to PUDL exception
		} catch (Exception $e) {
			$error = error_get_last();

			throw new pudlConnectionException($this,
				'Unable to open to Sqlite database file ' .
				'"' . $auth['database'] . '"' .
				"\nError: " . $error['message']
			);
		}


		// Set a busy timeout for Sqlite to 'timeout' seconds
		// http://php.net/manual/en/sqlite3.busytimeout.php
		$this->connection->busyTimeout($auth['timeout'] * 1000);
	}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT FROM SQLITE SERVICE (UNLOAD/RELEASE FILE HANDLE)
	// http://php.net/manual/en/sqlite3.close.php
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@$this->connection->close();
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE A VALUE
	// http://php.net/manual/en/sqlite3.escapestring.php
	////////////////////////////////////////////////////////////////////////////
	public function escape($str) {
		if (!$this->connection) return false;
		return @$this->connection->escapeString($str);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT DATA TO BLOB
	////////////////////////////////////////////////////////////////////////////
	protected function blob($value) {
		return "x'" . bin2hex($value) . "'";
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS A QUERY
	// http://php.net/manual/en/sqlite3.query.php
	// http://php.net/manual/en/class.sqlite3result.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlSqliteResult($this);

		try {
			$this->connection->enableExceptions(true);
			$result = $this->connection->query($query);
		} catch (Exception $e) {
			$result = NULL;
		}

		return new pudlSqliteResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST AUTO INCREMENT NUMBER FROM INSERTED DATA
	// http://php.net/manual/en/sqlite3.lastinsertrowid.php
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->lastInsertRowID();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS UPDATED BY THE LAST QUERY
	// http://php.net/manual/en/sqlite3.changes.php
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return 0;
		return $this->connection->changes();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE SQLITE VERSION NUMBER
	// http://php.net/manual/en/sqlite3.version.php
	////////////////////////////////////////////////////////////////////////////
	public function version() {
		if (!$this->connection) return NULL;
		$version = $this->connection->version();
		return $version['versionString'];
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR NUMBER
	// http://php.net/manual/en/sqlite3.lasterrorcode.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!$this->connection) return 0;
		return $this->connection->lastErrorCode();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR MESSAGE
	// http://php.net/manual/en/sqlite3.lasterrormsg.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return '';
		return $this->connection->lastErrorMsg();
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UPSERT PART OF THE QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _upsert($data) {
		if (!pudl_array($data)  ||  empty($data)) return false;

		return	' ON CONFLICT (' .
				$this->identifier(key($data)) .
				') DO UPDATE SET ' .
				$this->_update($data);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A LIST OF FIELDS FOR THE GIVEN $TABLE
	////////////////////////////////////////////////////////////////////////////
	protected function _fields($table) {
		$list = $this(
			'pragma table_info(' . $this->_table($table) . ')'
		)->complete();

		$columns = [];

		foreach ($list as $item) {
			$columns[] = [
				'field'		=> $item['name'],
				'type'		=> $item['type'],
				'null'		=> empty($item['notnull']) ? 'YES' : 'NO',
				'key'		=> empty($item['pk']) ? '' : 'PRI',
				'default'	=> $item['dflt_value'],
				'extra'		=> '',
				'table'		=> $table,
				'prefix'	=> '',
			];
		}

		return $columns;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LIST OF AVAILABLE TABLES
	////////////////////////////////////////////////////////////////////////////
	public function tables($where=NULL) {
		$output	= [];
		$rows	= $this->rows('sqlite_master', $where);
		foreach ($rows as $row) {
			if ($row['type'] !== 'table') continue;
			$output[] = $row['tbl_name'];
		}
		return $output;
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT COLUMN DEFINITION FROM PUDL STANDARD TO DATABASE SPECIFIC
	// THIS IS OVERWRITTEN IN SOME PUDL DATABASE DRIVERS
	////////////////////////////////////////////////////////////////////////////
	protected function dataType($type) {
		if ($type instanceof pudlType) {
			$type = 'text';

		} else {
			$type = str_replace('UNSIGNED', '', strtoupper($type));
		}

		// CHANGE "INT" TO "INTEGER"
		// FIXES: "AUTOINCREMENT is only allowed on an INTEGER PRIMARY KEY"
		$type = preg_replace('/\bINT\b/i', 'INTEGER', $type);

		return parent::dataType($type);
	}




	////////////////////////////////////////////////////////////////////////////
	// HANDLE TECH COLLATIONS
	// THIS IS OVERWRITTEN IN SOME PUDL DATABASE DRIVERS
	////////////////////////////////////////////////////////////////////////////
	protected function collate($collate) {
		$collate = parent::collate($collate);
		return (substr($collate, -3) === '_ci')
			? 'NOCASE'
			: 'BINARY';
	}



/*
// GET THE "CREATE TABLE" TEXT FOR TABLE session
.schema session

// GET DETAILED INFORMATION OF EACH COLUMN IN session
pragma table_info(session);
*/

}
