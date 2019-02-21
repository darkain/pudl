<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlPdoResult.php'));



class		pudlPdo
	extends	pudl {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options) {
		if (empty($options['server'])) {
			throw new pudlValueException(
				$this,
				'No DSN provided for PDO'
			);
		}


		//DEFAULT OPTIONS
		if (empty($options['options'])  ||  !pudl_array($options['options'])) {
			$options['options'] = [];
		}


		//DEFAULT TO ANSI STYLE IDENTIFIERS, BUT CAN BE OVERWRITTEN
		$this->identifier	= !empty($options['identifier'])
							? $options['identifier']
							: '"';


		parent::__construct($options);
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE A PDO CONNECTION TO THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();

		pudl_require_extension('pdo');


		//PERSISTENT CONNECTION
		if (!empty($data['persistent'])) {
			$data['options'][PDO::ATTR_PERSISTENT] = true;
		}


		try {
			//ATTEMPT TO CONNECT
			$this->connection = new PDO(
				$auth['server'],
				$auth['username'],
				$auth['password'],
				$auth['options']
			);

			$this->connection->setAttribute(PDO::ATTR_ERRMODE,		PDO::ERRMODE_SILENT);
			$this->connection->setAttribute(PDO::ATTR_CASE,			PDO::CASE_NATURAL);
			$this->connection->setAttribute(PDO::ATTR_ORACLE_NULLS,	PDO::NULL_NATURAL);
			$this->connection->setAttribute(PDO::ATTR_TIMEOUT,		$auth['timeout']);

		} catch (PDOException $e) {
			throw new pudlConnectionException($this,
				'Unable to connect to DDO database ' .
				'"' . $auth['server'] . '"' .
				' with the username ' .
				'"' . $auth['username'] . '"' .
				"\nError: " . $e->getMessage()
			);
		}
	}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT THE PDO CONNECTION FROM THE DATABASE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE AN IDENTIFIER
	////////////////////////////////////////////////////////////////////////////
	public function identifier($identifier) {
		if ($this->identifier === ']') {
			return '[' . str_replace(']', ']]', $identifier) . ']';
		}

		return parent::identifier($identifier);
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPES SPECIAL CHARACTERS IN A STRING FOR USE IN A SQL STATEMENT
	// http://php.net/manual/en/pdo.quote.php
	////////////////////////////////////////////////////////////////////////////
	public function escape($value) {
		if (!$this->connection) return parent::escape($value);
		return $this->connection->quote($value);
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS THE SQL QUERY
	// http://php.net/manual/en/pdo.query.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlPdoResult($this);
		if (strtoupper(substr($query, 0, 7)) === 'SELECT ') {
			$result = @$this->connection->query($query);
			return new pudlPdoResult($this, $result);
		}

		$this->updated = @$this->connection->exec($query);
		return new pudlPdoResult($this, true);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE MOST RECENT AUTO-INCREMENT ID
	// http://php.net/manual/en/pdo.lastinsertid.php
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->lastInsertId();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS AFFECTED BY THE MOST RECENT SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		return $this->updated;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	// http://php.net/manual/en/pdo.errorcode.php
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!$this->connection) return 0;
		$error = $this->connection->errorCode();
		$error = ltrim($error, '0');
		return ($error === '') ? 0 : $error;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	// http://php.net/manual/en/pdo.errorinfo.php
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return false;
		$error = $this->connection->errorInfo();
		if (pudl_array($error)) $error = implode(' - ', $error);
		$error = ltrim($error, '0');
		$error = rtrim($error, ' -');
		return ($error === '') ? false : $error;
	}




	////////////////////////////////////////////////////////////////////////////
	// CHECK IF WE'RE CURRENTLY INSIDE OF A TRANSACTION
	// http://php.net/manual/en/pdo.intransaction.php
	////////////////////////////////////////////////////////////////////////////
	public function inTransaction() {
		if (!$this->connection) return false;
		return $this->connection->inTransaction();
	}




	////////////////////////////////////////////////////////////////////////////
	// BEGIN A NEW TRANSACTION
	// http://php.net/manual/en/pdo.begintransaction.php
	////////////////////////////////////////////////////////////////////////////
	public function begin() {
		if ($this->connection) $this->connection->beginTransaction();
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE CURRENT TRANSACTION BY COMMITTING IT
	// http://php.net/manual/en/pdo.commit.php
	////////////////////////////////////////////////////////////////////////////
	public function commit($sync=false) {
		if ($this->connection) $this->connection->commit();
		if ($sync) $this->sync();
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// END THE CURRENT TRANSACTION BY ROLLING BACK THE CHANGES IT MADE
	// http://php.net/manual/en/pdo.rollback.php
	////////////////////////////////////////////////////////////////////////////
	public function rollback() {
		if ($this->connection) $this->connection->rollback();
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $updated = 0;

}
