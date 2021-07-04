<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;
use QB\Generic\Statement\Select;

class JoinTest extends TestCase
{
    public function toStringGetParamsProvider(): array
    {
        return [
            [
                IJoin::TYPE_INNER_JOIN,
                new Table('foo', 'f'),
                new Expr('f.id = bar.foo_id AND ?', [1]),
                'INNER JOIN foo AS f ON f.id = bar.foo_id AND ?',
                [[1, PDO::PARAM_INT]],
            ],
            [
                IJoin::TYPE_FULL_JOIN,
                new QueryAsTable(new Select(new Column(new Expr('?', [123]))), 'f'),
                null,
                'FULL JOIN (SELECT ?) AS f',
                [[123, PDO::PARAM_INT]],
            ],
            [
                IJoin::TYPE_LEFT_JOIN,
                'foo',
                'foo.id = bar.foo_id',
                'LEFT JOIN foo ON foo.id = bar.foo_id',
                [],
            ],
        ];
    }

    /**
     * @dataProvider toStringGetParamsProvider
     *
     * @param string                 $type
     * @param ITable|string          $table
     * @param IQueryPart|string|null $on
     * @param string                 $expectedSql
     * @param array                  $expectedParams
     */
    public function testToStringGetParamsProvider(
        string $type,
        ITable|string $table,
        IQueryPart|string|null $on,
        string $expectedSql,
        array $expectedParams
    ) {
        $sut = new Join($type, $table, $on);

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
        $this->expectException(InvalidArgumentException::class);

        new Join('foo', new Table('bar', 'b'), 'bar.id = foo.bar_id');
    }
}
