<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\SelectTest as GenericSelectTest;
use QB\MySQL\Clause\Lock;

class SelectTest extends GenericSelectTest
{
    public function testToStringModifiers()
    {
        $modifiers = [
            Select::DISTINCT,
            Select::HIGH_PRIORITY,
            Select::STRAIGHT_JOIN,
            Select::SQL_SMALL_RESULT,
            Select::SQL_BIG_RESULT,
            Select::SQL_BUFFER_RESULT,
            Select::SQL_NO_CACHE,
            Select::SQL_CALC_FOUND_ROWS,
        ];

        $sql = (string)$this->getSut('foo')
            ->addModifier(...$modifiers)
            ->addColumns('id', 'bar_id');

        $parts   = [];
        $parts[] = sprintf('SELECT %s id, bar_id', implode(' ', $modifiers));
        $parts[] = 'FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithOuterLimits()
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

    public function testToStringWithOuterOffsetAndLimits()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = (string)$this->getSut('foo')
            ->addColumns('id')
            ->addUnion($unionQuery)
            ->setOuterLimit(10)
            ->setOuterOffset(20);

        $parts   = [];
        $parts[] = '(SELECT id';
        $parts[] = 'FROM foo';
        $parts[] = 'UNION';
        $parts[] = 'SELECT id';
        $parts[] = 'FROM baz)';
        $parts[] = 'LIMIT 20, 10';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithOuterLock()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = (string)$this->getSut('foo')
            ->addColumns('id')
            ->addUnion($unionQuery)
            ->setOuterLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT));

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

    public function testToStringWithLimitAndLock()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = (string)$this->getSut('foo')
            ->addColumns('id')
            ->setLimit(10)
            ->addUnion($unionQuery)
            ->setOuterLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_SKIP_LOCKED));

        $parts   = [];
        $parts[] = '(SELECT id';
        $parts[] = 'FROM foo';
        $parts[] = 'LIMIT 10';
        $parts[] = 'UNION';
        $parts[] = 'SELECT id';
        $parts[] = 'FROM baz)';
        $parts[] = 'FOR UPDATE SKIP LOCKED';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $columnQuery = $this->getSut('quix')
            ->addColumns('b')
            ->addWhere(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $unionQuery = $this->getSut('baz')
            ->addColumns('b', 'f');

        $sql = (string)$this->getSut('foo', 'bar')
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
            ->setLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT))
            ->addUnion($unionQuery)
            ->setOuterLimit(25)
            ->setOuterOrderBy('baz_count', 'DESC');

        $parts   = [];
        $parts[] = '(SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id'; // nolint
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
        $parts[] = 'FROM baz)';
        $parts[] = 'ORDER BY baz_count DESC';
        $parts[] = 'LIMIT 25';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string|Table ...$tables
     *
     * @return Select
     */
    protected function getSut(string|Table ...$tables): Select
    {
        return (new Select())->addFrom(...$tables);
    }
}
