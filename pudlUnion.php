<?php


trait pudlUnion {


	public function unionStart() {
		if ($this->union !== false) return false;
		$this->union = array();
		return true;
	}



	public function unionEnd($order=false, $limit=false, $offset=false, $type='') {
		if (!is_array($this->union)) return false;

		$query  = $this->_union($type);
		$query .= $this->_order($order);
		$query .= $this->_limit($limit, $offset);
		//TODO: figure out how to convert this over to 'TOP' syntax

		$this->union = false;
		return $this($query);
	}



	public function unionGroup($group=false, $order=false, $limit=false, $offset=false, $type='') {
		if (!is_array($this->union)) return false;

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
