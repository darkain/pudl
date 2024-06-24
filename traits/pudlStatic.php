<?php


////////////////////////////////////////////////////////////////////////////////
// STATIC API CALLS
////////////////////////////////////////////////////////////////////////////////
trait pudlStatic {




	////////////////////////////////////////////////////////////////////////////
	// UNKNOWN METHODS
	////////////////////////////////////////////////////////////////////////////
	public function __call($name, $arguments) {
		switch ($name) {
			case 'count':
				return call_user_func_array([$this, 'total'], $arguments);

			case 'increment':
				return call_user_func_array([$this, 'increase'], $arguments);

			case 'set':
				return call_user_func_array([$this, 'variable'], $arguments);
		}

		throw new pudlMethodException($this,
			'Invalid method call: ' . get_class($this) . '->' . $name . '()'
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// UNKNOWN STATIC METHODS ARE CONVERTED INTO PUDLFUNCTION CALLS
	////////////////////////////////////////////////////////////////////////////
	public static function __callStatic($name, $arguments) {
		switch ($name) {
			case 'count':
				return static::_count(reset($arguments));

			case 'increment':
				if (empty($arguments)) $arguments = [1];
				return static::_increment(reset($arguments));

			case 'set':
				return forward_static_call_array(
					[self::class, '_set'],
					$arguments
				);
		}

		$value	= new pudlFunction();
		$name	= '_' . strtoupper($name);
		$value->$name = $arguments;
		return $value;
	}




	////////////////////////////////////////////////////////////////////////////
	// SAFELY PASS A COLUMN INTO A QUERY
	////////////////////////////////////////////////////////////////////////////
	public static function column($column, $value=false) {
		return (func_num_args() < 2)
			? new pudlColumn($column)
			: new pudlColumn($column, $value);
	}




	////////////////////////////////////////////////////////////////////////////
	// COMPARE A VALUE AGAINST MULTIPLE COLUMNS
	////////////////////////////////////////////////////////////////////////////
	public static function bravo($value, $columns /* ... */) {
		if (!pudl_array($columns)) {
			$columns = func_get_args();
			array_shift($columns);
		}

		foreach ($columns as &$column) {
			if (!is_string($column)) continue;
			$column = static::column($column);
		}

		return new pudlEquals($value, $columns, ' IN ');
	}




	////////////////////////////////////////////////////////////////////////////
	// UNSAFE - PASS RAW SQL INTO A QUERY - USE CAUTION WITH THIS METHOD!
	////////////////////////////////////////////////////////////////////////////
	public static function raw(/* ...$values */) {
		return (new ReflectionClass('pudlRaw'))
				->newInstanceArgs(func_get_args());
	}




	////////////////////////////////////////////////////////////////////////////
	// FORCE DATATYPE INTO STRING WHEN INSERTING INTO SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	public static function text(/* ...$values */) {
		return (new ReflectionClass('pudlText'))
				->newInstanceArgs(func_get_args());
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT A UNIX TIMESTAMP INTO A DATETIME
	////////////////////////////////////////////////////////////////////////////
	public static function date($timestamp=false) {
		return ($timestamp === false)
			? static::now()
			: static::from_unixtime($timestamp);
	}




	////////////////////////////////////////////////////////////////////////////
	// HELPER FUNCTION FOR DATE RANGES FROM UNIX TIMESTAMPS
	////////////////////////////////////////////////////////////////////////////
	public static function daterange($begin, $end) {
		return static::between(static::date($begin), static::date($end));
	}




	////////////////////////////////////////////////////////////////////////////
	// FIND IN SET
	////////////////////////////////////////////////////////////////////////////
	public static function find($column, $values) {
		if (!pudl_array($values)) $values = explode(',', $values);
		$return = [];
		foreach ($values as $item) {
			$return[] = static::find_in_set($item, static::column($column));
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// -NOT- FIND IN SET
	////////////////////////////////////////////////////////////////////////////
	public static function notFind($column, $values) {
		if (!pudl_array($values)) $values = explode(',', $values);
		$return = [];
		foreach ($values as $item) {
			$return[] = static::{'!find_in_set'}($item, static::column($column));
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// EXTRACT KEYS FROM A GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public static function extract($array, $keys) {
		if ($array instanceof pudlObject) {
			$array = $array->raw();
		} else if ($array instanceof pudlResult) {
			$array = $array->rows();
		}

		$return = [];
		if (!pudl_array($keys)) {
			$keys = func_get_args();
			array_shift($keys);
		}

		foreach ($keys as $key => $value) {
			if (!is_string($key)) $key = $value;
			if (!array_key_exists($key, $array)) continue;
			$return[$key] = $array[$key];
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// TABLE COLUMN SET DATA TYPE
	////////////////////////////////////////////////////////////////////////////
	public static function _set($values) {
		if (!pudl_array($values)) $values = func_get_args();
		return new pudlType('SET', $values);
	}




	////////////////////////////////////////////////////////////////////////////
	// TABLE COLUMN ENUM DATA TYPE
	////////////////////////////////////////////////////////////////////////////
	public static function enum($values) {
		if (!pudl_array($values)) $values = func_get_args();
		return new pudlType('ENUM', $values);
	}


}
