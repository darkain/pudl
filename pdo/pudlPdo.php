<?php


require_once('pudlPdoResult.php');


class pudlPdo extends pudl {

	public function __construct($data, $autoconnect=true) {
		if (empty($data['server'])) {
			throw new pudlException('No DSN provided for PDO');
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
			$this->pdo = new PDO(
				$auth['server'],
				$auth['username'],
				$auth['password'],
				$auth['otions']
			);

			$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

		} catch (PDOException $e) {
			die('ERROR CONNECTING THROUGH PDO: ' . $this->error());
		}
	}



	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		$this->pdo = false;
	}



	public function identifier($identifier) {
		if (!$this->pdo) return parent::identifier($identifier);

		if ($this->identifier === ']') {
			return '[' . str_replace(']', ']]', $identifier) . ']';
		}

		return parent::identifier($identifier);
	}



	public function escape($value) {
		if (!$this->pdo) return parent::escape($value);
		return $this->pdo->quote($value);
	}



	protected function process($query) {
		if (!$this->pdo) return new pudlPdoResult(false, $this);
		if (strtoupper(substr($query, 0, 7)) === 'SELECT ') {
			$result = @$this->pdo->query($query);
			return new pudlPdoResult($result, $this);
		}

		$this->updated = @$this->pdo->exec($query);
		return new pudlPdoResult(true, $this);
	}



	public function insertId() {
		if (!$this->pdo) return 0;
		return $this->pdo->lastInsertId();
	}



	public function updated() {
		return $this->updated;
	}



	public function errno() {
		if (!$this->pdo) return 0;
		return $this->pdo->errorCode();
	}



	public function error() {
		if (!$this->pdo) return false;
		return $this->pdo->errorInfo();
	}



	public function inTransaction() {
		if (!$this->pdo) return false;
		return $this->pdo->inTransaction();
	}



	public function begin() {
		if ($this->pdo) $this->pdo->beginTransaction();
		return $this;
	}



	public function commit($sync=false) {
		if ($this->pdo) $this->pdo->commit();
		if ($sync) $this->sync();
		return $this;
	}



	public function rollback() {
		if ($this->pdo) $this->pdo->rollback();
		return $this;
	}



	private $pdo		= false;
	private $updated	= 0;
}
