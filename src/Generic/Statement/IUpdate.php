<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use QB\Generic\IQueryPart;

interface IUpdate extends IStatement
{
    public function addFrom(string|Table ...$tables): static;

    public function addModifier(string ...$modifiers): static;

    public function setValues(array $values): static;

    public function addWhere(string|IQueryPart ...$whereParts): static;

    public function getValues(): array;
}
