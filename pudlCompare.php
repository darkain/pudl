<?php


trait pudlCompare {


	public static function between($low, $high)			{ return new pudlBetween($low, $high ); }
	public static function appendSet($value)			{ return new pudlAppendSet($value); }
	public static function removeSet($value)			{ return new pudlRemoveSet($value); }
	public static function eq($value=false)				{ return new pudlEquals($value, '='  ); }
	public static function neq($value=false)			{ return new pudlEquals($value, '!=' ); }
	public static function nulleq($value=false)			{ return new pudlEquals($value, '<=>'); }
	public static function lt($value=false)				{ return new pudlEquals($value, '<'  ); }
	public static function lteq($value=false)			{ return new pudlEquals($value, '<=' ); }
	public static function gt($value=false)				{ return new pudlEquals($value, '>'  ); }
	public static function gteq($value=false)			{ return new pudlEquals($value, '>=' ); }
	public static function like($value=false)			{ return new pudlLike($value, PUDL_BOTH ); }
	public static function likeLeft($value=false)		{ return new pudlLike($value, PUDL_START); }
	public static function likeRight($value=false)		{ return new pudlLike($value, PUDL_END  ); }
	public static function regexp($value=false)			{ return new pudlRegexp($value); }
	public static function notLike($value=false)		{ return self::like($value)->not(); }
	public static function notLikeLeft($value=false)	{ return self::likeLeft($value)->not(); }
	public static function notLikeRight($value=false)	{ return self::likeRight($value)->not(); }
	public static function notRegexp($value=false)		{ return self::pudlRegexp($value)->not(); }



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
