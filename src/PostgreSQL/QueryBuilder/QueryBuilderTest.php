<?php

declare(strict_types=1);

namespace QB\PostgreSQL\QueryBuilder;

use QB\Generic\QueryBuilder\IQueryBuilder;
use QB\Generic\QueryBuilder\QueryBuilderTest as GenericQueryBuilderTest;

class QueryBuilderTest extends GenericQueryBuilderTest
{
    /** @var QueryBuilder */
    protected IQueryBuilder $sut;

    public function setUp(): void
    {
        $this->sut = new QueryBuilder();
    }
}
