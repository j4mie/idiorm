<?php

class CacheIntegrationTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        ORM::configure('sqlite::memory:');
        ORM::configure('logging', true);
        ORM::configure('caching', true);

        ORM::raw_execute('CREATE TABLE `league` ( `class_id` INTEGER )');
        // needs to be individually inserted to support SQLite before
        // version 3.7.11
        ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (1)');
        ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (2)');
        ORM::raw_execute('INSERT INTO `league`(`class_id`) VALUES (3)');

        $x = ORM::for_table('league')->count();
        $this->assertEquals(3, $x);
    }

    public function tearDown() {
        ORM::raw_execute('DROP TABLE `league`');
    }

    public function testRegressionForPullRequest319() {
        $rs = ORM::for_table('league')->where('class_id', 1);
        $total = $rs->count();
        $this->assertEquals(1, $total);
        $row = $rs->find_one();
        $this->assertEquals(array('class_id' => 1), $row->as_array());

        $rs = ORM::for_table('league')->where('class_id', 1);
        $total = $rs->count();
        $this->assertEquals(1, $total);
        try {
            $row = $rs->find_one();
        } catch(PDOException $e) {
            $this->fail("Caching is breaking subsequent queries!\n{$e->getMessage()}");
        }
        $this->assertEquals(array('class_id' => 1), $row->as_array());
    }

}
