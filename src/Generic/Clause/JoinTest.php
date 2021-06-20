<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

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
                [[1, \PDO::PARAM_STR]],
            ],
            [
                IJoin::TYPE_LEFT_JOIN,
                'foo',
                new Expr('foo.id = bar.foo_id AND ?', [1]),
                null,
                'LEFT JOIN foo ON foo.id = bar.foo_id AND ?',
                [[1, \PDO::PARAM_STR]],
            ],
        ];
    }

    /**
     * @dataProvider toStringGetParamsProvider
     *
     * @param string      $type
     * @param string      $tableName
     * @param Expr        $on
     * @param string|null $alias
     * @param string      $expectedSql
     * @param array       $expectedParams
     */
    public function testToStringGetParamsProvider(
        string $type,
        string $tableName,
        Expr $on,
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
}
