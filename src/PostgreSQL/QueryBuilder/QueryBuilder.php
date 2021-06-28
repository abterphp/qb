<?php

declare(strict_types=1);

namespace QB\PostgreSQL\QueryBuilder;

use QB\Generic\QueryBuilder\QueryBuilder as GenericQueryBuilder;
use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\ISelect;
use QB\PostgreSQL\Statement\Insert;
use QB\PostgreSQL\Statement\Select;

class QueryBuilder extends GenericQueryBuilder
{
    /**
     * @return Select
     */
    public function select(): ISelect
    {
        return new Select();
    }
    /**
     * @return Insert
     */
    public function insert(): IInsert
    {
        return new Insert();
    }
}
