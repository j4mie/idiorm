<?php
/*
 * MSSQL testng for Idiorm
 *
 * Checks that the generated SQL is correct for the MSSQL / Sybase variant
 *
 */

require_once dirname(__FILE__) . "/../idiorm.php";
require_once dirname(__FILE__) . "/test_classes.php";

class DummyMSSQLPDO {
  public function __construct() {
    $this->attributes = array(
      PDO::ATTR_DRIVER_NAME => 'sqlsrv'
      );
  }

  /**
   * Return a dummy PDO statement
   */
  public function prepare($statement, $driver_options=array()) {
    $this->last_query = new DummyPDOStatement($statement);
    return $this->last_query;
  }

  public function getAttribute($attribute) {
    return $this->attributes[$attribute];
  }
}

// Enable logging
ORM::configure('logging', true);

// Set up the dummy database connection
$db = new DummyMSSQLPDO();
ORM::set_db($db);

ORM::for_table('widget')->find_one();
$expected = "SELECT TOP 1 * FROM \"widget\"";
Tester::check_equal("Basic find 1", $expected);

ORM::for_table('widget')->find_many();
$expected = "SELECT * FROM \"widget\"";
Tester::check_equal("Basic unfiltered find_many query", $expected);

Tester::report();
?>
