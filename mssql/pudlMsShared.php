<?php


abstract class	pudlMsShared
	extends		pudl {


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
		$tables	= [];
		$auth	= $this->auth();
		$len	= $this->prefix !== false ? strlen($this->prefix) : 0;

		$list	= $this->rows(
			'INFORMATION_SCHEMA.TABLES',
			['TABLE_CATALOG' => $auth['database']]
		);

		if ($list instanceof pudlStringResult) {
			return (string) $list;
		}

		foreach ($list as $item) {
			if (!empty($item['TABLE_NAME'])) {
				$table = $item['TABLE_NAME'];

				if ($this->prefix !== false) {
					if (substr($table, 0, $len) === $this->prefix) {
						$table = 'pudl_' . substr($table, $len);
					}
				}

				$tables[] = $table;
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

		if ($offset !== false) {
			$query .= ' OFFSET ' . ((int)$offset) . ' ROWS';
		}

		if ($limit !== false) {
			$query .= ' FETCH NEXT ' . ((int)$limit) . ' ROWS ONLY';
		}

		return $query;
	}


}
