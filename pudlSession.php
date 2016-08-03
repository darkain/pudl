<?php

//SET THE NUMBER OF BITS PER CHARACTER TO MAX
ini_set('session.hash_bits_per_character', 6);
ini_set('session.gc_maxlifetime', 60*60*24*30);


class pudlSession {

	public function __construct($database, $table, $name=false, $domain=false, $secure=false) {
		$this->db		= $database;
		$this->table	= $table;
		$this->name		= $name;
		$this->domain	= $domain;

		$this->db->on('disconnect', [$this, 'disconnect']);

		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'clean']
		);

		//Different session name for HTTPS connections
		session_name(
			(empty($this->name) ? 'PUDLSESSID' : $this->name) .
			($secure ? '-SECURE' : '')
		);

		session_set_cookie_params(
			60*60*24*30,		//Save session for one month
			'/',				//Session is for entire domain
			empty($this->domain) ? '' : $this->domain,
			$secure				//HTTPS only
		);

		session_start();
	}



	private function cache($id) {
		return 'session-' . $this->name . '-' . $this->domain . '-' . $id;
	}



	private function purge($id) {
		$this->db->purge( $this->cache($id) );
		return true;
	}



	function disconnect() {
		session_write_close();
	}



	function open() {
		return true;
	}



	function close() {
		return true;
	}



	function read($id) {
		$data = $this->db->cache(60*60, $this->cache($id))->selectRow(
			['user', 'data'],
			$this->table,
			['id' => $id]
		);

		if ($data === false) $data = ['data'=>false, 'user'=>0];

		$this->user = $data['user'];

		$this->hash = is_string($data['data'])
			? hash('sha512', $this->db->salt().$data['data'])
			: false;

		return $data['data'];
	}



	function write($id, $data) {
		if (is_string($data)  &&  $this->hash === hash('sha512', $this->db->salt().$data)) return true;

		if (empty($data)) return $this->destroy($id);

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$address = $_SERVER['REMOTE_ADDR'];
		} else {
			$address = '';
		}

		//Create new entity in database
		$this->db->insert($this->table, [
			'id'		=> $id,
			'user'		=> $this->user,
			'access'	=> $this->db->time(),
			'address'	=> $address,
			'data'		=> $data,
		], true);

		//Purge the cache for this ID
		return $this->purge($id);
	}



	function destroy($id) {
		//Delete the object
		if ($this->hash !== false) {
			$this->db->deleteId($this->table, 'id', $id);
		}

		//Purge the cache for this ID
		return $this->purge($id);
	}



	function clean($max) {
		$expire = $this->db->time() - (int) $max;
		$this->db->delete($this->table, ['access'=>pudl::lt($expire)]);
		return true;
	}



	public function user($user=false, $name=false) {
		if ($user === false) return $this->user;

		$this->user = (int) $user;

		if ($name !== false) {
			if ($this->user === 0) {
				unset($_SESSION[$name]);
			} else {
				$_SESSION[$name] = $this->user;
			}
		}
	}



	public function table() { return $this->table; }



	private $db;
	private $table;
	private $name;
	private $domain;
	private $hash		= false;
	private $user		= 0;
	private $secure		= false;
}
