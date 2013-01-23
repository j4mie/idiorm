<?php
    /*
     * Basic testing for Idiorm
     *
     * Checks that the generated SQL is correct
     *
     */

    require_once dirname(__FILE__) . "/../idiorm.php";
    require_once dirname(__FILE__) . "/test_classes.php";

    // Enable logging
    ORM::configure('logging', true);

    // Set up the mock database connection
    $db = new MockPDO('sqlite::memory:');
    ORM::set_db($db);

    ORM::for_table('widget')->find_many();
    $expected = "SELECT * FROM `widget`";
    Tester::check_equal_query("Basic unfiltered find_many query", $expected);

    ORM::for_table('widget')->find_one();
    $expected = "SELECT * FROM `widget` LIMIT 1";
    Tester::check_equal_query("Basic unfiltered find_one query", $expected);

    ORM::for_table('widget')->where_id_is(5)->find_one();
    $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
    Tester::check_equal_query("where_id_is method", $expected);

    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
    Tester::check_equal_query("Filtering on ID passed into find_one method", $expected);

    ORM::for_table('widget')->count();
    $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
    Tester::check_equal_query("COUNT query", $expected);

    ORM::for_table('person')->max('height');
    $expected = "SELECT MAX(`height`) AS `max` FROM `person` LIMIT 1";
    Tester::check_equal_query("MAX query", $expected);

    ORM::for_table('person')->min('height');
    $expected = "SELECT MIN(`height`) AS `min` FROM `person` LIMIT 1";
    Tester::check_equal_query("MIN query", $expected);

    ORM::for_table('person')->avg('height');
    $expected = "SELECT AVG(`height`) AS `avg` FROM `person` LIMIT 1";
    Tester::check_equal_query("AVG query", $expected);

    ORM::for_table('person')->sum('height');
    $expected = "SELECT SUM(`height`) AS `sum` FROM `person` LIMIT 1";
    Tester::check_equal_query("SUM query", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' LIMIT 1";
    Tester::check_equal_query("Single where clause", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND `age` = '10' LIMIT 1";
    Tester::check_equal_query("Multiple WHERE clauses", $expected);

    ORM::for_table('widget')->where_not_equal('name', 'Fred')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` != 'Fred'";
    Tester::check_equal_query("where_not_equal method", $expected);

    ORM::for_table('widget')->where_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` LIKE '%Fred%' LIMIT 1";
    Tester::check_equal_query("where_like method", $expected);

    ORM::for_table('widget')->where_not_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE '%Fred%' LIMIT 1";
    Tester::check_equal_query("where_not_like method", $expected);

    ORM::for_table('widget')->where_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IN ('Fred', 'Joe')";
    Tester::check_equal_query("where_in method", $expected);

    ORM::for_table('widget')->where_not_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` NOT IN ('Fred', 'Joe')";
    Tester::check_equal_query("where_not_in method", $expected);

    ORM::for_table('widget')->limit(5)->find_many();
    $expected = "SELECT * FROM `widget` LIMIT 5";
    Tester::check_equal_query("LIMIT clause", $expected);

    ORM::for_table('widget')->limit(5)->offset(5)->find_many();
    $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
    Tester::check_equal_query("LIMIT and OFFSET clause", $expected);

    ORM::for_table('widget')->order_by_desc('name')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
    Tester::check_equal_query("ORDER BY DESC", $expected);

    ORM::for_table('widget')->order_by_asc('name')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
    Tester::check_equal_query("ORDER BY ASC", $expected);

    ORM::for_table('widget')->order_by_expr('SOUNDEX(`name`)')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY SOUNDEX(`name`) LIMIT 1";
    Tester::check_equal_query("ORDER BY expression", $expected);

    ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
    Tester::check_equal_query("Multiple ORDER BY", $expected);

    ORM::for_table('widget')->group_by('name')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name`";
    Tester::check_equal_query("GROUP BY", $expected);

    ORM::for_table('widget')->group_by('name')->group_by('age')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
    Tester::check_equal_query("Multiple GROUP BY", $expected);

    ORM::for_table('widget')->group_by_expr("FROM_UNIXTIME(`time`, '%Y-%m')")->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY FROM_UNIXTIME(`time`, '%Y-%m')";
    Tester::check_equal_query("GROUP BY expression", $expected);

    ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->find_one();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' LIMIT 1";
    Tester::check_equal_query("Single having clause", $expected);

    ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->having('age', 10)->find_one();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND `age` = '10' LIMIT 1";
    Tester::check_equal_query("Multiple HAVING clauses", $expected);

    ORM::for_table('widget')->group_by('name')->having_not_equal('name', 'Fred')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` != 'Fred'";
    Tester::check_equal_query("having_not_equal method", $expected);

    ORM::for_table('widget')->group_by('name')->having_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` LIKE '%Fred%' LIMIT 1";
    Tester::check_equal_query("having_like method", $expected);

    ORM::for_table('widget')->group_by('name')->having_not_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT LIKE '%Fred%' LIMIT 1";
    Tester::check_equal_query("having_not_like method", $expected);

    ORM::for_table('widget')->group_by('name')->having_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IN ('Fred', 'Joe')";
    Tester::check_equal_query("having_in method", $expected);

    ORM::for_table('widget')->group_by('name')->having_not_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT IN ('Fred', 'Joe')";
    Tester::check_equal_query("having_not_in method", $expected);

    ORM::for_table('widget')->group_by('name')->having_lt('age', 10)->having_gt('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` < '10' AND `age` > '5'";
    Tester::check_equal_query("HAVING less than and greater than", $expected);


    ORM::for_table('widget')->group_by('name')->having_lte('age', 10)->having_gte('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` <= '10' AND `age` >= '5'";
    Tester::check_equal_query("HAVING less than or equal and greater than or equal", $expected);

    ORM::for_table('widget')->group_by('name')->having_null('name')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NULL";
    Tester::check_equal_query("having_null method", $expected);

    ORM::for_table('widget')->group_by('name')->having_not_null('name')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NOT NULL";
    Tester::check_equal_query("having_not_null method", $expected);

    ORM::for_table('widget')->group_by('name')->having_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
    Tester::check_equal_query("Raw HAVING clause", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' ORDER BY `name` ASC LIMIT 5 OFFSET 5";
    Tester::check_equal_query("Complex query", $expected);

    ORM::for_table('widget')->where_lt('age', 10)->where_gt('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` < '10' AND `age` > '5'";
    Tester::check_equal_query("Less than and greater than", $expected);

    ORM::for_table('widget')->where_lte('age', 10)->where_gte('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` <= '10' AND `age` >= '5'";
    Tester::check_equal_query("Less than or equal and greater than or equal", $expected);

        ///////////////////////////////////////////

    ORM::for_table('widget')->where_null('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
    Tester::check_equal_query("where_null method", $expected);

    ORM::for_table('widget')->where_not_null('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
    Tester::check_equal_query("where_not_null method", $expected);

    ORM::for_table('widget')->where_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
    Tester::check_equal_query("Raw WHERE clause", $expected);

    ORM::for_table('widget')->where_raw('STRFTIME("%Y", "now") = ?', array(2012))->find_many();
    $expected = "SELECT * FROM `widget` WHERE STRFTIME(\"%Y\", \"now\") = '2012'";
    Tester::check_equal_query("Raw WHERE clause with '%'", $expected);

    ORM::for_table('widget')->where_raw('`name` = "Fred"')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
    Tester::check_equal_query("Raw WHERE clause with no parameters", $expected);

    ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND `size` = 'large'";
    Tester::check_equal_query("Raw WHERE clause in method chain", $expected);

    ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w')->find_many();
    $expected = "SELECT `w`.* FROM `widget` w";
    Tester::check_equal_query("Raw query", $expected);

    ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->find_many();
    $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
    Tester::check_equal_query("Raw query with parameters", $expected);

    ORM::for_table('widget')->select('name')->find_many();
    $expected = "SELECT `name` FROM `widget`";
    Tester::check_equal_query("Simple result column", $expected);

    ORM::for_table('widget')->select('name')->select('age')->find_many();
    $expected = "SELECT `name`, `age` FROM `widget`";
    Tester::check_equal_query("Multiple simple result columns", $expected);

    ORM::for_table('widget')->select('widget.name')->find_many();
    $expected = "SELECT `widget`.`name` FROM `widget`";
    Tester::check_equal_query("Specify table name and column in result columns", $expected);

    ORM::for_table('widget')->select('widget.name', 'widget_name')->find_many();
    $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
    Tester::check_equal_query("Aliases in result columns", $expected);

    /////@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    ORM::for_table('widget')->select_expr('COUNT(*)', 'count')->find_many();
    $expected = "SELECT COUNT(*) AS `count` FROM `widget`";
    Tester::check_equal_query("Literal expression in result columns", $expected);

    ORM::for_table('widget')->select_many(array('widget_name' => 'widget.name'), 'widget_handle')->find_many();
    $expected = "SELECT `widget`.`name` AS `widget_name`, `widget_handle` FROM `widget`";
    Tester::check_equal_query("Aliases in select many result columns", $expected);

    ORM::for_table('widget')->select_many_expr(array('count' => 'COUNT(*)'), 'SUM(widget_order)')->find_many();
    $expected = "SELECT COUNT(*) AS `count`, SUM(widget_order) FROM `widget`";
    Tester::check_equal_query("Literal expression in select many result columns", $expected);

    ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Simple join", $expected);

    ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_one(5);
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` WHERE `widget`.`id` = '5' LIMIT 1";
    Tester::check_equal_query("Simple join with where_id_is method", $expected);

    ORM::for_table('widget')->inner_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` INNER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Inner join", $expected);

    ORM::for_table('widget')->left_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` LEFT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Left outer join", $expected);

    ORM::for_table('widget')->right_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` RIGHT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Right outer join", $expected);

    ORM::for_table('widget')->full_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` FULL OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Full outer join", $expected);

    ORM::for_table('widget')
        ->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))
        ->join('widget_nozzle', array('widget_nozzle.widget_id', '=', 'widget.id'))
        ->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` JOIN `widget_nozzle` ON `widget_nozzle`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Multiple join sources", $expected);

    ORM::for_table('widget')->table_alias('w')->find_many();
    $expected = "SELECT * FROM `widget` `w`";
    Tester::check_equal_query("Main table alias", $expected);

    ORM::for_table('widget')->join('widget_handle', array('wh.widget_id', '=', 'widget.id'), 'wh')->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `widget`.`id`";
    Tester::check_equal_query("Join with alias", $expected);

    ORM::for_table('widget')->join('widget_handle', "widget_handle.widget_id = widget.id")->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON widget_handle.widget_id = widget.id";
    Tester::check_equal_query("Join with string constraint", $expected);

    ORM::for_table('widget')->distinct()->select('name')->find_many();
    $expected = "SELECT DISTINCT `name` FROM `widget`";
    Tester::check_equal_query("Select with DISTINCT", $expected);

    $widget = ORM::for_table('widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
    Tester::check_equal_query("Insert data", $expected);

    $widget = ORM::for_table('widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->set_expr('added', 'NOW()');
    $widget->save();
    $expected = "INSERT INTO `widget` (`name`, `age`, `added`) VALUES ('Fred', '10', NOW())";
    Tester::check_equal_query("Insert data containing an expression", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
    Tester::check_equal_query("Update data", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->set_expr('added', 'NOW()');
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW() WHERE `id` = '1'";
    Tester::check_equal_query("Update data containing an expression", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->set(array("name" => "Fred", "age" => 10));
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
    Tester::check_equal_query("Update multiple fields", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->set(array("name" => "Fred", "age" => 10));
    $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = GeomFromText('POINT(1.2347 2.3436)') WHERE `id` = '1'";
    Tester::check_equal_query("Update multiple fields containing an expression", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->set(array("name" => "Fred", "age" => 10));
    $widget->set_expr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
    $widget->lat_long = 'unknown';
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = 'unknown' WHERE `id` = '1'";
    Tester::check_equal_query("Update multiple fields containing an expression (override previously set expression with plain value)", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->delete();
    $expected = "DELETE FROM `widget` WHERE `id` = '1'";
    Tester::check_equal_query("Delete data", $expected);

    $widget = ORM::for_table('widget')->where_equal('age', 10)->delete_many();
    $expected = "DELETE FROM `widget` WHERE `age` = '10'";
    Tester::check_equal_query("Delete many", $expected);

    ORM::raw_execute("INSERT OR IGNORE INTO `widget` (`id`, `name`) VALUES (?, ?)", array(1, 'Tolstoy'));
    $expected = "INSERT OR IGNORE INTO `widget` (`id`, `name`) VALUES ('1', 'Tolstoy')";
    Tester::check_equal_query("Raw execute", $expected); // A bit of a silly test, as query is passed through
    // Tests of muliple connections
    define('ALTERNATE', 'alternate');
    ORM::set_db(new MockDifferentPDO('sqlite::memory:'), 'alternate');
    ORM::configure('logging', true, ALTERNATE);

    $person1 = ORM::for_table('person')->find_one();
    $person2 = ORM::for_table('person', ALTERNATE)->find_one();
    $expected = "SELECT * FROM `person` LIMIT 1";

    Tester::check_equal_string("Multiple connection (1)", $person1->name, 'Fred');
    Tester::check_equal_string("Multiple connection (2)", $person2->name, 'Steve');

    $expectedToo = "SELECT * FROM `widget`";
    ORM::raw_execute("SELECT * FROM `widget`", array(), ALTERNATE);

    Tester::check_equal_string(
        "Multiple connection log (1)",
        ORM::get_last_query(ORM::DEFAULT_CONNECTION),
        $expected
    );
    Tester::check_equal_string(
        "Multiple connection query log (2)",
        ORM::get_last_query(),
        $expectedToo
    );
    Tester::check_equal_string(
        "Multiple connection query log (3)",
        ORM::get_last_query(ALTERNATE),
        $expectedToo
    );

    $widget = ORM::for_table('widget')->create();
    $widget['name'] = "Fred";
    $widget['age'] = 10;
    $widget->save();
    $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
    Tester::check_equal_query("Insert data using ArrayAccess", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->find_one();
    $statement = ORM::get_last_statement();
    $test_name = 'get_last_statement() returned MockPDOStatement';
    if($statement instanceOf MockPDOStatement) {
        Tester::report_pass($test_name);
    } else {
        $actual = gettype($statement);
        if('object' == $actual) {
            $actual = get_class($statement);
        }
        Tester::report_failure($test_name, 'MockPDOStatement', $actual);
    }

    // Regression tests

    $widget = ORM::for_table('widget')->select('widget.*')->find_one();
    $expected = "SELECT `widget`.* FROM `widget` LIMIT 1";
    Tester::check_equal_query("Issue #12 - incorrect quoting of column wildcard", $expected);

    $widget = ORM::for_table('widget')->where_raw('username LIKE "ben%"')->find_many();
    $expected = 'SELECT * FROM `widget` WHERE username LIKE "ben%"';
    Tester::check_equal_query('Issue #57 - _log_query method raises a warning when query contains "%"', $expected);

    $widget = ORM::for_table('widget')->where_raw('comments LIKE "has been released?%"')->find_many();
    $expected = 'SELECT * FROM `widget` WHERE comments LIKE "has been released?%"';
    Tester::check_equal_query('Issue #57 - _log_query method raises a warning when query contains "?"', $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->set('ad`ded', '2013-01-04');
    $widget->save();
    $expected = "UPDATE `widget` SET `ad``ded` = '2013-01-04' WHERE `id` = '1'";
    Tester::check_equal_query('Issue #74 - escaping quote symbols in "_quote_identifier_part"', $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->set_expr('added', 'NOW()');
    $widget->save();
    $expected = "UPDATE `widget` SET `added` = NOW() WHERE `id` = '1'";
    Tester::check_equal_query("Issue #90 - When using set_expr alone it doesn't trigger query creation", $expected);

    // Tests that alter Idiorm's config are done last

    ORM::configure('id_column', 'primary_key');
    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `primary_key` = '5' LIMIT 1";
    Tester::check_equal_query("Setting: id_column", $expected);

    ORM::configure('id_column_overrides', array(
        'widget' => 'widget_id',
        'widget_handle' => 'widget_handle_id',
    ));

    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `widget_id` = '5' LIMIT 1";
    Tester::check_equal_query("Setting: id_column_overrides, first test", $expected);

    ORM::for_table('widget_handle')->find_one(5);
    $expected = "SELECT * FROM `widget_handle` WHERE `widget_handle_id` = '5' LIMIT 1";
    Tester::check_equal_query("Setting: id_column_overrides, second test", $expected);

    ORM::for_table('widget_nozzle')->find_one(5);
    $expected = "SELECT * FROM `widget_nozzle` WHERE `primary_key` = '5' LIMIT 1";
    Tester::check_equal_query("Setting: id_column_overrides, third test", $expected);

    ORM::for_table('widget')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal_query("Instance ID column, first test", $expected);

    ORM::for_table('widget_handle')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget_handle` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal_query("Instance ID column, second test", $expected);

    ORM::for_table('widget_nozzle')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget_nozzle` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal_query("Instance ID column, third test", $expected);

    // Test caching. This is a bit of a hack.
    ORM::configure('caching', true);
    ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
    ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
    $expected = ORM::get_last_query();
    ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this shouldn't run a query!
    Tester::check_equal_query("Caching, same query not run twice", $expected);

    // Test caching with multiple connections (also a bit of a hack)
    ORM::configure('caching', true, ALTERNATE);
    ORM::for_table('widget', ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one();
    ORM::for_table('widget', ALTERNATE)->where('name', 'Tom')->where('age', 120)->find_one();
    $expectedToo = ORM::get_last_query();
    ORM::for_table('widget', ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one(); // this shouldn't run a query!
    Tester::check_equal_query(
        "Multi-connection caching, same query not run twice, on alternate connection",
        $expectedToo
    );

    ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this still shouldn't run a query!
    Tester::check_equal_string(
        "Multi-conneciton caching, same query not run twice across connections",
        ORM::get_last_query(ORM::DEFAULT_CONNECTION),
        $expected
    );

    Tester::report();