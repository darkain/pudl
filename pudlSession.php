<?php

class pudlSession {
	public function __construct($database, $table) {
		$this->db = $database;
		$this->table = $table;
		
		session_set_save_handler(array(&$this, 'open'),  array(&$this, 'close'),   array(&$this, 'read'),
								 array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'clean'));
		session_start();
	}


	function open($path, $name) {
		return true;
	}


	function close() {
	}


	function read($id) {
		$id = $this->db->safe($id);
		return $this->db->cellId($this->table, 'data', 'id', $id);
	}


	function write($id, $data) {
		$insert['id']     = $id;
		$insert['access'] = time();
		$insert['data']   = $data;

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$insert['address'] = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (isset($_SERVER['REMOTE_ADDR'])) {
			$insert['address'] = $_SERVER['REMOTE_ADDR'];
		}

		return $this->db->replace($this->table, $insert, true);
	}


	function destroy($id) {
		$id = $this->db->safe($id);
		return $this->db->delete($this->table, "`id`='$id'");
	}


	function clean($max) {
		$expire = time() - (int) $max;
		return $this->db->delete($this->table, "`access`<'$expire'");
	}


	private $db;
	private $table;
}
