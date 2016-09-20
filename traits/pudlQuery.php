<?php


trait pudlQuery {


	public function escape($value) {
		switch (true) {
			case is_int($value):
			case is_float($value):
			case is_null($value):
			case is_bool($value):
				return $value;
		}

		return str_replace(
			['\\',		"\0",	"\x08",	"\x26",	"'",	'"',	"\n",	"\r",	"\t"],
			['\\\\',	'\0',	'\b',	'\Z',	"\'",	'\"',	'\n',	'\r',	'\t'],
			(string)$value
		);
	}



	public function likeEscape($value) {
		return addcslashes($this->escape($value), '%_');
	}



	public function setEscape($value) {
		return str_replace(',', '', $value);
	}



	protected function _cache() {
		return '';
	}



	public function _value($value, $quote=true, $isnull=false) {
		static $depth = 0;
		$query = false;

		if ($depth++ > 31) {
			throw new pudlException('Recursion limit reached');
			return $query;
		}

		switch (true) {

			//THIS IS FOR AND/OR RECUSION, HANDLED ELSEWHERE
			//IN THIS CASE, DO NOTHING!
			case is_array($value): break;


			case is_int($value):
			case is_float($value):
				if (is_nan($value)  ||  is_infinite($value)) {
					$query = $isnull ? ' IS NULL' : 'NULL';
				} else {
					$query = $value;
				}
			break;


			case is_string($value):
				$query = $quote ? "'".$this->escape($value)."'" : $value;
			break;


			case is_bool($value):
				$query = $value ? 'TRUE' : 'FALSE';
			break;


			case is_null($value):
				$query = $isnull ? ' IS NULL' : 'NULL';
			break;


			case $value instanceof pudlValue:
				$query = $value->pudlValue($this, $quote);
			break;


			case is_callable([$value, '__toString']):
				$query = $quote
					? "'" . $this->escape((string)$value) . "'"
					: (string)$value;
			break;


			default:
				return $this->_invalidType($value);
		}


		$depth--;
		return $query;
	}



	private function _regexp($value) {
		$query = '';
		if (!pudl_array($value)) $value = [$value];
		foreach ($value as $item) {
			$query .= is_string($item) ?
				$this->escape(preg_quote($item)) :
				$this->_value($item, false);
		}
		return "'".$query."'";
	}



	protected function _column($column) {
		if ($column instanceof pudlId) {
			$column = key( $column->pudlId() );
		}

		if (!pudl_array($column)) {
			switch ($column) {
				case '':
				case '*':
				case null:
				case false:
					return '*';
			}
			return $this->_value($column, false);
		}

		$query = '';
		foreach ($column as $key => $value) {
			if (strlen($query)) $query .= ', ';
			if (is_string($key)) {
				if (is_string($value)) {
					$query .= $this->identifiers($value);
				} else {
					$query .= $this->_value($value, true);
				}
				$query .= ' AS ' . $this->identifier($key);
			} else {
				$query .= $this->_value($value, is_string($key));
			}
		}

		return $query;
	}



	public function identifier($identifier) {
		if ($identifier instanceof pudlHelper) {
			return $this->_value($identifier);
		}

		return $this->identifier . str_replace(
			$this->identifier,
			$this->identifier.$this->identifier,
			$identifier
		) . $this->identifier;
	}



	public function identifiers($identifiers, $prefix=false) {
		if ($identifiers === false) return '';

		//PUDL HELPERS HAVE SPECIAL HANDLERS
		if ($identifiers instanceof pudlHelper) {
			return $this->_value($identifiers);
		}

		//PARSE OUT STRING
		$dynamic	= explode('#', $identifiers);
		$list		= explode('.', $dynamic[0]);

		//VERIFY TOTAL NUMBER OF PARTS
		if (count($dynamic) > 2) throw new pudlException(
			'Wrong column format for dynamic column'
		);

		//CLEAN UP TABLE AND COLUMN NAMES
		foreach ($list as &$item) {
			$item = trim($item);
			if (!strlen($item)) throw new pudlException('Wrong column name');
		} unset($item);

		//PROCESS TABLE NAME
		if ($prefix !== false  &&  $this->prefix !== false) {
			$table = array_pop($list);
			if (substr($table, 0, 5) === 'pudl_') {
				$table = $this->prefix . substr($table, 5);
			}
			$list[] = $table;
		}

		//CLEAN UP EACH PART OF THE IDENTIFIER
		foreach ($list as &$item) $item = $this->identifier($item);
		unset($item);

		//EARLY OUT IF WE ARE NOT IN A DYNAMIC COLUMN
		$return		= implode('.', $list);
		if (count($dynamic) === 1) return $return;


		//PARSE OUT DATA TYPE
		$parts		= explode(':', $dynamic[1]);
		if (count($parts) !== 2) throw new pudlException(
			'Wrong column format for dynamic column'
		);

		//SEPARATE AND VERIFY LENGTH OF EACH SECTION
		$items		= explode('.', $parts[0]);
		foreach ($items as &$item) {
			$item = trim($item);
			if (!strlen($item)) throw new pudlException(
				'Wrong column name for dynamic column'
			);
		} unset($item);

		//RECURSIVELY GENERATE COLUMN_GET FUNCTIONS
		while (count($items)) {
			$return	= 'COLUMN_GET(' . $return . ', '
					. $this->_value( array_shift($items) ) . ' AS '
					. (count($items) ? 'BINARY' : $this->dynamic_type($parts[1])) . ')';
		}

		return $return;
	}



	public function _table($table) {
		return $this->identifiers($table, true);
	}



	protected function _tables($table) {
		if ($table === false)		return;
		if (is_string($table))		return ' FROM ' . $this->_table($table);
		if (!pudl_array($table))	return $this->_invalidType($value, 'table');

		$query = '';
		foreach ($table as $key => $value) {
			if (strlen($query)) $query .= ', ';

			if (!pudl_array($value)) {
				if ($value instanceof pudlStringResult) {
					$query .= (string) $value;
				} else {
					$query .= $this->_table($value);
				}
				if (is_string($key)) $query .= ' AS ' . $this->identifier($key);

			} else {
				$query .= $this->_table(reset($value));
				if (is_string($key)) $query .= ' AS ' . $this->identifier($key);
				foreach ($value as $join) {
					if (!empty($join['join'])) {
						$query .= $this->_joinTable($join['join'], false);
					} else if (!empty($join['cross'])) {
						$query .= $this->_joinTable($join['cross'], 'CROSS');
					} else if (!empty($join['left'])) {
						$query .= $this->_joinTable($join['left'], 'LEFT');
					} else if (!empty($join['right'])) {
						$query .= $this->_joinTable($join['right'], 'RIGHT');
					} else if (!empty($join['natural'])) {
						$query .= $this->_joinTable($join['natural'], 'NATURAL');
					} else if (!empty($join['inner'])) {
						$query .= $this->_joinTable($join['inner'], 'INNER');
					} else if (!empty($join['outer'])) {
						$query .= $this->_joinTable($join['outer'], 'OUTER');
					} else if (!empty($join['hack'])) {
						$query .= ' LEFT JOIN (' . $join['hack'] . ')';
					}

					if (!empty($join['clause'])) {
						$query .= $this->_clause($join['clause'], 'ON');
					} else if (!empty($join['on'])) {
						$query .= $this->_clause($join['on'], 'ON');
					} else if (!empty($join['using'])) {
						$query .= $this->_joinUsing($join['using']);
					}
				}
			}
		}

		return ' FROM ' . $query;
	}



	protected function _clause($clause, $type='WHERE') {
		if ($clause === false)	return '';

		if ($clause instanceof pudlStringResult) return (string) $clause;

		if ($clause instanceof pudlId) {
			return ' ' . $type . ' (' . $this->_clauseRecurse($clause->pudlId()) .')';
		}

		if (is_array($clause)  ||  is_object($clause)) {
			if (empty($clause))	return '';
			return ' ' . $type . ' (' . $this->_clauseRecurse($clause) .')';
		}

		return ' ' . $type . ' (' . $clause . ')';
	}



	protected function _order($order) {
		if (is_string($order)) return ' ORDER BY ' . $order;
		if (!is_array($order)  &&  !is_object($order)) return '';
		if ($order instanceof pudlStringResult) return (string) $order;
		if (empty($order)) return '';
		return ' ORDER BY ' . $this->_clauseRecurse($order,', ');
	}



	protected function _group($group) {
		if ($group === false)	return '';
		if ($group instanceof pudlStringResult) return (string) $group;
		if (is_array($group))	return ' GROUP BY ' . $this->_clauseRecurse($group,', ');
		if (is_object($group))	return ' GROUP BY ' . $this->_clauseRecurse($group,', ');
		return ' GROUP BY ' . $group;
	}



	private function _clauseRecurse($clause, $joiner=' AND ') {
		static $depth = 0;
		$query = '';

		if ($depth > 31) {
			throw new pudlException('Recursion limit reached');
			return '';
		}

		if ($clause instanceof pudlAnd) {
			return $this->_clauseRecurse($clause->clause, $clause->joiner);
		}

		if ($clause instanceof pudlColumn  &&  $clause->args) {
			if (is_string($clause->column)) {
				$query		.=	$this->identifiers($clause->column);
			} else {
				$query		.=	$this->_value($clause->column);
			}
			$clause			 =	$clause->value;
			$query			.=	$this->_clauseEquals($clause);
			return $query	.	$this->_value($clause);
		}

		if ($clause instanceof pudlEquals  &&  $clause->compare !== false) {
			$query			.=	$this->_value($clause->compare);
			$query			.=	$this->_clauseEquals($clause);
			if (!($clause instanceof pudlBetween)) $clause = $clause->value;
			return $query	.	$this->_value($clause);
		}

		if ($clause instanceof pudlHelper) {
			return $query	.	$this->_value($clause);
		}

		$depth++;
		foreach ($clause as $key => $value) {
			if (strlen($query)) $query .= $joiner;

			if ($value instanceof pudlFloat) {
				$value->column = $key;

			} else if (is_string($key)) {
				$query			.= $this->identifiers($key);
				$query			.= $this->_clauseEquals($value);
				if (pudl_array($value)) continue;

			} else if ($value instanceof pudlColumn  &&  $value->args) {
				$key			= '';
				if (is_string($value->column)) {
					$query		.= $this->identifiers($value->column);
				} else {
					$query		.= $this->_value($value->column);
				}
				$value			 = $value->value;
				$query			.= $this->_clauseEquals($value);
				if (pudl_array($value)) continue;

			} else if ($value instanceof pudlAnd) {
				$query .= '(' . $this->_clauseRecurse($value->clause, $value->joiner) . ')';
				continue;

			} else if ($value instanceof pudlEquals  &&  $value->compare !== false) {
				$query			.= $this->_value($value->compare);
				$query			.= $this->_clauseEquals($value);
				if (!($value instanceof pudlBetween)) $value = $value->value;
			}

			$new = $this->_value($value, is_string($key), is_string($key));

			if ($new !== false) {
				$query .= $new;

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ' AND ') {
				$query .= '(' . $this->_clauseRecurse($value, ' OR ') . ')';

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ' OR ') {
				$query .= '(' . $this->_clauseRecurse($value, ' AND ') . ')';

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ', ') {
				$query .= $this->_clauseRecurse($value, $joiner);

			} else {
				return $this->_invalidType($value, 'clause');
			}
		}

		$depth--;
		return $query;
	}



	private function _clauseEquals($value) {
		if ($value instanceof pudlEquals) {
			if (pudl_array($value->value)) {
				if ($value->equals == '=')	return ' IN ';
				if ($value->equals == '!=')	return ' NOT IN ';
			}
			return $value->equals;
		}

		if ($value instanceof pudlStringResult) return $value->type;

		if (pudl_array($value)) return ' IN (' . $this->_inSet($value) . ')';

		if (is_float($value)  &&  (is_nan($value)  ||  is_infinite($value))) return '';

		if (!is_null($value)) return '=';

		return '';
	}



	protected function _clauseId($column, $id=false) {
		if ($id === false) {
			$this->_requireMethod($column, 'pudlId');
			$value = $column->pudlId();
			$this->_requireTrue($value, 'Object retuned invalid value from pudlId');
			return $value;
		}

		if (pudl_array($id)) {
			$list		= explode('.', $column);
			$id			= $id[end($list)];

		} else if (is_object($id)  &&  !($id instanceof pudlHelper)) {
			$list	= explode('.', $column);
			$this->_requireProperty($id, end($list));
			$id		= $id->{end($list)};
		}

		return [$column => $id];
	}



	public function _inSet($value) {
		$query = '';
		foreach ($value as $item) {
			if (strlen($query)) $query .= ', ';
			$query .= $this->_value( pudl_array($item) ? reset($item) : $item );
		}
		return $query;
	}



	protected function _limit($limit, $offset=false) {
		if (pudl_array($limit)) {
			$offset	= count($limit) > 1 ? end($limit) : false;
			$limit	= reset($limit);
		}

		$query = '';

		if ($limit === false  &&  $offset !== false)
			$query .= ' LIMIT 18446744073709551615';

		else if ($limit !== false)
			$query .= ' LIMIT ' . ((int)$limit);

		if ($offset !== false)
			$query .= ' OFFSET ' . ((int)$offset);

		return $query;
	}



	protected function _lock($lock) {
		if ($lock === 'SHARE')	return ' LOCK IN SHARE MODE';
		if ($lock === 'UPDATE')	return ' FOR UPDATE';
		if ($lock === true)		return ' FOR UPDATE';
		return '';
	}



	protected function _lockTable($table, $lock) {
		if (!pudl_array($table)) return $this->_table($table) . ' ' . $lock;

		$query = '';
		foreach ($table as $key => $value) {
			if (pudl_array($value)) continue;
			if (strlen($query)) $query .= ', ';
			$query .= $this->_table($value);
			if (is_string($key)) $query .= ' ' . $this->_table($key);
			$query .= ' ' . $lock;
		}
		return $query;
	}



	protected function _joinUsing($using) {
		if ($using === false)		return '';
		if (!pudl_array($using))	return ' USING (' . $this->identifiers($using) . ')';
		if (!count($using))			return '';

		$query = '';
		foreach ($using as $item) {
			if (strlen($query)) $query .= ', ';
			$query .= $this->identifiers($item);
		}
		return ' USING (' . $query . ')';
	}



	protected function _joinTable($join, $type='LEFT') {
		$query = (empty($type) ? '' : ' '.$type) . ' JOIN ';

		if (is_string($join)) {
			return $query . '(' . $this->_table($join) . ')';

		} else if (pudl_array($join)) {
			$value = reset($join);
			if ($value instanceof pudlStringResult) {
				$query .= (string)$value;
			} else {
				$query .= $this->_table($value);
			}

			$alias = key($join);
			if (is_string($alias)) $query .= ' AS ' . $this->identifier($alias);
			return $query;
		}

		return $this->_invalidType($join, 'join');
	}



	protected function _update($data) {
		if (empty($data)) {
			throw new pudlException('Update data cannot be empty');
		}

		if (!is_array($data)  &&  !is_object($data)) return $data;

		$query = '';

		foreach ($data as $column => $value) {
			if (strlen($query)) $query .= ', ';

			if (is_int($column)) {
				$query .= $value;
				continue;
			}

			$query .= $this->identifier($column) . '=';

			if ($value instanceof pudlFunction  &&  isset($value->__INCREMENT)) {
				$query .= $this->identifier($column);
				$query .= '+' . $this->_value(reset($value->__INCREMENT));

			} else if ($value instanceof pudlAppendSet) {
				$query .= 'CONCAT_WS(\',\', ' .
					$this->identifier($column) . ', ' .
					$this->setEscape($this->_value($value->value)) . ')';

			} else if ($value instanceof pudlStringResult) {
				$query .= '(' . (string)$value . ')';

			} else if ($value instanceof pudlRemoveSet) {
				$query .= 'REPLACE(CONCAT(\',\', ' .
					$this->identifier($column) . ', \',\'), \',' .
					$this->setEscape($this->_value($value->value, false)) . ',\', \',\')';

			} else {
				$query .= $this->_dynamic_create($value);
			}
		}

		return $query;
	}



	public function prefixColumns($table, $col=false, $unprefixed=true) {
		$joiners = array(
			'join', 'cross', 'left', 'right',
			'natural', 'inner', 'outer', 'hack',
		);

		$prefix = array();

		if (!pudl_array($table)) return false;

		foreach ($table as $key => $val) {
			if (pudl_array($val)) {
				foreach ($val as $subtable) {
					if (pudl_array($subtable)) {
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



	public static function extract($array, $keys) {
		$return = [];
		if (!is_array($keys)) {
			$keys = func_get_args();
			array_shift($keys);
		}
		foreach ($keys as $item) $return[$item] = $array[$item];
		return $return;
	}



	public static function merge($source, $keys) {
		$return = [];
		foreach ($source as $key => $value) {
			if (!array_key_exists($key, $keys)) continue;
			$return[$key] = $value;
		}
		return $return;
	}



	protected function _requireMethod($object, $method) {
		if (!is_object($object)) $this->_invalidType($object, 'object');
		if (method_exists($object, $method)) return;
		throw new pudlException(
			'Undefined method: ' . get_class($object) . '::' . $method
		);
	}



	protected function _requireProperty($object, $property) {
		if (!is_object($object)) $this->_invalidType($object, 'object');
		if (property_exists($object, $property)) return;
		throw new pudlException(
			'Undefined property: ' . get_class($object) . '::' . $property
		);
	}



	protected function _requireKey($array, $key) {
		if (!is_array($array)) $this->_invalidType($array, 'array');
		if (array_key_exists($key, $array)) return;
		throw new pudlException('Undefined key: ' . $key);
	}



	protected function _requireTrue($value, $error) {
		if ($value) return;
		throw new pudlException($error);
	}



	protected function _invalidType($item, $thing=false) {
		$error = false;

		switch (true) {
			case ($thing !== false)  &&  is_object($item):
				$error = 'Invalid object type for ' . $thing . ': ' . get_class($item);
			break;

			case ($thing !== false):
				$error = 'Invalid data type for ' . $thing . ': ' . gettype($item);
			break;

			case is_object($item):
				$error = 'Invalid object type: ' . get_class($item);
			break;

			default:
				$error = 'Invalid data type: ' . gettype($item);
			break;
		}

		if ($error) throw new pudlException($error);
	}



	protected $identifier	= '"';
	protected $prefix		= false;

}
