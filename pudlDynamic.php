<?php


trait pudlDynamic {


	public static function json($column) {
		return self::column_json( self::column($column) );
	}



	public static function dynamic($column, $length=false) {
		$parts = explode(':', $column);

		if (count($parts) !== 2) trigger_error(
			'Wrong column format for pudl::dynamic', E_USER_ERROR
		);

		switch (strtoupper($parts[1])) {
			case 'B': case 'BIN': case 'BINARY':
				$format = 'BINARY';
			break;

			case 'C': case 'CHAR': case 'S': case 'STR': case 'STRING':
				$format = 'CHAR';
			break;

			case 'D': case 'DATE':
				$format = 'DATE';
			break;

			case 'DT': case 'DATETIME':
				$format = 'DATETIME';
			break;

			case 'N': case 'NUMBER': case 'DECIMAL':
				$format = 'DECIMAL';
			break;

			case 'F': case 'FLOAT': case 'DOUBLE':
				$format = 'DOUBLE';
			break;

			case 'I': case 'INT': case 'INTEGER':
				$format = 'INTEGER';
			break;

			case 'S': case 'SINT': case 'SIGNED':
				$format = 'SIGNED';
			break;

			case 'T': case 'TIME':
				$format = 'TIME';
			break;

			case 'U': case 'UINT': case 'UNSIGNED':
				$format = 'UNSIGNED';
			break;

			default: trigger_error(
				'Wrong column data type for pudl::dynamic', E_USER_ERROR
			);
		}

		$items = explode('.', $parts[0]);
		if (count($items) < 2) trigger_error(
			'Wrong column format for pudl::dynamic', E_USER_ERROR
		);

		foreach ($items as $test) if (!strlen($test)) trigger_error(
			'Wrong column name for pudl::dynamic', E_USER_ERROR
		);

		$return = self::column_get(
			self::column( array_shift($items) ),
			new pudlAs(array_shift($items), self::raw( count($items)?'BINARY':$format ), $length)
		);

		while (count($items)) {
			$return = self::column_get(
				self::column( $return ),
				new pudlAs(array_shift($items), self::raw( count($items)?'BINARY':$format ), $length)
			);
		}

		return $return;
	}



	public static function dynamic_binary($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('BINARY'), $length)
		);
	}


	public static function dynamic_char($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('CHAR'), $length)
		);
	}


	public static function dynamic_date($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('DATE'), $length)
		);
	}


	public static function dynamic_datetime($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('DATETIME'), $length)
		);
	}


	public static function dynamic_decimal($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('DECIMAL'), $length)
		);
	}


	public static function dynamic_double($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('DOUBLE'), $length)
		);
	}


	public static function dynamic_integer($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('INTEGER'), $length)
		);
	}


	public static function dynamic_signed($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('SIGNED'), $length)
		);
	}


	public static function dynamic_time($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('TIME'), $length)
		);
	}


	public static function dynamic_unsigned($blob, $column, $length=false) {
		return self::column_get(
			self::column($blob),
			new pudlAs($column, self::raw('UNSIGNED'), $length)
		);
	}


}
