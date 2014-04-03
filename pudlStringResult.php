<?php


require_once('pudlResult.php');


class pudlStringResult extends pudlResult {

	public function __construct($query) {
		parent::__construct(false, $query);
	}

	public function __destruct() {}

	public function free() { return false; }
	public function cell($row=0, $column=0) { return false; }
	public function count() { return 0; }
	public function fields() { return 0; }
	public function getField($column) { return array();	}
	public function row($type='ARRAY') { return false; }
	public function error() { return false; }

}
