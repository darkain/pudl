<?php


trait pudlTransaction {


	////////////////////////////////////////////////////////////////////////////
	// CHECK IF WE'RE CURRENTLY INSIDE OF A TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	public function inTransaction() {
		return is_array($this->transaction);
	}




	////////////////////////////////////////////////////////////////////////////
	// START A NEW TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	public function begin() {
		if ($this->inTransaction()) return $this;
		$this->transaction	= [];
		$this->_inserted	= 0;
		$this->_transtime	= time();
		$this->_begin();
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// INTERNAL METHOD FOR STARTING THE TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	protected function _begin() {
		return $this('BEGIN');
	}




	////////////////////////////////////////////////////////////////////////////
	// COMMIT THE CURRENT TRANSACTION
	/** @suppress PhanUndeclaredMethod */
	////////////////////////////////////////////////////////////////////////////
	public function commit($sync=false) {
		if (!$this->inTransaction()) return $this;
		$this->_commit();
		$this->transaction = false;
		return $sync ? $this->sync() : $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// INTERNAL METHOD FOR COMMITTING A TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	protected function _commit() {
		return $this('COMMIT');
	}




	////////////////////////////////////////////////////////////////////////////
	// COMMIT A TRANSACTION AND START A NEW ONE AFTER CHUNK SIZE
	////////////////////////////////////////////////////////////////////////////
	public function chunk($size=1000, $sync=false, $time=0) {
		if (!$this->inTransaction()) return $this;

		$time = (int) $time;

		switch (true) {
			case (++$this->_inserted % $size === 0):
			case ($time  &&  (time() - $this->_transtime > $time)):
				$this->commit($sync)->begin();
			break;
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE A SAVE POINT OR CHECK POINT FOR CURRENT TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	public function savepoint($savepoint) {
		if ($this->inTransaction()) {
			$this('SAVEPOINT ' . $this->identifier($savepoint));
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// RELEASE A SAVE POINT OR CHECK POINT FOR CURRENT TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	public function release($savepoint) {
		if ($this->inTransaction()) {
			$this('RELEASE SAVEPOINT ' . $this->identifier($savepoint));
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// ROLLBACK THE CURRENT TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	public function rollback($savepoint=NULL) {
		if (!$this->inTransaction()) return $this;

		if (is_null($savepoint)) {
			$this->transaction = false;
		}

		$this->_rollback($savepoint);

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// INTERNAL METHOD FOR ROLLING BACK THE CURRENT TRANSACTION
	////////////////////////////////////////////////////////////////////////////
	protected function _rollback($savepoint=NULL) {
		return !is_null($savepoint)
			? ($this('ROLLBACK TO SAVEPOINT ' . $this->identifier($savepoint)))
			: ($this('ROLLBACK'));
	}




	////////////////////////////////////////////////////////////////////////////
	// PLAYBACK A FAILED TRANSACTION
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// LOCK THE GIVEN TABLES
	////////////////////////////////////////////////////////////////////////////
	public function lock($table) {
		$query = 'LOCK TABLES ';

		if (pudl_array($table)) {
			$set = [];

			if (isset($table['read'])) {
				/** @phan-suppress-next-line PhanUndeclaredMethod */
				$item = $this->_lockTable($table['read'], 'READ');
				if (!empty($item)) $set[] = $item;
				unset($table['read']);
			}

			if (isset($table['write'])) {
				/** @phan-suppress-next-line PhanUndeclaredMethod */
				$item = $this->_lockTable($table['write'], 'WRITE');
				if (!empty($item)) $set[] = $item;
				unset($table['write']);
			}

			/** @phan-suppress-next-line PhanUndeclaredMethod */
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




	////////////////////////////////////////////////////////////////////////////
	// UNLOCK PREVIOUSLY LOCKED TABLES
	////////////////////////////////////////////////////////////////////////////
	public function unlock() {
		if (!$this->locked) return $this;
		$this('UNLOCK TABLES');
		$this->locked = false;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF INSERTED ROWS
	////////////////////////////////////////////////////////////////////////////
	public function inserted() {
		return $this->_inserted;
	}




	////////////////////////////////////////////////////////////////////////////
	// LOCAL MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var array|false */		protected		$transaction	= false;
	/** @var bool */			private			$locked			= false;
	/** @var int */				private			$_transtime		= 0;
	/** @var int */				private			$_inserted		= 0;

}
