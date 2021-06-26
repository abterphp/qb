<?php

declare(strict_types=1);

namespace QB\Generic\Expr;

class SuperExprTest extends ExprTest
{

    public function testIn()
    {
        $expectedSql = 'col IN (?, ?, ?)';
        $expectedParams = [['col', \PDO::PARAM_STR], [8, \PDO::PARAM_STR], [6, \PDO::PARAM_INT]];

        $sql    = 'col IN (??)';
        $params = [[['col', \PDO::PARAM_STR], 8, [6, \PDO::PARAM_INT]]];

        $sut = $this->createSut($sql, $params);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }

    /**
     * @param string $sql
     * @param array  $params
     *
     * @return Expr
     */
    protected function createSut(string $sql, array $params = []): Expr
    {
        return new SuperExpr($sql, $params);
    }
}
