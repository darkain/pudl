<?php

require_once('pudlDefine.php');
require_once('pudlStringResult.php');
require_once('pudlQuery.php');


abstract class pudl extends pudlQuery {

	
	public function __construct() {
		$this->bench	= false;
		$this->debug	= false;
		$this->locked	= false;
		$this->query	= false;
		$this->tostring	= false;
		$this->shm		= false;
		$this->server	= false;
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

		if ($this->tostring) {
			$result = new pudlStringResult($query);
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
		$query  = 'SELECT *, COUNT(*) FROM (SELECT ';
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
		$query  = 'SELECT *, COUNT(*) FROM (SELECT ';
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
		$query  = 'SELECT DISTINCT ';
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
		$query  = 'SELECT DISTINCT * FROM (SELECT ';
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
		$query  = 'SELECT DISTINCT ';
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



	public function selectCache($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT SQL_CACHE ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->query($query);
	}



	public function selectNoCache($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT SQL_NO_CACHE ';
		$query .= $this->_top($limit);
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this->query($query);
	}
	
	
	
	public function selectEx(&$params) {
		if (isset($params['group'])  &&  isset($params['order'])) {
			$query = 'SELECT *, COUNT(*) FROM (SELECT ';
		} else {
			$query = 'SELECT ';
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
		$return = array();
		while ($data = $result->row()) { $return[] = $data; }
		$result->free();
		return $return;
	}



	public function rows($table, $clause=false, $order=false, $lock=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false, $lock);
	}



	public function rowId($table, $idcol, $id, $lock=false) {
		return $this->row($table, "{$this->escstart}$idcol{$this->escend}='$id'", false, $lock);
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



	public function cellId($table, $col, $idcol, $id) {
		return $this->cell($table, $col, "{$this->escstart}$idcol{$this->escend}='$id'");
	}



	public function idExists($table, $col, $id) {
		return ($this->cellId($table, $col, $col, $id) !== false);
	}



	public function clauseExists($table, $clause) {
		return ($this->cell($table, '*', $clause) !== false);
	}



	public function count($table, $clause='1') {
		$return = $this->cell($table, 'COUNT(*)', $clause);
		return $return === false ? $return : (int) $return;
	}



	public function countGroup($table, $clause, $group, $col=false) {
		if ($col === false) $col = $group;

		$query  = 'SELECT COUNT(*) FROM (';
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

		$query  = 'SELECT * FROM (';
		$query .= $this->_union($type);
		$query .= ') pudltablealias';
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this->query($query);
	}



	public function insert($table, $data, $safe=false, $update=false) {
		if (!is_array($data)) {
			trigger_error('Invalid data type for pudl::insert', E_USER_ERROR);
			return false;
		}

		$cols = '';
		$vals = '';

		$table = $this->_table($table);

		$count = 0;
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
				if ($count != 0) {
					$cols .= ', ';
					$vals .= ', ';
				}
				$cols .= "{$this->escstart}$column{$this->escend}";
				$vals .= $good;
				$count++;
			}
		}

		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table ($cols) VALUES ($vals)";
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table ($cols) VALUES ($vals)";
		} else {
			$query = "INSERT INTO $table ($cols) VALUES ($vals)";
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



	public function insertUpdate($table, $data, $idcol, $safe=false) {
		$update = "{$this->escstart}$idcol{$this->escend}=LAST_INSERT_ID({$this->escstart}$idcol{$this->escend})";
		return $this->insert($table, $data, $safe, $update);
	}



	public function insertEx($table, $cols, $data, $safe=false, $update=false) {
		if (!is_array($data)) {
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
		}

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
			}

			$query .= ')';
		}

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
		if (is_array($id)) $id = $id[$column];
		return $this->update($table, $data, "{$this->escstart}$column{$this->escend}='$id'", $safe);
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



	public function fieldType($table, $column, $safe=false) {
		if ($safe) {
			$table  = $this->safe($table);
			$column = $this->safe($column);
		}

		//TODO: convert this to some sort of standard SQL
		$query = "SELECT SQL_CACHE COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$table' AND COLUMN_NAME='$column'";
		$result = $this->query($query);
		$return = $result->cell();
		$result->free();

		if (substr($return, 0, 5) === 'enum(') {
			$return = substr($return, 5, strlen($return)-6);
			$return = explode(',', $return);
			foreach ($return as $key => &$val) {
				if (substr($val, 0,  1) === "'") $val = substr($val, 1);
				if (substr($val, -1, 1) === "'") $val = substr($val, 0, strlen($val)-1);
			}
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
				}
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
				}
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
			}
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
		}

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





	//Get or Set the TO STRING param
	//When TRUE, no SQL execution happens
	public function tostring($to=NULL) {
		if ($to === NULL) return $this->tostring;
		if (is_callable($to)) {
			$tmp = $this->tostring;
			$this->tostring = true;
			$to();
			$this->tostring = $tmp;
			return $this->query;
		}
		$this->tostring = !!$to;
	}


	//enable SQL execution
	public function on() {
		$this->tostring(false);
		return $this;
	}


	//disable SQL execution
	public function off() {
		$this->tostring(true);
		return $this;
	}


	private $locked;
	private $debug;
	private $bench;
	private $query;
	private $time;
	private $microtime;
	private $tostring;
	protected $shm;
	protected $server;
	protected $transaction;
}
