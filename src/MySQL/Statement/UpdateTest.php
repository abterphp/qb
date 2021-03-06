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
            ->modifier(Update::LOW_PRIORITY, Update::IGNORE)
            ->values(['id' => '1234', 'bar_id' => new Expr('?', [2345])])
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->limit(10);

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
