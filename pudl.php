<?php



if (!function_exists('is_owner')) {
	/** @suppress PhanRedefineFunction */
	function is_owner($path) { return $path; }
}



require_once(is_owner(__DIR__.'/pudlInclude.inc.php'));



abstract	class	pudl {
			use		pudlCte;
			use		pudlAuth;
			use		pudlJson;
			use		pudlAlias;
			use		pudlRedis;
			use		pudlQuery;
			use		pudlUnion;
			use		pudlTable;
			use		pudlSelect;
			use		pudlInsert;
			use		pudlUpdate;
			use		pudlDelete;
			use		pudlStatic;
			use		pudlCompare;
			use		pudlCounter;
			use		pudlRequire;
			use		pudlCallback;
			use		pudlInternal;
			use		pudlTransaction;




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	// $DATA IS A KEY/VALUE PAIR LIST WITH CONFIGURATION DETAILS
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options) {
		if (!empty($options[0])  &&  $options[0] instanceof pudl) {
			$pudl = $options[0];
			unset($options[0]);
			$options += $pudl->auth();
		}

		//SANITIZE DATA
		//TODO:	create a method to parse and validate every possible $options item
		if (empty($options['username']))	$options['username']	= '';
		if (empty($options['password']))	$options['password']	= '';
		if (empty($options['database']))	$options['database']	= '';
		if (empty($options['server']))		$options['server']		= 'localhost';
		if (empty($options['prefix']))		$options['prefix']		= [];
		if (empty($options['persistent']))	$options['persistent']	= false;
		if (empty($options['key']))			$options['key']			= NULL;
		if (empty($options['salt']))		$options['salt']		= '';
		if (empty($options['timeout']))		$options['timeout']		= 10;
		if (empty($options['readonly']))	$options['readonly']	= false;
		if (empty($options['offline']))		$options['offline']		= false;

		//SET INITIAL DATA
		$this->microtime	= microtime(true);
		$this->time			= (int) $this->microtime;

		//STORE CREDENTIALS IN SECURED AREA HIDDEN FROM VAR_DUMP/VAR_EXPORT
		$this->updateAuth($options);

		//INITIALIZE REDIS CONNECTION
		if (!empty($options['redis'])) {
			$this->redis($options['redis']);
		} else {
			$this->redis	= new pudlVoid;
		}

		//CONNECT TO SERVER
		if (!$options['offline']) $this->connect();
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FORCE ERASE SENSITIVE CONFIGURATION DATA
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->_auth(NULL);
	}




	////////////////////////////////////////////////////////////////////////////
	// INVOKE THIS OBJECT
	// STRING:	EXECUTE THE SQL QUERY STRING
	// ARRAY:	GENERATE A SQL QUERY STRING THROUGH SELEX, AND EXECUTE IT
	////////////////////////////////////////////////////////////////////////////
	public function __invoke($query) {
		if (is_null($this->log)) {
			$this->log = false;
			throw new pudlException(
				$this,
				'Cannot run PUDL queries from within logging functions'
			);
		}


		//CONVERT FROM A STRING RESULT TO JUST A STRING
		if ($query instanceof pudlStringResult) {
			$query = (string) $query;
		}


		//SELEX
		if (pudl_array($query)  ||  func_num_args() > 1) {
			return call_user_func_array(
				[$this, 'selex'],
				func_get_args()
			);
		}


		//UNIONS
		if ($this->inUnion()) {
			$this->union[] = $query;
			return true;
		}


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) $microtime = microtime(true);


		//PREPEND CTE
		if ($this->isCte()) {
			$query = $this->_cte($query);
		}


		//STORE THE QUERY STRING LOCALLY
		$this->query = $query;


		//STORE TRANSACTION INFORMATION
		if (pudl_array($this->transaction)) $this->transaction[] = $query;


		//RETURN A STRING
		$string = end($this->string);
		if ($string === true) {
			$result = new pudlStringResult($this, $string);
			array_pop($this->string);


		//EXECUTE SUBQUERY
		} else if ($string instanceof pudlString) {
			array_pop($this->string);
			return $this($string . '(' . $query . ')');


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
				if (empty($hash)) $hash = $this->hash($query);

				if ($this->cache < 0) {
					$this->stats['total']--;
					$this->purge($hash);
					$result = new pudlCacheResult($this, [], '');

				} else {
					if ($this->recache) {
						$data = false;
					} else {
						$data = $this->redis->get("pudl:$hash");
					}

					if ($data === false  ||  is_null($data)) {
						$result = $this->missed($query);
						if (!$this->error()  &&  !$result->error()) {
							$data = $result->complete();

							// DONT CACHE EMPTY RESULT SETS
							if (!empty($data)) {
								$this->redis->set("pudl:$hash", $data, $this->cache);
							}

							$result = new pudlCacheResult($this, $data, $hash);
						}

					} else if (!empty($data)  &&  pudl_array($data)) {
						$this->stats['hits']++;
						$result = new pudlCacheResult($this, $data, $hash);
					}
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


		//LOG QUERY
		if ($this->log) {
			$this->log = NULL;
			$this->trigger('log', $this, $result);
			$this->log = false;
		}


		//RESET CACHE INFORMATION FOR NEXT QUERY
		$this->decache();


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) {
			$bench = $this->bench;
			$diff = round(microtime(true)-$microtime, 6);
			$bench($query, $diff, $this);
		}


		//ERROR REPORTING
		$errno = $this->errno();
		if (!empty($errno)) {
			if ($this->trigger('debug', $this, $result) === NULL) {
				$error = $this->error();
				if ($result instanceof pudlResult) {
					$error .= "\n" . $result->error();
				}
				throw new pudlException($this, $error, $errno);
			}
		}


		//RETURN FINAL RESULT
		return $result;
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE AN INSTANCE OF THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data) {
		if (!empty($data[0])  &&  $data[0] instanceof pudl) {
			$pudl = $data[0];
			unset($data[0]);
			$data += $pudl->auth();
		}

		if (get_called_class() !== __CLASS__) {
			$type = str_ireplace(__CLASS__, '', get_called_class());
			if (!empty($type)) $data['type'] = $type;
		}

		if (empty($data['type'])) {
			if (!empty($data['server'])) {
				$data['type'] = pudl_array($data['server']) ? 'Galera' : 'MySqli';
			} else {
				throw new pudlValueException(NULL,
					'No database type or server specified'
				);
			}
		}

		// GET THE ENGINE TYPE PHP PATH/FILE
		$engine = static::_engine($data['type']);

		if (empty($engine)) {
			throw new pudlValueException(NULL,
				'Unknown Database Server Type: ' . $data['type']
			);
		}

		require_once(is_owner(__DIR__ . end($engine)));

		$class = 'pudl' . reset($engine);
		return new $class($data);
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS THE SQL QUERY THROUGH THE DATABASE ENGINE
	// RETURNS INSTANCE OF PUDLRESULT
	////////////////////////////////////////////////////////////////////////////
	abstract protected function process($query);




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR CODE OF THE MOST RECENT SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	abstract public function errno();




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR TEXT OF THE MOST RECENT SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	abstract public function error();




	////////////////////////////////////////////////////////////////////////////
	// FORCE A WAIT STATE ON THE DATABASE ENGINE
	////////////////////////////////////////////////////////////////////////////
	public function wait($wait=true) {
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// FORCE A SYNCHRONIZATION OF THE DATABASE STORAGE SYSTEM
	////////////////////////////////////////////////////////////////////////////
	public function sync() {
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// CONNECT TO THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function connect() {}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT FROM THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		if ($trigger) $this->trigger('disconnect');
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE RAW DATABASE CONNECTION HANDLE
	////////////////////////////////////////////////////////////////////////////
	public function connection() {
		return $this->connection;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE MOST RECENTLY EXECUTED SQL QUERY STRING
	////////////////////////////////////////////////////////////////////////////
	public function query() {
		return $this->query;
	}




	////////////////////////////////////////////////////////////////////////////
	// ENABLE LOGGING
	////////////////////////////////////////////////////////////////////////////
	public function log() {
		if (is_null($this->log)) {
			$this->log = false;
			throw new pudlException(
				$this,
				'Cannot change logging status while in log callback'
			);
		}
		$this->log = true;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A LIST OF FIELDS FOR THE GIVEN $TABLES
	////////////////////////////////////////////////////////////////////////////
	public function listFields($tables, $prefix='') {
		if (!pudl_array($tables)) $tables = [$tables];

		$return = [];

		foreach ($tables as $key => $table) {
			if (in_array($key, ['on', 'clause', 'using'], true)) continue;

			if (pudl_array($table)) {
				if (is_int($key)) $key = '';
				$return		+= $this->listFields($table, $key);

			} else {
				$table = (string) $table;

				if (isset($this->listcache[$table])) {
					$list	= $this->listcache[$table];

				} else {
					$list	= $this->_fields($table);
					$this->listcache[$table] = $list;
				}

				foreach ($list as $item) {
					array_change_key_case($item);
					$item['table']	= $table;
					$item['prefix']	= is_int($key) ? $prefix : $key;
					$return[$item['field']] = $item;
				}
			}
		}

		return $return;
	}



	////////////////////////////////////////////////////////////////////////////
	// GET A LIST OF FIELDS FOR THE GIVEN $TABLE
	////////////////////////////////////////////////////////////////////////////
	protected function _fields($table) {
		return $this(
			'SHOW COLUMNS FROM ' . $this->_table($table)
		)->complete();
	}





	////////////////////////////////////////////////////////////////////////////
	// RUN "EXPLAIN" ON THE GIVEN SQL QUERY STRING
	////////////////////////////////////////////////////////////////////////////
	public function explain($query) {
		$return = '';
		$result = $this('EXPLAIN ' . $query);
		if ($result instanceof pudlStringResult) return $result;
		while ($data = $result()) $return .= print_r($data, true);
		$result->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK TO SEE IF THE GIVEN $ID VALUE EXISTS WITHIN THE GIVEN $TABLE
	////////////////////////////////////////////////////////////////////////////
	public function idExists($table, $col, $id=false) {
		return $this->cellId($table, $col, $col, $id) !== false;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK TO SEE IF THE GIVEN $CLAUSE IS TRUE FOR THE GIVEN $TABLE
	////////////////////////////////////////////////////////////////////////////
	public function clauseExists($table, $clause) {
		return $this->cell($table, true, $clause) !== false;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS FOUND
	////////////////////////////////////////////////////////////////////////////
	public function found() {
		$result = $this('SELECT FOUND_ROWS()');
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->completeCell();
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// ???
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// GET THE GLOBAL STATUS
	////////////////////////////////////////////////////////////////////////////
	public function globals($like=false, $limit=false, $offset=false) {
		return $this->listItems('GLOBAL STATUS', $like, $limit, $offset);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET VARIABLES
	////////////////////////////////////////////////////////////////////////////
	public function variables($like=false, $limit=false, $offset=false) {
		return $this->listItems('VARIABLES', $like, $limit, $offset);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE DATABASE SERVER STATUS
	////////////////////////////////////////////////////////////////////////////
	public function status($like=false, $limit=false, $offset=false) {
		return $this->listItems('STATUS', $like, $limit, $offset);
	}




	////////////////////////////////////////////////////////////////////////////
	// ENABLE QUERY BENCHMARKING
	////////////////////////////////////////////////////////////////////////////
	public function benchmark($benchmark) {
		$this->bench = $benchmark;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE APPLICATION SYNCED TIME
	////////////////////////////////////////////////////////////////////////////
	public function time($source=NULL) {
		if (empty($source)) {
			return is_object($this->time)
				? $this->time->time()
				: $this->time;
		}

		$this->time = $source;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE APPLICATION SYNCED MICRO-TIME
	////////////////////////////////////////////////////////////////////////////
	public function microtime($source=NULL) {
		if (empty($source)) {
			return is_object($this->microtime)
				? $this->microtime->microtime()
				: $this->microtime;
		}

		$this->microtime = $source;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET THE TIMEOUT VALUE
	////////////////////////////////////////////////////////////////////////////
	public function timeout($timeout) {}




	////////////////////////////////////////////////////////////////////////////
	// GET THE DATABASE SERVER FOR THE ACTIVE CONNECTION
	////////////////////////////////////////////////////////////////////////////
	public function server() {
		$auth = $this->auth();
		return $auth['server'];
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK IF WE'RE IN STRING-GENERATOR MODE
	////////////////////////////////////////////////////////////////////////////
	public function isString() {
		return end($this->string);
	}




	////////////////////////////////////////////////////////////////////////////
	// ENABLE STRING-GENERATOR MODE
	// PUDL WONT EXECUTE SQL QUERY AFTER GENERATING IT
	////////////////////////////////////////////////////////////////////////////
	public function string() {
		$this->string[] = true;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// DISABLE STRING-GENERATOR MODE
	////////////////////////////////////////////////////////////////////////////
	public function destring() {
		$this->string = [];
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// DISABLE STRING-GENERATOR MODE
	////////////////////////////////////////////////////////////////////////////
	public function decursion() {
		$this->_depth = 0;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET THE NEXT QUERY TO BE A SUB-QUERY WITH "IN"
	////////////////////////////////////////////////////////////////////////////
	public function in() {
		$this->string[] = ' IN ';
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET THE NEXT QUERY TO BE A SUB-QUERY WITH "NOT IN"
	////////////////////////////////////////////////////////////////////////////
	public function notIn() {
		$this->string[] = ' NOT IN ';
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET A VARIABLE ON THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function variable($variable, $value, $global=false) {
		$query  = 'SET ';
		$query .= $this->_value(new pudlGlobal($variable, $global));
		$query .= '=';
		$query .= $this->_value($value);
		return $this($query);
	}





	////////////////////////////////////////////////////////////////////////////
	// DATE/TIME
	////////////////////////////////////////////////////////////////////////////
	public function datetime($time=false) {
		if ($time === false)	$time	= $this->time();

		if (!is_int($time))		$time	= ctype_digit($time)
										? ((int) $time)
										: strtotime($time);

		// CANNOT BE SELF:: OR STATIC:: BECAUSE PHP SCOPING IS BROKEN
		return pudlFunction::from_unixtime($time);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LOCAL FILE SYSTEM PATH OF THE PUDL LIBRARY
	////////////////////////////////////////////////////////////////////////////
	public static function dir() {
		return __DIR__;
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var bool */			private			$log			= false;
	/** @var bool */			private			$bench			= false;
	/** @var ?string */			private			$query			= NULL;
	/** @var int */				private			$time			= 0;
	/** @var float */			private			$microtime		= 0.0;
	/** @var array */			private			$listcache		= [];
	/** @var mixed */			protected		$connection		= NULL;
	/** @var array */			protected		$string			= [];
	/** @var int */				protected		$_depth			= 0;




	////////////////////////////////////////////////////////////////////////////
	// PUDL VERSION INFORMATION
	////////////////////////////////////////////////////////////////////////////
	const version			= '2.9.1';
	const version_id		= 20901;
	const version_major		= 2;
	const version_minor		= 9;
	const version_release	= 1;

}
