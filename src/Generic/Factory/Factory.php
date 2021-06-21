<?php

declare(strict_types=1);

namespace QB\Generic\Factory;

use QB\Generic\Statement\IInsert;
use QB\Generic\Statement\IDelete;
use QB\Generic\Statement\ISelect;
use QB\Generic\Statement\IUpdate;
use QB\Generic\Statement\ITruncate;
use QB\Generic\Statement\Insert;
use QB\Generic\Statement\Delete;
use QB\Generic\Statement\Select;
use QB\Generic\Statement\Update;
use QB\Generic\Statement\Truncate;

class Factory implements IFactory
{
    /**
     * @return ISelect
     */
    public function select(): ISelect
    {
        return new Select();
    }

    /**
     * @return IInsert
     */
    public function insert(): IInsert
    {
        return new Insert();
    }

    /**
     * @return IUpdate
     */
    public function update(): IUpdate
    {
        return new Update();
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
