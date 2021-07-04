<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\IQueryPart;

interface IUpdate extends IStatement
{
    public function __construct(string|Table ...$tables);

    public function modifier(string ...$modifiers): static;

    public function setValues(array $values): static;

    public function where(string|IQueryPart ...$whereParts): static;

    public function values(): array;
}
