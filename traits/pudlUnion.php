<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlUnion {



	////////////////////////////////////////////////////////////////////////////
	// CHECK TO SEE IF WE'RE CURRENTLY IN THE MIDDLE OF A UNION GENERATOR
	////////////////////////////////////////////////////////////////////////////
	public function inUnion() {
		return is_array($this->union);
	}




	////////////////////////////////////////////////////////////////////////////
	// START THE UNION GENERATOR
	////////////////////////////////////////////////////////////////////////////
	public function unionStart() {
		if ($this->inUnion()) return false;
		$this->union = [];
		return true;
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE UNION GENERATOR AND EXECUTE IT
	////////////////////////////////////////////////////////////////////////////
	public function unionEnd($order=false, $limit=false, $offset=false, $type='') {
		$query =	$this->_union($type) .
					$this->_order($order) .
					$this->_limit($limit, $offset);

		$this->union = NULL;

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE UNION GENERATOR AND EXECUTE IT, RETURNING DATA INSTEAD OF RESULT
	////////////////////////////////////////////////////////////////////////////
	public function unionComplete($order=false, $limit=false, $offset=false, $type='') {
		return $this->unionEnd($order, $limit, $offset, $type)->complete();
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE UNION "ALL" GENERATOR AND EXECUTE IT
	////////////////////////////////////////////////////////////////////////////
	public function unionAll($order=false, $limit=false, $offset=false) {
		return $this->unionEnd($order, $limit, $offset, 'ALL');
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE UNION "DISTINCT" GENERATOR AND EXECUTE IT
	////////////////////////////////////////////////////////////////////////////
	public function unionDistinct($order=false, $limit=false, $offset=false) {
		return $this->unionEnd($order, $limit, $offset, 'DISTINCT');
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE UNION "GROUP BY" GENERATOR AND EXECUTE IT
	////////////////////////////////////////////////////////////////////////////
	public function unionGroup($group=false, $order=false, $limit=false, $offset=false, $type='') {

		$query =	'SELECT ' .
					$this->_cache() .
					'* FROM (' .
					$this->_union($type) .
					') ' .
					$this->_alias() .
					$this->_group($group) .
					$this->_order($order) .
					$this->_limit($limit, $offset);

		$this->union = NULL;

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UNION SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _union($type='') {
		if ($this->union === NULL) {
			throw new pudlMethodException($this,
				'Invalid call to ' . __METHOD__
			);
		}

		$type = strtoupper($type);

		if ($type !== 'ALL'  &&  $type !== 'DISTINCT') $type = '';

		$union = ') UNION ' . (empty($type) ? '(' : ($type.' ('));

		return '(' . implode($union, $this->union) . ')';
	}




	/** @var ?array */
	protected $union = NULL;

}
