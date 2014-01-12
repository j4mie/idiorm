<?php

require_once dirname(__FILE__) . '/../idiorm.php';

/**
 *
 * Mock version of the PDOStatement class.
 *
 */
class MockPDOStatement extends PDOStatement {
   private $current_row = 0;
   private $statement = NULL;
   
   /**
    * Store the statement that gets passed to the constructor
    */
   public function __construct($statement) {
       $this->statement = $statement;
   }

   /**
    * Check that the array
    */
   public function execute($params) {
       $count = 0;
       $m = array();
       if (preg_match_all('/"[^"\\\\]*(?:\\?)[^"\\\\]*"|\'[^\'\\\\]*(?:\\?)[^\'\\\\]*\'|(\\?)/', $this->statement, $m)) {
           $count = count($m);
           for ($i = 0; $i < $count; $i++) {
               if (!isset($params[$i])) {
                   ob_start();
                   var_dump($m, $params);
                   $output = ob_get_clean();
                   throw new Exception('Incorrect parameter count. Expected ' . $count . ' got ' . count($params) . ".\n" . $this->statement . "\n" . $output);
               }
           }
       }
   }
   
   /**
    * Return some dummy data
    */
   public function fetch($fetch_style=PDO::FETCH_BOTH, $cursor_orientation=PDO::FETCH_ORI_NEXT, $cursor_offset=0) {
       if ($this->current_row == 5) {
           return false;
       } else {
           return array('name' => 'Fred', 'age' => 10, 'id' => ++$this->current_row);
       }
   }
}

/**
 * Another mock PDOStatement class, used for testing multiple connections
 */
class MockDifferentPDOStatement extends MockPDOStatement { }

/**
 *
 * Mock database class implementing a subset
 * of the PDO API.
 *
 */
class MockPDO extends PDO {
   
   /**
    * Return a dummy PDO statement
    */
   public function prepare($statement, $driver_options=array()) {
       $this->last_query = new MockPDOStatement($statement);
       return $this->last_query;
   }
}

/**
 * A different mock database class, for testing multiple connections
 * Mock database class implementing a subset of the PDO API.
 */
class MockDifferentPDO extends MockPDO {

    /**
     * Return a dummy PDO statement
     */
    public function prepare($statement, $driver_options = array()) {
        $this->last_query = new MockDifferentPDOStatement($statement);
        return $this->last_query;
    }
}

class MockMsSqlPDO extends MockPDO {

   public $fake_driver = 'mssql';

   /**
    * If we are asking for the name of the driver, check if a fake one
    * has been set.
    */
    public function getAttribute($attribute) {
        if ($attribute == self::ATTR_DRIVER_NAME) {
            if (!is_null($this->fake_driver)) {
                return $this->fake_driver;
            }
        }
        
        return parent::getAttribute($attribute);
    }
    
}
