<?php



class pudlFunction implements pudlValue, pudlHelper {
	public static function __callStatic($name, $arguments) {
		return forward_static_call_array(['pudl', '_'.$name], $arguments);
	}


	/*
	If CONVERT_TZ returns NULL, make sure the timezone table of mysql is filled
	Note that this might need to be ran on ALL MySQL instances in a cluster!
		install mysql-community-server-tools
		mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
	*/
	/** @suppress PhanNonClassMethodCall */
	public static function timestamp($time) {
		return pudl::convert_tz(
			static::from_unixtime(
				is_object($time) ? $time->time() : ((int)$time)
			),
			new pudlGlobal('time_zone'),
			'UTC'
		);
	}

	public static function binary($data, $pad=0) {
		return pudl::unhex(str_pad(bin2hex($data), $pad, '0'));
	}

	public static function increment($amount=1) {
		return pudl::_increment($amount);
	}


	public function pudlValue(pudl $pudl, $quote=true) {
		foreach ($this as $property => $value) {
			$query	= '';
			foreach ($value as $item) {
				if (strlen($query)) $query .= ', ';
				$query .= $pudl->_value($item);
			}
			return ltrim($property, '_') . '(' . $query . ')';
		}

		throw new pudlFunctionException($pudl, 'Invalid pudlFunction');
	}
}



class pudlVoid implements pudlHelper {
	public function __call($name, $arguments) {
		return false;
	}
}



class pudlEquals implements pudlValue, pudlHelper {
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

	public function pudlValue(pudl $pudl, $quote=true) {
		if (pudl_array($this->value)) {
			return '(' . $pudl->_inSet($this->value) . ')';
		}
		return $pudl->_value($this->value, $quote);
	}

	public	$value;
	public	$compare;
	public	$equals;
}



class pudlFloat extends pudlEquals {
	public function __construct($value, $precision=10) {
		parent::__construct($value);
		if ($precision < 1) {
			$this->precision = '1';
		} else {
			$this->precision = '0.' . str_repeat('0', $precision-1) . '1';
		}
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return 'ABS(' . $pudl->identifier($this->column)
			. '-' . $pudl->_value($this->value, $quote)
			. ')<' . $this->precision;
	}

	public	$precision;
	public	$column;
}



class pudlColumn extends pudlEquals {
	public function __construct($column, $value=false) {
		parent::__construct($value);
		$this->args = func_num_args() > 1;

		if (!pudl_array($column)) {
			$this->column	= $column;
		} else if (count($column) > 1  &&  is_int($column[0])) {
			$this->column	= $column[1];
		} else {
			$this->column	= implode('.', $column);
		}
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return $pudl->identifiers($this->column);
	}

	public	$column;
	public	$args;
}



class pudlCount extends pudlColumn {
	public function __construct($column='*') {
		parent::__construct($column);
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return 'COUNT(' . $pudl->identifiers($this->column) . ')';
	}
}



class pudlAs extends pudlColumn {
	public function __construct($column, $alias, $length=false) {
		parent::__construct($column);
		$this->alias	= $alias;
		$this->length	= $length;
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return $pudl->_value($this->column) .
			' AS ' . $pudl->identifier($this->alias) .
			($this->length === false ? '' : ('('.$this->length.')'));
	}

	public	$alias;
	public	$length;
}



class pudlBetween extends pudlEquals {
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

	public function pudlValue(pudl $pudl, $quote=true) {
		return $pudl->_value($this->value[0], $quote)
			. ' AND '
			. $pudl->_value($this->value[1], $quote);
	}
}



class pudlLike extends pudlEquals {
	public function __construct($value, $compare, $side) {
		parent::__construct($value, $compare, ' LIKE ');
		$this->left		= ($side & PUDL_START)	? '%' : '';
		$this->right	= ($side & PUDL_END)	? '%' : '';
		$this->raw		= ($side === PUDL_NONE);
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		if (!is_object($this->value)) {
			return "'" . $this->left
				. $pudl->likeEscape($this->value, $this->raw)
				. $this->right . "'";
		}

		return "CONCAT('" . $this->left . "',"
			. $pudl->_value($this->value)
			. ",'" . $this->right . "')";
	}

	public $left;
	public $right;
	public $raw;
}



class pudlRegexp extends pudlEquals {
	public function __construct($value) {
		parent::__construct(
			func_num_args() === 1 ? $value : func_get_args(),
			false, ' REGEXP '
		);
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		$query = '';
		if (!pudl_array($this->value)) $this->value = [$this->value];
		foreach ($this->value as $item) {
			$query .= is_string($item) ?
				$pudl->escape(preg_quote($item)) :
				$pudl->_value($item, false);
		}
		return "'" . $query . "'";
	}
}



class pudlSet extends pudlEquals {
	public function __construct($value) {
		if (empty($value)) $value = [''];
		parent::__construct($value, false, ' IN ');
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return '(' . $pudl->_inSet($this->value) . ')';
	}
}



class pudlAppendSet extends pudlEquals {}
class pudlRemoveSet extends pudlEquals {}



class pudlSort extends pudlEquals {
	public function __construct($sort, $column=false) {
		parent::__construct($sort, false, ' ');
		$this->column = $column;
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return $this->value;
	}

	public $column;
}



class pudlRaw implements pudlValue, pudlHelper {
	public function __construct(/* ...$values */) {
		$this->value = func_get_args();
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		$query = '';
		foreach ($this->value as $item) {
			if (strlen($query)) $query .= $this->joiner;

			if ($item instanceof pudlValue) {
				$query .= $pudl->_value($item);
			} else {
				$query .= $item;
			}
		}
		return $query;
	}

	public $value	= [];
	public $joiner	= ',';
}



class pudlText extends pudlRaw {
	public function pudlValue(pudl $pudl, $quote=true) {
		$query = '';
		foreach ($this->value as $item) {
			if (strlen($query)) $query .= $this->joiner;
			$query .= $pudl->_value($item);
		}
		return $query;
	}
}



class pudlVariable extends pudlRaw {
	public function __construct($name) {
		parent::__construct('@'.$name);
	}
}



class pudlGlobal extends pudlRaw {
	public function __construct($name, $global=false) {
		if (is_string($global)) {
			parent::__construct('@@'.$global.'.'.$name);
		} else if ($global) {
			parent::__construct('@@GLOBAL.'.$name);
		} else {
			parent::__construct('@@SESSION.'.$name);
		}
	}
}



class pudlAnd implements pudlHelper {
	public function __construct($clause) {
		$this->clause	= $clause;
		$this->joiner	= ' AND ';
	}

	public $clause;
	public $joiner;
}



class pudlOr extends pudlAnd {
	public function __construct($clause) {
		parent::__construct($clause);
		$this->joiner	= ' OR ';
	}
}



class pudlString {
	public function __construct($value='') {
		$this->value = (string) $value;
	}

	public function __toString() {
		return (string) $this->value;
	}

	public $value;
}
