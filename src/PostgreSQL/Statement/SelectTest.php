<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\Clause\Column;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\SelectTest as GenericSelectTest;
use QB\PostgreSQL\Clause\Lock;

class SelectTest extends GenericSelectTest
{
    public function testToStringModifiers()
    {
        $sql = (string)$this->getSut('foo')
            ->modifier(Select::ALL, Select::DISTINCT)
            ->columns('id', 'bar_id');

        $parts   = [];
        $parts[] = 'SELECT ALL DISTINCT id, bar_id';
        $parts[] = 'FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $unionQuery = $this->getSut('baz')
            ->columns('id');

        $sql = (string)$this->getSut('foo')
            ->columns('id')
            ->union($unionQuery)
            ->outerOffset(20)
            ->outerLimit(10)
            ->outerOrderBy('id', Select::DIRECTION_DESC);

        $parts   = [];
        $parts[] = '(SELECT id';
        $parts[] = 'FROM foo';
        $parts[] = 'UNION';
        $parts[] = 'SELECT id';
        $parts[] = 'FROM baz)';
        $parts[] = 'ORDER BY id DESC';
        $parts[] = 'LIMIT 10';
        $parts[] = 'OFFSET 20 ROWS';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplexWithUnion()
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
            ->addColumn('bar.id', 'bar_id')
            ->innerJoin(new Table('quix', 'q'), 'foo.id = q.foo_id')
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->groupBy('q.foo_id', new Expr('q.bar.id'))
            ->having('baz_count > 0')
            ->orderBy('baz_count', 'ASC')
            ->lock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT))
            ->limit(10)
            ->offset(20)
            ->union($unionQuery);

        $parts   = [];
        $parts[] = 'SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id'; // nolint
        $parts[] = 'FROM foo, bar';
        $parts[] = 'INNER JOIN quix AS q ON foo.id = q.foo_id';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'GROUP BY q.foo_id, q.bar.id';
        $parts[] = 'HAVING baz_count > 0';
        $parts[] = 'ORDER BY baz_count ASC';
        $parts[] = 'OFFSET 20 ROWS';
        $parts[] = 'FETCH FIRST 10 ROWS ONLY';
        $parts[] = 'FOR UPDATE NOWAIT';
        $parts[] = 'UNION';
        $parts[] = 'SELECT b, f';
        $parts[] = 'FROM baz';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplexWithIntersect()
    {
        $columnQuery = $this->getSut('quix')
            ->columns('b')
            ->where(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $intersectQuery = $this->getSut('baz')
            ->columns('b', 'f');

        $sql = (string)$this->getSut('foo', 'bar')
            ->modifier('DISTINCT')
            ->columns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->columns(new Column($columnExpr, 'now'))
            ->addColumn('bar.id', 'bar_id')
            ->innerJoin(new Table('quix', 'q'), 'foo.id = q.foo_id')
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->groupBy('q.foo_id', new Expr('q.bar.id'))
            ->having('baz_count > 0')
            ->orderBy('baz_count', 'ASC')
            ->lock(new Lock(Lock::FOR_UPDATE, ['foo'], Lock::MODIFIER_NOWAIT))
            ->limit(10)
            ->offset(20)
            ->intersect($intersectQuery);

        $parts   = [];
        $parts[] = 'SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id'; // nolint
        $parts[] = 'FROM foo, bar';
        $parts[] = 'INNER JOIN quix AS q ON foo.id = q.foo_id';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'GROUP BY q.foo_id, q.bar.id';
        $parts[] = 'HAVING baz_count > 0';
        $parts[] = 'ORDER BY baz_count ASC';
        $parts[] = 'OFFSET 20 ROWS';
        $parts[] = 'FETCH FIRST 10 ROWS ONLY';
        $parts[] = 'FOR UPDATE OF foo NOWAIT';
        $parts[] = 'INTERSECT';
        $parts[] = 'SELECT b, f';
        $parts[] = 'FROM baz';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplexWithUnionAndExcept()
    {
        $columnQuery = $this->getSut('quix')
            ->columns('b')
            ->where(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $unionQuery = $this->getSut('baz')
            ->columns('b', 'f');

        $exceptQuery = $this->getSut('sec')
            ->columns('v', 'w');

        $sql = (string)$this->getSut('foo', 'bar')
            ->modifier('DISTINCT')
            ->columns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->columns(new Column($columnExpr, 'now'))
            ->addColumn('bar.id', 'bar_id')
            ->innerJoin(new Table('quix', 'q'), 'foo.id = q.foo_id')
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->groupBy('q.foo_id', new Expr('q.bar.id'))
            ->having('baz_count > 0')
            ->orderBy('baz_count', 'ASC')
            ->lock(new Lock(Lock::FOR_KEY_SHARE))
            ->limit(10)
            ->offset(20)
            ->union($unionQuery)
            ->except($exceptQuery)
            ->outerLimit(100);

        $parts   = [];
        $parts[] = '(SELECT DISTINCT COUNT(DISTINCT baz) AS baz_count, (SELECT b FROM quix WHERE id = ?) AS quix_b, NOW() AS now, bar.id AS bar_id'; // nolint
        $parts[] = 'FROM foo, bar';
        $parts[] = 'INNER JOIN quix AS q ON foo.id = q.foo_id';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'GROUP BY q.foo_id, q.bar.id';
        $parts[] = 'HAVING baz_count > 0';
        $parts[] = 'ORDER BY baz_count ASC';
        $parts[] = 'OFFSET 20 ROWS';
        $parts[] = 'FETCH FIRST 10 ROWS ONLY';
        $parts[] = 'FOR KEY SHARE';
        $parts[] = 'UNION';
        $parts[] = 'SELECT b, f';
        $parts[] = 'FROM baz';
        $parts[] = 'EXCEPT';
        $parts[] = 'SELECT v, w';
        $parts[] = 'FROM sec)';
        $parts[] = 'LIMIT 100';

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
        return (new Select())->from(...$tables);
    }
}
