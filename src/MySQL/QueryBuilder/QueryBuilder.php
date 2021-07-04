<?php

declare(strict_types=1);

namespace QB\MySQL\QueryBuilder;

use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\Table;
use QB\Generic\QueryBuilder\QueryBuilder as GenericQueryBuilder;
use QB\MySQL\Statement\Delete;
use QB\MySQL\Statement\Insert;
use QB\MySQL\Statement\Select;
use QB\MySQL\Statement\Update;

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

    /**
     * @param Table|string ...$tables
     *
     * @return Update
     */
    public function update(Table|string ...$tables): Update
    {
        return new Update(...$tables);
    }

    /**
     * @return Delete
     */
    public function delete(): Delete
    {
        return new Delete();
    }
}
