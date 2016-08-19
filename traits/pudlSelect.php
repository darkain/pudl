<?php


trait pudlSelect {


	public function select($col, $table=false, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function having($col, $table, $clause=false, $having=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_clause($having, 'HAVING');
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function group($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function groupHaving($col, $table, $clause=false, $group=false, $having=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
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



	public function orderGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		if (pudl_array($limit)) $query .= $this->_limit($limit[0]);
		$query .= ') ';
		$query .= $this->_alias();
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		if (pudl_array($limit)) $query .= $this->_limit($limit[1], $offset);
		else $query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function orderGroupEx($col, $table, $clause=false, $inner_group=false, $outer_group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($inner_group);
		$query .= $this->_order($order);
		if (pudl_array($limit)) $query .= $this->_limit($limit[0]);
		$query .= ') ';
		$query .= $this->_alias();
		$query .= $this->_group($outer_group);
		$query .= $this->_order($order);
		if (pudl_array($limit)) $query .= $this->_limit($limit[1], $offset);
		else $query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function selectJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
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



	public function distinct($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function distinctGroup($col, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT * FROM (SELECT ';
		$query .= $this->_column($col);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= ') ';
		$query .= $this->_alias();
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		$query .= $this->_lock($lock);
		return $this($query);
	}



	public function distinctJoin($col, $table, $join_table, $join_clause, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
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

		if (isset($params['column']))	$query .= $this->_column($params['column']);
		if (!isset($params['column']))	$query .= '*';
		if (isset($params['table']))	$query .= $this->_tables($params['table']);
		if (isset($params['clause']))	$query .= $this->_clause($params['clause']);

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= $this->_order($params['order']);
			$query .= ') ';
			$query .= $this->_alias();
			$query .= ' ';
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



	public function rowLock($table, $clause=false, $order=false) {
		return $this->selectRow('*', $table, $clause, $order, 1, false, true);
	}



	public function rowEx($col, $table, $clause=false, $order=false, $lock=false) {
		return $this->selectRow($col, $table, $clause, $order, 1, false, $lock);
	}



	public function selectRows($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset, $lock);
		if ($this->inUnion()) return true;
		if ($result instanceof pudlStringResult) return $result;
		return $result->complete();
	}



	public function selectIndexed($col, $table, $clause=false, $order=false, $limit=false, $offset=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset, $lock);
		if ($this->inUnion()) return true;
		if ($result instanceof pudlStringResult) return $result;
		return $result->complete(PUDL_INDEX);
	}



	public function rows($table, $clause=false, $order=false, $lock=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false, $lock);
	}



	public function indexed($table, $clause=false, $order=false, $lock=false) {
		return $this->selectIndexed('*', $table, $clause, $order, false, false, $lock);
	}



	public function rowsLock($table, $clause=false, $order=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false, true);
	}



	public function rowId($table, $column, $id, $lock=false) {
		return $this->row($table, $this->_clauseId($column,$id), false, $lock);
	}



	public function rowLockId($table, $column, $id) {
		return $this->row($table, $this->_clauseId($column,$id), false, true);
	}



	public function rowsId($table, $column, $id, $lock=false) {
		return $this->selectRows('*', $table, $this->_clauseId($column,$id), false, false, false, $lock);
	}



	public function rowsLockId($table, $column, $id) {
		return $this->selectRows('*', $table, $this->_clauseId($column,$id), false, false, false, true);
	}



	public function cell($table, $col, $clause=false, $order=false, $lock=false) {
		$result = $this->select($col, $table, $clause, $order, 1, false, $lock);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return;
	}



	public function cellLock($table, $col, $clause=false, $order=false) {
		return $this->cell($table, $col, $clause, $order, true);
	}



	public function cellId($table, $col, $column, $id, $order=false, $lock=false) {
		return $this->cell($table, $col, $this->_clauseId($column,$id), $order, $lock);
	}



	public function cellLockId($table, $col, $column, $id, $order=false) {
		return $this->cell($table, $col, $this->_clauseId($column,$id), $order, true);
	}
}
