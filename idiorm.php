<?php

    /**
     *
     * Idiorm
     *
     * http://github.com/j4mie/idiorm/
     *
     * A single-class super-simple database abstraction layer for PHP.
     * Provides (nearly) zero-configuration object-relational mapping
     * and a fluent interface for building basic, commonly-used queries.
     *
     * BSD Licensed.
     *
     * Copyright (c) 2010, Jamie Matthews
     * All rights reserved.
     *
     * Redistribution and use in source and binary forms, with or without
     * modification, are permitted provided that the following conditions are met:
     *
     * * Redistributions of source code must retain the above copyright notice, this
     *   list of conditions and the following disclaimer.
     *
     * * Redistributions in binary form must reproduce the above copyright notice,
     *   this list of conditions and the following disclaimer in the documentation
     *   and/or other materials provided with the distribution.
     *
     * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
     * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
     * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
     * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE
     * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
     * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
     * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
     * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
     * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
     * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
     *
     */

    class ORM {

        // ----------------------- //
        // --- CLASS CONSTANTS --- //
        // ----------------------- //

        // Find types
        const FIND_ONE = 0;
        const FIND_MANY = 1;
        const COUNT = 2;

        // Update or insert?
        const UPDATE = 0;
        const INSERT = 1;

        // Order by array keys
        const ORDER_BY_COLUMN_NAME = 0;
        const ORDER_BY_ORDERING = 1;

        // Where clauses array keys
        const WHERE_COLUMN_NAME = 0;
        const WHERE_VALUE = 1;
        const WHERE_OPERATOR = 2;

        // ------------------------ //
        // --- CLASS PROPERTIES --- //
        // ------------------------ //

        // Class configuration
        private static $config = array(
            'connection_string' => 'sqlite::memory:',
            'id_column' => 'id',
            'id_column_overrides' => array(),
            'error_mode' => PDO::ERRMODE_EXCEPTION,
            'username' => null,
            'password' => null,
            'driver_options' => null,
        );

        // Database connection, instance of the PDO class
        private static $db;

        // --------------------------- //
        // --- INSTANCE PROPERTIES --- //
        // --------------------------- //

        // The name of the table the current ORM instance is associated with
        private $table_name;

        // Will be FIND_ONE or FIND_MANY
        private $find_type;

        // Values to be bound to the query
        private $values = array();

        // Is this a raw query?
        private $is_raw_query = false;

        // The raw query
        private $raw_query = '';

        // The raw query parameters
        private $raw_parameters = array();

        // Array of WHERE clauses
        private $where = array();

        // Is the WHERE clause raw?
        private $where_is_raw = false;

        // Raw WHERE clause
        private $raw_where_clause = '';

        // Raw WHERE parameters
        private $raw_where_parameters = array();

        // LIMIT
        private $limit = null;

        // OFFSET
        private $offset = null;

        // ORDER BY
        private $order_by = array();

        // The data for a hydrated instance of the class
        private $data = array();

        // Fields that have been modified during the
        // lifetime of the object
        private $dirty_fields = array();

        // Are we updating or inserting?
        private $update_or_insert = self::UPDATE;

        // ---------------------- //
        // --- STATIC METHODS --- //
        // ---------------------- //

        /**
         * Pass configuration settings to the class in the form of
         * key/value pairs. As a shortcut, if the second argument
         * is omitted, the setting is assumed to be the DSN string
         * used by PDO to connect to the database. Often, this
         * will be the only configuration required to use Idiorm.
         */
        public static function configure($key, $value=null) {
            // Shortcut: If only one argument is passed, 
            // assume it's a connection string
            if (is_null($value)) {
                $value = $key;
                $key = 'connection_string';
            }
            self::$config[$key] = $value;
        }

        /**
         * Despite its slightly odd name, this is actually the factory
         * method used to acquire instances of the class. It is named
         * this way for the sake of a readable interface, ie
         * ORM::for_table('table_name')->find_one()-> etc. As such,
         * this will normally be the first method called in a chain.
         */
        public static function for_table($table_name) {
            return new self($table_name);
        }

        /**
         * Set up the database connection used by the class.
         */
        private static function setup_db() {
            if (!is_object(self::$db)) {
                $connection_string = self::$config['connection_string'];
                $username = self::$config['username'];
                $password = self::$config['password'];
                $driver_options = self::$config['driver_options'];
                self::$db = new PDO($connection_string, $username, $password, $driver_options);
                self::$db->setAttribute(PDO::ATTR_ERRMODE, self::$config['error_mode']);
            }
        }

        /**
         * This can be called if the ORM should use a ready-instantiated
         * PDO object as its database connection. Won't be used in normal
         * operation, but it's here in case it's needed.
         */
        public static function set_db($db) {
            self::$db = $db;
        }

        /**
         * Returns the PDO instance used by the the ORM to communicate with
         * the database. This can be called if any low-level DB access is
         * required outside the class.
         */
        public static function get_db() {
            self::setup_db();
            return self::$db;
        }

        // ------------------------ //
        // --- INSTANCE METHODS --- //
        // ------------------------ //

        /**
         * Private constructor; can't be called directly.
         * Use the ORM::for_table factory method instead.
         */
        private function __construct($table_name, $data=array()) {
            $this->table_name = $table_name;
            $this->data = $data;
        }

        /**
         * Create a new, empty instance of the class. Used
         * to add a new row to your database. May optionally
         * be passed an associative array of data to populate
         * the instance. If so, all fields will be flagged as
         * dirty so all will be saved to the database when
         * save() is called.
         */
        public function create($data=null) {
            $this->update_or_insert = self::INSERT;

            if (!is_null($data)) {
                return $this->hydrate($data)->force_all_dirty();
            }
            return $this;
        }

        /**
         * Tell the ORM that you are expecting a single result
         * back from your query, and execute it. Will return
         * a single instance of the ORM class, or false if no
         * rows were returned.
         * As a shortcut, you may supply an ID as a parameter
         * to this method. This will perform a primary key
         * lookup on the table.
         */
        public function find_one($id=null) {
            if(!is_null($id)) {
                $this->where($this->get_id_column_name(), $id);
            }
            $this->find_type = self::FIND_ONE;
            return $this->run();
        }

        /**
         * Tell the ORM that you are expecting multiple results
         * from your query, and execute it. Will return an array
         * of instances of the ORM class, or an empty array if
         * no rows were returned.
         */
        public function find_many() {
            $this->find_type = self::FIND_MANY;
            return $this->run();
        }

        /**
         * Tell the ORM that you wish to execute a COUNT query.
         * Will return an integer representing the number of
         * rows returned.
         */
        public function count() {
            $this->find_type = self::COUNT;
            return $this->run();
        }

         /**
         * This method can be called to hydrate (populate) this
         * instance of the class from an associative array of data.
         * This will usually be called only from inside the class,
         * but it's public in case you need to call it directly.
         */
        public function hydrate($data=array()) {
            $this->data = $data;
            return $this;
        }

        /**
         * Force the ORM to flag all the fields in the $data array
         * as "dirty" and therefore update them when save() is called.
         */
        public function force_all_dirty() {
            $this->dirty_fields = $this->data;
            return $this;
        }

        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         */
        public function raw_query($query, $parameters) {
            $this->is_raw_query = true;
            $this->raw_query = $query;
            $this->raw_parameters = $parameters;
            return $this;
        }

        /**
         * Private method to add a WHERE clause to the query
         * Class constants defined above should be used to provide the
         * $operator argument.
         */
        private function add_where($column_name, $operator, $value) {
            $this->where[] = array(
                self::WHERE_COLUMN_NAME => $column_name,
                self::WHERE_OPERATOR => $operator,
                self::WHERE_VALUE => $value,
            );
            return $this;
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         */
        public function where($column_name, $value) {
            return $this->where_equal($column_name, $value);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         */
        public function where_equal($column_name, $value) {
            return $this->add_where($column_name, '=', $value);
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         */
        public function where_like($column_name, $value) {
            return $this->add_where($column_name, 'LIKE', $value);
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         */
        public function where_not_like($column_name, $value) {
            return $this->add_where($column_name, 'NOT LIKE', $value);
        }

        /**
         * Add a WHERE ... > clause to your query
         */
        public function where_gt($column_name, $value) {
            return $this->add_where($column_name, '>', $value);
        }

        /**
         * Add a WHERE ... < clause to your query
         */
        public function where_lt($column_name, $value) {
            return $this->add_where($column_name, '<', $value);
        }

        /**
         * Add a WHERE ... >= clause to your query
         */
        public function where_gte($column_name, $value) {
            return $this->add_where($column_name, '>=', $value);
        }

        /**
         * Add a WHERE ... <= clause to your query
         */
        public function where_lte($column_name, $value) {
            return $this->add_where($column_name, '<=', $value);
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         */
        public function where_raw($clause, $parameters) {
            $this->where_is_raw = true;
            $this->raw_where_clause = $clause;
            $this->raw_where_parameters = $parameters;
            return $this;
        }

        /**
         * Add a LIMIT to the query
         */
        public function limit($limit) {
            $this->limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         */
        public function offset($offset) {
            $this->offset = $offset;
            return $this;
        }

        /**
         * Add an ORDER BY clause to the query
         */
        private function add_order_by($column_name, $ordering) {
            $this->order_by[] = array(
                self::ORDER_BY_COLUMN_NAME => $column_name,
                self::ORDER_BY_ORDERING => $ordering,
            );
            return $this;
        }

        /**
         * Add an ORDER BY column DESC clause
         */
        public function order_by_desc($column_name) {
            return $this->add_order_by($column_name, 'DESC');
        }

        /**
         * Add an ORDER BY column ASC clause
         */
        public function order_by_asc($column_name) {
            return $this->add_order_by($column_name, 'ASC');
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         */
        private function build_select() {

            if ($this->is_raw_query) {
                $this->values = $this->raw_parameters;
                return $this->raw_query;
            }

            $query = array();

            if ($this->find_type === self::COUNT) {
                $query[] = 'SELECT COUNT(*) AS count FROM ' . $this->table_name;
            } else {
                $query[] = 'SELECT * FROM ' . $this->table_name;
            }

            if ($this->where_is_raw) { // Raw WHERE clause
                $query[] = "WHERE";
                $query[] = $this->raw_where_clause;
                $this->values = array_merge($this->values, $this->raw_where_parameters);
            } else if (count($this->where) > 0) { // Standard WHERE clauses
                $where_clauses = array();

                while($where = array_shift($this->where)) {
                    $where_clauses[] = join(" ", array(
                        $where[self::WHERE_COLUMN_NAME],
                        $where[self::WHERE_OPERATOR],
                        '?'
                    ));
                    $this->values[] = $where[self::WHERE_VALUE];
                }

                $query[] = "WHERE";
                $query[] = join(" AND ", $where_clauses);
            }

            // Add ORDER BY clause(s)
            $order_by = array();
            foreach ($this->order_by as $order) {
                $order_by[] = $order[self::ORDER_BY_COLUMN_NAME] . " " . $order[self::ORDER_BY_ORDERING];
            }

            if (count($order_by) != 0) {
                $query[] = "ORDER BY";
                $query[] = join(", ", $order_by);
            }

            // Add LIMIT if present
            if (!is_null($this->limit)) {
                $query[] = "LIMIT " . $this->limit;
            }

            // Add OFFSET if present
            if (!is_null($this->offset)) {
                $query[] = "OFFSET " . $this->offset;
            }

            return join(" ", $query);
        }

        /**
         * Execute the SELECT query that has been built up by chaining methods
         * on this class. This method is called by find_one() and find_many().
         * If find_one() has been called, this will return a single instance of
         * the class or false. If find_many() has been called, this will return
         * an array of instances of the class.
         */
        private function run() {
            self::setup_db();
            $statement = self::$db->prepare($this->build_select());
            $statement->execute($this->values);

            switch ($this->find_type) {
                case self::FIND_ONE:
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                    return $result ? self::for_table($this->table_name)->hydrate($result) : $result;
                case self::FIND_MANY:
                    $instances = array();
                    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                        $instances[] = self::for_table($this->table_name)->hydrate($row);
                    }
                    return $instances;
                case self::COUNT:
                    $result = $statement->fetch(PDO::FETCH_ASSOC);
                    return isset($result['count']) ? (int) $result['count'] : 0;
            }
        }

        /**
         * Return the value of a property of this object (database row)
         * or null if not present.
         */
        public function get($key) {
            return isset($this->data[$key]) ? $this->data[$key] : null;
        }

        /**
         * Return the name of the column in the database table which contains
         * the primary key ID of the row.
         */
        private function get_id_column_name() {
            if (isset(self::$config['id_column_overrides'][$this->table_name])) {
                return self::$config['id_column_overrides'][$this->table_name];
            } else {
                return self::$config['id_column'];
            }
        }

        /**
         * Get the primary key ID of this object.
         */
        public function id() {
            return $this->get($this->get_id_column_name());
        }

        /**
         * Set a property to a particular value on this object.
         * Flags that property as 'dirty' so it will be saved to the
         * database when save() is called.
         */
        public function set($key, $value) {
            $this->data[$key] = $value;
            $this->dirty_fields[$key] = $value;
        }

        /**
         * Save any fields which have been modified on this object
         * to the database.
         */
        public function save() {
            $query = array();
            $values = array_values($this->dirty_fields);

            if ($this->update_or_insert == self::UPDATE) {

                // If there are no dirty values, do nothing
                if (count($values) == 0) {
                    return true;
                }

                $query[] = "UPDATE";
                $query[] = $this->table_name;
                $query[] = "SET";

                $field_list = array();
                foreach ($this->dirty_fields as $key => $value) {
                    $field_list[] = "$key = ?";
                }
                $query[] = join(", ", $field_list);

                $query[] = "WHERE";
                $query[] = $this->get_id_column_name();
                $query[] = "= ?";
                $values[] = $this->id();

            } else {
                $query[] = "INSERT INTO"; 
                $query[] = $this->table_name;
                $query[] = "(" . join(", ", array_keys($this->dirty_fields)) . ")";
                $query[] = "VALUES";

                $placeholders = array();
                $dirty_field_count = count($this->dirty_fields);
                for ($i = 0; $i < $dirty_field_count; $i++) {
                    $placeholders[] = "?";
                }

                $query[] = "(" . join(", ", $placeholders) . ")";
            }

            $query = join(" ", $query);
            self::setup_db();
            $statement = self::$db->prepare($query);
            $success = $statement->execute($values);

            // If we've just inserted a new record, set the ID of this object
            if ($this->update_or_insert == self::INSERT) {
                $this->update_or_insert = self::UPDATE;
                $this->data[$this->get_id_column_name()] = self::$db->lastInsertId();
            }

            return $success;
        }

        /**
         * Delete this record from the database
         */
        public function delete() {
            $query = join(" ", array(
                "DELETE FROM",
                $this->table_name,
                "WHERE",
                $this->get_id_column_name(),
                "= ?",
            ));
            self::setup_db();
            $statement = self::$db->prepare($query);
            return $statement->execute(array($this->id()));
        }

        // --------------------- //
        // --- MAGIC METHODS --- //
        // --------------------- //
        public function __get($key) {
            return $this->get($key);
        }

        public function __set($key, $value) {
            $this->set($key, $value);
        }
    }

