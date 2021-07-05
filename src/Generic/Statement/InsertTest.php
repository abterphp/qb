<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use RuntimeException;

class InsertTest extends TestCase
{
    /**
     * @suppress PhanNoopCast
     */
    public function testToStringThrowsAnExceptionIfNotInitialized()
    {
        $this->expectException(RuntimeException::class);

        (string)$this->getSut('foo');
    }

    /**
     * @suppress PhanNoopCast
     */
    public function testAddValuesThrowsAnExceptionIfCountIsWrong()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getSut('foo')
            ->columns('a', 'b')
            ->values('A');
    }

    /**
     * @suppress PhanNoopCast
     */
    public function testAddColumnsThrowsAnExceptionIfCountIsWrong()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getSut('foo')
            ->values('A')
            ->columns('a', 'b');
    }

    public function testInsertSimple()
    {
        $sql = (string)$this->getSut('foo')->values('1234', ['2345', '3456'], null);

        $parts   = [];
        $parts[] = 'INSERT INTO foo';
        $parts[] = 'VALUES (1234, \'["2345","3456"]\', NULL)';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testComplex()
    {
        $sql = (string)$this->getSut('foo')
            ->modifier('BAR')
            ->columns('id', 'bar_id', 'baz')
            ->values('1234', new Expr('?', ['a']), '"a"')
            ->values('3456', '4567', '"b"');

        $parts   = [];
        $parts[] = 'INSERT BAR INTO foo (id, bar_id, baz)';
        $parts[] = 'VALUES (1234, ?, "a"),';
        $parts[] = '(3456, 4567, "b")';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParams()
    {
        $expectedParams = [[2345, PDO::PARAM_INT]];

        $values = ['id' => '1234', 'bar_id' =>  new Expr('?', [2345])];

        $query = $this->getSut('foo')
            ->values(...array_values($values))
            ->columns(...array_keys($values));

        $params = $query->getParams();

        $this->assertSame($expectedParams, $params);
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
