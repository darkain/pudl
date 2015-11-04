<?php

trait pudlMySqlHelper {

	protected function _cache() {
		if (is_array($this->union))	return '';
		if (empty(!$this->string))	return '';
		if (!$this->cache)			return '';
		if (!$this->redis)			return 'SQL_CACHE ';
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



	public static function dieOnError($die) {
		self::$die = $die;
	}



	private static $die = true;

}
