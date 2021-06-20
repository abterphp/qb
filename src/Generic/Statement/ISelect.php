<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\IJoin;
use QB\Generic\Clause\Table;
use QB\Generic\IQueryPart;

interface ISelect extends IStatement
{
    public function addFrom(string|Table ...$tables): static;

    public function addModifier(string ...$modifiers): static;

    public function addColumn(string $column, ?string $alias = null): static;

    public function addColumns(string|IColumn ...$columns): static;

    public function addInnerJoin(string $table, string|IQueryPart $on, ?string $alias = null): static;

    public function addLeftJoin(string $table, string|IQueryPart $on, ?string $alias = null): static;

    public function addRightJoin(string $table, string|IQueryPart $on, ?string $alias = null): static;

    public function addFullJoin(string $table, string|IQueryPart $on, ?string $alias = null): static;

    public function addJoin(IJoin ...$joins): static;

    public function addWhere(string|IQueryPart ...$whereParts): static;

    public function addGroupBy(string|IQueryPart ...$groupByParts): static;

    public function addHaving(string|IQueryPart ...$havingParts): static;

    public function addOrderBy(string $column, string $direction = 'ASC'): static;

    public function setOffset(?int $offset): static;

    public function setLimit(?int $limit): static;
}
