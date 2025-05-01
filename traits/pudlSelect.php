<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlSelect {



	////////////////////////////////////////////////////////////////////////////
	// GENERAL SELECT STATEMENT, RETURNING A RESULT
	////////////////////////////////////////////////////////////////////////////
	public function select(	$columns=NULL,	$tables=NULL,	$clause=NULL,
							$order=NULL,	$limit=NULL,	$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_columns(	$columns);
		$query .= $this->_tables(	$tables);
		$query .= $this->_where(	$clause);
		$query .= $this->_order(	$order);
		$query .= $this->_limit(	$limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "HAVING"
	////////////////////////////////////////////////////////////////////////////
	public function having(	$columns=NULL,	$tables=NULL,	$clause=NULL,
							$having=NULL,	$order=NULL,	$limit=NULL,
							$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_columns(	$columns);
		$query .= $this->_tables(	$tables);
		$query .= $this->_where(	$clause);
		$query .= $this->_having(	$having);
		$query .= $this->_order(	$order);
		$query .= $this->_limit(	$limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "GROUP BY"
	////////////////////////////////////////////////////////////////////////////
	public function group(	$columns=NULL,	$tables=NULL,	$clause=NULL,
							$group=NULL,	$order=NULL,	$limit=NULL,
							$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
		$query .= $this->_where($clause);
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT USING "GROUP BY" AND "HAVING"
	////////////////////////////////////////////////////////////////////////////
	public function groupHaving(	$columns=NULL,	$tables=NULL,	$clause=NULL,
									$group=NULL,	$having=NULL,	$order=NULL,
									$limit=NULL,	$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
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
	public function orderGroup(	$columns=NULL,	$tables=NULL,	$clause=NULL,
								$group=NULL,	$order=NULL,	$limit=NULL,
								$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
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
	public function orderGroupEx(	$columns=NULL,	$tables=NULL,	$clause=NULL,
									$inner_group=NULL,	$outer_group=NULL,
									$order=NULL,	$limit=NULL,	$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '*, COUNT(*) FROM (SELECT ';
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
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
	public function distinct(	$columns=NULL,	$tables=NULL,	$clause=NULL,
								$order=NULL,	$limit=NULL,	$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT ';
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
		$query .= $this->_where($clause);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SELECT DISTINCT QUERY USING A "GROUP BY"
	////////////////////////////////////////////////////////////////////////////
	public function distinctGroup(	$columns=NULL,	$tables=NULL,	$clause=NULL,
									$group=NULL,	$order=NULL,	$limit=NULL,
									$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= 'DISTINCT * FROM (SELECT ';
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
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
	public function selectExplain(	$columns=NULL,	$tables=NULL,	$clause=NULL,
									$order=NULL,	$limit=NULL,	$offset=NULL) {
		$query  = 'SELECT ';
		$query .= $this->_columns($columns);
		$query .= $this->_tables($tables);
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
			$query .= $this->_columns($params['column']);
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
	public function selectRow(	$columns=NULL,	$tables=NULL,	$clause=NULL,
								$order=NULL,	$limit=1,		$offset=NULL) {
		$result = $this->select($columns, $tables, $clause, $order, $limit, $offset);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->row();
		$result->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE ROW MATCHING THE OPTIONAL CLAUSE AS ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function row($tables=NULL, $clause=NULL, $order=NULL) {
		return $this->selectRow('*', $tables, $clause, $order, 1);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS A ARRAYS
	////////////////////////////////////////////////////////////////////////////
	public function rowEx($columns=NULL, $tables=NULL, $clause=NULL, $order=NULL) {
		return $this->selectRow($columns, $tables, $clause, $order, 1);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS A RESULT
	////////////////////////////////////////////////////////////////////////////
	public function selectRows(	$columns=NULL,	$tables=NULL,	$clause=NULL,
								$order=NULL,	$limit=NULL,	$offset=NULL) {
		$result = $this->select($columns, $tables, $clause, $order, $limit, $offset);
		if ($this->inUnion()) return [];
		if ($result instanceof pudlStringResult) return $result;
		return $result->complete();
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE OPTIONAL CLAUSE AS ARRAYS
	////////////////////////////////////////////////////////////////////////////
	public function rows($tables, $clause=NULL, $order=NULL) {
		return $this->selectRows('*', $tables, $clause, $order, NULL, NULL);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE ROW MATCHING THE ID
	////////////////////////////////////////////////////////////////////////////
	public function rowId($tables, $search=NULL, $id=false) {
		return $this->row($tables, $this->_clauseId($search, $id));
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL ALL ROWS MATCHING THE ID
	////////////////////////////////////////////////////////////////////////////
	public function rowsId($tables, $search, $id=false) {
		return $this->rows($tables, $this->_clauseId($search, $id));
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE CELL
	////////////////////////////////////////////////////////////////////////////
	public function cell($tables, $column, $clause=NULL, $order=NULL) {
		$result = $this->select($column, $tables, $clause, $order, 1, NULL);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->cell();
		$result->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE CELL BASED ON THE VALUE OF AN ID
	////////////////////////////////////////////////////////////////////////////
	public function cellId($tables, $column, $search, $id=false, $order=NULL) {
		return $this->cell($tables, $column, $this->_clauseId($search, $id), $order);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A SINGLE CELL BASED ON THE VALUE OF AN ID, RETURNING JSON DATA
	////////////////////////////////////////////////////////////////////////////
	public function jsonId($tables, $column, $search, $id=false, $order=NULL) {
		return pudl::jsonDecode(
			$this->cellId($tables, $column, $search, $id, $order)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL AN INTEGER ID COLUMN BASED ON THE VALUE OF ANOTHER COLUMN
	////////////////////////////////////////////////////////////////////////////
	public function id($tables, $column, $search, $id=false, $order=NULL) {
		return (int) $this->cell($tables, $column, $this->_clauseId($search, $id), $order);
	}




	////////////////////////////////////////////////////////////////////////////
	// PULL A KEY/VALUE COLUMN PAIR AND COMBINE THEM INTO A SINGLE PHP ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function collection(	$tables,		$key_column=NULL,		$value_column=NULL,
								$clause=NULL,	$order=NULL,			$limit=NULL) {

		if ($key_column === NULL  &&  $value_column === NULL) {
			$result = $this->select('*', $tables, $clause, $order, $limit);
		} else {
			$result = $this->select([$key_column, $value_column], $tables, $clause, $order, $limit);
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
