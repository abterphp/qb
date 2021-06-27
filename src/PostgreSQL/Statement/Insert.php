<?php

declare(strict_types=1);

namespace QB\PostgreSQL\Statement;

use QB\Generic\Statement\Insert as GenericInsert;

class Insert extends GenericInsert
{
    public const DEFAULT_VALUES = 'DEFAULT VALUES';

    public const CONFLICT_DO_NOTHING = 'DO NOTHING';
    public const CONFLICT_DO_UPDATE  = 'DO UPDATE';

    /** @var array<int,string> */
    protected array $onConflict = [];

    /** @var array<int,string> */
    protected array $doUpdate = [];

    /** @var bool */
    protected bool $doNothing = false;

    /** @var array<int,string> */
    protected array $returning = [];

    /**
     * @param string ...$columns
     *
     * @return $this
     */
    public function setOnConflict(string ...$columns): static
    {
        $this->onConflict = $columns;

        return $this;
    }

    /**
     * @param string ...$columns
     *
     * @return $this
     */
    public function setDoUpdate(string ...$columns): static
    {
        $this->doNothing = false;
        $this->doUpdate  = $columns;

        return $this;
    }

    /**
     * @return $this
     */
    public function setDoNothing(): static
    {
        $this->doNothing = true;
        $this->doUpdate  = [];

        return $this;
    }

    /**
     * @param string ...$columns
     *
     * @return $this
     */
    public function setReturning(string ...$columns): static
    {
        $this->returning = $columns;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $parts = [parent::__toString()];

        if ($this->doNothing || count($this->doUpdate) > 0) {
            $action = $this->doNothing ? static::CONFLICT_DO_NOTHING : static::CONFLICT_DO_UPDATE;
            if (count($this->onConflict) > 0) {
                $parts[] = sprintf('ON CONFLICT (%s) %s', implode(', ', $this->onConflict), $action);
            } else {
                $parts[] = sprintf('ON CONFLICT %s', $action);
            }

            if (count($this->doUpdate) > 0) {
                $parts[] = sprintf('SET %s', implode(', ', $this->doUpdate));
            }
        }

        if (count($this->returning) > 0) {
            $parts[] = sprintf('RETURNING %s', implode(',', $this->returning));
        }

        return implode(PHP_EOL, $parts);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return count($this->tables) === 1;
    }

    /**
     * @return string[]
     */
    protected function values(): array
    {
        if (count($this->values) == 0) {
            return [self::DEFAULT_VALUES];
        }

        return parent::values();
    }
}
