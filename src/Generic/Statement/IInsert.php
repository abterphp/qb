<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface IInsert extends IStatement
{
    public function into(string|Table $table): static;

    public function modifier(string ...$modifiers): static;

    public function columns(string ...$columns): static;

    public function values(...$values): static;
}
