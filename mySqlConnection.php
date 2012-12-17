<?php


require_once('mySqlResult.php');
require_once('mySqlQuery.php');


class mySqlConnection {

	
	public function __construct($username, $password, $database, $server='localhost') {
		$this->bench  = false;
		$this->debug  = false;
		$this->union  = false;	
		$this->locked = false;
		
		$this->mysql  = false;
		$this->mysql  = @mysql_pconnect($server, $username, $password);
		
		if (!$this->mysql) {
			$this->mysql = @mysql_connect($server, $username, $password);
		}
		
		if (!$this->mysql) {
			$error  = "<br />\r\n";
			$error .= 'Unable to connect to database server: "' . $server;
			$error .= '" with the username: "' . $username;
			$error .= "\"<br />\r\nError " . mysql_errno() . ': ' . mysql_error(); 
			die($error);
		}
		
		$selected = false;
		$selected = @mysql_select_db($database, $this->mysql);
		if (!$selected) {
			$error  = "<br />\r\n";
			$error .= 'Unable to select database : "' . $database;
			$error .= "\"<br />\r\nError " . mysql_errno() . ': ' . mysql_error(); 
			die($error);
		}
	}
	
	
	
	public static function instance($data) {
		$username = isset($data['username']) ? $data['username'] : '';
		$password = isset($data['password']) ? $data['password'] : '';
		$database = isset($data['database']) ? $data['database'] : '';
		$server   = isset($data['server'])   ? $data['server']   : 'localhost';
		return new mySqlConnection($username, $password, $database, $server);
	}



	public static function safe($str) {
		$return = false;
		$return = @mysql_real_escape_string($str);
		return $return;
	}

	
	
	public function safer($str) {
		$return = false;
		$return = @mysql_real_escape_string($str, $this->mysql);
		return $return;
	}
	
	
	public function errno() {
		$return = false;
		$return = @mysql_errno($this->mysql);
		return $return;
	}

	
	
	public function error() {
		$return = false;
		$return = @mysql_error($this->mysql);
		return $return;
	}
	
	
	
	public function query($query) {
		if (is_array($this->union)) {
			$this->union[] = $query;
			return true;
		}
		
		if (!empty($this->bench)) $microtime = microtime();

		$result = false;
		$result = @mysql_query($query, $this->mysql);
		
		if (!empty($this->bench)) {
			$bench = $this->bench;
			$diff = round(microtime()-$microtime, 6);
			$bench($query, $diff, $this);
		}
		
		$return = new mySqlResult($result, $query);
		
		if ($result === false  &&  $this->debug !== false) {
			$debug = $this->debug;
			$debug($this, $return);
		}
		
		return $return;
	}
	
	
	
	public function insertId() {
		$return = false;
		$return = @mysql_insert_id($this->mysql);
		return $return;
	}

	
	
	public function updated() {
		$return = false;
		$return = @mysql_affected_rows($this->mysql);
		return $return;
	}
	


	public function listFields($table, $safe=false) {
		$return = array();
		if (is_array($table)) {
			foreach ($table as $t) {
				if ($safe) $t = $this->safer($t);
				$result = $this->query("SHOW COLUMNS FROM `$t`");
				while ($data = $result->row()) $return[$data['Field']] = $data;
				$result->free();
			}
		} else {
			if ($safe) $table = $this->safer($table);
			$result = $this->query("SHOW COLUMNS FROM `$table`");
			while ($data = $result->row()) $return[$data['Field']] = $data;
			$result->free();
		}
		return $return;
	}	



	public function select($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::group($group);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectOrderGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT *, COUNT(*) FROM (SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= ') groupbyorderby ';
		$query .= mySqlQuery::group($group);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::joinTable($join_table);
		$query .= mySqlQuery::joinClause($join_clause);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectDistinct($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT DISTINCT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectDistinctGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT DISTINCT * FROM (SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= ') groupbyorderby ';
		$query .= mySqlQuery::group($group);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectDistinctJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT DISTINCT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::joinTable($join_table);
		$query .= mySqlQuery::joinClause($join_clause);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectExplain($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->explain($query);
	}



	public function selectCache($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT SQL_CACHE ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}



	public function selectNoCache($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT SQL_NO_CACHE ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);
		$query .= mySqlQuery::lock($lock);
		return $this->query($query);
	}
	
	
	
	public function selectEx(&$params) {
		if (isset($params['group'])  &&  isset($params['order'])) {
			$query = 'SELECT *, COUNT(*) FROM (SELECT ';
		} else {
			$query = 'SELECT ';
		}

		if (isset($params['column'])) $query .= mySqlQuery::column($params['column']);
		if (isset($params['table' ])) $query .= mySqlQuery::table( $params['table' ]);
		if (isset($params['clause'])) $query .= mySqlQuery::clause($params['clause']);

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= mySqlQuery::order($params['order']);
			$query .= ') groupbyorderby ';
		}

		if (isset($params['group'])) $query .= mySqlQuery::group($params['group']);
		if (isset($params['order'])) $query .= mySqlQuery::order($params['order']);

		if (isset($params['limit'])) {
			if (isset($params['offset'])) {
				$query .= mySqlQuery::limit($params['limit'], $params['offset']);
			} else {
				$query .= mySqlQuery::limit($params['limit']);
			}
		}

		if (isset($params['lock'])) $query .= mySqlQuery::lock($params['lock']);

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
		return $this->row($table, "`$idcol`='$id'", false, $lock);
	}



	public function delete($table, $clause, $limit=false, $offset=false) {
		$query  = 'DELETE ';
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::limit($limit, $offset);
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
		$result = $this->selectCache($col, $table, $clause, $order, 1);
		$return = $result->cell();
		$result->free();
		return $return;
	}



	public function cellId($table, $col, $idcol, $id) {
		return $this->cell($table, $col, "`$idcol`='$id'");
	}



	public function idExists($table, $col, $id) {
		return ($this->cellId($table, $col, $col, $id) !== false);
	}



	public function clauseExists($table, $clause) {
		return ($this->cell($table, '*', $clause) !== false);
	}



	public function count($table, $clause='1') {
		return $this->cell($table, 'COUNT(*)', $clause);
	}



	public function countGroup($table, $clause, $group, $col='*') {
		$query  = 'SELECT COUNT(*) FROM (';
		$query .= 'SELECT ';
		$query .= mySqlQuery::column($col);
		$query .= mySqlQuery::table($table);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::group($group);
		$query .= ') groupbycount';

		$result = $this->query($query);
		$return = $result->cell();
		$result->free();
		return $return;
	}




	public function unionStart() {
		if ($this->union !== false) return false;
		$this->union = array();
		return true;
	}



	public function unionEnd($order=false, $limit=false, $offset=false, $type='') {
		if (!is_array($this->union)) return false;
		if ($type !== 'ALL'  &&  $type !== 'DISTINCT') $type = '';

		$query = '';
		$first = true;

		foreach($this->union as &$union) {
			if (!$first) $query .= " UNION $type ";
			$first = false;
			$query .= $union;
		}

		$query .= mySqlQuery::order($order);
		$query .= mySqlQuery::limit($limit, $offset);

		$this->union = false;
		return $this->query($query);
	}



	public function insert($table, $data, $safe=false, $update=false) {
		$cols = '';
		$vals = '';

		$count = 0;
		foreach ($data as $column => &$value) {
			$good = false;

			if (is_null($value)) {
				$good = 'NULL';
			} else if (is_array($value)) {
				foreach ($value as $func => $sub_value) {
					if ($func == 'AES_ENCRYPT') {
						if ($safe !== false) $sub_value['key']  = $this->safer($sub_value['key']);
						if ($safe !== false) $sub_value['data'] = $this->safer($sub_value['data']);
						$good = $func . '("' . $sub_value['data'] . '","' . $sub_value['key'] . '")';
					} else {
						if ($safe !== false) $sub_value = $this->safer($sub_value);
						$good = $func . '(' . $sub_value . ')';
					}
					break;
				}

			} else {
				if ($safe !== false) $value = $this->safer($value);
				$good = "'$value'";
			}

			if ($good !== false) {
				if ($count != 0) {
					$cols .= ', ';
					$vals .= ', ';
				}
				$cols .= "`$column`";
				$vals .= $good;
				$count++;
			}
		}

		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO `$table` ($cols) VALUES ($vals)";
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO `$table` ($cols) VALUES ($vals)";
		} else {
			$query = "INSERT INTO `$table` ($cols) VALUES ($vals)";
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= mySqlQuery::update($update, $safe);
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
		$update = "`$idcol`=LAST_INSERT_ID(`$idcol`)";
		return $this->insert($table, $data, $safe, $update);
	}



	public function insertEx($table, $cols, $data, $safe=false, $update=false) {
		$query = '';

		$first = true;
		foreach ($cols as &$name) {
			if (!first) $query .= ',';
			$first = false;
			$query .= "`$name`";
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
			$query = "INSERT IGNORE INTO `$table` (" . $query;
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO `$table` (" . $query;
		} else {
			$query = "INSERT INTO `$table` (" . $query;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= mySqlQuery::update($update, $safe);
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
		$query  = "UPDATE `$table` SET ";
		$query .= mySqlQuery::update($data, $safe);
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::limit($limit, $offset);
		return $this->query($query);
	}



	public function updateIn($table, $data, $field, $in, $safe=false, $limit=false, $offset=false) {
		$query  = "UPDATE `$table` SET ";
		$query .= mySqlQuery::update($data, $safe);
		$query .= " WHERE `$field` IN ($in)";
		$query .= mySqlQuery::limit($limit, $offset);
		return $this->query($query);
	}



	public function updateId($table, $data, $column, $id, $safe=false) {
		if (is_array($id)) $id = $id[$column];
		return $this->update($table, $data, "`$column`='$id'", $safe);
	}



	public function increment($table, $col, $clause, $amount=1, $limit=false, $offset=false) {
		$query = "UPDATE `$table` SET `$col`=`$col`+($amount) ";
		$query .= mySqlQuery::clause($clause);
		$query .= mySqlQuery::limit($limit, $offset);
		return $this->query($query);
	}



	public function fieldType($table, $column, $safe=false) {
		if ($safe) {
			$table  = $this->safer($table);
			$column = $this->safer($column);
		}

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
						$query .= "`$val` $key READ";
					} else {
						$query .= "`$val` READ";
					}
				}
			}

			if (isset($table['write'])) {
				foreach ($table['write'] as $key => &$val) {
					if (!$first) $query .= ', ';
					$first = false;
					if (!is_numeric($key)) {
						$query .= "`$val` $key WRITE";
					} else {
						$query .= "`$val` WRITE";
					}
				}
			}

			foreach ($table as $key => &$val) {
				if (!is_array($val)) {
					if (!$first) $query .= ', ';
					$first = false;
					if (!is_numeric($key)) {
						$query .= "`$val` $key WRITE";
					} else {
						$query .= "`$val` WRITE";
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
		$this->query('START TRANSACTION');
	}



	public function commit() {
		$this->query('COMMIT');
	}



	public function rollback() {
		$this->query('ROLLBACK');
	}
	
	
	
	public function debugger($debugger) {
		$this->debug = $debugger;
	}
	
	
	public function benchmark($benchmark) {
		$this->bench = $benchmark;
	}


	private $mysql;
	private $union;
	private $locked;
	private $debug;
	private $bench;
}
