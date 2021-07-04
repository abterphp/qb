<?php

declare(strict_types=1);

namespace QB\Generic\Clause;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Expr\Expr;
use QB\Generic\Statement\Select;

class QueryAsTableTest extends TestCase
{
    public function testToStringFromStringQuery()
    {
        $expectedSql = "(SELECT 'foo') AS f";

        $query = "SELECT 'foo'";
        $alias = 'f';

        $sut       = new QueryAsTable($query, $alias);
        $actualSql = (string)$sut;

        $this->assertSame($expectedSql, $actualSql);
    }

    public function testToStringFromObjectQuery()
    {
        $expectedSql = "(SELECT 'foo') AS f";

        $query = new Select("'foo'");
        $alias = 'f';

        $sut       = new QueryAsTable($query, $alias);
        $actualSql = (string)$sut;

        $this->assertSame($expectedSql, $actualSql);
    }

    public function testGetParamFromStringQuery()
    {
        $expectedParams = [];

        $query = "SELECT 'foo'";
        $alias = 'f';

        $sut          = new QueryAsTable($query, $alias);
        $actualParams = $sut->getParams();

        $this->assertSame($expectedParams, $actualParams);
    }

    public function testGetParamFromObjectQuery()
    {
        $expectedParams = [['foo', PDO::PARAM_STR]];

        $query = new Select(
            new Column(new Expr('?', ['foo']))
        );
        $alias = 'f';

        $sut          = new QueryAsTable($query, $alias);
        $actualParams = $sut->getParams();

        $this->assertSame($expectedParams, $actualParams);
    }
}
