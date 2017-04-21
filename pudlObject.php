<?php


class pudlObject implements ArrayAccess, Iterator {

	public function __construct(&$array=NULL, $clone=false) {
		if (!is_array($array)  &&  !is_a($array, 'Traversable')) return;
		$clone ? $this->_clone($array) : $this->_replace($array);
	}


	public function _clear() {
		$this->_object_array	= [];
		$this->_snapshot		= false;
	}


	public function _replace(&$array) {
		$this->_clear();
		if (is_a($array, 'Traversable')) return $this->_clone($array);
		$this->_object_array = &$array;
	}


	public function _clone($array) {
		$this->_clear();
		if (!tbx_array($array)) return;
		foreach($array as $key => $value) {
			$this->_object_array[$key]	= $value;
		}
	}


	public function _merge($array) {
		if (empty($array)) return;
		foreach($array as $key => $value) {
			$this->_object_array[$key]	= $value;
		}
	}


	public function _mergeInto(&$array) {
		if (empty($array)) return;
		foreach($this->_object_array as $key => $value) {
			$array[$key]				= $value;
		}
	}


	public function _append($array) {
		if (empty($array)) return;
		foreach($array as $key => $value) {
			if (isset($this->_object_array[$key])) continue;
			$this->_object_array[$key]	= $value;
		}
	}


	public function _appendInto(&$array) {
		if (empty($array)) return;
		foreach($this->_object_array as $key => $value) {
			if (isset($array[$key])) continue;
			$array[$key]				= $value;
		}
	}


	public function &_get() {
		return $this->_object_array;
	}


	public function _count() {
		return count($this->_object_array);
	}


	public function _push() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_push', $args);
	}


	public function _pop() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_pop', $args);
	}


	public function _shift() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_shift', $args);
	}


	public function _unshift() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_unshift', $args);
	}


	public function _diff() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_diff', $args);
	}


	public function _diff_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_diff_assoc', $args);
	}


	public function _diff_key() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_diff_key', $args);
	}


	public function _intersect() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_intersect', $args);
	}


	public function _intersect_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_intersect_assoc', $args);
	}


	public function _intersect_key() {
		$args = func_get_args();
		array_unshift($args, $this->_object_array);
		return call_user_func_array('array_intersect_key', $args);
	}


	public function _keys($search_value=null, $strict=false) {
		return array_keys($this->_object_array, $search_value, $strict);
	}


	public function _slice($offset, $length=NULL, $preserve_keys=false) {
		return array_slice($this->_object_array, $offset, $length, $preserve_keys);
	}


	public function __set($key, $value) {
		$this->_object_array[$key]		= $value;
	}


	public function offsetSet($key, $value) {
		if (is_null($key)) {
			$this->_object_array[]		= $value;
		} else {
			$this->_object_array[$key]	= $value;
		}
	}


	public function &__get($key) {
		return $this->_object_array[$key];
	}


	public function &offsetGet($key) {
		return $this->_object_array[$key];
	}


	public function __isset($key) {
		return isset($this->_object_array[$key]);
	}


	public function offsetExists($key, $isset=true) {
		return $isset
			? isset($this->_object_array[$key])
			: array_key_exists($key, $this->_object_array);
	}


	public function __unset($key) {
		unset($this->_object_array[$key]);
	}


	public function offsetUnset($key) {
		unset($this->_object_array[$key]);
	}


	public function rewind() {
		reset($this->_object_array);
	}


	public function current() {
		return current($this->_object_array);
	}


	public function key() {
		return key($this->_object_array);
	}


	public function next() {
		return next($this->_object_array);
	}


	public function valid() {
		$key = key($this->_object_array);
		return ($key !== NULL && $key !== FALSE);
	}


	public function extract($keys) {
		$return = [];
		if (!is_array($keys)) $keys = func_get_args();
		foreach ($keys as $item) {
			$return[$item] = $this->_object_array[$item];
		}
		return $return;
	}


	public function snapshot($return=false) {
		if ($return) return $this->_snapshot;
		$this->_snapshot = $this->_object_array;
	}


	public function compare() {
		$return = [];
		if (!empty($this->_snapshot)) {
			foreach ($this->_object_array as $key => $value) {
				if (!array_key_exists($key, $this->_snapshot)) {
					$return[$key] = $value;
				} else if ($this->_snapshot[$key] !== $value) {
					$return[$key] = $value;
				}
			}
		}
		return $return;
	}


	public function __debugInfo() {
		return $this->_object_array;
	}


	private $_object_array	= [];
	private $_snapshot		= false;

}
