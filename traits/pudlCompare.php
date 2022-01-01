<?php


trait pudlCompare {


	public static function between(		$v1, $v2,	$v3=false)	{ return new pudlBetween(	$v1, $v2, $v3); }
	public static function appendSet(	$value)					{ return new pudlAppendSet(	$value); }
	public static function removeSet(	$value)					{ return new pudlRemoveSet(	$value); }
	public static function eq(			$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '='  ); }
	public static function neq(			$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '!=' ); }
	public static function nulleq(		$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '<=>'); }
	public static function lt(			$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '<'  ); }
	public static function lteq(		$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '<=' ); }
	public static function gt(			$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '>'  ); }
	public static function gteq(		$v1=false,	$v2=false)	{ return new pudlEquals(	$v1, $v2, '>=' ); }
	public static function like(		$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_BOTH ); }
	public static function likeRaw(		$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_NONE ); }
	public static function likeLeft(	$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_START); }
	public static function likeRight(	$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_END  ); }
	public static function notLike(		$v1=false,	$v2=false)	{ return static::like(		$v1, $v2)->not(); }
	public static function notLikeLeft(	$v1=false,	$v2=false)	{ return static::likeLeft(	$v1, $v2)->not(); }
	public static function notLikeRight($v1=false,	$v2=false)	{ return static::likeRight(	$v1, $v2)->not(); }
	public static function notBetween(	$v1, $v2,	$v3=false)	{ return static::between(	$v1, $v2, $v3)->not(); }

	public static function asc( $column=false)	{ return new pudlSort('ASC',  $column); }
	public static function dsc( $column=false)	{ return new pudlSort('DESC', $column); }
	public static function desc($column=false)	{ return new pudlSort('DESC', $column); }


	public static function regexp($value) {
		if (func_num_args() === 1) return new pudlRegexp($value);
		return (new ReflectionClass('pudlRegexp'))->newInstanceArgs(func_get_args());
	}

	public static function notRegexp($value) {
		$regexp = forward_static_call_array([__CLASS__,'regexp'], func_get_args());
		return $regexp->not();
	}


	//NOTE: this function is experimental and will most likely change syntax!
	public static function reglike($column, $like, $regexp, $replace='') {
		return static::column(
			static::regexp_replace(static::column($column), $regexp, $replace),
			static::like($like)
		);
	}



	//Compare floats
	public static function fleq($value, $precision=10) {
		return new pudlFloat($value, $precision);
	}



	public static function _and($clause) {
		return new pudlAnd($clause);
	}



	public static function _or($clause) {
		return new pudlOr($clause);
	}

}
