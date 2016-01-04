<?php


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

		$query  = $this->_union($type);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this($query);
	}



	public function unionGroup($group=false, $order=false, $limit=false, $offset=false, $type='') {
		if (!$this->inUnion()) return false;

		$query  = 'SELECT ';
		$query .= $this->_cache();
		$query .= '* FROM (';
		$query .= $this->_union($type);
		$query .= ') pudltablealias';
		$query .= $this->_group($group);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this($query);
	}

}
