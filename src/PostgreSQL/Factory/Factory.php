<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Factory;

use QB\Generic\Clause\Table;
use QB\Generic\Statement\ISelect;
use QB\Generic\Factory\Factory as GenericFactory;
use QB\PostgreSQL\Statement\Select;

class Factory extends GenericFactory
{
    /**
     * @return Select
     */
    public function select(): ISelect
    {
        return new Select();
    }
}