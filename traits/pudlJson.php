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
		return pudl::column(
			$column,
			pudl::json_set(
				pudl::ifnull(pudl::nullif(pudl::column($column),''), '{}'),
				'$.' . $field,
				$value
			)
		);
	}



	public static function jsonInsert($column, $field, $value) {
		return pudl::column(
			$column,
			pudl::json_insert(
				pudl::ifnull(pudl::nullif(pudl::column($column),''), '{}'),
				'$.' . $field,
				$value
			)
		);
	}



	public static function jsonReplace($column, $field, $value) {
		return pudl::column(
			$column,
			pudl::json_replace(
				pudl::ifnull(pudl::nullif(pudl::column($column),''), '{}'),
				'$.' . $field,
				$value
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


}
