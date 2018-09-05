<?php


if (!function_exists('is_owner')) {
	/** @suppress PhanRedefineFunction */
	function is_owner($path) { return $path; }
}



require_once(is_owner(__DIR__.'/pudlInclude.inc.php'));



abstract	class	pudl {
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
			use		pudlDynamic;
			use		pudlCallback;
			use		pudlTransaction;



	public function __construct($data, $autoconnect=true) {
		if (!empty($data[0])  &&  $data[0] instanceof pudl) {
			$pudl = $data[0];
			unset($data[0]);
			$data += $pudl->auth();
		}

		//SANITIZE DATA
		if (empty($data['username']))	$data['username']	= '';
		if (empty($data['password']))	$data['password']	= '';
		if (empty($data['database']))	$data['database']	= '';
		if (empty($data['server']))		$data['server']		= 'localhost';
		if (empty($data['prefix']))		$data['prefix']		= false;
		if (empty($data['persistent']))	$data['persistent']	= false;
		if (empty($data['salt']))		$data['salt']		= '';
		if (empty($data['timeout']))	$data['timeout']	= 10;

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
			$this->redis	= new pudlVoid;
		}

		//CONNECT TO SERVER
		if ($autoconnect) $this->connect();
	}



	public function __destruct() {
		$this->_auth(NULL);
	}



	public function __invoke($query) {
		if (is_null($this->log)) {
			$this->log = false;
			throw new pudlException(
				'Cannot run PUDL queries from within logging functions'
			);
		}


		//CONVERT FROM A STRING RESULT TO JUST A STRING
		if ($query instanceof pudlStringResult) {
			$query = (string) $query;
		}


		//SELEX
		if (pudl_array($query)) {
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
							$this->redis->set("pudl:$hash", $data, $this->cache);
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
				throw new pudlException($error, $errno);
			}
		}


		//RETURN FINAL RESULT
		return $result;
	}




	public static function instance($data, $autoconnect=true) {
		if (!empty($data[0])  &&  $data[0] instanceof pudl) {
			$pudl = $data[0];
			unset($data[0]);
			$data += $pudl->auth();
		}

		if (empty($data['type'])) {
			if (!empty($data['server'])) {
				$data['type'] = pudl_array($data['server']) ? 'Galera' : 'MySqli';
			} else {
				throw new pudlException(
					'No database type or server specified',
					PUDL_X_CONNECTION
				);
			}
		}

		switch (strtoupper($data['type'])) {
			case 'MYSQL':
				require_once(is_owner(__DIR__.'/mysql/pudlMySql.php'));
			break;

			case 'MYSQLI':
				require_once(is_owner(__DIR__.'/mysql/pudlMySqli.php'));
			break;

			case 'GALERA':
				require_once(is_owner(__DIR__.'/mysql/pudlGalera.php'));
			break;

			case 'PGSQL':
				require_once(is_owner(__DIR__.'/pgsql/pudlPgSql.php'));
			break;

			case 'MSSQL':
				require_once(is_owner(__DIR__.'/mssql/pudlMsSql.php'));
			break;

			case 'SQLITE':
				require_once(is_owner(__DIR__.'/sqlite/pudlSqlite.php'));
			break;

			case 'ODBC':
				require_once(is_owner(__DIR__.'/sql/pudlOdbc.php'));
			break;

			case 'PDO':
				require_once(is_owner(__DIR__.'/sql/pudlPdo.php'));
			break;

			case 'NULL':
				require_once(is_owner(__DIR__.'/null/pudlNull.php'));
			break;


			default:
				throw new pudlException(
					'Unknown Database Server Type: ' . $data['type'],
					PUDL_X_CONNECTION
				);
		}

		return call_user_func(
			['pudl'.$data['type'], 'instance'],
			$data,
			$autoconnect
		);
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



	public function connection() {
		return $this->connection;
	}



	public function query($query=false) {
		if (func_num_args() < 1) return $this->query;
		if (!pudl_array($query)) return $this($query);
		return call_user_func_array([$this,'selex'], func_get_args());
	}



	public function log() {
		if (is_null($this->log)) {
			$this->log = false;
			throw new pudlException(
				'Cannot change logging status while in log callback'
			);
		}
		$this->log = true;
		return $this;
	}



	public function listFields($table, $prefix='') {
		if (!pudl_array($table)) $table = [$table];

		$return = [];

		foreach ($table as $key => $value) {
			if (in_array($key, ['on', 'clause', 'using'], true)) continue;

			if (pudl_array($value)) {
				if (is_int($key)) $key = '';
				$return		+= $this->listFields($value, $key);

			} else {
				$value		= $this->_table($value);

				if (isset($this->listcache[$value])) {
					$list	= $this->listcache[$value];

				} else {
					$list	= $this('SHOW COLUMNS FROM ' . $value)->complete();
					$this->listcache[$value] = $list;
				}

				foreach ($list as $item) {
					$item['Table']	= $value;
					$item['Prefix']	= is_int($key) ? $prefix : $key;
					$return[$item['Field']] = $item;
				}
			}
		}

		return $return;
	}



	public function explain($query) {
		$return = '';
		$result = $this('EXPLAIN ' . $query);
		if ($result instanceof pudlStringResult) return $result;
		while ($data = $result()) $return .= print_r($data, true);
		$result->free();
		return $return;
	}



	public function idExists($table, $col, $id=false) {
		return $this->cellId($table, $col, $col, $id) !== false;
	}



	public function clauseExists($table, $clause) {
		return $this->cell($table, true, $clause) !== false;
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



	public function destring() {
		array_pop($this->string);
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



	public function set($variable, $value, $global=false) {
		$query  = 'SET ';
		$query .= $this->_value(new pudlGlobal($variable, $global));
		$query .= '=';
		$query .= $this->_value($value);
		return $this($query);
	}




	public function datetime($time=false) {
		if ($time === false)	$time	= $this->time();

		if (!is_int($time))		$time	= ctype_digit($time)
										? ((int) $time)
										: strtotime($time);

		return self::from_unixtime($time);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LOCAL PATH OF THE PUDL LIBRARY
	////////////////////////////////////////////////////////////////////////////
	public static function dir() {
		return __DIR__;
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////

	/** @var bool */
	private			$log			= false;

	/** @var bool */
	private			$bench			= false;

	/** @var string|false */
	private			$query			= false;

	/** @var int */
	private			$time			= 0;

	/** @var float */
	private			$microtime		= 0.0;

	/** @var array */
	private			$listcache		= [];

	/** @var resource|object|null */
	protected		$connection		= NULL;

	/** @var array */
	protected		$string			= [];

	/** @var string */
	public static	$version		= 'PUDL 2.9.0';

}
