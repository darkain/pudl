<?php

class mySqlQuery {


	public static function column($col) {
		if (!is_array($col)) {
			if ($col === false  ||  $col === ''  ||  $col === null) return '*';
			return $col;
		}

		$query = '';
		$first = true;

		foreach ($col as $key => &$val) {
			if (!$first) $query .= ', ';
			if (is_numeric($key)) {
				$query .= $val;
			} else {
				$query .= "$key.`$val`";
			}
			$first = false;
		}

		return $query;
	}



	public static function table($table) {
		if (!is_array($table)) return " FROM `$table`";

		$query = ' FROM ';
		$first = true;

		foreach ($table as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;

			if (!is_array($val)) {
				$query .= "`$val` $key";
			} else {
				$query .= "`$val[0]` $key";
				for ($i=1; $i<count($val); $i++) {
					$query .= self::joinTable( $val[$i]['join']);

					if (isset($val[$i]['clause'])) {
						$query .= self::joinClause($val[$i]['clause']);
					}

					if (isset($val[$i]['using'])) {
						$query .= self::joinUsing($val[$i]['using']);
					}
				}
			}
		}

		return $query;
	}



	public static function clause($clause) {
		if ($clause === false) return '';
		if (!is_array($clause)) return " WHERE $clause";
		if (!count($clause)) return '';

		$query = " WHERE ";
		$first = true;

		foreach ($clause as $key => &$val) {
			if (!$first) $query .= ' AND ';
			$first = false;

			if (is_array($val)) {
				$query .= '(';
				$val_first = true;
				foreach ($val as $val_key => &$val_val) {
					if (!$val_first) $query .= ' OR ';
					$val_first = false;
					$query .= $val_val;
				}
				$query .= ')';

			} else {
				$query .= $val;
			}
		}

		return $query;
	}



	public static function order($order) {
		if ($order === false)  return '';
		if (!is_array($order)) return " ORDER BY $order";
		if (!count($order)) return '';

		$query = " ORDER BY ";
		$first = true;

		foreach ($order as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		return $query;
	}



	public static function group($group) {
		if ($group === false)  return '';
		if (!is_array($group)) return " GROUP BY $group";
		if (!count($group)) return '';

		$query = " GROUP BY ";
		$first = true;

		foreach ($group as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		return $query;
	}	



	public static function limit($limit, $offset) {
		if ($limit !== false  &&  $offset === false) return " LIMIT $limit";
		if ($limit !== false  &&  $offset !== false) return " LIMIT $offset,$limit";
		return '';
	}	



	public static function lock($lock) {
		if ($lock === "SHARE")  return ' LOCK IN SHARE MODE';
		if ($lock === "UPDATE") return ' FOR UPDATE';
		return '';
	}



	public static function joinClause($join_clause) {
		if ($join_clause === false) return '';
		if (!is_array($join_clause)) return " ON ($join_clause)";
		if (!count($join_clause)) return '';

		$query = ' ON (';
		$first = true;

		foreach ($join_clause as $key => &$val) {
			if (!$first) $query .= ' AND ';
			$first = false;

			if (is_array($val)) {
				$query .= '(';
				$val_first = true;
				foreach ($val as $val_key => &$val_val) {
					if (!$val_first) $query .= ' OR ';
					$val_first = false;
					$query .= $val_val;
				}
				$query .= ')';

			} else {
				$query .= $val;
			}
		}

		$query .= ')';
		return $query;
	}



	public static function joinUsing($join_using) {
		if ($join_using === false)  return '';
		if (!is_array($join_using)) return " USING ($join_using)";
		if (!count($join_using)) return '';

		$query = ' USING (';

		$first = true;
		foreach ($join_using as $key => &$val) {
			if (!$first) $query .= ', ';
			$first = false;
			$query .= $val;
		}

		$query .= ')';
		return $query;
	}



	public static function joinTable($join_table) {
		if (!is_array($join_table)) return " LEFT JOIN (`$join_table`)";

		// $query = " LEFT JOIN (";
		$query = " LEFT JOIN ";
		$first = true;

		foreach ($join_table as $key => &$val) {
			// if (!$first) $query .= ', ';
			$query .= "`$val` $key";
			break;
			// $first = false;
		}

		// $query .= ')';
		return $query;
	}



	public static function update($data, $safe=false) {
		global $db;
		if (!is_array($data)) return $data;

		$query = '';

		$first = true;
		foreach ($data as $column => &$value) {
			$good = false;

			if (is_null($value)) {
				$good = 'NULL';
			} else if (is_array($value)) {
				foreach ($value as $func => $sub_value) {
					if ($func == 'AES_ENCRYPT') {
						if ($safe !== false) $sub_value['key']  = $db->safer($sub_value['key']);
						if ($safe !== false) $sub_value['data'] = $db->safer($sub_value['data']);
						$good = $func . '("' . $sub_value['data'] . '","' . $sub_value['key'] . '")';
					} else {
						if ($safe !== false) $sub_value = $db->safer($sub_value);
						$good = $func . '(' . $sub_value . ')';
					}
					break;
				}
			} else {
				if ($safe !== false) $value = $db->safer($value);
				$good = "'$value'";
			}

			if ($good !== false) {
				if (!$first) $query .= ', ';
				$first  = false;
				$query .= "`$column`=$good";
			}
		}

		return $query;
	}	


}
