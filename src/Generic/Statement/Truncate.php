<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;
use RuntimeException;

class Truncate implements ITruncate
{
    /** @var array<int,Table|string> */
    protected array $tables = [];

    /**
     * @param Table|string ...$tables
     *
     * @return $this
     */
    public function from(Table|string ...$tables): static
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
            throw new RuntimeException('under-initialized TRUNCATE query');
        }

        return 'TRUNCATE ' . implode(', ', $this->tables);
    }

    public function isValid(): bool
    {
        return count($this->tables) > 0;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return [];
    }
}
