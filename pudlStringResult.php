<?php


require_once('pudlResult.php');


class pudlStringResult extends pudlResult {

	public function __construct($db) {
		parent::__construct(false, $db);
	}

	public function __destruct() {}

	public function free()						{ return false; }
	public function cell($row=0, $column=0)		{ return $this->query; }
	public function count()						{ return $this->query; }
	public function fields()					{ return $this->query; }
	public function getField($column)			{ return $this->query; }
	public function seek($row)					{ return $this->returned=false; }
	public function error()						{ return false; }

	public function row($type=PUDL_ARRAY) {
		if ($this->returned) return false;
		$this->returned = true;
		return $this->query;
	}

	public function rows($type=PUDL_ARRAY) {
		if ($this->returned) return false;
		$this->returned = true;
		return $this->query;
	}


	private $returned = false;
}
