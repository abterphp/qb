<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Clause\Column;
use QB\Generic\Clause\IJoin;
use QB\Generic\Clause\Join;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class SelectTest extends TestCase
{
    /**
     * @suppress PhanNoopCast
     */
    public function testToStringThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(\RuntimeException::class);

        (string)$this->getSut();
    }

    public function testToStringConstant()
    {
        $sql = (string)$this->getSut()->addColumn('1');

        $this->assertSame('SELECT 1', $sql);
    }

    public function testToStringExpressions()
    {
        $sql = (string)$this->getSut()->addColumn('COUNT(foo)', 'foo_count');

        $this->assertSame('SELECT COUNT(foo) AS foo_count', $sql);
    }

    public function testToStringFromTwoTables()
    {
        $sql = (string)$this->getSut('foo', 'bar');

        $expectedSql = "SELECT *\nFROM foo, bar";

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringFromTwoTablesWithOneAlias()
    {
        $sql = (string)$this->getSut(new Table('foo', 'f'), 'bar');

        $expectedSql = "SELECT *\nFROM foo AS f, bar";

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringFromTwoTablesWithAliases()
    {
        $sql = (string)$this->getSut(new Table('foo', 'f'), new Table('bar', 'b'));

        $expectedSql = "SELECT *\nFROM foo AS f, bar AS b";

        $this->assertSame($expectedSql, $sql);
    }

    public function testWithInnerJoin()
    {
        $sql = (string)$this->getSut('foo')
            ->addInnerJoin('baz', 'foo.id = baz.foo_id');

        $parts   = [];
        $parts[] = "SELECT *";
        $parts[] = "FROM foo";
        $parts[] = "INNER JOIN baz ON foo.id = baz.foo_id";

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithLeftJoin()
    {
        $sql = (string)$this->getSut('foo')
            ->addLeftJoin('baz', new Expr('foo.id = b.foo_id'), 'b');

        $parts   = [];
        $parts[] = "SELECT *";
        $parts[] = "FROM foo";
        $parts[] = "LEFT JOIN baz AS b ON foo.id = b.foo_id";

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithRightJoin()
    {
        $sql = (string)$this->getSut(new Table('foo', 'f'))
            ->addRightJoin('baz', new Expr('f.id = b.foo_id'), 'b');

        $parts   = [];
        $parts[] = "SELECT *";
        $parts[] = "FROM foo AS f";
        $parts[] = "RIGHT JOIN baz AS b ON f.id = b.foo_id";

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithFullJoin()
    {
        $sql = (string)$this->getSut(new Table('foo', 'f'))
            ->addFullJoin('baz', new Expr('f.id = b.foo_id'), 'b');

        $parts   = [];
        $parts[] = "SELECT *";
        $parts[] = "FROM foo AS f";
        $parts[] = "FULL JOIN baz AS b ON f.id = b.foo_id";

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithManyJoins()
    {
        $join0 = new Join(IJoin::TYPE_INNER_JOIN, 'bar', 'b0.foo_id = foo.id', 'b0');
        $join1 = new Join(IJoin::TYPE_LEFT_JOIN, 'bar', 'b1.foo_id = foo.id AND FALSE', 'b1');
        $join2 = new Join(IJoin::TYPE_LEFT_JOIN, 'bar', 'b2.foo_id = foo.id AND 0', 'b2');

        $sql = (string)$this->getSut('foo')
            ->addColumn('foo.*')
            ->addJoin($join0, $join1, $join2);

        $parts   = [];
        $parts[] = "SELECT foo.*";
        $parts[] = "FROM foo";
        $parts[] = "INNER JOIN bar AS b0 ON b0.foo_id = foo.id";
        $parts[] = "LEFT JOIN bar AS b1 ON b1.foo_id = foo.id AND FALSE";
        $parts[] = "LEFT JOIN bar AS b2 ON b2.foo_id = foo.id AND 0";

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $sql = (string)$this->getSut()
            ->addFrom('foo', 'bar')
            ->addModifier('DISTINCT')
            ->addColumns('COUNT(DISTINCT baz) AS baz_count', 'q.foo_id')
            ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
            ->addHaving('baz_count > 0')
            ->addOrderBy('baz_count', 'ASC')
            ->setLimit(10)
            ->setOffset(20);

        $parts   = [];
        $parts[] = 'SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, q.foo_id';
        $parts[] = 'FROM foo, bar';
        $parts[] = 'INNER JOIN quix AS q ON foo.id = q.foo_id';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'GROUP BY q.foo_id, q.bar.id';
        $parts[] = 'HAVING baz_count > 0';
        $parts[] = 'ORDER BY baz_count ASC';
        $parts[] = 'OFFSET 20 ROWS';
        $parts[] = 'FETCH FIRST 10 ROWS ONLY';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParamsComplex()
    {
        $query = $this->getSut()
            ->addFrom('foo', 'bar')
            ->addColumns(new Column(new Expr('COUNT(*) + ?', [2]), 'cpp'))
            ->addLeftJoin('baz', new Expr('b.c < ?', [3]), 'b')
            ->addWhere(new Expr('foo.a IN (?)', [[4], [5]]))
            ->addGroupBy(new Expr('foo.c > ?', [6]))
            ->addHaving(new Expr('foo.maybe = ?', [7]));

        $expectedParams = [
            [2, PDO::PARAM_INT],
            [3, PDO::PARAM_INT],
            [4, PDO::PARAM_INT],
            [5, PDO::PARAM_INT],
            [6, PDO::PARAM_INT],
            [7, PDO::PARAM_INT],
        ];

        $this->assertSame($expectedParams, $query->getParams());
    }

    /**
     * @param string|Table ...$tables
     *
     * @return ISelect
     */
    protected function getSut(string|Table ...$tables): ISelect
    {
        return (new Select())->addFrom(...$tables);
    }
}
