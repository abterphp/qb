<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;

class SelectTest extends TestCase
{
    public function testSelectThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(\RuntimeException::class);

        (string)$this->getSut();
    }

    public function testSelectConstant()
    {
        $sql = (string)$this->getSut()->addColumn('1');

        $this->assertSame('SELECT 1', $sql);
    }

    public function testSelectExpressions()
    {
        $sql = (string)$this->getSut()->addColumn('COUNT(foo)', 'foo_count');

        $this->assertSame('SELECT COUNT(foo) AS foo_count', $sql);
    }

    public function testSelectFromTwoTables()
    {
        $sql = (string)$this->getSut('foo', 'bar');

        $expectedSql = "SELECT *\nFROM foo, bar";

        $this->assertSame($expectedSql, $sql);
    }

    public function testSelectFromTwoTablesWithOneAlias()
    {
        $sql = (string)$this->getSut(new Table('foo', 'f'), 'bar');

        $expectedSql = "SELECT *\nFROM foo AS f, bar";

        $this->assertSame($expectedSql, $sql);
    }

    public function testSelectComplex()
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
