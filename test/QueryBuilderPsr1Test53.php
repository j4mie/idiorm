<?php

class QueryBuilderPsr1Test53 extends PHPUnit_Framework_TestCase {

    public function setUp() {
        // Enable logging
        ORM::configure('logging', true);

        // Set up the dummy database connection
        $db = new MockPDO('sqlite::memory:');
        ORM::setDb($db);
    }

    public function tearDown() {
        ORM::configure('logging', false);
        ORM::setDb(null);
    }

    public function testFindMany() {
        ORM::forTable('widget')->findMany();
        $expected = "SELECT * FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testFindOne() {
        ORM::forTable('widget')->findOne();
        $expected = "SELECT * FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testFindOneWithPrimaryKeyFilter() {
        ORM::forTable('widget')->findOne(5);
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereIdIs() {
        ORM::forTable('widget')->whereIdIs(5)->findOne();
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSingleWhereClause() {
        ORM::forTable('widget')->where('name', 'Fred')->findOne();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleWhereClauses() {
        ORM::forTable('widget')->where('name', 'Fred')->where('age', 10)->findOne();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereNotEqual() {
        ORM::forTable('widget')->whereNotEqual('name', 'Fred')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` != 'Fred'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereLike() {
        ORM::forTable('widget')->whereLike('name', '%Fred%')->findOne();
        $expected = "SELECT * FROM `widget` WHERE `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereNotLike() {
        ORM::forTable('widget')->whereNotLike('name', '%Fred%')->findOne();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereIn() {
        ORM::forTable('widget')->whereIn('name', array('Fred', 'Joe'))->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereNotIn() {
        ORM::forTable('widget')->whereNotIn('name', array('Fred', 'Joe'))->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testLimit() {
        ORM::forTable('widget')->limit(5)->findMany();
        $expected = "SELECT * FROM `widget` LIMIT 5";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testLimitAndOffset() {
        ORM::forTable('widget')->limit(5)->offset(5)->findMany();
        $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testOrderByDesc() {
        ORM::forTable('widget')->orderByDesc('name')->findOne();
        $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testOrderByAsc() {
        ORM::forTable('widget')->orderByAsc('name')->findOne();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testOrderByExpression() {
        ORM::forTable('widget')->orderByExpr('SOUNDEX(`name`)')->findOne();
        $expected = "SELECT * FROM `widget` ORDER BY SOUNDEX(`name`) LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleOrderBy() {
        ORM::forTable('widget')->orderByAsc('name')->orderByDesc('age')->findOne();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testGroupBy() {
        ORM::forTable('widget')->groupBy('name')->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleGroupBy() {
        ORM::forTable('widget')->groupBy('name')->groupBy('age')->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testGroupByExpression() {
        ORM::forTable('widget')->groupByExpr("FROM_UNIXTIME(`time`, '%Y-%m')")->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY FROM_UNIXTIME(`time`, '%Y-%m')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHaving() {
        ORM::forTable('widget')->groupBy('name')->having('name', 'Fred')->findOne();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleHaving() {
        ORM::forTable('widget')->groupBy('name')->having('name', 'Fred')->having('age', 10)->findOne();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingNotEqual() {
        ORM::forTable('widget')->groupBy('name')->havingNotEqual('name', 'Fred')->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` != 'Fred'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingLike() {
        ORM::forTable('widget')->groupBy('name')->havingLike('name', '%Fred%')->findOne();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingNotLike() {
        ORM::forTable('widget')->groupBy('name')->havingNotLike('name', '%Fred%')->findOne();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingIn() {
        ORM::forTable('widget')->groupBy('name')->havingIn('name', array('Fred', 'Joe'))->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingNotIn() {
        ORM::forTable('widget')->groupBy('name')->havingNotIn('name', array('Fred', 'Joe'))->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingLessThan() {
        ORM::forTable('widget')->groupBy('name')->havingLt('age', 10)->havingGt('age', 5)->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingLessThanOrEqualAndGreaterThanOrEqual() {
        ORM::forTable('widget')->groupBy('name')->havingLte('age', 10)->havingGte('age', 5)->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingNull() {
        ORM::forTable('widget')->groupBy('name')->havingNull('name')->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NULL";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testHavingNotNull() {
        ORM::forTable('widget')->groupBy('name')->havingNotNull('name')->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawHaving() {
        ORM::forTable('widget')->groupBy('name')->havingRaw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->findMany();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testComplexQuery() {
        ORM::forTable('widget')->where('name', 'Fred')->limit(5)->offset(5)->orderByAsc('name')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' ORDER BY `name` ASC LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereLessThanAndGreaterThan() {
        ORM::forTable('widget')->whereLt('age', 10)->whereGt('age', 5)->findMany();
        $expected = "SELECT * FROM `widget` WHERE `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereLessThanAndEqualAndGreaterThanAndEqual() {
        ORM::forTable('widget')->whereLte('age', 10)->whereGte('age', 5)->findMany();
        $expected = "SELECT * FROM `widget` WHERE `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereNull() {
        ORM::forTable('widget')->whereNull('name')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testWhereNotNull() {
        ORM::forTable('widget')->whereNotNull('name')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawWhereClause() {
        ORM::forTable('widget')->whereRaw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawWhereClauseWithPercentSign() {
        ORM::forTable('widget')->whereRaw('STRFTIME("%Y", "now") = ?', array(2012))->findMany();
        $expected = "SELECT * FROM `widget` WHERE STRFTIME(\"%Y\", \"now\") = '2012'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawWhereClauseWithNoParameters() {
        ORM::forTable('widget')->whereRaw('`name` = "Fred"')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawWhereClauseInMethodChain() {
        ORM::forTable('widget')->where('age', 18)->whereRaw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->findMany();
        $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND `size` = 'large'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawQuery() {
        ORM::forTable('widget')->rawQuery('SELECT `w`.* FROM `widget` w')->findMany();
        $expected = "SELECT `w`.* FROM `widget` w";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawQueryWithParameters() {
        ORM::forTable('widget')->rawQuery('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->findMany();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawQueryWithNamedPlaceholders() {
        ORM::forTable('widget')->rawQuery('SELECT `w`.* FROM `widget` w WHERE `name` = :name AND `age` = :age', array(':name' => 'Fred', ':age' => 5))->findMany();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSimpleResultColumn() {
        ORM::forTable('widget')->select('name')->findMany();
        $expected = "SELECT `name` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleSimpleResultColumns() {
        ORM::forTable('widget')->select('name')->select('age')->findMany();
        $expected = "SELECT `name`, `age` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSpecifyTableNameAndColumnInResultColumns() {
        ORM::forTable('widget')->select('widget.name')->findMany();
        $expected = "SELECT `widget`.`name` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMainTableAlias() {
        ORM::forTable('widget')->tableAlias('w')->findMany();
        $expected = "SELECT * FROM `widget` `w`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testAliasesInResultColumns() {
        ORM::forTable('widget')->select('widget.name', 'widget_name')->findMany();
        $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testAliasesInSelectManyResults() {
        ORM::forTable('widget')->selectMany(array('widget_name' => 'widget.name'), 'widget_handle')->findMany();
        $expected = "SELECT `widget`.`name` AS `widget_name`, `widget_handle` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testLiteralExpressionInResultColumn() {
        ORM::forTable('widget')->selectExpr('COUNT(*)', 'count')->findMany();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testLiteralExpressionInSelectManyResultColumns() {
        ORM::forTable('widget')->selectManyExpr(array('count' => 'COUNT(*)'), 'SUM(widget_order)')->findMany();
        $expected = "SELECT COUNT(*) AS `count`, SUM(widget_order) FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSimpleJoin() {
        ORM::forTable('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findMany();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSimpleJoinWithWhereIdIsMethod() {
        ORM::forTable('widget')->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findOne(5);
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` WHERE `widget`.`id` = '5' LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testInnerJoin() {
        ORM::forTable('widget')->innerJoin('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findMany();
        $expected = "SELECT * FROM `widget` INNER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testLeftOuterJoin() {
        ORM::forTable('widget')->leftOuterJoin('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findMany();
        $expected = "SELECT * FROM `widget` LEFT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRightOuterJoin() {
        ORM::forTable('widget')->rightOuterJoin('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findMany();
        $expected = "SELECT * FROM `widget` RIGHT OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testFullOuterJoin() {
        ORM::forTable('widget')->fullOuterJoin('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))->findMany();
        $expected = "SELECT * FROM `widget` FULL OUTER JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMultipleJoinSources() {
        ORM::forTable('widget')
        ->join('widget_handle', array('widget_handle.widget_id', '=', 'widget.id'))
        ->join('widget_nozzle', array('widget_nozzle.widget_id', '=', 'widget.id'))
        ->findMany();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` JOIN `widget_nozzle` ON `widget_nozzle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testJoinWithAliases() {
        ORM::forTable('widget')->join('widget_handle', array('wh.widget_id', '=', 'widget.id'), 'wh')->findMany();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testJoinWithAliasesAndWhere() {
        ORM::forTable('widget')->tableAlias('w')->join('widget_handle', array('wh.widget_id', '=', 'w.id'), 'wh')->whereEqual('id', 1)->findMany();
        $expected = "SELECT * FROM `widget` `w` JOIN `widget_handle` `wh` ON `wh`.`widget_id` = `w`.`id` WHERE `w`.`id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testJoinWithStringConstraint() {
        ORM::forTable('widget')->join('widget_handle', "widget_handle.widget_id = widget.id")->findMany();
        $expected = "SELECT * FROM `widget` JOIN `widget_handle` ON widget_handle.widget_id = widget.id";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawJoin() {
        ORM::forTable('widget')->rawJoin('INNER JOIN ( SELECT * FROM `widget_handle` )', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle')->findMany();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` ) `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawJoinWithParameters() {
        ORM::forTable('widget')->rawJoin('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))->findMany();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE '%button%' AND `widget_handle`.category = '2') `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testRawJoinAndRawWhereWithParameters() {
        ORM::forTable('widget')
            ->rawJoin('INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE ? AND `widget_handle`.category = ?)', array('widget_handle.widget_id', '=', 'widget.id'), 'widget_handle', array('%button%', 2))
            ->rawJoin('INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE ?)', array('person.id', '=', 'widget.person_id'), 'person', array('%Fred%'))
            ->whereRaw('`id` > ? AND `id` < ?', array(5, 10))
            ->findMany();
        $expected = "SELECT * FROM `widget` INNER JOIN ( SELECT * FROM `widget_handle` WHERE `widget_handle`.name LIKE '%button%' AND `widget_handle`.category = '2') `widget_handle` ON `widget_handle`.`widget_id` = `widget`.`id` INNER JOIN ( SELECT * FROM `person` WHERE `person`.name LIKE '%Fred%') `person` ON `person`.`id` = `widget`.`person_id` WHERE `id` > '5' AND `id` < '10'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSelectWithDistinct() {
        ORM::forTable('widget')->distinct()->select('name')->findMany();
        $expected = "SELECT DISTINCT `name` FROM `widget`";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testInsertData() {
        $widget = ORM::forTable('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testInsertDataContainingAnExpression() {
        $widget = ORM::forTable('widget')->create();
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->setExpr('added', 'NOW()');
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`, `added`) VALUES ('Fred', '10', NOW())";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testInsertDataUsingArrayAccess() {
        $widget = ORM::forTable('widget')->create();
        $widget['name'] = "Fred";
        $widget['age'] = 10;
        $widget->save();
        $expected = "INSERT INTO `widget` (`name`, `age`) VALUES ('Fred', '10')";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testUpdateData() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testUpdateDataContainingAnExpression() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->name = "Fred";
        $widget->age = 10;
        $widget->setExpr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW() WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testUpdateMultipleFields() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10' WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testUpdateMultipleFieldsContainingAnExpression() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->setExpr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = GeomFromText('POINT(1.2347 2.3436)') WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testUpdateMultipleFieldsContainingAnExpressionAndOverridePreviouslySetExpression() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->set(array("name" => "Fred", "age" => 10));
        $widget->setExpr(array("added" => "NOW()", "lat_long" => "GeomFromText('POINT(1.2347 2.3436)')"));
        $widget->lat_long = 'unknown';
        $widget->save();
        $expected = "UPDATE `widget` SET `name` = 'Fred', `age` = '10', `added` = NOW(), `lat_long` = 'unknown' WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testDeleteData() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->delete();
        $expected = "DELETE FROM `widget` WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testDeleteMany() {
        ORM::forTable('widget')->whereEqual('age', 10)->delete_many();
        $expected = "DELETE FROM `widget` WHERE `age` = '10'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testCount() {
        ORM::forTable('widget')->count();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }
    
    public function testIgnoreSelectAndCount() {
    	ORM::forTable('widget')->select('test')->count();
    	$expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
    	$this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMax() {
        ORM::forTable('person')->max('height');
        $expected = "SELECT MAX(`height`) AS `max` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testMin() {
        ORM::forTable('person')->min('height');
        $expected = "SELECT MIN(`height`) AS `min` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testAvg() {
        ORM::forTable('person')->avg('height');
        $expected = "SELECT AVG(`height`) AS `avg` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testSum() {
        ORM::forTable('person')->sum('height');
        $expected = "SELECT SUM(`height`) AS `sum` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    /**
     * Regression tests
     */
    public function testIssue12IncorrectQuotingOfColumnWildcard() {
        ORM::forTable('widget')->select('widget.*')->findOne();
        $expected = "SELECT `widget`.* FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testIssue57LogQueryRaisesWarningWhenPercentSymbolSupplied() {
        ORM::forTable('widget')->whereRaw('username LIKE "ben%"')->findMany();
        $expected = 'SELECT * FROM `widget` WHERE username LIKE "ben%"';
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testIssue57LogQueryRaisesWarningWhenQuestionMarkSupplied() {
        ORM::forTable('widget')->whereRaw('comments LIKE "has been released?%"')->findMany();
        $expected = 'SELECT * FROM `widget` WHERE comments LIKE "has been released?%"';
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testIssue74EscapingQuoteMarksIn_quote_identifier_part() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->set('ad`ded', '2013-01-04');
        $widget->save();
        $expected = "UPDATE `widget` SET `ad``ded` = '2013-01-04' WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }

    public function testIssue90UsingSetExprAloneDoesTriggerQueryGeneration() {
        $widget = ORM::forTable('widget')->findOne(1);
        $widget->setExpr('added', 'NOW()');
        $widget->save();
        $expected = "UPDATE `widget` SET `added` = NOW() WHERE `id` = '1'";
        $this->assertEquals($expected, ORM::getLastQuery());
    }
}

