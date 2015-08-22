<?php

//Default Behavior
define('PUDL_DEFAULT',	0);

//Array Types
define('PUDL_ARRAY',	1);
define('PUDL_NUMBER',	2);
define('PUDL_BOTH',		3);

//Escapes
define('PUDL_START',	1);
define('PUDL_END',		2);
//define('PUDL_BOTH',	3);


trait pudlHelper {}


class pudlFunction {
	use pudlHelper;

	public static function __callStatic($name, $arguments) {
		return forward_static_call_array(['pudl', $name], $arguments);
	}

	public static function timestamp() {
		global $db;
		return self::from_unixtime($db->time());
	}

	public static function binary($data) {
		return pudl::unhex(bin2hex($data));
	}

	public static function increment($amount=1) {
		return pudl::_increment($amount);
	}
}



class pudlVoid {
	use pudlHelper;

	public function __call($name, $arguments) {
		return false;
	}
}



class pudlLike {
	use pudlHelper;

	public function __construct($query) {
		$this->query = $query;
	}

	public function __toString() { return $this->query; }

	private	$query;
	public	$left	= '';
	public	$right	= '';
	public	$not	= '';
}
