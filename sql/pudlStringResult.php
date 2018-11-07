<?php


class pudlStringResult extends pudlResult implements pudlValue, pudlHelper {


	public function __construct(pudl $pudl, $type) {
		parent::__construct($pudl);
		$this->type = $type;
	}


	public function __toString()		{ return $this->query; }

	public function pudlValue(pudl $pudl, $quote=true) {
		return $this->query;
	}

	public function free()				{ return false; }
	public function count()				{ return 1; }
	public function fields()			{ return ['QUERY']; }
	public function getField($column)	{ return false; }
	public function error()				{ return false; }
	public function seek($row)			{}


	public function cell($row=0, $column=0) {
		if ($row  ||  $column) return false;
		return $this->query;
	}


	public function row() {
		if ($this->returned) return false;
		$this->returned = true;
		return $this->query;
	}


	public function rows() {
		if ($this->returned) return false;
		$this->returned = true;
		return $this->query;
	}


	private	$returned	= false;
	public	$type		= false;
}
