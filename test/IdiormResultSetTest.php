<?php

class IdiormResultSetTest extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        ORM::set_db($db);
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::set_db(null);
    }

    public function testGet() {
        $IdiormResultSet = new IdiormResultSet();
        $this->assertInternalType('array', $IdiormResultSet->get_results());
    }

    public function testConstructor() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet($result_set);
        $this->assertSame($IdiormResultSet->get_results(), $result_set);
    }

    public function testSetResultsAndGetResults() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet();
        $IdiormResultSet->set_results($result_set);
        $this->assertSame($IdiormResultSet->get_results(), $result_set);
    }

    public function testAsArray() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet();
        $IdiormResultSet->set_results($result_set);
        $this->assertSame($IdiormResultSet->as_array(), $result_set);
    }

    public function testCount() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet($result_set);
        $this->assertSame($IdiormResultSet->count(), 1);
        $this->assertSame(count($IdiormResultSet), 1);
    }

    public function testGetIterator() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet($result_set);
        $this->assertInstanceOf('ArrayIterator', $IdiormResultSet->getIterator());
    }

    public function testForeach() {
        $result_set = array('item' => new stdClass);
        $IdiormResultSet = new IdiormResultSet($result_set);
        $return_array = array();
        foreach($IdiormResultSet as $key => $record) {
            $return_array[$key] = $record;
        }
        $this->assertSame($result_set, $return_array);
    }

    public function testCallingMethods() {
        $result_set = array('item' => ORM::for_table('test'), 'item2' => ORM::for_table('test'));
        $IdiormResultSet = new IdiormResultSet($result_set);
        $IdiormResultSet->set('field', 'value')->set('field2', 'value');

        foreach($IdiormResultSet as $record) {
            $this->assertTrue(isset($record->field));
            $this->assertSame($record->field, 'value');

            $this->assertTrue(isset($record->field2));
            $this->assertSame($record->field2, 'value');
        }
    }
    
}