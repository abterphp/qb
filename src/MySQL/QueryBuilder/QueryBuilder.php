<?php

declare(strict_types=1);

namespace QB\MySQL\QueryBuilder;

use QB\Generic\QueryBuilder\QueryBuilder as GenericQueryBuilder;
use QB\Generic\Statement\ISelect;
use QB\MySQL\Statement\Select;

class QueryBuilder extends GenericQueryBuilder
{
    /**
     * @return Select
     */
    public function select(): ISelect
    {
        return new Select();
    }
}
