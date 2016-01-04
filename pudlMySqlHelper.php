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
		$return = $this->cell('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_TYPE', [
			'TABLE_NAME'	=> $table,
			'COLUMN_NAME'	=> $column,
		]);

		if (substr($return, 0, 5) === 'enum(') {
			$return = substr($return, 5, strlen($return)-6);
			$return = explode(',', $return);
			foreach ($return as &$val) {
				if (substr($val, 0,  1) === "'") $val = substr($val, 1);
				if (substr($val, -1, 1) === "'") $val = substr($val, 0, strlen($val)-1);
			} unset($val);
		}

		return $return;
	}



	public static function dieOnError($die) {
		self::$die = $die;
	}



	private static $die = true;

}
