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
        ORM::reset_config();
        ORM::reset_db();
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
    public function testCustomCacheCallback() {
        $phpunit = $this;
        $my_cache = array();
        
        ORM::configure('cache_query_result', function ($cache_key,$value,$connection_name) use ($phpunit,&$my_cache) {
            $phpunit->assertEquals(true, is_string($cache_key));
            $my_cache[$connection_name][$cache_key] = $value;
        });
        ORM::configure('check_query_cache', function ($cache_key,$connection_name) use ($phpunit,&$my_cache) {
            $phpunit->assertEquals(true, is_string($cache_key));
            $phpunit->assertEquals(true, is_string($connection_name));
            if(isset($my_cache[$connection_name]) and isset($my_cache[$connection_name][$cache_key])){
               $phpunit->assertEquals(true, is_array($my_cache[$connection_name][$cache_key]));
               return $my_cache[$connection_name][$cache_key];
            } else {
                return false;
            }
        });
        ORM::configure('clear_cache', function ($connection_name) use ($phpunit,$my_cache) {
             $phpunit->assertEquals(true, is_string($connection_name));
        });
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 21)->find_one();
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 21)->find_one();
        ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
        
        $new = ORM::for_table('widget')->create();
        $new->name = "Joe";
        $new->age = 25;
        $saved = $new->save();
    }
}