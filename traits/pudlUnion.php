<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlUnion {


	public function inUnion() {
		return is_array($this->union);
	}



	public function unionStart() {
		if ($this->inUnion()) return false;
		$this->union = [];
		return true;
	}



	public function unionEnd($order=false, $limit=false, $offset=false, $type='') {
		if (!$this->inUnion()) return false;

		$query =	$this->_union($type) .
					$this->_order($order) .
					$this->_limit($limit, $offset);

		$this->union = NULL;

		return $this($query);
	}



	public function unionGroup($group=false, $order=false, $limit=false, $offset=false, $type='') {
		if (!$this->inUnion()) return false;

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




	protected function _union($type='') {
		if ($this->union === NULL) {
			throw new pudlMethodException($this, 'Invalid call to _union()');
		}
		$type = strtoupper($type);
		if ($type !== 'ALL'  &&  $type !== 'DISTINCT') $type = '';
		return '(' . implode(") UNION $type (", $this->union) . ')';
	}



	/** @var ?array */
	protected $union = NULL;

}
