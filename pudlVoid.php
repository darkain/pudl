<?php

class pudlVoid {
	public function __call($name, $arguments) {
		return false;
	}
}
