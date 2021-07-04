<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use RuntimeException;

class DeleteTest extends TestCase
{
    /**
     * @suppress PhanNoopCast
     */
    public function testToStringThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(RuntimeException::class);

        (string)$this->getSut();
    }

    public function testToStringSimple()
    {
        $sql = (string)$this->getSut('foo');

        $parts   = [];
        $parts[] = 'DELETE FROM foo';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->where('foo.bar = "foo-bar"', 'bar.foo = 17');

        $parts   = [];
        $parts[] = 'DELETE FROM foo';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = 17';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParams()
    {
        $expectedParams = [['bar-foo', PDO::PARAM_STR]];
        $query = $this->getSut('foo')
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']));

        $params = $query->getParams();

        $this->assertSame($expectedParams, $params);
    }

    /**
     * @param string ...$tables
     *
     * @return IDelete
     */
    protected function getSut(string ...$tables): IDelete
    {
        return (new Delete())->from(...$tables);
    }
}
