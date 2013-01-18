<?php

class ORMTest extends PHPUnit_Framework_TestCase {

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

    public function testStaticAtrributes() {
        $this->assertEquals('0', ORM::CONDITION_FRAGMENT);
        $this->assertEquals('1', ORM::CONDITION_VALUES);
    }

    public function testForTable() {
        $result = ORM::for_table('test');
        $this->assertInstanceOf('ORM', $result);
    }

    public function testCreate() {
        $model = ORM::for_table('test')->create();
        $this->assertInstanceOf('ORM', $model);
        $this->assertTrue($model->is_new());
    }

    public function testIsNew() {
        $model = ORM::for_table('test')->create();
        $this->assertTrue($model->is_new());

        $model = ORM::for_table('test')->create(array('test' => 'test'));
        $this->assertTrue($model->is_new());
    }

    public function testIsDirty() {
        $model = ORM::for_table('test')->create();
        $this->assertFalse($model->is_dirty('test'));
        
        $model = ORM::for_table('test')->create(array('test' => 'test'));
        $this->assertTrue($model->is_dirty('test'));
    }

    public function testArrayAccess() {
        $value = 'test';
        $model = ORM::for_table('test')->create();
        $model['test'] = $value;
        $this->assertTrue(isset($model['test']));
        $this->assertEquals($model['test'], $value);
        unset($model['test']);
        $this->assertFalse(isset($model['test']));
    }

    public function testFindResultSet() {
        $result_set = ORM::for_table('test')->find_result_set();
        $this->assertInstanceOf('IdiormResultSet', $result_set);
        $this->assertSame(count($result_set), 5);
    }

    public function testFindResultSetByDefault() {
        ORM::configure('return_result_sets', true);

        $result_set = ORM::for_table('test')->find_many();
        $this->assertInstanceOf('IdiormResultSet', $result_set);
        $this->assertSame(count($result_set), 5);
        
        ORM::configure('return_result_sets', false);
        
        $result_set = ORM::for_table('test')->find_many();
        $this->assertInternalType('array', $result_set);
        $this->assertSame(count($result_set), 5);
    }

    public function testGetLastPdoStatement() {
        ORM::for_table('widget')->where('name', 'Fred')->find_one();
        $statement = ORM::get_last_statement();
        $this->assertInstanceOf('MockPDOStatement', $statement);
    }

}