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

        // Where condition array keys
        const WHERE_FRAGMENT = 0;
        const WHERE_VALUES = 1;

        // ------------------------ //
        // --- CLASS PROPERTIES --- //
        // ------------------------ //

        // Class configuration
        protected static $_config = array(
            'connection_string' => 'sqlite::memory:',
            'id_column' => 'id',
            'id_column_overrides' => array(),
            'error_mode' => PDO::ERRMODE_EXCEPTION,
            'username' => null,
            'password' => null,
            'driver_options' => null,
            'identifier_quote_character' => null, // if this is null, will be autodetected
            'logging' => false,
            'caching' => false,
        );

        // Database connection, instance of the PDO class
        protected static $_db;

        // Last query run, only populated if logging is enabled
        protected static $_last_query;

        // Log of all queries run, only populated if logging is enabled
        protected static $_query_log = array();

        // Query cache, only used if query caching is enabled
        protected static $_query_cache = array();

        // --------------------------- //
        // --- INSTANCE PROPERTIES --- //
        // --------------------------- //

        // The name of the table the current ORM instance is associated with
        protected $_table_name;

        // Alias for the table to be used in SELECT queries
        protected $_table_alias = null;

        // Values to be bound to the query
        protected $_values = array();

        // Columns to select in the result
        protected $_result_columns = array('*');

        // Are we using the default result column or have these been manually changed?
        protected $_using_default_result_columns = true;

        // Join sources
        protected $_join_sources = array();

        // Should the query include a DISTINCT keyword?
        protected $_distinct = false;

        // Is this a raw query?
        protected $_is_raw_query = false;

        // The raw query
        protected $_raw_query = '';

        // The raw query parameters
        protected $_raw_parameters = array();

        // Array of WHERE clauses
        protected $_where_conditions = array();

        // LIMIT
        protected $_limit = null;

        // OFFSET
        protected $_offset = null;

        // ORDER BY
        protected $_order_by = array();

        // GROUP BY
        protected $_group_by = array();

        // The data for a hydrated instance of the class
        protected $_data = array();

        // Fields that have been modified during the
        // lifetime of the object
        protected $_dirty_fields = array();

        // Is this a new object (has create() been called)?
        protected $_is_new = false;

        // Name of the column to use as the primary key for
        // this instance only. Overrides the config settings.
        protected $_instance_id_column = null;

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
            self::$_config[$key] = $value;
        }

        /**
         * Despite its slightly odd name, this is actually the factory
         * method used to acquire instances of the class. It is named
         * this way for the sake of a readable interface, ie
         * ORM::for_table('table_name')->find_one()-> etc. As such,
         * this will normally be the first method called in a chain.
         */
        public static function forTable($table_name) {
            self::_setupDb();
            return new self($table_name);
        }

        /**
         * Set up the database connection used by the class.
         */
        protected static function _setupDb() {
            if (!is_object(self::$_db)) {
                $connection_string = self::$_config['connection_string'];
                $username = self::$_config['username'];
                $password = self::$_config['password'];
                $driver_options = self::$_config['driver_options'];
                $db = new PDO($connection_string, $username, $password, $driver_options);
                $db->setAttribute(PDO::ATTR_ERRMODE, self::$_config['error_mode']);
                self::set_db($db);
            }
        }

        /**
         * Set the PDO object used by Idiorm to communicate with the database.
         * This is public in case the ORM should use a ready-instantiated
         * PDO object as its database connection.
         */
        public static function setDb($db) {
            self::$_db = $db;
            self::_setupIdentifierQuoteCharacter();
        }

        /**
         * Detect and initialise the character used to quote identifiers
         * (table names, column names etc). If this has been specified
         * manually using ORM::configure('identifier_quote_character', 'some-char'),
         * this will do nothing.
         */
        public static function _setupIdentifierQuoteCharacter() {
            if (is_null(self::$_config['identifier_quote_character'])) {
                self::$_config['identifier_quote_character'] = self::_detectIdentifierQuoteCharacter();
            }
        }

        /**
         * Return the correct character used to quote identifiers (table
         * names, column names etc) by looking at the driver being used by PDO.
         */
        protected static function _detectIdentifierQuoteCharacter() {
            switch(self::$_db->getAttribute(PDO::ATTR_DRIVER_NAME)) {
                case 'pgsql':
                case 'sqlsrv':
                case 'dblib':
                case 'mssql':
                case 'sybase':
                    return '"';
                case 'mysql':
                case 'sqlite':
                case 'sqlite2':
                default:
                    return '`';
            }
        }

        /**
         * Returns the PDO instance used by the the ORM to communicate with
         * the database. This can be called if any low-level DB access is
         * required outside the class.
         */
        public static function getDb() {
            self::_setupDb(); // required in case this is called before Idiorm is instantiated
            return self::$_db;
        }

        /**
         * Add a query to the internal query log. Only works if the
         * 'logging' config option is set to true.
         *
         * This works by manually binding the parameters to the query - the
         * query isn't executed like this (PDO normally passes the query and
         * parameters to the database which takes care of the binding) but
         * doing it this way makes the logged queries more readable.
         */
        protected static function _logQuery($query, $parameters) {
            // If logging is not enabled, do nothing
            if (!self::$_config['logging']) {
                return false;
            }

            if (count($parameters) > 0) {
                // Escape the parameters
                $parameters = array_map(array(self::$_db, 'quote'), $parameters);

                // Replace placeholders in the query for vsprintf
                $query = str_replace("?", "%s", $query);

                // Replace the question marks in the query with the parameters
                $bound_query = vsprintf($query, $parameters);
            } else {
                $bound_query = $query;
            }

            self::$_last_query = $bound_query;
            self::$_query_log[] = $bound_query;
            return true;
        }

        /**
         * Get the last query executed. Only works if the
         * 'logging' config option is set to true. Otherwise
         * this will return null.
         */
        public static function getLastQuery() {
            return self::$_last_query;
        }

        /**
         * Get an array containing all the queries run up to
         * now. Only works if the 'logging' config option is
         * set to true. Otherwise returned array will be empty.
         */
        public static function getQueryLog() {
            return self::$_query_log;
        }

        // ------------------------ //
        // --- INSTANCE METHODS --- //
        // ------------------------ //

        /**
         * "Private" constructor; shouldn't be called directly.
         * Use the ORM::for_table factory method instead.
         */
        protected function __construct($table_name, $data=array()) {
            $this->_table_name = $table_name;
            $this->_data = $data;
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
            $this->_is_new = true;
            if (!is_null($data)) {
                return $this->hydrate($data)->forceAllDirty();
            }
            return $this;
        }

        /**
         * Specify the ID column to use for this instance or array of instances only.
         * This overrides the id_column and id_column_overrides settings.
         *
         * This is mostly useful for libraries built on top of Idiorm, and will
         * not normally be used in manually built queries. If you don't know why
         * you would want to use this, you should probably just ignore it.
         */
        public function useIdColumn($id_column) {
            $this->_instance_id_column = $id_column;
            return $this;
        }

        /**
         * Create an ORM instance from the given row (an associative
         * array of data fetched from the database)
         */
        protected function _createInstanceFromRow($row) {
            $instance = self::forTable($this->_table_name);
            $instance->useIdColumn($this->_instance_id_column);
            $instance->hydrate($row);
            return $instance;
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
        public function findOne($id=null) {
            if (!is_null($id)) {
                $this->whereIdIs($id);
            }
            $this->limit(1);
            $rows = $this->_run();

            if (empty($rows)) {
                return false;
            }

            return $this->_createInstanceFromRow($rows[0]);
        }

        /**
         * Tell the ORM that you are expecting multiple results
         * from your query, and execute it. Will return an array
         * of instances of the ORM class, or an empty array if
         * no rows were returned.
         */
        public function findMany() {
            $rows = $this->_run();
            return array_map(array($this, '_create_instance_from_row'), $rows);
        }

        /**
         * Tell the ORM that you wish to execute a COUNT query.
         * Will return an integer representing the number of
         * rows returned.
         */
        public function count() {
            $this->selectExpr('COUNT(*)', 'count');
            $result = $this->findOne();
            return ($result !== false && isset($result->count)) ? (int) $result->count : 0;
        }

         /**
         * This method can be called to hydrate (populate) this
         * instance of the class from an associative array of data.
         * This will usually be called only from inside the class,
         * but it's public in case you need to call it directly.
         */
        public function hydrate($data=array()) {
            $this->_data = $data;
            return $this;
        }

        /**
         * Force the ORM to flag all the fields in the $data array
         * as "dirty" and therefore update them when save() is called.
         */
        public function forceAllDirty() {
            $this->_dirty_fields = $this->_data;
            return $this;
        }

        /**
         * Perform a raw query. The query should contain placeholders,
         * in either named or question mark style, and the parameters
         * should be an array of values which will be bound to the
         * placeholders in the query. If this method is called, all
         * other query building methods will be ignored.
         */
        public function rawQuery($query, $parameters) {
            $this->_is_raw_query = true;
            $this->_raw_query = $query;
            $this->_raw_parameters = $parameters;
            return $this;
        }

        /**
         * Add an alias for the main table to be used in SELECT queries
         */
        public function tableAlias($alias) {
            $this->_table_alias = $alias;
            return $this;
        }

        /**
         * Internal method to add an unquoted expression to the set
         * of columns returned by the SELECT query. The second optional
         * argument is the alias to return the expression as.
         */
        protected function _addResultColumn($expr, $alias=null) {
            if (!is_null($alias)) {
                $expr .= " AS " . $this->_quoteIdentifier($alias);
            }

            if ($this->_using_default_result_columns) {
                $this->_result_columns = array($expr);
                $this->_using_default_result_columns = false;
            } else {
                $this->_result_columns[] = $expr;
            }
            return $this;
        }

        /**
         * Add a column to the list of columns returned by the SELECT
         * query. This defaults to '*'. The second optional argument is
         * the alias to return the column as.
         */
        public function select($column, $alias=null) {
            $column = $this->_quoteIdentifier($column);
            return $this->_addResultColumn($column, $alias);
        }

        /**
         * Add an unquoted expression to the list of columns returned
         * by the SELECT query. The second optional argument is
         * the alias to return the column as.
         */
        public function selectExpr($expr, $alias=null) {
            return $this->_addResultColumn($expr, $alias);
        }

        /**
         * Add a DISTINCT keyword before the list of columns in the SELECT query
         */
        public function distinct() {
            $this->_distinct = true;
            return $this;
        }

        /**
         * Internal method to add a JOIN source to the query.
         *
         * The join_operator should be one of INNER, LEFT OUTER, CROSS etc - this
         * will be prepended to JOIN.
         *
         * The table should be the name of the table to join to.
         *
         * The constraint may be either a string or an array with three elements. If it
         * is a string, it will be compiled into the query as-is, with no escaping. The
         * recommended way to supply the constraint is as an array with three elements:
         *
         * first_column, operator, second_column
         *
         * Example: array('user.id', '=', 'profile.user_id')
         *
         * will compile to
         *
         * ON `user`.`id` = `profile`.`user_id`
         *
         * The final (optional) argument specifies an alias for the joined table.
         */
        protected function _addJoinSource($join_operator, $table, $constraint, $table_alias=null) {

            $join_operator = trim("{$join_operator} JOIN");

            $table = $this->_quoteIdentifier($table);

            // Add table alias if present
            if (!is_null($table_alias)) {
                $table_alias = $this->_quoteIdentifier($table_alias);
                $table .= " {$table_alias}";
            }

            // Build the constraint
            if (is_array($constraint)) {
                list($first_column, $operator, $second_column) = $constraint;
                $first_column = $this->_quoteIdentifier($first_column);
                $second_column = $this->_quoteIdentifier($second_column);
                $constraint = "{$first_column} {$operator} {$second_column}";
            }

            $this->_join_sources[] = "{$join_operator} {$table} ON {$constraint}";
            return $this;
        }

        /**
         * Add a simple JOIN source to the query
         */
        public function join($table, $constraint, $table_alias=null) {
            return $this->_addJoinSource("", $table, $constraint, $table_alias);
        }

        /**
         * Add an INNER JOIN souce to the query
         */
        public function innerJoin($table, $constraint, $table_alias=null) {
            return $this->_addJoinSource("INNER", $table, $constraint, $table_alias);
        }

        /**
         * Add a LEFT OUTER JOIN souce to the query
         */
        public function leftOuterJoin($table, $constraint, $table_alias=null) {
            return $this->_addJoinSource("LEFT OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Add an RIGHT OUTER JOIN souce to the query
         */
        public function rightOuterJoin($table, $constraint, $table_alias=null) {
            return $this->_addJoinSource("RIGHT OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Add an FULL OUTER JOIN souce to the query
         */
        public function fullOuterJoin($table, $constraint, $table_alias=null) {
            return $this->_addJoinSource("FULL OUTER", $table, $constraint, $table_alias);
        }

        /**
         * Internal method to add a WHERE condition to the query
         */
        protected function _addWhere($fragment, $values=array()) {
            if (!is_array($values)) {
                $values = array($values);
            }
            $this->_where_conditions[] = array(
                self::WHERE_FRAGMENT => $fragment,
                self::WHERE_VALUES => $values,
            );
            return $this;
        }

        /**
         * Helper method to compile a simple COLUMN SEPARATOR VALUE
         * style WHERE condition into a string and value ready to
         * be passed to the _add_where method. Avoids duplication
         * of the call to _quote_identifier
         */
        protected function _addSimpleWhere($column_name, $separator, $value) {
            $column_name = $this->_quoteIdentifier($column_name);
            return $this->_addWhere("{$column_name} {$separator} ?", $value);
        }

        /**
         * Return a string containing the given number of question marks,
         * separated by commas. Eg "?, ?, ?"
         */
        protected function _createPlaceholders($number_of_placeholders) {
            return join(", ", array_fill(0, $number_of_placeholders, "?"));
        }

        /**
         * Add a WHERE column = value clause to your query. Each time
         * this is called in the chain, an additional WHERE will be
         * added, and these will be ANDed together when the final query
         * is built.
         */
        public function where($column_name, $value) {
            return $this->whereEqual($column_name, $value);
        }

        /**
         * More explicitly named version of for the where() method.
         * Can be used if preferred.
         */
        public function whereEqual($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '=', $value);
        }

        /**
         * Add a WHERE column != value clause to your query.
         */
        public function whereNotEqual($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '!=', $value);
        }

        /**
         * Special method to query the table by its primary key
         */
        public function whereIdIs($id) {
            return $this->where($this->_getIdColumnName(), $id);
        }

        /**
         * Add a WHERE ... LIKE clause to your query.
         */
        public function whereLike($column_name, $value) {
            return $this->_addSimpleWhere($column_name, 'LIKE', $value);
        }

        /**
         * Add where WHERE ... NOT LIKE clause to your query.
         */
        public function whereNotLike($column_name, $value) {
            return $this->_addSimpleWhere($column_name, 'NOT LIKE', $value);
        }

        /**
         * Add a WHERE ... > clause to your query
         */
        public function whereGt($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '>', $value);
        }

        /**
         * Add a WHERE ... < clause to your query
         */
        public function whereLt($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '<', $value);
        }

        /**
         * Add a WHERE ... >= clause to your query
         */
        public function whereGte($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '>=', $value);
        }

        /**
         * Add a WHERE ... <= clause to your query
         */
        public function whereLte($column_name, $value) {
            return $this->_addSimpleWhere($column_name, '<=', $value);
        }

        /**
         * Add a WHERE ... IN clause to your query
         */
        public function whereIn($column_name, $values) {
            $column_name = $this->_quoteIdentifier($column_name);
            $placeholders = $this->_createPlaceholders(count($values));
            return $this->_addWhere("{$column_name} IN ({$placeholders})", $values);
        }

        /**
         * Add a WHERE ... NOT IN clause to your query
         */
        public function whereNotIn($column_name, $values) {
            $column_name = $this->_quoteIdentifier($column_name);
            $placeholders = $this->_createPlaceholders(count($values));
            return $this->_addWhere("{$column_name} NOT IN ({$placeholders})", $values);
        }

        /**
         * Add a WHERE column IS NULL clause to your query
         */
        public function whereNull($column_name) {
            $column_name = $this->_quoteIdentifier($column_name);
            return $this->_addWhere("{$column_name} IS NULL");
        }

        /**
         * Add a WHERE column IS NOT NULL clause to your query
         */
        public function whereNotNull($column_name) {
            $column_name = $this->_quoteIdentifier($column_name);
            return $this->_addWhere("{$column_name} IS NOT NULL");
        }

        /**
         * Add a raw WHERE clause to the query. The clause should
         * contain question mark placeholders, which will be bound
         * to the parameters supplied in the second argument.
         */
        public function whereRaw($clause, $parameters=array()) {
            return $this->_addWhere($clause, $parameters);
        }

        /**
         * Add a LIMIT to the query
         */
        public function limit($limit) {
            $this->_limit = $limit;
            return $this;
        }

        /**
         * Add an OFFSET to the query
         */
        public function offset($offset) {
            $this->_offset = $offset;
            return $this;
        }

        /**
         * Add an ORDER BY clause to the query
         */
        protected function _addOrderBy($column_name, $ordering) {
            $column_name = $this->_quoteIdentifier($column_name);
            $this->_order_by[] = "{$column_name} {$ordering}";
            return $this;
        }

        /**
         * Add an ORDER BY column DESC clause
         */
        public function orderByDesc($column_name) {
            return $this->_addOrderBy($column_name, 'DESC');
        }

        /**
         * Add an ORDER BY column ASC clause
         */
        public function orderByAsc($column_name) {
            return $this->_addOrderBy($column_name, 'ASC');
        }

        /**
         * Add a column to the list of columns to GROUP BY
         */
        public function groupBy($column_name) {
            $column_name = $this->_quoteIdentifier($column_name);
            $this->_group_by[] = $column_name;
            return $this;
        }

        /**
         * Build a SELECT statement based on the clauses that have
         * been passed to this instance by chaining method calls.
         */
        protected function _buildSelect() {
            // If the query is raw, just set the $this->_values to be
            // the raw query parameters and return the raw query
            if ($this->_is_raw_query) {
                $this->_values = $this->_raw_parameters;
                return $this->_raw_query;
            }

            // Build and return the full SELECT statement by concatenating
            // the results of calling each separate builder method.
            return $this->_joinIfNotEmpty(" ", array(
                $this->_buildSelectStart(),
                $this->_buildJoin(),
                $this->_buildWhere(),
                $this->_buildGroupBy(),
                $this->_buildOrderBy(),
                $this->_buildLimit(),
                $this->_buildOffset(),
            ));
        }

        /**
         * Build the start of the SELECT statement
         */
        protected function _buildSelectStart() {
            $result_columns = join(', ', $this->_result_columns);

            if ($this->_distinct) {
                $result_columns = 'DISTINCT ' . $result_columns;
            }

            $fragment = "SELECT {$result_columns} FROM " . $this->_quoteIdentifier($this->_table_name);

            if (!is_null($this->_table_alias)) {
                $fragment .= " " . $this->_quoteIdentifier($this->_table_alias);
            }
            return $fragment;
        }

        /**
         * Build the JOIN sources
         */
        protected function _buildJoin() {
            if (count($this->_join_sources) === 0) {
                return '';
            }

            return join(" ", $this->_join_sources);
        }

        /**
         * Build the WHERE clause(s)
         */
        protected function _buildWhere() {
            // If there are no WHERE clauses, return empty string
            if (count($this->_where_conditions) === 0) {
                return '';
            }

            $where_conditions = array();
            foreach ($this->_where_conditions as $condition) {
                $where_conditions[] = $condition[self::WHERE_FRAGMENT];
                $this->_values = array_merge($this->_values, $condition[self::WHERE_VALUES]);
            }

            return "WHERE " . join(" AND ", $where_conditions);
        }

        /**
         * Build GROUP BY
         */
        protected function _buildGroupBy() {
            if (count($this->_group_by) === 0) {
                return '';
            }
            return "GROUP BY " . join(", ", $this->_group_by);
        }

        /**
         * Build ORDER BY
         */
        protected function _buildOrderBy() {
            if (count($this->_order_by) === 0) {
                return '';
            }
            return "ORDER BY " . join(", ", $this->_order_by);
        }

        /**
         * Build LIMIT
         */
        protected function _buildLimit() {
            if (!is_null($this->_limit)) {
                return "LIMIT " . $this->_limit;
            }
            return '';
        }

        /**
         * Build OFFSET
         */
        protected function _buildOffset() {
            if (!is_null($this->_offset)) {
                return "OFFSET " . $this->_offset;
            }
            return '';
        }

        /**
         * Wrapper around PHP's join function which
         * only adds the pieces if they are not empty.
         */
        protected function _joinIfNotEmpty($glue, $pieces) {
            $filtered_pieces = array();
            foreach ($pieces as $piece) {
                if (is_string($piece)) {
                    $piece = trim($piece);
                }
                if (!empty($piece)) {
                    $filtered_pieces[] = $piece;
                }
            }
            return join($glue, $filtered_pieces);
        }

        /**
         * Quote a string that is used as an identifier
         * (table names, column names etc). This method can
         * also deal with dot-separated identifiers eg table.column
         */
        protected function _quoteIdentifier($identifier) {
            $parts = explode('.', $identifier);
            $parts = array_map(array($this, '_quote_identifier_part'), $parts);
            return join('.', $parts);
        }

        /**
         * This method performs the actual quoting of a single
         * part of an identifier, using the identifier quote
         * character specified in the config (or autodetected).
         */
        protected function _quoteIdentifierPart($part) {
            if ($part === '*') {
                return $part;
            }
            $quote_character = self::$_config['identifier_quote_character'];
            return $quote_character . $part . $quote_character;
        }

        /**
         * Create a cache key for the given query and parameters.
         */
        protected static function _createCacheKey($query, $parameters) {
            $parameter_string = join(',', $parameters);
            $key = $query . ':' . $parameter_string;
            return sha1($key);
        }

        /**
         * Check the query cache for the given cache key. If a value
         * is cached for the key, return the value. Otherwise, return false.
         */
        protected static function _checkQueryCache($cache_key) {
            if (isset(self::$_query_cache[$cache_key])) {
                return self::$_query_cache[$cache_key];
            }
            return false;
        }

        /**
         * Clear the query cache
         */
        public static function clearCache() {
            self::$_query_cache = array();
        }

        /**
         * Add the given value to the query cache.
         */
        protected static function _cacheQueryResult($cache_key, $value) {
            self::$_query_cache[$cache_key] = $value;
        }

        /**
         * Execute the SELECT query that has been built up by chaining methods
         * on this class. Return an array of rows as associative arrays.
         */
        protected function _run() {
            $query = $this->_buildSelect();
            $caching_enabled = self::$_config['caching'];

            if ($caching_enabled) {
                $cache_key = self::_createCacheKey($query, $this->_values);
                $cached_result = self::_checkQueryCache($cache_key);

                if ($cached_result !== false) {
                    return $cached_result;
                }
            }

            self::_logQuery($query, $this->_values);
            $statement = self::$_db->prepare($query);
            $statement->execute($this->_values);

            $rows = array();
            while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
                $rows[] = $row;
            }

            if ($caching_enabled) {
                self::_cacheQueryResult($cache_key, $rows);
            }

            return $rows;
        }

        /**
         * Return the raw data wrapped by this ORM
         * instance as an associative array. Column
         * names may optionally be supplied as arguments,
         * if so, only those keys will be returned.
         */
        public function asArray() {
            if (func_num_args() === 0) {
                return $this->_data;
            }
            $args = func_get_args();
            return array_intersect_key($this->_data, array_flip($args));
        }

        /**
         * Return the value of a property of this object (database row)
         * or null if not present.
         */
        public function get($key) {
            return isset($this->_data[$key]) ? $this->_data[$key] : null;
        }

        /**
         * Return the name of the column in the database table which contains
         * the primary key ID of the row.
         */
        protected function _getIdColumnName() {
            if (!is_null($this->_instance_id_column)) {
                return $this->_instance_id_column;
            }
            if (isset(self::$_config['id_column_overrides'][$this->_table_name])) {
                return self::$_config['id_column_overrides'][$this->_table_name];
            } else {
                return self::$_config['id_column'];
            }
        }

        /**
         * Get the primary key ID of this object.
         */
        public function id() {
            return $this->get($this->_getIdColumnName());
        }

        /**
         * Set a property to a particular value on this object.
         * Flags that property as 'dirty' so it will be saved to the
         * database when save() is called.
         */
        public function set($key, $value) {
            $this->_data[$key] = $value;
            $this->_dirty_fields[$key] = $value;
        }

        /**
         * Check whether the given field has been changed since this
         * object was saved.
         */
        public function isDirty($key) {
            return isset($this->_dirty_fields[$key]);
        }

        /**
         * Save any fields which have been modified on this object
         * to the database.
         */
        public function save() {
            $query = array();
            $values = array_values($this->_dirty_fields);

            if (!$this->_is_new) { // UPDATE
                // If there are no dirty values, do nothing
                if (count($values) == 0) {
                    return true;
                }
                $query = $this->_buildUpdate();
                $values[] = $this->id();
            } else { // INSERT
                $query = $this->_buildInsert();
            }

            self::_logQuery($query, $values);
            $statement = self::$_db->prepare($query);
            $success = $statement->execute($values);

            // If we've just inserted a new record, set the ID of this object
            if ($this->_is_new) {
                $this->_is_new = false;
                if (is_null($this->id())) {
                    $this->_data[$this->_getIdColumnName()] = self::$_db->lastInsertId();
                }
            }

            $this->_dirty_fields = array();
            return $success;
        }

        /**
         * Build an UPDATE query
         */
        protected function _buildUpdate() {
            $query = array();
            $query[] = "UPDATE {$this->_quoteIdentifier($this->_table_name)} SET";

            $field_list = array();
            foreach ($this->_dirty_fields as $key => $value) {
                $field_list[] = "{$this->_quoteIdentifier($key)} = ?";
            }
            $query[] = join(", ", $field_list);
            $query[] = "WHERE";
            $query[] = $this->_quoteIdentifier($this->_getIdColumnName());
            $query[] = "= ?";
            return join(" ", $query);
        }

        /**
         * Build an INSERT query
         */
        protected function _buildInsert() {
            $query = array();
            $query[] = "INSERT INTO";
            $query[] = $this->_quoteIdentifier($this->_table_name);
            $field_list = array_map(array($this, '_quote_identifier'), array_keys($this->_dirty_fields));
            $query[] = "(" . join(", ", $field_list) . ")";
            $query[] = "VALUES";

            $placeholders = $this->_createPlaceholders(count($this->_dirty_fields));
            $query[] = "({$placeholders})";
            return join(" ", $query);
        }

        /**
         * Delete this record from the database
         */
        public function delete() {
            $query = join(" ", array(
                "DELETE FROM",
                $this->_quoteIdentifier($this->_table_name),
                "WHERE",
                $this->_quoteIdentifier($this->_getIdColumnName()),
                "= ?",
            ));
            $params = array($this->id());
            self::_logQuery($query, $params);
            $statement = self::$_db->prepare($query);
            return $statement->execute($params);
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

        public function __isset($key) {
            return isset($this->_data[$key]);
        }
    }

