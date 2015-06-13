<?php

require_once('pudlDefine.php');
require_once('pudlStringResult.php');
require_once('pudlCacheResult.php');
require_once('pudlQuery.php');
require_once('pudlFunction.php');


abstract class pudl extends pudlQuery {


	public function __construct() {
		$this->bench	= false;
		$this->debug	= false;
		$this->locked	= false;
		$this->query	= false;
		$this->string	= false;
		$this->shm		= false;
		$this->server	= false;
		$this->cache	= false;
		$this->cachekey	= false;
		$this->redis	= false;
		$this->time		= time();
		$this->microtime= microtime();
		$this->transaction = false;
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


	public function query($query=false) {
		if ($query === false) return $this->query;

		if (is_array($this->union)) {
			$this->union[] = $query;
			return true;
		}

		if (!empty($this->bench)) $microtime = microtime(true);

		$this->query = $query;

		if (is_array($this->transaction)) $this->transaction[] = $query;

		if ($this->string === true) {
			$result = new pudlStringResult($this);
			$this->string = false;

		} else if ($this->string !== false) {
			$this->query = $this->string . '(' . $this->query . ')';
			$result = new pudlStringResult($this);
			$this->string = false;

		} else if ($this->cache  &&  $this->redis) {
			$hash = $this->cachekey;
			if (empty($hash)) $hash = md5($query);
			$data = $this->redis->get("pudl:$hash");
			if (empty($data)) {
				$result	= $this->process($query);
				$data	= $result->rows();
				$this->redis->set("pudl:$hash", $data, $this->cache);
			}
			$result = new pudlCacheResult($data, $this);
			$this->cache = $this->cachekey = false;

		} else {
			$result = $this->process($query);
		}

		if (!empty($this->bench)) {
			$bench = $this->bench;
			$diff = round(microtime(true)-$microtime, 6);
			$bench($query, $diff, $this);
		}

		if ($result->error()  &&  $this->debug !== false) {
			$debug = $this->debug;
			$debug($this, $result);
		}

		return $result;
	}



	public function listFields($table, $safe=false) {
		$return = array();
		if (is_array($table)) {
			foreach ($table as $t) {
				if ($safe) $t = $this->safe($t);
				$t = $this->_table($t);
				$result = $this->query("SHOW COLUMNS FROM $t");
				while ($data = $result->row()) $return[$data['Field']] = $data;
				$result->free();
			}
		} else {
			if ($safe) $table = $this->safe($table);
			$table = $this->_table($table);
			$result = $this->query("SHOW COLUMNS FROM $table");
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
		return $this->query($query);
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
		return $this->query($query);
	}



	public function selectGroupHaving($col, $table, $clause=false, $group=false, $having=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		if (empty($having)) $query .= " HAVING $having ";
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->query($query);
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
		return $this->query($query);
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
		return $this->query($query);
	}



	public function selectJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_joinTable($join_table);
		$query .= $this->_joinClause($join_clause);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->query($query);
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
		return $this->query($query);
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
		return $this->query($query);
	}



	public function selectDistinctJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_joinTable($join_table);
		$query .= $this->_joinClause($join_clause);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->query($query);
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




	public function selectEx(&$params) {
		$query = 'SELECT ';
		$query .= $this->_cache();

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= ' *, COUNT(*) FROM (SELECT ';
		}

		if (isset($params['limit' ])) $query .= $this->_top(   $params['limit ']);
		if (isset($params['column'])) $query .= $this->_column($params['column']);
		if (isset($params['table' ])) $query .= $this->_tables($params['table' ]);
		if (isset($params['clause'])) $query .= $this->_clause($params['clause']);

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= $this->_order($params['order']);
			$query .= ') groupbyorderby ';
		}

		if (isset($params['group'])) $query .= $this->_group($params['group']);
		if (isset($params['order'])) $query .= $this->_order($params['order']);

		if (isset($params['limit'])) {
			if (isset($params['offset'])) {
				$query .= $this->_limit($params['limit'], $params['offset']);
			} else {
				$query .= $this->_limit($params['limit']);
			}
		}

		if (isset($params['lock'])) $query .= $this->_lock($params['lock']);

		$result = $this->query($query);
		$params['rows'] = $result->count();
		return $result;
	}



	public function selectRow($col, $table, $clause=false, $order=false, $limit=1, $offset=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset, $lock);
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



	public function delete($table, $clause, $limit=false, $offset=false) {
		$query  = 'DELETE ';
		$query .= $this->_top($limit);
		$query .= ' FROM ';
		$query .= $this->_table($table);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this->query($query);
	}


	public function deleteId($table, $column, $id) {
		return $this->delete($table, $this->_clauseId($column,$id));
	}



	public function explain($query) {
		$return = '';
		$result = $this->query("EXPLAIN $query");
		while ($data = $result->row()) $return .= print_r($data, true);
		$result->free();
		return $return;
	}



	public function cell($table, $col, $clause=false, $order=false) {
		$result = $this->select($col, $table, $clause, $order, 1);
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

		$result = $this->query($query);
		$return = $result->cell();
		$result->free();

		return $return === false ? $return : (int) $return;
	}



	public function found() {
		$result = $this->query('SELECT FOUND_ROWS()');
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
		return $this->query($query);
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
		return $this->query($query);
	}



	public function insertValues($table, $data, $safe=false, $update=false) {
		return $this->insert($table, $data, $safe, $update, false);
	}



	public function insert($table, $data, $safe=false, $update=false, $prefix=true) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insert', E_USER_ERROR);
			return false;
		}

		$cols	= '(';
		$vals	= '';
		$first	= true;
		foreach ($data as $column => $value) {
			if (!$first) {
				$cols .= ', ';
				$vals .= ', ';
			} else $first = false;

			$cols .= $this->escstart . $column . $this->escend;
			$vals .= $this->_columnData($value, $safe);
		}

		if ($prefix) $cols .= ')'; else $cols = '';

		$table = $this->_table($table);
		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table $cols VALUES ($vals)";
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table $cols VALUES ($vals)";
		} else {
			$query = "INSERT INTO $table $cols VALUES ($vals)";
			if ($update === true) $update = $data;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update, $safe);
			}
		}

		$this->query($query);
		return $this->insertId();
	}



	public function replace($table, $data, $safe=false) {
		return $this->insert($table, $data, $safe, "REPLACE");
	}



	public function insertIgnore($table, $data, $safe=false) {
		return $this->insert($table, $data, $safe, "IGNORE");
	}



	public function insertUpdate($table, $data, $column, $safe=false) {
		$update = "{$this->escstart}$column{$this->escend}=LAST_INSERT_ID({$this->escstart}$column{$this->escend})";
		return $this->insert($table, $data, $safe, $update);
	}



	public function insertEx($table, $cols, $data, $safe=false, $update=false) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insertEx', E_USER_ERROR);
			return false;
		}

		$table = $this->_table($table);

		$query = '';

		$first = true;
		foreach ($cols as &$name) {
			if (!first) $query .= ',';
			$first = false;
			$query .= "{$this->escstart}$name{$this->escend}";
		} unst($name);

		$query .= ') VALUES ';

		$first = true;
		foreach ($data as &$set) {
			if (!first) $query .= ',';
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
				$query .= $this->_update($update, $safe);
			}
		}

		$this->query($query);
		return $this->insertId();
	}



	public function replaceEx($table, $cols, $data, $safe=false) {
		return $this->insertEx($table, $cols, $data, $safe, "REPLACE");
	}



	public function insertIgnoreEx($table, $cols, $data, $safe=false) {
		return $this->insertEx($table, $cols, $data, $safe, "IGNORE");
	}



	public function update($table, $data, $clause, $safe=false, $limit=false, $offset=false) {
		$query  = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data, $safe);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this->query($query);
	}



	public function updateIgnore($table, $data, $clause, $safe=false, $limit=false, $offset=false) {
		$query  = 'UPDATE IGNORE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data, $safe);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this->query($query);
	}



	public function updateIn($table, $data, $field, $in, $safe=false, $limit=false, $offset=false) {
		if (is_array($in)) $in = implode(',', $in);
		$query  = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data, $safe);
		$query .= " WHERE {$this->escstart}$field{$this->escend} IN ($in)";
		$query .= $this->_limit($limit, $offset);
		return $this->query($query);
	}



	public function updateId($table, $data, $column, $id, $safe=false) {
		return $this->update($table, $data, $this->_clauseId($column,$id), $safe);
	}



	public function increment($table, $col, $clause, $amount=1, $limit=false, $offset=false) {
		$query = 'UPDATE ';
		$query .= $this->_top($limit);
		$query .= $this->_table($table);
		$query .= " SET {$this->escstart}$col{$this->escend}={$this->escstart}$col{$this->escend}+($amount) ";
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this->query($query);
	}



	public function listItems($type, $like=false) {
		$query = 'SHOW ' . $type;
		if (!empty($like)) $query .= ' LIKE "' . $like . '"';
		$result = $this->query($query);

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



	public function fieldType($table, $column, $safe=false) {
		if ($safe) {
			$table  = $this->safe($table);
			$column = $this->safe($column);
		}

		//TODO: convert this to some sort of standard SQL
		$query = "SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$table' AND COLUMN_NAME='$column'";
		$result = $this->query($query);
		$return = $result->cell();
		$result->free();

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
			$first = true;

			if (isset($table['read'])) {
				foreach ($table['read'] as $key => &$val) {
					if (!$first) $query .= ', ';
					$first = false;
					if (!is_numeric($key)) {
						$query .= "{$this->escstart}$val{$this->escend} $key READ";
					} else {
						$query .= "{$this->escstart}$val{$this->escend} READ";
					}
				} unset($val);
			}

			if (isset($table['write'])) {
				foreach ($table['write'] as $key => &$val) {
					if (!$first) $query .= ', ';
					$first = false;
					if (!is_numeric($key)) {
						$query .= "{$this->escstart}$val{$this->escend} $key WRITE";
					} else {
						$query .= "{$this->escstart}$val{$this->escend} WRITE";
					}
				} unset($val);
			}

			foreach ($table as $key => &$val) {
				if (!is_array($val)) {
					if (!$first) $query .= ', ';
					$first = false;
					if (!is_numeric($key)) {
						$query .= "{$this->escstart}$val{$this->escend} $key WRITE";
					} else {
						$query .= "{$this->escstart}$val{$this->escend} WRITE";
					}
				}
			} unset($val);
		} else {
			$query .= $table;
		}

		$this->query($query);
		$this->locked = true;
	}



	public function unlock() {
		if (!$this->locked) return;
		$this->query('UNLOCK TABLES');
		$this->locked = false;
	}



	public function begin() {
		if (!empty($this->transaction)) return;
		$this->transaction = [];
		$this->query('START TRANSACTION');
	}



	public function commit($sleep=0) {
		if (empty($this->transaction)) return;
		$this->query('COMMIT');
		$this->transaction = false;
		if (!empty($sleep)) usleep($sleep);
	}



	public function rollback() {
		if (empty($this->transaction)) return;
		$this->transaction = false;
		$this->query('ROLLBACK');
	}



	protected function retryTransaction() {
		if (empty($this->transaction)) return;

		$list = $this->transaction;
		$this->transaction = false;

		$return = false;
		foreach ($list as &$item) {
			$return = $this->query($item);
		} unset($item);

		return $return;
	}



	public function inTransaction() {
		return is_array($this->transaction);
	}



	public function debugger($debugger) {
		if (!function_exists($debugger)) {
			die("<br />\nERROR: PUDL debugger function does not exist: $debugger()");
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


	public function onlineServers($servers) {
		return $servers;
		if (count($servers) < 2) return $servers;

		$key = ftok(__FILE__, 't');
		$shm = shm_attach($key);

		if (!shm_has_var($shm, 1)) {
			shm_detach($shm);
			return $servers;
		}

		$list = shm_get_var($shm, 1);
		foreach ($servers as $index => &$item) {
			if (in_array($item, $list)) unset($servers[$index]);
		} unset($item);

		shm_detach($shm);
		return $servers;
	}


	public function onlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		foreach ($list as $key => &$val) {
			if ($val === $server) unset($list[$key]);
		} unset($val);
		shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}


	public function offlineServer($server) {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		if (!in_array($server, $list)) $list[] = $server;
		shm_put_var($shm, 1, $list);
		shm_detach($shm);
	}


	public function offlineServers() {
		$key	= ftok(__FILE__, 't');
		$shm	= shm_attach($key);
		$list	= shm_has_var($shm, 1) ? shm_get_var($shm, 1) : [];
		shm_detach($shm);
		return $list;
	}



	public function cache($seconds=0, $key=false) {
		$this->cache	= $seconds;
		$this->cachekey	= $key;
		return $this;
	}


	public function purge($key) {
		if (!$this->redis) return;
		$this->redis->delete("pudl:$key");
	}


	public function redis() { return $this->redis; }



	public function string() {
		$this->string = true;
		return $this;
	}


	public function in($column=false) {
		$this->string = ($column===false ? '' :  $this->_columnValue(false,$column)) . ' IN ';
		return $this;
	}


	public function notIn($column=false) {
		$this->string = ($column===false ? '' :  $this->_columnValue(false,$column)) . ' NOT IN ';
		return $this;
	}


	private $locked;
	private $debug;
	private $bench;
	private $query;
	private $time;
	private $microtime;
	private $string;
	protected $cache;
	protected $cachekey;
	protected $redis;
	protected $shm;
	protected $server;
	protected $transaction;
}
