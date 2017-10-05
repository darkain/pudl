<?php


require_once('pudlPdoResult.php');


class pudlPdo extends pudl {

	public function __construct($data, $autoconnect=true) {
		if (empty($data['server'])) {
			throw new pudlException('No DSN provided for PDO', PUDL_X_CONNECTION);
		}

		if (empty($data['options'])) $data['options'] = [];

		//DEFAULT TO ANSI STYLE IDENTIFIERS, BUT CAN BE OVERWRITTEN
		$this->identifier	= !empty($data['identifier'])
							? $data['identifier']
							: '"';

		parent::__construct($data, $autoconnect);
	}



	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}



	public static function instance($data, $autoconnect=true) {
		return new pudlPdo($data, $autoconnect);
	}



	public function connect() {
		$auth = $this->auth();

		try {
			$this->connection = new PDO(
				$auth['server'],
				$auth['username'],
				$auth['password'],
				$auth['otions']
			);

			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

		} catch (PDOException $e) {
			throw new pudlException(
				'ERROR CONNECTING THROUGH PDO: ' . $this->error(),
				PUDL_X_CONNECTION
			);
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		$this->connection = NULL;
	}



	public function identifier($identifier) {
		if (!$this->connection) return parent::identifier($identifier);

		if ($this->identifier === ']') {
			return '[' . str_replace(']', ']]', $identifier) . ']';
		}

		return parent::identifier($identifier);
	}



	public function escape($value) {
		if (!$this->connection) return parent::escape($value);
		return $this->connection->quote($value);
	}



	protected function process($query) {
		if (!$this->connection) return new pudlPdoResult(false, $this);
		if (strtoupper(substr($query, 0, 7)) === 'SELECT ') {
			$result = @$this->connection->query($query);
			return new pudlPdoResult($result, $this);
		}

		$this->updated = @$this->connection->exec($query);
		return new pudlPdoResult(true, $this);
	}



	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->lastInsertId();
	}



	public function updated() {
		return $this->updated;
	}



	public function errno() {
		if (!$this->connection) return 0;
		return $this->connection->errorCode();
	}



	public function error() {
		if (!$this->connection) return false;
		return $this->connection->errorInfo();
	}



	public function inTransaction() {
		if (!$this->connection) return false;
		return $this->connection->inTransaction();
	}



	public function begin() {
		if ($this->connection) $this->connection->beginTransaction();
		return $this;
	}



	public function commit($sync=false) {
		if ($this->connection) $this->connection->commit();
		if ($sync) $this->sync();
		return $this;
	}



	public function rollback() {
		if ($this->connection) $this->connection->rollback();
		return $this;
	}



	private $updated	= 0;
}
