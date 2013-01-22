<?php

class QueryBuilderTest extends PHPUnit_Framework_TestCase {

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

    public function testFindMany() {
        ORM::for_table('widget')->find_many();
        $expected = "SELECT * FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testFindOne() {
        ORM::for_table('widget')->find_one();
        $expected = "SELECT * FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testFindOneWithPrimaryKeyFilter() {
        ORM::for_table('widget')->find_one(5);
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIdIs() {
        ORM::for_table('widget')->where_id_is(5)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `id` = '5' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSingleWhereClause() {
        ORM::for_table('widget')->where('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleWhereClauses() {
        ORM::for_table('widget')->where('name', 'Fred')->where('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotEqual() {
        ORM::for_table('widget')->where_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` != 'Fred'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLike() {
        ORM::for_table('widget')->where_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotLike() {
        ORM::for_table('widget')->where_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereIn() {
        ORM::for_table('widget')->where_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotIn() {
        ORM::for_table('widget')->where_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLimit() {
        ORM::for_table('widget')->limit(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testLimitAndOffset() {
        ORM::for_table('widget')->limit(5)->offset(5)->find_many();
        $expected = "SELECT * FROM `widget` LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByDesc() {
        ORM::for_table('widget')->order_by_desc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByAsc() {
        ORM::for_table('widget')->order_by_asc('name')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testOrderByExpression() {
        ORM::for_table('widget')->order_by_expr('SOUNDEX(`name`)')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY SOUNDEX(`name`) LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleOrderBy() {
        ORM::for_table('widget')->order_by_asc('name')->order_by_desc('age')->find_one();
        $expected = "SELECT * FROM `widget` ORDER BY `name` ASC, `age` DESC LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testGroupBy() {
        ORM::for_table('widget')->group_by('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleGroupBy() {
        ORM::for_table('widget')->group_by('name')->group_by('age')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name`, `age`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testGroupByExpression() {
        ORM::for_table('widget')->group_by_expr("FROM_UNIXTIME(`time`, '%Y-%m')")->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY FROM_UNIXTIME(`time`, '%Y-%m')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHaving() {
        ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleHaving() {
        ORM::for_table('widget')->group_by('name')->having('name', 'Fred')->having('age', 10)->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND `age` = '10' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotEqual() {
        ORM::for_table('widget')->group_by('name')->having_not_equal('name', 'Fred')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` != 'Fred'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLike() {
        ORM::for_table('widget')->group_by('name')->having_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotLike() {
        ORM::for_table('widget')->group_by('name')->having_not_like('name', '%Fred%')->find_one();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT LIKE '%Fred%' LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingIn() {
        ORM::for_table('widget')->group_by('name')->having_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotIn() {
        ORM::for_table('widget')->group_by('name')->having_not_in('name', array('Fred', 'Joe'))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` NOT IN ('Fred', 'Joe')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLessThan() {
        ORM::for_table('widget')->group_by('name')->having_lt('age', 10)->having_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingLessThanOrEqualAndGreaterThanOrEqual() {
        ORM::for_table('widget')->group_by('name')->having_lte('age', 10)->having_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNull() {
        ORM::for_table('widget')->group_by('name')->having_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testHavingNotNull() {
        ORM::for_table('widget')->group_by('name')->having_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawHaving() {
        ORM::for_table('widget')->group_by('name')->having_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` GROUP BY `name` HAVING `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testComplexQuery() {
        ORM::for_table('widget')->where('name', 'Fred')->limit(5)->offset(5)->order_by_asc('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' ORDER BY `name` ASC LIMIT 5 OFFSET 5";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLessThanAndGreaterThan() {
        ORM::for_table('widget')->where_lt('age', 10)->where_gt('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` < '10' AND `age` > '5'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereLessThanAndEqualAndGreaterThanAndEqual() {
        ORM::for_table('widget')->where_lte('age', 10)->where_gte('age', 5)->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` <= '10' AND `age` >= '5'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNull() {
        ORM::for_table('widget')->where_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testWhereNotNull() {
        ORM::for_table('widget')->where_not_null('name')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` IS NOT NULL";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClause() {
        ORM::for_table('widget')->where_raw('`name` = ? AND (`age` = ? OR `age` = ?)', array('Fred', 5, 10))->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = 'Fred' AND (`age` = '5' OR `age` = '10')";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseWithPercentSign() {
        ORM::for_table('widget')->where_raw('STRFTIME("%Y", "now") = ?', array(2012))->find_many();
        $expected = "SELECT * FROM `widget` WHERE STRFTIME(\"%Y\", \"now\") = '2012'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseWithNoParameters() {
        ORM::for_table('widget')->where_raw('`name` = "Fred"')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `name` = \"Fred\"";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawWhereClauseInMethodChain() {
        ORM::for_table('widget')->where('age', 18)->where_raw('(`name` = ? OR `name` = ?)', array('Fred', 'Bob'))->where('size', 'large')->find_many();
        $expected = "SELECT * FROM `widget` WHERE `age` = '18' AND (`name` = 'Fred' OR `name` = 'Bob') AND `size` = 'large'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawQuery() {
        ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w')->find_many();
        $expected = "SELECT `w`.* FROM `widget` w";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testRawQueryWithParameters() {
        ORM::for_table('widget')->raw_query('SELECT `w`.* FROM `widget` w WHERE `name` = ? AND `age` = ?', array('Fred', 5))->find_many();
        $expected = "SELECT `w`.* FROM `widget` w WHERE `name` = 'Fred' AND `age` = '5'";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSimpleResultColumn() {
        ORM::for_table('widget')->select('name')->find_many();
        $expected = "SELECT `name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMultipleSimpleResultColumns() {
        ORM::for_table('widget')->select('name')->select('age')->find_many();
        $expected = "SELECT `name`, `age` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSpecifyTableNameAndColumnInResultColumns() {
        ORM::for_table('widget')->select('widget.name')->find_many();
        $expected = "SELECT `widget`.`name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testAliasesInResultColumns() {
        ORM::for_table('widget')->select('widget.name', 'widget_name')->find_many();
        $expected = "SELECT `widget`.`name` AS `widget_name` FROM `widget`";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testCount() {
        ORM::for_table('widget')->count();
        $expected = "SELECT COUNT(*) AS `count` FROM `widget` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMax() {
        ORM::for_table('person')->max('height');
        $expected = "SELECT MAX(`height`) AS `max` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testMin() {
        ORM::for_table('person')->min('height');
        $expected = "SELECT MIN(`height`) AS `min` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testAvg() {
        ORM::for_table('person')->avg('height');
        $expected = "SELECT AVG(`height`) AS `avg` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

    public function testSum() {
        ORM::for_table('person')->sum('height');
        $expected = "SELECT SUM(`height`) AS `sum` FROM `person` LIMIT 1";
        $this->assertEquals($expected, ORM::get_last_query());
    }

}

