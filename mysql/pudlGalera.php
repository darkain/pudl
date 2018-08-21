<?php



require_once('pudlMySqli.php');



class pudlGalera extends pudlMySqli {
	use pudlMySqlHelper;



	public function __construct($data, $autoconnect=true) {
		//CONNECT TO THE SERVER CLUSTER
		parent::__construct($data, false);

		if (!pudl_array($data['server'])) {
			throw new pudlException(
				'Not a valid server pool, $data[server] must be ARRAY data type',
				PUDL_X_CONNECTION
			);
		}


		//ONLY SET SHMKEY IF EXTENSION EXISTS
		$this->shmkey = extension_loaded('sysvshm') ? 1 : false;


		//SET INITIAL VALUES
		$this->pool = $this->onlineServers($data['server']);


		//RANDOMIZE SERVER POOL ORDER
		//IF REMOTE_ADDR AVAILABLE, USE IT TO HASH ROUTE TO SAME NODE EACH TIME
		if (!empty($_SERVER['REMOTE_ADDR'])) {
			srand( crc32($_SERVER['REMOTE_ADDR']) );
			shuffle($this->pool);
			srand();
		} else {
			shuffle($this->pool);
		}

		//SET BACKUP SERVERS
		if (!empty($data['backup'])  &&  pudl_array($data['backup'])) {
			$this->pool = array_merge($this->pool, $data['backup']);
		}

		//CONNECT TO CLUSTER
		if ($autoconnect) $this->connect();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlGalera($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		foreach ($this->pool as $server) {
			$this->connection = mysqli_init();

			//SET CONNECTION TIMEOUT TO 1 SECOND IF IN CLSUTER MODE
			//SET CONNECTION TIMEOUT TO 10 SECONDS IF IT IS THE LAST NODE
			$this->connection->options(
				MYSQLI_OPT_CONNECT_TIMEOUT,
				(count($this->pool)>1) ? 1 : $auth['timeout']
			);

			//SET READ TIMEOUT TO 10 SECONDS
			//SHORTER TIMES ARE STILL UNSTABLE
			//THIS TIMEOUT IS INCREASED AFTER OUR FIRST SUCCESSFUL COMMAND BELOW
			$this->connection->options(MYSQLI_OPT_READ_TIMEOUT, $auth['timeout']);

			//ATTEMPT TO CREATE A CONNECTION
			$ok = @$this->connection->real_connect(
				(empty($auth['persistent']) ? '' : 'p:') . $server,
				$auth['username'],
				$auth['password'],
				$auth['database']
			);

			//VERIFY WE CONNECTED OKAY!
			if ($ok) $ok = ($this->connectErrno() === 0);

			//ATTEMPT TO SET UTF-8 CHARACTER SET
			if ($ok) $ok = @$this->connection->set_charset('utf8mb4');

			//VERIFY WE'RE NOT IN A READ-ONLY STATE
			if ($ok  &&  !self::$die) $ok = !$this->readonly();

			//ATTEMPT TO GET THE CLUSTER SYNC STATE OF THIS NODE
			$this->state = $ok ? $this->globals('wsrep_local_state') : [];

			//SET THE LOCAL STATE TO INVALID IF WE COULD NOT PULL ONE
			if (empty($this->state['wsrep_local_state'])) {
				$this->state['wsrep_local_state'] = 0;
			}

			//ONLY CONNECT IF NODE IS IN A 'JOINED' OR 'SYNCED' STATE
			$state = (int) $this->state['wsrep_local_state'];
			if ($state === GALERA_JOINED  ||  $state === GALERA_SYNCED) {
				$this->strict()->timeout($auth);
				$this->connected = $server;

				return true;
			}

			//OKAY, MAYBE WE'RE NOT
			$this->disconnect(false);
			$this->offlineServer($server);
		}

		//CANNOT CONNECT, BUT BAILING OUT OF SCRIPT IS DISABLED
		if (!self::$die) return false;

		//CANNOT CONNECT - ERROR OUT
		$error  = "<br />\n";
		$error .= 'Unable to connect to galera cluster "';
		$error .= implode(', ', $this->pool);
		$error .= '" with the username: "' . $auth['username'] . "\"<br />\n";
		if (!$this->connectErrno()  &&  isset($this->state['wsrep_local_state'])) {
			if ($this->state['wsrep_local_state'] == 0) {
				$error .= 'Invalid WSREP LOCAL STATE';
			} else if ($this->state['wsrep_local_state'] == 1) {
				$error .= 'This node is still joining the cluster and is currently unavailable';
			} else if ($this->state['wsrep_local_state'] == 2) {
				$error .= 'This node is currently acting as a donor for other nodes and is currently unavailable';
			}
		} else {
			$error .= 'Error ' . $this->connectErrno() . ': ' . $this->connectError();
		}
		throw new pudlException($error, PUDL_X_CONNECTION);
	}



	public function reconnect() {
		if (empty($this->pool)) return false;

		array_shift($this->pool);

		if (empty($this->pool)) {
			if (self::$die) {
				throw new pudlException(
					'No more servers available in server pool',
					PUDL_X_CONNECTION
				);
			}
			return false;
		}

		return $this->connect();
	}



	protected function process($query) {
		if (!$this->connection) return new pudlMySqliResult($this);

		//PROPERLY HANDLE RE-ENTRY TO THIS FUNCTION
		$wait = $this->wait;
		$this->wait = false;

		if ($wait) {
			@$this->connection->query(
				'SET @wsrep_sync_wait_orig = @@wsrep_sync_wait'
			);
			if ($this->errno()) return new pudlMySqliResult($this);

			@$this->connection->query(
				'SET SESSION wsrep_sync_wait = @wsrep_sync_wait_orig | ' . ((int)$wait)
			);
			if ($this->errno()) return new pudlMySqliResult($this);
		}


		$result = @$this->connection->query($query);

		switch ($this->errno()) {
			case 0: break; //NO ERRORS!

			//AN ERROR OCCURRED WITH THIS NODE, SO LET'S CONNECT TO A DIFFERENT NODE IN THE CLUSTER
			case 1047: // "WSREP HAS NOT YET PREPARED NODE FOR APPLICATION USE"
			case 1053: // "SERVER SHUTDOWN IN PROGRESS"
			case 1927: // "CONNECTION WAS KILLED"
			case 2006: // "MYSQL SERVER HAS GONE AWAY"
			case 2013: // "LOST CONNECTION TO MYSQL SERVER DURING QUERY"
			case 2062: // "READ TIMEOUT IS REACHED"
				if (!$this->reconnect()) return new pudlMySqliResult($this);
				if ($this->inTransaction()) {
					$result = $this->retryTransaction();
				} else {
					$result = $this->process($query);
				}
			break;

			//A DEADLOCKING CONDITION OCCURRED, SIMPLE, LET'S RETRY!
			case 1205: // "LOCK WAIT TIMEOUT EXCEEDED; TRY RESTARTING TRANSACTION"
			case 1213: // "DEADLOCK FOUND WHEN TRYING TO GET LOCK; TRY RESTARTING TRANSACTION"
				if ($this->inTransaction()) {
					usleep(mt_rand(30000,50000));
					$result = $this->retryTransaction();

				//IT IS POSSIBLE TO DEADLOCK WITH A SINGLE QUERY
				//THIS CONDITION IS SIMPLE: JUST RETRY THE QUERY!
				} else {
					usleep(mt_rand(15000,25000));
					$result = @$this->connection->query($query);

					//IF WE DEADLOCK AGAIN, TRY ONCE MORE BUT WAIT LONGER
					if ($this->errno() == 1205  ||  $this->errno() == 1213) {
						usleep(mt_rand(30000,50000));
						$result = @$this->connection->query($query);
					}
				}
			break;
		}

		if ($wait  &&  !$this->errno()) {
			@$this->connection->query(
				'SET SESSION wsrep_sync_wait = @wsrep_sync_wait_orig'
			);
		}

		return new pudlMySqliResult($this,
			$result instanceof mysqli_result ?
			$result : NULL
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// SET THE wsrep_sync_wait VARIABLE FOR THE NEXT STATEMENT
	// SEE pudlConstants.php FOR LIST OF VALUES
	////////////////////////////////////////////////////////////////////////////
	public function wait($wait=true) {
		$this->wait = ($wait === true) ? GALERA_ALL : (int)$wait;
		return $this;
	}




	public function sync() {
		$die		= self::$die;
		self::$die	= false;
		foreach ($this->pool as $server) {
			if ($server == $this->connected) continue;

			$sync	= new pudlGalera([$this, 'server'=>[$server]]);
			if ($sync->server() === false) continue;

			$sync->wait()->row('information_schema.SESSION_VARIABLES');
		}
		self::$die	= $die;
		return $this;
	}



	public function onlineServer($server) {
		if (!$this->shmkey) return;

		$key	= ftok(__FILE__, 't');
		$shm	= @shm_attach($key);
		$list	= @shm_get_var($shm, $this->shmkey);
		if (empty($list)) $list = [];

		unset($list[$server]);

		@shm_put_var($shm, $this->shmkey, $list);
		@shm_detach($shm);
	}



	public function onlineServers($servers) {
		if (!$this->shmkey) return $servers;
		if (count($servers) < 2) return $servers;

		$key	= ftok(__FILE__, 't');
		$shm	= @shm_attach($key);
		$list	= @shm_get_var($shm, $this->shmkey);
		$change	= false;

		if (!empty($list)  &&  pudl_array($list)) {
			foreach ($servers as $index => $item) {
				if (empty($list[$item])) continue;
				if (($this->time() - $list[$item]) < 10) {
					unset($servers[$index]);
				} else {
					$change = true;
					unset($list[$item]);
				}
			}
		}
		if ($change) {
			@shm_put_var($shm, $this->shmkey, $list);
		}

		@shm_detach($shm);
		return $servers;
	}



	public function offlineServer($server) {
		if (!$this->shmkey) return;

		$key	= ftok(__FILE__, 't');
		$shm	= @shm_attach($key);
		$list	= @shm_get_var($shm, $this->shmkey);

		if (empty($list)) $list = [];
		if (empty($list[$server])) $list[$server] = $this->time();

		@shm_put_var($shm, $this->shmkey, $list);
		@shm_detach($shm);
	}



	public function offlineServers() {
		if (!$this->shmkey) return [];

		$key	= ftok(__FILE__, 't');
		$shm	= @shm_attach($key);
		$list	= @shm_get_var($shm, $this->shmkey);

		@shm_detach($shm);

		return (!empty($list) ? $list : []);
	}



	public function offlineReset() {
		if (!$this->shmkey) return;

		$key	= ftok(__FILE__, 't');
		$shm	= @shm_attach($key);

		@shm_remove_var($shm, $this->shmkey);
	}



	public function server()	{ return $this->connected; }
	public function state()		{ return $this->state; }
	public function pool()		{ return $this->pool; }



	private $pool		= [];
	private $wait		= false;
	private $connected	= false;
	private $state		= [];
	private $shmkey		= false;
}
