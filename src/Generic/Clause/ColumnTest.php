<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;

class ColumnTest extends TestCase
{
    public function testToStringWithoutAliasAndParams()
    {
        $colName = 'col';

        $sut = new Column($colName);

        $actualSql = (string)$sut;

        $this->assertSame($colName, $actualSql);
    }

    public function testToStringWithoutParams()
    {
        $expr   = new Expr('COUNT(col)');
        $column = 'col_count';

        $sut = new Column($expr, $column);

        $expectedSql  = 'COUNT(col) AS col_count';
        $actualResult = (string)$sut;

        $this->assertSame($expectedSql, $actualResult);
    }

    public function testToStringWithoutAlias()
    {
        $expectedSql = 'COUNT(col)';

        $expr = new Expr($expectedSql);

        $sut = new Column($expr);

        $actualResult = (string)$sut;

        $this->assertSame($expectedSql, $actualResult);
    }

    public function testGetParamsReturnsEmptyArrayIfColumnNameIsProvided()
    {
        $expectedSql = 'foo';

        $sut = new Column($expectedSql);

        $actualParams = $sut->getParams();

        $this->assertSame([], $actualParams);
    }

    public function testGetParamsReturnsExpressionParams()
    {
        $expr   = new Expr('LENGTH(:word) > 5', [':word' => 'foobar']);
        $column = 'is_word_long';

        $sut = new Column($expr, $column);

        $expectedParams = [':word' => ['foobar', PDO::PARAM_STR]];
        $actualParams   = $sut->getParams();

        $this->assertSame($expectedParams, $actualParams);
    }
}
