<?php

// SET THE NUMBER OF BITS PER CHARACTER TO MAX
if (!headers_sent()) {
	ini_set('session.sid_length',				64);
	ini_set('session.sid_bits_per_character',	6);
	ini_set('session.hash_bits_per_character',	6);
	ini_set('session.gc_maxlifetime',			60*60*24*30);
	ini_set('session.hash_function',			1);
}




////////////////////////////////////////////////////////////////////////////////
// PUDL CUSTOM SESSION HANDLER
////////////////////////////////////////////////////////////////////////////////
class			pudlSession
	implements	SessionHandlerInterface {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR, PASS IN SOME PUDL AND SESSION CONFIGURATIONS
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl, $table, $options=[]) {

		// ENSURE VALUES ARE SET
		if (!isset($options['domain']))		$options['domain']		= '';
		if (!isset($options['path']))		$options['path']		= '/';
		if (!isset($options['name']))		$options['name']		= 'PUDLSESSID';
		if (!isset($options['samesite']))	$options['samesite']	= 'Strict';
		if (!isset($options['lifetime']))	$options['lifetime']	= 60*60*24*30;

		// FORCE BOOLEAN
		$options['secure']		= !isset($options['secure'])    ||  !empty($options['secure']);
		$options['httponly']	= !isset($options['httponly'])  ||  !empty($options['httponly']);

		// BASIC INFORMATION
		$this->pudl				= $pudl;
		$this->table			= $table;
		$this->options			= $options;

		// VERIFY THAT SESSION SUPPORT IS AVAILABLE
		pudl_require_extension('session');

		// WHEN THE DB DISCONNECTS, CALL OUR DISCONNECT HANDLER
		$this->pudl->on('disconnect', [$this, 'disconnect']);

		// SET THIS INSTANCE AS PHP'S SESSION HANDLER
		session_set_save_handler($this, true);

		// DIFFERENT SESSION NAME FOR HTTPS CONNECTIONS
		session_name(($options['secure'] ? '__Secure-' : '') . $options['name']);


		// SET PARAMETERS FOR BROWSER SESSION COOKIE
		if (version_compare(PHP_VERSION, '7.3.0', '>=')) {
			unset($options['name']);
			session_set_cookie_params($options);

		} else {
			session_set_cookie_params(
				$options['lifetime'],			// SAVE SESSION FOR LENGTH OF TIME
				$options['path'],				// LIMIT TO A SPECIFIC PATH WITHIN DOMAIN
				$options['domain'],				// DOMAIN THIS SESSION IS TIED TO
				$options['secure'],				// HTTPS ONLY - BLOCK HTTP ACCESS
				$options['httponly']			// HTTP(S) ONLY - BLOCK JAVASCRIPT ACCESS
			);
		}


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
		return implode('-', [
			'session',
			$this->options['name'],
			$this->options['domain'],
			$id,
		]);
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
	// GET THE DATABASE CONNECTION THIS SESSION IS ASSOCIATED WITH
	////////////////////////////////////////////////////////////////////////////
	public function pudl() { return $this->pudl; }




	////////////////////////////////////////////////////////////////////////////
	// GET THE DATABASE TABLE THIS SESSION IS ASSOCIATED WITH
	////////////////////////////////////////////////////////////////////////////
	public function table() { return $this->table; }




	////////////////////////////////////////////////////////////////////////////
	// PRIVATE LOCAL VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $pudl		= NULL;
	private $table		= '';
	private $options	= [];
	private $user		= 0;
	private $hash		= false;
	private $secure		= false;
}
