<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;

class ExprTest extends TestCase
{
    public function testToStringWithoutParams()
    {
        $expectedResult = 'COUNT(foo)';

        $sut = new Expr($expectedResult);

        $actualResult = (string)$sut;

        $this->assertSame($expectedResult, $actualResult);
    }

    public function testToStringWithUntypedUnnamedParams()
    {
        $expectedSql    = 'COUNT(?)';
        $params         = ['bar'];
        $expectedParams = [['bar', \PDO::PARAM_STR]];

        $sut = new Expr($expectedSql);
        $sut->bindParams($params);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }

    public function testToStringWithUntypedNamedParams()
    {
        $expectedSql    = 'COUNT(:foo)';
        $params         = [':foo' => 'bar'];
        $expectedParams = [':foo' => ['bar', \PDO::PARAM_STR]];

        $sut = $this->createSut($expectedSql);
        $sut->bindParams($params);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }

    public function testToStringWithMultipleUntypedAndUnnamedParams()
    {
        $expectedSql    = 'COUNT(?) + ?';
        $params         = ['col', 6];
        $expectedParams = [['col', \PDO::PARAM_STR], [6, \PDO::PARAM_STR]];

        $sut = $this->createSut($expectedSql);
        $sut->bindParams($params);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }

    public function testToStringWithMultipleUnnamedParams()
    {
        $expectedSql = 'COUNT(?) + ?';
        $params      = [['col', \PDO::PARAM_STR], [6, \PDO::PARAM_INT]];

        $sut = $this->createSut($expectedSql);
        $sut->bindParams($params);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($params, $actualParams);
    }

    public function testBindingParamWitInvalidNameThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);

        $sql    = 'COUNT(:foo)';
        $params = [':bar' => ['col', \PDO::PARAM_STR]];

        $sut = $this->createSut($sql);
        $sut->bindParams($params);
    }

    public function testBindingTooManyUnnamedParamsThrowsException()
    {
        $this->expectException(LogicException::class);

        $sql    = 'COUNT(?)';
        $params = [['col', \PDO::PARAM_STR], [6, \PDO::PARAM_INT]];

        $sut = $this->createSut($sql);
        $sut->bindParams($params);
    }

    /**
     * @param string $sql
     * @param array  $params
     *
     * @return Expr
     */
    protected function createSut(string $sql, array $params = []): Expr
    {
        return new Expr($sql, $params);
    }
}
