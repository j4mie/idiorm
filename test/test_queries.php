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

    // Set up the dummy database connection
    $db = new DummyPDO('sqlite::memory:');
    ORM::set_db($db);

    ORM::for_table('widget')->find_many();
    $expected = "SELECT * FROM `widget`";
    Tester::check_equal("Basic unfiltered find_many query", $expected);

    ORM::for_table('widget')->find_one();
    $expected = "SELECT * FROM `widget` LIMIT 1";
    Tester::check_equal("Basic unfiltered find_one query", $expected);

    ORM::for_table('widget')->where_id_is(5)->find_one();
    $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
    Tester::check_equal("where_id_is method", $expected);

    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
    Tester::check_equal("Filtering on ID passed into find_one method", $expected);

    ORM::for_table('widget')->count();
    $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
    Tester::check_equal("COUNT query", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' LIMIT 1";
    Tester::check_equal("Single where clause", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND `age` = '10' LIMIT 1";
    Tester::check_equal("Multiple WHERE clauses", $expected);

    ORM::for_table('widget')->where_not_equal('name', 'Fred')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` != 'Fred'";
    Tester::check_equal("where_not_equal method", $expected);

    ORM::for_table('widget')->where_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` LIKE '%Fred%' LIMIT 1";
    Tester::check_equal("where_like method", $expected);

    ORM::for_table('widget')->where_not_like('name', '%Fred%')->find_one();
    $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE '%Fred%' LIMIT 1";
    Tester::check_equal("where_not_like method", $expected);

    ORM::for_table('widget')->where_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IN ('Fred', 'Joe')";
    Tester::check_equal("where_in method", $expected);

    ORM::for_table('widget')->where_not_in('name', array('Fred', 'Joe'))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` NOT IN ('Fred', 'Joe')";
    Tester::check_equal("where_not_in method", $expected);

    ORM::for_table('widget')->limit(5)->find_many();
    $expected = "SELECT * FROM `widget` LIMIT 5";
    Tester::check_equal("LIMIT clause", $expected);

    ORM::for_table('widget')->limit(5)->offset(5)->find_many();
    $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
    Tester::check_equal("LIMIT and OFFSET clause", $expected);

    ORM::for_table('widget')->order_by_desc('name')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
    Tester::check_equal("ORDER BY DESC", $expected);

    ORM::for_table('widget')->order_by_asc('name')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
    Tester::check_equal("ORDER BY ASC", $expected);

    ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
    $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
    Tester::check_equal("Multiple ORDER BY", $expected);

    ORM::for_table('widget')->group_by('name')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name`";
    Tester::check_equal("GROUP BY", $expected);

    ORM::for_table('widget')->group_by('name')->group_by('age')->find_many();
    $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
    Tester::check_equal("Multiple GROUP BY", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' ORDER BY `name` ASC LIMIT 5 OFFSET 5";
    Tester::check_equal("Complex query", $expected);

    ORM::for_table('widget')->where_lt('age', 10)->where_gt('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` < '10' AND `age` > '5'";
    Tester::check_equal("Less than and greater than", $expected);

    ORM::for_table('widget')->where_lte('age', 10)->where_gte('age', 5)->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` <= '10' AND `age` >= '5'";
    Tester::check_equal("Less than or equal and greater than or equal", $expected);

    ORM::for_table('widget')->where_null('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
    Tester::check_equal("where_null method", $expected);

    ORM::for_table('widget')->where_not_null('name')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
    Tester::check_equal("where_not_null method", $expected);

    ORM::for_table('widget')->where_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
    Tester::check_equal("Raw WHERE clause", $expected);

    ORM::for_table('widget')->where_raw('`name` = "Fred"')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
    Tester::check_equal("Raw WHERE clause with no parameters", $expected);

    ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->find_many();
    $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND `size` = 'large'";
    Tester::check_equal("Raw WHERE clause in method chain", $expected);

    ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->find_many();
    $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
    Tester::check_equal("Raw query", $expected);

    ORM::for_table('widget')->select('name')->find_many();
    $expected = "SELECT `name` FROM `widget`";
    Tester::check_equal("Simple result column", $expected);

    ORM::for_table('widget')->select('name')->select('age')->find_many();
    $expected = "SELECT `name`, `age` FROM `widget`";
    Tester::check_equal("Multiple simple result columns", $expected);

    ORM::for_table('widget')->select('widget.name')->find_many();
    $expected = "SELECT `widget`.`name` FROM `widget`";
    Tester::check_equal("Specify table name and column in result columns", $expected);

    ORM::for_table('widget')->select('widget.name', 'widget_name')->find_many();
    $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
    Tester::check_equal("Aliases in result columns", $expected);

    ORM::for_table('widget')->select_expr('COUNT(*)', 'count')->find_many();
    $expected = "SELECT COUNT(*) AS `count` FROM `widget`";
    Tester::check_equal("Literal expression in result columns", $expected);

    ORM::for_table('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Simple join", $expected);

    ORM::for_table('widget')->inner_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` INNER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Inner join", $expected);

    ORM::for_table('widget')->left_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` LEFT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Left outer join", $expected);

    ORM::for_table('widget')->right_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` RIGHT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Right outer join", $expected);

    ORM::for_table('widget')->full_outer_join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->find_many();
    $expected = "SELECT * FROM `widget` FULL OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Full outer join", $expected);

    ORM::for_table('widget')
        ->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))
        ->join('widget_nozzle', array('widget_nozzle.widget_id', '=', 'widget.id'))
        ->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` JOIN `widget_nozzle` ON `widget_nozzle`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Multiple join sources", $expected);

    ORM::for_table('widget')->table_alias('w')->find_many();
    $expected = "SELECT * FROM `widget` `w`";
    Tester::check_equal("Main table alias", $expected);

    ORM::for_table('widget')->join('widget_handle', array('wh.widget_id', '=', 'widget.id'), 'wh')->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `widget`.`id`";
    Tester::check_equal("Join with alias", $expected);

    ORM::for_table('widget')->join('widget_handle', "widget_handle.widget_id = widget.id")->find_many();
    $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON widget_handle.widget_id = widget.id";
    Tester::check_equal("Join with string constraint", $expected);

    ORM::for_table('widget')->distinct()->select('name')->find_many();
    $expected = "SELECT DISTINCT `name` FROM `widget`";
    Tester::check_equal("Select with DISTINCT", $expected);

    $widget = ORM::for_table('widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
    Tester::check_equal("Insert data", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
    Tester::check_equal("Update data", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->delete();
    $expected = "DELETE FROM `widget` WHERE `id` = '1'";
    Tester::check_equal("Delete data", $expected);

    // Regression tests

    $widget = ORM::for_table('widget')->select('widget.*')->find_one();
    $expected = "SELECT `widget`.* FROM `widget` LIMIT 1";
    Tester::check_equal("Issue #12 - incorrect quoting of column wildcard", $expected);

    // Tests that alter Idiorm's config are done last

    ORM::configure('id_column', 'primary_key');
    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `primary_key` = '5' LIMIT 1";
    Tester::check_equal("Setting: id_column", $expected);

    ORM::configure('id_column_overrides', array(
        'widget' => 'widget_id',
        'widget_handle' => 'widget_handle_id',
    ));

    ORM::for_table('widget')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `widget_id` = '5' LIMIT 1";
    Tester::check_equal("Setting: id_column_overrides, first test", $expected);

    ORM::for_table('widget_handle')->find_one(5);
    $expected = "SELECT * FROM `widget_handle` WHERE `widget_handle_id` = '5' LIMIT 1";
    Tester::check_equal("Setting: id_column_overrides, second test", $expected);

    ORM::for_table('widget_nozzle')->find_one(5);
    $expected = "SELECT * FROM `widget_nozzle` WHERE `primary_key` = '5' LIMIT 1";
    Tester::check_equal("Setting: id_column_overrides, third test", $expected);

    ORM::for_table('widget')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal("Instance ID column, first test", $expected);

    ORM::for_table('widget_handle')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget_handle` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal("Instance ID column, second test", $expected);

    ORM::for_table('widget_nozzle')->use_id_column('new_id')->find_one(5);
    $expected = "SELECT * FROM `widget_nozzle` WHERE `new_id` = '5' LIMIT 1";
    Tester::check_equal("Instance ID column, third test", $expected);

    // Test caching. This is a bit of a hack.
    ORM::configure('caching', true);
    ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
    ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
    $expected = ORM::get_last_query();
    ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this shouldn't run a query!
    Tester::check_equal("Caching, same query not run twice", $expected);


    Tester::report();
?>
