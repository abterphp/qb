<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Statement\Select as GenericSelect;

class Select extends GenericSelect
{
    public const DISTINCTROW         = 'DISTINCTROW';
    public const HIGH_PRIORITY       = 'HIGH_PRIORITY';
    public const STRAIGHT_JOIN       = 'STRAIGHT_JOIN';
    public const SQL_SMALL_RESULT    = 'SQL_SMALL_RESULT';
    public const SQL_BIG_RESULT      = 'SQL_BIG_RESULT';
    public const SQL_BUFFER_RESULT   = 'SQL_BUFFER_RESULT';
    public const SQL_NO_CACHE        = 'SQL_NO_CACHE';
    public const SQL_CALC_FOUND_ROWS = 'SQL_CALC_FOUND_ROWS';

    public const LOCK_FOR_SHARE   = 'FOR SHARE';
    public const LOCK_FOR_UPDATE  = 'FOR UPDATE';
    public const LOCK_NOWAIT      = 'NOWAIT';
    public const LOCK_SKIP_LOCKED = 'SKIP LOCKED';

    protected const GROUP_WITH_ROLLUP = 'WITH ROLLUP';

    protected bool $groupWithRollup = false;

    /** @var Select[] */
    protected array $union = [];

    /** @var string[] */
    protected array $locks = [];

    protected ?int $outerOffset = null;

    protected ?int $outerLimit = null;

    /** @var array<string,string> */
    protected array $outerOrderByParts = [];

    /** @var string[] */
    protected array $outerLocks = [];

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function addModifier(string ...$modifiers): static
    {
        foreach ($modifiers as $modifier) {
            switch ($modifier) {
                case static::ALL:
                case static::DISTINCT:
                case static::DISTINCTROW:
                    $this->modifiers[0] = $modifier;
                    break;
                case static::HIGH_PRIORITY:
                    $this->modifiers[1] = $modifier;
                    break;
                case static::STRAIGHT_JOIN:
                    $this->modifiers[2] = $modifier;
                    break;
                case static::SQL_SMALL_RESULT:
                    $this->modifiers[3] = $modifier;
                    break;
                case static::SQL_BIG_RESULT:
                    $this->modifiers[4] = $modifier;
                    break;
                case static::SQL_BUFFER_RESULT:
                    $this->modifiers[5] = $modifier;
                    break;
                case static::SQL_NO_CACHE:
                    $this->modifiers[6] = $modifier;
                    break;
                case static::SQL_CALC_FOUND_ROWS:
                    $this->modifiers[7] = $modifier;
                    break;
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function setGroupWithRollup(): static
    {
        $this->groupWithRollup = true;

        return $this;
    }

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
     * @return $this
     */
    public function addOuterOrderBy(string $column, string $direction = 'ASC'): static
    {
        $this->outerOrderByParts[$column] = $direction;

        return $this;
    }

    /**
     * @param string ...$locks
     *
     * @return $this
     */
    public function addLock(string ...$locks): static
    {
        foreach ($locks as $lock) {
            switch ($lock) {
                case static::LOCK_FOR_SHARE:
                case static::LOCK_FOR_UPDATE:
                    $this->locks[0] = $lock;
                    break;
                case static::LOCK_NOWAIT:
                case static::LOCK_SKIP_LOCKED:
                    $this->locks[1] = $lock;
                    break;
            }
        }

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

    /**
     * @param string ...$locks
     *
     * @return $this
     */
    public function addOuterLock(string ...$locks): static
    {
        foreach ($locks as $lock) {
            switch ($lock) {
                case static::LOCK_FOR_SHARE:
                case static::LOCK_FOR_UPDATE:
                    $this->outerLocks[0] = $lock;
                    break;
                case static::LOCK_NOWAIT:
                case static::LOCK_SKIP_LOCKED:
                    $this->outerLocks[1] = $lock;
                    break;
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getLocks(),
            $this->getUnion()
        );

        $parts = array_filter($parts);

        $sql = implode(PHP_EOL, $parts);

        if ($this->outerLimit === null && $this->outerOffset === null
            && count($this->outerOrderByParts) === 0 && count($this->outerLocks) === 0) {

            return $sql;
        }

        $parts = array_merge(
            ['(' . $sql . ')'],
            $this->getOuterOrderBy(),
            $this->getOuterLimit(),
            $this->getOuterLocks()
        );

        $parts = array_filter($parts);

        return implode(PHP_EOL, $parts);
    }

    protected function getGroupBy(): array
    {
        $groupBy = parent::getGroupBy();

        if ($this->groupWithRollup && count($groupBy) > 0) {
            $groupBy[0] = sprintf('%s %s', $groupBy[0], static::GROUP_WITH_ROLLUP);
        }

        return $groupBy;
    }

    protected function getLimit(): array
    {
        $parts = [];
        if ($this->limit !== null && $this->offset !== null) {
            $parts[] = 'LIMIT ' . $this->offset . ', ' . $this->limit;
        } elseif ($this->limit !== null) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        return $parts;
    }

    protected function getLocks(): array
    {
        return [implode(' ', $this->locks)];
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

    protected function getOuterLocks(): array
    {
        return [implode(' ', $this->outerLocks)];
    }
}
