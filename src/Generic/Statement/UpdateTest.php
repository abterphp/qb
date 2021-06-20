<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class UpdateTest extends TestCase
{
    public function testUpdateSimple()
    {
        $sql = (string)$this->getSut('foo')
            ->setValues(['id' => '1234', 'bar_id' => '2345'])
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']));

        $parts   = [];
        $parts[] = 'UPDATE foo';
        $parts[] = 'SET id = ?, bar_id = ?';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string ...$tables
     *
     * @return IUpdate
     */
    protected function getSut(string ...$tables): IUpdate
    {
        return (new Update())->addFrom(...$tables);
    }
}
