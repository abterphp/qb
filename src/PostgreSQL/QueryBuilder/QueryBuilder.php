<?php

declare(strict_types=1);

namespace QB\PostgreSQL\QueryBuilder;

use QB\Generic\Clause\IColumn;
use QB\Generic\QueryBuilder\QueryBuilder as GenericQueryBuilder;
use QB\PostgreSQL\Statement\Insert;
use QB\PostgreSQL\Statement\Select;

class QueryBuilder extends GenericQueryBuilder
{
    /**
     * @return Select
     */
    public function select(IColumn|string ...$columns): Select
    {
        return new Select(...$columns);
    }
    /**
     * @return Insert
     */
    public function insert(): Insert
    {
        return new Insert();
    }
}
