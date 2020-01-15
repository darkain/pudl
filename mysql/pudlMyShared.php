<?php


////////////////////////////////////////////////////////////////////////////////
// COMMON FUNCTIONS FOR MYSQL, MYSQLI, AND GALERA OBJECTS
////////////////////////////////////////////////////////////////////////////////
abstract class	pudlMyShared
	extends		pudl {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options) {
		//SET INITIAL VALUES
		$this->identifier = '`';

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
	// EXECUTE A RAW SQL QUERY WITHOUT ADDITIONAL PROCESSING
	////////////////////////////////////////////////////////////////////////////
	protected abstract function _query($query);




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UPSERT PART OF THE QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _upsert($data) {
		return	' ON DUPLICATE KEY UPDATE ' .
				$this->_update($data);
	}




	////////////////////////////////////////////////////////////////////////////
	// SETS THE QUERY CACHING HINT TO THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	protected function _cache() {
		if (!$this->cache)						return '';
		if ($this->isString())					return '';
		if ($this->inUnion())					return '';
		if (!is_object($this->redis))			return 'SQL_CACHE ';
		if ($this->redis instanceof pudlVoid)	return 'SQL_CACHE ';
		return 'SQL_NO_CACHE ';
	}




	////////////////////////////////////////////////////////////////////////////
	// GET FIELD TYPE INFORMATION FOR A PARITUCLAR COLUMN IN A TABLE
	////////////////////////////////////////////////////////////////////////////
	public function fieldType($table, $column) {
		$auth = $this->auth();

		$return = $this->cell('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_TYPE', [
			'TABLE_SCHEMA'	=> $auth['database'],
			'TABLE_NAME'	=> $this->_prefix($table),
			'COLUMN_NAME'	=> $column,
		]);

		if (substr($return, 0, 5) === 'enum(') {
			$return = str_getcsv(substr($return, 5, -1), ',', "'");
		} else if (substr($return, 0, 4) === 'set(') {
			$return = str_getcsv(substr($return, 4, -1), ',', "'");
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET THE QUERY TIMEOUT VALUE - HELPS PREVENT DDOS ATTACKS
	////////////////////////////////////////////////////////////////////////////
	public function timeout($timeout) {
		if (pudl_array($timeout)) {
			if (empty($timeout['timeout'])) return $this;
			$timeout = $timeout['timeout'];
		}

		if (!empty($timeout)) {
			$this->set('max_statement_time', (int)$timeout);
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE "READ ONLY" STATUS FROM THE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function readonly() {
		$ro = $this->variables('read_only');
		if (!pudl_array($ro)  ||  empty($ro)) return false;
		$ro = strtoupper(reset($ro));
		return ($ro === 'ON')  ||  ($ro === '1');
	}




	////////////////////////////////////////////////////////////////////////////
	// SET STRICT SQL COMPATIBILITY MODE
	////////////////////////////////////////////////////////////////////////////
	public function strict() {
		$this->_query(
			"SET @@SQL_MODE = CONCAT(@@SQL_MODE, ',TRADITIONAL')"
		);

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE HOSTNAME AS REPORTED BY THE DATABASE SERVER
	// https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_hostname
	////////////////////////////////////////////////////////////////////////////
	public function hostname() {
		if (!$this->connection) return NULL;
		return $this('SELECT @@hostname')->cell();
	}
}
