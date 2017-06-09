<?php


trait pudlDelete {


	public function delete($table, $clause, $limit=false, $offset=false) {
		return $this(
			'DELETE FROM ' .
			$this->_table($table) .
			$this->_clause($clause) .
			$this->_limit($limit, $offset)
		);
	}



	public function deleteId($table, $column, $id=false, $limit=false, $offset=false) {
		return $this->delete($table, $this->_clauseId($column,$id), $limit, $offset);
	}



	public function deleteRow($table, $clause) {
		return $this->delete($table, $clause, 1);
	}


}
