<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface ITruncate extends IStatement
{
    public function addFrom(string|Table ...$tables): static;
}
