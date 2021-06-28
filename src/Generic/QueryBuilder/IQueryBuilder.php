<?php

declare(strict_types=1);

namespace QB\Generic\QueryBuilder;

use QB\Generic\Statement\IDelete;
use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\ITruncate;
use QB\Generic\Statement\IUpdate;

interface IQueryBuilder
{
    public function select(): ISelect;

    public function insert(): IInsert;

    public function update(): IUpdate;

    public function delete(): IDelete;

    public function truncate(): ITruncate;
}
