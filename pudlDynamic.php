<?php


trait pudlDynamic {


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


}
