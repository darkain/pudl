<?php



////////////////////////////////////////////////////////////////////////////////
// DO NOTHING
// THIS IS USED TO CREATE FAKE VERSIONS OF SOME CLASSES THAT DO NOTHING
////////////////////////////////////////////////////////////////////////////////
class pudlVoid implements pudlHelper {
	public function __call($name, $arguments) {
		return false;
	}
}




////////////////////////////////////////////////////////////////////////////////
// FLOATING POINT COMPARISON
// WE CANNOT COMPARE FLOATS DIRECTLY, SO INSTEAD WE COMPARE THE DIFFERENCE OF
// TWO FLOATS AND SEE IF THAT VALUE IS WITHIN A PARTICULAR MARGIN FOR ERROR
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// USED WHERE PUDL EXPECTS A STRING BUT WE NEED A SQL TABLE'S COLUMN INSTEAD
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// SQL COUNT() FUNCTION
////////////////////////////////////////////////////////////////////////////////
class pudlCount extends pudlColumn {
	public function __construct($column='*') {
		parent::__construct($column);
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return 'COUNT(' . $pudl->identifiers($this->column) . ')';
	}
}




////////////////////////////////////////////////////////////////////////////////
// SQL ALIAS
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// SQL "BETWEEN" TWO DIFFERENT VALUES
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// USE SQL STRING "LIKE" COMPARISON
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// USE SQL STRING "REGEXP" COMPARISON
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// CHECK IF ONE VALUE IS INSIDE OF ANOTHER SET OF VALUES
////////////////////////////////////////////////////////////////////////////////
class pudlSet extends pudlEquals {
	public function __construct($value) {
		if (empty($value)) $value = [''];
		parent::__construct($value, false, ' IN ');
	}

	public function pudlValue(pudl $pudl, $quote=true) {
		return '(' . $pudl->_inSet($this->value) . ')';
	}
}




////////////////////////////////////////////////////////////////////////////////
// ADD AN ITEM TO A SET VALUE
////////////////////////////////////////////////////////////////////////////////
class pudlAppendSet extends pudlEquals {}




////////////////////////////////////////////////////////////////////////////////
// REMOVE AN ITEM FROM A SET VALUE
////////////////////////////////////////////////////////////////////////////////
class pudlRemoveSet extends pudlEquals {}




////////////////////////////////////////////////////////////////////////////////
// ???
////////////////////////////////////////////////////////////////////////////////
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




////////////////////////////////////////////////////////////////////////////////
// FORCE "AND" JOINING OF COMPARISON CLAUSE GROUPS
////////////////////////////////////////////////////////////////////////////////
class pudlAnd implements pudlHelper {
	public function __construct($clause) {
		$this->clause	= $clause;
		$this->joiner	= ' AND ';
	}

	public $clause;
	public $joiner;
}




////////////////////////////////////////////////////////////////////////////////
// FORCE "OR" JOINING OF COMPARIOSON CLAUSE GROUPS
////////////////////////////////////////////////////////////////////////////////
class pudlOr extends pudlAnd {
	public function __construct($clause) {
		parent::__construct($clause);
		$this->joiner	= ' OR ';
	}
}




////////////////////////////////////////////////////////////////////////////////
// FORCE A STRING COMPARISON WHERE PUDL EXPECTS A COLUMN OR OTHER VALUE
////////////////////////////////////////////////////////////////////////////////
class pudlString {
	public function __construct($value='') {
		$this->value = (string) $value;
	}

	public function __toString() {
		return (string) $this->value;
	}

	public $value;
}




////////////////////////////////////////////////////////////////////////////////
// COMPLEX DATA TYPES FOR CREATE TABLE SYNTAX GENERATOR
////////////////////////////////////////////////////////////////////////////////
class pudlType {
	public function __construct($type, $value) {
		$this->type		= $type;
		$this->value	= $value;
	}

	public $type;
	public $value;
}
