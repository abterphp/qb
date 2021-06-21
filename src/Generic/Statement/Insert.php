<?php

declare(strict_types=1);

namespace QB\Generic\Statement;

use QB\Generic\Clause\Table;

class Insert implements IInsert
{
    /** @var array<int,string|Table> */
    protected array $tables = [];

    /** @var string[] */
    protected array $modifiers = [];

    /** @var array<int|string,string> */
    protected array $columns = [];

    /** @var array<int,mixed> */
    protected array $values = [];

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
     * @param string ...$modifiers
     *
     * @return $this
     */
    public function addModifier(string ...$modifiers): static
    {
        $this->modifiers = array_merge($this->modifiers, $modifiers);

        return $this;
    }

    /**
     * @param string      $column
     * @param string|null $alias
     *
     * @return $this
     */
    public function addColumn(string $column, ?string $alias = null): static
    {
        if ($alias === null) {
            $this->columns[] = $column;
        } else {
            $this->columns[$alias] = $column;
        }

        return $this;
    }

    /**
     * @param string ...$columns
     *
     * @return $this
     */
    public function addColumns(string ...$columns): static
    {
        foreach ($columns as $rawColumn) {
            if (strpos($rawColumn, ' AS ')) {
                $parts                    = explode(' AS ', $rawColumn);
                $this->columns[$parts[1]] = $parts[0];
            } else {
                $this->columns[] = $rawColumn;
            }
        }

        return $this;
    }

    /**
     * @param mixed ...$values
     *
     * @return $this
     */
    public function addValues(...$values): static
    {
        if (count($this->columns) && count($values) !== count($this->columns)) {
            throw new \InvalidArgumentException('number of values does not match the number of columns');
        }

        $this->values[] = $values;

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

        $insert = $this->insert();

        $sqlParts = array_merge(
            [$insert],
            $this->values(),
        );

        $sqlParts = array_filter($sqlParts);

        return implode(PHP_EOL, $sqlParts);
    }

    public function isValid(): bool
    {
        return count($this->tables) === 1 && count($this->values) > 0;
    }

    protected function insert(): string
    {
        $sql = [];
        $sql[] = 'INSERT';
        $sql[] = $this->getModifiers();
        $sql[] = 'INTO';
        $sql[] = $this->tables[0];

        $sql = array_filter($sql);

        $sql = implode(' ', $sql);

        if (count($this->columns) === 0) {
            return $sql;
        }

        $columnParts = [];
        foreach ($this->columns as $alias => $column) {
            if (is_numeric($alias)) {
                $columnParts[] = $column;
            } else {
                $columnParts[] = "$column AS $alias";
            }
        }

        return $sql . ' (' . implode(', ', $columnParts) . ')';
    }

    protected function getModifiers(): string
    {
        if (empty($this->modifiers)) {
            return '';
        }

        return implode(' ', $this->modifiers);
    }

    protected function values(): array
    {
        $columnCount = count($this->columns) > 0 ? count($this->columns) : count($this->values[0]);

        $values = array_fill(0, $columnCount, '?');

        $row = sprintf('(%s)', implode(', ', $values));

        $rows = array_fill(0, count($this->values), $row);

        return ['VALUES ' . implode(",\n", $rows)];
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        foreach ($this->values as $values) {
            foreach ($values as $value) {
                $params[] = [$value, \PDO::PARAM_STR];
            }
        }

        return $params;
    }

    /**
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }
}
