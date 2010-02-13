<?php
    /*
     * Basic testing for Idiorm
     *
     * Checks that the generated SQL is correct
     *
     */

    require_once "../idiorm.php";
    require_once "test_classes.php";

    // Set up the dummy database connection
    $db = new DummyPDO();
    ORM::set_db($db);
    Tester::set_db($db);

    ORM::for_table('widget')->find_many();
    $expected = 'SELECT * FROM widget';
    Tester::check_equal("Basic unfiltered query", $expected);

    ORM::for_table('widget')->find_one(5);
    $expected = 'SELECT * FROM widget WHERE id = "5"';
    Tester::check_equal("Filtering on ID", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->find_one();
    $expected = 'SELECT * FROM widget WHERE name = "Fred"';
    Tester::check_equal("Single where clause", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
    $expected = 'SELECT * FROM widget WHERE name = "Fred" AND age = "10"';
    Tester::check_equal("Multiple WHERE clauses", $expected);

    ORM::for_table('widget')->limit(5)->find_one();
    $expected = 'SELECT * FROM widget LIMIT "5"';
    Tester::check_equal("LIMIT clause", $expected);

    ORM::for_table('widget')->limit(5)->offset(5)->find_one();
    $expected = 'SELECT * FROM widget LIMIT "5" OFFSET "5"';
    Tester::check_equal("LIMIT and OFFSET clause", $expected);

    ORM::for_table('widget')->order_by_desc('name')->find_one();
    $expected = 'SELECT * FROM widget ORDER BY "name" DESC';
    Tester::check_equal("ORDER BY DESC", $expected);

    ORM::for_table('widget')->order_by_asc('name')->find_one();
    $expected = 'SELECT * FROM widget ORDER BY "name" ASC';
    Tester::check_equal("ORDER BY ASC", $expected);

    ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
    $expected = 'SELECT * FROM widget ORDER BY "name" ASC, "age" DESC';
    Tester::check_equal("Multiple ORDER BY", $expected);

    ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
    $expected = 'SELECT * FROM widget WHERE name = "Fred" LIMIT "5" OFFSET "5" ORDER BY "name" ASC';
    Tester::check_equal("Complex query", $expected);

    $widget = ORM::for_table('widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = 'INSERT INTO widget (name, age) VALUES ("Fred", "10")';
    Tester::check_equal("Insert data", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = 'UPDATE widget SET name = "Fred", age = "10" WHERE id = "1"';
    Tester::check_equal("Update data", $expected);

    $widget = ORM::for_table('widget')->find_one(1);
    $widget->delete();
    $expected = 'DELETE FROM widget WHERE id = "1"';
    Tester::check_equal("Delete data", $expected);

    ORM::configure('id_column', 'primary_key');
    ORM::for_table('widget')->find_one(5);
    $expected = 'SELECT * FROM widget WHERE primary_key = "5"';
    Tester::check_equal("Setting: id_column", $expected);

    ORM::configure('id_column_overrides', array(
        'widget' => 'widget_id',
        'widget_handle' => 'widget_handle_id',
    ));

    ORM::for_table('widget')->find_one(5);
    $expected = 'SELECT * FROM widget WHERE widget_id = "5"';
    Tester::check_equal("Setting: id_column_overrides, first test", $expected);

    ORM::for_table('widget_handle')->find_one(5);
    $expected = 'SELECT * FROM widget_handle WHERE widget_handle_id = "5"';
    Tester::check_equal("Setting: id_column_overrides, second test", $expected);

    ORM::for_table('widget_nozzle')->find_one(5);
    $expected = 'SELECT * FROM widget_nozzle WHERE primary_key = "5"';
    Tester::check_equal("Setting: id_column_overrides, third test", $expected);

    Tester::report();
?>
