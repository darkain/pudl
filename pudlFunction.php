<?php

class pudlFunction {

	public static function __callStatic($name, $arguments) {
		$value = new pudlFunction();

		$name = '_' . strtoupper($name);
		$value->$name = $arguments;

		return $value;
	}


	public static function timestamp() {
		global $db;
		return self::from_unixtime($db->time());
	}

}
