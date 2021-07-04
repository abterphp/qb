<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface IUpdate extends IWhereStatement
{
    public function __construct(string|Table ...$tables);

    public function modifier(string ...$modifiers): static;

    public function values(array $values): static;
}
