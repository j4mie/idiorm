<?php

class CacheTest extends PHPUnit_Framework_TestCase {

    const ALTERNATE = 'alternate'; // Used as name of alternate connection

    public function setUp() {
        // Set up the dummy database connections
        ORM::set_db(new MockPDO('sqlite::memory:'));
        ORM::set_db(new MockDifferentPDO('sqlite::memory:'), self::ALTERNATE);

        // Enable logging
        ORM::configure('logging', true);
        ORM::configure('logging', true, self::ALTERNATE);
        ORM::configure('caching', true);
        ORM::configure('caching', true, self::ALTERNATE);
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::configure('logging', false, self::ALTERNATE);
        ORM::configure('caching', false);
        ORM::configure('caching', false, self::ALTERNATE);
        ORM::set_db(null);
        ORM::set_db(null, self::ALTERNATE);
    }

    // Test caching. This is a bit of a hack.
    public function testQueryGenerationOnlyOccursOnce() {
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
        ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
        $expected = ORM::get_last_query();
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one(); // this shouldn't run a query!
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testQueryGenerationOnlyOccursOnceWithMultipleConnections() {
        // Test caching with multiple connections (also a bit of a hack)
        ORM::for_table('widget', self::ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one();
        ORM::for_table('widget', self::ALTERNATE)->where('name', 'Tom')->where('age', 120)->find_one();
        $expected = ORM::get_last_query();
        ORM::for_table('widget', self::ALTERNATE)->where('name', 'Steve')->where('age', 80)->find_one(); // this shouldn't run a query!
        $this->assertEquals($expected, ORM::get_last_query(self::ALTERNATE));
    }
}