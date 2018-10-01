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



	public function tables($clause=NULL) {
		$tables	= [];
		$auth	= $this->auth();

		if ($this->isString()) $clause = [$clause];
		if (!pudl_array($clause)) $clause = [];

		$list	= $this->rows(
			'INFORMATION_SCHEMA.TABLES',
			$clause + ['TABLE_CATALOG' => $auth['database']]
		);

		if ($list instanceof pudlStringResult) {
			return (string) $list;
		}

		foreach ($list as $item) {
			if (!empty($item['TABLE_NAME'])) {
				$tables[] = $item['TABLE_NAME'];
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
