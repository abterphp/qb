<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Expr\Expr;
use QB\Generic\Statement\IUpdate;
use QB\Generic\Statement\UpdateTest as GenericUpdateTest;

class UpdateTest extends GenericUpdateTest
{
    public function testUpdateSimple()
    {
        $sql = (string)$this->getSut('foo')
            ->addModifier(Update::LOW_PRIORITY)
            ->setValues(['id' => '1234', 'bar_id' => '2345'])
            ->addWhere('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']))
            ->setLimit(10);

        $parts   = [];
        $parts[] = 'UPDATE LOW_PRIORITY foo';
        $parts[] = 'SET id = ?, bar_id = ?';
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
    protected function getSut(string ...$tables): IUpdate
    {
        return (new Update())->addFrom(...$tables);
    }
}
