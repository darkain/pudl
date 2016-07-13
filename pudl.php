<?php

require_once('pudlHelpers.php');
require_once('pudlStringResult.php');
require_once('pudlCacheResult.php');
require_once('pudlAuth.php');
require_once('pudlAlias.php');
require_once('pudlRedis.php');
require_once('pudlQuery.php');
require_once('pudlUnion.php');
require_once('pudlTable.php');
require_once('pudlSelect.php');
require_once('pudlInsert.php');
require_once('pudlUpdate.php');
require_once('pudlDelete.php');
require_once('pudlCompare.php');
require_once('pudlDynamic.php');
require_once('pudlCallback.php');
require_once('pudlTransaction.php');



abstract class pudl {
	use pudlAuth;
	use pudlAlias;
	use pudlRedis;
	use pudlQuery;
	use pudlUnion;
	use pudlTable;
	use pudlSelect;
	use pudlInsert;
	use pudlUpdate;
	use pudlDelete;
	use pudlCompare;
	use pudlDynamic;
	use pudlCallback;
	use pudlTransaction;



	public function __construct($data, $autoconnect=true) {
		//SANITIZE DATA
		$data['username']	= empty($data['username'])	? ''			: $data['username'];
		$data['password']	= empty($data['password'])	? ''			: $data['password'];
		$data['database']	= empty($data['database'])	? ''			: $data['database'];
		$data['server']		= empty($data['server'])	? 'localhost'	: $data['server'];
		$data['prefix']		= empty($data['prefix'])	? false			: $data['prefix'];
		$data['salt']		= empty($data['salt'])		? ''			: $data['salt'];

		//SET INITIAL DATA
		$this->microtime	= microtime(true);
		$this->time			= (int) $this->microtime;
		$this->prefix		= $data['prefix'];

		//STORE CREDENTIALS IN SECURED AREA HIDDEN FROM VAR_DUMP/VAR_EXPORT
		$this->_auth($data);

		//INITIALIZE REDIS CONNECTION
		if (!empty($data['redis'])) {
			$this->redis($data['redis']);
		} else {
			$this->redis = new pudlVoid;
		}

		if ($autoconnect) $this->connect();
	}



	function __destruct() {
		$this->_auth(NULL);
	}



	public function __invoke($query) {
		//SELEX
		if (is_array($query)) {
			return $this->selex($query);
		}


		//UNIONS
		if ($this->inUnion()) {
			$this->union[] = $query;
			return true;
		}


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) $microtime = microtime(true);


		//STORE THE QUERY STRING LOCALLY
		$this->query = $query;


		//STORE TRANSACTION INFORMATION
		if (is_array($this->transaction)) $this->transaction[] = $query;


		//RETURN A STRING
		$string = end($this->string);
		if ($string === true) {
			$result = new pudlStringResult($this, $string);
			array_pop($this->string);


		//RETURN A SUBQUERY STRING
		} else if ($string !== false) {
			$this->query = '(' . $this->query . ')';
			$result = new pudlStringResult($this, $string);
			array_pop($this->string);
			return $result;


		//CACHE THE QUERY IN REDIS
		} else if ($this->cache  &&  is_object($this->redis)  &&  !($this->redis instanceof pudlVoid)) {
			$this->stats['total']++;
			try {
				$hash = $this->cachekey;
				if (empty($hash)) $hash = hash('sha512', $this->salt().$query, true);
				$data = $this->redis->get("pudl:$hash");

				if ($data === false) {
					$result = $this->missed($query);
					if (!$this->error()  &&  !$result->error()) {
						$data = $result->complete();
						$this->redis->set("pudl:$hash", $data, $this->cache);
						$result = new pudlCacheResult($data, $this, $hash);
					}

				} else if (is_array($data)  &&  !empty($data)) {
					$this->stats['hits']++;
					$result = new pudlCacheResult($data, $this, $hash);
				}
			} catch (RedisException $e) {}

			if (empty($result)  ||  !($result instanceof pudlResult)) {
				$result = $this->missed($query);
			}


		//PROCESS THE QUERY NORMALLY
		} else {
			$this->stats['total']++;
			$this->stats['queries']++;
			$result = $this->process($query);
		}


		//RESET CACHE INFORMATION FOR NEXT QUERY
		$this->cache = $this->cachekey = false;


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) {
			$bench = $this->bench;
			$diff = round(microtime(true)-$microtime, 6);
			$bench($query, $diff, $this);
		}


		//ERROR REPORTING
		if (!empty($this->errno())  &&  $this->debug !== false) {
			$debug = $this->debug;
			$debug($this, $result);
		}

		return $result;
	}



	public static function __callStatic($name, $arguments) {
		$value = new pudlFunction();
		$name = '_' . strtoupper($name);
		$value->$name = $arguments;
		return $value;
	}




	abstract protected function process($query);

	abstract public function errno();
	abstract public function error();



	public function wait($wait=true)	{ return $this; }
	public function sync()				{ return $this; }
	public function connect()			{}



	public function disconnect($trigger=true) {
		if ($trigger) $this->trigger('disconnect');
	}



	public function query($query=false) {
		return ($query === false) ? $this->query : $this($query);
	}




	public function listFields($table) {
		if (!is_array($table)) $table = [$table];
		$return = [];
		foreach ($table as $t) {
			$result = $this('SHOW COLUMNS FROM ' . $this->_table($t));
			while ($data = $result->row()) $return[$data['Field']] = $data;
			$result->free();
		}
		return $return;
	}



	public function explain($query) {
		$return = '';
		$result = $this('EXPLAIN ' . $query);
		if ($result instanceof pudlStringResult) return $result;
		while ($data = $result->row()) $return .= print_r($data, true);
		$result->free();
		return $return;
	}



	public function idExists($table, $col, $id) {
		return $this->cellId($table, $col, $col, $id) !== false;
	}



	public function clauseExists($table, $clause) {
		return $this->cell($table, true, $clause) !== false;
	}



	public function count($table, $clause='1') {
		$return = $this->cell($table, 'COUNT(*)', $clause);
		if ($return instanceof pudlStringResult) return $return;
		return $return === false ? $return : (int) $return;
	}



	public function countId($table, $column, $id) {
		return $this->count($table, $this->_clauseId($column,$id));
	}



	public function countGroup($table, $clause, $group, $col=false) {
		if ($col === false) $col = $group;

		$query =	'SELECT ' .
					$this->_cache() .
					'COUNT(*) FROM (' .
					'SELECT ' .
					$this->_column($col) .
					$this->_tables($table) .
					$this->_clause($clause) .
					$this->_group($group) .
					') ' .
					$this->_alias();

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->completeCell();
		return $return === false ? $return : (int) $return;
	}



	public function found() {
		$result = $this('SELECT FOUND_ROWS()');
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->completeCell();
		return $return === false ? $return : (int) $return;
	}



	public function listItems($type, $like=false, $limit=false, $offset=false) {
		$query = 'SHOW ' . $type;
		if (!empty($like)) $query .= ' LIKE ' . $this->_value($like);
		$query .= $this->_limit($limit, $offset);
		$result = $this($query);

		if ($result instanceof pudlStringResult) return $result;

		$return = [];
		while ($data = $result->row()) {
			$return[reset($data)] = end($data);
		}

		$result->free();
		return $return;
	}



	public function globals($like=false, $limit=false, $offset=false) {
		return $this->listItems('GLOBAL STATUS', $like, $limit, $offset);
	}


	public function variables($like=false, $limit=false, $offset=false) {
		return $this->listItems('VARIABLES', $like, $limit, $offset);
	}


	public function status($like=false, $limit=false, $offset=false) {
		return $this->listItems('STATUS', $like, $limit, $offset);
	}




	public function debugger($debugger) {
		if (!is_callable($debugger)) {
			trigger_error('Function does not exist for pudl::debugger', E_USER_ERROR);
		}
		$this->debug = $debugger;
	}



	public function benchmark($benchmark) {
		$this->bench = $benchmark;
	}



	public function time($source=false) {
		if ($source === false) {
			if (is_object($this->time)) return $this->time->time();
			return $this->time;
		}

		$this->time = $source;
	}



	public function microtime($source=false) {
		if ($source === false) {
			if (is_object($this->microtime)) return $this->microtime->microtime();
			return $this->microtime;
		}

		$this->microtime = $source;
	}



	public function server() {
		$auth = $this->auth();
		return $auth['server'];
	}



	public function isString() {
		return end($this->string);
	}



	public function string() {
		$this->string[] = true;
		return $this;
	}



	public function in() {
		$this->string[] = ' IN ';
		return $this;
	}



	public function notIn() {
		$this->string[] = ' NOT IN ';
		return $this;
	}



	public static function column($column, $value=false) {
		if (func_num_args() === 2) return new pudlColumn($column, $value);
		return new pudlColumn($column);
	}



	public static function raw($value) {
		return new pudlRaw($value);
	}



	public static function text($text) {
		return new pudlText($text);
	}



	public static function jsonEncode($data) {
		return @json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT);
	}



	public static function jsonDecode($data) {
		return @json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
	}




	private			$debug			= false;
	private			$bench			= false;
	private			$query			= false;
	private			$time			= 0;
	private			$microtime		= 0.0;
	protected		$string			= [];
	public static	$version		= 'PUDL 2.1.0';

}
