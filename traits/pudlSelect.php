<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlSelect {


	public function select($column, $table=false, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function having($column, $table, $clause=false, $having=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_clause($having, 'HAVING');
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function group($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function groupHaving($column, $table, $clause=false, $group=false, $having=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_group($group);
		$query .= $this->_clause($having, 'HAVING');
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function orderGroup($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($column);
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
		return $this($query);
	}



	public function orderGroupEx($column, $table, $clause=false, $inner_group=false, $outer_group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($column);
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
		return $this($query);
	}



	public function distinct($column, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function distinctGroup($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT * FROM (SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= ') ';
		$query .= $this->_alias();
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function selectExplain($column, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_clause($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this->explain($query);
	}



	public function selex(/* ...$selex */) {
		$query		= '';
		$params		= [];
		$args		= func_get_args();
		foreach ($args as $arg) {
			$params	= array_merge_recursive($params, $arg);
		}

		if (!empty($params['explain']))		$query .= 'EXPLAIN ';

		$query .= 'SELECT ';
		$query .= $this->_cache();

		if (!empty($params['distinct']))	$query .= 'DISTINCT ';

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= ' *, COUNT(*) FROM (SELECT ';
		}

		if ( isset($params['column']))	$query .= $this->_column($params['column']);
		if (!isset($params['column']))	$query .= '*';
		if ( isset($params['table']))	$query .= $this->_tables($params['table']);
		if ( isset($params['clause']))	$query .= $this->_clause($params['clause']);

		if (isset($params['group'])  &&  isset($params['order'])) {
			$query .= $this->_order($params['order']);
			$query .= ') ';
			$query .= $this->_alias();
		}

		if (isset($params['order'])  &&  isset($params['group'])) {
			$query .= $this->_group($params['group'], NULL);
			if (isset($params['having'])) {
				$query .= $this->_clause($params['having'], 'HAVING');
			}
			$query .= $this->_order($params['order'], NULL);

		} else {
			if (isset($params['group']))	$query .= $this->_group($params['group']);
			if (isset($params['having']))	$query .= $this->_clause($params['having'], 'HAVING');
			if (isset($params['order']))	$query .= $this->_order($params['order']);
		}

		$limit	= isset($params['limit'])	? $params['limit']	: false;
		$offset	= isset($params['offset'])	? $params['offset']	: false;
		$query .= $this->_limit($limit, $offset);

		return $this($query);
	}



	public function selectRow($col, $table, $clause=false, $order=false, $limit=1, $offset=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->row();
		$result->free();
		return $return;
	}



	public function row($table, $clause=false, $order=false) {
		return $this->selectRow('*', $table, $clause, $order, 1, false);
	}



	public function rowEx($col, $table, $clause=false, $order=false) {
		return $this->selectRow($col, $table, $clause, $order, 1, false);
	}



	public function selectRows($col, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset);
		if ($this->inUnion()) return true;
		if ($result instanceof pudlStringResult) return $result;
		return $result->complete();
	}



	public function rows($table, $clause=false, $order=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false);
	}



	public function rowId($table, $column, $id=false) {
		return $this->row($table, $this->_clauseId($column,$id), false);
	}



	public function rowsId($table, $column, $id=false) {
		return $this->selectRows('*', $table, $this->_clauseId($column,$id), false, false, false);
	}



	public function cell($table, $col, $clause=false, $order=false) {
		$result = $this->select($col, $table, $clause, $order, 1, false);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return;
	}



	public function cellId($table, $col, $column, $id=false, $order=false) {
		return $this->cell($table, $col, $this->_clauseId($column,$id), $order);
	}



	public function id($table, $col, $column, $id=false, $order=false) {
		return (int) $this->cell($table, $col, $this->_clauseId($column,$id), $order);
	}



	public function collection($table, $key_column=false, $value_column=false, $clause=false, $order=false, $limit=false) {

		if ($key_column === false  &&  $value_column === false) {
			$result = $this->select('*', $table, $clause, $order, $limit);
		} else {
			$result = $this->select([$key_column, $value_column], $table, $clause, $order, $limit);
		}

		if ($result instanceof pudlStringResult) return $result;
		if ($result instanceof pudlResult) return $result->collection();
		return $result;
	}
}
