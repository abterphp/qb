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
     * @param string|Table $table
     *
     * @return $this
     */
    public function setInto(string|Table $table): static
    {
        $this->tables = [$table];

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
     * @param string ...$columns
     *
     * @return $this
     */
    public function setColumns(string ...$columns): static
    {
        if (count($this->values) > 0 && count($columns) !== count($this->values[0])) {
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
    public function addValues(...$values): static
    {
        if (count($this->columns) > 0 && count($values) !== count($this->columns)) {
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

        $sqlParts = array_merge(
            [$this->getCommand()],
            $this->values(),
        );

        $sqlParts = array_filter($sqlParts);

        return implode(PHP_EOL, $sqlParts);
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return count($this->tables) === 1 && count($this->values) > 0;
    }

    protected function getCommand(): string
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

        return $sql . ' (' . implode(', ', $this->columns) . ')';
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
        return array_merge(...$this->values);
    }
}
