<?php


////////////////////////////////////////////////////////////////////////////////
// JSON FUNCTIONS
// https://mariadb.com/kb/en/library/json-functions/
////////////////////////////////////////////////////////////////////////////////


trait pudlJson {


	public static function json($column) {
		return ['JSON('.$column.')' => $column];
	}



	static function jsonSet($column, $field, $value) {
		return pudl::column(
			$column,
			pudl::json_set(
				pudl::column($column),
				'$.' . $field,
				$value
			)
		);
	}


	static function jsonInsert($column, $field, $value) {
		return pudl::column(
			$column,
			pudl::json_insert(
				pudl::column($column),
				'$.' . $field,
				$value
			)
		);
	}


	static function jsonReplace($column, $field, $value) {
		return pudl::column(
			$column,
			pudl::json_replace(
				pudl::column($column),
				'$.' . $field,
				$value
			)
		);
	}


}
