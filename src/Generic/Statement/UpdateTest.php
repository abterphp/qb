<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class UpdateTest extends TestCase
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
        $sql = (string)$this->getSut('foo')
            ->setValues(['id' => '1234', 'bar_id' => '2345'])
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']));

        $parts   = [];
        $parts[] = 'UPDATE foo';
        $parts[] = 'SET id = 1234, bar_id = 2345';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testToStringComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->modifier('BAR')
            ->setValues(['id' => '1234', 'bar_id' => new Expr('?', [2345])])
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']));

        $parts   = [];
        $parts[] = 'UPDATE BAR foo';
        $parts[] = 'SET id = 1234, bar_id = ?';
        $parts[] = 'WHERE foo.bar = "foo-bar" AND bar.foo = ?';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParams()
    {
        $expectedParams = [[2345, PDO::PARAM_INT], ['bar-foo', PDO::PARAM_STR]];

        $query = $this->getSut('foo')
            ->setValues(['id' => '1234', 'bar_id' => new Expr('?', [2345])])
            ->where('foo.bar = "foo-bar"', new Expr('bar.foo = ?', ['bar-foo']));

        $params = $query->getParams();

        $this->assertSame($expectedParams, $params);
    }

    /**
     * @param string ...$tables
     *
     * @return IUpdate
     */
    protected function getSut(string ...$tables): IUpdate
    {
        return new Update(...$tables);
    }
}
