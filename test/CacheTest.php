<?php

class CacheTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);
        ORM::configure('caching', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        ORM::set_db($db);
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::configure('caching', false);
        ORM::set_db(null);
    }

    // Test caching. This is a bit of a hack.
    public function testQueryGenerationOnlyOccursOnce() {
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
        ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
        $expected = ORM::get_last_query();
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this shouldn't run a query!
        $this->assertEquals($expected, ORM::get_last_query());
    }
}