<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\IQueryPart;
use QB\Generic\Statement\Select as GenericSelect;
use QB\MySQL\Clause\CombiningQuery;
use QB\MySQL\Clause\Lock;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
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

    protected const GROUP_WITH_ROLLUP = 'WITH ROLLUP';

    protected bool $groupWithRollup = false;

    /** @var CombiningQuery[] */
    protected array $combiningQueries = [];

    protected ?Lock $lock = null;

    protected ?int $outerOffset = null;

    protected ?int $outerLimit = null;

    /** @var array<string,string> */
    protected array $outerOrderBy = [];

    protected ?Lock $outerLock = null;

    /**
     * @SuppressWarnings("complexity")
     *
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function modifier(string ...$modifiers): static
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

        ksort($this->modifiers);

        return $this;
    }

    /**
     * @return $this
     */
    public function groupWithRollup(): static
    {
        $this->groupWithRollup = true;

        return $this;
    }

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
     * @return $this
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
     * @param Lock $lock
     *
     * @return $this
     */
    public function outerLock(Lock $lock): static
    {
        $this->outerLock = $lock;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getLock(),
            $this->getUnion()
        );

        $parts = array_filter($parts);

        $sql = implode(PHP_EOL, $parts);

        if (
            $this->outerLimit === null && $this->outerOffset === null && count($this->outerOrderBy) === 0 &&
            $this->outerLock === null
        ) {
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

    /**
     * @return string[]
     */
    protected function getGroupBy(): array
    {
        $groupBy = parent::getGroupBy();

        if ($this->groupWithRollup && count($groupBy) > 0) {
            $groupBy[0] = sprintf('%s %s', $groupBy[0], static::GROUP_WITH_ROLLUP);
        }

        return $groupBy;
    }

    /**
     * @return string[]
     */
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
    protected function getUnion(): array
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
        if ($this->outerLimit !== null && $this->outerOffset !== null) {
            $parts[] = 'LIMIT ' . $this->outerOffset . ', ' . $this->outerLimit;
        } elseif ($this->outerLimit !== null) {
            $parts[] = 'LIMIT ' . $this->outerLimit;
        }

        return $parts;
    }

    /**
     * @return string[]
     */
    protected function getOuterLocks(): array
    {
        if ($this->outerLock === null) {
            return [];
        }

        return [(string)$this->outerLock];
    }
}
