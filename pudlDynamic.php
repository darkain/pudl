<?php


trait pudlDynamic {


	static function dynamic_binary($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('BINARY'), $length)
		);
	}


	static function dynamic_char($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('CHAR'), $length)
		);
	}


	static function dynamic_date($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('DATE'), $length)
		);
	}


	static function dynamic_datetime($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('DATETIME'), $length)
		);
	}


	static function dynamic_decimal($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('DECIMAL'), $length)
		);
	}


	static function dynamic_double($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('DOUBLE'), $length)
		);
	}


	static function dynamic_integer($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('INTEGER'), $length)
		);
	}


	static function dynamic_signed($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('SIGNED'), $length)
		);
	}


	static function dynamic_time($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('TIME'), $length)
		);
	}


	static function dynamic_unsigned($blob, $column, $length=false) {
		return pudl::column_get(
			pudl::column($blob),
			new pudlAs($column, pudl::raw('UNSIGNED'), $length)
		);
	}


}
