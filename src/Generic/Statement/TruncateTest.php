<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PHPUnit\Framework\TestCase;

class TruncateTest extends TestCase
{
    /**
     * @suppress PhanNoopCast
     */
    public function testToStringThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(\RuntimeException::class);

        (string)$this->getSut();
    }

    public function testToStringSimple()
    {
        $sql = (string)$this->getSut('foo', 'bar');

        $parts   = [];
        $parts[] = 'TRUNCATE foo, bar';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParams()
    {
        $query = $this->getSut('foo', 'bar');

        $params = $query->getParams();

        $this->assertSame([], $params);
    }

    /**
     * @param string ...$tables
     *
     * @return ITruncate
     */
    protected function getSut(string ...$tables): ITruncate
    {
        return (new Truncate())->from(...$tables);
    }
}
