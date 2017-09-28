<?php


////////////////////////////////////////////////////////////////////////////////
// JSON FUNCTIONS
// https://mariadb.com/kb/en/library/json-functions/
////////////////////////////////////////////////////////////////////////////////


trait pudlJson {


	public static function json($column) {
		return ['JSON('.$column.')' => $column];
	}



	public static function jsonSet($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_set'],
				static::_json_params($column, func_get_args())
			)
		);
	}



	public static function jsonInsert($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_insert'],
				static::_json_params($column, func_get_args())
			)
		);
	}



	public static function jsonReplace($column /* ...$keys, $values */) {
		return static::column(
			$column,
			forward_static_call_array(
				['static', '_json_replace'],
				static::_json_params($column, func_get_args())
			)
		);
	}



	public static function jsonRemove($column, $field) {
		return static::column(
			$column,
			static::json_remove(
				static::_json_column($column),
				static::_json_path($field)
			)
		);
	}



	public function jsonUpdate($table, $column, $field, $value, $clause) {
		return $this->update(
			$table,
			[static::jsonSet($column, $field, $value)],
			$clause
		);
	}



	public function jsonUpdateId($table, $column, $field, $value, $col, $id) {
		return $this->updateId(
			$table,
			[static::jsonSet($column, $field, $value)],
			$col, $id
		);
	}



	protected static function _json_params($column, $args) {
		if (!is_string($column)) {
			throw new pudlException("Invalid JSON Column Name");
		}

		array_shift($args);
		$return	= [static::_json_column($column)];

		if ((count($args) === 1)  &&  pudl_array($args[0])) {
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

		throw new pudlException("Invalid JSON Key/Value Pairs");
	}



	protected static function _json_column($column) {
		return static::ifnull(
			static::nullif(
				static::trim(static::column($column)),
				''
			),
			pudl::raw("'{\"\":0}'")
			//TODO: this SHOULD be an empty JSON object
			//		MariaDB 10.2.8 and earlier won't accept this
			//		We'll switch it back to '{}' when MariaDB is fixed
		);
	}



	protected static function _json_path($field) {
		switch (substr($field, 0, 1)) {
			case '$':
				return $field;

			case '[':
			case '{':
				return '$' . $field;
		}
		return '$.' . $field;
	}


}
