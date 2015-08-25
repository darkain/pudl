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



class pudlEquals {
	use pudlHelper;

	public function __construct($value, $equals='=') {
		$this->value	= $value;
		$this->equals	= $equals;
		if (is_null($value)) {
			if ($equals === '=') {
				$this->equals = ' IS ';
			} else if ($equals === '!=') {
				$this->equals = ' IS NOT ';
			}
		}
	}

	public function __toString() { return (string) $this->value; }

	public function not() {
		$this->equals = ' NOT' . $this->equals;
		return $this;
	}

	public	$value;
	public	$equals;
}



class pudlColumn extends pudlEquals {
	use pudlHelper;
}



class pudlBetween extends pudlEquals {
	use pudlHelper;

	public function __construct($low, $high) {
		parent::__construct([$low, $high], ' BETWEEN ');
	}
}



class pudlLike extends pudlEquals {
	use pudlHelper;

	public function __construct($value, $side) {
		parent::__construct($value, ' LIKE ');
		$this->left		= ($side & PUDL_START)	? '%' : '';
		$this->right	= ($side & PUDL_END)	? '%' : '';
	}

	public $left;
	public $right;
}



class pudlRegexp extends pudlEquals {
	use pudlHelper;

	public function __construct($value) {
		parent::__construct($value, ' REGEXP ');
	}
}



class pudlSet extends pudlEquals {
	use pudlHelper;

	public function __construct($value) {
		parent::__construct($value, ' IN ');
	}
}
