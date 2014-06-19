<?php

class CacheTestCustom extends PHPUnit_Framework_TestCase {

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
        ORM::reset_config();
        ORM::reset_db();
    }

    // Test caching. This is a bit of a hack.
    public function testCustomCacheCallback() {
		ORM::for_table('widget')->where('name', 'Fred')->where('age', 17)->find_one();
        ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
	
	
         ORM::configure('cache_query_result', function ($hash) {
			$this->assertEquals(true, is_string($hash));
        });

        ORM::configure('check_query_cache', function ($cache_key,$connection_name) {
            $this->assertEquals(true, is_string($hash));
            $this->assertEquals(true, is_string($connection_name));
        });
        ORM::configure('clear_cache', function ($connection_name) {
             $this->assertEquals(true, is_string($connection_name));
        });
    }
 
}