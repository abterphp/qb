<?php

declare(strict_types=1);

namespace QB\MySQL\Factory;

use QB\Generic\Statement\ISelect;
use QB\Generic\Factory\Factory as GenericFactory;
use QB\MySQL\Statement\Select;

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
