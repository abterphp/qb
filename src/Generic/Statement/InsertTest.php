<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;

class InsertTest extends TestCase
{
    public function testInsertSimple()
    {
        $sql = (string)$this->getSut('foo')->addValues('1234', '2345');

        $parts   = [];
        $parts[] = 'INSERT INTO foo';
        $parts[] = 'VALUES (?, ?)';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->addModifier('BAR')
            ->addColumns('id', 'bar_id', 'baz')
            ->addValues('1234', '2345', 'a')
            ->addValues('3456', '4567', 'b');

        $parts   = [];
        $parts[] = 'INSERT BAR INTO foo (id, bar_id, baz)';
        $parts[] = 'VALUES (?, ?, ?),';
        $parts[] = '(?, ?, ?)';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string $table
     *
     * @return IInsert
     */
    protected function getSut(string $table): IInsert
    {
        return (new Insert())->setInto($table);
    }
}
