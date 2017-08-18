<?php

//SET THE NUMBER OF BITS PER CHARACTER TO MAX
ini_set('session.hash_bits_per_character', 6);
ini_set('session.gc_maxlifetime', 60*60*24*30);




////////////////////////////////////////////////////////////////////////////////
//PUDL CUSTOM SESSION HANDLER
////////////////////////////////////////////////////////////////////////////////
class			pudlSession
	implements	SessionHandlerInterface {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR, PASS IN SOME PUDL AND SESSION CONFIGURATIONS
	////////////////////////////////////////////////////////////////////////////
	public function __construct($database, $table, $name=false, $domain=false, $secure=false) {
		$this->db		= $database;
		$this->table	= $table;
		$this->name		= $name;
		$this->domain	= $domain;

		//When the DB disconnects, call our disconnect handler
		$this->db->on('disconnect', [$this, 'disconnect']);

		//Set this instance as PHP's session handler
		session_set_save_handler($this, true);

		//Different session name for HTTPS connections
		session_name(
			(empty($this->name) ? 'PUDLSESSID' : $this->name) .
			($secure ? '-SECURE' : '')
		);

		//Set parameters for browser session cookie
		session_set_cookie_params(
			60*60*24*30,		//Save session for one month
			'/',				//Session is for entire domain
			empty($this->domain) ? '' : $this->domain,
			$secure,			//HTTPS only
			true				//HTTP(S) only - block JavaScript access
		);

		//Start the session
		session_start();
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE REDIS CACHE ID FOR THIS SESSION ID
	////////////////////////////////////////////////////////////////////////////
	private function cache($id) {
		return 'session-' . $this->name . '-' . $this->domain . '-' . $id;
	}




	////////////////////////////////////////////////////////////////////////////
	//PURGE SESSION DATA FROM REDIS CACHE
	////////////////////////////////////////////////////////////////////////////
	private function purge($id) {
		$this->db->purge( $this->cache($id) );
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	//FINALIZE SESSION
	////////////////////////////////////////////////////////////////////////////
	public function disconnect() {
		session_write_close();
	}




	////////////////////////////////////////////////////////////////////////////
	//INITIALIZE SESSION
	//http://php.net/manual/en/sessionhandlerinterface.open.php
	////////////////////////////////////////////////////////////////////////////
	public function open($path, $name) {
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	//CLOSE THE SESSION
	//http://php.net/manual/en/sessionhandlerinterface.close.php
	////////////////////////////////////////////////////////////////////////////
	public function close() {
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	//READ SESSION DATA
	//http://php.net/manual/en/sessionhandlerinterface.read.php
	////////////////////////////////////////////////////////////////////////////
	public function read($id) {
		$data = $this->db->cache(60*60, $this->cache($id))->selectRow(
			['user', 'data'],
			$this->table,
			['id' => $id]
		);

		if (empty($data)  ||  !isset($data['data'])  ||  !isset($data['user'])) {
			$data = ['data'=>'', 'user'=>0];
		}

		$this->user = $data['user'];
		$this->hash = $this->db->hash($data['data']);

		return (string) $data['data'];
	}




	////////////////////////////////////////////////////////////////////////////
	//WRITE SESSION DATA
	//http://php.net/manual/en/sessionhandlerinterface.write.php
	////////////////////////////////////////////////////////////////////////////
	public function write($id, $data) {
		if (is_string($data)  &&  $this->hash === $this->db->hash($data)) return true;

		if (empty($data)) return $this->destroy($id);

		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (!empty($_SERVER['REMOTE_ADDR'])) {
			$address = $_SERVER['REMOTE_ADDR'];
		} else {
			$address = '';
		}

		//Create new entity in database
		$this->db->upsert($this->table, [
			'id'		=> $id,
			'user'		=> $this->user,
			'access'	=> $this->db->time(),
			'address'	=> $address,
			'data'		=> $data,
		]);

		//Purge the cache for this ID
		return $this->purge($id);
	}




	////////////////////////////////////////////////////////////////////////////
	//DESTROY A SESSION
	//http://php.net/manual/en/sessionhandlerinterface.destroy.php
	////////////////////////////////////////////////////////////////////////////
	public function destroy($id) {
		//Delete the object
		if ($this->hash !== false) {
			$this->db->deleteId($this->table, 'id', $id);
		}

		//Purge the cache for this ID
		return $this->purge($id);
	}




	////////////////////////////////////////////////////////////////////////////
	//CLEANUP OLD SESSIONS
	//http://php.net/manual/en/sessionhandlerinterface.gc.php
	////////////////////////////////////////////////////////////////////////////
	public function gc($max) {
		$expire = $this->db->time() - (int) $max;
		$this->db->delete($this->table, ['access'=>pudl::lt($expire)]);
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	//SET OR GET USER INFORMATION
	////////////////////////////////////////////////////////////////////////////
	public function user($user=false, $name=false) {
		if ($user === false) return $this->user;

		$this->user = (int) (pudl_array($user) ? $user['user_id'] : $user);

		if ($name !== false) {
			if ($this->user === 0) {
				unset($_SESSION[$name]);
			} else {
				$_SESSION[$name] = $this->user;
			}
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE DATABASE TABLE THIS SESSION IS ASSOCIATED WITH
	////////////////////////////////////////////////////////////////////////////
	public function table() { return $this->table; }




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE LOCAL VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $db;
	private $table;
	private $name;
	private $domain;
	private $user		= 0;
	private $hash		= false;
	private $secure		= false;
}
