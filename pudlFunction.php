<?php

class pudlFunction {

	static function __callStatic($name, $arguments) {
		$value = new pudlFunction();

		$name = '_' . $name;
		$value->$name = $arguments;

		return $value;
	}

}
