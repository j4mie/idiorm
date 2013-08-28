<?php

class ConfigTest53 extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        ORM::set_db($db);

        ORM::configure('id_column', 'primary_key');
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::set_db(null);

        ORM::configure('id_column', 'id');
    }

    public function testLoggerCallback() {
        ORM::configure('logger', function($log_string) {
            return $log_string;
        });
        $function = ORM::get_config('logger');
        $this->assertTrue(is_callable($function));

        $log_string = "UPDATE `widget` SET `added` = NOW() WHERE `id` = '1'";
        $this->assertEquals($log_string, $function($log_string));

        ORM::configure('logger', null);
    }

}