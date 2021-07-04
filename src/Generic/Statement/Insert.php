<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\ITable;
use QB\Generic\IQueryPart;

class Insert implements IInsert
{
    protected ITable|string $table;

    /** @var string[] */
    protected array $modifiers = [];

    /** @var array<int|string,string> */
    protected array $columns = [];

    /** @var array<int,mixed> */
    protected array $rawValues = [];

    /**
     * @param string|ITable $table
     *
     * @return $this
     */
    public function into(string|ITable $table): static
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function modifier(string ...$modifiers): static
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);

        return $this;
    }

    /**
     * @param string ...$columns
     *
     * @return $this
     */
    public function columns(string ...$columns): static
    {
        if (count($this->rawValues) > 0 && count($columns) !== count($this->rawValues[0])) {
            throw new \InvalidArgumentException('number of columns does not match the number of values');
        }

        $this->columns = $columns;

        return $this;
    }

    /**
     * @param mixed ...$values
     *
     * @return $this
     */
    public function values(...$values): static
    {
        if (count($this->columns) > 0 && count($values) !== count($this->columns)) {
            throw new \InvalidArgumentException('number of values does not match the number of columns');
        }

        $this->rawValues[] = $values;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isValid()) {
            throw new \RuntimeException('under-initialized INSERT query');
        }

        $sqlParts = array_merge(
            [$this->getCommand()],
            $this->getRawValues(),
        );

        $sqlParts = array_filter($sqlParts);

        return implode(PHP_EOL, $sqlParts);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->table && count($this->rawValues) > 0;
    }

    protected function getCommand(): string
    {
        $sql = [];
        $sql[] = 'INSERT';
        $sql[] = $this->getModifiers();
        $sql[] = 'INTO';
        $sql[] = $this->table;

        $sql = array_filter($sql);

        $sql = implode(' ', $sql);

        if (count($this->columns) === 0) {
            return $sql;
        }

        return $sql . ' (' . implode(', ', $this->columns) . ')';
    }

    protected function getModifiers(): string
    {
        if (empty($this->modifiers)) {
            return '';
        }

        return implode(' ', $this->modifiers);
    }

    /**
     * @return string[]
     */
    protected function getRawValues(): array
    {
        $lines = [];
        foreach ($this->rawValues as $values) {
            $line = [];
            foreach ($values as $value) {
                $line[] = (string)$value;
            }
            $lines[] = '(' . implode(', ', $line) . ')';
        }

        return ['VALUES ' . implode(",\n", $lines)];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        foreach ($this->rawValues as $values) {
            foreach ($values as $value) {
                if ($value instanceof IQueryPart) {
                    $params = array_merge($params, $value->getParams());
                }
            }
        }

        return $params;
    }
}
