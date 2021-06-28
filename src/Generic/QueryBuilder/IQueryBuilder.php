<?php

declare(strict_types=1);

namespace QB\Generic\QueryBuilder;

use QB\Generic\Clause\Table;
use QB\Generic\Statement\IDelete;
use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\ITruncate;
use QB\Generic\Statement\IUpdate;

interface IQueryBuilder
{
    /**
     * @return ISelect
     */
    public function select(): ISelect;

    /**
     * @return IInsert
     */
    public function insert(): IInsert;

    /**
     * @param string|Table ...$tables
     *
     * @return IUpdate
     */
    public function update(string|Table ...$tables): IUpdate;

    /**
     * @return IDelete
     */
    public function delete(): IDelete;

    /**
     * @return ITruncate
     */
    public function truncate(): ITruncate;
}
