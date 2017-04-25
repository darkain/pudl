<?php


class pudlObject implements ArrayAccess, Iterator {

	public function __construct(&$array=NULL, $copy=false) {
		if (!is_array($array)  &&  !is_a($array, 'Traversable')) return;
		$copy ? $this->copy($array) : $this->replace($array);
	}


	public function clear() {
		$this->__array		= [];
		$this->__snapshot	= false;
	}


	public function replace(&$array) {
		$this->clear();
		if (is_a($array, 'Traversable')) return $this->copy($array);
		$this->__array = &$array;
	}


	public function copy($array) {
		$this->clear();
		if (!tbx_array($array)) return;
		foreach($array as $key => $value) {
			$this->__array[$key] = $value;
		}
	}


	public function merge($array) {
		if (empty($array)) return;
		foreach($array as $key => $value) {
			$this->__array[$key] = $value;
		}
	}


	public function mergeInto(&$array) {
		if (empty($array)) return;
		foreach($this->__array as $key => $value) {
			$array[$key] = $value;
		}
	}


	public function append($array) {
		if (empty($array)) return;
		foreach($array as $key => $value) {
			if (isset($this->__array[$key])) continue;
			$this->__array[$key] = $value;
		}
	}


	public function appendInto(&$array) {
		if (empty($array)) return;
		foreach($this->__array as $key => $value) {
			if (isset($array[$key])) continue;
			$array[$key] = $value;
		}
	}


	public function &raw() {
		return $this->__array;
	}


	public function count() {
		return count($this->__array);
	}


	public function push() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_push', $args);
	}


	public function pop() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_pop', $args);
	}


	public function shift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_shift', $args);
	}


	public function unshift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_unshift', $args);
	}


	public function diff() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff', $args);
	}


	public function diff_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_assoc', $args);
	}


	public function diff_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_key', $args);
	}


	public function intersect() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect', $args);
	}


	public function intersect_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_assoc', $args);
	}


	public function intersect_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_key', $args);
	}


	public function keys($search_value=null, $strict=false) {
		return array_keys($this->__array, $search_value, $strict);
	}


	public function slice($offset, $length=NULL, $preserve_keys=false) {
		return array_slice($this->__array, $offset, $length, $preserve_keys);
	}


	public function __set($key, $value) {
		$this->__array[$key]		= $value;
	}


	public function offsetSet($key, $value) {
		if (is_null($key)) {
			$this->__array[]		= $value;
		} else {
			$this->__array[$key]	= $value;
		}
	}


	public function &__get($key) {
		return $this->__array[$key];
	}


	public function &offsetGet($key) {
		return $this->__array[$key];
	}


	public function __isset($key) {
		return isset($this->__array[$key]);
	}


	public function offsetExists($key, $isset=true) {
		return $isset
			? isset($this->__array[$key])
			: array_key_exists($key, $this->__array);
	}


	public function __unset($key) {
		unset($this->__array[$key]);
	}


	public function offsetUnset($key) {
		unset($this->__array[$key]);
	}


	public function rewind() {
		reset($this->__array);
	}


	public function current() {
		return current($this->__array);
	}


	public function key() {
		return key($this->__array);
	}


	public function next() {
		return next($this->__array);
	}


	public function valid() {
		$key = key($this->__array);
		return ($key !== NULL && $key !== FALSE);
	}


	public function extract($keys) {
		$return = [];
		if (!is_array($keys)) $keys = func_get_args();
		foreach ($keys as $key) {
			$return[$key] = $this->__array[$key];
		}
		return $return;
	}


	public function extend($source, $keys) {
		if (!pudl_array($keys)) $keys = [$keys];
		foreach ($keys as $key) {
			$this->__array[$key] = $source[$key];
		}
	}


	public function snapshot($return=false) {
		if ($return) return $this->__snapshot;
		$this->__snapshot = $this->__array;
	}


	public function compare() {
		if (empty($this->__snapshot)) return [];
		return array_diff_assoc($this->__array, $this->__snapshot);
	}


	public function __debugInfo() {
		return $this->__array;
	}


	private $__array	= [];
	private $__snapshot	= false;

}
