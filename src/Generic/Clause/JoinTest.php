<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;

class JoinTest extends TestCase
{
    public function toStringGetParamsProvider(): array
    {
        return [
            [
                IJoin::TYPE_INNER_JOIN,
                'foo',
                new Expr('f.id = bar.foo_id AND ?', [1]),
                'f',
                'INNER JOIN foo AS f ON f.id = bar.foo_id AND ?',
                [[1, \PDO::PARAM_INT]],
            ],
            [
                IJoin::TYPE_LEFT_JOIN,
                'foo',
                'foo.id = bar.foo_id',
                null,
                'LEFT JOIN foo ON foo.id = bar.foo_id',
                [],
            ],
        ];
    }

    /**
     * @dataProvider toStringGetParamsProvider
     *
     * @param string            $type
     * @param string            $tableName
     * @param IQueryPart|string $on
     * @param string|null       $alias
     * @param string            $expectedSql
     * @param array             $expectedParams
     */
    public function testToStringGetParamsProvider(
        string $type,
        string $tableName,
        IQueryPart|string $on,
        ?string $alias,
        string $expectedSql,
        array $expectedParams
    ) {
        $sut = new Join($type, $tableName, $on, $alias);

        $actualSql    = (string)$sut;
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }

    /**
     * @suppress PhanNoopNew
     */
    public function testInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Join('foo', 'bar', 'bar.id = foo.bar_id', 'b');
    }
}
