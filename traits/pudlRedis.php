<?php


trait pudlRedis {


	////////////////////////////////////////////////////////////////////////////
	// SET THE NEXT QUERY TO CACHE FOR X NUMBER OF SECONDS
	////////////////////////////////////////////////////////////////////////////
	public function cache($seconds=0, $key=NULL) {
		$this->cache	= $seconds;
		$this->cachekey	= $key;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// FORCE THE NEXT QUERY TO BYPASS EXISTING CACHE, BUT STILL CACHE RESULT
	////////////////////////////////////////////////////////////////////////////
	public function recache($seconds, $key=NULL) {
		$this->recache = true;
		return $this->cache($seconds, $key);
	}




	////////////////////////////////////////////////////////////////////////////
	// FORCE THE NEXT QUERY TO BYPASS EXISTING CACHE, AND DELETE RESULT CACHE
	////////////////////////////////////////////////////////////////////////////
	public function uncache($key=NULL) {
		$this->cache	= -1;
		$this->cachekey	= $key;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// RESET INTERNAL CACHE STATE FOR THE NEXT QUERY
	////////////////////////////////////////////////////////////////////////////
	public function decache() {
		$this->cache	= NULL;
		$this->cachekey	= NULL;
		$this->recache	= false;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// DELETE A PARTICULAR CACHE KEY FROM REDIS SERVER
	////////////////////////////////////////////////////////////////////////////
	public function purge($key) {
		if (!$this->redis  ||  empty($key)) return false;
		try {
			return $this->redis->del("pudl:$key");
		} catch (RedisException $e) {}
		return false;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET OR SET THE REDIS SERVER
	////////////////////////////////////////////////////////////////////////////
	public function redis($server=false) {
		if ($server === false) return $this->redis;

		if ($server instanceof Redis) {
			$this->redis = $server;

		} else if (is_bool($server)  ||  $server === NULL) {
			$this->redis = NULL;

		} else if (class_exists('Redis')) {
			try {
				$this->redis = new pudlRedisHack;

				if ($this->redis->connect($server, -1, 0.25)) {
					$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
				} else {
					$this->redis = NULL;
				}

			} catch (RedisException $e) {
				$this->redis = NULL;
			}
		}

		if (!is_object($this->redis)) $this->redis = new pudlVoid;
		return $this->redis;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET CACHE STATS
	////////////////////////////////////////////////////////////////////////////
	public function stats() {
		return $this->stats;
	}




	////////////////////////////////////////////////////////////////////////////
	// QUEREY CACHE MISS, PROCESS IT FROM DATABASE
	////////////////////////////////////////////////////////////////////////////
	private function missed($query) {
		$this->stats['queries']++;
		$this->stats['misses']++;
		$this->stats['missed'][] = $query;
		return $this->process($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE SALT VALUE
	////////////////////////////////////////////////////////////////////////////
	public function salt() {
		$auth = $this->auth();
		return empty($auth['salt']) ? __FILE__ : $auth['salt'];
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE HASH VALUE FOR THE DATA
	////////////////////////////////////////////////////////////////////////////
	public function hash($data) {
		if (!is_string($data)  ||  empty($data)) return false;

		static $algs = ['sha512', 'sha384', 'sha256', 'sha224', 'sha1', 'md5'];
		static $list = false;

		if ($list === false) {
			$list = extension_loaded('hash') ? hash_algos() : [];
		}

		foreach ($algs as $algo) {
			if (in_array($algo, $list)) {
				return hash_hmac($algo,	$data, $this->salt());
			}
		}

		return sha1($this->salt().$data);
	}




	/** @var ?int */				protected		$cache		= NULL;
	/** @var ?string */				protected		$cachekey	= NULL;
	/** @var bool */				protected		$recache	= false;
	/** @var ?Redis|pudlVoid */		protected		$redis		= NULL;

	/** @var array */
	protected		$stats		= [
		'total'					=> 0,
		'queries'				=> 0,
		'hits'					=> 0,
		'misses'				=> 0,
		'missed'				=> [],
	];

}




////////////////////////////////////////////////////////////////////////////////
// PHP7 HACK BECAUSE EVERYTHING IS BROKEN EVERYWHERE!
// PHP7 IS EVEN MORE BROKEN DUE TO ERRORS VS EXCEPTIONS
// HANDLE LEGACY (ERRORS) AND MODERN (EXCEPTIONS) FROM PHP
// IN PHP 7.2, DEBIAN IS STILL SHOWING ERRORS, FREEBSD IS SHOWING EXCEPTIONS
////////////////////////////////////////////////////////////////////////////////
if (!defined('HHVM_VERSION')  &&  class_exists('Redis')) {
	/** @suppress PhanRedefineClass */
	class pudlRedisHack_base extends Redis {
		public function connect($host, $port=-1, $timeout=0.0, $persistent_id='') {

			$level	= error_reporting(0);

			try {
				$return	= parent::connect($host, $port, $timeout, $persistent_id);
			} catch (Exception $e) {
				$return = false;
			}

			error_reporting($level);
			return	$return;
		}


		public function pconnect_hack(
				$host, $port=-1, $timeout=0.0,
				$persistent_id='', $retry_interval=0,
				$read_timeout=0) {

			$level	= error_reporting(0);

			try {
				if ($read_timeout !== 0) {
					$return	= parent::pconnect(
						$host, $port, $timeout, $persistent_id,
						$retry_interval, $read_timeout
					);
				} else {
					$return	= parent::pconnect(
						$host, $port, $timeout, $persistent_id,
						$retry_interval, $read_timeout
					);
				}
			} catch (Exception $e) {
				$return = false;
			}

			error_reporting($level);
			return	$return;
		}
	}


	if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
		class pudlRedisHack extends pudlRedisHack_base {
			public function pconnect_hack(
					$host, $port=-1, $timeout=0.0,
					$persistent_id='', $retry_interval=0,
					$read_timeout=0) {

				return parent::pconnect(
					$host, $port, $timeout, $persistent_id,
					$retry_interval, $read_timeout
				);
			}
		}

	} else {
		class pudlRedisHack extends pudlRedisHack_base {
			public function pconnect_hack(
					$host, $port=-1, $timeout=0.0,
					$persistent_id='', $retry_interval=0) {

				return parent::pconnect(
					$host, $port, $timeout, $persistent_id,
					$retry_interval
				);
			}
		}
	}
}




////////////////////////////////////////////////////////////////////////////////
// HHVM HACK BECAUSE EVERYTHING IS BROKEN EVERYWHERE!
////////////////////////////////////////////////////////////////////////////////
if (defined('HHVM_VERSION')  &&  class_exists('Redis')) {
	/** @suppress PhanRedefineClass */
	class pudlRedisHack extends Redis {
		protected function doConnect(	$host, $port, $timeout, $persistent_id,
										$retry_interval, $persistent=false) {

			$level	= error_reporting(0);

			/** @suppress PhanUndeclaredStaticMethod */
			try {
				$return	= parent::doConnect($host, $port, $timeout, $persistent_id,
											$retry_interval, $persistent);
			} catch (Exception $e) {
				$return = false;
			}

			error_reporting($level);
			return	$return;
		}
	}
}
