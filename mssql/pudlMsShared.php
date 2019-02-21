<?php


abstract class	pudlMsShared
	extends		pudl {



	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options) {
		//SET INITIAL VALUES
		$this->identifier = ']';

		parent::__construct($options);
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR, FORCE DISCONNECT
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE AN IDENTIFIER
	////////////////////////////////////////////////////////////////////////////
	public function identifier($identifier) {
		return	'[' . str_replace(
			$this->identifier,
			$this->identifier.$this->identifier,
			$identifier
		) . ']';
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LIST OF TABLES
	////////////////////////////////////////////////////////////////////////////
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




	////////////////////////////////////////////////////////////////////////////
	// LIMIT AND OFFSET
	////////////////////////////////////////////////////////////////////////////
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
