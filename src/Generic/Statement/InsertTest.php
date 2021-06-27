<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
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
            ->setColumns('a', 'b')
            ->addValues('A');
    }

    /**
     * @suppress PhanNoopCast
     */
    public function testAddColumnsThrowsAnExceptionIfCountIsWrong()
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getSut('foo')
            ->addValues('A')
            ->setColumns('a', 'b');
    }

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
            ->setColumns('id', 'bar_id', 'baz')
            ->addValues('1234', '2345', 'a')
            ->addValues('3456', '4567', 'b');

        $parts   = [];
        $parts[] = 'INSERT BAR INTO foo (id, bar_id, baz)';
        $parts[] = 'VALUES (?, ?, ?),';
        $parts[] = '(?, ?, ?)';

        $expectedSql = implode(PHP_EOL, $parts);

        $this->assertSame($expectedSql, $sql);
    }

    public function testGetParams()
    {
        $expectedParams = [['1234', PDO::PARAM_STR], ['2345', PDO::PARAM_STR]];

        $values = ['id' => '1234', 'bar_id' => '2345'];

        $query = $this->getSut('foo')
            ->addValues(...array_values($values))
            ->setColumns(...array_keys($values));

        $params = $query->getParams();

        $this->assertSame($expectedParams, $params);
    }

    public function testGetValues()
    {
        $values = ['id' => '1234', 'bar_id' => '2345'];

        $query = $this->getSut('foo')
            ->addValues(...array_values($values))
            ->setColumns(...array_keys($values));

        $actualValues = $query->getValues();

        $this->assertSame(array_values($values), $actualValues);
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
