<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\IJoin;
use QB\Generic\Clause\ITable;
use QB\Generic\IQueryPart;

interface ISelect extends IWhereStatement
{
    public const DIRECTION_ASC = 'ASC';
    public const DIRECTION_DESC = 'DESC';

    public function __construct(IColumn|string ...$columns);

    public function from(ITable|string ...$tables): static;

    public function modifier(string ...$modifiers): static;

    public function columns(IColumn|string ...$columns): static;

    public function innerJoin(ITable|string $table, IQueryPart|string|null $on): static;

    public function leftJoin(ITable|string $table, IQueryPart|string|null $on): static;

    public function rightJoin(ITable|string $table, IQueryPart|string|null $on): static;

    public function fullJoin(ITable|string $table, IQueryPart|string|null $on): static;

    public function join(IJoin ...$joins): static;

    public function groupBy(IQueryPart|string ...$groupByParts): static;

    public function having(IQueryPart|string ...$havingParts): static;

    public function orderBy(string $column, string $direction = self::DIRECTION_ASC): static;

    public function offset(?int $offset): static;

    public function limit(?int $limit): static;
}
