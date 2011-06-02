<?php

require_once('interface.php');

class MySQLDatabaseDriver implements DatabaseDriver {
	private $_handle;

	public function initialize($connection_string, $username, $password, $driver_options) {
		$host = null;
		$database = null;

		$splitted = explode(';', $connection_string);
		foreach($splitted as $split) {
			$pair = explode('=', $split);
			if($pair[0] == 'host')
				$host = $pair[1];
			elseif($pair[0] == 'dbname')
				$database = $pair[1];
		}

		$this->_handle = mysql_connect($host, $username, $password);
		mysql_select_db($database, $this->_handle);
	}
	
	public function escape_string($value) {
		return mysql_real_escape_string($value, $this->_handle);
	}

	public function quote($value) {
		return '"'.$this->escape_string($value).'"';
	}

	public function prepare($query) {
		return new MySQLDatabaseDriverStatement($this, $this->_handle, $query);
	}

	public function lastInsertId() {
		return mysql_insert_id($this->_handle);
	}

	public function setErrorMode($value) {
		return true;
	}

	public function getDriverName() {
		return 'mysql';
	}
}

class MySQLDatabaseDriverStatement implements DatabaseDriverStatement {
	private $_driver;
	private $_handle;
	private $_query;
	private $_result;

	public function __construct($driver, $handle, $query) {
		$this->_driver = $driver;
		$this->_handle = $handle;
		$this->_query = $query;
	}

	public function execute($values) {
		$query = $this->_query;
		if(count($values) > 0) {
			$values = array_map(array($this->_driver, 'quote'), $values);
			array_unshift($values, str_replace('?', '%s', $query));
			$query = call_user_func_array('sprintf', $values);
		}
		$this->_result = mysql_query($query, $this->_handle);
		return $this->_result !== false;
	}

	public function fetch_assoc() {
		if(!$this->_result)
			return false;
		return mysql_fetch_assoc($this->_result);
	}
}

?>
