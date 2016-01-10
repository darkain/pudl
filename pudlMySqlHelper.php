<?php

trait pudlMySqlHelper {

	protected function _cache() {
		if (!$this->cache)						return '';
		if ($this->isString())					return '';
		if ($this->inUnion())					return '';
		if (!is_object($this->redis))			return 'SQL_CACHE ';
		if ($this->redis instanceof pudlVoid)	return 'SQL_CACHE ';
		return 'SQL_NO_CACHE ';
	}



	public static function aesKey($key) {
		$aes = str_repeat(chr(0), 16);
		$len = strlen($key);
		for ($i=0; $i<$len; $i++) {
			$aes[$i%16] = $aes[$i%16] ^ $key[$i];
		}
		return $aes;
	}



	public static function aesDecrypt($data, $key) {
		return rtrim(
			mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				self::aesKey($key),
				pack('H*', $data),
				MCRYPT_MODE_ECB,
				''
			),
			"\0"
		);
	}



	public function fieldType($table, $column) {
		if (substr($table, 0, 5) === 'pudl_') {
			$table = $this->prefix . substr($table, 5);
		}

		$return = $this->cell('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_TYPE', [
			'TABLE_NAME'	=> $table,
			'COLUMN_NAME'	=> $column,
		]);

		if (substr($return, 0, 5) === 'enum(') {
			$return = str_getcsv(substr($return, 5, -1), ',', "'");
		} else if (substr($return, 0, 4) === 'set(') {
			$return = str_getcsv(substr($return, 4, -1), ',', "'");
		}

		return $return;
	}



	public static function dieOnError($die) {
		self::$die = $die;
	}



	private static $die = true;

}
