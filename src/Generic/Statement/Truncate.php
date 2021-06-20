<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

class Truncate implements ITruncate
{
    /** @var array<int,string|Table> */
    protected array $tables = [];

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
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('under-initialized TRUNCATE query');
        }

        return $this->truncate();
    }

    public function isValid(): bool
    {
        return count($this->tables) > 0;
    }

    protected function truncate(): string
    {
        return 'TRUNCATE ' . implode(', ', $this->tables);
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }
}