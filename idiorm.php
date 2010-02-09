<?php

    class ORM {

        // -- Class constants -- //

        // Select WHERE operators
        const EQUALS = '=';
        const LIKE = 'LIKE';

        // Find types
        const FIND_ONE = 0;
        const FIND_MANY = 1;

        // Update or insert?
        const UPDATE = 0;
        const INSERT = 1;

        // Where clauses array keys
        const COLUMN_NAME = 0;
        const VALUE = 1;
        const OPERATOR = 2;


        // -- Class properties -- //
        private static $config = array(
            'connection_string' => 'sqlite://:memory:',
            'id_column' => 'id',
            'id_column_overrides' => array(),
        );

        // Database connection, instance of the PDO class
        private static $db;

        // -- Instance properties -- //
        private $table_name;
        private $find_type; // will be FIND_ONE or FIND_MANY
        private $values = array(); // Values to be bound to the query
        private $where = array();

        private $data = array();

        // Are we updating or inserting?
        private $update_or_insert = self::UPDATE;

        // -- Static methods -- //
        public static function configure($key, $value=null) {

            // Shortcut: If only one argument is passed, 
            // assume it's a connection string
            if (is_null($value)) {
                $value = $key;
                $key = 'connection_string';
            }
            self::$config[$key] = $value;
        }

        public static function for_table($table_name) {
            return new self($table_name);
        }

        private static function setup_db() {
            self::$db = new PDO(self::$config['connection_string']);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }

        public static function get_db() {
            if (!is_object(self::$db)) {
                self::setup_db();
            }
            return self::$db;
        }

        // -- Instance methods -- //
        private function __construct($table_name, $data=array()) {
            $this->table_name = $table_name;
            $this->data = $data;
        }

        public function create($data) {
            $this->update_or_insert = self::INSERT;
            return $this;
        }

        public function hydrate($table_name, $data=array()) {
            return new self($table_name, $data);
        }

        public function find_one() {
            $this->find_type = self::FIND_ONE;
            return $this;
        }

        public function find_many() {
            $this->find_type = self::FIND_MANY;
            return $this;
        }

        public function where($column_name, $value, $operator=self::EQUALS) {
            $this->where[] = array(
                self::COLUMN_NAME => $column_name,
                self::VALUE => $value,
                self::OPERATOR => $operator
            );
            return $this;
        }

        private function build_select() {
            $query = array();
            $query[] = 'SELECT * FROM ' . $this->table_name;

            if (count($this->where) > 0) {
                $query[] = "WHERE";
                $first = array_shift($this->where);
                $query[] = join(" ", array(
                    $first[self::COLUMN_NAME],
                    $first[self::OPERATOR],
                    '?'
                ));
                $this->values[] = $first[self::VALUE];

                while($where = array_shift($this->where)) {
                    $query[] = "AND";
                    $query[] = join(" ", array(
                        $where[self::COLUMN_NAME],
                        $where[self::OPERATOR],
                        '?'
                    ));
                    $this->values[] = $first[self::VALUE];
                }
            }

            return join(" ", $query);
        }

        public function run() {
            self::setup_db();
            $statement = self::$db->prepare($this->build_select());
            $statement->execute($this->values);

            if ($this->find_type == self::FIND_ONE) {
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                $this->data = $result;
                return $this;
            } else {
                $instances = array();
                while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                    $instances[] = self::hydrate($this->table_name, $row);
                }
                return $instances;
            }
        }

        public function as_sql() {
            $sql = $this->build_select();
            $sql = str_replace("?", "%s", $sql);

            $quoted_values = array();
            foreach ($this->values as $value) {
                $quoted_values[] = '"' . $value . '"';
            }
            return vsprintf($sql, $quoted_values);
        }
    }

