<?php

require_once('pudlHelpers.php');
require_once('pudlStringResult.php');
require_once('pudlCacheResult.php');
require_once('pudlQuery.php');



abstract class pudl extends pudlQuery {


	public function __construct() {
		$this->instance		= ++self::$instances;
		$this->redis		= new pudlVoid;
		$this->time			= time();
		$this->microtime	= microtime(true);
		$this->transaction	= false;
	}


	function __destruct() {}



	abstract protected function process($query);

	abstract public function insertId();
	abstract public function updated();

	abstract public function errno();
	abstract public function error();


	public function disconnect() {
		session_write_close();
	}


	public function __invoke($query) {
		//SELEX
		if (is_array($query)) {
			return $this->selex($query);
		}


		//UNIONS
		if (is_array($this->union)) {
			$this->union[] = $query;
			return true;
		}


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) $microtime = microtime(true);


		//STORE THE QUERY STRING LOCALLY
		$this->query = $query;


		//STORE TRANSACTION INFORMATION
		if (is_array($this->transaction)) $this->transaction[] = $query;


		//RETURN A STRING
		$string = end($this->string);
		if ($string === true) {
			$result = new pudlStringResult($this, $string);
			array_pop($this->string);


		//RETURN A SUBQUERY STRING
		} else if ($string !== false) {
			$this->query = '(' . $this->query . ')';
			$result = new pudlStringResult($this, $string);
			array_pop($this->string);
			return $result;


		//CACHE THE QUERY IN REDIS
		} else if ($this->cache  &&  is_object($this->redis)  &&  !($this->redis instanceof pudlVoid)) {
			$this->stats['total']++;
			try {
				$hash = $this->cachekey;
				if (empty($hash)) $hash = md5($query);
				$data = $this->redis->get("pudl:$hash");
				if ($data === false) {
					$this->stats['queries']++;
					$this->stats['misses']++;
					$this->stats['missed'][] = $query;
					$result = $this->process($query);
					if (!$result->error()) {
						$data = $result->rows();
						$this->redis->set("pudl:$hash", $data, $this->cache);
						$result = new pudlCacheResult($data, $this, $hash);
					}
				} else {
					$this->stats['hits']++;
					$result = new pudlCacheResult($data, $this, $hash);
				}
			} catch (RedisException $e) {
				if (empty($result)) {
					$this->stats['queries']++;
					$this->stats['misses']++;
					$result = $this->process($query);
				}
			}


		//PROCESS THE QUERY NORMALLY
		} else {
			$this->stats['total']++;
			$this->stats['queries']++;
			$result = $this->process($query);
		}


		//RESET CACHE INFORMATION FOR NEXT QUERY
		$this->cache = $this->cachekey = false;


		//PERFORMANCE PROFILING DATA
		if (!empty($this->bench)) {
			$bench = $this->bench;
			$diff = round(microtime(true)-$microtime, 6);
			$bench($query, $diff, $this);
		}


		//ERROR REPORTING'
		if ($result->error()  &&  $this->debug !== false) {
			$debug = $this->debug;
			$debug($this, $result);
		}

		return $result;
	}



	public static function __callStatic($name, $arguments) {
		$value = new pudlFunction();
		$name = '_' . strtoupper($name);
		$value->$name = $arguments;
		return $value;
	}



	public function query($query=false) {
		if ($query === false) return $this->query;
		return $this($query);
	}



	public function stats() {
		return $this->stats;
	}



	public function listFields($table) {
		$return = array();
		if (is_array($table)) {
			foreach ($table as $t) {
				$t = $this->_table($t);
				$result = $this("SHOW COLUMNS FROM $t");
				while ($data = $result->row()) $return[$data['Field']] = $data;
				$result->free();
			}
		} else {
			$table = $this->_table($table);
			$result = $this("SHOW COLUMNS FROM $table");
			while ($data = $result->row()) $return[$data['Field']] = $data;
			$result->free();
		}
		return $return;
	}



	public function select($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectHaving($col, $table, $clause=false, $having=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_clause($having, 'HAVING');
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectGroupHaving($col, $table, $clause=false, $group=false, $having=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= $this->_clause($having, 'HAVING');
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectOrderGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		if (is_array($limit)) $query .= $this->_limit($limit[0]);
		$query .= ') groupbyorderby ';
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		if (is_array($limit))  $query .= $this->_limit($limit[1], $offset);
		else $query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectOrderGroupEx($col, $table, $clause=false, $inner_group=false, $outer_group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($inner_group);
		$query .= $this->_order($order);
		if (is_array($limit)) $query .= $this->_limit($limit[0]);
		$query .= ') groupbyorderby ';
		$query .= $this->_group($outer_group);
		$query .= $this->_order($order);
		if (is_array($limit))  $query .= $this->_limit($limit[1], $offset);
		else $query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_joinTable($join_table);
		$query .= $this->_clause($join_clause, 'ON');
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectDistinct($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectDistinctGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT * FROM (SELECT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= ') groupbyorderby ';
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectDistinctJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_joinTable($join_table);
		$query .= $this->_clause($join_clause, 'ON');
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectExplain($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->explain($query);
	}




	public function selex($params) {
		$query = 'SELECT ';
		$query .= $this->_cache();

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= ' *, COUNT(*) FROM (SELECT ';
		}

		if (isset($params['limit']))	$query .= $this->_top(   $params['limit']);
		if (isset($params['column']))	$query .= $this->_column($params['column']);
		if (!isset($params['column']))	$query .= '*';
		if (isset($params['table']))	$query .= $this->_tables($params['table']);
		if (isset($params['clause']))	$query .= $this->_clause($params['clause']);

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= $this->_order($params['order']);
			$query .= ') groupbyorderby ';
		}

		if (isset($params['group']))	$query .= $this->_group($params['group']);
		if (isset($params['having']))	$query .= $this->_clause($params['having'], 'HAVING');
		if (isset($params['order']))	$query .= $this->_order($params['order']);

		$limit	= isset($params['limit'])	? $params['limit']	: false;
		$offset	= isset($params['offset'])	? $params['offset']	: false;
		$query .= $this->_limit($limit, $offset);

		if (isset($params['lock'])) $query .= $this->_lock($params['lock']);

		return $this($query);
	}



	public function selectRow($col, $table, $clause=false, $order=false, $limit=1, $offset=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset, $lock);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->row();
		$result->free();
		return $return;
	}



	public function row($table, $clause=false, $order=false, $lock=false) {
		return $this->selectRow('*', $table, $clause, $order, 1, false, $lock);
	}



	public function rowEx($col, $table, $clause=false, $order=false, $lock=false) {
		return $this->selectRow($col, $table, $clause, $order, 1, false, $lock);
	}



	public function selectRows($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset, $lock);
		if (is_array($this->union)) return true;
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->rows();
		$result->free();
		return $return;
	}



	public function rows($table, $clause=false, $order=false, $lock=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false, $lock);
	}



	public function rowId($table, $column, $id, $lock=false) {
		return $this->row($table, $this->_clauseId($column,$id), false, $lock);
	}



	public function rowsId($table, $column, $id, $lock=false) {
		return $this->selectRows('*', $table, $this->_clauseId($column,$id), false, false, false, $lock);
	}



	public function delete($table, $clause, $limit=false, $offset=false) {
		$query  = 'DELETE ';
		$query .= $this->_top($limit);
		$query .= 'FROM ';
		$query .= $this->_table($table);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}


	public function deleteId($table, $column, $id, $limit=false, $offset=false) {
		return $this->delete($table, $this->_clauseId($column,$id), $limit, $offset);
	}



	public function explain($query) {
		$return = '';
		$result = $this("EXPLAIN $query");
		if ($result instanceof pudlStringResult) return $result;
		while ($data = $result->row()) $return .= print_r($data, true);
		$result->free();
		return $return;
	}



	public function cell($table, $col, $clause=false, $order=false) {
		$result = $this->select($col, $table, $clause, $order, 1);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return;
	}



	public function cellId($table, $col, $column, $id) {
		return $this->cell($table, $col, $this->_clauseId($column,$id));
	}



	public function idExists($table, $col, $id) {
		return ($this->cellId($table, $col, $col, $id) !== false);
	}



	public function clauseExists($table, $clause) {
		return ($this->cell($table, true, $clause) !== false);
	}



	public function count($table, $clause='1') {
		$return = $this->cell($table, 'COUNT(*)', $clause);
		if ($return instanceof pudlStringResult) return $return;
		return $return === false ? $return : (int) $return;
	}



	public function countId($table, $column, $id) {
		return $this->count($table, $this->_clauseId($column,$id));
	}



	public function countGroup($table, $clause, $group, $col=false) {
		if ($col === false) $col = $group;

		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'COUNT(*) FROM (';
		$query .= 'SELECT ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= ') groupbycount';

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();

		return $return === false ? $return : (int) $return;
	}



	public function found() {
		$result = $this('SELECT FOUND_ROWS()');
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return === false ? $return : (int) $return;
	}




	public function unionStart() {
		if ($this->union !== false) return false;
		$this->union = array();
		return true;
	}



	public function unionEnd($order=false, $limit=false, $offset=false, $type='') {
		if (!is_array($this->union)) return false;

		$query  = $this->_union($type);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this($query);
	}



	public function unionGroup($group=false, $order=false, $limit=false, $offset=false, $type='') {
		if (!is_array($this->union)) return false;

		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '* FROM (';
		$query .= $this->_union($type);
		$query .= ') pudltablealias';
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this($query);
	}



	public function insertValues($table, $data, $update=false) {
		return $this->insert($table, $data, $update, false);
	}



	public function insert($table, $data, $update=false, $prefix=true) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insert', E_USER_ERROR);
			return false;
		}

		$cols	= ' (';
		$vals	= '';
		$first	= true;
		foreach ($data as $column => $value) {
			if (!$first) {
				$cols .= ', ';
				$vals .= ', ';
			} else $first = false;

			$cols .= $this->_table($column, false);
			$vals .= $this->_columnData($value);
		}

		if ($prefix) $cols .= ')'; else $cols = '';

		$table = $this->_table($table);
		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table$cols VALUES ($vals)";
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table$cols VALUES ($vals)";
		} else {
			$query = "INSERT INTO $table$cols VALUES ($vals)";
			if ($update === true) $update = $data;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update);
			}
		}

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		return $this->insertId();
	}



	public function replace($table, $data) {
		return $this->insert($table, $data, "REPLACE");
	}



	public function insertIgnore($table, $data) {
		return $this->insert($table, $data, "IGNORE");
	}



	public function insertUpdate($table, $data, $column, $update=false, $prefix=true) {
		if (empty($update)) {
			$update = [];
		} else if ($update === true  &&  is_array($data)) {
			$update = $data;
		} else if ($update === true) {
			$update = [$data];
		}

		$update[] = $this->_table($column,false).'=LAST_INSERT_ID('.$this->_table($column,false).')';

		return $this->insert($table, $data, $update, $prefix);
	}



	public function insertEx($table, $cols, $data, $update=false) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insertEx', E_USER_ERROR);
			return false;
		}

		$table = $this->_table($table);

		$query = '';

		foreach ($cols as &$name) {
			if (strlen($query)) $query .= ',';
			$query .= $this->_table($name, false);
		} unst($name);

		$query .= ') VALUES ';

		$first = true;
		foreach ($data as &$set) {
			if (!$first) $query .= ',';
			$first = false;
			$query .= '(';

			$firstitem = true;
			foreach ($set as &$item) {
				if (!firstitem) $query .= ',';
				$firstitem = false;
				$query .= "'$item'";
			} unset($item);

			$query .= ')';
		} unset($set);

		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table (" . $query;
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table (" . $query;
		} else {
			$query = "INSERT INTO $table (" . $query;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update);
			}
		}

		$this($query);
		return $this->insertId();
	}



	public function replaceEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, "REPLACE");
	}



	public function insertIgnoreEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, "IGNORE");
	}



	public function update($table, $data, $clause, $limit=false, $offset=false) {
		$query  = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function updateIgnore($table, $data, $clause, $limit=false, $offset=false) {
		$query  = 'UPDATE IGNORE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function updateIn($table, $data, $field, $in, $limit=false, $offset=false) {
		if (is_array($in)) $in = implode(',', $in);
		$query  = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data);
		$query .= ' WHERE (';
		$query .= $this->_table($field, false);
		$query .= $this->_clause($in, 'IN');
		$query .= ')';
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function updateId($table, $data, $column, $id) {
		return $this->update($table, $data, $this->_clauseId($column,$id));
	}



	public function updateCount($table_update, $field, $clause_update, $table_select, $clause_select=true) {
		if ($clause_select === true) $clause_select = $clause_update;
		return $this->update($table_update, [
			$field => $this->string()->count($table_select, $clause_select)
		], $clause_update);
	}



	public function increment($table, $col, $clause, $amount=1, $limit=false, $offset=false) {
		$query = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET '	. $this->_table($col, false);
		$query .= '='		. $this->_table($col, false);
		$query .= '+'		. $this->_value($amount);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function incrementId($table, $col, $column, $id, $amount=1) {
		return $this->increment($table, $col, $this->_clauseId($column,$id), $amount);
	}



	public function listItems($type, $like=false) {
		$query = 'SHOW ' . $type;
		if (!empty($like)) $query .= ' LIKE "' . $like . '"';
		$result = $this($query);

		$return = [];
		while ($data = $result->row()) {
			$return[reset($data)] = end($data);
		}

		$result->free();
		return $return;
	}



	public function globals($like=false) {
		return $this->listItems('GLOBAL STATUS', $like);
	}


	public function variables($like=false) {
		return $this->listItems('VARIABLES', $like);
	}


	public function status($like=false) {
		return $this->listItems('STATUS', $like);
	}



	//NOTE: THIS IS CURRENTLY ONLY DESIGNED FOR MYSQL/MARIADB
	public function fieldType($table, $column) {
		$return = $this->cell('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_TYPE', [
			'TABLE_NAME'	=> $table,
			'COLUMN_NAME'	=> $column,
		]);

		if (substr($return, 0, 5) === 'enum(') {
			$return = substr($return, 5, strlen($return)-6);
			$return = explode(',', $return);
			foreach ($return as $key => &$val) {
				if (substr($val, 0,  1) === "'") $val = substr($val, 1);
				if (substr($val, -1, 1) === "'") $val = substr($val, 0, strlen($val)-1);
			} unset($val);
		}

		return $return;
	}



	public function lock($table) {
		$query = 'LOCK TABLES ';

		if (is_array($table)) {
			$set = [];

			if (isset($table['read'])) {
				$item = $this->_lockTable($table['read'], 'READ');
				if (!empty($item)) $set[] = $item;
				unset($table['read']);
			}

			if (isset($table['write'])) {
				$item = $this->_lockTable($table['write'], 'WRITE');
				if (!empty($item)) $set[] = $item;
				unset($table['write']);
			}

			$item = $this->_lockTable($table, 'WRITE');
			if (!empty($item)) $set[] = $item;

			$query .= implode(', ', $set);
		} else {
			$query .= $table;
		}

		$this($query);
		$this->locked = true;

		return $this;
	}



	public function unlock() {
		if (!$this->locked) return $this;
		$this('UNLOCK TABLES');
		$this->locked = false;
		return $this;
	}



	public function begin() {
		if ($this->inTransaction()) return $this;
		$this->transaction = [];
		$this('START TRANSACTION');
		return $this;
	}



	public function commit($sleep=0) {
		if (!$this->inTransaction()) return $this;
		$this('COMMIT');
		$this->transaction = false;
		if ($sleep === true) $sleep = 250000;
		if (!empty($sleep)) usleep($sleep);
		return $this;
	}



	public function rollback() {
		if (!$this->inTransaction()) return $this;
		$this->transaction = false;
		$this('ROLLBACK');
		return $this;
	}



	protected function retryTransaction() {
		if (!$this->inTransaction()) return;

		$list = $this->transaction;
		$this->transaction = [];

		$return = false;
		foreach ($list as &$item) {
			$return = $this($item);
		} unset($item);

		return $return;
	}



	public function inTransaction() {
		return is_array($this->transaction);
	}



	public function debugger($debugger) {
		if (!is_callable($debugger)) {
			trigger_error('Function does not exist for pudl::debugger', E_USER_ERROR);
		}
		$this->debug = $debugger;
	}


	public function benchmark($benchmark) {
		$this->bench = $benchmark;
	}


	public function time() {
		return $this->time;
	}


	public function microtime() {
		return $this->microtime;
	}


	public function server() {
		return $this->server;
	}



	public function cache($seconds=0, $key=false) {
		$this->cache	= $seconds;
		$this->cachekey	= $key;
		return $this;
	}


	public function purge($key) {
		if (!$this->redis) return;
		try { $this->redis->delete("pudl:$key"); } catch (RedisException $e) {}
	}


	public function redis() { return $this->redis; }



	public function isString() { return end($this->string); }


	public function string() {
		$this->string[] = true;
		return $this;
	}


	public function in() {
		$this->string[] = ' IN ';
		return $this;
	}


	public function notIn() {
		$this->string[] = ' NOT IN ';
		return $this;
	}



	public static function column($value) {
		return new pudlColumn($value);
	}


	public static function inSet($value) {
		if (is_array($value)  &&  func_num_args() === 1) {
			return new pudlSet($value);
		} else if ($value instanceof pudlResult) {
			$set = [];
			while ($data = $value->row()) $set[] = reset($data);
			return new pudlSet($set);
		} else {
			return new pudlSet(func_get_args());
		}
	}


	public static function notInSet($value) {
		if (is_array($value)  &&  func_num_args() === 1) {
			return (new pudlSet($value))->not();
		} else if ($value instanceof pudlResult) {
			$set = [];
			while ($data = $value->row()) $set[] = reset($data);
			return (new pudlSet($set))->not();
		} else {
			return (new pudlSet(func_get_args()))->not();
		}
	}



	public static function between($low, $high)	{ return new pudlBetween($low, $high ); }
	public static function eq($value)			{ return new pudlEquals($value, '='  ); }
	public static function neq($value)			{ return new pudlEquals($value, '!=' ); }
	public static function nulleq($value)		{ return new pudlEquals($value, '<=>'); }
	public static function lt($value)			{ return new pudlEquals($value, '<'  ); }
	public static function lteq($value)			{ return new pudlEquals($value, '<=' ); }
	public static function gt($value)			{ return new pudlEquals($value, '>'  ); }
	public static function gteq($value)			{ return new pudlEquals($value, '>=' ); }
	public static function appendSet($value)	{ return new pudlappendSet($value); }
	public static function removeSet($value)	{ return new pudlRemoveSet($value); }
	public static function like($value)			{ return new pudlLike($value, PUDL_BOTH ); }
	public static function likeLeft($value)		{ return new pudlLike($value, PUDL_START); }
	public static function likeRight($value)	{ return new pudlLike($value, PUDL_END  ); }
	public static function regexp($value)		{ return new pudlRegexp($value); }
	public static function notLike($value)		{ return self::like($value)->not(); }
	public static function notLikeLeft($value)	{ return self::likeLeft($value)->not(); }
	public static function notLikeRight($value)	{ return self::likeRight($value)->not(); }
	public static function notRegexp($value)	{ return self::pudlRegexp($value)->not(); }



	public static function jsonEncode($data) {
		return @json_encode($data, JSON_HEX_APOS|JSON_HEX_QUOT);
	}

	public static function jsonDecode($data) {
		return @json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
	}




	private function _auth($instance, $data=false) {
		static $auth = [];
		if (!empty($data)) return $auth[$instance] = $data;
		if (empty($auth[$instance])) return [];
		return $auth[$instance];
	}


	protected function auth($data=false) {
		return $this->_auth($this->instance, $data);
	}



	private		$locked			= false;
	private		$debug			= false;
	private		$bench			= false;
	private		$query			= false;
	private		$time			= 0;
	private		$microtime		= 0;
	protected	$string			= [];
	protected	$cache			= false;
	protected	$cachekey		= false;
	protected	$redis			= false;
	protected	$shm			= false;
	protected	$server			= false;
	protected	$transaction	= false;

	private			$instance	= 0;
	private static	$instances	= 0;

	protected	$stats = [
		'total'		=> 0,
		'queries'	=> 0,
		'hits'		=> 0,
		'misses'	=> 0,
		'missed'	=> [],
	];
}
