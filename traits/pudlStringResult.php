<?php


class pudlStringResult extends pudlResult {
	use pudlHelper;


	public function __construct($db, $type) {
		parent::__construct(false, $db);
		$this->type = $type;
	}


	public function __toString()		{ return $this->query; }


	public function free()				{ return false; }
	public function count()				{ return 1; }
	public function fields()			{ return ['QUERY']; }
	public function getField($column)	{ return false; }
	public function error()				{ return false; }


	public function seek($row) {
		if ($row) return false;
		return !($this->returned = false);
	}


	public function cell($row=0, $column=0) {
		if ($row  ||  $column) return false;
		return $this->query;
	}


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


	private	$returned	= false;
	public	$type		= false;
}
