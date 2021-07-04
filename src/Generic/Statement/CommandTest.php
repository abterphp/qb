<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use PDO;
use PHPUnit\Framework\TestCase;
use QB\Generic\Clause\Column;
use QB\Generic\Expr\Expr;

class CommandTest extends TestCase
{
    public function testToStringSimple()
    {
        $command = 'BEGIN TRANSACTION';

        $query = new Command($command);

        $sql = (string)$query;

        $this->assertSame($command, $sql);
    }

    public function testGetParams()
    {
        $command = 'EXPLAIN %s';

        $query = (new Select())->columns(new Column(new Expr('?', [2])));

        $query = new Command($command, $query);

        $sql    = (string)$query;
        $params = $query->getParams();

        $expectedSql = 'EXPLAIN SELECT ?';
        $expectedParams = [[2, PDO::PARAM_INT]];

        $this->assertSame($expectedSql, $sql);
        $this->assertSame($expectedParams, $params);
    }

    /**
     * @param string ...$tables
     *
     * @return IDelete
     */
    protected function getSut(string ...$tables): IDelete
    {
        return (new Delete())->from(...$tables);
    }
}
