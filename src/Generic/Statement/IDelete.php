<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\IQueryPart;

interface IDelete extends IStatement
{
    public function addFrom(string|Table ...$tables): static;

    public function addWhere(string|IQueryPart ...$whereParts): static;
}
