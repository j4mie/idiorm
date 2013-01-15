<?php

class ORMTest extends PHPUnit_Framework_TestCase {

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

}