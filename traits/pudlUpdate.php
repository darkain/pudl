<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlUpdate {


	abstract public function updated();



	public function update($table, $data, $clause, $limit=false, $offset=false) {
		$query  = 'UPDATE ';
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data);
		$query .= $this->_where($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function updateIn($table, $data, $field, $in, $limit=false, $offset=false) {
		$query  = 'UPDATE ';
		$query .= $this->_table($table);
		$query .= ' SET ';
		$query .= $this->_update($data);
		$query .= ' WHERE (';
		$query .= $this->identifiers($field);
		$query .= $this->_in($in);
		$query .= ')';
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}



	public function updateId($table, $data, $column, $id=false, $limit=false, $offset=false) {
		return $this->update($table, $data, $this->_clauseId($column,$id), $limit, $offset);
	}




	public function updateExtract($table, $data, $clause, $limit=false, $offset=false) {
		return $this->update(
			$table,
			$this->extractColumns($table, $data),
			$clause,
			$limit,
			$offset
		);
	}



	public function updateExtractIn($table, $data, $field, $in, $limit=false, $offset=false) {
		return $this->updateIn(
			$table,
			$this->extractColumns($table, $data),
			$field,
			$in,
			$limit,
			$offset
		);
	}



	public function updateExtractId($table, $data, $column, $id=false, $limit=false, $offset=false) {
		return $this->update(
			$table,
			$this->extractColumns($table, $data),
			$this->_clauseId($column, $id),
			$limit,
			$offset
		);
	}



	public function updateField($table, $field, $value, $clause, $limit=false, $offset=false) {
		return $this->update($table, [$field=>$value], $clause, $limit, $offset);
	}



	public function updateFieldId($table, $field, $value, $column, $id=false, $limit=false, $offset=false) {
		return $this->update($table, [$field=>$value], $this->_clauseId($column,$id), $limit, $offset);
	}



	public function updateCount($table_update, $field, $clause_update, $table_select, $clause_select=true, $limit=false, $offset=false) {
		if ($clause_select === true) $clause_select = $clause_update;
		return $this->update($table_update, [
			$field => $this->string()->count($table_select, $clause_select)
		], $clause_update, $limit, $offset);
	}



	public function increment($table, $col, $clause, $amount=1, $limit=false, $offset=false) {
		switch (true) {
			case $amount === NAN:
			case $amount === INF:
			case $amount === -INF:
			case is_bool($amount):
			case is_null($amount):
			case pudl_array($amount):
			return $this->_invalidType($amount, 'increment');
		}

		$value = $this->_value($amount);

		switch (true) {
			case is_int($value)		&&  $value >= 0:
			case is_float($value)	&&  $value >= 0:
			case !is_int($value)	&&  !is_float($value):
				$value = '+' . (string)$value;
		}

		return $this('UPDATE '
			. $this->_table($table)
			. ' SET '	. $this->identifiers($col)
			. '='		. $this->identifiers($col)
			. $value
			. $this->_where($clause)
			. $this->_limit($limit, $offset)
		);
	}



	public function incrementId($table, $col, $column, $id=false, $amount=1, $limit=false, $offset=false) {
		return $this->increment($table, $col, $this->_clauseId($column,$id), $amount, $limit, $offset);
	}

}
