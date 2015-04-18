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
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'clean')
		);

		if (!empty($this->name))	session_name($this->name);
		if (!empty($this->domain))	session_set_cookie_params(0, '/', $this->domain);
		session_start();
	}


	private function cache($id) {
		return 'PUDL-SESSION-' . $this->name . '-' . $this->domain . '-' . $id;
	}


	function open($path, $name) {
		return true;
	}


	function close() {
		return true;
	}


	function read($id) {
		return $this->db->cache(60*60, $this->cache($id))->cellId(
			$this->table,
			'data',
			'id',
			$this->db->safe($id)
		);
	}


	function write($id, $data) {
		if (empty($data)) return $this->destroy($id);

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$address = $_SERVER['REMOTE_ADDR'];
		} else {
			$address = '';
		}

		//Create new entity in database
		$this->db->insert(
			$this->table,
			array(
				'id'		=> $id,
				'access'	=> $this->db->time(),
				'address'	=> $address,
				'data'		=> $data,
			),
			true, true
		);

		//Purge the cache for this ID
		$this->db->purge( $this->cache($id) );

		return true;
	}


	function destroy($id) {
		//Delete the object
		$this->db->deleteId($this->table, 'id', $this->db->safe($id));

		//Purge the cache for this ID
		$this->db->purge( $this->cache($id) );

		return true;
	}


	function clean($max) {
		$expire = time() - (int) $max;
		$this->db->delete($this->table, "access<'$expire'");
		return true;
	}


	private $db;
	private $table;
	private $name;
	private $domain;
}
