<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlInsert {




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST INSERT AUTO INCREMENT ID
	////////////////////////////////////////////////////////////////////////////
	abstract public function insertId();




	////////////////////////////////////////////////////////////////////////////
	// INSERT A ROW INTO THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function insert($table, $data, $update=false, $prefix=true) {
		if ($data === false) $data = [];

		if (!is_array($data)  &&  !is_object($data)) {
			throw new pudlTypeException($this, 'Invalid data type for pudl::insert');
		}

		$cols	= ' (';
		$vals	= '';
		$first	= true;
		foreach ($data as $column => $value) {
			if (!$first) {
				$cols .= ', ';
				$vals .= ', ';
			} else $first = false;

			if (pudl_array($value)) {
				/** @suppress PhanUndeclaredStaticMethod */
				$value = empty($value) ? NULL : static::jsonEncode($value);
			}

			$cols .= $this->identifiers($column, NULL);
			$vals .= $this->_value($value);
		}

		if ($prefix) $cols .= ')'; else $cols = '';

		$table = $this->_table($table);
		if ($update === 'REPLACE') {
			$query = "REPLACE INTO $table$cols VALUES ($vals)";

		} else {
			$query = "INSERT INTO $table$cols VALUES ($vals)";

			if ($update === true) $update = $data;

			if (is_string($update)  &&  strpos($update,'=') === false) {
				$update = static::column(
					$update,
					static::last_insert_id(
						static::column($update)
					)
				);
			}

			if ($update !== false) {
				$query .= $this->_upsert($update);
			}
		}

		$result = $this($query);
		if ($result instanceof pudlStringResult) return $result;
		return $this->insertId();
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UPSERT PART OF THE QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _upsert($data) {
		return	' UPDATE ' .
				$this->_update($data);
	}




	////////////////////////////////////////////////////////////////////////////
	// INSERT OR UPDATE A ROW IN THE DATABASE WHEN KEYS ARE DUPLICATED
	////////////////////////////////////////////////////////////////////////////
	public function upsert($table, $data, $idcol=false) {
		$update = $data;
		if (!is_bool($idcol)) {
			$update[$idcol] = pudl::last_insert_id(
				pudl::column($idcol)
			);
		}
		return $this->insert($table, $data, $update);
	}




	////////////////////////////////////////////////////////////////////////////
	// INSERT ROW INTO THE DATABASE USING COLUMN POSITION INSTEAD OF FIELD NAME
	////////////////////////////////////////////////////////////////////////////
	public function insertValues($table, $data, $update=false) {
		return $this->insert($table, $data, $update, false);
	}




	////////////////////////////////////////////////////////////////////////////
	// INSERT A ROW INTO THE DATABASE, USING ONLY FIELDS THAT EXIST IN THE TABLE
	////////////////////////////////////////////////////////////////////////////
	public function insertExtract($table, $data, $update=false, $prefix=true) {
		return $this->insert(
			$table,
			$this->extractColumns($table, $data),
			$update,
			$prefix
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// SAME AS INSERTEXTRACT, BUT WITH UPSERT
	////////////////////////////////////////////////////////////////////////////
	public function upsertExtract($table, $data, $idcol=false) {
		return $this->upsert(
			$table,
			$this->extractColumns($table, $data),
			$idcol
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// A SPECIALIZED INSERT THAT WILL UPDATE THE INSERTID() ON DUPLICATE KEYS
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// EXTENDED INSERT
	////////////////////////////////////////////////////////////////////////////
	public function insertEx($table, $cols, $data, $update=false) {
		if ($data === false) $data = [];

		if (!is_array($data)  &&  !is_object($data)) {
			throw new pudlTypeException(
				$this,
				'Invalid data type for pudl::insertEx'
			);
		}

		$query = '';

		foreach ($cols as &$name) {
			if (strlen($query)) $query .= ',';
			$query .= $this->identifiers($name, NULL);
		} unset($name);

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
				if (pudl_array($item)) {
					/** @suppress PhanUndeclaredStaticMethod */
					$item = static::jsonEncode($item);
				}
				$query .= $this->_value($item);
			}

			$query .= ')';
		}

		if ($update === 'REPLACE') {
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




	////////////////////////////////////////////////////////////////////////////
	// INSERT USING A SUBQUERY - REQUIRES SECOND CALL TO PUDL FOR SELECT
	////////////////////////////////////////////////////////////////////////////
	public function insertInto($table) {
		$this->string[] = new pudlString(
			'INSERT INTO ' . $this->_table($table) . ' '
		);
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// REPLACE A ROW IN A TABLE - DELETE IF EXIST, THEN INSERT REGARDLESS
	////////////////////////////////////////////////////////////////////////////
	public function replace($table, $data) {
		return $this->insert($table, $data, 'REPLACE');
	}




	////////////////////////////////////////////////////////////////////////////
	// SAME AS REPLACE, BUT USING EXTENDED INSERTS
	////////////////////////////////////////////////////////////////////////////
	public function replaceEx($table, $cols, $data) {
		return $this->insertEx($table, $cols, $data, 'REPLACE');
	}




	////////////////////////////////////////////////////////////////////////////
	// SAME AS INSERTINTO, BUT USING REPLACE
	////////////////////////////////////////////////////////////////////////////
	public function replaceInto($table) {
		$this->string[] = new pudlString(
			'REPLACE INTO ' . $this->_table($table) . ' '
		);
		return $this;
	}

}
