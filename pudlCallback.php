<?php


trait pudlCallback {

	public function on($action, $callback) {
		if (!is_string($action)) {
			throw pudlException('Not a valid callback action');
		}

		if (!is_callable($callback)) {
			throw pudlException('Not a valid callback function');
		}

		$this->_callbacks[$action][] = $callback;
	}



	protected function trigger($action) {
		if (empty($this->_callbacks[$action])) return;
		$args = func_get_args();
		foreach ($this->_callbacks[$action] as $item) {
			call_user_func_array($item, $args);
		}
	}



	private static $_callbacks = [];

}
