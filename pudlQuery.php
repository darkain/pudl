<?php

abstract class pudlQuery {


	abstract public function safe($str);



	protected function _top($limit) {
		if (!$this->top) return '';
		if ($limit === false) return '';
		return 'TOP ' . (int) $limit . ' ';
	}



	protected function _column(&$col) {
		$escstart = $this->escstart;
		$escend = $this->escend;
		
		if (!is_array($col)) {
			if ($col === false  ||  $col === ''  ||  $col === null) return '*';
			return $col;
		}

		$query = '';
		$first = true;

		foreach ($col as $key => &$val) {
			if (!$first) $query .= ', ';
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
		$escstart = $this->escstart;
		$escend = $this->escend;

		if (!is_array($table)) return ' FROM ' . $this->_table2($table);

		$query = ' FROM ';
		$first = true;

		foreach ($table as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;

			if (!is_array($val)) {
				$query .= $this->_table2($val) . ' ' . $key;
			} else {
				$query .= $this->_table2($val[0]) . ' ' . $key;
				for ($i=1; $i<count($val); $i++) {
					$query .= self::_joinTable( $val[$i]['join']);

					if (isset($val[$i]['clause'])) {
						$query .= self::_joinClause($val[$i]['clause']);
					}

					if (isset($val[$i]['using'])) {
						$query .= self::_joinUsing($val[$i]['using']);
					}
				}
			}
		}

		return $query;
	}



	protected function _table2(&$table) {
		if ($this->prefix !== false  &&  substr($table, 0, 5) === 'pudl_') {
			return $this->escstart . $this->prefix . substr($table, 5) . $this->escend;
		}

		return $this->escstart . $table . $this->escend;
	}



	protected function _clause(&$clause) {
		if ($clause === false) return '';
		if (!is_array($clause)) return " WHERE $clause";
		if (!count($clause)) return '';
		return " WHERE " . $this->_clause_recurse($clause);
	}


	private function _clause_recurse(&$clause, $or=false) {
		$first = true;
		$query = '';
		foreach ($clause as $key => &$val) {
			if (!$first) $query .= ($or ? ' OR ' : ' AND ');
			$first = false;

			if (is_array($val)) {
				$query .= '(' . $this->_clause_recurse($val, !$or) . ')';
			} else {
				$query .= $val;
			}
		}
		return $query;
	}



	protected function _order(&$order) {
		if ($order === false)  return '';
		if (!is_array($order)) return " ORDER BY $order";
		if (!count($order)) return '';

		$query = " ORDER BY ";
		$first = true;

		foreach ($order as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		return $query;
	}



	protected function _group(&$group) {
		if ($group === false)  return '';
		if (!is_array($group)) return " GROUP BY $group";
		if (!count($group)) return '';

		$query = " GROUP BY ";
		$first = true;

		foreach ($group as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		return $query;
	}	



	protected function _limit($limit, $offset=false) {
		if (!$this->limit) return '';
		if ($limit !== false  &&  $offset === false) return " LIMIT $limit";
		if ($limit !== false  &&  $offset !== false) return " LIMIT $offset,$limit";
		return '';
	}	



	protected function _lock($lock) {
		if ($lock === "SHARE")  return ' LOCK IN SHARE MODE';
		if ($lock === "UPDATE") return ' FOR UPDATE';
		return '';
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
				}
				$query .= ')';

			} else {
				$query .= $val;
			}
		}

		$query .= ')';
		return $query;
	}



	protected function _joinUsing($join_using) {
		if ($join_using === false)  return '';
		if (!is_array($join_using)) return " USING ($join_using)";
		if (!count($join_using)) return '';

		$query = ' USING (';

		$first = true;
		foreach ($join_using as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		$query .= ')';
		return $query;
	}



	protected function _joinTable($join_table) {
		$escstart = $this->escstart;
		$escend = $this->escend;

		if (!is_array($join_table)) return ' LEFT JOIN (' . $this->_table2($join_table) . ')';

		// $query = " LEFT JOIN (";
		$query = " LEFT JOIN ";
		$first = true;

		foreach ($join_table as $key => &$val) {
			// if (!$first) $query .= ', ';
			$query .= $this->_table2($val) . ' ' . $key;
			break;
			// $first = false;
		}

		// $query .= ')';
		return $query;
	}



	protected function _update($data, $safe=false) {
		$escstart = $this->escstart;
		$escend = $this->escend;

		if (!is_array($data)) return $data;

		$query = '';

		$first = true;
		foreach ($data as $column => &$value) {
			$good = false;

			if (is_null($value)) {
				$good = 'NULL';
			} else if (is_array($value)) {
				foreach ($value as $func => $sub_value) {
					if ($func == 'AES_ENCRYPT') {
						if ($safe !== false) $sub_value['key']  = $this->safe($sub_value['key']);
						if ($safe !== false) $sub_value['data'] = $this->safe($sub_value['data']);
						$good = $func . '("' . $sub_value['data'] . '","' . $sub_value['key'] . '")';
					} else {
						if ($safe !== false) $sub_value = $this->safe($sub_value);
						$good = $func . '(' . $sub_value . ')';
					}
					break;
				}
			} else {
				if ($safe !== false) $value = $this->safe($value);
				$good = "'$value'";
			}

			if ($good !== false) {
				if (!$first) $query .= ', ';
				$first  = false;
				$query .= "$escstart$column$escend=$good";
			}
		}

		return $query;
	}	



	public function prefixColumns($table, $col=false) {
		$prefix = array();
		
		if (is_array($table)) {
			foreach ($table as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $subtable) {
						if (is_array($subtable)) {
							foreach ($subtable['join'] as $subkey => $subname) {
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
			}
		}
		return $column;
	}



	protected $escstart = '`';
	protected $escend = '`';
	protected $top = false;
	protected $limit = false;
	protected $prefix = false;
}
