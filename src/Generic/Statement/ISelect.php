<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\IJoin;
use QB\Generic\Clause\ITable;
use QB\Generic\Clause\Table;
use QB\Generic\IQueryPart;

interface ISelect extends IWhereStatement
{
    public function __construct(string|IColumn ...$columns);

    public function from(string|Table ...$tables): static;

    public function modifier(string ...$modifiers): static;

    public function addColumn(string $column, ?string $alias = null): static;

    public function columns(IColumn|string ...$columns): static;

    public function innerJoin(ITable|string $table, IQueryPart|string $on): static;

    public function leftJoin(ITable|string $table, IQueryPart|string $on): static;

    public function rightJoin(ITable|string $table, IQueryPart|string $on): static;

    public function fullJoin(ITable|string $table, IQueryPart|string $on): static;

    public function join(IJoin ...$joins): static;

    public function groupBy(string|IQueryPart ...$groupByParts): static;

    public function having(string|IQueryPart ...$havingParts): static;

    public function orderBy(string $column, string $direction = 'ASC'): static;

    public function offset(?int $offset): static;

    public function limit(?int $limit): static;
}
