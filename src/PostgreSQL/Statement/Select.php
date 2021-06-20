<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\Statement\Select as GenericSelect;

class Select extends GenericSelect
{

    /** @var Select[] */
    protected array $union = [];

    protected ?int $outerOffset = null;

    protected ?int $outerLimit = null;

    /** @var array<string,string> */
    protected array $outerOrderByParts = [];

    /**
     * @param int|null $offset
     *
     * @return $this
     */
    public function setOuterOffset(?int $offset): static
    {
        $this->outerOffset = $offset;

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function setOuterLimit(?int $limit): static
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
    public function addOuterOrderBy(string $column, string $direction = 'ASC'): static
    {
        $this->outerOrderByParts[$column] = $direction;

        return $this;
    }

    /**
     * @param Select $select
     *
     * @return $this
     */
    public function addUnion(Select $select): static
    {
        $this->union[] = $select;

        return $this;
    }

    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getUnion()
        );

        $parts = array_filter($parts);

        $sql = implode(PHP_EOL, $parts);

        $parts = array_merge(
            ['(' . $sql . ')'],
            $this->getOuterOrderBy(),
            $this->getOuterLimit()
        );

        $parts = array_filter($parts);

        return implode(PHP_EOL, $parts);
    }

    public function getUnion(): array
    {
        $parts = [];
        foreach ($this->union as $select) {
            $parts[] = 'UNION' . PHP_EOL . $select;
        }

        return $parts;
    }

    protected function getOuterOrderBy(): array
    {
        if (count($this->outerOrderByParts) === 0) {
            return [];
        }

        $parts = [];
        foreach ($this->outerOrderByParts as $column => $direction) {
            $parts[] = "$column $direction";
        }

        return ['ORDER BY ' . implode(', ', $parts)];
    }

    protected function getOuterLimit(): array
    {
        $parts = [];
        if ($this->outerLimit !== null && $this->outerOffset !== null) {
            $parts[] = 'LIMIT ' . $this->outerOffset . ', ' . $this->outerLimit;
        } elseif ($this->outerLimit !== null) {
            $parts[] = 'LIMIT ' . $this->outerLimit;
        }

        return $parts;
    }
}