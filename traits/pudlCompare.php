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
	public static function likeLeft(	$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_START); }
	public static function likeRight(	$v1=false,	$v2=false)	{ return new pudlLike(		$v1, $v2, PUDL_END  ); }
	public static function notLike(		$v1=false,	$v2=false)	{ return self::like(		$v1, $v2)->not(); }
	public static function notLikeLeft(	$v1=false,	$v2=false)	{ return self::likeLeft(	$v1, $v2)->not(); }
	public static function notLikeRight($v1=false,	$v2=false)	{ return self::likeRight(	$v1, $v2)->not(); }
	public static function notBetween(	$v1, $v2,	$v3=false)	{ return self::between(		$v1, $v2, $v3)->not(); }

	public static function asc( $column=false)	{ return new pudlSort('ASC',  $column); }
	public static function dsc( $column=false)	{ return new pudlSort('DESC', $column); }
	public static function desc($column=false)	{ return new pudlSort('DESC', $column); }


	public static function regexp($value) {
		if (func_num_args() === 1) return new pudlRegexp($value);
		return (new ReflectionClass('pudlRegexp'))->newInstanceArgs(func_get_args());
	}

	public static function notRegexp($value) {
		$regexp = call_user_func_array([self,'regexp'], func_get_args());
		return $regexp->not();
	}


	//NOTE: this function is experimental and will most likely change syntax!
	public static function reglike($column, $like, $regexp, $replace='') {
		return self::column(
			self::regexp_replace(self::column($column), $regexp, $replace),
			self::like($like)
		);
	}



	//Compare floats
	public static function fleq($value, $precision=10) {
		return new pudlFloat($value, $precision);
	}



	public static function inSet($value) {
		if (pudl_array($value)  &&  func_num_args() === 1)
			return new pudlSet($value);

		if ($value instanceof pudlResult)
			return new pudlSet($value->rows());

		return new pudlSet(func_get_args());
	}



	public static function notInSet($value) {
		if (pudl_array($value)  &&  func_num_args() === 1)
			return (new pudlSet($value))->not();

		if ($value instanceof pudlResult)
			return (new pudlSet($value->rows()))->not();

		return (new pudlSet(func_get_args()))->not();
	}



	public static function _and($clause) {
		return new pudlAnd($clause);
	}



	public static function _or($clause) {
		return new pudlOr($clause);
	}

}
