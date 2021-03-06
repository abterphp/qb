<?php

declare(strict_types=1);

namespace QB\Generic\QueryBuilder;

use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\Table;
use QB\Generic\Statement\Delete;
use QB\Generic\Statement\IDelete;
use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\Insert;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\ITruncate;
use QB\Generic\Statement\IUpdate;
use QB\Generic\Statement\Select;
use QB\Generic\Statement\Truncate;
use QB\Generic\Statement\Update;

class QueryBuilder implements IQueryBuilder
{
    /**
     * @return ISelect
     */
    public function select(IColumn|string ...$columns): ISelect
    {
        return new Select(...$columns);
    }

    /**
     * @return IInsert
     */
    public function insert(): IInsert
    {
        return new Insert();
    }

    /**
     * @param Table|string ...$tables
     *
     * @return IUpdate
     */
    public function update(Table|string ...$tables): IUpdate
    {
        return new Update(...$tables);
    }

    /**
     * @return IDelete
     */
    public function delete(): IDelete
    {
        return new Delete();
    }

    /**
     * @return ITruncate
     */
    public function truncate(): ITruncate
    {
        return new Truncate();
    }
}
