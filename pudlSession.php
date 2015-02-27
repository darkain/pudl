<?php

//Set the number of bits per character to max
ini_set('session.hash_bits_per_character', 6);


class pudlSession {
	public function __construct($database, $table, $name=false, $domain=false) {
		$this->db = $database;
		$this->table = $table;

		session_set_save_handler(
			array($this, 'open'),
			array($this, 'close'),
			array($this, 'read'),
			array($this, 'write'),
			array($this, 'destroy'),
			array($this, 'clean')
		);

		if (!empty($name)) session_name($name);
		if (!empty($domain)) session_set_cookie_params(0, '/', $domain);
		session_start();
	}


	function open($path, $name) {
		return true;
	}


	function close() {
		return true;
	}


	function read($id) {
		$id = $this->db->safe($id);
		return $this->db->cellId($this->table, 'data', 'id', $id);
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

		return true;
	}


	function destroy($id) {
		$id = $this->db->safe($id);
		$this->db->delete($this->table, "`id`='$id'");
		return true;
	}


	function clean($max) {
		$expire = time() - (int) $max;
		$this->db->delete($this->table, "`access`<'$expire'");
		return true;
	}


	private $db;
	private $table;
}
