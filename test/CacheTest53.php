<?php

class CacheTest53 extends PHPUnit_Framework_TestCase {

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

     
    public function testCustomCacheCallback() {
        $phpunit = $this;
        $my_cache = array();
        ORM::configure('caching_auto_clear', true);
 
        ORM::configure('create_cache_key', function ($query, $parameters, $table_name, $connection) use ($phpunit, &$my_cache) {
            $phpunit->assertEquals(true, is_string($query));
            $phpunit->assertEquals(true, is_array($parameters));
            $phpunit->assertEquals(true, is_string($connection));
            $phpunit->assertEquals('widget', $table_name);
            $parameter_string = join(',', $parameters);
            $key = $query . ':' . $parameter_string;
            $my_key = 'some-prefix'.crc32($key);
            return $my_key;
        });
        ORM::configure('cache_query_result', function ($cache_key, $value, $table_name, $connection_name) use ($phpunit, &$my_cache) {
            $phpunit->assertEquals(true, is_string($cache_key));
            $phpunit->assertEquals('widget', $table_name);
            $my_cache[$cache_key] = $value;
        });
        ORM::configure('check_query_cache', function ($cache_key, $table_name, $connection_name) use ($phpunit, &$my_cache) {
            $phpunit->assertEquals(true, is_string($cache_key));
            $phpunit->assertEquals(true, is_string($connection_name));
            $phpunit->assertEquals('widget', $table_name);

            if(isset($my_cache) and isset($my_cache[$cache_key])){
               $phpunit->assertEquals(true, is_array($my_cache[$cache_key]));
               return $my_cache[$cache_key];
            } else {
               return false;
            }
        });
        ORM::configure('clear_cache', function ($table_name, $connection_name) use ($phpunit, &$my_cache) {
             $phpunit->assertEquals(true, is_string($table_name)); 
             $phpunit->assertEquals(true, is_string($connection_name));
             $my_cache = array();
        });
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 21)->find_one();
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 21)->find_one();
        ORM::for_table('widget')->where('name', 'Bob')->where('age', 42)->find_one();
 
        //our custom cache should be full now 
        $this->assertEquals(true, !empty($my_cache));
 
        //checking custom cache key 
        foreach($my_cache as $k=>$v){
        $this->assertEquals('some-prefix', substr($k,0,11));
        }
        
        $new = ORM::for_table('widget')->create();
        $new->name = "Joe";
        $new->age = 25;
        $saved = $new->save();
        
        //our custom cache should be empty now 
        $this->assertEquals(true, empty($my_cache));
    }
}