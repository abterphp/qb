<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface IInsert extends IStatement
{
    public function addFrom(string|Table ...$tables): static;

    public function addColumn(string $column, ?string $alias = null): static;

    public function addColumns(string ...$columns): static;

    public function addValues(...$values): static;

    public function getValues(): array;
}
