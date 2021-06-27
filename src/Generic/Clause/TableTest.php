<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PHPUnit\Framework\TestCase;

class TableTest extends TestCase
{
    public function testToStringWithoutAlias()
    {
        $tableName = 'foo';

        $sut = new Table($tableName);

        $expectedSql = $tableName;
        $actualSql   = (string)$sut;

        $this->assertSame($expectedSql, $actualSql);
    }

    public function testToStringWithAlias()
    {
        $tableName = 'foo';
        $alias     = 'f';

        $sut = new Table($tableName, $alias);

        $expectedSql = sprintf('%s AS %s', $tableName, $alias);
        $actualSql   = (string)$sut;

        $this->assertSame($expectedSql, $actualSql);
    }

    public function testGetParam()
    {
        $sut = new Table('foo', 'f');

        $actualParams = $sut->getParams();

        $this->assertSame([], $actualParams);
    }
}
