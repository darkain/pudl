<?php


'@phan-file-suppress PhanUndeclaredMethod';



trait pudlTable {


	////////////////////////////////////////////////////////////////////////////
	// GET A LIST OF TABLES FROM THE DATABASE
	// https://mariadb.com/kb/en/library/show-tables/
	////////////////////////////////////////////////////////////////////////////
	public function tables($clause=NULL) {
		$tables				= [];
		$query				= 'SHOW TABLES' . $this->_clause($clause);
		$list				= $this($query)->complete();

		if (is_string($list)  ||  $list instanceof pudlStringResult) {
			return (string) $list;
		}

		foreach ($list as $item) {
			$tables[]		= reset($item);
		}

		return $tables;
	}




	////////////////////////////////////////////////////////////////////////////
	// USED TO TRANSLATE TABLE NAME PREFIXES
	////////////////////////////////////////////////////////////////////////////
	protected function _prefix($table) {
		foreach ($this->prefix as $key => $prefix) {
			if (is_int($key)) continue;

			if (substr($table, 0, strlen($key)) === $key) {
				return $prefix . substr($table, strlen($key));
			}
		}

		return (isset($this->prefix[0]))
			? $this->prefix[0] . $table
			: $table;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK TO SEE IF THE GIVEN TABLE NAME EXISTS IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function tableExists($table) {
		$tables = $this->tables();
		return in_array($this->_prefixTable($table), $tables);
	}




	////////////////////////////////////////////////////////////////////////////
	// RENAME A TABLE IN THE DATABASE
	// https://mariadb.com/kb/en/library/rename-table/
	////////////////////////////////////////////////////////////////////////////
	public function rename($rename, $to=false, $wait=NULL) {
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

		$query .= $this->_wait($wait);

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// SWAP THE NAMES OF TWO DIFFERENT TABLES IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	public function swapTables($table1, $table2, $wait=NULL) {
		$temp = 'TABLE_SWAP_' . (
			function_exists('random_bytes')
				? bin2hex(random_bytes(20))
				: sha1(microtime() . rand() . uniqid('',TRUE))
		);

		return $this->rename([
			$table1	=> $temp,
			$table2	=> $table1,
			$temp	=> $table2,
		], false, $wait);
	}




	////////////////////////////////////////////////////////////////////////////
	// DROP (DELETE) THE GIVEN TABLE(S)
	// OPTIONAL $temp - WHEN TRUE, ONLY WORKS ON TEMPORARY TABLES
	// https://mariadb.com/kb/en/library/drop-table/
	////////////////////////////////////////////////////////////////////////////
	public function drop($tables, $temp=true, $wait=NULL) {
		$query = 'DROP ' . ($temp?'TEMPORARY ':'') . 'TABLE IF EXISTS ';

		if (pudl_array($tables)) {
			$first = true;
			foreach ($tables as $table) {
				if ($first) $first=false; else $query .= ', ';
				$query .= $this->_table($table);
			}
		} else {
			$query .= $this->_table($tables);
		}

		$query .= $this->_wait($wait);

		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// TRUNCATE THE GIVEN TABLE
	// https://mariadb.com/kb/en/library/truncate-table/
	////////////////////////////////////////////////////////////////////////////
	public function truncate($table, $wait=NULL) {
		return $this(
			'TRUNCATE TABLE '
			. $this->_table($table)
			. $this->_wait($wait)
		);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT DATA TYPE FROM STANDARD TO DATABASE SPECIFIC
	// THIS IS OVERWRITTEN IN SOME PUDL DATABASE DRIVERS
	////////////////////////////////////////////////////////////////////////////
	protected function datatype($type) {
		return rtrim($type, " \t\n\r\0\x0B,");
	}




	////////////////////////////////////////////////////////////////////////////
	// !!! EXPERIMENTAL !!!
	// CREATE A TABLE IN THE DATABASE
	////////////////////////////////////////////////////////////////////////////
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
				$query .= $this->datatype($item);
			}

		} else {
			throw new pudlException($this, 'Invalid data type for $columns');
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
			throw new pudlException($this, 'Invalid data type for $keys');
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
			throw new pudlException($this, 'Invalid data type for $options');
		}


		return $this($query);
	}




	////////////////////////////////////////////////////////////////////////////
	// ADD SYSTEM VERSIONING TO THE GIVEN TABLE
	// https://mariadb.com/kb/en/library/system-versioned-tables/
	////////////////////////////////////////////////////////////////////////////
	public function addVersioning($table, $wait=NULL) {
		return $this(
			'ALTER TABLE '
			. $this->_table($table)
			. ' ADD SYSTEM VERSIONING'
			. $this->_wait($wait)
		);
	}
}
