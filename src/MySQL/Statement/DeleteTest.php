<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Expr\Expr;
use QB\Generic\Statement\DeleteTest as GenericDeleteTest;

class DeleteTest extends GenericDeleteTest
{
    public function testDeleteComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->addModifier(Delete::LOW_PRIORITY, Delete::QUICK, Delete::IGNORE)
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->addOrderBy('bar.baz', 'DESC')
            ->setLimit(10);

        $parts   = [];
        $parts[] = 'DELETE LOW_PRIORITY QUICK IGNORE FROM foo';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'ORDER BY bar.baz DESC';
        $parts[] = 'LIMIT 10';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string ...$tables
     *
     * @return Delete
     */
    protected function getSut(string ...$tables): Delete
    {
        return (new Delete())->addFrom(...$tables);
    }
}
