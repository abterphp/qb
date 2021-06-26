<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\SelectTest as GenericSelectTest;

class SelectTest extends GenericSelectTest
{
    public function testSelectModifiers()
    {
        $sql = $this->getSut('foo')
            ->addModifier(Select::ALL, Select::DISTINCT, Select::SQL_CALC_FOUND_ROWS)
            ->addColumns('id', 'bar_id')
            ->__toString();

        $parts   = [];
        $parts[] = 'SELECT DISTINCT SQL_CALC_FOUND_ROWS id, bar_id';
        $parts[] = 'FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testSelectWithOuterLimits()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = (string)$this->getSut('foo')
            ->addColumns('id')
            ->addUnion($unionQuery)
            ->setOuterLimit(10);

        $parts   = [];
        $parts[] = '(SELECT id';
        $parts[] = 'FROM foo';
        $parts[] = 'UNION';
        $parts[] = 'SELECT id';
        $parts[] = 'FROM baz)';
        $parts[] = 'LIMIT 10';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testSelectWithOuterLock()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = $this->getSut('foo')
            ->addColumns('id')
            ->addUnion($unionQuery)
            ->addOuterLock(Select::LOCK_FOR_UPDATE, Select::LOCK_NOWAIT)
            ->__toString();

        $parts   = [];
        $parts[] = '(SELECT id';
        $parts[] = 'FROM foo';
        $parts[] = 'UNION';
        $parts[] = 'SELECT id';
        $parts[] = 'FROM baz)';
        $parts[] = 'FOR UPDATE NOWAIT';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testSelectComplex()
    {
        $columnQuery = $this->getSut('quix')
            ->addColumns('b')
            ->addWhere(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $unionQuery = $this->getSut('baz')
            ->addColumns('b', 'f');

        $sql = $this->getSut('foo', 'bar')
            ->addModifier('DISTINCT')
            ->addColumns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->addColumns(new Column($columnExpr, 'now'))
            ->addColumn('bar.id', 'bar_id')
            ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
            ->setGroupWithRollup()
            ->addHaving('baz_count > 0')
            ->addOrderBy('baz_count', 'ASC')
            ->setLimit(10)
            ->setOffset(20)
            ->addLock(Select::LOCK_FOR_UPDATE, Select::LOCK_NOWAIT)
            ->addUnion($unionQuery)
            ->__toString();

        $parts   = [];
        $parts[] = 'SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id'; // nolint
        $parts[] = 'FROM foo, bar';
        $parts[] = 'INNER JOIN quix AS q ON foo.id = q.foo_id';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'GROUP BY q.foo_id, q.bar.id WITH ROLLUP';
        $parts[] = 'HAVING baz_count > 0';
        $parts[] = 'ORDER BY baz_count ASC';
        $parts[] = 'LIMIT 20, 10';
        $parts[] = 'FOR UPDATE NOWAIT';
        $parts[] = 'UNION';
        $parts[] = 'SELECT b, f';
        $parts[] = 'FROM baz';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string|Table ...$tables
     *
     * @return Select
     */
    protected function getSut(string|Table ...$tables): ISelect
    {
        return (new Select())->addFrom(...$tables);
    }
}
