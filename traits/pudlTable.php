<?php


trait pudlTable {


	public function tables() {
		$tables				= [];
		$len				= $this->prefix !== false ? strlen($this->prefix) : 0;
		$list				= $this('SHOW TABLES')->complete();

		foreach ($list as $item) {
			$table			= reset($item);

			if ($this->prefix !== false) {
				if (substr($table, 0, $len) === $this->prefix) {
					$table	= 'pudl_' . substr($table, $len);
				}
			}

			$tables[]		= $table;
		}
		return $tables;
	}



	public function tableExists($table) {
		$tables = $this->tables();
		return in_array($table, $tables);
	}



	public function rename($rename, $to=false) {
		$query = 'RENAME TABLE ';

		if (!pudl_array($rename)) {
			if ($to === false) return $this($query . $rename);
			return $this($query . $this->_table($rename) . ' TO ' . $this->_table($to));
		}

		$first = true;
		foreach ($rename as $old => $new) {
			if ($first) $first=false; else $query .= ', ';
			$query .= $this->_table($old) . ' TO ' . $this->_table($new);
		}

		return $this($query);
	}



	public function swapTables($table1, $table2) {
		$temp = 'TABLE_SWAP_' . sha1(microtime().rand());

		return $this->rename([
			$table1	=> $temp,
			$table2	=> $table1,
			$temp	=> $table2,
		]);
	}



	public function drop($table, $temp=true) {
		$query = 'DROP ' . ($temp?'TEMPORARY ':'') . 'TABLE IF EXISTS ';

		if (!pudl_array($table)) return $this($query . $this->_table($table));

		$first = true;
		foreach ($table as $item) {
			if ($first) $first=false; else $query .= ', ';
			$query .= $this->_table($item);
		}

		return $this($query);
	}



	public function truncate($table) {
		return $this('TRUNCATE TABLE ' . $this->_table($table));
	}



	public function create($table, $columns, $keys=false, $options=false) {
		$query  = 'CREATE TABLE IF NOT EXISTS ';
		$query .= $this->_table($table) . ' (';

		if (is_string($columns)) {
			$query .= rtrim($columns, " \t\n\r\0\x0B,");

		} else if (pudl_array($columns)) {
			$first = true;
			foreach ($columns as $key => $item) {
				if ($first) $first = false; else $query .= ', ';
				if (is_string($key)) {
					$query .= $this->identifier($key) . ' ';
				}
				$query .= rtrim($item, " \t\n\r\0\x0B,");
			}

		} else {
			throw new pudlException('Invalid data type for $columns');
		}


		if ($keys === false) {
			//DO NOTHING
		} else if (is_string($keys)) {
			$query .= ', ' . rtrim($keys, " \t\n\r\0\x0B,");

		} else if (pudl_array($keys)) {
			foreach ($keys as $item) {
				$query .= ', ' . rtrim($item, " \t\n\r\0\x0B,");
			}

		} else {
			throw new pudlException('Invalid data type for $keys');
		}


		$query .= ')';


		if ($options === false) {
			//DO NOTHING
		} else if (is_string($options)) {
			$query .= ' ' . $options;

		} else if (pudl_array($options)) {
			foreach ($options as $key => $item) {
				if (is_string($key)) {
					$query .= ' ' . $key . '=' . $item;
				} else {
					$query .= ' ' . $item;
				}
			}

		} else {
			throw new pudlException('Invalid data type for $options');
		}


		return $this($query);
	}




	public function addVersioning($table) {
		$query  = 'ALTER TABLE ' . $this->_table($table) . ' ';
		$query .= 'ADD SYSTEM VERSIONING PARTITION BY SYSTEM_TIME (';
		$query .= 'PARTITION p_hist HISTORY,';
		$query .= 'PARTITION p_cur CURRENT';
		$query .= ')';
	}
}
