<?php


trait pudlAlias {

	protected function _alias() {
		return $this->_table('x_pudl_alias_' . ++self::$_aliases);
	}

	private static $_aliases = 0;

}
