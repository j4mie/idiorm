<?php

require_once('interface.php');

class MySQLiDatabaseDriver implements DatabaseDriver {
	private $_handle;

	public function initialize($connection_string, $username, $password, $driver_options) {
		$host = null;
		$port = null;
		$database = null;

		$splitted = explode(';', $connection_string);
		foreach($splitted as $split) {
			$pair = explode('=', $split);
			if($pair[0] == 'host')
				$host = $pair[1];
			elseif($pair[0] == 'port')
				$port = $pair[1];
			elseif($pair[0] == 'dbname')
				$database = $pair[1];
		}

		$this->_handle = new mysqli($host, $username, $password, $database, $port);
	}

	public function quote($value) {
		return '"'.$this->_handle->real_escape_string($value).'"';
	}

	public function prepare($query) {
		return new MySQLiDatabaseDriverStatement($this->_handle->prepare($query));
	}

	public function lastInsertId() {
		return $this->_handle->insert_id;
	}

	public function setErrorMode($value) {
		return true;
	}

	public function getDriverName() {
		return 'mysql';
	}
}

class MySQLiDatabaseDriverStatement implements DatabaseDriverStatement {
	private $_statement;

	public function __construct($statement) {
		$this->_statement = $statement;
	}
	
	protected static function _get_type($value) {
		if(is_double($value))
			return 'd';
		if(is_int($value))
			return 'i';
		return 's';
	}

	protected static function _reference_array($array) {
		if(strnatcmp(phpversion(), '5.3') >= 0) {
			$references = array();
			foreach($array as $key => $value)
				$references[$key] = &$array[$key];
			return $references;
		}
		return $array;
	}
	
	protected function internal_bind() {
		if(isset($this->_row))
			return;

		$parameters = array();
		$meta = $this->_statement->result_metadata();
		while($field = $meta->fetch_field())
			$parameters[] = &$this->_row[$field->name];
		
		if(count($parameters) > 0)
			call_user_func_array(array($this->_statement, 'bind_result'), $parameters);
	}
	
	protected function internal_fetch_assoc() {  
		$this->internal_bind();

		if($this->_statement->fetch()) {
			$result = array();
			foreach($this->_row as $key => $value)
				$result[$key] = $value;
			return $result;
		}
		return false;
	}  

	public function execute($values) {
		if(count($values) > 0) {
			$types = '';
			foreach($values as $value)
				$types .= self::_get_type($value);
			array_unshift($values, $types);
			call_user_func_array(array($this->_statement, 'bind_param'), self::_reference_array($values));
		}
		return $this->_statement->execute();
	}

	public function fetch_assoc() {
		return $this->internal_fetch_assoc();
	}
}

?>
