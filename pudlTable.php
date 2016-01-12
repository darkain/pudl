<?php


trait pudlTable {

	function rename($rename, $to=false) {
		$query = 'RENAME TABLE ';

		if (!is_array($rename)) {
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



	function swapTables($table1, $table2) {
		$temp = 'TABLE_SWAP_' . md5(microtime());

		return $this->rename([
			$table1	=> $temp,
			$table2	=> $table1,
			$temp	=> $table2,
		]);
	}



	function drop($table, $temp=true) {
		$query = 'DROP ' . ($temp?'TEMPORARY ':'') . 'TABLE IF EXISTS ';

		if (!is_array($table)) return $this($query . $this->_table($table));

		$first = true;
		foreach ($table as $item) {
			if ($first) $first=false; else $query .= ', ';
			$query .= $this->_table($item);
		}

		return $this($query);
	}

}
