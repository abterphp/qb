<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\IQueryPart;

interface IStatement extends IQueryPart
{
    public function isValid(): bool;
}
