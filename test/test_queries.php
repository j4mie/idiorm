<?php
    /*
     * Basic testing for Paris.
     *
     * We deliberately don't test the query API - that's Idiorm's job.
     * We just test Paris-specific functionality.
     *
     * Checks that the generated SQL is correct
     *
     */

    require_once dirname(__FILE__) . "/idiorm.php";
    require_once dirname(__FILE__) . "/../paris.php";
    require_once dirname(__FILE__) . "/test_classes.php";

    // Set up the dummy database connection
    $db = new DummyPDO();
    ORM::set_db($db);
    Tester::set_db($db);

    class Simple extends Model {
    }

    Model::factory('Simple')->find_many();
    $expected = 'SELECT * FROM `simple`';
    Tester::check_equal("Simple auto table name", $expected);


    class ComplexModelClassName extends Model {
    }

    Model::factory('ComplexModelClassName')->find_many();
    $expected = 'SELECT * FROM `complex_model_class_name`';
    Tester::check_equal("Complex auto table name", $expected);

    class ModelWithCustomTable extends Model {
        public static $_table = 'custom_table';
    }

    Model::factory('ModelWithCustomTable')->find_many();
    $expected = 'SELECT * FROM `custom_table`';
    Tester::check_equal("Custom table name", $expected);

    class ModelWithCustomTableAndCustomIdColumn extends Model {
        public static $_table = 'custom_table';
        public static $_id_column = 'custom_id_column';
    }

    Model::factory('ModelWithCustomTableAndCustomIdColumn')->find_one(5);
    $expected = 'SELECT * FROM `custom_table` WHERE `custom_id_column` = "5"';
    Tester::check_equal("Custom ID column", $expected);

    class ModelWithFilters extends Model {

        public static function name_is_fred($orm) {
            return $orm->where('name', 'Fred');
        }

        public static function name_is($orm, $name) {
            return $orm->where('name', $name);
        }
    }

    Model::factory('ModelWithFilters')->filter('name_is_fred')->find_many();
    $expected = 'SELECT * FROM `model_with_filters` WHERE `name` = "Fred"';
    Tester::check_equal("Filter with no arguments", $expected);

    Model::factory('ModelWithFilters')->filter('name_is', 'Bob')->find_many();
    $expected = 'SELECT * FROM `model_with_filters` WHERE `name` = "Bob"';
    Tester::check_equal("Filter with arguments", $expected);

    class Widget extends Model {
    }

    $widget = Model::factory('Widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = 'INSERT INTO `widget` (`name`, `age`) VALUES ("Fred", "10")';
    Tester::check_equal("Insert data", $expected);

    $widget = Model::factory('Widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = 'UPDATE `widget` SET `name` = "Fred", `age` = "10" WHERE `id` = "1"';
    Tester::check_equal("Update data", $expected);

    $widget = Model::factory('Widget')->find_one(1);
    $widget->delete();
    $expected = 'DELETE FROM `widget` WHERE `id` = "1"';
    Tester::check_equal("Delete data", $expected);

    class CustomIdWidget extends Model {
        public static $_id_column = 'widget_id';
    }

    Tester::report();
?>
