<?php


trait pudlDelete {


	public function delete($table, $clause, $limit=false, $offset=false) {
		$query  = 'DELETE ';
		$query .= 'FROM ';
		$query .= $this->_table($table);
		$query .= $this->_clause($clause);
		$query .= $this->_limit($limit, $offset);
		return $this($query);
	}


	public function deleteId($table, $column, $id, $limit=false, $offset=false) {
		return $this->delete($table, $this->_clauseId($column,$id), $limit, $offset);
	}

}
