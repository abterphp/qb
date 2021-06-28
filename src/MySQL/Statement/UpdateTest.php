<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Expr\Expr;
use QB\Generic\Statement\UpdateTest as GenericUpdateTest;

class UpdateTest extends GenericUpdateTest
{
    public function testToStringComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->addModifier(Update::LOW_PRIORITY, Update::IGNORE)
            ->setValues(['id' => '1234', 'bar_id' => new Expr('?', [2345])])
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->setLimit(10);

        $parts   = [];
        $parts[] = 'UPDATE LOW_PRIORITY IGNORE foo';
        $parts[] = 'SET id = 1234, bar_id = ?';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';
        $parts[] = 'LIMIT 10';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string ...$tables
     *
     * @return Update
     */
    protected function getSut(string ...$tables): Update
    {
        return new Update(...$tables);
    }
}
