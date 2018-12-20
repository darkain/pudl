<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlSelect {



	////////////////////////////////////////////////////////////////////////////
	// GENERAL SELECT STATEMENT, RETURNING A RESULT
	////////////////////////////////////////////////////////////////////////////
	public function select($column, $table=false, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "HAVING"
	////////////////////////////////////////////////////////////////////////////
	public function having($column, $table, $clause=false, $having=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_having($having);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "GROUP BY"
	////////////////////////////////////////////////////////////////////////////
	public function group($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "GROUP BY" AND "HAVING"
	////////////////////////////////////////////////////////////////////////////
	public function groupHaving($column, $table, $clause=false, $group=false, $having=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_group($group);
		$query .= $this->_having($having);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "ORDER BY" AND "GROUP BY"
	////////////////////////////////////////////////////////////////////////////
	public function orderGroup($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
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




	////////////////////////////////////////////////////////////////////////////
	// SELECT "ORDER BY" "GROUP BY" USING DIFFERENT INNER AND OUTTER GROUPINGS
	////////////////////////////////////////////////////////////////////////////
	public function orderGroupEx($column, $table, $clause=false, $inner_group=false, $outer_group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
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




	////////////////////////////////////////////////////////////////////////////
	// SELECT DISTINCT QUERY
	////////////////////////////////////////////////////////////////////////////
	public function distinct($column, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT DISTINCT QUERY USING A "GROUP BY"
	////////////////////////////////////////////////////////////////////////////
	public function distinctGroup($column, $table, $clause=false, $group=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT * FROM (SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_order($order);
		$query .= ') ';
		$query .= $this->_alias();
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// EXPLAIN A SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	public function selectExplain($column, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$query  = 'SELECT ';
		$query .= $this->_column($column);
		$query .= $this->_tables($table);
		$query .= $this->_where($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this->explain($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELEX - COMPLEX SQL SELECT STATEMENT GENERATOR USING ARRAY(S)
	////////////////////////////////////////////////////////////////////////////
	public function selex(/* ...$selex */) {
		$query		= '';
		$params		= [];
		$args		= func_get_args();
		foreach ($args as $arg) {
			$params	= array_merge_recursive($params, $arg);
		}


		//	ARE WE IN OUR CUSTOM (GROUP BY) + (ORDER BY) SUBQUERY?
		$subquery = (isset($params['group'])  &&  isset($params['order']));


		//	EXPLAIN
		if (!empty($params['explain'])) $query .= 'EXPLAIN ';


		//	CTE
		if (!empty($params['cte'])  &&  pudl_array($params['cte'])) {
			$this->cte_query = reset($params['cte']);
			$this->cte_alias = key($params['cte']);
			$query .= $this->_cte();
		}


		//	SELECT
		$query .= 'SELECT ';


		//	(NO) CACHE
		$query .= $this->_cache();


		//	DISTICT
		if (!empty($params['distinct'])) $query .= 'DISTINCT ';


		//	(GROUP BY + ORDER BY) SUBQUERY
		if ($subquery) {
			$query .= '*, COUNT(*) FROM (SELECT ';
		}


		//	COLUMNS
		if (!empty($params['column'])) {
			$query .= $this->_column($params['column']);
		} else {
			$query .= '*';
		}


		//	FROM
		if (isset($params['table'])) {
			$query .= $this->_tables($params['table']);
		}


		//	WHERE
		if (!empty($params['where'])) {
			$query .= $this->_where($params['where']);
		} else if (!empty($params['clause'])) {
			$query .= $this->_where($params['clause']);
		}


		//	(GROUP BY + ORDER BY) SUBQUERY
		if ($subquery) {
			//	ORDER BY
			$query .= $this->_order($params['order']);

			//	SUBQUERY ALIAS
			$query .= ') ' . $this->_alias();
		}


		//	GROUP BY
		if (isset($params['group'])) {
			$query .= $this->_group($params['group'], $subquery?NULL:false);
		}


		//	HAVING
		if (isset($params['having'])) {
			$query .= $this->_having($params['having']);
		}


		//	ORDER BY
		if (isset($params['order'])) {
			$query .= $this->_order($params['order'], $subquery?NULL:false);
		}


		//	LIMIT AND OFFSET
		$limit	= isset($params['limit'])	? $params['limit']	: false;
		$offset	= isset($params['offset'])	? $params['offset']	: false;
		$query .= $this->_limit($limit, $offset);


		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE ROW MATCHING THE OPTIONAL CLAUSE AS A RESULT
	////////////////////////////////////////////////////////////////////////////
	public function selectRow($col, $table, $clause=false, $order=false, $limit=1, $offset=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->row();
		$result->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE ROW MATCHING THE OPTIONAL CLAUSE AS ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function row($table, $clause=false, $order=false) {
		return $this->selectRow('*', $table, $clause, $order, 1, false);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS A ARRAYS
	////////////////////////////////////////////////////////////////////////////
	public function rowEx($col, $table, $clause=false, $order=false) {
		return $this->selectRow($col, $table, $clause, $order, 1, false);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS A RESULT
	////////////////////////////////////////////////////////////////////////////
	public function selectRows($col, $table, $clause=false, $order=false, $limit=false, $offset=false) {
		$result = $this->select($col, $table, $clause, $order, $limit, $offset);
		if ($this->inUnion()) return true;
		if ($result instanceof pudlStringResult) return $result;
		return $result->complete();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS ARRAYS
	////////////////////////////////////////////////////////////////////////////
	public function rows($table, $clause=false, $order=false) {
		return $this->selectRows('*', $table, $clause, $order, false, false);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE ROW MATCHING THE ID
	////////////////////////////////////////////////////////////////////////////
	public function rowId($table, $column, $id=false) {
		return $this->row($table, $this->_clauseId($column,$id), false);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE ID
	////////////////////////////////////////////////////////////////////////////
	public function rowsId($table, $column, $id=false) {
		return $this->selectRows('*', $table, $this->_clauseId($column,$id), false, false, false);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE CELL
	////////////////////////////////////////////////////////////////////////////
	public function cell($table, $col, $clause=false, $order=false) {
		$result = $this->select($col, $table, $clause, $order, 1, false);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE CELL BASED ON THE VALUE OF AN ID
	////////////////////////////////////////////////////////////////////////////
	public function cellId($table, $col, $column, $id=false, $order=false) {
		return $this->cell($table, $col, $this->_clauseId($column,$id), $order);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL AN INTEGER ID COLUMN BASED ON THE VALUE OF ANOTHER COLUMN
	////////////////////////////////////////////////////////////////////////////
	public function id($table, $col, $column, $id=false, $order=false) {
		return (int) $this->cell($table, $col, $this->_clauseId($column,$id), $order);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A KEY/VALUE COLUMN PAIR AND COMBINE THEM INTO A SINGLE PHP ARRAY
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// PING THE DATABASE SERVER TO ENSURE CONNECTION STAYS ALIVE
	////////////////////////////////////////////////////////////////////////////
	public function ping() {
		$return = $this('SELECT 1')->complete();
		return ($return instanceof pudlStringResult)
			? $return
			: !empty($return);
	}


}
