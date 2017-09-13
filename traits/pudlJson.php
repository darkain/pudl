<?php


////////////////////////////////////////////////////////////////////////////////
// JSON FUNCTIONS
// https://mariadb.com/kb/en/library/json-functions/
////////////////////////////////////////////////////////////////////////////////


trait pudlJson {


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
