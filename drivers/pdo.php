<?php

require_once('interface.php');

class PDODatabaseDriver implements DatabaseDriver {
	private $_handle;

	public function initialize($connection_string, $username, $password, $driver_options) {
		$this->_handle = new PDO($connection_string, $username, $password, $driver_options);
	}

	public function quote($value) {
		return $this->_handle->quote($value);
	}

	public function prepare($query) {
		return new PDODatabaseDriverStatement($this->_handle->prepare($query));
	}

	public function lastInsertId() {
		return $this->_handle->lastInsertId();
	}

	public function setErrorMode($value) {
		return $this->_handle->setAttribute(PDO::ATTR_ERRMODE, $value);
	}

	public function getDriverName() {
		return $this->_handle->getAttribute(PDO::ATTR_DRIVER_NAME);
	}
}

class PDODatabaseDriverStatement implements DatabaseDriverStatement {
	private $_statement;

	public function __construct($statement) {
		$this->_statement = $statement;
	}

	public function execute($values) {
		return $this->_statement->execute();
	}

	public function fetch_assoc() {
		return $this->_statement->fetch(PDO::FETCH_ASSOC);
	}
}

?>
