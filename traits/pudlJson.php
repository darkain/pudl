<?php


'@phan-file-suppress PhanUndeclaredStaticMethod';
'@phan-file-suppress PhanUndeclaredStaticMethodInCallable';




////////////////////////////////////////////////////////////////////////////////
// JSON FUNCTIONS
// https://mariadb.com/kb/en/library/json-functions/
////////////////////////////////////////////////////////////////////////////////


trait pudlJson {


	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function json($column) {
		return ['JSON('.$column.')' => $column];
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function jsonSet($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_set'],
				static::_json_params($column, func_get_args())
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function jsonInsert($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_insert'],
				static::_json_params($column, func_get_args())
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function jsonReplace($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_replace'],
				static::_json_params($column, func_get_args())
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function jsonRemove($column, $field) {
		return static::column(
			$column,
			static::json_remove(
				static::_json_column($column),
				static::_json_path($field)
			)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	public static function jsonCompare($column, $field, $value) {
		return static::column(
			static::json_value(
				static::_json_column($column),
				static::_json_path($field)
			),
			$value
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	/** @suppress PhanUndeclaredMethod */
	////////////////////////////////////////////////////////////////////////////
	public function jsonUpdate($table, $column, $data, $clause) {
		return $this->update(
			$table,
			[static::jsonSet($column, $data)],
			$clause
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	/** @suppress PhanUndeclaredMethod */
	////////////////////////////////////////////////////////////////////////////
	public function jsonUpdateId($table, $column, $data, $col, $id=false) {
		return $this->updateId(
			$table,
			[static::jsonSet($column, $data)],
			$col, $id
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	protected static function _json_params($column, $args) {
		if (!is_string($column)) {
			throw new pudlException($this, 'Invalid JSON Column Name');
		}

		array_shift($args);
		$return	= [static::_json_column($column)];

		if ((count($args) === 1)  &&  isset($args[0])  &&  pudl_array($args[0])) {
			foreach ($args[0] as $key => $value) {
				$return[] = static::_json_path($key);
				$return[] = $value;
			}
			return $return;

		} else if ((count($args) > 1)  &&  (count($args) % 2 === 0)) {
			foreach ($args as $key => $value) {
				$return[]	= ($key % 2)
							? $value
							: static::_json_path($value);
			}
			return $return;
		}

		throw new pudlValueException($this, 'Invalid JSON Key/Value Pairs');
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	protected static function _json_column($column) {
		return static::ifnull(
			static::nullif(
				static::trim(static::column($column)),
				''
			),
			'{}'
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	protected static function _json_path($field) {
		switch (substr($field, 0, 1)) {
			case '$':	//TODO: THIS IS BAD
				return $field;

			case '[':
			case '{':
				return '$' . $field;
		}
		return '$.' . $field;
	}




	////////////////////////////////////////////////////////////////////////////
	// ??
	////////////////////////////////////////////////////////////////////////////
	protected static function jsonPathSafe($field) {
		return addcslashes(addcslashes($field, '*$[]{}.\\'), '\\');
	}




	////////////////////////////////////////////////////////////////////////////
	// CUSTOMIZED JSON ENCODER
	////////////////////////////////////////////////////////////////////////////
	public static function jsonEncode($data) {
		if ($data instanceof pudlObject) $data = $data->raw();

		return @json_encode(
			$data,
			JSON_HEX_APOS|JSON_HEX_QUOT|JSON_PARTIAL_OUTPUT_ON_ERROR
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// CUSTOMIZED JSON DECODER
	////////////////////////////////////////////////////////////////////////////
	public static function jsonDecode($data) {
		return @json_decode($data, true, 512, JSON_BIGINT_AS_STRING);
	}
}
