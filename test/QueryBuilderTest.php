<?php

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

    public function testFindManyQuery() {
        ORM::for_table('widget')->find_many();
        $expected = "SELECT * FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

}