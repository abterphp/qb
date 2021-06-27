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
            ->addModifier(Insert::HIGH_PRIORITY)
            ->addValues('1234', '2345')
            ->setOnDuplicateKeyUpdate(new Expr('bar = bar + 1'));

        $parts   = [];
        $parts[] = 'INSERT HIGH_PRIORITY INTO foo';
        $parts[] = 'VALUES (?, ?)';
        $parts[] = 'ON DUPLICATE KEY UPDATE bar = bar + 1';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testInsertSelect()
    {
        $select = new Select();
        $select->addColumn(new Expr('1'));

        $sql = (string)$this->getSut('foo')
            ->setSelect($select);

        $parts   = [];
        $parts[] = 'INSERT INTO foo';
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
        return (new Insert())->setInto($table);
    }
}
