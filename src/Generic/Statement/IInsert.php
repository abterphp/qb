<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

interface IInsert extends IStatement
{
    public function setInto(string|Table $table): static;

    public function addModifier(string ...$modifiers): static;

    public function setColumns(string ...$columns): static;

    public function addValues(...$values): static;

    public function getValues(): array;
}
