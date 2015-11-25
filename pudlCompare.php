<?php


trait pudlCompare {


	public static function between($low, $high)	{ return new pudlBetween($low, $high ); }
	public static function eq($value)			{ return new pudlEquals($value, '='  ); }
	public static function neq($value)			{ return new pudlEquals($value, '!=' ); }
	public static function nulleq($value)		{ return new pudlEquals($value, '<=>'); }
	public static function lt($value)			{ return new pudlEquals($value, '<'  ); }
	public static function lteq($value)			{ return new pudlEquals($value, '<=' ); }
	public static function gt($value)			{ return new pudlEquals($value, '>'  ); }
	public static function gteq($value)			{ return new pudlEquals($value, '>=' ); }
	public static function appendSet($value)	{ return new pudlAppendSet($value); }
	public static function removeSet($value)	{ return new pudlRemoveSet($value); }
	public static function like($value)			{ return new pudlLike($value, PUDL_BOTH ); }
	public static function likeLeft($value)		{ return new pudlLike($value, PUDL_START); }
	public static function likeRight($value)	{ return new pudlLike($value, PUDL_END  ); }
	public static function regexp($value)		{ return new pudlRegexp($value); }
	public static function notLike($value)		{ return self::like($value)->not(); }
	public static function notLikeLeft($value)	{ return self::likeLeft($value)->not(); }
	public static function notLikeRight($value)	{ return self::likeRight($value)->not(); }
	public static function notRegexp($value)	{ return self::pudlRegexp($value)->not(); }



	public static function inSet($value) {
		if (is_array($value)  &&  func_num_args() === 1) {
			return new pudlSet($value);
		} else if ($value instanceof pudlResult) {
			return new pudlSet($value->rows());
		} else {
			return new pudlSet(func_get_args());
		}
	}



	public static function notInSet($value) {
		if (is_array($value)  &&  func_num_args() === 1) {
			return (new pudlSet($value))->not();
		} else if ($value instanceof pudlResult) {
			return (new pudlSet($value->rows()))->not();
		} else {
			return (new pudlSet(func_get_args()))->not();
		}
	}

}
