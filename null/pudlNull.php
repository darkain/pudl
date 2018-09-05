<?php


require_once(is_owner(__DIR__.'/pudlNullResult.php'));


class pudlNull extends pudl {

	public function __construct($data=[], $autoconnect=true) {
		if (!empty($data['identifier'])) {
			$this->identifier = $data['identifier'];
		}

		parent::__construct($data, $autoconnect);
	}



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlNull($data, $autoconnect);
	}



	protected function process($query) {
		return new pudlNullResult($this);
	}



	public function insertId()	{ return 0; }
	public function updated()	{ return 0; }
	public function errno()		{ return 0; }
	public function error()		{ return ''; }

}
