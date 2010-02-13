<?php

    /**
     *
     * Mock database classes implementing a subset
     * of the PDO API. Used for testing Idiorm.
     *
     */

    class DummyStatement {

        private $query = '';
        private $input_parameters = array();
        private $current_row = 1;
        
        public function __construct($statement) {
            $this->query = $statement;
        }

        public function execute($input_parameters=array()) {
            $this->input_parameters = $input_parameters;
        }

        public function fetch($fetch_style) {
            if ($this->current_row == 5) {
                return false;
            } else {
                $this->current_row++;
                return array('name' => 'Fred', 'age' => 10, 'id' => '1');
            }
        }

        public function get_query() {
            return $this->query;
        }

        public function get_parameters() {
            return $this->input_parameters;
        }

        public function get_bound_query() {
            $sql = $this->get_query();
            $sql = str_replace("?", "%s", $sql);

            $quoted_values = array();
            $values = $this->get_parameters();
            foreach ($values as $value) {
                $quoted_values[] = '"' . $value . '"';
            }
            return vsprintf($sql, $quoted_values);
        }

    }

    class DummyPDO {

        private $last_query;
       
        public function __construct($connection_string="") {
        }

        public function setAttribute($attribute, $value) {
        }

        public function prepare($statement) {
            $this->last_query = new DummyStatement($statement);
            return $this->last_query;
        }

        public function lastInsertId() {
            return 0;
        }

        public function get_last_query() {
            return $this->last_query->get_bound_query();
        }
    }

    /**
     * Class to provide simple testing functionality
     */
    class Tester {

        private static $passed_tests = array();
        private static $failed_tests = array();
        private static $db;

        public static function set_db($db) {
            self::$db = $db;
        }

        private static function report_pass($test_name) {
            echo "<p>PASS: $test_name</p>";
            self::$passed_tests[] = $test_name;
        }

        private static function report_failure($test_name, $query) {
            echo "<p>FAIL: $test_name</p>";
            echo "<p>Expected: $query</p>";
            echo "<p>Actual: " . self::$db->get_last_query() . "</p>";
            self::$failed_tests[] = $test_name;
        }

        public static function report() {
            $passed_count = count(self::$passed_tests);
            $failed_count = count(self::$failed_tests);
            echo "<p>$passed_count tests passed. $failed_count tests failed.</p>";

            if ($failed_count != 0) {
                echo "<p>Failed tests: " . join(", ", self::$failed_tests) . "</p>";
            }
        }

        public static function check_equal($test_name, $query) {
            $last_query = self::$db->get_last_query();
            if ($query == self::$db->get_last_query()) {
                self::report_pass($test_name);
            } else {
                self::report_failure($test_name, $query);
            }
        }
    }
