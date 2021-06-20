<?php

declare(strict_types=1);

namespace QB\Generic\Factory;;

use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\IDelete;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\IUpdate;
use QB\Generic\Statement\ITruncate;

interface IFactory
{
    public function select(): ISelect;

    public function insert(): IInsert;

    public function update(): IUpdate;

    public function delete(): IDelete;

    public function truncate(): ITruncate;
}
