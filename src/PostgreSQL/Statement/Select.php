<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\IQueryPart;
use QB\Generic\Statement\Select as GenericSelect;
use QB\PostgreSQL\Clause\CombiningQuery;
use QB\PostgreSQL\Clause\Lock;

class Select extends GenericSelect
{
    /** @var CombiningQuery[] */
    protected array $combiningQueries = [];

    protected ?Lock $lock = null;

    protected ?string $lockTable = null;

    protected ?int $outerOffset = null;

    protected ?int $outerLimit = null;

    /** @var array<string,string> */
    protected array $outerOrderBy = [];

    /**
     * @param int|null $offset
     *
     * @return $this
     */
    public function outerOffset(?int $offset): static
    {
        $this->outerOffset = $offset;

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function outerLimit(?int $limit): static
    {
        $this->outerLimit = $limit;

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return Select
     */
    public function outerOrderBy(string $column, string $direction = 'ASC'): static
    {
        $this->outerOrderBy[$column] = $direction;

        return $this;
    }

    /**
     * @param Lock $lock
     *
     * @return $this
     */
    public function lock(Lock $lock): static
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * @param IQueryPart  $select
     * @param string|null $modifier
     *
     * @return $this
     */
    public function union(IQueryPart $select, ?string $modifier = null): static
    {
        $this->combiningQueries[] = new CombiningQuery(CombiningQuery::TYPE_UNION, $select, $modifier);

        return $this;
    }

    /**
     * @param IQueryPart  $select
     * @param string|null $modifier
     *
     * @return $this
     */
    public function intersect(IQueryPart $select, ?string $modifier = null): static
    {
        $this->combiningQueries[] = new CombiningQuery(CombiningQuery::TYPE_INTERSECT, $select, $modifier);

        return $this;
    }

    /**
     * @param IQueryPart  $select
     * @param string|null $modifier
     *
     * @return $this
     */
    public function except(IQueryPart $select, ?string $modifier = null): static
    {
        $this->combiningQueries[] = new CombiningQuery(CombiningQuery::TYPE_EXCEPT, $select, $modifier);

        return $this;
    }

    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getLock(),
            $this->getCombiningQueries()
        );

        $parts = array_filter($parts);

        $sql = implode(PHP_EOL, $parts);

        if ($this->outerLimit === null && $this->outerOffset === null && count($this->outerOrderBy) === 0) {
            return $sql;
        }

        $parts = array_merge(
            ['(' . $sql . ')'],
            $this->getOuterOrderBy(),
            $this->getOuterLimit()
        );

        $parts = array_filter($parts);

        return implode(PHP_EOL, $parts);
    }

    /**
     * @return string[]
     */
    protected function getLock(): array
    {
        if ($this->lock === null) {
            return [];
        }

        return [(string)$this->lock];
    }

    /**
     * @return string[]
     */
    protected function getCombiningQueries(): array
    {
        $parts = [];
        foreach ($this->combiningQueries as $query) {
            $parts[] = (string)$query;
        }

        return $parts;
    }

    /**
     * @return string[]
     */
    protected function getOuterOrderBy(): array
    {
        if (count($this->outerOrderBy) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->outerOrderBy as $column => $direction) {
            $parts[] = "$column $direction";
        }

        return ['ORDER BY ' . implode(', ', $parts)];
    }

    /**
     * @return string[]
     */
    protected function getOuterLimit(): array
    {
        $parts = [];
        if ($this->outerLimit !== null) {
            $parts[] = sprintf('LIMIT %d', $this->outerLimit);
        }
        if ($this->outerOffset !== null) {
            $parts[] = sprintf('OFFSET %d ROWS', $this->outerOffset);
        }

        return $parts;
    }
}
