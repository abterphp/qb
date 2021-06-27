<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\Statement\Select as GenericSelect;
use QB\PostgreSQL\Clause\Lock;

class Select extends GenericSelect
{
    protected const UNION     = 'union';
    protected const INTERSECT = 'intersect';
    protected const EXCEPT    = 'except';

    protected array $unionLikes = [];

    protected ?Lock $lock = null;

    protected ?string $lockTable = null;

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
     * @param Lock $lock
     *
     * @return $this
     */
    public function setLock(Lock $lock): static
    {
        $this->lock = $lock;

        return $this;
    }

    /**
     * @param Select $select
     * @param string $modifier
     *
     * @return $this
     */
    public function addUnion(Select $select, string $modifier = ''): static
    {
        return $this->addUnionLike(static::UNION, $select, $modifier);
    }

    /**
     * @param Select $select
     * @param string $modifier
     *
     * @return $this
     */
    public function addIntersect(Select $select, string $modifier = ''): static
    {
        return $this->addUnionLike(static::INTERSECT, $select, $modifier);
    }

    /**
     * @param Select $select
     * @param string $modifier
     *
     * @return $this
     */
    public function addExcept(Select $select, string $modifier = ''): static
    {
        return $this->addUnionLike(static::EXCEPT, $select, $modifier);
    }

    /**
     * @param string $type
     * @param Select $select
     * @param string $modifier
     *
     * @return $this
     */
    protected function addUnionLike(string $type, Select $select, string $modifier = ''): static
    {
        if ($type !== static::INTERSECT && $type !== static::EXCEPT) {
            $type = static::UNION;
        }

        if ($modifier !== static::ALL && $modifier !== static::DISTINCT) {
            $modifier = '';
        }

        $this->unionLikes[] = [$type, $select, $modifier];

        return $this;
    }

    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getLock(),
            $this->getUnionLikes()
        );

        $parts = array_filter($parts);

        $sql = implode(PHP_EOL, $parts);

        if ($this->outerLimit === null && $this->outerOffset === null && count($this->outerOrderByParts) === 0) {
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
    public function getUnionLikes(): array
    {
        $parts = [];
        foreach ($this->unionLikes as $unionLike) {
            $unionType = $unionLike[0];
            $select    = $unionLike[1];
            $modifier  = '';
            if ($unionLike[2]) {
                $modifier = ' ' . $unionLike[2];
            }

            switch ($unionType) {
                case static::UNION:
                    $parts[] = 'UNION' . $modifier . PHP_EOL . $select;
                    break;
                case static::INTERSECT:
                    $parts[] = 'INTERSECT' . $modifier . PHP_EOL . $select;
                    break;
                case static::EXCEPT:
                    $parts[] = 'EXCEPT' . $modifier . PHP_EOL . $select;
                    break;
            }
        }

        return $parts;
    }

    /**
     * @return string[]
     */
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

    /**
     * @return string[]
     */
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
