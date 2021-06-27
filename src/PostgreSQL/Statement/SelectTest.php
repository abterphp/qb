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
            ->addModifier(Select::ALL, Select::DISTINCT)
            ->addColumns('id', 'bar_id');

        $parts   = [];
        $parts[] = 'SELECT ALL DISTINCT id, bar_id';
        $parts[] = 'FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $unionQuery = $this->getSut('baz')
            ->addColumns('id');

        $sql = (string)$this->getSut('foo')
            ->addColumns('id')
            ->addUnion($unionQuery)
            ->setOuterOffset(20)
            ->setOuterLimit(10)
            ->setOuterOrderBy('id', 'DESC');

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
            ->addHaving('baz_count > 0')
            ->addOrderBy('baz_count', 'ASC')
            ->setLock(new Lock(Lock::FOR_UPDATE, [], Lock::MODIFIER_NOWAIT))
            ->setLimit(10)
            ->setOffset(20)
            ->addUnion($unionQuery);

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
            ->addColumns('b')
            ->addWhere(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $intersectQuery = $this->getSut('baz')
            ->addColumns('b', 'f');

        $sql = (string)$this->getSut('foo', 'bar')
            ->addModifier('DISTINCT')
            ->addColumns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->addColumns(new Column($columnExpr, 'now'))
            ->addColumn('bar.id', 'bar_id')
            ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
            ->addHaving('baz_count > 0')
            ->addOrderBy('baz_count', 'ASC')
            ->setLock(new Lock(Lock::FOR_UPDATE, ['foo'], Lock::MODIFIER_NOWAIT))
            ->setLimit(10)
            ->setOffset(20)
            ->addIntersect($intersectQuery);

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
            ->addColumns('b')
            ->addWhere(new Expr('id = ?', [7]));

        $columnExpr = new Expr('NOW()');

        $unionQuery = $this->getSut('baz')
            ->addColumns('b', 'f');

        $exceptQuery = $this->getSut('sec')
            ->addColumns('v', 'w');

        $sql = (string)$this->getSut('foo', 'bar')
            ->addModifier('DISTINCT')
            ->addColumns('COUNT(DISTINCT baz) AS baz_count', new Column($columnQuery, 'quix_b'))
            ->addColumns(new Column($columnExpr, 'now'))
            ->addColumn('bar.id', 'bar_id')
            ->addInnerJoin('quix', 'foo.id = q.foo_id', 'q')
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->addGroupBy('q.foo_id', new Expr('q.bar.id'))
            ->addHaving('baz_count > 0')
            ->addOrderBy('baz_count', 'ASC')
            ->setLock(new Lock(Lock::FOR_KEY_SHARE))
            ->setLimit(10)
            ->setOffset(20)
            ->addUnion($unionQuery)
            ->addExcept($exceptQuery)
            ->setOuterLimit(100);

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
        return (new Select())->addFrom(...$tables);
    }
}
