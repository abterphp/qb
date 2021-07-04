<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\ITable;

interface IInsert extends IStatement
{
    public function into(ITable|string $table): static;

    public function modifier(string ...$modifiers): static;

    public function columns(string ...$columns): static;

    public function values(...$values): static;
}
