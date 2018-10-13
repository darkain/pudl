<?php


'@phan-file-suppress PhanUndeclaredMethod';
'@phan-file-suppress PhanUndeclaredStaticMethod';
'@phan-file-suppress PhanUndeclaredProperty';



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



	protected function blob($value) {
		return '0x' . bin2hex($value);
	}



	public function likeEscape($value, $raw=false) {
		if ($raw) return $value;
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

		if ($depth++ > PUDL_RECURSION) {
			throw new pudlException($this,
				'Recursion limit reached for value expression'
			);
		}

		switch (true) {

			case is_array($value):
				//THIS IS FOR AND/OR RECUSION, HANDLED ELSEWHERE
				//IN THIS CASE, DO NOTHING!
			break;


			case is_int($value):
				$query = $value;
			break;


			case is_float($value):
				if (is_nan($value)  ||  is_infinite($value)) {
					$query = $isnull ? ' IS NULL' : 'NULL';
				} else {
					$query = $value;
				}
			break;


			case is_string($value):
				if (!$quote) {
					$query = $value;
				} else if (preg_match('/[\x80-\xFF]/', $value)) {
					$query = $this->blob($value);
				} else {
					$query = "'" . $this->escape($value) . "'";
				}
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


			case is_object($value) && is_callable([$value, '__toString']):
				$query = $this->_value((string)$value, $quote, $isnull);
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

		if (is_object($column)) {
			if ($column instanceof pudlValue) {
				return $column->pudlValue($this);
			} elseif (method_exists($column, '__toString')) {
				$column = (string) $column;
			}
		}

		if (is_null($column)) return '*';
		if ($column === false) return '*'; //DEPRICATED

		if (is_string($column)) {
			$column = trim($column);
			if ($column === ''  ||  $column === '*') return '*';

			if (strpos($column, ',') !== false) {
				$column = array_map('trim', explode(',', $column));
			} else {
				return $this->identifiers($column);
			}
		}

		if (!pudl_array($column)) {
			return $this->_value($column);
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
			} else if (pudl_array($value)) {
				$query .= $this->_column($value);
			} else if (is_string($value)) {
				$query .= $this->identifiers($value);
			} else {
				$query .= $this->_value($value, true);
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
		if (count($dynamic) > 2) {
			throw new pudlException(
				$this,
				'Wrong column format for JSON column'
			);
		}

		//CLEAN UP TABLE AND COLUMN NAMES
		foreach ($list as &$item) {
			$item = trim($item);
			if (!strlen($item)) {
				throw new pudlValueException($this, 'Wrong column name');
			}
		} unset($item);

		//PROCESS TABLE NAME
		if ($prefix !== false  &&  $prefix !== NULL) {
			$list[] = $this->_prefix(array_pop($list));
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
		//TODO: THIS IS BAD
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

		return $return;
	}



	protected function _as($name) {
		if (!is_string($name)) return '';
		return ' AS ' . $this->identifier($name);
	}



	protected function _clause($clause, $type='WHERE') {
		$prefix = empty($type) ? '' : (' ' . $type . ' (');
		$suffix = empty($type) ? '' : ')';


		if ($clause === false)	return '';
		if ($clause === NULL)	return '';


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




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE 'ORDER BY' SECTION OF THE SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _order($order, $prefix=false) {
		//TODO: (string) should escape identifier
		if (empty($order)) return '';
		if (is_string($order)) return ' ORDER BY ' . $order;

		if (!pudl_array($order)  &&  !($order instanceof pudlHelper)) {
			throw new pudlTypeException($this,
				'Invalid data type for $order: ' . gettype($order)
			);
		}

		if ($order instanceof pudlStringResult) return (string) $order;
		return ' ORDER BY ' . $this->_clauseRecurse($order, ', ', $prefix);
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE 'GROUP BY' SECTION OF THE SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _group($group, $prefix=false) {
		//TODO: (string) should escape identifier
		if (empty($group)) return '';
		if (is_string($group)) return ' GROUP BY ' . $group;

		if (!pudl_array($group)  &&  !($group instanceof pudlHelper)) {
			throw new pudlTypeException($this,
				'Invalid data type for $group: ' . gettype($group)
			);
		}

		if ($group instanceof pudlStringResult) return (string) $group;
		return ' GROUP BY ' . $this->_clauseRecurse($group, ', ', $prefix);
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE A RECUSIVE CLAUSE
	// THIS IS USED BY 'WHERE', 'ON', 'HAVING', 'GROUP BY', 'ORDER BY'
	////////////////////////////////////////////////////////////////////////////
	private function _clauseRecurse($clause, $joiner=' AND ', $prefix=false) {
		static $depth = 0;
		$query = '';

		if ($depth > PUDL_RECURSION) {
			throw new pudlException($this,
				'Recursion limit reached in recursive clause'
			);
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
			throw new pudlTypeException(
				$this,
				is_object($column)
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
			throw new pudlValueException($this, 'Invalid clause: ' . $clause);
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



	protected function _wait($wait=NULL) {
		if ($wait === NULL)		return '';
		if ($wait === false)	return ' NOWAIT';
		return ' WAIT ' . ((int)$wait);
	}



	protected function _update($data) {
		if (empty($data)) {
			throw new pudlValueException($this, 'Update data cannot be empty');
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
				$value	= (array) $value;

				if (array_keys($value) === range(0, count($value) - 1)) {
					$query	.= $this->_value(
						/** @suppress PhanUndeclaredStaticMethod */
						static::jsonEncode($value)
					);

				} else {
					$query	.= 'JSON_SET(';
					$query	.= $this->_value($this->_json_column($column));
					foreach ($value as $json_path => $json_value) {
						$query .= ",'$." . $this->jsonPathSafe($json_path) . "',";
						if (is_string($json_value)) {
							$query .= $this->_value($json_value);
						} else {
							$query .= 'JSON_COMPACT(';
							$query .= $this->_value(
								/** @suppress PhanUndeclaredStaticMethod */
								static::jsonEncode($json_value)
							);
							$query .= ')';
						}
					}
					$query	.= ')';
				}

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



	public function extractColumns($table, $data, $options=[]) {
		if (!pudl_array($options))			$options				= [];
		if (empty($options['primary']))		$options['primary']		= false;
		if (empty($options['generated']))	$options['generated']	= false;

		$fields = $this->listFields($table);

		foreach ($fields as $key => $field) {
			if (!$options['primary']  &&  !empty($field['Key'])) {
				if (stripos($field['Key'], 'PRI') !== false) {
					unset($fields[$key]);
				}
			}
			if (!$options['generated']  &&  !empty($field['Extra'])) {
				if (stripos($field['Extra'], 'GENERATED') !== false) {
					unset($fields[$key]);
				}
			}
		}

		return static::extract($data, array_keys($fields));
	}



	protected function _requireProperty($object, $property) {
		if (!is_object($object)) $this->_invalidType($object, 'object');
		if (property_exists($object, $property)) return;
		throw new pudlException(
			$this,
			'Undefined property: ' . get_class($object) . '::' . $property
		);
	}



	protected function _requireKey($array, $key) {
		if (!is_array($array)) $this->_invalidType($array, 'array');
		if (array_key_exists($key, $array)) return;
		throw new pudlException($this, 'Undefined key: ' . $key);
	}



	protected function _requireTrue($value, $error) {
		if ($value) return;
		throw new pudlValueException($this, $error);
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

		throw new pudlTypeException($this, $error);

		return NULL;
	}



	/** @var string */			protected $identifier	= '"';
	/** @var string|false */	protected $prefix		= false;

}
