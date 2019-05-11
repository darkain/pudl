<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlTable {


	////////////////////////////////////////////////////////////////////////////
	// GET A LIST OF TABLES FROM THE DATABASE
	// https://mariadb.com/kb/en/library/show-tables/
	////////////////////////////////////////////////////////////////////////////
	public function tables($where=NULL) {
		$tables				= [];
		$query				= 'SHOW TABLES' . $this->_where($where);
		$list				= $this($query)->complete();

		if (is_string($list)  ||  $list instanceof pudlStringResult) {
			return (string) $list;
		}

		foreach ($list as $item) {
			$tables[]		= reset($item);
		}

		return $tables;
	}




	////////////////////////////////////////////////////////////////////////////
	// USED TO TRANSLATE TABLE NAME PREFIXES
	////////////////////////////////////////////////////////////////////////////
	protected function _prefix($table) {
		foreach ($this->prefix as $key => $prefix) {
			if (is_int($key)) continue;

			if (substr($table, 0, strlen($key)) === $key) {
				return $prefix . substr($table, strlen($key));
			}
		}

		if (!isset($this->prefix[0])) return $table;

		if (substr($table, 0, strlen($this->prefix[0])) === $this->prefix[0]) {
			return $table;
		}

		return $this->prefix[0] . $table;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK TO SEE IF THE GIVEN TABLE NAME EXISTS IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function tableExists($table) {
		$tables = $this->tables();
		return in_array($this->_prefix($table), $tables);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET STATISTICS ABOUT THE GIVEN TABLES
	////////////////////////////////////////////////////////////////////////////
	public function tableStatus($tables) {
		if (!pudl_array($tables)) {
			$tables = [$tables];
		}

		foreach ($tables as &$table) {
			$table = $this->_prefix($table);
		}

		return $this(
			'SHOW TABLE STATUS' .
			$this->_where(['name' => $tables])
		)->complete();
	}




	////////////////////////////////////////////////////////////////////////////
	// RENAME A TABLE IN THE DATABASE
	// https://mariadb.com/kb/en/library/rename-table/
	////////////////////////////////////////////////////////////////////////////
	public function rename($rename, $to=false, $wait=NULL) {
		$query = 'RENAME TABLE ';

		if (!pudl_array($rename)) {
			if ($to === false) return $this($query . $rename);
			return $this($query . $this->_table($rename) . ' TO ' . $this->_table($to));
		}

		$first = true;
		foreach ($rename as $old => $new) {
			if ($first) $first=false; else $query .= ', ';
			$query .= $this->_table($old) . ' TO ' . $this->_table($new);
		}

		$query .= $this->_wait($wait);

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SWAP THE NAMES OF TWO DIFFERENT TABLES IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function swapTables($table1, $table2, $wait=NULL) {
		$temp = 'TABLE_SWAP_' . (
			function_exists('random_bytes')
				? bin2hex(random_bytes(20))
				: sha1(microtime() . rand() . uniqid('',TRUE))
		);

		return $this->rename([
			$table1	=> $temp,
			$table2	=> $table1,
			$temp	=> $table2,
		], false, $wait);
	}




	////////////////////////////////////////////////////////////////////////////
	// DROP (DELETE) THE GIVEN TABLE(S)
	// OPTIONAL $temp - WHEN TRUE, ONLY WORKS ON TEMPORARY TABLES
	// https://mariadb.com/kb/en/library/drop-table/
	////////////////////////////////////////////////////////////////////////////
	public function drop($tables, $temp=true, $wait=NULL) {
		$query = 'DROP ' . ($temp?'TEMPORARY ':'') . 'TABLE IF EXISTS ';

		if (pudl_array($tables)) {
			$first = true;
			foreach ($tables as $table) {
				if ($first) $first=false; else $query .= ', ';
				$query .= $this->_table($table);
			}
		} else {
			$query .= $this->_table($tables);
		}

		$query .= $this->_wait($wait);

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// TRUNCATE THE GIVEN TABLE (DELETE ALL ROWS AND RESET AUTO INCREMENT ID)
	// https://mariadb.com/kb/en/library/truncate-table/
	////////////////////////////////////////////////////////////////////////////
	public function truncate($table, $wait=NULL) {
		return $this(
			'TRUNCATE TABLE '
			. $this->_table($table)
			. $this->_wait($wait)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT COLUMN DEFINITION FROM PUDL STANDARD TO DATABASE SPECIFIC
	// THIS IS OVERWRITTEN IN SOME PUDL DATABASE DRIVERS
	////////////////////////////////////////////////////////////////////////////
	protected function dataType($type) {
		if (!($type instanceof pudlType)) {
			return strtoupper(
				preg_replace("/[^A-Za-z0-9_(), ']/", '', $type)
			);

		}

		$query = $type->type . '(';
		$first = true;

		foreach ($type->value as $item) {
			if ($first) $first = false; else $query .= ',';
			$query .= $this->_value((string)$item);
		}

		return $query . ')';
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT COLUMN DEFINITION FROM PUDL STANDARD TO DATABASE SPECIFIC
	// THIS IS OVERWRITTEN IN SOME PUDL DATABASE DRIVERS
	////////////////////////////////////////////////////////////////////////////
	protected function columnType($type) {
		if (!pudl_array($type)) {
			return strtoupper(
				preg_replace("/[^A-Za-z0-9_(), ']/", '', $type)
			);
		}

		$query = '';

		if (!empty($type['type'])) {
			$query .= $this->dataType($type['type']);
		} else if (!empty($type[0])) {
			$query .= $this->dataType($type[0]);
		}

		if (!empty($type['key'])) {
			switch (strtolower($type['key'])) {
				case 'auto':
					$query .= ' PRIMARY KEY AUTOINCREMENT';
				break;

				case 'primary':
					$query .= ' PRIMARY KEY';
				break;

				case 'unique':
					$query .= ' UNIQUE';
				break;
			}
		}

		if (!empty($type['charset'])) {
			$query	.= ' CHARACTER SET '
					.  strtolower($this->_value($type['charset'], false));
		}

		if (!empty($type['collate'])) {
			$query	.= ' COLLATE '
					.  strtolower($this->_value($type['collate'], false));
		}

		if (!empty($type['null'])) {
			$query .= ' NULL';
		} else if (isset($type['null'])) {
			$query .= ' NOT NULL';
		}

		if (array_key_exists('default', $type)) {
			$query .= ' DEFAULT ' . $this->_value($type['default']);
		}

		if (!empty($type['comment'])) {
			$query .= ' COMMENT ' . $this->_value($type['comment']);
		}

		return $query;
	}




	////////////////////////////////////////////////////////////////////////////
	// !!! EXPERIMENTAL !!!
	// CREATE A TABLE IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function create($table, $columns, $keys=false, $options=false) {
		$query  = 'CREATE TABLE IF NOT EXISTS ';
		$query .= $this->_table($table) . ' (';

		if (is_string($columns)) {
			$query .= rtrim($columns, " \t\n\r\0\x0B,");

		} else if (pudl_array($columns)) {
			$first = true;
			foreach ($columns as $key => $item) {
				if ($first) $first = false; else $query .= ', ';

				if (is_string($key)) {
					$query .= $this->identifier($key) . ' ';
					$query .= $this->columnType($item);
				} else {
					$query .= $item;
				}
			}

		} else {
			throw new pudlTypeException($this, 'Invalid data type for $columns');
		}


		if ($keys === false) {
			//DO NOTHING
		} else if (is_string($keys)) {
			$query .= ', ' . rtrim($keys, " \t\n\r\0\x0B,");

		} else if (pudl_array($keys)) {
			foreach ($keys as $item) {
				$query .= ', ' . rtrim($item, " \t\n\r\0\x0B,");
			}

		} else {
			throw new pudlTypeException($this, 'Invalid data type for $keys');
		}


		$query .= ')';


		if ($options === false) {
			//DO NOTHING
		} else if (is_string($options)) {
			$query .= ' ' . $options;

		} else if (pudl_array($options)) {
			foreach ($options as $key => $item) {
				if (is_string($key)) {
					$query .= ' ' . $key . '=' . $item;
				} else {
					$query .= ' ' . $item;
				}
			}

		} else {
			throw new pudlTypeException($this, 'Invalid data type for $options');
		}


		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// ADD SYSTEM VERSIONING TO THE GIVEN TABLE
	// https://mariadb.com/kb/en/library/system-versioned-tables/
	////////////////////////////////////////////////////////////////////////////
	public function addVersioning($table, $wait=NULL) {
		return $this(
			'ALTER TABLE '
			. $this->_table($table)
			. ' ADD SYSTEM VERSIONING'
			. $this->_wait($wait)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET A SQL ESCAPED TABLE NAME
	////////////////////////////////////////////////////////////////////////////
	protected function _table($table) {
		return $this->identifiers($table, true);
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS A LIST OF TABLES
	////////////////////////////////////////////////////////////////////////////
	protected function _tables($table) {
		if ($table === false)			return;
		if ($table === NULL)			return;
		if ($table instanceof pudlRaw)	return ' FROM ' . $table->pudlValue($this);
		if (is_string($table))			return ' FROM ' . $this->_table($table);
		if (!pudl_array($table))		return $this->_invalidType($table, 'table');

		$query = '';
		foreach ($table as $key => $value) {
			if (!pudl_array($value)) {

				// SUBQUERY
				if ($value instanceof pudlStringResult) {
					if (strlen($query)) $query .= ', ';
					$query	.= (string) $value;
					$query	.= $this->_as($key);

				// MODERN JOIN SYNTAX
				} else {
					$join	 = $this->_join($value, $key);
					if ($join !== false) {
						$query	.= $join;
					} else {
						if (strlen($query)) $query .= ', ';
						$query	.= $this->_table($value);
						$query	.= $this->_as($key);
					}
				}

			// LEGACY JOIN SYNTAX
			} else {
				if (strlen($query)) $query .= ', ';
				$query .= $this->_table(reset($value));
				$query .= $this->_as($key);

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
						$query .= $this->_on($join['clause']);
					} else if (!empty($join['on'])) {
						$query .= $this->_on($join['on']);
					} else if (!empty($join['using'])) {
						$query .= $this->_joinUsing($join['using']);
					}
				}
			}
		}

		return ' FROM ' . $query;
	}




	////////////////////////////////////////////////////////////////////////////
	// MODERN JOIN SYNTAX PARSER
	////////////////////////////////////////////////////////////////////////////
	protected function _join($table, $alias=false) {
		$table = Ltrim($table);
		if (strlen($table) < 2) return false;

		$query	= false;
		$pos	= strpos($table, '(');

		if ($pos === false) $pos = strlen($table);

		switch ($table[0]) {
			case '<':
				$query = ($table[1] === '>')
					? (' OUTER JOIN ' . $this->_table(substr($table, 2, $pos-2)))
					: (' LEFT JOIN '  . $this->_table(substr($table, 1, $pos-1)));
			break;


			case '>':
				$query = ($table[1] === '<')
					? (' INNER JOIN ' . $this->_table(substr($table, 2, $pos-2)))
					: (' RIGHT JOIN ' . $this->_table(substr($table, 1, $pos-1)));
			break;


			case '~':
				$query	= ' JOIN '
						. $this->_table(substr($table, 1, $pos-1));
			break;


			case '+':
				$query	= ' CROSS JOIN '
						. $this->_table(substr($table, 1, $pos-1));
			break;


			case '=':
				if ($table[1] === '<') {
					$query	= ' NATURAL LEFT JOIN '
							. $this->_table(substr($table, 2, $pos-2));

				} else if ($table[1] === '>') {
					$query	= ' NATURAL RIGHT JOIN '
							. $this->_table(substr($table, 2, $pos-2));

				} else {
					$query	= ' NATURAL JOIN '
							. $this->_table(substr($table, 1, $pos-1));
				}
			break;
		}


		if ($query !== false) {
			$query .= $this->_as($alias);

			if ($pos < strlen($table)) {
				$end = strpos($table, ')', $pos+1);
				if ($end === false) {
					return $this->_invalidType($table, 'table');
				}
				$query .= $this->_joinUsing(substr($table, $pos+1, $end-$pos-1));
			}
		}


		return $query;
	}




	////////////////////////////////////////////////////////////////////////////
	// JOIN USING SYNTAX PARSER
	// TODO: SUPPORT MORE COMPLEX "ON" QUERIES AUTOMATICALLY WITH THIS
	////////////////////////////////////////////////////////////////////////////
	protected function _joinUsing($using) {
		if ($using === false  ||  $using === NULL) return '';

		if (!pudl_array($using)) {
			return preg_match('/(<=?>|[<|>|!]?=|[><])/', $using)
				? (' ON (' . $this->_compare($using) . ')')
				: (' USING (' . $this->identifiers($using) . ')');
		}

		if (!count($using)) return '';

		$query = '';
		foreach ($using as $item) {
			if (strlen($query)) $query .= ', ';
			$query .= $this->identifiers($item);
		}
		return ' USING (' . $query . ')';
	}




	////////////////////////////////////////////////////////////////////////////
	// LEGACY JOIN TABLE PARSER
	////////////////////////////////////////////////////////////////////////////
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

			return $query . $this->_as(key($join));
		}

		return $this->_invalidType($join, 'join');
	}

}
