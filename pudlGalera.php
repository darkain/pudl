<?php


require_once('pudlMySqli.php');



class pudlGalera extends pudlMySqli {
	use pudlMySqlHelper;


	public function __construct($username, $password, $database, $servers, $prefix=false) {
		parent::__construct($username, $password, $database, false, $prefix);

		if (!is_array($servers)) {
			throw new pudlException('Not a valid server pool, must be of type array');
		}

		//SET INITIAL VALUES
		$this->pool = $this->onlineServers($servers);
		shuffle($this->pool);

		//CONNECT
		$this->connect();
	}



	public static function instance($data) {
		$username	= empty($data['pudl_username'])	? ''	: $data['pudl_username'];
		$password	= empty($data['pudl_password'])	? ''	: $data['pudl_password'];
		$database	= empty($data['pudl_database'])	? ''	: $data['pudl_database'];
		$server		= empty($data['pudl_server'])	? []	: $data['pudl_server'];
		$prefix		= empty($data['pudl_prefix'])	? false	: $data['pudl_prefix'];

		$db = new pudlGalera($username, $password, $database, $server, $prefix);

		if (!empty($data['pudl_redis'])) {
			$redis = $db->redis;

			if (is_object($data['pudl_redis'])) {
				$db->redis = $data['pudl_redis'];
			} else if (class_exists('Redis')) {
				try {
					$db->redis = new Redis();
					if ($db->redis->connect($data['pudl_redis'], -1, 1)) {
						$db->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
					} else {
						$db->redis = $redis;
					}
				} catch(RedisException $e) {
					$db->redis = $redis;
				}
			}
		}

		return $db;
	}



	public function connect() {
		$auth = $this->auth();

		foreach ($this->pool as $server) {
			$this->mysqli = mysqli_init();

			//Set connection timeout to 1 second if we're in a clsuter, else 10 seconds
			$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, count($this->pool)>1 ? 1 : 10);

			//Attempt to create a persistant connection
			$ok = @$this->mysqli->real_connect(
				"p:$server",
				$auth['username'],
				$auth['password'],
				$auth['database']
			);

			//Attempt to create a non-persistant connection
			if (empty($ok)) {
				$ok = @$this->mysqli->real_connect(
					$server,
					$auth['username'],
					$auth['password'],
					$auth['database']
				);
			}

			//Attempt to set UTF-8 character set
			if ($ok  &&  $this->mysqli->set_charset('utf8')) {
				$this->server = $server;
				break;
			} else { $ok = false; }

			//Okay, maybe we're not
			$this->offlineServer($server);
		}


		//Cannot connect - Error out
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to database server "';
			$error .= implode(', ', $this->pool);
			$error .= '" with the username: "' . $auth['username'];
			$error .= "\"<br />\nError " . $this->connectErrno() . ': ' . $this->connectError();
			if (self::$die) die($error);
		}
	}



	public function reconnect() {
		if (empty($this->pool)) return;

		array_shift($this->pool);

		if (empty($this->pool)) {
			if (self::$die) die('No more servers available in server pool');
			return;
		}

		$this->connect();
	}



	protected function process($query) {
		if ($this->wait) {
			@$this->mysqli->query(
				'SET @wsrep_sync_wait_orig = @@wsrep_sync_wait'
			);

			@$this->mysqli->query(
				'SET SESSION wsrep_sync_wait = GREATEST(@wsrep_sync_wait_orig, '
				. $this->wait . ')'
			);
		}


		$result = @$this->mysqli->query($query);

		switch ($this->errno()) {
			case 0: break; //NO ERRORS!

			//An error occurred with this node, so let's connect to a different node in the cluster
			case 1047: //Unknown command
			case 1053: //Server shutdown in progress
			case 2006: //MySQL server has gone away
			case 2062: //Read timeout is reached
				$this->reconnect();
				if ($this->inTransaction()) {
					$result = $this->retryTransaction();
				} else {
					$result = @$this->mysqli->query($query);
				}
			break;

			//A deadlocking condition occurred, simple, let's retry!
			case 1205: //Lock wait timeout exceeded; try restarting transaction
			case 1213: //Deadlock found when trying to get lock; try restarting transaction
				if ($this->inTransaction()) {
					usleep(50000);
					$result = $this->retryTransaction();

				//It is possible to deadlock with a single query
				//This condition is simple: just retry the query!
				} else {
					usleep(25000);
					$result = @$this->mysqli->query($query);

					//If we deadlock again, try once more but wait longer
					if ($this->errno() == 1205  ||  $this->errno() == 1213) {
						usleep(50000);
						$result = @$this->mysqli->query($query);
					}
				}
			break;
		}

		if ($this->wait) {
			$this->wait = false;
			@$this->mysqli->query(
				'SET SESSION wsrep_sync_wait = @wsrep_sync_wait_orig'
			);
		}

		return new pudlMySqliResult($result, $this);
	}



	public function wait($wait=4) {
		$this->wait = (int) $wait;
		return $this;
	}



	public function onlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		foreach ($list as $key => $value) {
			if ($value === $server) unset($list[$key]);
		}
		shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}



	public function onlineServers($servers) {
		return $servers;
		if (count($servers) < 2) return $servers;

		$key = ftok(__FILE__, 't');
		$shm = shm_attach($key);

		if (!shm_has_var($shm, 1)) {
			shm_detach($shm);
			return $servers;
		}

		$list = shm_get_var($shm, 1);
		foreach ($servers as $index => &$item) {
			if (in_array($item, $list)) unset($servers[$index]);
		} unset($item);

		shm_detach($shm);
		return $servers;
	}



	public function offlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		if (!in_array($server, $list)) $list[] = $server;
		shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}



	public function offlineServers() {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		shm_detach($shm);
		return $list;
	}



	private $pool = [];
	private $wait = false;
}
