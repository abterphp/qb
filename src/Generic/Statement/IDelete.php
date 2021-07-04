<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface IDelete extends IWhereStatement
{
    public function from(Table|string ...$tables): static;
}
