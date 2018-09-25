<?php


trait pudlMsHelper {


	public function __construct($data, $autoconnect=true) {
		//SET INITIAL VALUES
		$this->identifier = ']';

		parent::__construct($data, $autoconnect);
	}



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public function identifier($identifier) {
		return	'[' . str_replace(
			$this->identifier,
			$this->identifier.$this->identifier,
			$identifier
		) . ']';
	}



	public function tables() {
		$auth	 = $this->auth();

		$query	 = 'SELECT * FROM [INFORMATION_SCHEMA].[TABLES] WHERE [TABLE_CATALOG]=';
		$query	.= $this->_value($auth['database']);
		$list	 = $this->process($query)->complete();

		$len	 = $this->prefix !== false ? strlen($this->prefix) : 0;
		$tables	 = [];

		foreach ($list as $item) {
			if (!empty($item['TABLE_NAME'])) {
				$table		= $item['TABLE_NAME'];

				if ($this->prefix !== false) {
					if (substr($table, 0, $len) === $this->prefix) {
						$table	= 'pudl_' . substr($table, $len);
					}
				}

				$tables[]	= $table;
			}
		}

		return $tables;
	}



	protected function _limit($limit, $offset=false) {
		if (pudl_array($limit)) {
			$offset	= count($limit) > 1 ? end($limit) : false;
			$limit	= reset($limit);
		}

		$query = '';

		if ($offset !== false)
			$query .= ' OFFSET ' . ((int)$offset) . ' ROWS';

		if ($limit !== false)
			$query .= ' FETCH NEXT ' . ((int)$limit) . ' ROWS ONLY';

		return $query;
	}


}
