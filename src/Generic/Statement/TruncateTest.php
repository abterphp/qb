<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;

class TruncateTest extends TestCase
{
    public function testTruncateSimple()
    {
        $sql = (string)$this->getSut('foo', 'bar');

        $parts   = [];
        $parts[] = 'TRUNCATE foo, bar';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    /**
     * @param string ...$tables
     *
     * @return ITruncate
     */
    protected function getSut(string ...$tables): ITruncate
    {
        return (new Truncate())->addFrom(...$tables);
    }
}
