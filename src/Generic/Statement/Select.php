<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Column;
use QB\Generic\Clause\IColumn;
use QB\Generic\Clause\IJoin;
use QB\Generic\Clause\Join;
use QB\Generic\Clause\Table;
use QB\Generic\Expr\Expr;
use QB\Generic\IQueryPart;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * SuppressWarnings("complexity")
 */
class Select implements ISelect
{
    public const ALL      = 'ALL';
    public const DISTINCT = 'DISTINCT';

    /** @var array<int,string|Table> */
    protected array $tables = [];

    /** @var string[] */
    protected array $modifiers = [];

    /** @var IColumn[] */
    protected array $columns = [];

    /** @var IJoin[] */
    protected array $joins = [];

    /** @var IQueryPart[] */
    protected array $whereParts = [];

    /** @var IQueryPart[] */
    protected array $groupByParts = [];

    /** @var IQueryPart[] */
    protected array $havingParts = [];

    /** @var array<string,string> */
    protected array $orderByParts = [];

    protected ?int $offset = null;

    protected ?int $limit = null;

    /**
     * @param string|Table ...$tables
     *
     * @return $this
     */
    public function addFrom(string|Table ...$tables): static
    {
        $this->tables = array_merge($this->tables, $tables);

        return $this;
    }

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function addModifier(string ...$modifiers): static
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);

        return $this;
    }

    /**
     * @param string|IQueryPart $column
     * @param string|null       $alias
     *
     * @return $this
     */
    public function addColumn(string|IQueryPart $column, ?string $alias = null): static
    {
        $this->columns[] = new Column($column, $alias);

        return $this;
    }

    /**
     * @param string|IColumn ...$columns
     *
     * @return $this
     */
    public function addColumns(string|IColumn ...$columns): static
    {
        foreach ($columns as $column) {
            if ($column instanceof IColumn) {
                $this->columns[] = $column;
                continue;
            }

            if (strpos($column, ' AS ')) {
                $parts = explode(' AS ', $column);

                $this->columns[] = new Column($parts[0], $parts[1]);
            } else {
                $this->columns[] = new Column($column, null);
            }
        }

        return $this;
    }

    /**
     * @param string            $table
     * @param string|IQueryPart $on
     * @param string|null       $alias
     *
     * @return $this
     */
    public function addInnerJoin(string $table, string|IQueryPart $on, ?string $alias = null): static
    {
        $this->joins[] = new Join(IJoin::TYPE_INNER_JOIN, $table, $on, $alias);

        return $this;
    }

    /**
     * @param string            $table
     * @param string|IQueryPart $on
     * @param string|null       $alias
     *
     * @return $this
     */
    public function addLeftJoin(string $table, string|IQueryPart $on, ?string $alias = null): static
    {
        $this->joins[] = new Join(IJoin::TYPE_LEFT_JOIN, $table, $on, $alias);

        return $this;
    }

    /**
     * @param string            $table
     * @param string|IQueryPart $on
     * @param string|null       $alias
     *
     * @return $this
     */
    public function addRightJoin(string $table, string|IQueryPart $on, ?string $alias = null): static
    {
        $this->joins[] = new Join(IJoin::TYPE_RIGHT_JOIN, $table, $on, $alias);

        return $this;
    }

    /**
     * @param string            $table
     * @param string|IQueryPart $on
     * @param string|null       $alias
     *
     * @return $this
     */
    public function addFullJoin(string $table, string|IQueryPart $on, ?string $alias = null): static
    {
        $this->joins[] = new Join(IJoin::TYPE_FULL_JOIN, $table, $on, $alias);

        return $this;
    }

    /**
     * @param IJoin ...$joins
     *
     * @return $this
     */
    public function addJoin(IJoin ...$joins): static
    {
        $this->joins = array_merge($this->joins, $joins);

        return $this;
    }

    /**
     * @param string|IQueryPart ...$whereParts
     *
     * @return $this
     */
    public function addWhere(string|IQueryPart ...$whereParts): static
    {
        foreach ($whereParts as $wherePart) {
            $wherePart = is_string($wherePart) ? new Expr($wherePart) : $wherePart;

            $this->whereParts[] = $wherePart;
        }

        return $this;
    }

    /**
     * @param string|IQueryPart ...$groupByParts
     *
     * @return $this
     */
    public function addGroupBy(string|IQueryPart ...$groupByParts): static
    {
        foreach ($groupByParts as $groupByPart) {
            $groupByPart = is_string($groupByPart) ? new Expr($groupByPart) : $groupByPart;

            $this->groupByParts[] = $groupByPart;
        }

        return $this;
    }

    /**
     * @param string|IQueryPart ...$havingParts
     *
     * @return $this
     */
    public function addHaving(string|IQueryPart ...$havingParts): static
    {
        foreach ($havingParts as $havingPart) {
            $havingPart = is_string($havingPart) ? new Expr($havingPart) : $havingPart;

            $this->havingParts[] = $havingPart;
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function addOrderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderByParts[$column] = $direction;

        return $this;
    }

    /**
     * @param int|null $offset
     *
     * @return $this
     */
    public function setOffset(?int $offset): static
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function setLimit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('under-initialized SELECT query');
        }

        $select = $this->getSelect();

        if (count($this->tables) === 0) {
            return $select;
        }

        $parts = array_merge(
            [$select],
            $this->getFrom(),
            $this->getJoin(),
            $this->getWhere(),
            $this->getGroupBy(),
            $this->getHaving(),
            $this->getOrderBy(),
            $this->getLimit(),
        );

        $parts = array_filter($parts);

        return implode(PHP_EOL, $parts);
    }

    public function isValid(): bool
    {
        return count($this->columns) > 0 || count($this->tables) > 0;
    }

    protected function getSelect(): string
    {
        $sql   = [];
        $sql[] = 'SELECT';
        $sql[] = $this->getModifiers();

        $sql = array_filter($sql);

        $sql = implode(' ', $sql);

        return $sql . ' ' . $this->getColumns();
    }

    protected function getColumns(): string
    {
        if (empty($this->columns)) {
            return '*';
        }

        $parts = [];
        foreach ($this->columns as $column) {
            $parts[] = (string)$column;
        }

        return implode(', ', $parts);
    }

    protected function getModifiers(): string
    {
        if (empty($this->modifiers)) {
            return '';
        }

        return implode(' ', $this->modifiers);
    }

    protected function getFrom(): array
    {
        return ['FROM ' . implode(', ', $this->tables)];
    }

    /**
     * @return string[]
     */
    protected function getJoin(): array
    {
        if (count($this->joins) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->joins as $join) {
            $parts[] = (string)$join;
        }

        return $parts;
    }

    protected function getWhere(): array
    {
        if (count($this->whereParts) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->whereParts as $wherePart) {
            $parts[] = (string)$wherePart;
        }

        return ['WHERE ' . implode(' AND ', $parts)];
    }

    protected function getGroupBy(): array
    {
        if (count($this->groupByParts) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->groupByParts as $groupByPart) {
            $parts[] = (string)$groupByPart;
        }

        return ['GROUP BY ' . implode(', ', $parts)];
    }

    protected function getHaving(): array
    {
        if (count($this->havingParts) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->havingParts as $havingPart) {
            $parts[] = (string)$havingPart;
        }

        return ['HAVING ' . implode(' AND ', $parts)];
    }

    protected function getOrderBy(): array
    {
        if (count($this->orderByParts) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->orderByParts as $column => $direction) {
            $parts[] = "$column $direction";
        }

        return ['ORDER BY ' . implode(', ', $parts)];
    }

    protected function getLimit(): array
    {
        $parts = [];
        if ($this->offset !== null) {
            $parts[] = sprintf('OFFSET %d ROWS', $this->offset);
        }
        if ($this->limit !== null) {
            $parts[] = sprintf('FETCH FIRST %d ROWS ONLY', $this->limit);
        }

        return $parts;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        foreach ($this->columns as $column) {
            $params = array_merge($params, $column->getParams());
        }

        foreach ($this->joins as $join) {
            $params = array_merge($params, $join->getParams());
        }

        foreach ($this->whereParts as $wherePart) {
            $params = array_merge($params, $wherePart->getParams());
        }

        foreach ($this->groupByParts as $groupByPart) {
            $params = array_merge($params, $groupByPart->getParams());
        }

        foreach ($this->havingParts as $havingPart) {
            $params = array_merge($params, $havingPart->getParams());
        }

        return $params;
    }
}
