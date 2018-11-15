<?php


class		pudlPdoResult
	extends	pudlResult {




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - FREE RESOURCES
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		parent::__destruct();
		$this->free();
	}





	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	// http://php.net/manual/en/pdostatement.closecursor.php
	////////////////////////////////////////////////////////////////////////////
	public function free() {
		if (!$this->result) return false;
		$return = $this->result->closeCursor();
		$this->result = false;
		return $return;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	// http://php.net/manual/en/pdostatement.fetch.php
	////////////////////////////////////////////////////////////////////////////
	public function cell($row=0, $column=0) {
		if (!is_object($this->result)) return false;

		$data = $this->result->fetch(PDO::FETCH_BOTH, PDO::FETCH_ORI_ABS, $row);

		return (pudl_array($data)  &&  array_key_exists($column, $data))
			? $data[$column] : false;
	}





	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	// http://php.net/manual/en/pdostatement.rowcount.php
	////////////////////////////////////////////////////////////////////////////
	public function count() {
		if (!is_object($this->result)) return 0;
		return $this->result->rowCount();
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	// http://php.net/manual/en/pdostatement.columncount.php
	////////////////////////////////////////////////////////////////////////////
	public function fields() {
		if (!is_object($this->result)) return 0;
		return $this->result->columnCount();
	}





	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	// http://php.net/manual/en/pdostatement.getcolumnmeta.php
	////////////////////////////////////////////////////////////////////////////
	public function getField($column) {
		if (!is_object($this->result)) return [];
		return $this->result->getColumnMeta($column);
	}





	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	// http://php.net/manual/en/pdostatement.fetch.php
	////////////////////////////////////////////////////////////////////////////
	public function seek($row) {
		if (!is_object($this->result)) return;
		$this->result->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $row);
		if (!$row) $this->seekzero = true;
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	// http://php.net/manual/en/pdostatement.fetch.php
	////////////////////////////////////////////////////////////////////////////
	public function row() {
		if (!is_object($this->result)) return false;

		$seek = $this->seekzero ? 0 : 1;
		$this->seekzero = false;

		$this->data = $this->result->fetch(
			PDO::FETCH_ASSOC,
			PDO::FETCH_ORI_REL,
			$seek
		);

		if ($this->data !== false) {
			$this->row	= ($this->row !== false)
						? ($this->row + 1)
						: 0;
		}

		return $this->data;
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR CODE FOR THIS RESULT - 0, FALSE, NULL ALL MEAN NO ERROR
	// http://php.net/manual/en/pdostatement.errorcode.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!is_object($this->result)) return 0;
		return $this->result->errorCode();
	}





	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR MESSAGE FOR THIS RESULT
	// http://php.net/manual/en/pdostatement.errorinfo.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!is_object($this->result)) return '';
		return $this->result->errorInfo();
	}





	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $seekzero = false;
}
