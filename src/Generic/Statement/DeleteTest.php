<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class DeleteTest extends TestCase
{
    public function testDeleteSimple()
    {
        $sql = $this->getSut('foo')
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->__toString();

        $parts   = [];
        $parts[] = 'DELETE FROM foo';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string ...$tables
     *
     * @return IDelete
     */
    protected function getSut(string ...$tables): IDelete
    {
        return (new Delete())->addFrom(...$tables);
    }
}
