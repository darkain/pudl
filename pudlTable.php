<?php


trait pudlTable {

	function rename($rename, $to=false) {
		$query = 'RENAME TABLE ';

		if (!is_array($rename)) {
			if ($to === false) return $this($query . $rename);
			return $this($query . $this->table($rename) . ' TO ' . $this->table($to));
		}

		$first = true;
		foreach ($rename as $old => $new) {
			if ($first) $first=false; else $query .= ', ';
			$query .= $this->table($old) . ' TO ' . $this->table($new);
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

}
