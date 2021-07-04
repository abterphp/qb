<?php

declare(strict_types=1);

namespace QB\MySQL\Statement;

use QB\Generic\Statement\Delete as GenericDelete;
use QB\Generic\Statement\ISelect;

class Delete extends GenericDelete
{
    public const LOW_PRIORITY = 'LOW_PRIORITY';
    public const QUICK        = 'QUICK';
    public const IGNORE       = 'IGNORE';

    /** @var array<int,string> */
    protected array $modifiers = [];

    /** @var array<string,string> */
    protected array $orderByParts = [];

    protected ?int $limit = null;

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function modifier(string ...$modifiers): static
    {
        foreach ($modifiers as $modifier) {
            switch ($modifier) {
                case static::LOW_PRIORITY:
                    $this->modifiers[0] = $modifier;
                    break;
                case static::QUICK:
                    $this->modifiers[1] = $modifier;
                    break;
                case static::IGNORE:
                    $this->modifiers[2] = $modifier;
                    break;
            }
        }

        return $this;
    }

    /**
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy(string $column, string $direction = ISelect::DIRECTION_ASC): static
    {
        $this->orderByParts[$column] = $direction;

        return $this;
    }

    /**
     * @param int|null $limit
     *
     * @return $this
     */
    public function limit(?int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = array_merge(
            [parent::__toString()],
            $this->getOrderBy(),
            $this->getLimit(),
        );

        return implode(PHP_EOL, $parts);
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
        if ($this->limit !== null) {
            $parts[] = sprintf('LIMIT %d', $this->limit);
        }

        return $parts;
    }
}
