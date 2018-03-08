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
				$query = $this->_invalidType($value);
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
			if (!is_object($column)) {
				switch ($column) {
					case '':
					case '*':
					case null:
					case false:
						return '*';
				}
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
			} else if (is_array($value)) {
				$query .= $this->_column($value);
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
		$dynamic	= preg_split('/[#$]/', $identifiers);
		$list		= explode('.', $dynamic[0]);

		//VERIFY TOTAL NUMBER OF PARTS
		if (count($dynamic) > 2) throw new pudlException(
			'Wrong column format for dynamic or JSON column'
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
		end($list);
		$last = key($list);
		foreach ($list as $key => &$item) {
			if ($key === $last  &&  $item === '*') continue;
			$item = $this->identifier($item);
		} unset($item);

		//WE'RE ONLY NEEDING THE COLUMN WITHOUT TABLE
		if ($prefix === NULL) {
			return end($list);
		}

		//EARLY OUT IF WE ARE NOT IN A DYNAMIC COLUMN
		$return		= implode('.', $list);
		if (count($dynamic) === 1) return $return;


		//JSON COLUMN FORMAT
		if (strpos($identifiers, '$') !== false) {
			if (substr($dynamic[1], 0, 1) !== '.') {
				$dynamic[1] = '$.' . $dynamic[1];
			} else {
				$dynamic[1] = '$' . $dynamic[1];
			}
			return 'JSON_VALUE('
					. $return
					. ','
					. $this->_value($dynamic[1])
					. ')';
		}


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
		$prefix = empty($type) ? '' : (' ' . $type . ' (');
		$suffix = empty($type) ? '' : ')';


		if ($clause === false) return '';


		if ($clause instanceof pudlStringResult) {
			return (string) $clause;
		}


		if ($clause instanceof pudlId) {
			return $prefix . $this->_clauseRecurse($clause->pudlId()) . $suffix;
		}


		if ($clause instanceof pudlEquals  &&  $clause->equals === ' IN ') {
			$query	 = $prefix;
			$query	.= $this->_value($clause->compare);
			$query	.= $this->_clauseEquals($clause);
			$query	.= '(' . $this->_inSet($clause->value) . ')';
			return	$query . $suffix;
		}


		if (is_array($clause)  ||  is_object($clause)) {
			if (empty($clause))	return '';
			return $prefix . $this->_clauseRecurse($clause) . $suffix;
		}


		return $prefix . $this->_compare($clause) . $suffix;
	}




	protected function _order($order, $prefix=false) {
		if (is_string($order)) return ' ORDER BY ' . $order;
		if (!is_array($order)  &&  !is_object($order)) return '';
		if ($order instanceof pudlStringResult) return (string) $order;
		if (empty($order)) return '';
		return ' ORDER BY ' . $this->_clauseRecurse($order, ', ', $prefix);
	}



	protected function _group($group, $prefix=false) {
		if ($group === false)	return '';
		if ($group instanceof pudlStringResult) return (string) $group;
		if (is_array($group))	return ' GROUP BY ' . $this->_clauseRecurse($group, ', ', $prefix);
		if (is_object($group))	return ' GROUP BY ' . $this->_clauseRecurse($group, ', ', $prefix);
		return ' GROUP BY ' . $group;
	}



	private function _clauseRecurse($clause, $joiner=' AND ', $prefix=false) {
		static $depth = 0;
		$query = '';

		if ($depth > 31) {
			throw new pudlException('Recursion limit reached');
			return '';
		}

		if ($clause instanceof pudlAnd) {
			return $this->_clauseRecurse($clause->clause, $clause->joiner, $prefix);
		}

		if ($clause instanceof pudlSort) {
			$query			.=	$this->identifiers($clause->column, $prefix);
			return $query	.	' ' . $clause->value;
		}

		if ($clause instanceof pudlColumn  &&  $clause->args) {
			if (is_string($clause->column)) {
				$query		.=	$this->identifiers($clause->column, $prefix);
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
			if (is_int($key)  &&  $value==='') continue;

			if (strlen($query)) $query .= $joiner;

			if (is_int($key)  &&  is_string($value)) {
				$query .= $this->_compare($value);
				continue;
			}


			if ($value instanceof pudlFloat) {
				$value->column = $key;

			} else if (is_string($key)) {
				$query			.= $this->identifiers($key, $prefix);
				$query			.= $this->_clauseEquals($value);
				if (pudl_array($value)) continue;

			} else if ($value instanceof pudlSort  &&  is_int($key)) {
				$query			.= $this->identifiers($value->column);
				$query			.= ' ' . $value->value;
				continue;

			} else if ($value instanceof pudlColumn  &&  $value->args) {
				$key			 = ''; //FORCE KEY TO STRING TYPE FOR _VALUE
				if (is_string($value->column)) {
					$query		.= $this->identifiers($value->column, $prefix);
				} else {
					$query		.= $this->_value($value->column);
				}
				$value			 = $value->value;
				$query			.= $this->_clauseEquals($value);
				if (pudl_array($value)) continue;

			} else if ($value instanceof pudlAnd) {
				$query .= '(' . $this->_clauseRecurse($value->clause, $value->joiner, $prefix) . ')';
				continue;

			} else if ($value instanceof pudlEquals  &&  $value->compare !== false) {
				$key			 = ''; //FORCE KEY TO STRING TYPE FOR _VALUE
				$query			.= $this->_value($value->compare);
				$query			.= $this->_clauseEquals($value);

				if ($value->equals === ' IN ') {
					$query .= '(' . $this->_inSet($value->value) . ')';
					continue;
				}
			}

			$new = $this->_value($value, is_string($key), is_string($key));

			if ($new !== false) {
				$query .= $new;

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ' AND ') {
				$query .= '(' . $this->_clauseRecurse($value, ' OR ', $prefix) . ')';

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ' OR ') {
				$query .= '(' . $this->_clauseRecurse($value, ' AND ', $prefix) . ')';

			} else if ((is_array($value)  ||  is_object($value))  &&  $joiner === ', ') {
				$query .= $this->_clauseRecurse($value, $joiner, $prefix);

			} else {
				$query = $this->_invalidType($value, 'clause');
				break;
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
			if ($column instanceof pudlId) {
				$value	= $column->pudlId();
				$this->_requireTrue($value, 'Object retuned invalid value from pudlId');
				return $value;
			}
			throw new pudlException(is_object($column)
				? 'Undefined method: ' . get_class($column) . '::pudlId'
				: 'Invalid data type for object: ' . gettype($column)
			);
			return false;
		}

		if ($id instanceof pudlId) {
			$value	= $id->pudlId();
			if (is_array($value)  &&  (count($value)  ===  1)  &&  (key($value)  === $column)) {
				return $value;
			}
		}

		if (pudl_array($id)) {
			$list	= explode('.', $column);
			$id		= $id[end($list)];

		} else if (is_object($id)  &&  !($id instanceof pudlHelper)) {
			$list	= explode('.', $column);
			$this->_requireProperty($id, end($list));
			$id		= $id->{end($list)};
		}

		return [$column => $id];
	}




	protected function _compare($clause) {
		$equals	= [];
		preg_match('/(<=?>|[<|>|!]?=|[><])/', $clause, $equals);

		$parts	= preg_split('/(<=?>|[<|>|!]?=|[><])/', $clause);
		if (!isset($parts[1])) $parts[1] = '';

		$parts[0] = trim($parts[0]);
		$parts[1] = trim($parts[1]);

		if ($parts[0]===''  ||  count($parts)>2  ||  ($parts[1]==='' && !empty($equals[0]))) {
			throw new pudlException('Invalid clause: ' . $clause);
			return false;
		}

		$query	= is_numeric($parts[0])
				? (float) $parts[0]
				: $this->identifiers($parts[0]);

		if (empty($equals[0])) return $query;

		$query .= $equals[0];

		return $query	. (is_numeric($parts[1])
						? (float) $parts[1]
						: $this->identifiers($parts[1]));
	}




	protected function _in($list) {
		if (!pudl_array($list)) {
			$list = explode(',', $list);
			foreach ($list as &$item) { $item=trim($item); } unset($item);
		}

		$query = '';
		foreach ($list as $item) {
			if (strlen($query)) $query .=', ';
			$query .= $this->_value($item);
		}

		return ' IN (' . $query . ')';
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
			if (is_string($key)) $query .= ' AS ' . $this->_table($key);
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

		if ($data instanceof pudlHelper) {
			return $this->_clauseRecurse($data);
		}

		$query = '';

		foreach ($data as $column => $value) {
			if (strlen($query)) $query .= ', ';

			if (is_int($column)) {
				$query	.= ($value instanceof pudlValue)
							? trim($this->_clause($value, ''))
							: $value;
				continue;
			}

			$query .= $this->identifier($column) . '=';

			if ($value instanceof pudlFunction  &&  isset($value->__INCREMENT)) {
				$query	.= $this->identifier($column);
				$query	.= '+' . $this->_value(reset($value->__INCREMENT));

			} else if ($value instanceof pudlAppendSet) {
				$query .= 'CONCAT_WS(\',\', ' .
					$this->identifier($column) . ', ' .
					$this->setEscape($this->_value($value->value)) . ')';

			} else if ($value instanceof pudlStringResult) {
				$query	.= '(' . (string)$value . ')';

			} else if ($value instanceof pudlRemoveSet) {
				$query	.= 'TRIM(BOTH \',\' FROM REPLACE(CONCAT(\',\', '
						.  $this->identifier($column)
						. ', \',\'), \','
						.  $this->setEscape($this->_value($value->value, false))
						.  ',\', \',\'))';

			} else if (pudl_array($value)  &&  count($value)) {
				$query	.= 'JSON_SET(';
				$query	.= $this->_value($this->_json_column($column));
				foreach ($value as $json_path => $json_value) {
					$query .= ",'" . $this->_json_path($json_path) . "',";
					if (is_string($json_value)) {
						$query .= $this->_value($json_value);
					} else {
						$query .= 'JSON_COMPACT(';
						$query .= $this->_value(
							static::jsonEncode($json_value)
						);
						$query .= ')';
					}
				}
				$query	.= ')';

			} else if (is_array($value)) {
				$query	.= 'NULL';

			} else {
				$query	.= $this->_value($value);
			}
		}

		return $query;
	}



	public function prefixColumns($tables, $columns=false, $unprefixed=true) {
		if ($columns === false) return [];

		$list	= $this->listFields($tables);
		$return	= [];

		foreach ($columns as $val) {
			if (!empty($list[$val]['Prefix'])) {
				$return[] = $list[$val]['Prefix'] . '.' . $val;

			} else if ($unprefixed) {
				$return[] = $val;
			}
		}

		return $return;
	}



	public function extractColumns($table, $data, $virtual=true) {
		$fields = $this->listFields($table);

		if (!$virtual) {
			foreach ($fields as $key => $field) {
				if (!empty($field['Extra'])) {
					if (!strcasecmp($field['Extra'], 'VIRTUAL GENERATED')) {
						unset($fields[$key]);
					}
				}
			}
		}

		return static::extract($data, array_keys($fields));
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

		throw new pudlException($error);

		return NULL;
	}



	protected $identifier	= '"';
	protected $prefix		= false;

}
