<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\IQueryPart;

interface IWhereStatement extends IStatement
{
    public function where(string|IQueryPart ...$whereParts): static;
}
