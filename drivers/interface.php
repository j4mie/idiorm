<?php

interface DatabaseDriver {
	public function initialize($connection_string, $username, $password, $driver_options);

	public function quote($value);

	public function prepare($query);

	public function lastInsertId();

	public function setErrorMode($value);

	public function getDriverName();
}

interface DatabaseDriverStatement {
	public function execute($values);

	public function fetch_assoc();
}

?>
