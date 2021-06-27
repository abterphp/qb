<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\Select;

class JoinQueryTest extends TestCase
{
    public function toStringGetParamsProvider(): array
    {
        $select1 = (new Select())->addFrom('foo');
        $on1     = new Expr('f.id = bar.foo_id AND ?', [1]);

        $select2 = (new Select())->addFrom('foo')->addWhere(new Expr('bar = ?', ['baz']));
        $on2     = $on1;

        return [
            [
                IJoin::TYPE_INNER_JOIN,
                $select1,
                $on1,
                'f',
                'INNER JOIN (SELECT * FROM foo) AS f ON f.id = bar.foo_id AND ?',
                [[1, \PDO::PARAM_INT]],
            ],
            [
                IJoin::TYPE_LEFT_JOIN,
                $select2,
                $on2,
                'f',
                'LEFT JOIN (SELECT * FROM foo WHERE bar = ?) AS f ON f.id = bar.foo_id AND ?',
                [['baz', \PDO::PARAM_STR], [1, \PDO::PARAM_INT]],
            ],
        ];
    }

    /**
     * @dataProvider toStringGetParamsProvider
     *
     * @param string $type
     * @param Select $subQuery
     * @param Expr   $on
     * @param string $alias
     * @param string $expectedSql
     * @param array  $expectedParams
     */
    public function testToStringGetParamsProvider(
        string $type,
        Select $subQuery,
        Expr $on,
        string $alias,
        string $expectedSql,
        array $expectedParams
    ) {
        $sut = new JoinQuery($type, $subQuery, $on, $alias);

        $actualSql    = $sut->__toString();
        $actualParams = $sut->getParams();

        $this->assertSame($expectedSql, $actualSql);
        $this->assertSame($expectedParams, $actualParams);
    }
}
