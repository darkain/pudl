<?php

// SET THE NUMBER OF BITS PER CHARACTER TO MAX
if (!headers_sent()) {
	ini_set('session.hash_bits_per_character', 6);
	ini_set('session.gc_maxlifetime', 60*60*24*30);
}




////////////////////////////////////////////////////////////////////////////////
// PUDL CUSTOM SESSION HANDLER
////////////////////////////////////////////////////////////////////////////////
class			pudlSession
	implements	SessionHandlerInterface {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR, PASS IN SOME PUDL AND SESSION CONFIGURATIONS
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl, $table, $name=false,
								$domain=false, $secure=false) {

		$this->pudl		= $pudl;
		$this->table	= $table;
		$this->name		= $name;
		$this->domain	= $domain;

		// VERIFY THAT SESSION SUPPORT IS AVAILABLE
		pudl_require_extension('session');

		// WHEN THE DB DISCONNECTS, CALL OUR DISCONNECT HANDLER
		$this->pudl->on('disconnect', [$this, 'disconnect']);

		// SET THIS INSTANCE AS PHP'S SESSION HANDLER
		session_set_save_handler($this, true);

		// DIFFERENT SESSION NAME FOR HTTPS CONNECTIONS
		session_name(
			(empty($this->name) ? 'PUDLSESSID' : $this->name) .
			($secure ? '-SECURE' : '')
		);

		// SET PARAMETERS FOR BROWSER SESSION COOKIE
		session_set_cookie_params(
			60*60*24*30,		// SAVE SESSION FOR ONE MONTH
			'/',				// SESSION IS FOR ENTIRE DOMAIN
			empty($this->domain) ? '' : $this->domain,
			$secure,			// HTTPS ONLY
			true				// HTTP(S) ONLY - BLOCK JAVASCRIPT ACCESS
		);

		// START THE SESSION
		if (!headers_sent()) {
			session_start();
		} else {
			$_SESSION = [];
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE REDIS CACHE ID FOR THIS SESSION ID
	////////////////////////////////////////////////////////////////////////////
	private function cache($id) {
		return 'session-' . $this->name . '-' . $this->domain . '-' . $id;
	}




	////////////////////////////////////////////////////////////////////////////
	// PURGE SESSION DATA FROM REDIS CACHE
	////////////////////////////////////////////////////////////////////////////
	private function purge($id) {
		$this->pudl->purge( $this->cache($id) );
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// FINALIZE SESSION
	////////////////////////////////////////////////////////////////////////////
	public function disconnect() {
		session_write_close();
	}




	////////////////////////////////////////////////////////////////////////////
	// INITIALIZE SESSION
	// http://php.net/manual/en/sessionhandlerinterface.open.php
	////////////////////////////////////////////////////////////////////////////
	public function open($path, $name) {
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// CLOSE THE SESSION
	// http://php.net/manual/en/sessionhandlerinterface.close.php
	////////////////////////////////////////////////////////////////////////////
	public function close() {
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// READ SESSION DATA
	// http://php.net/manual/en/sessionhandlerinterface.read.php
	////////////////////////////////////////////////////////////////////////////
	public function read($id) {
		$data = $this->pudl->cache(60*60, $this->cache($id))->selectRow(
			['user', 'data'],
			$this->table,
			['id' => $id]
		);

		if (empty($data)  ||  !isset($data['data'])  ||  !isset($data['user'])) {
			$data = ['data'=>'', 'user'=>0];
		}

		$this->user = $data['user'];
		$this->hash = $this->pudl->hash($data['data']);

		return (string) $data['data'];
	}




	////////////////////////////////////////////////////////////////////////////
	// WRITE SESSION DATA
	// http://php.net/manual/en/sessionhandlerinterface.write.php
	////////////////////////////////////////////////////////////////////////////
	public function write($id, $data) {

		// IF CONTENT UNCHANGED, NOP
		if (is_string($data)) {
			if ($this->hash === $this->pudl->hash($data)) {
				return true;
			}
		}


		// IF NOTHING TO SAVE, JUST REMOVE THE DATA
		if (empty($data)) return $this->destroy($id);


		// GET REMOTE IP THE ADDRESS TO STORE
		if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else if (!empty($_SERVER['REMOTE_ADDR'])) {
			$address = $_SERVER['REMOTE_ADDR'];
		} else {
			$address = '';
		}


		// CREATE NEW ENTITY IN DATABASE
		$this->pudl->upsert($this->table, [
			'id'		=> $id,
			'user'		=> $this->user,
			'access'	=> $this->pudl->time(),
			'address'	=> $address,
			'data'		=> $data,
		]);


		// PURGE THE CACHE FOR THIS ID
		return $this->purge($id);
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTROY A SESSION
	// http://php.net/manual/en/sessionhandlerinterface.destroy.php
	////////////////////////////////////////////////////////////////////////////
	public function destroy($id) {
		// DELETE THE OBJECT
		if ($this->hash !== false) {
			$this->pudl->deleteId($this->table, 'id', $id);
		}

		// PURGE THE CACHE FOR THIS ID
		return $this->purge($id);
	}




	////////////////////////////////////////////////////////////////////////////
	// CLEANUP OLD SESSIONS
	// http://php.net/manual/en/sessionhandlerinterface.gc.php
	////////////////////////////////////////////////////////////////////////////
	public function gc($max) {
		$expire = $this->pudl->time() - (int) $max;
		$this->pudl->delete($this->table, ['access'=>pudl::lt($expire)]);
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// SET OR GET USER INFORMATION
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
	// GET THE DATABASE TABLE THIS SESSION IS ASSOCIATED WITH
	////////////////////////////////////////////////////////////////////////////
	public function table() { return $this->table; }




	////////////////////////////////////////////////////////////////////////////
	// PRIVATE LOCAL VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $pudl;
	private $table;
	private $name;
	private $domain;
	private $user		= 0;
	private $hash		= false;
	private $secure		= false;
}
