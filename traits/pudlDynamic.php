<?php


trait pudlDynamic {


	protected function _dynamic($data) {
		static $depth = 0;
		if ($depth++ > 31) {
			throw new pudlException('Recursion limit reached');
			return '';
		}

		$query = '';
		foreach ($data as $property => $value) {
			if (strlen($query)) $query .= ', ';
			$query	.= $this->_value($property) . ','
					.  $this->_dynamic_create($value);
		}

		$depth--;
		return $query;
	}



	protected function _dynamic_create($value) {
		$new = $this->_value($value);
		if ($new !== false) return $new;

		if (is_array($value)  ||  is_object($value)) {
			if (empty($value)) return 'NULL';
			return 'COLUMN_CREATE(' . $this->_dynamic($value) . ')';
		}

		return $this->_invalidType($value, 'column');
	}



	public function dynamic_add($table, $column, $data, $clause) {
		return $this->update($table,
			$this->identifier($column)	. '=COLUMN_ADD(' .
			$this->identifier($column)	. ', ' .
			$this->_dynamic($data)		. ')',
		$clause);
	}



	public static function json($column) {
		return self::column_json( self::column($column) );
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



	protected static function dynamic_type($type, $die=true) {
		switch (strtoupper($type)) {
			case 'C': case 'CHAR': case 'S': case 'STR': case 'STRING':
				return 'CHAR';

			case 'I': case 'INT': case 'INTEGER':
				return 'INTEGER';

			case 'S': case 'SINT': case 'SIGNED':
				return 'SIGNED';

			case 'U': case 'UINT': case 'UNSIGNED':
				return 'UNSIGNED';

			case 'F': case 'FLOAT': case 'DOUBLE':
				return 'DOUBLE';

			case 'N': case 'NUMBER': case 'DECIMAL':
				return 'DECIMAL';

			case 'D': case 'DATE':
				return 'DATE';

			case 'T': case 'TIME':
				return 'TIME';

			case 'DT': case 'DATETIME':
				return 'DATETIME';

			case 'B': case 'BIN': case 'BINARY':
				return 'BINARY';
		}

		if ($die) throw new pudlException('Wrong dynamic column data type');

		return false;
	}
}
