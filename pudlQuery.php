<?php

abstract class pudlQuery {


	public function safe($value) {
		if (is_int($value)  ||  is_float($value)) return $value;

		return str_replace([
			'\\',   "\0", "'",  '"',  chr(8), "\n", "\r", "\t", "\Z", '%',
			'_',  '(',  ')',  '[',  ']',  '{',  '}',  '-',  '+',  '&',  '.',
			',',  '!',  '$',  '^',  ';',
		], [
			'\\\\', '\0', "\'", '\"', '\b',   '\n', '\r', '\t', '\Z', '\%',
			'\_', '\(', '\)', '\[', '\]', '\{', '\}', '\-', '\+', '\&', '\.',
			'\,', '\!', '\$', '\^', '\;',
		], (string)$value);
	}



	protected function _cache() {
		return '';
	}



	protected function _top($limit) {
		if (!$this->top) return '';
		if ($limit === false) return '';
		return 'TOP ' . (int) $limit . ' ';
	}



	protected function _column($column) {
		if (!is_array($column)) {
			if ($column === false  ||
				$column === ''  ||
				$column === null  ||
				$column === '*') return '*';
			return $this->_columnValue(false, $column);
		}

		$query		= '';
		$first		= true;
		foreach ($column as $key => $value) {
			if (strlen($query)) $query .= ', ';
			$query .= $this->_columnValue($key, $value);
		}

		return $query;
	}


	protected function _columnValue($key, $value) {
		if (is_null($value)) {
			return 'NULL';

		} else if (is_int($value)  ||  is_float($value)) {
			return $value;

		} else if (is_bool($value)) {
			return $value ? 'TRUE' : 'FALSE';

		} else if (is_int($key)) {
			return $value;

		} else if (!empty($key)) {
			return $this->escstart . $key . $this->escend . $this->escstart . $value . $this->escend;

		} else if (is_string($value)) {
			return $value;

		} else {
			trigger_error(
				'Invalid data type for column: ' .
				(gettype($value)==='object'?get_class($value):gettype($value)),
				E_USER_ERROR
			);
		}
	}



	protected function _table($table) {
		$list = explode('.', $table);

		if ($this->prefix !== false) {
			$table = array_pop($list);
			if (substr($table, 0, 5) === 'pudl_') {
				$table = $this->prefix . substr($table, 5);
			}
			$list[] = $table;
		}
		return $this->escstart . implode($this->escend.'.'.$this->escstart, $list) . $this->escend;
	}



	protected function _tables($table) {
		$escstart = $this->escstart;
		$escend = $this->escend;

		if (!is_array($table)) return ' FROM ' . self::_table($table);

		$query = ' FROM ';
		$first = true;

		foreach ($table as $key => &$val) {
			if (!$first) $query .= ', '; else $first = false;

			if (!is_array($val)) {
				$query .= self::_table($val);
				if (!is_int($key)) $query .= ' ' . $key;
			} else {
				$query .= self::_table(reset($val));
				if (!is_int($key)) $query .= ' ' . $key;
				foreach ($val as $join) {
					if (!empty($join['join'])) {
						$query .= self::_joinTable($join['join'], '');
					} else if (!empty($join['cross'])) {
						$query .= self::_joinTable($join['cross'], 'CROSS');
					} else if (!empty($join['left'])) {
						$query .= self::_joinTable($join['left'], 'LEFT');
					} else if (!empty($join['right'])) {
						$query .= self::_joinTable($join['right'], 'RIGHT');
					} else if (!empty($join['natural'])) {
						$query .= self::_joinTable($join['natural'], 'NATURAL');
					} else if (!empty($join['inner'])) {
						$query .= self::_joinTable($join['inner'], 'INNER');
					} else if (!empty($join['outer'])) {
						$query .= self::_joinTable($join['outer'], 'OUTER');
					} else if (!empty($join['hack'])) {
						$query .= ' LEFT JOIN (' . $join['hack'] . ')';
					}

					if (!empty($join['clause'])) {
						$query .= self::_joinClause($join['clause']);
					} else if (!empty($join['on'])) {
						$query .= self::_joinClause($join['on']);
					} else if (!empty($join['using'])) {
						$query .= self::_joinUsing($join['using']);
					}
				}
			}
		} unset($val);

		return $query;
	}



	protected function _clause($clause, $type='WHERE') {
		if ($clause === false)	return '';
		if ($clause instanceof pudlStringResult) return (string) $clause;
		if (is_array($clause))	return ' ' . $type . ' ' . self::_clause_recurse($clause);
		if (is_object($clause))	return ' ' . $type . ' ' . self::_clause_recurse($clause);
		return ' ' . $type . ' ' . $clause;
	}



	private function _clause_recurse($clause, $or=false, $safe=false) {
		static $depth = 0;
		if ($depth > 31) {
			trigger_error('Recursion limit reached', E_USER_ERROR);
			return '';
		}
		$depth++;

		$query = '';
		foreach ($clause as $key => $value) {
			if (strlen($query)) $query .= ($or ? ' OR ' : ' AND ');

			if (is_string($key)) {
				$key = explode('.', $key);
				$query .= $this->escstart .
					implode($this->escend.'.'.$this->escstart, $key) .
					$this->escend . '=';
			}

			if (is_int($value)  ||  is_float($value)  ||  is_string($value)) {
				$query .= $value;

			} else if (is_null($value)) {
				$query .= 'NULL';

			} else if (is_bool($value)) {
				$query .= $value ? 'TRUE' : 'FALSE';

			} else if ($value instanceof pudlFunction) {
				$query .= $this->_function($value, $safe);

			} else if ($value instanceof pudlStringResult) {
				$query .= '(' . ((string)$value) . ')';

			} else if (is_array($value)  ||  is_object($value)) {
				$query .= '(' . self::_clause_recurse($value, !$or, $safe) . ')';

			} else {
				trigger_error(
					'Invalid data type for clause: ' . gettype($value),
					E_USER_ERROR
				);
			}
		}

		$depth--;
		return $query;
	}


	protected function _clauseId($column, $id) {
		$column = explode('.', $column);
		if (is_array($id)) $id = $id[end($column)];
		$id = is_null($id) ? ' IS NULL' : "='$id'";

		return $this->escstart .
			implode($this->escend.'.'.$this->escstart, $column) .
			$this->escend . $id;
	}



	protected function _order($order) {
		if ($order === false)	return '';
		if (!is_array($order))	return " ORDER BY $order";
		if (!count($order))		return '';
		return ' ORDER BY ' . implode(',', $order);
	}



	protected function _group($group) {
		if ($group === false)	return '';
		if (!is_array($group))	return " GROUP BY $group";
		if (!count($group))		return '';
		return ' GROUP BY ' . implode(',', $group);
	}



	protected function _limit($limit, $offset=false) {
		if (!$this->limit) return '';
		if ($limit !== false  &&  $offset === false) return " LIMIT $limit";
		if ($limit !== false  &&  $offset !== false) return " LIMIT $offset,$limit";
		return '';
	}



	protected function _lock($lock) {
		if ($lock === 'SHARE')	return ' LOCK IN SHARE MODE';
		if ($lock === 'UPDATE')	return ' FOR UPDATE';
		if ($lock === true)		return ' FOR UPDATE';
		return '';
	}



	protected function _union($type='') {
		if ($type !== 'ALL'  &&  $type !== 'DISTINCT') $type = '';
		return '(' . implode(") UNION $type (", $this->union) . ')';
	}



	protected function _joinClause($join_clause) {
		if ($join_clause === false) return '';
		if (!is_array($join_clause)) return " ON ($join_clause)";
		if (!count($join_clause)) return '';

		$query = ' ON (';
		$first = true;

		foreach ($join_clause as $key => &$val) {
			if (!$first) $query .= ' AND '; else  $first = false;

			if (is_array($val)) {
				$query .= '(';
				$val_first = true;
				foreach ($val as $val_key => &$val_val) {
					if (!$val_first) $query .= ' OR ';
					$val_first = false;
					$query .= $val_val;
				} unset($val_val);
				$query .= ')';

			} else {
				$query .= $val;
			}
		} unset($val);

		$query .= ')';
		return $query;
	}



	protected function _joinUsing($join_using) {
		if ($join_using === false)	return '';
		if (!is_array($join_using))	return " USING ($join_using)";
		if (!count($join_using))	return '';
		return ' USING (' . implode(',', $join_using) . ')';
	}



	protected function _joinTable($join, $type='LEFT') {
		if (!is_array($join)) return " $type JOIN (" . self::_table($join) . ')';

		$escstart	= $this->escstart;
		$escend		= $this->escend;
		$query		= " $type JOIN ";

		foreach ($join as $key => &$val) {
			$query .= self::_table($val) . ' ' . $key;
			break;
		} unset($val);

		return $query;
	}



	protected function _update($data, $safe=false) {
		if (!is_array($data)  &&  !is_object($data)) return $data;

		$escstart	= $this->escstart;
		$escend		= $this->escend;
		$query		= '';
		$first		= true;

		foreach ($data as $column => $value) {
			if ($value instanceof pudlFunction  &&  isset($value->__INCREMENT)) {
				$good = "$escstart$column$escend+'" . reset($value->__INCREMENT) . "'";
			} else {
				$good = $this->_columnData($value, $safe);
			}

			if (!$first) $query .= ', '; else $first = false;
			$query .= "$escstart$column$escend=$good";
		}

		return $query;
	}



	protected function _columnData($value, $safe=false) {
		if (is_null($value)) {
			return 'NULL';

		} else if (is_int($value)  ||  is_float($value)) {
			return $value;

		} else if (is_bool($value)) {
			return $value ? 'TRUE' : 'FALSE';

		} else if (is_string($value)  &&  $safe !== false) {
			return "'" . $this->safe($value) . "'";

		} else if (is_string($value)) {
			return "'$value'";

		} else if ($value instanceof pudlFunction) {
			return $this->_function($value, $safe);

		} else if ($value instanceof pudlStringResult) {
			return '(' . ((string)$value) . ')';

		} else if (is_array($value)  ||  is_object($value)) {
			if (empty($value)) return 'NULL';
			return 'COLUMN_CREATE(' . $this->_dynamic($value, $safe) . ')';
		}


		trigger_error(
			'Invalid data type for column: ' .
			(gettype($value)==='object'?get_class($value):gettype($value)),
			E_USER_ERROR
		);
	}



	protected function _function($data, $safe=false) {
		foreach ($data as $property => $value) {
			$query	= '';
			foreach ($value as $item) {
				if (strlen($query)) $query .= ',';
				$query .= $this->_columnData($item, $safe);
			}
			return ltrim($property, '_') . '(' . $query . ')';
		}

		trigger_error('Invalid pudlFunction', E_USER_ERROR);
	}



	protected function _dynamic($data, $safe=false) {
		static $depth = 0;
		if ($depth > 31) {
			trigger_error('Recursion limit reached', E_USER_ERROR);
			return '';
		}
		$depth++;

		$query = '';
		foreach ($data as $property => $value) {
			if (strlen($query)) $query .= ',';

			if ($safe !== false) $property = $this->safe($property);

			$query .= "'" . $property . "'," . $this->_columnData($value, $safe);
		}

		$depth--;
		return $query;
	}



	public function prefixColumns($table, $col=false, $unprefixed=true) {
		$joiners = array(
			'join', 'cross', 'left', 'right',
			'natural', 'inner', 'outer', 'hack',
		);

		$prefix = array();

		if (!is_array($table)) return false;

		foreach ($table as $key => $val) {
			if (is_array($val)) {
				foreach ($val as $subtable) {
					if (is_array($subtable)) {
						foreach ($subtable as $joinkey => $jointable) {
							if (in_array($joinkey, $joiners)) {
								foreach ($jointable as $subkey => $subname) {
									$fields = $this->listFields($subname);
									foreach ($fields as $field) {
										if (!isset($prefix[$field['Field']])) $prefix[$field['Field']] = $subkey;
									}
								}
							}
						}
					} else {
						$fields = $this->listFields($subtable);
						foreach ($fields as $field) {
							if (!isset($prefix[$field['Field']])) $prefix[$field['Field']] = $key;
						}
					}
				}
			} else {
				$fields = $this->listFields($val);
				foreach ($fields as $field) {
					if (!isset($prefix[$field['Field']])) $prefix[$field['Field']] = $key;
				}
			}
		}

		if ($col === false) return $prefix;

		$column = array();
		foreach ($col as $val) {
			if (isset($prefix[$val])) {
				$column[] = $prefix[$val] . '.' . $val;
			} else if ($unprefixed) {
				$column[] = $val;
			}
		}
		return $column;
	}



	public function _escape($which=PUDL_BOTH) {
		switch ($which) {
			case PUDL_START:	return $this->escstart;
			case PUDL_END:		return $this->escend;
		}
		return $this->escstart . $this->escend;
	}



	protected $escstart	= '`';
	protected $escend	= '`';
	protected $top		= false;
	protected $limit	= false;
	protected $prefix	= false;
	protected $union	= false;
}
