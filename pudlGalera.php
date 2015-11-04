<?php


require_once('pudlMySqli.php');



class pudlGalera extends pudlMySqli {
	use pudlMySqlHelper;


	public function __construct($data, $autoconnect=true) {
		parent::__construct($data, false);

		if (!is_array($data['server'])) {
			throw new pudlException('Not a valid server pool, must be of type array');
		}

		//SET INITIAL VALUES
		$this->pool = $this->onlineServers($data['server']);
		shuffle($this->pool);

		//CONNECT TO THE SERVER CLUSTER
		if ($autoconnect) $this->connect();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlGalera($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		foreach ($this->pool as $server) {
			$this->mysqli = mysqli_init();

			//SET CONNECTION TIMEOUT TO 1 SECOND IF WE'RE IN A CLSUTER, ELSE 10 SECONDS
			$this->mysqli->options(MYSQLI_OPT_CONNECT_TIMEOUT, count($this->pool)>1 ? 1 : 10);

			//ATTEMPT TO CREATE A PERSISTANT CONNECTION
			$ok = @$this->mysqli->real_connect(
				"p:$server",
				$auth['username'],
				$auth['password'],
				$auth['database']
			);

			//ATTEMPT TO CREATE A NON-PERSISTANT CONNECTION
			if (empty($ok)) {
				$ok = @$this->mysqli->real_connect(
					$server,
					$auth['username'],
					$auth['password'],
					$auth['database']
				);
			}

			//ATTEMPT TO SET UTF-8 CHARACTER SET
			if ($ok  &&  @$this->mysqli->set_charset('utf8')) {
				$this->connected = $server;
				break;
			}

			//OKAY, MAYBE WE'RE NOT
			$ok = false;
			$this->offlineServer($server);
		}


		//CANNOT CONNECT - ERROR OUT
		if (empty($ok)) {
			$error  = "<br />\n";
			$error .= 'Unable to connect to galera cluster "';
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

			//AN ERROR OCCURRED WITH THIS NODE, SO LET'S CONNECT TO A DIFFERENT NODE IN THE CLUSTER
			case 1047: //UNKNOWN COMMAND
			case 1053: //SERVER SHUTDOWN IN PROGRESS
			case 2006: //MYSQL SERVER HAS GONE AWAY
			case 2062: //READ TIMEOUT IS REACHED
				$this->reconnect();
				if ($this->inTransaction()) {
					$result = $this->retryTransaction();
				} else {
					$result = @$this->mysqli->query($query);
				}
			break;

			//A DEADLOCKING CONDITION OCCURRED, SIMPLE, LET'S RETRY!
			case 1205: //LOCK WAIT TIMEOUT EXCEEDED; TRY RESTARTING TRANSACTION
			case 1213: //DEADLOCK FOUND WHEN TRYING TO GET LOCK; TRY RESTARTING TRANSACTION
				if ($this->inTransaction()) {
					usleep(50000);
					$result = $this->retryTransaction();

				//IT IS POSSIBLE TO DEADLOCK WITH A SINGLE QUERY
				//THIS CONDITION IS SIMPLE: JUST RETRY THE QUERY!
				} else {
					usleep(25000);
					$result = @$this->mysqli->query($query);

					//IF WE DEADLOCK AGAIN, TRY ONCE MORE BUT WAIT LONGER
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



	public function wait($wait=true) {
		$this->wait = ($wait === true) ? 7 : (int)$wait;
		return $this;
	}



	public function sync() {
		$auth = $this->auth();
		$die = self::$die;
		self::$die = false;
		foreach ($this->pool as $server) {
			if ($server == $this->connected) continue;
			$sync = pudlGalera::instance(['server'=>[$server]]+$auth);
			$sync->wait()->query('SELECT * FROM information_schema.GLOBAL_VARIABLES LIMIT 1');
		}
		self::$die = $die;
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



	private $pool		= [];
	private $wait		= false;
	private $connected	= false;
}
