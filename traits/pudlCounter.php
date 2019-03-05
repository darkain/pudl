<?php


trait pudlCounter {


	////////////////////////////////////////////////////////////////////////////
	// COUNT THE NUMBER OF ROWS IN A TABLE
	// $pudl->count('table') is a virtual alias of $pudl->total('table')
	////////////////////////////////////////////////////////////////////////////
	public function total($table, $clause=NULL) {
		$return	= $this->cell($table, new pudlCount(), $clause);
		if ($return instanceof pudlStringResult) return $return;
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESTIMATE THE NUMBER OF ROWS IN A TABLE
	// SIGNIFICANTLY FASTER THAN COUNT(*) ON LARGER TABLES (1 MILLION+ ROWS)
	// BUT NOT 100% ACCURATE
	////////////////////////////////////////////////////////////////////////////
	public function estimate($table) {
		$auth	= $this->auth();

		$return	= $this->cell(
			pudl::raw('INFORMATION_SCHEMA.TABLES'),
			'TABLE_ROWS',
			[
				'TABLE_SCHEMA'	=> $auth['database'],
				'TABLE_NAME'	=> $this->_prefix($table),
			]
		);

		if ($return instanceof pudlStringResult) return $return;
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// COUNT THE NUMBER OF TIMES AN ID EXISTS
	////////////////////////////////////////////////////////////////////////////
	public function countId($table, $column, $id=false) {
		return $this->total($table, $this->_clauseId($column,$id));
	}




	////////////////////////////////////////////////////////////////////////////
	// COUNT THE NUMBER OF TIMES A PARTICULAR GROUP EXISTS
	////////////////////////////////////////////////////////////////////////////
	public function countGroup($table, $clause, $group, $col=false) {
		if ($col === false) $col = $group;

		$query =	'SELECT ' .
					$this->_cache() .
					'COUNT(*) FROM (' .
					'SELECT ' .
					$this->_columns($col) .
					$this->_tables($table) .
					$this->_where($clause) .
					$this->_group($group) .
					') ' .
					$this->_alias();

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		$return = $result->completeCell();
		return $return === false ? $return : (int) $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE SPECIAL FUNCTION TO COUNT A PARITCULAR COLUMN
	////////////////////////////////////////////////////////////////////////////
	public static function _count($column='*') {
		if ($column === false) $column = '*';
		return new pudlCount($column);
	}

}
