<?php


trait pudlCounter {


	////////////////////////////////////////////////////////////////////////////
	//COUNT THE NUMBER OF ROWS IN A TABLE
	////////////////////////////////////////////////////////////////////////////
	public function count($table, $clause=false) {
		$return = $this->cell($table, static::_count(), $clause);
		if ($return instanceof pudlStringResult) return $return;
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//COUNT THE NUMBER OF TIMES AN ID EXISTS
	////////////////////////////////////////////////////////////////////////////
	public function countId($table, $column, $id=false) {
		return $this->count($table, $this->_clauseId($column,$id));
	}




	////////////////////////////////////////////////////////////////////////////
	//COUNT THE NUMBER OF TIMES A PARTICULAR GROUP EXISTS
	////////////////////////////////////////////////////////////////////////////
	public function countGroup($table, $clause, $group, $col=false) {
		if ($col === false) $col = $group;

		$query =	'SELECT ' .
					$this->_cache() .
					'COUNT(*) FROM (' .
					'SELECT ' .
					$this->_column($col) .
					$this->_tables($table) .
					$this->_clause($clause) .
					$this->_group($group) .
					') ' .
					$this->_alias();

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->completeCell();
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//RETURNS THE SPECIAL FUNCTION TO COUNT A PARITCULAR COLUMN
	////////////////////////////////////////////////////////////////////////////
	public static function _count($column='*') {
		return new pudlCount($column);
	}

}
