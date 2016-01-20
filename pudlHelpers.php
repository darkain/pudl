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


class pudlException extends Exception {}



class pudlFunction {
	use pudlHelper;

	public static function __callStatic($name, $arguments) {
		return forward_static_call_array(['pudl', '_'.$name], $arguments);
	}


	/*
	If CONVERT_TZ returns NULL, make sure the timezone table of mysql is filled
	Note that this might need to be ran on ALL MySQL instances in a cluster!
		install mysql-community-server-tools
		mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
	*/
	public static function timestamp($time=false) {
		global $db;
		return pudl::convert_tz(
			self::from_unixtime($time !== false ? $time : $db->time()),
			new pudlGlobal('session.time_zone'),
			'UTC'
		);
	}

	public static function binary($data, $pad=0) {
		return pudl::unhex(str_pad(bin2hex($data), $pad, '0'));
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

	public function __construct($value=false, $equals='=') {
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

	public function __call($name, $arguments) {
		$this->value = forward_static_call_array(['pudl',$name], $arguments);
		return $this;
	}

	public function not() {
		$this->equals = $this->equals === '=' ? '!=' : ' NOT' . $this->equals;
		return $this;
	}

	public	$value;
	public	$equals;
}



class pudlColumn extends pudlEquals {
	use pudlHelper;

	public function __construct($column, $value=false) {
		parent::__construct($value);
		$this->column	= $column;
		$this->args		= func_num_args() > 1;
	}

	public	$column;
	public	$args;
}



class pudlBetween extends pudlEquals {
	use pudlHelper;

	public function __construct($low, $high) {
		parent::__construct([$low, $high], ' BETWEEN ');
	}

	public function __toString() {
		return (string) $this->value[0] . ', ' . $this->value[1];
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
		parent::__construct(
			func_num_args() === 1 ? $value : func_get_args(),
			' REGEXP '
		);
	}
}



class pudlSet extends pudlEquals {
	use pudlHelper;

	public function __construct($value) {
		if (empty($value)) $value = [''];
		parent::__construct($value, ' IN ');
	}
}



class pudlAppendSet extends pudlEquals {
	use pudlHelper;
}



class pudlRemoveSet extends pudlEquals {
	use pudlHelper;
}



class pudlRaw {
	public function __construct($value) {
		$this->value = $value;
	}

	public $value;
}



class pudlText extends pudlRaw {}



class pudlVariable extends pudlRaw {
	public function __construct($name) {
		parent::__construct('@'.$name);
	}
}



class pudlGlobal extends pudlRaw {
	public function __construct($name) {
		parent::__construct('@@'.$name);
	}
}
