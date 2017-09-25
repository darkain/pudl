<?php


////////////////////////////////////////////////////////////////////////////////
// JSON FUNCTIONS
// https://mariadb.com/kb/en/library/json-functions/
////////////////////////////////////////////////////////////////////////////////


trait pudlJson {


	public static function json($column) {
		return ['JSON('.$column.')' => $column];
	}



	public static function jsonSet($column, $field, $value) {
		return static::column(
			$column,
			static::json_set(
				static::json_column($column),
				static::json_path($field),
				$value
			)
		);
	}



	public static function jsonInsert($column, $field, $value) {
		return static::column(
			$column,
			static::json_insert(
				static::json_column($column),
				static::json_path($field),
				$value
			)
		);
	}



	public static function jsonReplace($column, $field, $value) {
		return static::column(
			$column,
			static::json_replace(
				static::json_column($column),
				static::json_path($field),
				$value
			)
		);
	}



	public static function jsonRemove($column, $field) {
		return static::column(
			$column,
			static::json_remove(
				static::json_column($column),
				static::json_path($field)
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



	protected static function json_column($column) {
		return static::ifnull(
			static::nullif(
				static::trim(static::column($column)),
				''
			),
			'{"":0}'
			//TODO: this SHOULD be an empty JSON object
			//		MariaDB 10.2.8 and earlier won't accept this
			//		We'll switch it back to '{}' when MariaDB is fixed
		);
	}



	protected static function json_path($field) {
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
