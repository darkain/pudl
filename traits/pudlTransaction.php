<?php


trait pudlTransaction {


	public function inTransaction() {
		return is_array($this->transaction);
	}



	public function begin() {
		if ($this->inTransaction()) return $this;
		$this->transaction = [];
		$this->_inserted = 0;
		$this('START TRANSACTION');
		return $this;
	}



	public function commit($sync=false) {
		if (!$this->inTransaction()) return $this;
		$this('COMMIT');
		$this->transaction = false;
		return $sync ? $this->sync() : $this;
	}



	public function chunk($size=1000, $sync=false) {
		if (!$this->inTransaction()) return $this;
		return (++$this->_inserted % $size === 0)
			 ? $this->commit($sync)->begin()
			 : $this;
	}



	public function rollback() {
		if (!$this->inTransaction()) return $this;
		$this->transaction = false;
		$this('ROLLBACK');
		return $this;
	}



	protected function retryTransaction() {
		if (!$this->inTransaction()) return;

		$list = $this->transaction;
		$this->transaction = [];

		$return = false;
		foreach ($list as &$item) {
			$return = $this($item);
		} unset($item);

		return $return;
	}



	public function lock($table) {
		$query = 'LOCK TABLES ';

		if (pudl_array($table)) {
			$set = [];

			if (isset($table['read'])) {
				$item = $this->_lockTable($table['read'], 'READ');
				if (!empty($item)) $set[] = $item;
				unset($table['read']);
			}

			if (isset($table['write'])) {
				$item = $this->_lockTable($table['write'], 'WRITE');
				if (!empty($item)) $set[] = $item;
				unset($table['write']);
			}

			$item = $this->_lockTable($table, 'WRITE');
			if (!empty($item)) $set[] = $item;

			$query .= implode(', ', $set);
		} else {
			$query .= $table;
		}

		$this($query);
		$this->locked = true;

		return $this;
	}



	public function unlock() {
		if (!$this->locked) return $this;
		$this('UNLOCK TABLES');
		$this->locked = false;
		return $this;
	}



	public function inserted() {
		return $this->_inserted;
	}



	protected		$transaction	= false;
	private			$locked			= false;
	private			$_inserted		= 0;

}