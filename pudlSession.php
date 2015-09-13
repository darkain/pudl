<?php

//Set the number of bits per character to max
ini_set('session.hash_bits_per_character', 6);


class pudlSession {
	public function __construct($database, $table, $name=false, $domain=false) {
		$this->db		= $database;
		$this->table	= $table;
		$this->name		= $name;
		$this->domain	= $domain;

		session_set_save_handler(
			[$this, 'open'],
			[$this, 'close'],
			[$this, 'read'],
			[$this, 'write'],
			[$this, 'destroy'],
			[$this, 'clean']
		);

		if (!empty($this->name))	session_name($this->name);
		if (!empty($this->domain))	session_set_cookie_params(60*60*24*30, '/', $this->domain);
		session_start();
	}


	private function cache($id) {
		return 'session-' . $this->name . '-' . $this->domain . '-' . $id;
	}


	private function purge($id) {
		$this->db->purge( $this->cache($id) );
	}



	function open($path, $name) {
		return true;
	}


	function close() {
		return true;
	}


	function read($id) {
		$data = $this->db->cache(60*60, $this->cache($id))->cellId(
			$this->table, 'data', 'id', $id
		);

		if ($data === false) $data = '';

		$this->hash = is_string($data) ? md5($data) : false;

		return $data;
	}


	function write($id, $data) {
		if (is_string($data)  &&  $this->hash === md5($data)) return true;
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
			'access'	=> $this->db->time(),
			'address'	=> $address,
			'data'		=> $data,
		], true);

		//Purge the cache for this ID
		$this->purge($id);

		return true;
	}


	function destroy($id) {
		//Delete the object
		if ($this->hash !== false) {
			$this->db->deleteId($this->table, 'id', $id);
		}

		//Purge the cache for this ID
		$this->purge($id);

		return true;
	}


	function clean($max) {
		$expire = $this->db->time() - (int) $max;
		$this->db->delete($this->table, ['access'=>pudl::lt($expire)]);
		return true;
	}


	private $db;
	private $table;
	private $name;
	private $domain;
	private $hasdata	= false;
	private $hash		= false;
}
