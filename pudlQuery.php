<?php

abstract class pudlQuery {


	abstract public function safe($value);



	protected function _cache() {
		return '';
	}



	protected function _top($limit) {
		if (!$this->top) return '';
		if ($limit === false) return '';
		return 'TOP ' . (int) $limit . ' ';
	}



	protected function _column(&$col) {
		if (!is_array($col)) {
			if ($col === false  ||  $col === ''  ||  $col === null) return '*';
			return $col;
		}

		$escstart	= $this->escstart;
		$escend		= $this->escend;
		$query		= '';
		$first		= true;

		foreach ($col as $key => $val) {
			if (!$first) $query .= ', ';
			if (is_null($val)) $val = 'NULL';
			if (is_numeric($key)) {
				$query .= $val;
			} else {
				$query .= "$escstart$key$escend.$escstart$val$escend";
			}
			$first = false;
		}

		return $query;
	}



	protected function _table(&$table) {
		if ($this->prefix !== false  &&  substr($table, 0, 5) === 'pudl_') {
			return $this->escstart . $this->prefix . substr($table, 5) . $this->escend;
		}

		return $this->escstart . $table . $this->escend;
	}



	protected function _tables(&$table) {
		$escstart = $this->escstart;
		$escend = $this->escend;

		if (!is_array($table)) return ' FROM ' . self::_table($table);

		$query = ' FROM ';
		$first = true;

		foreach ($table as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;

			if (!is_array($val)) {
				$query .= self::_table($val) . ' ' . $key;
			} else {
				$query .= self::_table($val[0]) . ' ' . $key;
				for ($i=1; $i<count($val); $i++) {
					if (!empty($val[$i]['join'])) {
						$query .= self::_joinTable($val[$i]['join'], '');
					} else if (!empty($val[$i]['cross'])) {
						$query .= self::_joinTable($val[$i]['cross'], 'CROSS');
					} else if (!empty($val[$i]['left'])) {
						$query .= self::_joinTable($val[$i]['left'], 'LEFT');
					} else if (!empty($val[$i]['right'])) {
						$query .= self::_joinTable($val[$i]['right'], 'RIGHT');
					} else if (!empty($val[$i]['natural'])) {
						$query .= self::_joinTable($val[$i]['natural'], 'NATURAL');
					} else if (!empty($val[$i]['inner'])) {
						$query .= self::_joinTable($val[$i]['inner'], 'INNER');
					} else if (!empty($val[$i]['outer'])) {
						$query .= self::_joinTable($val[$i]['outer'], 'OUTER');
					} else if (!empty($val[$i]['hack'])) {
						$query .= ' LEFT JOIN (' . $val[$i]['hack'] . ')';
					}

					if (!empty($val[$i]['clause'])) {
						$query .= self::_joinClause($val[$i]['clause']);
					} else if (!empty($val[$i]['on'])) {
						$query .= self::_joinClause($val[$i]['on']);
					} else if (!empty($val[$i]['using'])) {
						$query .= self::_joinUsing($val[$i]['using']);
					}
				}
			}
		} unset($val);

		return $query;
	}



	protected function _clause(&$clause) {
		if ($clause === false)	return '';
		if (!is_array($clause))	return " WHERE $clause";
		if (!count($clause))	return '';
		return " WHERE " . self::_clause_recurse($clause);
	}


	private function _clause_recurse(&$clause, $or=false) {
		$first = true;
		$query = '';
		foreach ($clause as $key => &$val) {
			if (!$first) $query .= ($or ? ' OR ' : ' AND ');
			$first = false;

			if (is_array($val)) {
				$query .= '(' . self::_clause_recurse($val, !$or) . ')';
			} else {
				$query .= $val;
			}
		} unset($val);
		return $query;
	}



	protected function _order(&$order) {
		if ($order === false)	return '';
		if (!is_array($order))	return " ORDER BY $order";
		if (!count($order))		return '';

		$query = " ORDER BY ";
		$first = true;

		foreach ($order as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		} unset($val);

		return $query;
	}



	protected function _group(&$group) {
		if ($group === false)	return '';
		if (!is_array($group))	return " GROUP BY $group";
		if (!count($group))		return '';

		$query = " GROUP BY ";
		$first = true;

		foreach ($group as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		} unset($val);

		return $query;
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

		$query = '(';
		$first = true;

		foreach($this->union as &$union) {
			if (!$first) $query .= ") UNION $type (";
			$first = false;
			$query .= $union;
		} unset($union);

		return $query . ')';
	}



	protected function _joinClause($join_clause) {
		if ($join_clause === false) return '';
		if (!is_array($join_clause)) return " ON ($join_clause)";
		if (!count($join_clause)) return '';

		$query = ' ON (';
		$first = true;

		foreach ($join_clause as $key => &$val) {
			if (!$first) $query .= ' AND ';
			$first = false;

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

		$query = ' USING (';

		$first = true;
		foreach ($join_using as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		} unset($val);

		$query .= ')';
		return $query;
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
		if (!is_array($data)) return $data;

		$escstart	= $this->escstart;
		$escend		= $this->escend;
		$query		= '';
		$first		= true;

		foreach ($data as $column => $value) {
			if ($value instanceof pudlFunction  &&  isset($value->_INCREMENT)) {
				$good = "$escstart$column$escend+'" . reset($value->_INCREMENT) . "'";
			} else {
				$good = $this->_columnData($value, $safe);
			}

			if (!$first) $query .= ', '; else $first = false;
			$query .= "$escstart$column$escend=$good";
		}

		return $query;
	}



	public function _columnData($value, $safe=false) {
		if (is_null($value)) {
			return 'NULL';

		} else if (is_int($value)  ||  is_float($value)) {
			return $value;

		} else if (is_bool($value)) {
			return $value ? 'TRUE' : 'FALSE';

		} else if (is_string($value)) {
			if ($safe !== false) $value = $this->safe($value);
			return "'$value'";

		} else if (is_array($value)) {
			return 'COLUMN_CREATE(' . $this->_dynamic($value, $safe) . ')';

		} else if ($value instanceof pudlFunction) {
			return $this->_function($value, $safe);
		}


		trigger_error(
			'Invalid data type for column: ' . (gettype($value)=='object'?get_class($value):gettype($value)),
			E_USER_ERROR
		);
	}



	public function _function($data, $safe=false) {
		$query = '';
		foreach ($data as $property => $value) {
			$query	= ltrim($property, '_') . '(';
			$first	= true;
			foreach ($value as $item) {
				if (!$first) $query .= ','; else $first = false;

				if (is_int($value)  ||  is_float($value)) {
					$query .= $value;
				} else {
					if ($safe !== false) $item = $this->safe($item);
					$query .= "'" . $item . "'";
				}
			}
			$query .= ')';
			break;
		}
		return $query;
	}



	public function _dynamic($data, $safe=false) {
		$query = '';
		$first = true;

		foreach ($data as $property => $value) {
			if (!$first) $query .= ','; else $first = false;

			if ($safe !== false) {
				$property = $this->safe($property);
				$value = $this->safe($value);
			}

			$query .= "'" . $property . "'," . $this->_columnData($value);
		}

		return $query;
	}



	public function prefixColumns($table, $col=false, $unprefixed=true) {
		$prefix = array();

		if (is_array($table)) {
			foreach ($table as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $subtable) {
						if (is_array($subtable)) {
							foreach ($subtable['left'] as $subkey => $subname) {
								$fields = $this->listFields($subname);
								foreach ($fields as $field) {
									if (!isset($prefix[$field['Field']])) $prefix[$field['Field']] = $subkey;
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
