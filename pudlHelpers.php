<?php


class pudlFunction implements pudlValue {
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


	public function pudlValue($db, $quote=true) {
		foreach ($this as $property => $value) {
			$query	= '';
			foreach ($value as $item) {
				if (strlen($query)) $query .= ', ';
				$query .= $db->_value($item);
			}
			return ltrim($property, '_') . '(' . $query . ')';
		}

		throw new pudlException('Invalid pudlFunction');
	}
}



class pudlVoid {
	use pudlHelper;

	public function __call($name, $arguments) {
		return false;
	}
}



class pudlEquals implements pudlValue {
	use pudlHelper;

	public function __construct($value=false, $compare=false, $equals='=') {
		$this->value	= $compare === false ? $value : $compare;
		$this->compare	= $compare === false ? $compare : $value;
		$this->equals	= $equals;

		if (is_null($this->value)) {
			if ($equals === '=') {
				$this->equals = ' IS ';
			} else if ($equals === '!=') {
				$this->equals = ' IS NOT ';
			}
		}
	}

	public function __toString() { return (string) $this->value; }

	public function __call($name, $arguments) {
		$this->value	= forward_static_call_array(['pudl',$name], $arguments);
		return $this;
	}

	public function not() {
		$this->equals	= $this->equals === '=' ? '!=' : ' NOT' . $this->equals;
		return $this;
	}

	public function pudlValue($db, $quote=true) {
		if (pudl_array($this->value)) {
			return '(' . $db->_inSet($this->value) . ')';
		}
		return $db->_value($this->value, $quote);
	}

	public	$value;
	public	$compare;
	public	$equals;
}



class pudlFloat extends pudlEquals {
	use pudlHelper;

	public function __construct($value, $precision=10) {
		parent::__construct($value);
		if ($precision < 1) {
			$this->precision = '1';
		} else {
			$this->precision = '0.' . str_repeat('0', $precision-1) . '1';
		}
	}

	public function pudlValue($db, $quote=true) {
		return 'ABS(' . $db->identifier($this->column)
			. '-' . $db->_value($this->value, $quote)
			. ')<' . $this->precision;
	}

	public	$precision;
	public	$column;
}



class pudlColumn extends pudlEquals {
	use pudlHelper;

	public function __construct($column, $value=false) {
		parent::__construct($value);
		$this->column	= $column;
		$this->args		= func_num_args() > 1;
	}

	public function pudlValue($db, $quote=true) {
		return $db->identifiers($this->column);
	}

	public	$column;
	public	$args;
}



class pudlAs extends pudlColumn {
	use pudlHelper;

	public function __construct($column, $alias, $length=false) {
		parent::__construct($column);
		$this->alias	= $alias;
		$this->length	= $length;
	}

	public function pudlValue($db, $quote=true) {
		return $db->_value($this->column) .
			' AS ' . $db->identifier($this->alias) .
			($this->length === false ? '' : ('('.$this->length.')'));
	}

	public	$alias;
	public	$length;
}



class pudlBetween extends pudlEquals {
	use pudlHelper;

	public function __construct($v1, $v2, $v3=false) {
		if ($v3 === false) {
			parent::__construct([$v1,$v2], false, ' BETWEEN ');
		} else {
			parent::__construct($v1, [$v2,$v3], ' BETWEEN ');
		}
	}

	public function __toString() {
		return (string) $this->value[0] . ', ' . $this->value[1];
	}

	public function pudlValue($db, $quote=true) {
		return $db->_value($this->value[0], $quote)
			. ' AND '
			. $db->_value($this->value[1], $quote);
	}
}



class pudlLike extends pudlEquals {
	use pudlHelper;

	public function __construct($value, $compare, $side) {
		parent::__construct($value, $compare, ' LIKE ');
		$this->left		= ($side & PUDL_START)	? '%' : '';
		$this->right	= ($side & PUDL_END)	? '%' : '';
	}

	public function pudlValue($db, $quote=true) {
		if (!is_object($this->value)) {
			return "'" . $this->left
				. $db->likeEscape($this->value)
				. $this->right . "'";
		}

		return "CONCAT('" . $this->left . "',"
			. $db->_value($this->value)
			. ",'" . $this->right . "')";
	}

	public $left;
	public $right;
}



class pudlRegexp extends pudlEquals {
	use pudlHelper;

	public function __construct($value) {
		parent::__construct(
			func_num_args() === 1 ? $value : func_get_args(),
			false, ' REGEXP '
		);
	}

	public function pudlValue($db, $quote=true) {
		$query = '';
		if (!pudl_array($this->value)) $this->value = [$this->value];
		foreach ($this->value as $item) {
			$query .= is_string($item) ?
				$db->escape(preg_quote($item)) :
				$db->_value($item, false);
		}
		return "'" . $query . "'";
	}
}



class pudlSet extends pudlEquals {
	use pudlHelper;

	public function __construct($value) {
		if (empty($value)) $value = [''];
		parent::__construct($value, false, ' IN ');
	}

	public function pudlValue($db, $quote=true) {
		return '(' . $db->_inSet($this->value) . ')';
	}
}



class pudlAppendSet extends pudlEquals {
	use pudlHelper;
}



class pudlRemoveSet extends pudlEquals {
	use pudlHelper;
}



class pudlRaw implements pudlValue {
	use pudlHelper;

	public function __construct($value) {
		$this->value = $value;
	}

	public function pudlValue($db, $quote=true) {
		return $this->value;
	}

	public $value;
}



class pudlText extends pudlRaw {
	use pudlHelper;

	public function pudlValue($db, $quote=true) {
		return $db->_value($this->value);
	}
}



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
