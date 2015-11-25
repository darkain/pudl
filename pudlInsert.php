<?php


trait pudlInsert {


	abstract public function insertId();



	public function insert($table, $data, $update=false, $prefix=true) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insert', E_USER_ERROR);
			return false;
		}

		$cols	= ' (';
		$vals	= '';
		$first	= true;
		foreach ($data as $column => $value) {
			if (!$first) {
				$cols .= ', ';
				$vals .= ', ';
			} else $first = false;

			$cols .= $this->_table($column, false);
			$vals .= $this->_columnData($value);
		}

		if ($prefix) $cols .= ')'; else $cols = '';

		$table = $this->_table($table);
		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table$cols VALUES ($vals)";
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table$cols VALUES ($vals)";
		} else {
			$query = "INSERT INTO $table$cols VALUES ($vals)";
			if ($update === true) $update = $data;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update);
			}
		}

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		return $this->insertId();
	}



	public function insertValues($table, $data, $update=false) {
		return $this->insert($table, $data, $update, false);
	}



	public function insertIgnore($table, $data) {
		return $this->insert($table, $data, "IGNORE");
	}



	public function replace($table, $data) {
		return $this->insert($table, $data, "REPLACE");
	}



	public function insertUpdate($table, $data, $column, $update=false, $prefix=true) {
		if (empty($update)) {
			$update = [];
		} else if ($update === true  &&  is_array($data)) {
			$update = $data;
		} else if ($update === true) {
			$update = [$data];
		}

		$update[] = $this->_table($column,false).'=LAST_INSERT_ID('.$this->_table($column,false).')';

		return $this->insert($table, $data, $update, $prefix);
	}



	public function insertEx($table, $cols, $data, $update=false) {
		if (!is_array($data)  &&  !is_object($data)) {
			trigger_error('Invalid data type for pudl::insertEx', E_USER_ERROR);
			return false;
		}

		$table = $this->_table($table);

		$query = '';

		foreach ($cols as &$name) {
			if (strlen($query)) $query .= ',';
			$query .= $this->_table($name, false);
		} unst($name);

		$query .= ') VALUES ';

		$first = true;
		foreach ($data as &$set) {
			if (!$first) $query .= ',';
			$first = false;
			$query .= '(';

			$firstitem = true;
			foreach ($set as &$item) {
				if (!firstitem) $query .= ',';
				$firstitem = false;
				$query .= "'$item'";
			} unset($item);

			$query .= ')';
		} unset($set);

		if ($update === 'IGNORE') {
			$query = "INSERT IGNORE INTO $table (" . $query;
		} else if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table (" . $query;
		} else {
			$query = "INSERT INTO $table (" . $query;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update);
			}
		}

		$this($query);
		return $this->insertId();
	}



	public function replaceEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, "REPLACE");
	}



	public function insertIgnoreEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, "IGNORE");
	}

}
