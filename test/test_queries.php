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

    // Enable logging
    ORM::configure('logging', true);

    // Set up the dummy database connection
    $db = new DummyPDO('sqlite::memory:');
    ORM::set_db($db);

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
    $expected = "SELECT * FROM `custom_table` WHERE `custom_id_column` = '5' LIMIT 1";
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
    $expected = "SELECT * FROM `model_with_filters` WHERE `name` = 'Fred'";
    Tester::check_equal("Filter with no arguments", $expected);

    Model::factory('ModelWithFilters')->filter('name_is', 'Bob')->find_many();
    $expected = "SELECT * FROM `model_with_filters` WHERE `name` = 'Bob'";
    Tester::check_equal("Filter with arguments", $expected);

    class Widget extends Model {
    }

    $widget = Model::factory('Widget')->create();
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
    Tester::check_equal("Insert data", $expected);

    $widget = Model::factory('Widget')->find_one(1);
    $widget->name = "Fred";
    $widget->age = 10;
    $widget->save();
    $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
    Tester::check_equal("Update data", $expected);

    $widget = Model::factory('Widget')->find_one(1);
    $widget->delete();
    $expected = "DELETE FROM `widget` WHERE `id` = '1'";
    Tester::check_equal("Delete data", $expected);

    class Profile extends Model {
        public function user() {
            return $this->belongs_to('User');
        }
    }

    class User extends Model {
        public function profile() {
            return $this->has_one('Profile');
        }
    }

    $user = Model::factory('User')->find_one(1);
    $profile = $user->profile()->find_one();
    $expected = "SELECT * FROM `profile` WHERE `user_id` = '1' LIMIT 1";
    Tester::check_equal("has_one relation", $expected);

    class UserTwo extends Model {
        public function profile() {
            return $this->has_one('Profile', 'my_custom_fk_column');
        }
    }

    $user2 = Model::factory('UserTwo')->find_one(1);
    $profile = $user2->profile()->find_one();
    $expected = "SELECT * FROM `profile` WHERE `my_custom_fk_column` = '1' LIMIT 1";
    Tester::check_equal("has_one relation with custom FK name", $expected);

    $profile->user_id = 1;
    $user3 = $profile->user()->find_one();
    $expected = "SELECT * FROM `user` WHERE `id` = '1' LIMIT 1";
    Tester::check_equal("belongs_to relation", $expected);

    class ProfileTwo extends Model {
        public function user() {
            return $this->belongs_to('User', 'custom_user_fk_column');
        }
    }
    $profile2 = Model::factory('ProfileTwo')->find_one(1);
    $profile2->custom_user_fk_column = 5;
    $user4 = $profile2->user()->find_one();
    $expected = "SELECT * FROM `user` WHERE `id` = '5' LIMIT 1";
    Tester::check_equal("belongs_to relation with custom FK name", $expected);

    class Post extends Model {
    }

    class UserThree extends Model {
        public function posts() {
            return $this->has_many('Post');
        }
    }

    $user4 = Model::factory('UserThree')->find_one(1);
    $posts = $user4->posts()->find_many();
    $expected = "SELECT * FROM `post` WHERE `user_three_id` = '1'";
    Tester::check_equal("has_many relation", $expected);

    class UserFour extends Model {
        public function posts() {
            return $this->has_many('Post', 'my_custom_fk_column');
        }
    }
    $user5 = Model::factory('UserFour')->find_one(1);
    $posts = $user5->posts()->find_many();
    $expected = "SELECT * FROM `post` WHERE `my_custom_fk_column` = '1'";
    Tester::check_equal("has_many relation with custom FK name", $expected);

    class Author extends Model {
    }

    class AuthorBook extends Model {
    }

    class Book extends Model {
        public function authors() {
            return $this->has_many_through('Author');
        }
    }

    $book = Model::factory('Book')->find_one(1);
    $authors = $book->authors()->find_many();
    $expected = "SELECT `author`.`*` FROM `author` JOIN `author_book` ON `author`.`id` = `author_book`.`author_id` WHERE `author_book`.`book_id` = '1'";
    Tester::check_equal("has_many_through relation", $expected);

    class AuthorTwo extends Model {
    }

    class WroteTheBook extends Model {
    }

    class BookTwo extends Model {
        public function authors() {
            return $this->has_many_through('AuthorTwo', 'WroteTheBook', 'custom_book_id', 'custom_author_id');
        }
    }

    $book2 = Model::factory('BookTwo')->find_one(1);
    $authors2 = $book2->authors()->find_many();
    $expected = "SELECT `author_two`.`*` FROM `author_two` JOIN `wrote_the_book` ON `author_two`.`id` = `wrote_the_book`.`custom_author_id` WHERE `wrote_the_book`.`custom_book_id` = '1'";
    Tester::check_equal("has_many_through relation with custom intermediate model and key names", $expected);

    Tester::report();
?>
