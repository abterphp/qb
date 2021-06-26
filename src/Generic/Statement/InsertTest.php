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

    public function testInsertComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->addColumns('id', 'bar_id')
            ->addValues('1234', '2345')
            ->addValues('3456', '4567');

        $parts   = [];
        $parts[] = 'INSERT INTO foo (id, bar_id)';
        $parts[] = 'VALUES (?, ?),';
        $parts[] = '(?, ?)';

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
