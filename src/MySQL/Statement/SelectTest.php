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
            ->modifier(...$modifiers)
            ->columns('id', 'bar_id');

        $parts   = [];
        $parts[] = sprintf('SELECT %s id, bar_id', implode(' ', $modifiers));
        $parts[] = 'FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringWithOuterLimits()
    {
        $unionQuery = $this->getSut('baz')
            ->columns('id');

        $sql = (string)$this->getSut('foo')
            ->columns('id')
            ->union($unionQuery)
            ->outerLimit(10);

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
            ->columns('id');

        $sql = (string)$this->getSut('foo')
            ->columns('id')
            ->union($unionQuery)
            ->outerLimit(10)
            ->outerOffset(20);

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
            ->columns('id');

        $sql = (string)$this->getSut('foo')
            ->columns('id')
            ->union($unionQuery)
            ->outerLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT));

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
            ->columns('id');

        $sql = (string)$this->getSut('foo')
            ->columns('id')
            ->limit(10)
            ->union($unionQuery)
            ->outerLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_SKIP_LOCKED));

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
            ->columns('b')
            ->where(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $unionQuery = $this->getSut('baz')
            ->columns('b', 'f');

        $sql = (string)$this->getSut('foo', 'bar')
            ->modifier('DISTINCT')
            ->columns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->columns(new Column($columnExpr, 'now'))
            ->columns(new Column('bar.id', 'bar_id'))
            ->innerJoin(new Table('quix', 'q'), 'foo.id = q.foo_id')
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->groupBy('q.foo_id', new Expr('q.bar.id'))
            ->groupWithRollup()
            ->having('baz_count > 0')
            ->orderBy('baz_count')
            ->limit(10)
            ->offset(20)
            ->lock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT))
            ->union($unionQuery)
            ->outerLimit(25)
            ->outerOrderBy('baz_count', Select::DIRECTION_DESC);

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
     * @param Table|string ...$tables
     *
     * @return Select
     */
    protected function getSut(Table|string ...$tables): Select
    {
        return (new Select())->from(...$tables);
    }
}
