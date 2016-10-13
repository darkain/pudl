<?php


trait pudlInsert {


	abstract public function insertId();



	public function insert($table, $data, $update=false, $prefix=true) {
		if ($data === false) $data = [];

		if (!is_array($data)  &&  !is_object($data)) {
			throw new pudlException('Invalid data type for pudl::insert');
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

			$cols .= $this->identifier($column);
			$vals .= $this->_dynamic_create($value);
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
		return $this->insert($table, $data, 'IGNORE');
	}



	public function replace($table, $data) {
		return $this->insert($table, $data, 'REPLACE');
	}



	public function insertUpdate($table, $data, $column, $update=false, $prefix=true) {
		if ($data === false) $data = [];

		if (empty($update)) {
			$update = [];
		} else if ($update === true  &&  pudl_array($data)) {
			$update = $data;
		} else if ($update === true) {
			$update = [$data];
		}

		$update[]	= $this->identifiers($column)
					. '=LAST_INSERT_ID('
					. $this->identifiers($column)
					. ')';

		return $this->insert($table, $data, $update, $prefix);
	}



	public function insertEx($table, $cols, $data, $update=false) {
		if ($data === false) $data = [];

		if (!is_array($data)  &&  !is_object($data)) {
			throw new pudlException('Invalid data type for pudl::insertEx');
			return false;
		}

		$query = '';

		foreach ($cols as &$name) {
			if (strlen($query)) $query .= ',';
			$query .= $this->identifier($name);
		} unst($name);

		$query .= ') VALUES ';

		$first = true;
		foreach ($data as $set) {
			if (!$first) $query .= ',';
			$first = false;
			$query .= '(';

			$firstitem = true;
			foreach ($set as $item) {
				if (!$firstitem) $query .= ',';
				$firstitem = false;
				$query .= $this->_dynamic_create($item);
			}

			$query .= ')';
		}

		if ($update === 'IGNORE') {
			$query = 'INSERT IGNORE INTO ' . $this->_table($table) . ' (' . $query;

		} else if ($update === 'REPLACE') {
			$query = 'REPLACE INTO ' . $this->_table($table) . ' (' . $query;

		} else {
			$query = 'INSERT INTO ' . $this->_table($table) . ' (' . $query;
			if ($update !== false) {
				$query .= ' ON DUPLICATE KEY UPDATE ';
				$query .= $this->_update($update);
			}
		}

		$this($query);
		return $this->insertId();
	}



	public function replaceEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, 'REPLACE');
	}



	public function insertIgnoreEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, 'IGNORE');
	}

}
