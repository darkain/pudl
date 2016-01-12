<?php


trait pudlRedis {


	public function cache($seconds=0, $key=false) {
		$this->cache	= $seconds;
		$this->cachekey	= $key;
		return $this;
	}



	public function purge($key) {
		if (!$this->redis) return;
		try { $this->redis->delete("pudl:$key"); } catch (RedisException $e) {}
	}



	public function redis($server=false) {
		if ($server === false) return $this->redis;

		if (is_object($server)  &&  is_a($server, 'Redis')) {
			$this->redis = $server;

		} else if (class_exists('Redis')) {
			try {
				$level = error_reporting(0); //HHVM HACK BECAUSE THEY HAVE YET TO FIX THEIR CODE
				$this->redis = new Redis;
				$this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
				if (!$this->redis->connect($server, -1, 0.025)) {
					$this->redis = false;
				}
				error_reporting($level);
			} catch (RedisException $e) {
				$this->redis = false;
			}
		}

		if (!is_object($this->redis)) $this->redis = new pudlVoid;
		return $this->redis;
	}



	public function stats() {
		return $this->stats;
	}



	private function missed($query) {
		$this->stats['queries']++;
		$this->stats['misses']++;
		$this->stats['missed'][] = $query;
		return $this->process($query);
	}



	public function salt() {
		$auth = $this->auth();
		return $auth['salt'];
	}



	protected		$cache		= false;
	protected		$cachekey	= false;
	protected		$redis		= false;

	protected		$stats		= [
		'total'					=> 0,
		'queries'				=> 0,
		'hits'					=> 0,
		'misses'				=> 0,
		'missed'				=> [],
	];

}
