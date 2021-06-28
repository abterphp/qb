<?php

declare(strict_types=1);

namespace QB\MySQL\QueryBuilder;

use QB\Generic\QueryBuilder\IQueryBuilder;
use QB\Generic\QueryBuilder\QueryBuilderTest as GenericQueryBuilderTest;
use QB\MySQL\Statement\Select;

class QueryBuilderTest extends GenericQueryBuilderTest
{
    /** @var QueryBuilder */
    protected IQueryBuilder $sut;

    public function setUp(): void
    {
        $this->sut = new QueryBuilder();
    }

    public function testSelect()
    {
        $select = $this->sut->select()->addFrom('foo');

        $this->assertInstanceOf(Select::class, $select);
        $this->assertSame("SELECT *\nFROM foo", (string)$select);
    }
}
