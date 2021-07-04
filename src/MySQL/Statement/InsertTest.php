<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Expr\Expr;
use QB\Generic\Statement\InsertTest as GenericInsertTest;

class InsertTest extends GenericInsertTest
{
    public function testOnDuplicateKeyUpdate()
    {
        $sql = (string)$this->getSut('foo')
            ->modifier(Insert::HIGH_PRIORITY)
            ->addValues('1234', '2345')
            ->setOnDuplicateKeyUpdate(new Expr('bar = bar + 1'));

        $parts   = [];
        $parts[] = 'INSERT HIGH_PRIORITY INTO foo';
        $parts[] = 'VALUES (1234, 2345)';
        $parts[] = 'ON DUPLICATE KEY UPDATE bar = bar + 1';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testComplex()
    {
        $select = new Select();
        $select->addColumn(new Expr('1'));

        $sql = (string)$this->getSut('foo')
            ->modifier(Insert::IGNORE)
            ->setSelect($select);

        $parts   = [];
        $parts[] = 'INSERT IGNORE INTO foo';
        $parts[] = 'SELECT 1';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string $table
     *
     * @return Insert
     */
    protected function getSut(string $table): Insert
    {
        return (new Insert())->into($table);
    }
}
